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
import { onMounted, ref } from "vue";
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
const defaultPassword = "ChikinTayo_2526";

const logoImg = new URL("../assets/chikinlogo.png", import.meta.url).href;

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

        if (res.data.ok) {
            overlayText.value = "Loading panel...";

            // Determine redirect based on user role
            const redirectPath = resolveRedirectPath(res.data.user?.role);

            if (res.data.user?.must_change_password) {
                pendingRedirectPath.value = redirectPath;
                loggedInUsername.value = res.data.user?.username || username.value;
                showForceModal.value = true;
                return;
            }

            setTimeout(() => {
                showOverlay.value = true;
                setTimeout(() => {
                    router.push(redirectPath);
                }, 600);
            }, 400);
        } else {
            errorMsg.value = res.data.message || "Login failed.";
        }
    } catch (e) {
        errorMsg.value = "Invalid username or password.";
    } finally {
        isLoading.value = false;
    }
}

function handleBack() {
    if (isLoading.value) return;
    isLoading.value = true;
    overlayText.value = "Loading home page...";

    setTimeout(() => {
        showOverlay.value = true;
        setTimeout(() => {
            router.push("/");
        }, 600);
    }, 400);
}

function resolveRedirectPath(role) {
    if (role === "BRANCH_MANAGER") return "/manager-panel";
    if (role === "STAFF") return "/staff-panel";
    if (role === "HR") return "/hr-panel";
    return "/admin-panel";
}

function handleForceCompleted() {
    showForceModal.value = false;
    overlayText.value = "Loading panel...";
    setTimeout(() => {
        showOverlay.value = true;
        setTimeout(() => {
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

onMounted(async () => {
    try {
        const res = await axios.get("/api/me", { withCredentials: true });
        if (res.data.ok && res.data.user?.must_change_password) {
            pendingRedirectPath.value = resolveRedirectPath(res.data.user?.role);
            loggedInUsername.value = res.data.user?.username || "";
            showForceModal.value = true;
        }
    } catch (e) {
        // ignore if unauthenticated
    }
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
