<template>
    <div class="login-page">
        <div class="login-card">
            <button class="back-btn" @click="handleBack" :disabled="isLoading">
                ←
            </button>

            <div class="login-brand">
                <div class="brand-logo">CT</div>
                <div class="brand-text">
                    <h1 style="color: var(--text-dark) !important">Chikin Tayo Admin</h1>
                    <p>Secure access to your store dashboard.</p>
                </div>
            </div>

            <div v-if="showForceModal" class="security-banner">
                First time login? Change your password!
            </div>

            <form class="login-form" @submit.prevent="handleLogin" data-no-overlay="1">
                <div class="field-group">
                    <label for="username">Username</label>
                    <input
                        id="username"
                        v-model="username"
                        type="text"
                        name="username"
                        autocomplete="username"
                        placeholder="Enter admin username"
                        required
                    />
                </div>

                <div class="field-group">
                    <label for="password">Password</label>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <input
                            id="password"
                            v-model="password"
                            :type="showPassword ? 'text' : 'password'"
                            name="password"
                            autocomplete="current-password"
                            placeholder="Enter password"
                            required
                            style="flex:1; width:100%;"
                        />
                        <button type="button" class="btn btn-secondary" @click="toggleShowPassword" :aria-pressed="showPassword" :title="showPassword ? 'Hide password' : 'Show password'" style="display:flex; align-items:center; justify-content:center; padding:6px 8px; width:40px;">
                            <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-7 0-11-7-11-7a21.79 21.79 0 0 1 5.06-6.94"></path><path d="M1 1l22 22"></path><path d="M9.88 9.88A3 3 0 0 0 14.12 14.12"></path></svg>
                        </button>
                    </div>
                </div>

                <button class="login-btn" type="submit" :disabled="isLoading">
                    <span v-if="!isLoading">Login to Admin Panel</span>
                    <span v-else class="loading-inline">
                        <span class="dot"></span>
                        <span class="dot"></span>
                        <span class="dot"></span>
                    </span>
                </button>
            </form>
            <!-- ngayon lang, sa baba ng </form> at sa itaas ng <p class="login-hint"> -->
            <div class="forgot-password-link">
                <a href="/admin/password/forgot" class="forgot-link">
                    Forgot Password?
                </a>
            </div>



            <p class="login-hint">

            </p>

            <p v-if="errorMsg" class="error-text">{{ errorMsg }}</p>
        </div>

        <transition name="fade">
            <div v-if="showOverlay" class="loading-overlay">
                <div class="logo-loading-box">
                    <img
                        :src="logoImg"
                        alt="Chikin Tayo"
                        class="logo-loading-img"
                    />
                    <p>{{ overlayText }}</p>
                </div>
            </div>
        </transition>

        <ForcePasswordChangeModal
            :show="showForceModal"
            :username="loggedInUsername"
            :defaultPassword="defaultPassword"
            @completed="handleForceCompleted"
            @cancel="handleForceCancel"
        />
    </div>
</template>

<script setup>
import { onMounted, ref, watchEffect } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";
import "../css/adminlogin.css";
import ForcePasswordChangeModal from "./ForcePasswordChangeModal.vue";

const router = useRouter();

const username = ref("");
const password = ref("");
const showPassword = ref(false)
const isLoading = ref(false);
const showOverlay = ref(false);
const overlayText = ref("Loading Admin Panel...");
const errorMsg = ref("");
const showForceModal = ref(false);
const pendingRedirectPath = ref("/admin-panel");
const loggedInUsername = ref("");
const defaultPassword = ref("");

const logoImg = new URL("../assets/chikinlogo.png", import.meta.url).href;

