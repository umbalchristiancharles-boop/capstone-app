<template>
  <section class="scaffold scaffold-white scaffold-contact" id="contact">
    <div class="scaffold-content contact-section">
      <div class="contact-header">
        <span class="contact-kicker">Get in touch</span>
        <h2>Contact Admin</h2>
        <p class="contact-subtitle">
          Have questions, concerns, or feedback? Send us a message and we'll get back to you as soon as possible.
        </p>
      </div>

      <div class="contact-container">
        <div class="contact-form-wrapper">
          <form @submit.prevent="submitReport" class="contact-form">
            <div v-if="successMessage" class="banner banner--success">
              ✅ {{ successMessage }}
            </div>
            <div v-if="errorMessage" class="banner banner--error">
              ⚠️ {{ errorMessage }}
            </div>

            <div class="form-row">
              <div class="field" :class="{ 'field--error': fieldErrors.customer_name }">
                <label for="customer_name" class="field-label">
                  Full Name <span class="req">*</span>
                </label>
                <input
                  id="customer_name"
                  v-model="form.customer_name"
                  type="text"
                  placeholder="Your full name"
                  :disabled="submitting"
                />
                <div class="inline-error" v-if="fieldErrors.customer_name">{{ fieldErrors.customer_name }}</div>
              </div>

              <div class="field" :class="{ 'field--error': fieldErrors.customer_email }">
                <label for="customer_email" class="field-label">
                  Email <span class="req">*</span>
                </label>
                <input
                  id="customer_email"
                  v-model="form.customer_email"
                  type="email"
                  placeholder="your.email@example.com"
                  :disabled="submitting"
                />
                <div class="inline-error" v-if="fieldErrors.customer_email">{{ fieldErrors.customer_email }}</div>
              </div>
            </div>

            <div class="form-row">
              <div class="field" :class="{ 'field--error': fieldErrors.customer_phone }">
                <label for="customer_phone" class="field-label">
                  Phone Number <span class="opt">(Optional)</span>
                </label>
                <input
                  id="customer_phone"
                  v-model="form.customer_phone"
                  type="tel"
                  placeholder="09xxxxxxxxx"
                  :disabled="submitting"
                />
                <div class="inline-error" v-if="fieldErrors.customer_phone">{{ fieldErrors.customer_phone }}</div>
              </div>

              <div class="field" :class="{ 'field--error': fieldErrors.subject }">
                <label for="subject" class="field-label">
                  Subject <span class="req">*</span>
                </label>
                <input
                  id="subject"
                  v-model="form.subject"
                  type="text"
                  placeholder="What is this about?"
                  :disabled="submitting"
                />
                <div class="inline-error" v-if="fieldErrors.subject">{{ fieldErrors.subject }}</div>
              </div>
            </div>

            <div class="field field--full" :class="{ 'field--error': fieldErrors.message }">
              <label for="message" class="field-label">
                Message <span class="req">*</span>
              </label>
              <textarea
                id="message"
                v-model="form.message"
                rows="6"
                placeholder="Please describe your question, concern, or feedback in detail..."
                :disabled="submitting"
              ></textarea>
              <div class="inline-error" v-if="fieldErrors.message">{{ fieldErrors.message }}</div>
            </div>

            <div class="form-actions">
              <button
                type="button"
                class="btn btn-cancel"
                @click="resetForm"
                :disabled="submitting"
              >
                Clear
              </button>
              <button
                type="submit"
                class="btn btn-primary"
                :disabled="submitting"
              >
                {{ submitting ? 'Sending...' : 'Send Message' }}
              </button>
            </div>
          </form>
        </div>

        <div class="contact-info">
          <div class="info-card">
            <div class="info-icon">📍</div>
            <div class="info-content">
              <h3>Visit Us</h3>
              <p>4606 Mangubat Ave, Zone 4<br />Dasmariñas, Cavite</p>
            </div>
          </div>

          <div class="info-card">
            <div class="info-icon">📞</div>
            <div class="info-content">
              <h3>Call Us</h3>
              <p>We'll respond within 24 hours</p>
            </div>
          </div>

          <div class="info-card">
            <div class="info-icon">⏰</div>
            <div class="info-content">
              <h3>Business Hours</h3>
              <p>Monday - Sunday<br />10:00 AM - 10:00 PM</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, reactive } from 'vue'
import axios from 'axios'

const submitting = ref(false)
const successMessage = ref('')
const errorMessage = ref('')
const fieldErrors = ref({})

const form = reactive({
  customer_name: '',
  customer_email: '',
  customer_phone: '',
  subject: '',
  message: '',
})

function resetForm() {
  form.customer_name = ''
  form.customer_email = ''
  form.customer_phone = ''
  form.subject = ''
  form.message = ''
  successMessage.value = ''
  errorMessage.value = ''
  fieldErrors.value = {}
}

