<template>
  <div v-if="visible">
    <button v-if="!open" @click="open = true" class="msg-fab" aria-label="Messages">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4-.9L3 20l1.1-3.3A7.972 7.972 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
    </button>

    <div v-if="open" class="msg-overlay" @click.self="open = false">
      <div class="msg-modal">
        <div class="msg-left">
          <div class="msg-left-header">Branch Users</div>
          <div class="msg-users">
            <div v-for="u in users" :key="u.id" :class="['msg-user', selected && selected.id === u.id ? 'active' : '']" @click="selectUser(u)">
              <div class="msg-user-name">{{ u.name }}</div>
              <div class="msg-user-role">{{ roleLabel(u.role) }}</div>
            </div>
          </div>
        </div>
        <div class="msg-right">
          <div class="msg-right-header">
            <div>{{ selected ? ('Conversation with ' + selected.name + ' (' + roleLabel(selected.role) + ')') : 'Select a user' }}</div>
            <button class="close-btn" @click="open = false">Close</button>
          </div>

          <div class="msg-messages" ref="messagesPane">
            <div v-if="!selected" class="msg-empty">Choose a user to start</div>
            <div v-else class="msg-thread">
              <div v-for="m in messages" :key="m.id" :class="['msg-bubble', m.from_user_id === meId ? 'mine' : 'theirs']">
                  <div v-if="m.from_user && m.from_user.name && m.from_user_id !== meId" class="msg-sender">{{ m.from_user.name }} ({{ roleLabel(m.from_user.role) }})</div>
                <div class="msg-body" v-html="escapeHtml(m.body)"></div>
                <div class="msg-ts">{{ formatDate(m.created_at) }}</div>
              </div>
            </div>
          </div>

          <div class="msg-composer">
            <textarea v-model="body" placeholder="Write a message..."></textarea>
            <div class="composer-actions">
              <button @click="send" :disabled="!selected || sending || !body.trim()">Send</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'MessageWidget',
  data() {
    return {
      open: false,
      users: [],
      selected: null,
      messages: [],
      body: '',
      sending: false,
      meId: null,
      clientHasUser: false,
      hasSession: false,
        csrfRetriedOnce: false,
        fetchUsersInProgress: false,
        stoppedUnauthenticated: false,
      pollTimer: null,
    }
  },
  computed: {
    visible() {
      try {
        const p = this.$route?.path || window.location.pathname || '/'
        const user = JSON.parse(localStorage.getItem('user') || 'null')
        if (!user || !user.role) return false

        const role = String(user.role).toUpperCase()
        const allowedRoles = ['STAFF', 'MANAGER', 'HR', 'BRANCH_MANAGER', 'BRANCH MANAGER', 'BRANCH-MANAGER']
        let isAllowedRole = allowedRoles.includes(role) || role.includes('STAFF') || role.includes('HR')

        // CUSTOM accounts: check if they have 'hr' module permission
        if (role === 'CUSTOM' && !isAllowedRole) {
          try {
            const modules = Array.isArray(user.permissions?.modules) ? user.permissions.modules.map(m => (m || '').toLowerCase()) : []
            isAllowedRole = modules.includes('hr')
          } catch (e) {
            isAllowedRole = false
          }
        }

        const isPanelPath = p.startsWith('/staff') ||
          p.startsWith('/manager') ||
          p.startsWith('/hr') ||
          p.startsWith('/custom-panel') ||
          p.includes('staff-panel') ||
          p.includes('manager-panel') ||
          p.includes('hr-panel') ||
          p.includes('custom-panel')
        return isAllowedRole && isPanelPath && this.hasSession
      } catch (e) {
        return false
      }
    }
  },
  watch: {
    open(isOpen) {
      if (isOpen) {
        this.fetchUsers()
        this.startPolling()
      } else {
        this.stopPolling()
      }
    },
    '$route.path'() {
      this.bootstrapAuthState()
    }
  },
  async mounted() {
      await this.bootstrapAuthState()
      window.addEventListener('storage', this.onStorageChange)
      window.addEventListener('focus', this.onWindowFocus)
  },
  beforeUnmount() {
      this.stopPolling()
      window.removeEventListener('storage', this.onStorageChange)
      window.removeEventListener('focus', this.onWindowFocus)
    },

  methods: {
    async bootstrapAuthState(){
      let user = null
      try {
        user = JSON.parse(localStorage.getItem('user') || 'null')
      } catch (e) {
        user = null
      }

      this.clientHasUser = !!(user && user.id)
      this.meId = this.clientHasUser ? user.id : null

      if (!this.clientHasUser) {
        this.hasSession = false
        this.stopPolling()
        return
      }

      try {
        await axios.get('/api/me')
        const becameAuthenticated = this.hasSession !== true
        this.hasSession = true
        this.stoppedUnauthenticated = false

        if (becameAuthenticated || this.open) {
          this.fetchUsers()
        }
      } catch (err) {
        this.hasSession = false
        this.stopPolling()
      }
    },
    onStorageChange(){
      this.bootstrapAuthState()
    },
    onWindowFocus(){
      this.bootstrapAuthState()
    },
    roleLabel(role){
      const value = String(role || '').trim()
      if (!value) return 'User'
      return value.replace(/_/g, ' ')
    },
    startPolling(){
      this.stopPolling()
      this.pollTimer = setInterval(() => {
        if (!this.hasSession) return
        this.fetchUsers()
        if (this.selected && this.selected.id) {
          this.loadConversation(this.selected.id)
        }
      }, 3000)
    },
    stopPolling(){
      if (this.pollTimer) {
        clearInterval(this.pollTimer)
        this.pollTimer = null
      }
    },
    ensureCsrfOnce(){
      if (this.csrfRetriedOnce) return Promise.resolve()
      return axios.get('/sanctum/csrf-cookie').then(() => { this.csrfRetriedOnce = true }).catch(() => {})
    },
    getCookie(name){
      try {
        const v = document.cookie.split('; ').find(row => row.startsWith(name + '='))
        if (!v) return null
        return decodeURIComponent(v.split('=')[1])
      } catch (e) { return null }
    },
    escapeHtml(s){ return (s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;') },
    formatDate(s){ try { return new Date(s).toLocaleString() } catch(e){ return s } },
    fetchUsers(){
      if (this.fetchUsersInProgress || this.stoppedUnauthenticated) return
      this.fetchUsersInProgress = true

      axios.get('/api/hr/messages/users').then(resp => {
        this.users = resp.data.users || []

        if (this.selected && this.users.length) {
          const nextSelected = this.users.find(u => String(u.id) === String(this.selected.id)) || null
          this.selected = nextSelected
        }

        if (!this.selected && this.users.length) {
          this.selectUser(this.users[0])
        }
      }).catch(err => {
        const status = err && err.response && err.response.status
        if (status === 401) {
          console.error('MessageWidget fetchUsers 401 - marking unauthenticated')
          this.hasSession = false
          this.stoppedUnauthenticated = true
          this.users = []
          return
        }
      }).finally(() => { this.fetchUsersInProgress = false })
    },
    selectUser(u){
      this.selected = u
      this.loadConversation(u.id)
    },
    loadConversation(userId){
      axios.get(`/api/hr/messages/conversation/${userId}`).then(resp => {
        this.messages = resp.data.messages || []
        this.$nextTick(() => {
          try { this.$refs.messagesPane.scrollTop = this.$refs.messagesPane.scrollHeight } catch(e) {}
        })
      }).catch(err => {
        if (err && err.response && err.response.status === 403) {
          alert('Cannot view conversation: not in same branch')
        }
        if (err && err.response && err.response.status === 401) {
          console.warn('MessageWidget loadConversation 401 - session invalid')
          this.hasSession = false
          this.messages = []
          return
        }
        if (err && err.response && err.response.status === 404) {
          this.messages = []
        }
      })
    },
    send(){
      if (!this.selected || !this.body.trim()) return
      this.sending = true
      axios.post('/api/hr/messages/send', { to_user_id: this.selected.id, body: this.body }).then(resp => {
        this.body = ''
        // append and reload conversation
        this.loadConversation(this.selected.id)
      }).catch(err => {
        const status = err && err.response && err.response.status
        if (status === 401) {
          alert('Session expired. Please login again.')
          this.hasSession = false
          try { this.$router.push('/staff-landing') } catch(e) { window.location.href = '/staff-landing' }
        } else {
          alert('Send failed')
        }
      }).finally(() => { this.sending = false })
    }
  }
}
</script>

<style scoped>
/* Improve message widget layout and contrast for logistics panel */
.msg-fab{position:fixed;right:18px;bottom:18px;z-index:10010;width:56px;height:56px;border-radius:999px;background:#2563eb;color:#fff;border:none;display:flex;align-items:center;justify-content:center;box-shadow:0 8px 22px rgba(37,99,235,0.22);cursor:pointer}
.msg-overlay{position:fixed;inset:0;background:rgba(0,0,0,0.45);display:flex;align-items:center;justify-content:center;z-index:10000}
.msg-modal{width:980px;max-width:98%;height:78vh;background:#ffffff;border-radius:10px;display:flex;overflow:hidden;box-shadow:0 10px 40px rgba(2,6,23,0.2)}
.msg-left{width:280px;min-width:220px;border-right:1px solid #eef2f7;display:flex;flex-direction:column;background:#fbfeff}
.msg-left-header{padding:14px;font-weight:700;border-bottom:1px solid #f3f6f9;color:#0f172a}
.msg-users{overflow:auto;padding:8px}
.msg-user{padding:10px;border-radius:8px;margin-bottom:8px;cursor:pointer;border:1px solid transparent}
.msg-user.active{background:#eef8ff;border-color:#dbeffd}
.msg-user-name{font-weight:700;color:#0f172a}
.msg-user-role{font-size:12px;color:#64748b;margin-top:4px}
.msg-right{flex:1;display:flex;flex-direction:column;background:linear-gradient(180deg,#ffffff 0%,#fbfdff 100%)}
.msg-right-header{display:flex;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #f3f6f9}
.close-btn{background:#ef4444;color:#fff;border:none;padding:6px 10px;border-radius:6px}
.msg-messages{flex:1;padding:16px;overflow:auto;background:transparent}
.msg-thread{display:flex;flex-direction:column;gap:10px}
.msg-bubble{max-width:78%;padding:12px;border-radius:10px;display:block;word-break:break-word;border:1px solid rgba(15,23,42,0.04);box-shadow:0 2px 6px rgba(2,6,23,0.04)}
.msg-bubble.mine{background:#dcfce7;align-self:flex-end;margin-left:auto}
.msg-bubble.theirs{background:#ffffff;align-self:flex-start;margin-right:auto}
.msg-sender{font-size:12px;color:#1f2937;font-weight:700;margin-bottom:6px}
.msg-ts{font-size:11px;color:#6b7280;margin-top:8px;text-align:right}
.msg-composer{padding:12px;border-top:1px solid #eef2f7;background:#ffffff}
.msg-composer textarea{width:100%;height:80px;padding:10px;border:1px solid #e6eef7;border-radius:8px;resize:vertical}
.composer-actions{display:flex;justify-content:flex-end;margin-top:8px}
.composer-actions button{background:#2563eb;color:#fff;border:none;padding:8px 14px;border-radius:8px}
.msg-empty{color:#6b7280;padding:20px}

/* Ensure messages wrap long words and code-like content */
.msg-body{white-space:pre-wrap;word-wrap:break-word;overflow-wrap:break-word}

</style>
