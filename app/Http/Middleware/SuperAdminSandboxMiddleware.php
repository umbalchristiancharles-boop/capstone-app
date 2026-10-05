<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\SuperAdminSandbox;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminSandboxMiddleware
{
    public function __construct(private readonly SuperAdminSandbox $sandbox)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $sandboxDatabase = $request->session()->get('superadmin_sandbox_database');
        $superAdmin = $this->resolveSuperAdmin($request);

        if (! $sandboxDatabase && $superAdmin) {
            try {
                $sandboxDatabase = $this->sandbox->createDatabase(
                    (int) $superAdmin->id,
                    $request->session()->getId()
                );
                $this->prepareSandboxFiles($sandboxDatabase);
                $request->session()->put('superadmin_sandbox_database', $sandboxDatabase);
            } catch (\Throwable $exception) {
                Log::error('Super admin request blocked because its isolated sandbox could not be created.', [
                    'user_id' => $superAdmin->id,
                    'exception' => $exception->getMessage(),
                ]);
                $this->discardIncompleteSandbox($sandboxDatabase ?? null);

                return $this->sandboxUnavailableResponse($request);
            }
        }

        if (! $sandboxDatabase) {
            $response = $next($request);

            $superAdmin = $this->resolveSuperAdmin($request);
            if (! $superAdmin) {
                return $response;
            }

            try {
                $sandboxDatabase = $this->sandbox->createDatabase(
                    (int) $superAdmin->id,
                    $request->session()->getId()
                );
                $this->prepareSandboxFiles($sandboxDatabase);
                $request->session()->put('superadmin_sandbox_database', $sandboxDatabase);
                return $response;
            } catch (\Throwable $exception) {
                Log::error('Super admin login blocked because its isolated sandbox could not be created.', [
                    'user_id' => $superAdmin->id,
                    'exception' => $exception->getMessage(),
                ]);
                Auth::logout();
                $request->session()->invalidate();
                $this->discardIncompleteSandbox($sandboxDatabase ?? null);
                try {
                    $superAdmin->tokens()->delete();
                } catch (\Throwable $tokenException) {
                    Log::error('Could not revoke super admin login token after sandbox creation failed.', [
                        'user_id' => $superAdmin->id,
                        'exception' => $tokenException->getMessage(),
                    ]);
                }

                return $this->sandboxUnavailableResponse($request);
            }
        }

        $sessionKey = substr(hash('sha256', $sandboxDatabase), 0, 32);
        $sandboxUserId = $request->session()->get('user_id');
        $originalRoots = [
            'local' => config('filesystems.disks.local.root'),
            'public' => config('filesystems.disks.public.root'),
            'public_url' => config('filesystems.disks.public.url'),
        ];
        $originalPublicPath = app()->publicPath();
        $originalStoragePath = app()->storagePath();
        $originalMailDriver = config('mail.default');
        $originalQueueDriver = config('queue.default');
        $sandboxRoot = $this->sandboxRoot($originalStoragePath, $sessionKey);
        try {
            $this->prepareSandboxFiles($sandboxDatabase, $sandboxRoot, $originalStoragePath, $originalPublicPath);
        } catch (\Throwable $exception) {
            Log::error('Super admin request blocked because sandbox files could not be prepared.', [
                'exception' => $exception->getMessage(),
            ]);

            return $this->sandboxUnavailableResponse($request);
        }
        $sandboxStoragePath = $sandboxRoot . DIRECTORY_SEPARATOR . 'storage';

        try {
            $connectionState = $this->sandbox->activate($sandboxDatabase);
        } catch (\Throwable $exception) {
            Log::error('Super admin request blocked because its sandbox could not be activated.', [
                'exception' => $exception->getMessage(),
            ]);

            return $this->sandboxUnavailableResponse($request);
        }

        config([
            'filesystems.disks.local.root' => $sandboxStoragePath . DIRECTORY_SEPARATOR . 'app'
                . DIRECTORY_SEPARATOR . 'private',
            'filesystems.disks.public.root' => $sandboxStoragePath . DIRECTORY_SEPARATOR . 'app'
                . DIRECTORY_SEPARATOR . 'public',
            'filesystems.disks.public.url' => rtrim((string) config('app.url'), '/') . '/superadmin-public',
            'mail.default' => 'array',
            'queue.default' => 'sync',
        ]);
        app()->useStoragePath($sandboxStoragePath);
        app()->usePublicPath($sandboxRoot . DIRECTORY_SEPARATOR . 'public');
        Storage::forgetDisk('local');
        Storage::forgetDisk('public');
        Mail::forgetMailers();
        $this->forgetQueueManager();

        try {
            $response = $next($request);
        } finally {
            $shouldDiscard = ! $request->session()->has('superadmin_sandbox_database');

            $this->sandbox->restore($connectionState);
            config([
                'filesystems.disks.local.root' => $originalRoots['local'],
                'filesystems.disks.public.root' => $originalRoots['public'],
                'filesystems.disks.public.url' => $originalRoots['public_url'],
                'mail.default' => $originalMailDriver,
                'queue.default' => $originalQueueDriver,
            ]);
            app()->useStoragePath($originalStoragePath);
            app()->usePublicPath($originalPublicPath);
            Storage::forgetDisk('local');
            Storage::forgetDisk('public');
            Mail::forgetMailers();
            $this->forgetQueueManager();

            if ($shouldDiscard) {
                try {
                    $this->sandbox->dropDatabase($sandboxDatabase);
                } finally {
                    try {
                        if ($sandboxUserId) {
                            $user = User::find($sandboxUserId);
                            $user?->tokens()->delete();
                        }
                    } finally {
                        \Illuminate\Support\Facades\File::deleteDirectory($sandboxRoot);
                    }
                }
            }
        }

        return $response;
    }

    private function forgetQueueManager(): void
    {
        app()->forgetInstance('queue');
        Queue::clearResolvedInstance('queue');
    }

    private function prepareSandboxFiles(
        string $sandboxDatabase,
        ?string $sandboxRoot = null,
        ?string $sourceStoragePath = null,
        ?string $sourcePublicPath = null
    ): void {
        $sessionKey = substr(hash('sha256', $sandboxDatabase), 0, 32);
        $sandboxRoot ??= $this->sandboxRoot(app()->storagePath(), $sessionKey);
        $sourceStoragePath ??= app()->storagePath();
        $sourcePublicPath ??= app()->publicPath();
        $readyMarker = $sandboxRoot . DIRECTORY_SEPARATOR . '.files-ready';

        if (is_file($readyMarker)) {
            $this->refreshSandboxBuildAssets($sourcePublicPath, $sandboxRoot);
            return;
        }

        $copies = [
            [
                $sourceStoragePath . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'public',
                $sandboxRoot . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'app'
                    . DIRECTORY_SEPARATOR . 'public',
            ],
            [
                $sourceStoragePath . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'private',
                $sandboxRoot . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'app'
                    . DIRECTORY_SEPARATOR . 'private',
            ],
            ...array_map(
                fn (string $directory): array => [
                    $sourcePublicPath . DIRECTORY_SEPARATOR . $directory,
                    $sandboxRoot . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $directory,
                ],
                ['avatars', 'product-images', 'receipts', 'build']
            ),
        ];

        foreach ($copies as [$source, $destination]) {
            if (! is_dir($source)) {
                \Illuminate\Support\Facades\File::ensureDirectoryExists($destination);
                continue;
            }

            if (! \Illuminate\Support\Facades\File::copyDirectory($source, $destination)) {
                throw new \RuntimeException('Could not copy source files into the super admin sandbox.');
            }
        }

        \Illuminate\Support\Facades\File::ensureDirectoryExists($sandboxRoot);
        \Illuminate\Support\Facades\File::put($readyMarker, 'ready');
    }

    private function refreshSandboxBuildAssets(string $sourcePublicPath, string $sandboxRoot): void
    {
        $sourceManifest = $sourcePublicPath . DIRECTORY_SEPARATOR . 'build'
            . DIRECTORY_SEPARATOR . 'manifest.json';
        $sandboxBuild = $sandboxRoot . DIRECTORY_SEPARATOR . 'public'
            . DIRECTORY_SEPARATOR . 'build';
        $sandboxManifest = $sandboxBuild . DIRECTORY_SEPARATOR . 'manifest.json';

        if (! is_file($sourceManifest)) {
            throw new \RuntimeException('The source Vite manifest is missing.');
        }

        if (is_file($sandboxManifest)
            && hash_file('sha256', $sourceManifest) === hash_file('sha256', $sandboxManifest)) {
            return;
        }

        if (! \Illuminate\Support\Facades\File::copyDirectory(
            dirname($sourceManifest),
            $sandboxBuild
        )) {
            throw new \RuntimeException('Could not refresh frontend assets in the super admin sandbox.');
        }
    }

    private function sandboxRoot(string $storagePath, string $sessionKey): string
    {
        return $storagePath . DIRECTORY_SEPARATOR . 'app'
            . DIRECTORY_SEPARATOR . 'superadmin-sandboxes' . DIRECTORY_SEPARATOR . $sessionKey;
    }

    private function discardIncompleteSandbox(?string $database): void
    {
        if (! $database) {
            return;
        }

        try {
            $this->sandbox->dropDatabase($database);
        } finally {
            $sessionKey = substr(hash('sha256', $database), 0, 32);
            \Illuminate\Support\Facades\File::deleteDirectory(
                $this->sandboxRoot(app()->storagePath(), $sessionKey)
            );
        }
    }

    private function resolveSuperAdmin(Request $request): ?User
    {
        $userId = $request->session()->get('user_id');
        if ($userId) {
            $user = User::find($userId);
            if ($user && in_array(strtoupper($user->role ?? ''), ['SUPER_ADMIN', 'SUPERADMIN'], true)) {
                return $user;
            }
        }

        $user = $request->user();
        if ($user && in_array(strtoupper($user->role ?? ''), ['SUPER_ADMIN', 'SUPERADMIN'], true)) {
            return $user;
        }

        $bearerToken = $request->bearerToken();
        if ($bearerToken && class_exists(\Laravel\Sanctum\PersonalAccessToken::class)) {
            $token = \Laravel\Sanctum\PersonalAccessToken::findToken($bearerToken);
            $tokenUser = $token?->tokenable;
            if ($tokenUser instanceof User
                && in_array(strtoupper($tokenUser->role ?? ''), ['SUPER_ADMIN', 'SUPERADMIN'], true)) {
                $request->session()->put('user_id', $tokenUser->id);
                $request->session()->put('user_role', $tokenUser->role);

                return $tokenUser;
            }
        }

        return null;
    }

    private function sandboxUnavailableResponse(Request $request): Response
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'ok' => false,
                'message' => 'The super admin test sandbox is unavailable. No module changes were applied.',
            ], 503);
        }

        return redirect('/staff-landing')
            ->with('error', 'The super admin test sandbox is unavailable. Please contact support.');
    }
}
