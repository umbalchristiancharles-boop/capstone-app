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

            <form class="login-form" @submit.prevent="handleLogin">
                <div class="field-group">
                    <label for="username">Username</label>
                    <input
                        id="username"
                        v-model="username"
                        type="text"
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
    </div>
</template>

<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";
import "../css/adminlogin.css";

const router = useRouter();

const username = ref("");
const password = ref("");
const isLoading = ref(false);
const showOverlay = ref(false);
const overlayText = ref("Loading Admin Panel...");
const errorMsg = ref("");

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
            let redirectPath = "/admin-panel";
            if (res.data.user && res.data.user.role === "BRANCH_MANAGER") {
                redirectPath = "/manager-panel";
            } else if (res.data.user && res.data.user.role === "STAFF") {
                redirectPath = "/staff-panel";
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
</script>
