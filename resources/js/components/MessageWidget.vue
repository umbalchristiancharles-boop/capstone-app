<template>
  <div v-if="visible">
    <div v-if="open" class="msg-overlay" :class="{ 'msg-overlay--owner': ownerMode }" @click.self="closeWidget">
      <div class="msg-modal" :class="{ 'msg-modal--owner': ownerMode }" role="dialog" aria-modal="true" aria-label="Messages">
        <div class="msg-left">
          <div v-if="ownerMode" class="msg-left-header">
            <div>
              <strong>Messages</strong>
              <span>Conversations with your team</span>
            </div>
            <span v-if="unreadCount > 0" class="msg-total-unread">{{ unreadCount > 99 ? '99+' : unreadCount }} unread</span>
          </div>
          <div v-else class="msg-left-header">Branch Users</div>
          <label v-if="ownerMode" class="msg-search">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.35-4.35"></path></svg>
            <input v-model="userSearch" type="search" placeholder="Search people" aria-label="Search conversations" />
          </label>
          <div class="msg-users">
            <button v-for="u in filteredUsers" :key="u.id" type="button" :class="['msg-user', selected && selected.id === u.id ? 'active' : '', u.unread_count > 0 ? 'has-unread' : '']" @click="selectUser(u)">
              <div class="msg-user-avatar" v-if="u.avatar"><img :src="u.avatar" alt="" /></div>
              <div class="msg-user-avatar" v-else><span>{{ (u.name||'').split(' ').map(n=>n[0]).slice(0,2).join('').toUpperCase() }}</span></div>
              <div class="msg-user-meta">
                <div :class="['msg-user-name', u.unread_count > 0 ? 'unread' : '']">{{ u.name }}</div>
                <div class="msg-user-role">{{ roleLabel(u.role) }}</div>
              </div>
              <span v-if="u.unread_count > 0" class="msg-user-unread">{{ u.unread_count > 99 ? '99+' : u.unread_count }}</span>
            </button>
            <div v-if="usersLoading" class="msg-list-state">Loading conversations...</div>
            <div v-else-if="users.length === 0" class="msg-list-state">No conversations available yet.</div>
            <div v-else-if="filteredUsers.length === 0" class="msg-list-state">No matching conversations.</div>
          </div>
        </div>
        <div class="msg-right">
          <div class="msg-right-header">
            <div class="msg-right-title">
              <div class="msg-right-avatar" v-if="selected && selected.avatar"><img :src="selected.avatar" alt="" /></div>
              <div class="msg-right-avatar" v-else-if="selected"><span>{{ (selected.name||'').split(' ').map(n=>n[0]).slice(0,2).join('').toUpperCase() }}</span></div>
              <div v-if="ownerMode" class="msg-right-text">
                <strong>{{ selected ? selected.name : 'Your inbox' }}</strong>
                <span>{{ selected ? roleLabel(selected.role) : 'Select a conversation to read and reply' }}</span>
              </div>
              <div v-else class="msg-right-text">{{ selected ? ('Conversation with ' + selected.name + ' (' + roleLabel(selected.role) + ')') : 'Select a user' }}</div>
            </div>
            <div class="msg-header-actions">
              <button v-if="canSubmitEmployeeReport && isHrManager(selected)" class="report-btn" @click="reportOpen = !reportOpen">Employee report</button>
              <button class="close-btn" aria-label="Close messages" title="Close" @click="closeWidget">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                  <path d="m18 6-12 12M6 6l12 12"></path>
                </svg>
              </button>
            </div>
          </div>

          <div class="msg-messages" ref="messagesPane">
            <div v-if="!selected && ownerMode" class="msg-empty">
              <span class="msg-empty__icon" aria-hidden="true">✉</span>
              <strong>Select a conversation</strong>
              <span>Choose someone from the list to view your messages.</span>
            </div>
            <div v-else-if="!selected" class="msg-empty">Choose a user to start</div>
            <div v-else-if="messages.length === 0 && ownerMode" class="msg-empty">
              <span class="msg-empty__icon" aria-hidden="true">✉</span>
              <strong>Start the conversation</strong>
              <span>Send a message to {{ selected.name }} below.</span>
            </div>
            <div v-else-if="messages.length === 0" class="msg-thread"></div>
            <div v-else class="msg-thread">
              <template v-for="(m, index) in messages" :key="m.id">
              <div
                v-if="ownerMode && (index === 0 || messageDateKey(messages[index - 1].created_at) !== messageDateKey(m.created_at))"
                class="msg-date-divider"
                role="separator"
              >{{ formatMessageDivider(m.created_at) }}</div>
              <div :class="['msg-row', m.from_user_id === meId ? 'row-mine' : 'row-theirs']">
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
                    <span><template v-if="!ownerMode">{{ formatDate(m.created_at) }}<br></template><em v-if="m.from_user_id === meId">{{ messageStatus(m) }}</em></span>
                  </div>
                </div>
                <div v-else :class="['msg-bubble', m.from_user_id === meId ? 'mine' : 'theirs', { 'msg-bubble--image': ownerMode && isImageAttachment(m) }]">
                  <div v-if="m.from_user?.name || m.from_user_id === meId" class="msg-sender">
                    <strong class="msg-sender-name">{{ m.from_user_id === meId ? currentUserName : m.from_user.name }}</strong>
                    <span v-if="m.from_user_id !== meId" class="msg-sender-role">({{ roleLabel(m.from_user.role) }})</span>
                  </div>
                  <div v-if="m.body" class="msg-body" v-html="escapeHtml(m.body)"></div>
                  <img v-if="isImageAttachment(m)" class="msg-attachment-image" :src="m.attachment_url" :alt="m.attachment_name" @click="openAttachment(m)" />
                  <a v-else-if="m.attachment_url" class="msg-attachment-link" :href="m.attachment_url" target="_blank" rel="noopener">View {{ m.attachment_name }}</a>
                  <div v-if="!ownerMode" class="msg-ts">{{ formatDate(m.created_at) }}</div>
                  <div v-if="m.from_user_id === meId" class="msg-status">{{ messageStatus(m) }}</div>
                </div>
              </div>
              </template>
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

          <div v-if="ownerMode" class="msg-composer msg-composer--owner">
            <input ref="attachmentInput" class="attachment-input" type="file" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt" @change="selectAttachment" />
            <button type="button" class="owner-composer-attach" :disabled="!selected || sending" title="Attach a picture or file" aria-label="Attach a picture or file" @click="$refs.attachmentInput.click()">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="3" y="3" width="18" height="18" rx="3"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><path d="m21 15-5-5L5 21"></path>
              </svg>
            </button>
            <div class="msg-compose-input">
              <div v-if="attachment" class="attachment-preview">
                <img v-if="attachmentPreviewUrl" :src="attachmentPreviewUrl" :alt="attachment.name" class="attachment-preview-image" />
                <span v-else class="attachment-preview-file">FILE</span>
                <span class="attachment-preview-name" :title="attachment.name">{{ attachment.name }}</span>
                <button type="button" class="attachment-remove" @click="clearAttachment" aria-label="Remove attachment">×</button>
              </div>
              <textarea
                v-model="body"
                placeholder="Aa"
                aria-label="Write a message"
                :disabled="!selected || sending"
                @keydown.enter.exact.prevent="send"
              ></textarea>
            </div>
            <button
              type="button"
              class="owner-composer-send"
              :disabled="!selected || sending || (!body.trim() && !attachment)"
              :aria-label="sending ? 'Sending message' : 'Send message'"
              :title="sending ? 'Sending...' : 'Send message'"
              @click="send"
            >
              <svg v-if="!sending" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="m22 2-7 20-4-9-9-4Z"></path><path d="M22 2 11 13"></path>
              </svg>
              <span v-else class="owner-composer-send__spinner" aria-hidden="true"></span>
            </button>
          </div>

          <div v-else class="msg-composer">
            <div class="msg-compose-input">
              <div v-if="attachment" class="attachment-preview">
                <img v-if="attachmentPreviewUrl" :src="attachmentPreviewUrl" :alt="attachment.name" class="attachment-preview-image" />
                <span v-else class="attachment-preview-file">FILE</span>
                <span class="attachment-preview-name" :title="attachment.name">{{ attachment.name }}</span>
                <button type="button" class="attachment-remove" @click="clearAttachment" aria-label="Remove attachment">×</button>
              </div>
              <textarea v-model="body" placeholder="Write a message..."></textarea>
            </div>
            <div class="composer-actions">
              <input ref="attachmentInput" class="attachment-input" type="file" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt" @change="selectAttachment" />
              <button class="attach-btn" @click="$refs.attachmentInput.click()" title="Attach a document or picture">Attach</button>
              <button @click="send" :disabled="!selected || sending || (!body.trim() && !attachment)">Send</button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div v-if="ownerMode && viewingImage" class="image-viewer" role="dialog" aria-modal="true" aria-label="Image preview" @click.self="closeImageViewer">
      <div class="image-viewer__actions">
        <button type="button" class="image-viewer__action" :disabled="imageDownloadBusy" aria-label="Download image" title="Download image" @click="downloadViewedImage">
          <svg v-if="!imageDownloadBusy" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v12m0 0 4-4m-4 4-4-4M5 17v3h14v-3" /></svg>
          <span v-else class="image-viewer__spinner" aria-hidden="true"></span>
        </button>
        <button type="button" class="image-viewer__action" aria-label="Forward image" title="Forward image" @click="forwardPickerOpen = !forwardPickerOpen">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 9V5l7 7-7 7v-4c-5 0-8 1.5-11 5 1-6 4-11 11-11Z" /></svg>
        </button>
        <button type="button" class="image-viewer__action" aria-label="Close image viewer" title="Close" @click="closeImageViewer">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m18 6-12 12M6 6l12 12" /></svg>
        </button>
      </div>
      <img class="image-viewer__image" :src="viewingImage.attachment_url" :alt="viewingImage.attachment_name || 'Sent image'" />
      <div v-if="forwardPickerOpen" class="image-forward-picker" role="dialog" aria-label="Forward image to">
        <div class="image-forward-picker__title">Forward image to</div>
        <div class="image-forward-picker__users">
          <button
            v-for="user in users.filter(user => String(user.id) !== String(meId))"
            :key="user.id"
            type="button"
            :disabled="forwardingImage"
            @click="forwardImageTo(user)"
          >
            <span>{{ user.name }}</span>
            <span class="image-forward-picker__role">{{ roleLabel(user.role) }}</span>
          </button>
          <div v-if="users.length === 0" class="image-forward-picker__empty">No available recipients.</div>
        </div>
        <div v-if="forwardingImage" class="image-forward-picker__progress">Forwarding image...</div>
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
      usersLoading: false,
      userSearch: '',
      ownerMode: false,
      selected: null,
      messages: [],
      body: '',
      attachment: null,
      attachmentPreviewUrl: '',
      sending: false,
      reportOpen: false,
      reportBody: '',
      reportSending: false,
      viewingImage: null,
      forwardPickerOpen: false,
      forwardingImage: false,
      imageDownloadBusy: false,
      meId: null,
      clientHasUser: false,
      hasSession: false,
        csrfRetriedOnce: false,
        fetchUsersInProgress: false,
        stoppedUnauthenticated: false,
      pollTimer: null,
        pendingOwnerUserId: null,
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
    filteredUsers() {
      const query = this.userSearch.trim().toLowerCase()
      if (!query) return this.users
      return this.users.filter(user =>
        `${user.name || ''} ${user.role || ''} ${user.department || ''}`.toLowerCase().includes(query)
      )
    },
    visible() {
      try {
        const p = this.$route?.path || window.location.pathname || '/'
        const user = JSON.parse(localStorage.getItem('user') || 'null')
        if (!user || !user.role) return false

        const isPanelPath = [
          '/admin-panel',
          '/admin/',
          '/admin/deleted-staff',
          '/manager-panel',
          '/manager/',
          '/staff-panel',
          '/staff/',
          '/staff-management',
          '/inventory',
          '/hr-panel',
          '/custom-panel',
          '/supplier-panel',
          '/owner-panel',
          '/owner/',
          '/main-branch/',
          '/super-admin-panel',
          '/super-admin/',
        ].some(prefix => p === prefix.slice(0, -1) || p.startsWith(prefix))

        return isPanelPath && this.hasSession
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
      window.addEventListener('open-message-widget', this.openFromNotification)
      window.addEventListener('keydown', this.onImageViewerKeydown)
  },
  beforeUnmount() {
      this.stopPolling()
      window.removeEventListener('storage', this.onStorageChange)
      window.removeEventListener('focus', this.onWindowFocus)
      window.removeEventListener('open-message-widget', this.openFromNotification)
      window.removeEventListener('keydown', this.onImageViewerKeydown)
    },

  methods: {
    openFromNotification(event) {
      if (!this.visible) return
      this.ownerMode = event?.detail?.ownerPanel === true
      this.pendingOwnerUserId = event?.detail?.userId || null
      this.open = true
      this.fetchUsers()
    },
    closeWidget() {
      this.closeImageViewer()
      this.open = false
      this.ownerMode = false
      this.userSearch = ''
    },
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
    messageDateKey(value){
      const date = new Date(value)
      if (Number.isNaN(date.getTime())) return ''
      return `${date.getFullYear()}-${date.getMonth()}-${date.getDate()}`
    },
    formatMessageDivider(value){
      const date = new Date(value)
      if (Number.isNaN(date.getTime())) return ''
      return date.toLocaleString([], { weekday: 'short', hour: 'numeric', minute: '2-digit' })
    },
    selectAttachment(event){
      const file = event.target.files[0] || null
      this.clearAttachment()
      this.attachment = file
      this.attachmentPreviewUrl = file && file.type.startsWith('image/') ? URL.createObjectURL(file) : ''
    },
    clearAttachment(){
      if (this.attachmentPreviewUrl) URL.revokeObjectURL(this.attachmentPreviewUrl)
      this.attachment = null
      this.attachmentPreviewUrl = ''
      if (this.$refs.attachmentInput) this.$refs.attachmentInput.value = ''
    },
    isImageAttachment(message){
      return !!message.attachment_url && String(message.attachment_mime || '').startsWith('image/')
    },
    openAttachment(message){
      if (this.ownerMode && this.isImageAttachment(message)) {
        this.viewingImage = message
        this.forwardPickerOpen = false
        return
      }
      window.open(message.attachment_url, '_blank', 'noopener')
    },
    closeImageViewer(){
      this.viewingImage = null
      this.forwardPickerOpen = false
    },
    onImageViewerKeydown(event){
      if (event.key === 'Escape' && this.viewingImage) {
        if (this.forwardPickerOpen) this.forwardPickerOpen = false
        else this.closeImageViewer()
      }
    },
    async downloadViewedImage(){
      if (!this.viewingImage || this.imageDownloadBusy) return
      this.imageDownloadBusy = true
      try {
        const response = await axios.get(this.viewingImage.attachment_url, { responseType: 'blob' })
        const url = URL.createObjectURL(response.data)
        const link = document.createElement('a')
        link.href = url
        link.download = this.viewingImage.attachment_name || 'image'
        document.body.appendChild(link)
        link.click()
        link.remove()
        window.setTimeout(() => URL.revokeObjectURL(url), 1000)
      } catch (error) {
        console.error('Could not download message image', error)
        alert('Could not download this image. Please try again.')
      } finally {
        this.imageDownloadBusy = false
      }
    },
    async forwardImageTo(user){
      if (!this.viewingImage || !user || this.forwardingImage) return
      this.forwardingImage = true
      try {
        const response = await axios.get(this.viewingImage.attachment_url, { responseType: 'blob' })
        const file = new File(
          [response.data],
          this.viewingImage.attachment_name || 'forwarded-image',
          { type: this.viewingImage.attachment_mime || response.data.type || 'application/octet-stream' }
        )
        const form = new FormData()
        form.append('to_user_id', user.id)
        form.append('body', '')
        form.append('attachment', file)
        await axios.post('/api/hr/messages/send', form)
        this.closeImageViewer()
        this.selectUser(user)
      } catch (error) {
        console.error('Could not forward message image', error)
        const message = error?.response?.data?.error || 'Could not forward this image. Please try again.'
        alert(message)
      } finally {
        this.forwardingImage = false
      }
    },
    messageStatus(message){
      return message.read_at ? 'Read' : 'Delivered'
    },
    fetchUsers(){
      if (this.fetchUsersInProgress || this.stoppedUnauthenticated) return
      this.fetchUsersInProgress = true
      this.usersLoading = this.users.length === 0

      axios.get('/api/hr/messages/users').then(resp => {
        this.users = resp.data.users || []
        this.unreadCount = Number(resp.data.unread_count || 0)
        window.dispatchEvent(new CustomEvent('owner-message-users-updated', { detail: { users: this.users } }))

        if (this.pendingOwnerUserId) {
          const pendingUser = this.users.find(user => String(user.id) === String(this.pendingOwnerUserId))
          this.pendingOwnerUserId = null
          if (pendingUser) this.selectUser(pendingUser)
        } else if (this.selected && this.users.length) {
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
      }).finally(() => {
        this.fetchUsersInProgress = false
        this.usersLoading = false
      })
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
        this.fetchUsers()
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
        this.clearAttachment()
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
.msg-overlay{position:fixed;inset:0;background:rgba(0,0,0,0.45);display:flex;align-items:center;justify-content:center;z-index:10000}
.msg-modal{width:980px;max-width:98%;height:78vh;background:linear-gradient(180deg,rgba(255,255,255,0.98),rgba(250,250,250,1));border-radius:12px;display:flex;overflow:hidden;box-shadow:0 18px 60px rgba(2,6,23,0.18)}
.msg-left{width:300px;min-width:240px;border-right:1px solid rgba(15,23,42,0.04);display:flex;flex-direction:column;background:linear-gradient(180deg, #fbfeff, #fff)}
.msg-left-header{padding:16px;font-weight:800;border-bottom:1px solid rgba(15,23,42,0.04);color:#0f172a}
.msg-users{overflow:auto;padding:10px;display:flex;flex-direction:column}
.msg-user{display:flex;gap:10px;align-items:center;padding:10px;border-radius:10px;margin-bottom:8px;cursor:pointer;border:1px solid transparent;transition:background .12s, transform .08s}
.msg-user{font:inherit;text-align:left;background:transparent}
.msg-user:hover{transform:translateY(-1px)}
.msg-user.active{background:linear-gradient(90deg, rgba(255,106,61,0.08), rgba(251,191,36,0.04));border-color:rgba(255,170,120,0.08)}
.msg-user.has-unread{background:#ecfeff;border-color:#67e8f9;font-weight:800}
.msg-user-unread{min-width:21px;height:21px;padding:0 5px;border-radius:999px;background:#dc2626;color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:900;box-sizing:border-box}
.msg-user-avatar{width:44px;height:44px;border-radius:10px;overflow:hidden;flex:0 0 44px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#ff6a3d,#f59e0b);color:#fff;font-weight:700}
.msg-user-avatar img{width:100%;height:100%;object-fit:cover}
.msg-user-meta{flex:1;min-width:0}
.msg-user-name{font-weight:500;color:#0f172a}
.msg-user-name.unread{font-weight:800}
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
.msg-sender-name{font-weight:800}
.msg-sender-role{font-weight:600;color:#6b7280;font-size:11px;margin-left:6px}
.msg-ts{font-size:11px;color:rgba(15,23,42,0.45);margin-top:8px;text-align:right}
.msg-status{font-size:10px;color:rgba(15,23,42,0.55);margin-top:3px;text-align:right}
.msg-bubble.mine .msg-ts,.msg-bubble.mine .msg-status{color:rgba(255,255,255,0.9)}
.msg-composer{padding:12px;border-top:1px solid rgba(15,23,42,0.04);background:linear-gradient(180deg,#fff,#fbfdff);display:flex;gap:12px;align-items:flex-end}
.msg-compose-input{flex:1;min-width:0;padding:8px;border:1px solid rgba(15,23,42,0.08);border-radius:12px;background:#fff}
.msg-composer textarea{display:block;width:100%;min-height:32px;max-height:160px;padding:4px 2px;border:0;border-radius:8px;resize:vertical;outline:none;box-sizing:border-box}
.attachment-preview{display:flex;align-items:center;gap:8px;margin-bottom:6px;padding:5px 7px;border-radius:8px;background:#f8fafc;min-width:0}
.attachment-preview-image{width:42px;height:42px;flex:0 0 42px;border-radius:6px;object-fit:cover}
.attachment-preview-file{display:grid;width:42px;height:42px;flex:0 0 42px;place-items:center;border-radius:6px;background:#ff853b;color:#fff;font-size:10px;font-weight:800}
.attachment-preview-name{min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:11px;color:#475569}
.attachment-remove{margin-left:auto;padding:2px 6px;border:0;border-radius:6px;background:transparent;color:#64748b;font-size:18px;line-height:1;cursor:pointer}
.attachment-remove:hover{background:#fee2e2;color:#b91c1c}
.composer-actions{display:flex;gap:8px;align-items:center}
.composer-actions button{background:linear-gradient(90deg,#ff6a3d,#f59e0b);color:#fff;border:none;padding:10px 16px;border-radius:10px;box-shadow:0 8px 20px rgba(255,106,61,0.12)}
.msg-empty{color:#6b7280;padding:20px}
.msg-overlay--owner{align-items:flex-end;justify-content:flex-end;padding:0 24px 24px;background:transparent;pointer-events:none}
.msg-modal--owner{display:flex;width:min(360px,calc(100vw - 24px));max-width:none;height:min(560px,calc(100dvh - 32px));flex-direction:column;border:1px solid rgba(219,188,160,.62);border-radius:16px;background:#fffdfa;box-shadow:0 12px 36px rgba(52,34,22,.24);pointer-events:auto}
.msg-modal--owner .msg-left{display:none}
.msg-modal--owner .msg-right{min-width:0;background:#fffdfa}
.msg-modal--owner .msg-right-header{min-height:62px;padding:9px 12px;border-color:#f1e5da;background:#fffaf5}
.msg-modal--owner .msg-right-title{min-width:0;gap:9px}
.msg-modal--owner .msg-right-avatar{width:36px;height:36px;flex:0 0 36px;background:#f8d4b4;color:#8e431d}
.msg-modal--owner .msg-right-text{display:grid;gap:3px;min-width:0}
.msg-modal--owner .msg-right-text strong{overflow:hidden;color:#3d2a1f;font-size:.82rem;text-overflow:ellipsis;white-space:nowrap}
.msg-modal--owner .msg-right-text span{color:#94735f;font-size:.67rem;font-weight:500}
.msg-modal--owner .msg-header-actions{gap:5px}
.msg-modal--owner .close-btn{display:grid;width:30px;height:30px;place-items:center;padding:0;border:1px solid #ead8ca;border-radius:50%;color:#694a38;background:#fff;font-size:17px;cursor:pointer}
.msg-modal--owner .msg-messages{min-height:0;padding:14px 12px;background:linear-gradient(180deg,#fffdfa,#fff8f1)}
.msg-modal--owner .msg-thread{gap:10px}
.msg-date-divider{align-self:center;margin:8px 0 2px;color:#967d6d;font-size:.68rem;font-weight:600;line-height:1.2;text-align:center}
.msg-modal--owner .msg-date-divider + .msg-row{margin-top:0}
.msg-modal--owner .msg-row{align-items:flex-end;gap:7px}
.msg-modal--owner .msg-avatar-small{width:27px;height:27px;flex-basis:27px;border-radius:50%;font-size:10px}
.msg-modal--owner .msg-bubble{max-width:82%;padding:9px 11px;border-radius:15px;box-shadow:none;font-size:.82rem}
.msg-modal--owner .msg-bubble.mine{border-bottom-right-radius:5px;background:#e87432;color:#fff}
.msg-modal--owner .msg-bubble.theirs{border:1px solid #efe3d8;border-bottom-left-radius:5px;background:#f5eee8;color:#3d2a1f}
.msg-modal--owner .msg-bubble--image{max-width:82%;padding:0;border:0;border-radius:0;background:transparent!important;box-shadow:none;color:inherit}
.msg-modal--owner .msg-bubble--image .msg-sender{margin:0 0 5px 2px}
.msg-modal--owner .msg-bubble--image .msg-attachment-image{width:auto;max-width:min(260px,100%);max-height:320px;margin:0;border-radius:12px;object-fit:contain;background:#f5eee8}
.msg-modal--owner .msg-bubble--image .msg-status{margin:3px 3px 0;color:#94735f}
.msg-modal--owner .msg-sender{margin-bottom:4px;font-size:10px}
.msg-modal--owner .msg-ts{margin-top:5px;font-size:9px}
.msg-modal--owner .msg-status{font-size:9px}
.image-viewer{position:fixed;z-index:10000;inset:0;display:flex;align-items:center;justify-content:center;padding:56px 24px 24px;background:rgba(25,25,25,.82);backdrop-filter:blur(10px)}
.image-viewer__image{display:block;max-width:min(100%,1200px);max-height:calc(100dvh - 100px);object-fit:contain;box-shadow:0 12px 50px rgba(0,0,0,.28)}
.image-viewer__actions{position:absolute;z-index:2;top:14px;right:18px;display:flex;gap:10px}
.image-viewer__action{display:grid;width:46px;height:46px;place-items:center;padding:0;border:1px solid rgba(255,255,255,.8);border-radius:50%;background:#252525;color:#fff;cursor:pointer;box-shadow:0 2px 8px rgba(0,0,0,.45)}
.image-viewer__action:hover:not(:disabled){background:#000}
.image-viewer__action:disabled{opacity:.65;cursor:wait}
.image-viewer__action svg{display:block;width:23px;height:23px;fill:none;stroke:#fff;stroke-width:2.25;stroke-linecap:round;stroke-linejoin:round}
.image-viewer__spinner{width:17px;height:17px;border:2px solid rgba(255,255,255,.45);border-top-color:#fff;border-radius:50%;animation:owner-send-spin .7s linear infinite}
.image-forward-picker{position:absolute;top:68px;right:18px;width:min(300px,calc(100vw - 36px));max-height:min(420px,calc(100dvh - 100px));overflow:hidden;border:1px solid #ead8ca;border-radius:12px;background:#fffdfa;color:#3d2a1f;box-shadow:0 12px 36px rgba(0,0,0,.28)}
.image-forward-picker__title{padding:13px 15px;border-bottom:1px solid #f1e5da;font-size:.9rem;font-weight:700}
.image-forward-picker__users{max-height:320px;overflow:auto}
.image-forward-picker__users button{display:flex;width:100%;flex-direction:column;gap:3px;padding:11px 15px;border:0;border-bottom:1px solid #f5eee8;background:transparent;color:inherit;text-align:left;cursor:pointer}
.image-forward-picker__users button:hover:not(:disabled){background:#fff0e5}
.image-forward-picker__users button:disabled{opacity:.6;cursor:wait}
.image-forward-picker__role{color:#94735f;font-size:.72rem}
.image-forward-picker__empty,.image-forward-picker__progress{padding:12px 15px;color:#94735f;font-size:.8rem}
@media(max-width:600px){.image-viewer{padding:58px 12px 16px}.image-viewer__actions{top:10px;right:10px;gap:7px}.image-viewer__action{width:40px;height:40px}.image-viewer__action svg{width:21px;height:21px}.image-viewer__image{max-height:calc(100dvh - 84px)}}
.msg-modal--owner .msg-empty{display:grid;place-items:center;align-content:center;gap:8px;height:100%;color:#8c796d;text-align:center}
.msg-empty__icon{display:grid;width:44px;height:44px;place-items:center;margin-bottom:3px;border-radius:50%;background:#fff0e5;color:#c25a12;font-size:1.3rem}
.msg-modal--owner .msg-empty strong{color:#523a2b;font-size:.92rem}
.msg-modal--owner .msg-empty span:last-child{font-size:.76rem}
.msg-modal--owner .msg-composer{align-items:flex-end;gap:7px;padding:9px;border-color:#f1e5da;background:#fff}
.msg-modal--owner .msg-compose-input{padding:7px 9px;border-color:#eaded4;border-radius:14px;background:#fffaf5}
.msg-modal--owner .msg-composer textarea{min-height:34px;max-height:100px;font-size:.8rem}
.msg-modal--owner .composer-actions{gap:5px}
.msg-modal--owner .composer-actions button{padding:9px 11px;border-radius:10px;cursor:pointer}
.msg-modal--owner .composer-actions .attach-btn{padding:8px;border:1px solid #eaded4;background:#fff;color:#8e431d;box-shadow:none}
.msg-composer--owner{align-items:center;gap:7px;padding:9px 10px}
.msg-composer--owner .msg-compose-input{display:flex;align-items:center;min-height:40px;padding:2px 11px;border-radius:999px}
.msg-composer--owner .msg-compose-input textarea{min-height:32px;max-height:90px;resize:none;padding:7px 2px;font-size:.82rem;line-height:1.25}
.msg-composer--owner .owner-composer-attach,
.msg-composer--owner .owner-composer-send{display:grid;width:36px;height:36px;flex:0 0 36px;place-items:center;padding:0;border:0;border-radius:50%;cursor:pointer}
.msg-composer--owner .owner-composer-attach{color:#bd5a24;background:transparent}
.msg-composer--owner .owner-composer-attach:hover:not(:disabled){background:#fff0e5}
.msg-composer--owner .owner-composer-send{color:#fff;background:#e87432;box-shadow:0 4px 10px rgba(232,116,50,.2)}
.msg-composer--owner .owner-composer-send:hover:not(:disabled){background:#d86325}
.msg-composer--owner .owner-composer-attach:disabled,
.msg-composer--owner .owner-composer-send:disabled{opacity:.45;cursor:not-allowed;box-shadow:none}
.owner-composer-send__spinner{width:16px;height:16px;border:2px solid rgba(255,255,255,.45);border-top-color:#fff;border-radius:50%;animation:owner-send-spin .7s linear infinite}
@keyframes owner-send-spin{to{transform:rotate(360deg)}}
@media (max-width:700px){.msg-overlay--owner{padding:0 10px 10px}.msg-modal--owner{width:min(360px,calc(100vw - 20px));height:min(560px,calc(100dvh - 20px));border-radius:14px}.msg-modal--owner .msg-right-header{padding:8px 10px}.msg-modal--owner .msg-messages{padding:10px}.msg-modal--owner .msg-composer{align-items:stretch;flex-direction:column}.msg-modal--owner .composer-actions{justify-content:flex-end}}
@media (max-width:700px){.msg-modal--owner .msg-composer--owner{align-items:center;flex-direction:row;gap:5px;padding:8px}.msg-composer--owner .msg-compose-input{min-width:0}.msg-composer--owner .owner-composer-attach,.msg-composer--owner .owner-composer-send{width:32px;height:32px;flex-basis:32px}}
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
.msg-attachment-image{display:block;max-width:220px;max-height:180px;margin-top:8px;border-radius:8px;object-fit:cover;cursor:pointer}
.msg-attachment-link{display:block;margin-top:8px;color:#0f766e;font-weight:700;word-break:break-word}

</style>