async function handleLogin() {
    if (isLoading.value) return;
    errorMsg.value = "";
    isLoading.value = true;

    try {
        // Ensure the XSRF cookie is set for stateful authentication
        try {
            await axios.get("/sanctum/csrf-cookie", { withCredentials: true });

            // Get the XSRF token from cookie and set it as header
            function getCookie(name) {
                const match = document.cookie.match(new RegExp('(^|; )' + name + '=([^;]*)'));
                return match ? match[2] : null;
            }
            const xsrfToken = getCookie('XSRF-TOKEN');
            if (xsrfToken) {
                axios.defaults.headers.common['X-XSRF-TOKEN'] = decodeURIComponent(xsrfToken);
            }
        } catch (e) {
            // Ignore; some environments may not use Sanctum but we'll still attempt login
        }

        const res = await axios.post(
            "/api/login",
            {
                username: username.value,
                password: password.value,
            },
            {
                withCredentials: true,
            },
        );

        try {
            console.debug('[LOGIN] response status:', res.status)
            console.debug('[LOGIN] response headers:', res.headers)
            console.debug('[LOGIN] response data:', res.data)
        } catch (e) {}

        if (res.data.ok) {
            overlayText.value = "Loading panel...";

            // CRITICAL: Save user to localStorage for router guard
            const userData = {
                id: res.data.user?.id,
                username: res.data.user?.username,
                role: (res.data.user?.role || '').toLowerCase(), // Normalize to lowercase
                department: (res.data.user?.department || '').toLowerCase(), // Normalize to lowercase
                full_name: res.data.user?.full_name,
                branch_id: res.data.user?.branch_id
            };

            // Persist permissions for CUSTOM fallback routing
            if (res.data.user?.permissions) {
                userData.permissions = res.data.user.permissions;
            }

            try {
                localStorage.setItem('user', JSON.stringify(userData));
                console.debug('[LOGIN] User saved to localStorage:', userData);

                // Store Sanctum token if provided (use 'token' key for consistency with app.js)
                if (res.data.token) {
                    localStorage.setItem('token', res.data.token);
                    // Also set axios header immediately for subsequent API calls
                    axios.defaults.headers.common['Authorization'] = `Bearer ${res.data.token}`;
                    console.debug('[LOGIN] Token saved to localStorage and axios header set');
                }
                // Fetch authoritative user object (including permissions) and update localStorage
                try {
                    const me = await axios.get('/api/me', { withCredentials: true });
                    if (me && me.data && me.data.user) {
                        const serverUser = me.data.user;
                        const merged = Object.assign({}, userData, {
                            id: serverUser.id,
                            username: serverUser.username || userData.username,
                            role: (serverUser.role || userData.role || '').toLowerCase(),
                            department: (serverUser.department || userData.department || '').toLowerCase(),
                            full_name: serverUser.full_name || userData.full_name,
                            branch_id: serverUser.branch_id || userData.branch_id,
                            permissions: serverUser.permissions || userData.permissions || {},
                        });
                        localStorage.setItem('user', JSON.stringify(merged));
                        console.debug('[LOGIN] Updated localStorage user from /api/me', merged);
                    }
                } catch (e) {
                    console.debug('[LOGIN] /api/me fetch failed, continuing', e);
                }
            } catch (e) {
                console.error('[LOGIN] Failed to save user to localStorage:', e);
            }

            // Use server-provided redirect_path as the authoritative source
            // This ensures proper role-based redirection from backend validation
            let redirectPath = res.data.redirect_path;

            // Fallback to client-side calculation if server doesn't provide redirect_path
            if (!redirectPath) {
                redirectPath = resolveRedirectPath(res.data.user?.role, res.data.user?.department, res.data.user?.permissions);
            }

            // If user belongs to Main Branch (branch_id === 1) and is logistics manager,
            // prefer the main-branch logistics route so they land on the HQ panel.
            try {
                const bId = Number(res.data.user?.branch_id || 0)
                const r = (res.data.user?.role || '').toString().toUpperCase()
                const d = (res.data.user?.department || '').toString().toUpperCase()
                if (bId === 1 && d.includes('LOGISTICS')) {
                    redirectPath = '/main-branch/logistics'
                }
            } catch (e) {
                // ignore
            }

            // Validate redirect path exists to prevent invalid routing
            if (!redirectPath || redirectPath.includes('error=')) {
                console.error('Invalid redirect path received:', redirectPath);
                errorMsg.value = "System configuration error. Please contact support.";
                return;
            }

            if (res.data.user?.must_change_password) {
                                // Show forced password change modal before redirect
                                loggedInUsername.value = res.data.user?.username || username.value;
                                defaultPassword.value = '';
                                pendingRedirectPath.value = redirectPath;
                                // Store username for ChangePasswordPage
                                try {
                                    localStorage.setItem('pending_username', loggedInUsername.value);
                                } catch (e) {}
                                // Immediately update the URL to /change-password
                                router.push('/change-password');
                                showForceModal.value = true;
                                return;
            }

            // After login, ensure the user has an email attached. If not, send them
            // to the verify-email flow so they can attach and confirm an email.
            try {
                const meRes = await axios.get('/api/me', { withCredentials: true });
                const u = meRes.data.user;
                if (!u || !u.email) {
                    router.push('/verify-email');
                    return;
                }
            } catch (e) {
                // ignore errors from /api/me - fall back to normal redirect
            }

            setTimeout(() => {
                showOverlay.value = true;
                setTimeout(() => {
                    try { sessionStorage.setItem('skipRouteOverlay', '1'); } catch (e) {}
                    router.push(redirectPath);
                }, 600);
            }, 400);
        } else {
            errorMsg.value = res.data.message || "Login failed.";
        }
    } catch (e) {
        // Show more specific error message based on response
        if (e.response && e.response.data) {
            const data = e.response.data;
            if (data.message) {
                errorMsg.value = data.message;
            } else if (data.error) {
                errorMsg.value = data.error;
            } else {
                errorMsg.value = "Invalid username or password.";
            }
        } else if (e.request) {
            errorMsg.value = "Server not responding. Please check your connection.";
        } else {
            errorMsg.value = "Invalid username or password.";
        }

        try {
            console.warn('[LOGIN] error:', e.response ? {
                status: e.response.status,
                data: e.response.data,
            } : e)
        } catch (ee) {}
    } finally {
        isLoading.value = false;
    }
}

