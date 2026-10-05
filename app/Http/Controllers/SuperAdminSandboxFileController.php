<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\ServeFile;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminSandboxFileController extends Controller
{
    public function show(string $directory, string $path): Response
    {
        abort_unless(in_array($directory, ['avatars', 'product-images', 'receipts'], true), 404);
        abort_if($path === '' || str_contains($path, '..') || str_contains($path, '\\'), 404);

        $directoryPath = realpath(public_path($directory));
        $filePath = realpath(public_path($directory . DIRECTORY_SEPARATOR . $path));

        abort_unless(
            $directoryPath
            && $filePath
            && is_file($filePath)
            && str_starts_with($filePath, $directoryPath . DIRECTORY_SEPARATOR),
            404
        );

        return response()->file($filePath);
    }

    public function showSandboxPublicStorage(Request $request, string $path): Response
    {
        abort_unless($request->session()->has('superadmin_sandbox_database'), 404);
        abort_if($path === '' || str_contains($path, '..') || str_contains($path, '\\'), 404);

        $disk = Storage::disk('public');
        $root = realpath($disk->path(''));
        $filePath = realpath($disk->path($path));

        abort_unless(
            $root
            && $filePath
            && is_file($filePath)
            && str_starts_with($filePath, $root . DIRECTORY_SEPARATOR),
            404
        );

        return response()->file($filePath);
    }

    public function showStorage(Request $request, string $path): Response
    {
        abort_if($path === '' || str_contains($path, '..') || str_contains($path, '\\'), 404);

        $disk = Storage::disk('public');
        if ($disk->exists($path)) {
            $root = realpath($disk->path(''));
            $filePath = realpath($disk->path($path));

            abort_unless(
                $root
                && $filePath
                && is_file($filePath)
                && str_starts_with($filePath, $root . DIRECTORY_SEPARATOR),
                404
            );

            return response()->file($filePath);
        }

        if ($request->session()->has('superadmin_sandbox_database')) {
            abort(404);
        }

        return (new ServeFile('local', config('filesystems.disks.local'), app()->isProduction()))($request, $path);
    }
}