async function submitReport() {
  // Clear previous messages
  successMessage.value = ''
  errorMessage.value = ''
  fieldErrors.value = {}

  // Basic validation
  const errors = {}
  if (!form.customer_name.trim()) errors.customer_name = 'Name is required'
  if (!form.customer_email.trim()) errors.customer_email = 'Email is required'
  else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.customer_email)) errors.customer_email = 'Invalid email format'
  if (!form.subject.trim()) errors.subject = 'Subject is required'
  if (!form.message.trim()) errors.message = 'Message is required'
  else if (form.message.trim().length < 10) errors.message = 'Message must be at least 10 characters'

  if (Object.keys(errors).length > 0) {
    fieldErrors.value = errors
    return
  }

  submitting.value = true

  try {
    const response = await axios.post('/api/customer-reports', {
      customer_name: form.customer_name.trim(),
      customer_email: form.customer_email.trim(),
      customer_phone: form.customer_phone.trim() || null,
      subject: form.subject.trim(),
      message: form.message.trim(),
    })

    if (response.data.ok) {
      successMessage.value = response.data.message || 'Your message has been sent successfully!'
      resetForm()
      
      // Scroll to top of form to show success message
      window.scrollTo({
        top: document.getElementById('contact')?.offsetTop - 100 || 0,
        behavior: 'smooth'
      })
    }
  } catch (error) {
    console.error('Error submitting report:', error)
    errorMessage.value = error.response?.data?.error || error.response?.data?.message || 'Failed to send message. Please try again.'
  } finally {
    submitting.value = false
  }
}
</script>

<style scoped>
.contact-section {
  max-width: 1200px;
  margin: 0 auto;
  padding: 60px 24px;
}

.contact-header {
  text-align: center;
  margin-bottom: 48px;
}

.contact-kicker {
  display: inline-block;
  padding: 6px 16px;
  background: linear-gradient(135deg, #FF9A4A, #FF6A3D);
  color: white;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 600;
  margin-bottom: 16px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.contact-header h2 {
  font-size: 2.5rem;
  font-weight: 800;
  color: #1F2937;
  margin: 0 0 12px 0;
  font-family: 'Inter', 'Poppins', sans-serif;
}

.contact-subtitle {
  font-size: 1.05rem;
  color: #6B7280;
  max-width: 600px;
  margin: 0 auto;
  line-height: 1.6;
}

.contact-container {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 48px;
  align-items: start;
}

.contact-form-wrapper {
  background: white;
  padding: 32px;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.contact-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.field--full {
  grid-column: 1 / -1;
}

.field-label {
  font-size: 0.95rem;
  font-weight: 600;
  color: #374151;
}

.req {
  color: #EF4444;
}

.opt {
  color: #9CA3AF;
  font-weight: 400;
}

input,
textarea {
  padding: 12px 16px;
  border: 1px solid #D1D5DB;
  border-radius: 8px;
  font-size: 0.95rem;
  font-family: inherit;
  transition: all 0.2s;
  background: white;
}

input:focus,
textarea:focus {
  outline: none;
  border-color: #FF6A3D;
  box-shadow: 0 0 0 3px rgba(255, 106, 61, 0.1);
}

input:disabled,
textarea:disabled {
  background: #F3F4F6;
  cursor: not-allowed;
  opacity: 0.7;
}

textarea {
  resize: vertical;
  min-height: 120px;
}

.field--error input,
.field--error textarea {
  border-color: #EF4444;
  background: #FEF2F2;
}

.inline-error {
  font-size: 0.85rem;
  color: #EF4444;
  margin-top: 4px;
}

.banner {
  padding: 12px 16px;
  border-radius: 8px;
  font-size: 0.95rem;
  font-weight: 500;
}

.banner--success {
  background: #D1FAE5;
  color: #065F46;
  border: 1px solid #A7F3D0;
}

.banner--error {
  background: #FEE2E2;
  color: #991B1B;
  border: 1px solid #FECACA;
}

.form-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  margin-top: 8px;
}

.btn {
  padding: 12px 24px;
  border: none;
  border-radius: 8px;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  font-family: inherit;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-primary {
  background: linear-gradient(135deg, #FF9A4A, #FF6A3D);
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: linear-gradient(135deg, #FF6A3D, #E85A2D);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(255, 106, 61, 0.3);
}

.btn-cancel {
  background: #F3F4F6;
  color: #6B7280;
}

.btn-cancel:hover:not(:disabled) {
  background: #E5E7EB;
}

.contact-info {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.info-card {
  background: white;
  padding: 24px;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  display: flex;
  gap: 16px;
  align-items: flex-start;
  transition: transform 0.2s;
}

.info-card:hover {
  transform: translateY(-2px);
}

.info-icon {
  font-size: 2rem;
  flex-shrink: 0;
}

.info-content h3 {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1F2937;
  margin: 0 0 8px 0;
}

.info-content p {
  font-size: 0.95rem;
  color: #6B7280;
  margin: 0;
  line-height: 1.5;
}

@media (max-width: 768px) {
  .contact-container {
    grid-template-columns: 1fr;
    gap: 32px;
  }

  .contact-header h2 {
    font-size: 2rem;
  }

  .form-row {
    grid-template-columns: 1fr;
  }

  .contact-form-wrapper {
    padding: 24px;
  }

  .form-actions {
    flex-direction: column-reverse;
  }

  .btn {
    width: 100%;
  }
}
</style>