function handleBack() {
    if (isLoading.value) return;

    isLoading.value = true;
    overlayText.value = "Loading CHIKIN TAYO...";
    showOverlay.value = true;

    setTimeout(() => {
        try { sessionStorage.setItem('skipRouteOverlay', '1'); } catch (e) {}
        router.push('/staff-landing');
    }, 2000);
}

function resolveRedirectPath(role, department, permissions = {}) {
    // Use case-insensitive comparison by normalizing to uppercase
    let r = (role || '').toString().trim().toUpperCase();
    const d = (department || '').toString().trim().toUpperCase();

    // Normalize permissions for CUSTOM accounts (modules/functions arrays)
    const permModules = Array.isArray(permissions.modules)
        ? permissions.modules.map(m => (m || '').toString().trim().toUpperCase())
        : [];

    // Handle MANAGER_HR role specially - treat as MANAGER with HR department
    if (r === 'MANAGER_HR') {
        return '/manager/hr';
    }

    // CUSTOM: choose a panel based on assigned modules; fallback to staff panel
    if (r === 'CUSTOM') {
        const has = (keys) => keys.some(k => permModules.includes(k.toUpperCase()));
        if (has(['ADMIN'])) return '/admin-panel';
        if (has(['FINANCE'])) return '/manager/finance';
        if (has(['PROCUREMENT'])) return '/manager/procurement';
        if (has(['LOGISTICS'])) return '/manager/logistics';
        if (has(['INVENTORY'])) return '/staff/inventory';
        if (has(['KITCHEN'])) return '/staff/kitchen';
        if (has(['CASHIER'])) return '/staff/cashier';
        if (has(['HR'])) return '/manager/hr';
        if (has(['REPORTS'])) return '/manager/finance';
        return '/staff-panel';
    }

    // If department explicitly indicates inventory/finance/etc, prefer that
    if (d.includes('INVENTORY')) return '/staff/inventory'
    if (d.includes('FINANCE')) return '/manager/finance'
    if (d.includes('LOGISTICS')) return '/manager/logistics'
    if (d.includes('HR')) return '/manager/hr'

    // Branch-level manager explicit values (fallback)
    if (r === 'BRANCH_MANAGER' || r === 'BRANCH MANAGER' || r === 'BRANCH-MANAGER') return '/manager-panel';

    // Manager role string may be like 'Manager Inventory' or 'manager_inventory'
    if (r.includes('MANAGER')) {
        // try to detect department in the role string after the word 'manager'
        const after = r.replace(/MANAGER[_\- ]*/, '')
        if (after.includes('INVENTORY')) return '/staff/inventory'
        if (after.includes('FINANCE')) return '/manager/finance'
        if (after.includes('LOGISTICS')) return '/manager/logistics'
        if (after.includes('HR')) return '/manager/hr'
        // fallback to manager panel
        return '/manager-panel'
    }
    if (r === "STAFF") {
        const dept = (department || '').toUpperCase();
        if (dept === "INVENTORY") return "/staff/inventory";
        if (dept === "CASHIER") return "/staff/cashier";
        if (dept === "FINANCE") return "/staff/finance";
        if (dept === "KITCHEN") return "/staff/kitchen";
        if (dept === "LOGISTICS") return "/staff/logistics";
        // Add more departments as needed
        return "/staff-panel";
    }
    if (r === "HR") return "/hr-panel";
    if (r === "OWNER") return "/owner-panel";
    if (r === "ADMIN") return "/admin-panel";
    if (r === "SUPER_ADMIN" || r === "SUPERADMIN") return "/super-admin/procurement";

    // Invalid role - return to login with error
    console.error('Invalid role detected in redirect:', role);
    return "/login?error=invalid_role";
}

async function handleForceCompleted() {
    showForceModal.value = false;
    // After password change, check if user needs to verify email
    try {
        const res = await axios.get('/api/me', { withCredentials: true });
        const u = res.data.user
        if (!u || !u.email) {
            router.push('/verify-email');
            return;
        }
    } catch (e) {
        // ignore
    }
    overlayText.value = "Loading panel...";
    setTimeout(() => {
        showOverlay.value = true;
        setTimeout(() => {
            try { sessionStorage.setItem('skipRouteOverlay', '1'); } catch (e) {}
            // Redirect to pending path
            router.push(pendingRedirectPath.value || '/admin-panel');
        }, 600);
    }, 400);
}

async function handleForceCancel() {
    showForceModal.value = false;
    try {
        await axios.post("/api/logout", {}, { withCredentials: true });
    } catch (e) {
        // ignore logout errors
    }
    // Reload page to get fresh CSRF token
    window.location.reload();
}

onMounted(() => {
    // Skip /api/me check on the login page to avoid 401 noise in the console.
});

function toggleShowPassword() {
    showPassword.value = !showPassword.value
}
</script>

<style scoped>
.security-banner {
    margin: 0.75rem 0 0.5rem;
    background: #fff7f1;
    border: 1px solid #ffd1bf;
    color: #c2461f;
    padding: 0.6rem 0.8rem;
    border-radius: 10px;
    font-weight: 700;
    text-align: center;
}
</style>
