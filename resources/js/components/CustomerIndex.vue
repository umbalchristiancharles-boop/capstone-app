<template>
	<div>
		<nav class="customer-top-nav" aria-label="Top navigation">
		  <button class="nav-btn" @click="goToStaffLanding" aria-label="Go to Staff Landing"><span class="sr-only">Staff Landing</span></button>
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
	background: transparent;
	backdrop-filter: none;
	color: transparent;
	border: none;
	padding: 0;
	width: 130px;
	height: 48px;
	border-radius: 0;
	font-weight: 400;
	cursor: pointer;
	box-shadow: none;
	transition: none;
}
.nav-btn:hover,
.nav-btn:active,
.nav-btn:focus,
.nav-btn:focus-visible {
	background: transparent;
	border: none;
	box-shadow: none;
	outline: none;
	color: transparent;
	transform: none;
	filter: none;
}

.sr-only {
	position: absolute;
	width: 1px;
	height: 1px;
	padding: 0;
	margin: -1px;
	overflow: hidden;
	clip: rect(0, 0, 0, 0);
	white-space: nowrap;
	border: 0;
}
</style>
