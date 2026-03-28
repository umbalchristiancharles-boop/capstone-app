<template>
  <div class="custom-panel-container">
    <div class="card">
        <h2 class="panel-title">Your Custom Account</h2>
        <p v-if="!modules || modules.length === 0" class="empty-message">No modules assigned. Contact admin to enable modules.</p>

        <div v-else class="modules-grid">
          <div v-for="m in modules" :key="m" class="module-tile" @click="goToModule(m)" role="button" tabindex="0" @keydown.enter="goToModule(m)">
            <div class="module-icon">{{ (prettyName(m) || '').charAt(0) }}</div>
            <div class="module-info">
              <div class="module-name">{{ prettyName(m) }}</div>
              <div class="module-desc">Open {{ prettyName(m) }} panel</div>
            </div>
          </div>
        </div>

        <div class="actions">
          <button @click="logout" class="btn btn-ghost" :disabled="isLoggingOut">{{ isLoggingOut ? 'Logging out...' : 'Logout' }}</button>
          <button @click="goToPanelDefault" class="btn btn-secondary">Go to Default Panel</button>
        </div>
      </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const modules = ref([]);
const isLoggingOut = ref(false);

onMounted(() => {
  try {
    const user = JSON.parse(localStorage.getItem('user') || 'null');
    if (user && Array.isArray(user.permissions?.modules)) {
      modules.value = user.permissions.modules.map(m => (m || '').toLowerCase());
    }
  } catch (e) {
    modules.value = [];
  }

  // If localStorage had no permissions, try fetching the authoritative /api/me
  if (!modules.value.length) {
    axios.get('/api/me', { withCredentials: true }).then(res => {
      const u = res.data.user || res.data.user?.user || null;
      // support different shapes returned by endpoints
      const perms = u?.permissions || (res.data.user && res.data.user.permissions) || null;
      if (perms && Array.isArray(perms.modules)) {
        modules.value = perms.modules.map(m => (m || '').toLowerCase());
      }
      // FIX: Store token for sanctum API auth (fixes 401 on manager/logistics APIs from custom panel)
      const token = res.data.token || res.data.access_token || null;
      if (token) {
        localStorage.setItem('token', token);
        // Ensure axios global header is set
        if (typeof axios !== 'undefined') {
          axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        }
        console.log('[CustomPanel] Token stored for API auth');
      }
    }).catch((err) => {
      console.warn('[CustomPanel] /api/me failed:', err.response?.status);
      // ignore - leave modules empty
    });
  }
});

const prettyName = (m) => {
  if (!m) return 'Unknown';
  return m.charAt(0).toUpperCase() + m.slice(1);
}

const goToModule = (m) => {
  // Map modules to existing panels/routes and include a query marker so
  // the target panel can show a "Back to Custom Panel" control.
  const q = { query: { from: 'custom-panel' } }
  const mod = (m || '').toLowerCase();
  if (mod === 'admin') return router.push({ path: '/admin-panel', ...q });
  if (mod === 'finance') return router.push({ path: '/manager/finance', ...q });
  if (mod === 'procurement') return router.push({ path: '/manager/procurement', ...q });
  if (mod === 'logistics') return router.push({ path: '/manager/logistics', ...q });
  if (mod === 'inventory') return router.push({ path: '/staff/inventory', ...q });
  if (mod === 'hr') return router.push({ path: '/manager/hr', ...q });
  if (mod === 'kitchen') return router.push({ path: '/staff/kitchen', ...q });
  if (mod === 'cashier') return router.push({ path: '/staff/cashier', ...q });
  if (mod === 'supplier') return router.push({ path: '/supplier-panel', ...q });
  // Fallback
  return router.push({ path: '/admin-panel', ...q });
}

const goToPanelDefault = () => {
  router.push('/admin-panel');
}

const logout = async () => {
  if (isLoggingOut.value) return;
  const ok = await (window.swalConfirm ? window.swalConfirm('This will end your current session for Chikin Tayo.', 'Confirm logout') : Promise.resolve(true));
  if (!ok) return;
  isLoggingOut.value = true;
  try { await axios.post('/api/logout', {}, { withCredentials: true }); } catch (e) {}
  try { localStorage.clear(); sessionStorage.clear(); } catch (e) {}
  try {
    await router.replace('/staff-landing');
  } catch (e) {
    window.location.replace('/staff-landing');
  }
}
</script>

<style scoped>
.custom-panel-container { display:flex; justify-content:center; align-items:center; min-height:80vh; padding:40px; background: #F8FAFC }
.card { background:white; padding:28px; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,0.12); max-width:720px; width:100%; text-align:left; border:1px solid #E5E7EB }
.panel-title { font-size:22px; font-weight:700; color:#0066FF; margin:0 0 8px 0; text-align:center }
.empty-message { color:#6B7280; text-align:center; margin:12px 0 }

.modules-grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap:16px; margin:18px 0 }
.module-tile { display:flex; gap:12px; align-items:center; padding:12px; background:#fff; border-radius:10px; border:1px solid #E5E7EB; cursor:pointer; box-shadow:0 6px 14px rgba(0,0,0,0.06); transition:transform .15s ease, box-shadow .15s ease }
.module-tile:hover { transform:translateY(-4px); box-shadow:0 10px 30px rgba(0,0,0,0.12) }
.module-tile:focus { outline: none; box-shadow: 0 0 0 3px rgba(0,102,255,0.12) }

.module-icon { width:48px; height:48px; border-radius:10px; background: linear-gradient(135deg,#ff9f43,#ff6b3a); display:flex; align-items:center; justify-content:center; color:white; font-weight:700; font-size:18px }
.module-info { display:flex; flex-direction:column }
.module-name { font-weight:600; color:#1F2937 }
.module-desc { font-size:12px; color:#6B7280; margin-top:4px }

.actions { margin-top:18px; display:flex; justify-content:center }
.actions { gap:10px }
.btn { font-weight:600; border-radius:8px; padding:10px 16px; cursor:pointer; border:none }
.btn-ghost { background:#fff; color:#1F2937; border:1px solid #E5E7EB }
.btn-ghost:hover { background:#F3F4F6 }
.btn-secondary { background:#0066FF; color:white; border:none; border-radius:8px; padding:10px 16px; cursor:pointer; font-weight:600 }
.btn-secondary:hover { background:#3B82F6 }

@media (max-width:420px) {
  .card { padding:18px }
  .module-icon { width:40px; height:40px }
}
</style>
