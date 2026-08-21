<template>
  <div v-if="visible">
    <button v-if="!open" @click="open = true" class="msg-fab" aria-label="Messages">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4-.9L3 20l1.1-3.3A7.972 7.972 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
      <span v-if="unreadCount > 0" class="msg-unread-badge">{{ unreadCount > 99 ? '99+' : unreadCount }}</span>
    </button>

    <div v-if="open" class="msg-overlay" @click.self="open = false">
      <div class="msg-modal">
        <div class="msg-left">
          <div class="msg-left-header">Branch Users</div>
          <div class="msg-users">
            <div v-for="u in users" :key="u.id" :class="['msg-user', selected && selected.id === u.id ? 'active' : '', u.unread_count > 0 ? 'has-unread' : '']" @click="selectUser(u)">
              <div class="msg-user-avatar" v-if="u.avatar"><img :src="u.avatar" alt="" /></div>
              <div class="msg-user-avatar" v-else><span>{{ (u.name||'').split(' ').map(n=>n[0]).slice(0,2).join('').toUpperCase() }}</span></div>
              <div class="msg-user-meta">
                <div class="msg-user-name">{{ u.name }}</div>
                <div class="msg-user-role">{{ roleLabel(u.role) }}</div>
              </div>
              <span v-if="u.unread_count > 0" class="msg-user-unread">{{ u.unread_count > 99 ? '99+' : u.unread_count }}</span>
            </div>
          </div>
        </div>
        <div class="msg-right">
          <div class="msg-right-header">
            <div class="msg-right-title">
              <div class="msg-right-avatar" v-if="selected && selected.avatar"><img :src="selected.avatar" alt="" /></div>
              <div class="msg-right-avatar" v-else-if="selected"><span>{{ (selected.name||'').split(' ').map(n=>n[0]).slice(0,2).join('').toUpperCase() }}</span></div>
              <div class="msg-right-text">{{ selected ? ('Conversation with ' + selected.name + ' (' + roleLabel(selected.role) + ')') : 'Select a user' }}</div>
            </div>
            <div class="msg-header-actions">
              <button v-if="canSubmitEmployeeReport && isHrManager(selected)" class="report-btn" @click="reportOpen = !reportOpen">Employee report</button>
              <button class="close-btn" @click="open = false">Close</button>
            </div>
          </div>

          <div class="msg-messages" ref="messagesPane">
            <div v-if="!selected" class="msg-empty">Choose a user to start</div>
            <div v-else class="msg-thread">
              <div v-for="m in messages" :key="m.id" :class="['msg-row', m.from_user_id === meId ? 'row-mine' : 'row-theirs']">
                <div v-if="m.from_user_id !== meId" class="msg-avatar-small">
                  <img v-if="m.from_user && m.from_user.avatar" :src="m.from_user.avatar" />
                  <div v-else class="avatar-initial">{{ (m.from_user?.name||'').split(' ').map(n=>n[0]).slice(0,2).join('').toUpperCase() }}</div>
                </div>
                <div v-if="isEmployeeReport(m)" class="employee-report-card">
                  <div class="employee-report-heading">
                    <div class="employee-report-mark">REPORT</div>
                    <div>
                      <div class="employee-report-title">Employee Report</div>
                      <div class="employee-report-subtitle">Confidential HR submission</div>
                    </div>
                  </div>
                  <div class="employee-report-divider"></div>
                  <div class="employee-report-field">
                    <span>Employee</span>
                    <strong>{{ employeeReportParts(m.body).employee }}</strong>
                  </div>
                  <div class="employee-report-field employee-report-details">
                    <span>Report details</span>
                    <p>{{ employeeReportParts(m.body).details }}</p>
                  </div>
                  <div class="employee-report-footer">
                    <span>Submitted by {{ m.from_user?.name || 'User' }}</span>
                    <span>{{ formatDate(m.created_at) }}<br v-if="m.from_user_id === meId"><em v-if="m.from_user_id === meId">{{ messageStatus(m) }}</em></span>
                  </div>
                </div>
                <div v-else :class="['msg-bubble', m.from_user_id === meId ? 'mine' : 'theirs']">
                  <div v-if="m.from_user && m.from_user.name && m.from_user_id !== meId" class="msg-sender">{{ m.from_user.name }} <span class="msg-sender-role">({{ roleLabel(m.from_user.role) }})</span></div>
                  <div v-if="m.body" class="msg-body" v-html="escapeHtml(m.body)"></div>
                  <img v-if="isImageAttachment(m)" class="msg-attachment-image" :src="m.attachment_url" :alt="m.attachment_name" @click="openAttachment(m)" />
                  <a v-else-if="m.attachment_url" class="msg-attachment-link" :href="m.attachment_url" target="_blank" rel="noopener">View {{ m.attachment_name }}</a>
                  <div class="msg-ts">{{ formatDate(m.created_at) }}</div>
                  <div v-if="m.from_user_id === meId" class="msg-status">{{ messageStatus(m) }}</div>
                </div>
              </div>
            </div>
          </div>

          <div v-if="reportOpen && canSubmitEmployeeReport && isHrManager(selected)" class="employee-report-form">
            <div class="report-form-title">Send employee report</div>
            <div class="report-employee-name">Employee: <strong>{{ currentUserName }}</strong></div>
            <textarea v-model="reportBody" placeholder="Describe the report..."></textarea>
            <div class="report-form-actions">
              <button class="cancel-report-btn" @click="closeReportForm">Cancel</button>
              <button class="report-submit-btn" @click="sendEmployeeReport" :disabled="reportSending || !reportBody.trim()">Send report</button>
            </div>
          </div>

          <div class="msg-composer">
            <textarea v-model="body" placeholder="Write a message..."></textarea>
            <div class="composer-actions">
              <input ref="attachmentInput" class="attachment-input" type="file" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt" @change="selectAttachment" />
              <button class="attach-btn" @click="$refs.attachmentInput.click()" title="Attach a document or picture">Attach</button>
              <span v-if="attachment" class="attachment-selected">{{ attachment.name }}</span>
              <button @click="send" :disabled="!selected || sending || (!body.trim() && !attachment)">Send</button>
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
      unreadCount: 0,
      selected: null,
      messages: [],
      body: '',
      attachment: null,
      sending: false,
      reportOpen: false,
      reportBody: '',
      reportSending: false,
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
    currentUserName() {
      try {
        const user = JSON.parse(localStorage.getItem('user') || 'null')
        return user?.full_name || user?.name || user?.username || (user?.id ? `User #${user.id}` : 'Current user')
      } catch (e) {
        return 'Current user'
      }
    },
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
          p.startsWith('/inventory') ||
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
        this.startPolling()
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
    isHrManager(user){
      return !!user && String(user.role || '').toUpperCase() === 'MANAGER' && String(user.department || '').toUpperCase() === 'HR'
    },
    isEmployeeReport(message){
      return String(message?.body || '').startsWith('EMPLOYEE REPORT\n')
    },
    employeeReportParts(body){
      const lines = String(body || '').split('\n')
      const employeeLine = lines.find(line => line.startsWith('Employee:')) || 'Employee:'
      return {
        employee: employeeLine.replace(/^Employee:\s*/, '').trim() || 'Not specified',
        details: lines.slice(lines.indexOf(employeeLine) + 2).join('\n').trim() || 'No details provided',
      }
    },
    canSubmitEmployeeReport(){
      try {
        const user = JSON.parse(localStorage.getItem('user') || 'null')
        if (!user) return false
        const role = String(user.role || '').trim().toUpperCase()
        const isHrManager = role === 'MANAGER' && String(user.department || '').trim().toUpperCase() === 'HR'
        return !['OWNER', 'ADMIN', 'SUPER_ADMIN', 'SUPERADMIN'].includes(role) && !isHrManager
      } catch (e) {
        return false
      }
    },
    startPolling(){
      this.stopPolling()
      this.pollTimer = setInterval(() => {
        if (!this.hasSession) return
        this.fetchUsers()
        if (this.open && this.selected && this.selected.id) {
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
    selectAttachment(event){
      this.attachment = event.target.files[0] || null
    },
    isImageAttachment(message){
      return !!message.attachment_url && String(message.attachment_mime || '').startsWith('image/')
    },
    openAttachment(message){
      window.open(message.attachment_url, '_blank', 'noopener')
    },
    messageStatus(message){
      return message.read_at ? 'Read' : 'Delivered'
    },
    fetchUsers(){
      if (this.fetchUsersInProgress || this.stoppedUnauthenticated) return
      this.fetchUsersInProgress = true

      axios.get('/api/hr/messages/users').then(resp => {
        this.users = resp.data.users || []
        this.unreadCount = Number(resp.data.unread_count || 0)

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
      this.closeReportForm()
      this.loadConversation(u.id)
    },
    closeReportForm(){
      this.reportOpen = false
      this.reportBody = ''
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
      if (!this.selected || (!this.body.trim() && !this.attachment)) return
      this.sending = true
      const form = new FormData()
      form.append('to_user_id', this.selected.id)
      form.append('body', this.body)
      if (this.attachment) form.append('attachment', this.attachment)
      axios.post('/api/hr/messages/send', form).then(resp => {
        this.body = ''
        this.attachment = null
        if (this.$refs.attachmentInput) this.$refs.attachmentInput.value = ''
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
    },
    sendEmployeeReport(){
      if (!this.canSubmitEmployeeReport || !this.isHrManager(this.selected) || !this.reportBody.trim()) return
      this.reportSending = true
      axios.post('/api/hr/messages/send-employee-report', {
        to_user_id: this.selected.id,
        report_body: this.reportBody,
      }).then(() => {
        this.closeReportForm()
        this.loadConversation(this.selected.id)
      }).catch(err => {
        if (err && err.response && err.response.status === 401) {
          alert('Session expired. Please login again.')
          this.hasSession = false
        } else {
          alert((err && err.response && err.response.data && err.response.data.error) || 'Could not send employee report')
        }
      }).finally(() => { this.reportSending = false })
    }
  }
}
</script>

<style scoped>
/* Improve message widget layout and contrast for logistics panel */
.msg-fab{position:fixed;right:18px;bottom:18px;z-index:10010;width:56px;height:56px;border-radius:999px;background:#0f766e;color:#fff;border:none;display:flex;align-items:center;justify-content:center;box-shadow:0 8px 28px rgba(15,118,110,0.3);cursor:pointer}
.msg-unread-badge{position:absolute;top:-4px;right:-4px;min-width:21px;height:21px;padding:0 5px;border:2px solid #fff;border-radius:999px;background:#dc2626;color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:900;line-height:1;box-sizing:border-box}
.msg-overlay{position:fixed;inset:0;background:rgba(0,0,0,0.45);display:flex;align-items:center;justify-content:center;z-index:10000}
.msg-modal{width:980px;max-width:98%;height:78vh;background:linear-gradient(180deg,rgba(255,255,255,0.98),rgba(250,250,250,1));border-radius:12px;display:flex;overflow:hidden;box-shadow:0 18px 60px rgba(2,6,23,0.18)}
.msg-left{width:300px;min-width:240px;border-right:1px solid rgba(15,23,42,0.04);display:flex;flex-direction:column;background:linear-gradient(180deg, #fbfeff, #fff)}
.msg-left-header{padding:16px;font-weight:800;border-bottom:1px solid rgba(15,23,42,0.04);color:#0f172a}
.msg-users{overflow:auto;padding:10px;display:flex;flex-direction:column}
.msg-user{display:flex;gap:10px;align-items:center;padding:10px;border-radius:10px;margin-bottom:8px;cursor:pointer;border:1px solid transparent;transition:background .12s, transform .08s}
.msg-user:hover{transform:translateY(-1px)}
.msg-user.active{background:linear-gradient(90deg, rgba(255,106,61,0.08), rgba(251,191,36,0.04));border-color:rgba(255,170,120,0.08)}
.msg-user.has-unread{background:#ecfeff;border-color:#67e8f9;font-weight:800}
.msg-user-unread{min-width:21px;height:21px;padding:0 5px;border-radius:999px;background:#dc2626;color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:900;box-sizing:border-box}
.msg-user-avatar{width:44px;height:44px;border-radius:10px;overflow:hidden;flex:0 0 44px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#ff6a3d,#f59e0b);color:#fff;font-weight:700}
.msg-user-avatar img{width:100%;height:100%;object-fit:cover}
.msg-user-meta{flex:1;min-width:0}
.msg-user-name{font-weight:800;color:#0f172a}
.msg-user-role{font-size:12px;color:#64748b;margin-top:4px}
.msg-right{flex:1;display:flex;flex-direction:column;background:transparent}
.msg-right-header{display:flex;align-items:center;justify-content:space-between;padding:12px 18px;border-bottom:1px solid rgba(15,23,42,0.04)}
.msg-right-title{display:flex;align-items:center;gap:12px}
.msg-right-avatar{width:40px;height:40px;border-radius:999px;overflow:hidden;display:flex;align-items:center;justify-content:center;background:#f3f4f6;color:#374151;font-weight:700}
.msg-right-avatar img{width:100%;height:100%;object-fit:cover}
.msg-right-text{font-weight:800;color:#0f172a}
.msg-header-actions{display:flex;gap:8px;align-items:center}
.report-btn{background:#fff7ed;color:#c2410c;border:1px solid #fed7aa;padding:7px 10px;border-radius:8px;font-weight:700;cursor:pointer}
.close-btn{background:#ef4444;color:#fff;border:none;padding:6px 10px;border-radius:8px}
.msg-messages{flex:1;padding:18px;overflow:auto;background:transparent;display:flex;flex-direction:column;gap:12px}
.msg-thread{display:flex;flex-direction:column;gap:12px}
.msg-row{display:flex;align-items:flex-end;gap:10px}
.row-mine{justify-content:flex-end}
.row-theirs{justify-content:flex-start}
.msg-avatar-small{width:36px;height:36px;border-radius:10px;overflow:hidden;flex:0 0 36px;display:flex;align-items:center;justify-content:center;background:#f3f4f6;color:#374151;font-weight:700}
.msg-avatar-small img{width:100%;height:100%;object-fit:cover}
.avatar-initial{font-weight:700;color:#374151}
.msg-bubble{max-width:72%;padding:12px;border-radius:16px;display:block;word-break:break-word;border:1px solid rgba(15,23,42,0.04);box-shadow:0 6px 18px rgba(2,6,23,0.04)}
.msg-bubble.mine{background:#ea580c;color:#fff;align-self:flex-end;margin-left:auto;border:none}
.msg-bubble.mine .msg-body{color:#fff}
.msg-bubble.theirs{background:#f8fafc;color:#0f172a;align-self:flex-start;margin-right:auto}
.msg-sender{font-size:12px;color:#0f172a;font-weight:700;margin-bottom:6px}
.msg-sender-role{font-weight:600;color:#6b7280;font-size:11px;margin-left:6px}
.msg-ts{font-size:11px;color:rgba(15,23,42,0.45);margin-top:8px;text-align:right}
.msg-status{font-size:10px;color:rgba(15,23,42,0.55);margin-top:3px;text-align:right}
.msg-bubble.mine .msg-ts,.msg-bubble.mine .msg-status{color:rgba(255,255,255,0.9)}
.msg-composer{padding:12px;border-top:1px solid rgba(15,23,42,0.04);background:linear-gradient(180deg,#fff,#fbfdff);display:flex;gap:12px;align-items:flex-end}
.msg-composer textarea{flex:1;min-height:48px;max-height:160px;padding:10px;border:1px solid rgba(15,23,42,0.04);border-radius:12px;resize:vertical}
.composer-actions{display:flex;gap:8px;align-items:center}
.composer-actions button{background:linear-gradient(90deg,#ff6a3d,#f59e0b);color:#fff;border:none;padding:10px 16px;border-radius:10px;box-shadow:0 8px 20px rgba(255,106,61,0.12)}
.msg-empty{color:#6b7280;padding:20px}
.employee-report-card{width:min(92%,520px);padding:16px 18px;background:#fff;border:1px solid #fdba74;border-left:5px solid #ea580c;border-radius:4px;box-shadow:0 8px 22px rgba(124,45,18,.1);color:#431407}
.row-mine .employee-report-card{margin-left:auto}
.row-theirs .employee-report-card{margin-right:auto}
.employee-report-heading{display:flex;align-items:center;gap:12px}
.employee-report-mark{padding:5px 7px;background:#9a3412;color:#fff;font-size:10px;font-weight:900;letter-spacing:1px}
.employee-report-title{font-size:15px;font-weight:900;color:#7c2d12}
.employee-report-subtitle{margin-top:2px;font-size:10px;text-transform:uppercase;letter-spacing:.6px;color:#9a3412}
.employee-report-divider{height:1px;margin:14px 0;border-top:1px solid #fed7aa}
.employee-report-field span{display:block;font-size:10px;font-weight:800;letter-spacing:.7px;text-transform:uppercase;color:#9a3412}
.employee-report-field strong{display:block;margin-top:4px;font-size:14px;color:#431407}
.employee-report-details{margin-top:14px}
.employee-report-details p{margin:5px 0 0;white-space:pre-wrap;line-height:1.45;color:#572314}
.employee-report-footer{display:flex;justify-content:space-between;gap:10px;margin-top:16px;padding-top:10px;border-top:1px solid #ffedd5;font-size:10px;color:#9a3412}
.employee-report-form{padding:12px;border-top:1px solid rgba(15,23,42,0.04);background:#fff7ed;display:flex;flex-direction:column;gap:8px}
.report-form-title{font-weight:800;color:#9a3412}
.report-employee-name{padding:9px;border:1px solid #fed7aa;border-radius:8px;background:#fff;color:#9a3412}
.employee-report-form textarea{width:100%;box-sizing:border-box;padding:9px;border:1px solid #fed7aa;border-radius:8px;background:#fff}
.employee-report-form textarea{min-height:74px;resize:vertical}
.report-form-actions{display:flex;justify-content:flex-end;gap:8px}
.cancel-report-btn,.report-submit-btn{border:none;padding:8px 12px;border-radius:8px;cursor:pointer}
.cancel-report-btn{background:#fff;color:#9a3412;border:1px solid #fed7aa}
.report-submit-btn{background:#ea580c;color:#fff}
.report-submit-btn:disabled{opacity:.55;cursor:not-allowed}

/* Ensure messages wrap long words and code-like content */
.msg-body{white-space:pre-wrap;word-wrap:break-word;overflow-wrap:break-word}
.attachment-input{display:none}
.attach-btn{background:#0f766e!important;color:#fff;border:none;padding:10px 12px;border-radius:10px;cursor:pointer}
.attachment-selected{max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:11px;color:#475569}
.msg-attachment-image{display:block;max-width:220px;max-height:180px;margin-top:8px;border-radius:8px;object-fit:cover;cursor:pointer}
.msg-attachment-link{display:block;margin-top:8px;color:#0f766e;font-weight:700;word-break:break-word}

</style>
