<template>
  <transition name="fade">
    <div v-if="show" class="info-backdrop" @click.self="$emit('close')">
      <div class="info-modal announcement-modal">
        <div class="modal-header-custom">
          <h3>📢 Send Announcement</h3>
          <button class="modal-close-btn" @click="$emit('close')">✕</button>
        </div>

        <div class="modal-body-custom">
          <div class="form-group-custom">
            <label class="info-label">Title</label>
            <input v-model="localTitle" class="info-input" type="text" placeholder="Enter announcement title" @keyup.enter="$emit('send')" />
          </div>

          <div class="form-group-custom">
            <label class="info-label">Message</label>
            <textarea v-model="localText" class="info-input" rows="5" placeholder="Write your announcement message..."></textarea>
          </div>

          <div class="form-group-custom">
            <label class="info-label">Send To</label>
            <select v-model="localTarget" class="info-input">
              <option value="all">👥 All Branches (Everyone)</option>
              <option value="staff">👨‍🍳 All Staff</option>
              <option value="managers">👔 Managers Only</option>
            </select>
          </div>

          <div v-if="error" class="alert-message alert-error">⚠️ {{ error }}</div>
          <div v-if="success" class="alert-message alert-success">✅ {{ success }}</div>
        </div>

        <div class="modal-footer-custom">
          <button class="btn-outline" @click="$emit('close')" :disabled="sending">Cancel</button>
          <button class="btn-primary" @click="$emit('send')" :disabled="sending">
            {{ sending ? 'Sending...' : 'Send Announcement' }}
          </button>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  show: { type: Boolean, default: true },
  title: { type: String, default: '' },
  text: { type: String, default: '' },
  target: { type: String, default: 'all' },
  error: { type: String, default: '' },
  success: { type: String, default: '' },
  sending: { type: Boolean, default: false },
})

const emit = defineEmits(['close', 'send', 'update:title', 'update:text', 'update:target'])

const localTitle = computed({
  get() { return props.title },
  set(val) { emit('update:title', val) }
})

const localText = computed({
  get() { return props.text },
  set(val) { emit('update:text', val) }
})

const localTarget = computed({
  get() { return props.target },
  set(val) { emit('update:target', val) }
})
</script>
