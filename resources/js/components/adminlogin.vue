<template>
    <div class="login-page">
        <div class="login-card">
            <button class="back-btn" @click="handleBack" :disabled="isLoading">
                ←
            </button>

            <div class="login-brand">
                <div class="brand-logo">CT</div>
                <div class="brand-text">
                    <h1>Chikin Tayo Admin</h1>
                    <p>Secure access to your store dashboard.</p>
                </div>
            </div>

            <div v-if="showForceModal" class="security-banner">
                First time login? Change your password!
            </div>

            <form class="login-form" @submit.prevent="handleLogin">
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
                    <input
                        id="password"
                        v-model="password"
                        type="password"
                        name="password"
                        autocomplete="current-password"
                        placeholder="Enter password"
                        required
                    />
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

                    <div class="cookie-debug">
                        <button type="button" class="cookie-debug-toggle" @click="showCookieDebug = !showCookieDebug">{{ showCookieDebug ? 'Hide' : 'Show' }} Cookie Info</button>
                        <div v-if="showCookieDebug" class="cookie-debug-box">
                            <p><strong>document.cookie</strong>: {{ cookieString }}</p>
                            <p><strong>XSRF-TOKEN</strong>: {{ cookieMap['XSRF-TOKEN'] || '(missing)' }}</p>
                            <p><strong>laravel_session</strong>: {{ cookieMap['laravel_session'] || '(missing)' }}</p>
                        </div>
                    </div>

            <p class="login-hint">
                For demo only. Real authentication will be connected soon.
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
const isLoading = ref(false);
const showOverlay = ref(false);
const overlayText = ref("Loading Admin Panel...");
const errorMsg = ref("");
const showForceModal = ref(false);
const pendingRedirectPath = ref("/admin-panel");
const loggedInUsername = ref("");
const defaultPassword = ref("");

const logoImg = new URL("../assets/chikinlogo.png", import.meta.url).href;
const showCookieDebug = ref(false)
const cookieString = ref('')
const cookieMap = ref({})

function parseCookies() {
    try {
        cookieString.value = document.cookie || ''
        const map = {}
        cookieString.value.split(';').map(s => s.trim()).forEach(pair => {
            if (!pair) return
            const idx = pair.indexOf('=')
            if (idx === -1) return
            const k = pair.substring(0, idx).trim()
            const v = decodeURIComponent(pair.substring(idx + 1))
            map[k] = v
        })
        cookieMap.value = map
    } catch (e) {
        cookieString.value = ''
        cookieMap.value = {}
    }
}

watchEffect(() => {
    if (showCookieDebug.value) parseCookies()
})

async function handleLogin() {
    if (isLoading.value) return;
    errorMsg.value = "";
    isLoading.value = true;

    try {
        // Ensure the XSRF cookie is set for stateful authentication
        try {
            await axios.get("/sanctum/csrf-cookie", { withCredentials: true });
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

            // Determine redirect based on user role AND department
            const redirectPath = resolveRedirectPath(res.data.user?.role, res.data.user?.department);

            if (res.data.user?.must_change_password) {
                pendingRedirectPath.value = redirectPath;
                loggedInUsername.value = res.data.user?.username || username.value;
                // Use the exact password the user just submitted as the current/default password
                defaultPassword.value = password.value;
                showForceModal.value = true;
                return;
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
        try {
            console.warn('[LOGIN] error:', e && e.response ? {
                status: e.response.status,
                headers: e.response.headers,
                data: e.response.data,
            } : e)
        } catch (ee) {}
        errorMsg.value = "Invalid username or password.";
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
        router.push("/");
    }, 2000);
}

function resolveRedirectPath(role, department) {
    const r = (role || '').toString().trim().toLowerCase();
    const d = (department || '').toString().trim().toLowerCase();

    // If department explicitly indicates inventory/finance/etc, prefer that
    if (d.includes('inventory')) return '/manager/inventory'
    if (d.includes('finance')) return '/manager/finance'
    if (d.includes('logistics')) return '/manager/logistics'
    if (d.includes('hr')) return '/manager/hr'

    // Branch-level manager explicit values (fallback)
    if (r === 'branch_manager' || r === 'branch manager' || r === 'branch-manager') return '/manager-panel';

    // Manager role string may be like 'Manager Inventory' or 'manager_inventory'
    if (r.includes('manager')) {
        // try to detect department in the role string after the word 'manager'
        const after = r.replace(/manager[_\- ]*/, '')
        if (after.includes('inventory')) return '/manager/inventory'
        if (after.includes('finance')) return '/manager/finance'
        if (after.includes('logistics')) return '/manager/logistics'
        if (after.includes('hr')) return '/manager/hr'
        // fallback to manager panel
        return '/manager-panel'
    }
    if (role === "STAFF") {
        const dept = (department || '').toLowerCase();
        if (dept === "inventory") return "/staff/inventory";
        if (dept === "cashier") return "/staff/cashier";
        if (dept === "finance") return "/staff/finance";
        // Add more departments as needed
        return "/staff-panel";
    }
    if (role === "HR") return "/hr-panel";
    if (role === "OWNER") return "/owner-panel";
    if (role === "ADMIN") return "/admin-panel";
    return "/admin-panel";
}

function handleForceCompleted() {
    showForceModal.value = false;
    overlayText.value = "Loading panel...";
    setTimeout(() => {
        showOverlay.value = true;
        setTimeout(() => {
            try { sessionStorage.setItem('skipRouteOverlay', '1'); } catch (e) {}
            router.push(pendingRedirectPath.value || "/admin-panel");
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
