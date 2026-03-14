<template>
	<div>
		<nav class="customer-top-nav" aria-label="Top navigation">
		  <button class="nav-btn" @click="goToStaffLanding" aria-label="Go to Staff Landing">Staff Landing</button>
		</nav>
		<IndexComponent />
	</div>
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue'
import IndexComponent from './index.vue'
import { useRouter } from 'vue-router'

const router = useRouter()

function goToStaffLanding() {
	router.push('/staff-landing')
}

onMounted(() => {
	try { sessionStorage.setItem('hideAdminLogin', '1') } catch (e) {}
})

onUnmounted(() => {
	try { sessionStorage.removeItem('hideAdminLogin') } catch (e) {}
})
</script>

<style scoped>
.customer-top-nav {
	position:fixed;
	top:12px;
	right:12px;
	z-index:60;
	display:flex;
	align-items:center;
}
.nav-btn {
	background: #ffffff22; /* subtle translucent */
	backdrop-filter: blur(6px);
	color: #fff;
	border: 1px solid rgba(255,255,255,0.12);
	padding: 8px 12px;
	border-radius: 8px;
	font-weight:600;
	cursor: pointer;
	box-shadow: 0 4px 14px rgba(0,0,0,0.08);
}
.nav-btn:hover { transform: translateY(-1px); box-shadow:0 6px 18px rgba(0,0,0,0.12); }
.nav-btn:active { transform: translateY(0); }

/* Make the button more visible on light sections */
@media (prefers-color-scheme: light) {
	.nav-btn { background: #f59e42; border-color: rgba(0,0,0,0.06); }
}
</style>
