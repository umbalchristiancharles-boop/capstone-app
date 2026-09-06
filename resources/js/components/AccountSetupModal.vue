<template>
  <div v-if="show" class="setup-modal-overlay">
    <div class="setup-modal-card">
      <div class="setup-modal-header">
        <h2>{{ setupType === 'full' ? 'Complete Your Account Setup' : 'Upload Required Documents' }}</h2>
        <p class="setup-subtitle">{{ setupType === 'full' ? 'We need a few details to get you started' : 'Please upload your required documents to continue' }}</p>
      </div>

      <div class="setup-steps">
        <!-- Step 1: Email Verification -->
        <div v-if="currentStep === 'email' && missingFields.includes('email')" class="setup-step active">
          <div class="step-header">
            <h3>📧 Email Address</h3>
            <span class="step-badge">Step 1 of {{ totalSteps }}</span>
          </div>

          <div class="step-content">
            <input
              v-model="form.email"
              type="email"
              placeholder="Enter your email"
              class="step-input"
              @keyup.enter="sendEmailCode"
              :disabled="emailCodeSent"
            />

            <button
              v-if="!emailCodeSent"
              @click="sendEmailCode"
              class="step-btn-primary"
              :disabled="isLoading || !form.email"
            >
              {{ isLoading ? 'Sending...' : 'Send Verification Code' }}
            </button>

            <div v-if="emailError" class="step-error">{{ emailError }}</div>
            <div v-if="emailSuccess" class="step-success">{{ emailSuccess }}</div>

            <!-- Enter verification code -->
            <div v-if="emailCodeSent" class="verification-section">
              <p class="verification-label">Enter the 6-digit code sent to your email</p>
              <input
                v-model="form.emailCode"
                type="text"
                placeholder="000000"
                maxlength="6"
                class="step-input code-input"
                @keyup.enter="verifyEmail"
              />
              <button @click="verifyEmail" class="step-btn-primary" :disabled="isLoading || form.emailCode.length !== 6">
                {{ isLoading ? 'Verifying...' : 'Verify Email' }}
              </button>
              <button @click="resendEmailCode" class="step-btn-secondary" :disabled="isLoading">
                Resend Code
              </button>
            </div>
          </div>
        </div>

        <!-- Step 2: Email Verification Only (if email exists but not verified) -->
        <div v-if="currentStep === 'email_verification' && missingFields.includes('email_verification')" class="setup-step active">
          <div class="step-header">
            <h3>📧 Verify Your Email</h3>
            <span class="step-badge">Step {{ getStepNumber('email_verification') }} of {{ totalSteps }}</span>
          </div>

          <div class="step-content">
            <p class="verification-label">We sent a 6-digit code to <strong>{{ form.email }}</strong></p>

            <input
              v-model="form.emailCode"
              type="text"
              placeholder="000000"
              maxlength="6"
              class="step-input code-input"
              @keyup.enter="verifyEmail"
            />

            <button @click="verifyEmail" class="step-btn-primary" :disabled="isLoading || form.emailCode.length !== 6">
              {{ isLoading ? 'Verifying...' : 'Verify Email' }}
            </button>
            <button @click="sendEmailCode" class="step-btn-secondary" :disabled="isLoading">
              Send Code Again
            </button>

            <div v-if="emailError" class="step-error">{{ emailError }}</div>
            <div v-if="emailSuccess" class="step-success">{{ emailSuccess }}</div>
          </div>
        </div>

        <!-- Step 3: Phone Number -->
        <div v-if="currentStep === 'phone' && missingFields.includes('phone_number')" class="setup-step active">
          <div class="step-header">
            <h3>📱 Phone Number</h3>
            <span class="step-badge">Step {{ getStepNumber('phone') }} of {{ totalSteps }}</span>
          </div>

          <div class="step-content">
            <input
              v-model="form.phoneNumber"
              type="tel"
              placeholder="e.g., +63 9XX-XXX-XXXX"
              class="step-input"
              @keyup.enter="nextStep"
            />
            <button @click="nextStep" class="step-btn-primary" :disabled="isLoading || !form.phoneNumber">
              Continue
            </button>
            <div v-if="phoneError" class="step-error">{{ phoneError }}</div>
          </div>
        </div>

        <!-- Step 4: Current Address (Location) -->
        <div v-if="currentStep === 'address' && missingFields.includes('address')" class="setup-step active">
          <div class="step-header">
            <h3>📍 Current Home Address</h3>
            <span class="step-badge">Step {{ getStepNumber('address') }} of {{ totalSteps }}</span>
          </div>

          <div class="step-content">
            <p class="step-description">Pin your current home address on the map</p>

            <!-- Use GeolocationMap component -->
            <GeolocationMap
              :initialLocation="form.location"
              :initialAddress="form.address"
              @update:location="onLocationUpdate"
              @save:location="onLocationSave"
            />

            <button
              @click="nextStep"
              class="step-btn-primary"
              :disabled="isLoading || !form.location.lat || !form.location.lng"
            >
              Continue
            </button>
            <div v-if="addressError" class="step-error">{{ addressError }}</div>
          </div>
        </div>

        <!-- Step 5: Documents -->
        <div v-if="currentStep === 'documents' && hasDocuments" class="setup-step active">
          <div class="step-header">
            <h3>📄 Required Documents</h3>
            <span class="step-badge">Step {{ getStepNumber('documents') }} of {{ totalSteps }}</span>
          </div>

          <div class="step-content">
            <p class="step-description">Upload copies of your identification documents</p>

            <div class="documents-grid">
              <!-- SSS ID -->
              <div v-if="missingFields.includes('sss_id')" class="document-item">
                <div class="document-label">SSS ID</div>
                <div v-if="form.sssId" class="document-preview">
                  <span class="document-name">{{ form.sssId.name }}</span>
                  <button @click="removeSssId" class="remove-btn" type="button">✕</button>
                </div>
                <label v-else class="document-upload">
                  <input type="file" @change="onSssIdChange" accept="image/*,.pdf" />
                  <span>Choose File</span>
                </label>
              </div>

              <!-- PhilHealth ID -->
              <div v-if="missingFields.includes('philhealth_id')" class="document-item">
                <div class="document-label">PhilHealth ID</div>
                <div v-if="form.philhealthId" class="document-preview">
                  <span class="document-name">{{ form.philhealthId.name }}</span>
                  <button @click="removePhilhealthId" class="remove-btn" type="button">✕</button>
                </div>
                <label v-else class="document-upload">
                  <input type="file" @change="onPhilhealthIdChange" accept="image/*,.pdf" />
                  <span>Choose File</span>
                </label>
              </div>

              <!-- Valid ID -->
              <div v-if="missingFields.includes('government_id')" class="document-item">
                <div class="document-label">Valid ID</div>
                <div v-if="form.governmentId" class="document-preview">
                  <span class="document-name">{{ form.governmentId.name }}</span>
                  <button @click="removeGovernmentId" class="remove-btn" type="button">✕</button>
                </div>
                <label v-else class="document-upload">
                  <input type="file" @change="onGovernmentIdChange" accept="image/*,.pdf" />
                  <span>Choose File</span>
                </label>
              </div>

              <!-- Tin ID -->
              <div v-if="missingFields.includes('tin_id')" class="document-item">
                <div class="document-label">TIN ID</div>
                <div v-if="form.tinId" class="document-preview">
                  <span class="document-name">{{ form.tinId.name }}</span>
                  <button @click="removeTinId" class="remove-btn" type="button">✕</button>
                </div>
                <label v-else class="document-upload">
                  <input type="file" @change="onTinIdChange" accept="image/*,.pdf" />
                  <span>Choose File</span>
                </label>
              </div>

              <!-- NBI Clearance -->
              <div v-if="missingFields.includes('nbi_clearance')" class="document-item">
                <div class="document-label">NBI Clearance</div>
                <div v-if="form.nbiClearance" class="document-preview">
                  <span class="document-name">{{ form.nbiClearance.name }}</span>
                  <button @click="removeNbiClearance" class="remove-btn" type="button">✕</button>
                </div>
                <label v-else class="document-upload">
                  <input type="file" @change="onNbiClearanceChange" accept="image/*,.pdf" />
                  <span>Choose File</span>
                </label>
              </div>

              <!-- Medical Certificate -->
              <div v-if="missingFields.includes('medical_certificate')" class="document-item">
                <div class="document-label">Medical Certificate</div>
                <div v-if="form.medicalCertificate" class="document-preview">
                  <span class="document-name">{{ form.medicalCertificate.name }}</span>
                  <button @click="removeMedicalCertificate" class="remove-btn" type="button">✕</button>
                </div>
                <label v-else class="document-upload">
                  <input type="file" @change="onMedicalCertificateChange" accept="image/*,.pdf" />
                  <span>Choose File</span>
                </label>
              </div>

              <!-- Drug Test Result -->
              <div v-if="missingFields.includes('drug_test_result')" class="document-item">
                <div class="document-label">Drug Test Result</div>
                <div v-if="form.drugTestResult" class="document-preview">
                  <span class="document-name">{{ form.drugTestResult.name }}</span>
                  <button @click="removeDrugTestResult" class="remove-btn" type="button">✕</button>
                </div>
                <label v-else class="document-upload">
                  <input type="file" @change="onDrugTestResultChange" accept="image/*,.pdf" />
                  <span>Choose File</span>
                </label>
              </div>
            </div>

            <button
              @click="submitDocuments"
              class="step-btn-primary"
              :disabled="isLoading || !allDocumentsReady"
            >
              {{ isLoading ? 'Uploading...' : 'Upload Documents' }}
            </button>

            <div v-if="documentError" class="step-error">{{ documentError }}</div>
            <div v-if="documentSuccess" class="step-success">{{ documentSuccess }}</div>
          </div>
        </div>

        <!-- Completion Step -->
        <div v-if="currentStep === 'complete'" class="setup-step active completion-step">
          <div class="completion-icon">✓</div>
          <h3>Account Setup Complete!</h3>
          <p>Your account is now fully set up. You can proceed to your dashboard.</p>
          <button @click="completeSetup" class="step-btn-primary">
            Go to Dashboard
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import axios from 'axios'
import GeolocationMap from './GeolocationMap.vue'

const emit = defineEmits(['complete'])

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  missingFields: {
    type: Array,
    default: () => []
  },
  setupType: {
    type: String,
    default: 'full',
    validator: (value) => ['full', 'documents'].includes(value)
  }
})

const currentStep = ref('email')
const isLoading = ref(false)

const form = ref({
  email: '',
  emailCode: '',
  phoneNumber: '',
  address: '',
  location: {
    lat: null,
    lng: null
  },
  sssId: null,
  philhealthId: null,
  governmentId: null,
  tinId: null,
  nbiClearance: null,
  medicalCertificate: null,
  drugTestResult: null
})

const emailCodeSent = ref(false)
const emailError = ref('')
const emailSuccess = ref('')
const phoneError = ref('')
const addressError = ref('')
const documentError = ref('')
const documentSuccess = ref('')

const steps = computed(() => {
  const s = []

  // Only include full setup steps if setupType is 'full'
  if (props.setupType === 'full') {
    if (props.missingFields.includes('email')) s.push('email')
    if (props.missingFields.includes('email_verification')) s.push('email_verification')
    if (props.missingFields.includes('phone_number')) s.push('phone')
    if (props.missingFields.includes('address')) s.push('address')
  }

  // Always include documents step if any required documents are missing
  if (
    props.missingFields.includes('sss_id') ||
    props.missingFields.includes('philhealth_id') ||
    props.missingFields.includes('government_id') ||
    props.missingFields.includes('tin_id') ||
    props.missingFields.includes('nbi_clearance') ||
    props.missingFields.includes('medical_certificate') ||
    props.missingFields.includes('drug_test_result')
  ) {
    s.push('documents')
  }

  s.push('complete')
  return s
})

const totalSteps = computed(() => steps.value.length - 1) // -1 because 'complete' is not a real step
const hasDocuments = computed(() => {
  return props.missingFields.includes('sss_id') ||
    props.missingFields.includes('philhealth_id') ||
    props.missingFields.includes('government_id') ||
    props.missingFields.includes('tin_id') ||
    props.missingFields.includes('nbi_clearance') ||
    props.missingFields.includes('medical_certificate') ||
    props.missingFields.includes('drug_test_result')
})

const allDocumentsReady = computed(() => {
  const docsNeeded = {
    sss_id: props.missingFields.includes('sss_id') ? !!form.value.sssId : true,
    philhealth_id: props.missingFields.includes('philhealth_id') ? !!form.value.philhealthId : true,
    government_id: props.missingFields.includes('government_id') ? !!form.value.governmentId : true,
    tin_id: props.missingFields.includes('tin_id') ? !!form.value.tinId : true,
    nbi_clearance: props.missingFields.includes('nbi_clearance') ? !!form.value.nbiClearance : true,
    medical_certificate: props.missingFields.includes('medical_certificate') ? !!form.value.medicalCertificate : true,
    drug_test_result: props.missingFields.includes('drug_test_result') ? !!form.value.drugTestResult : true,
  }
  return Object.values(docsNeeded).every(v => v)
})

const getStepNumber = (stepName) => {
  return steps.value.indexOf(stepName) + 1
}

const sendEmailCode = async () => {
  if (!form.value.email || !form.value.email.includes('@')) {
    emailError.value = 'Please enter a valid email'
    return
  }

  isLoading.value = true
  emailError.value = ''
  emailSuccess.value = ''

  try {
    await axios.post('/api/auth/send-verification', {
      email: form.value.email
    }, { withCredentials: true })

    emailSuccess.value = 'Verification code sent to your email'
    emailCodeSent.value = true
  } catch (e) {
    emailError.value = e.response?.data?.message || 'Failed to send verification code'
  } finally {
    isLoading.value = false
  }
}

const resendEmailCode = async () => {
  await sendEmailCode()
}

const verifyEmail = async () => {
  if (!form.value.emailCode || form.value.emailCode.length !== 6) {
    emailError.value = 'Please enter the 6-digit code'
    return
  }

  isLoading.value = true
  emailError.value = ''
  emailSuccess.value = ''

  try {
    await axios.post('/api/auth/confirm-email', {
      email: form.value.email,
      code: form.value.emailCode
    }, { withCredentials: true })

    emailSuccess.value = 'Email verified successfully!'
    setTimeout(() => {
      nextStep()
    }, 1000)
  } catch (e) {
    emailError.value = e.response?.data?.message || 'Invalid verification code'
  } finally {
    isLoading.value = false
  }
}

const onLocationUpdate = (location) => {
  form.value.location = {
    lat: location.lat,
    lng: location.lng
  }
  if (location.address) {
    form.value.address = location.address
  }
}

const onLocationSave = (location) => {
  form.value.location = {
    lat: location.lat,
    lng: location.lng
  }
  if (location.address) {
    form.value.address = location.address
  }
}

const onSssIdChange = (e) => {
  const file = e.target.files?.[0]
  if (file) {
    form.value.sssId = file
  }
}

const removeSssId = () => {
  form.value.sssId = null
}

const onPhilhealthIdChange = (e) => {
  const file = e.target.files?.[0]
  if (file) {
    form.value.philhealthId = file
  }
}

const removePhilhealthId = () => {
  form.value.philhealthId = null
}

const onGovernmentIdChange = (e) => {
  const file = e.target.files?.[0]
  if (file) {
    form.value.governmentId = file
  }
}

const removeGovernmentId = () => {
  form.value.governmentId = null
}

const onTinIdChange = (e) => {
  const file = e.target.files?.[0]
  if (file) {
    form.value.tinId = file
  }
}

const removeTinId = () => {
  form.value.tinId = null
}

const onNbiClearanceChange = (e) => {
  const file = e.target.files?.[0]
  if (file) {
    form.value.nbiClearance = file
  }
}

const removeNbiClearance = () => {
  form.value.nbiClearance = null
}

const onMedicalCertificateChange = (e) => {
  const file = e.target.files?.[0]
  if (file) {
    form.value.medicalCertificate = file
  }
}

const removeMedicalCertificate = () => {
  form.value.medicalCertificate = null
}

const onDrugTestResultChange = (e) => {
  const file = e.target.files?.[0]
  if (file) {
    form.value.drugTestResult = file
  }
}

const removeDrugTestResult = () => {
  form.value.drugTestResult = null
}

const submitDocuments = async () => {
  if (!allDocumentsReady.value) {
    documentError.value = 'Please upload all required documents'
    return
  }

  isLoading.value = true
  documentError.value = ''
  documentSuccess.value = ''

  try {
    // Upload each document
    const documentsToUpload = [
      { file: form.value.sssId, type: 'sss_id', required: props.missingFields.includes('sss_id') },
      { file: form.value.philhealthId, type: 'philhealth_id', required: props.missingFields.includes('philhealth_id') },
      { file: form.value.governmentId, type: 'government_id', required: props.missingFields.includes('government_id') },
      { file: form.value.tinId, type: 'tin_id', required: props.missingFields.includes('tin_id') },
      { file: form.value.nbiClearance, type: 'nbi_clearance', required: props.missingFields.includes('nbi_clearance') },
      { file: form.value.medicalCertificate, type: 'medical_certificate', required: props.missingFields.includes('medical_certificate') },
      { file: form.value.drugTestResult, type: 'drug_test_result', required: props.missingFields.includes('drug_test_result') },
    ]

    for (const doc of documentsToUpload) {
      if (doc.required && doc.file) {
        const formData = new FormData()
        formData.append('file', doc.file)

        await axios.post(`/api/auth/setup/document/${doc.type}`, formData, {
          headers: { 'Content-Type': 'multipart/form-data' },
          withCredentials: true
        })
      }
    }

    documentSuccess.value = 'Documents uploaded successfully!'
    setTimeout(() => {
      nextStep()
    }, 1000)
  } catch (e) {
    documentError.value = e.response?.data?.message || 'Failed to upload documents'
  } finally {
    isLoading.value = false
  }
}

const nextStep = async () => {
  // Handle current step actions before moving to next
  if (currentStep.value === 'phone' && form.value.phoneNumber) {
    isLoading.value = true
    try {
      await axios.put('/api/auth/setup/account-info', {
        phone_number: form.value.phoneNumber
      }, { withCredentials: true })
    } catch (e) {
      phoneError.value = 'Failed to save phone number'
      isLoading.value = false
      return
    }
    isLoading.value = false
  }

  if (currentStep.value === 'address') {
    isLoading.value = true
    try {
      await axios.put('/api/auth/setup/account-info', {
        address: form.value.address,
        latitude: form.value.location.lat,
        longitude: form.value.location.lng
      }, { withCredentials: true })
    } catch (e) {
      addressError.value = 'Failed to save address'
      isLoading.value = false
      return
    }
    isLoading.value = false
  }

  const currentIndex = steps.value.indexOf(currentStep.value)
  if (currentIndex < steps.value.length - 1) {
    currentStep.value = steps.value[currentIndex + 1]
  }
}

const completeSetup = () => {
  emit('complete')
}

onMounted(() => {
  // Initialize with first missing field step
  console.log('🔍 AccountSetupModal mounted:', {
    show: props.show,
    missingFields: props.missingFields,
    setupType: props.setupType,
    steps: steps.value,
    currentStep: currentStep.value
  })
  if (steps.value.length > 0) {
    currentStep.value = steps.value[0]
    console.log('📝 Set currentStep to:', currentStep.value)
  }
})

// Watch for prop changes and update current step
watch(() => props.missingFields, (newFields) => {
  console.log('🔄 missingFields changed:', newFields)
  console.log('📊 New steps array:', steps.value)
  if (steps.value.length > 0 && steps.value[0] !== currentStep.value) {
    currentStep.value = steps.value[0]
    console.log('🔄 Updated currentStep to:', currentStep.value)
  }
}, { deep: true })

watch(() => props.setupType, (newType) => {
  console.log('🔄 setupType changed:', newType)
  console.log('📊 New steps array:', steps.value)
  if (steps.value.length > 0 && steps.value[0] !== currentStep.value) {
    currentStep.value = steps.value[0]
    console.log('🔄 Updated currentStep to:', currentStep.value)
  }
})
</script>

<style scoped>
.setup-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(92, 79, 68, 0.28);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  padding: 1.5rem;
}

.setup-modal-card {
  width: 100%;
  max-width: 860px;
  background: #f3efe8;
  border: 1px solid #ebdfd2;
  border-radius: 28px;
  box-shadow: 0 22px 48px rgba(60, 39, 18, 0.18);
  overflow: hidden;
}

.setup-modal-header {
  background: #f39a45;
  color: #1f1d1b;
  padding: 2rem 2.25rem 1.5rem;
  text-align: center;
}

.setup-modal-header h2 {
  margin: 0 0 0.5rem;
  font-size: clamp(2rem, 2.8vw, 3rem);
  font-weight: 800;
  letter-spacing: -0.04em;
  color: #1f1d1b;
}

.setup-subtitle {
  margin: 0;
  font-size: 1.1rem;
  color: #2d2a28;
  opacity: 0.9;
}

.setup-steps {
  padding: 1.25rem 2rem 2rem;
  background: #f3efe8;
}

.setup-step {
  display: none;
}

.setup-step.active {
  display: block;
}

.step-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.2rem;
  padding: 0.9rem 0.25rem 1rem;
  border-bottom: 1px solid #e7d9ca;
  background: transparent;
}

.step-header h3 {
  margin: 0;
  font-size: 2rem;
  font-weight: 800;
  color: #1f1d1b;
}

.step-badge {
  background: #f4e8dc;
  color: #7e4a24;
  padding: 0.45rem 0.9rem;
  border-radius: 999px;
  font-size: 0.82rem;
  font-weight: 700;
  border: 1px solid #ead7c3;
}

.step-content {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.step-description {
  color: #4b4139;
  font-size: 1rem;
  margin: 0;
}

.step-input,
.code-input {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 1px solid #f1e5d8;
  border-radius: 8px;
  font-size: 1rem;
  font-family: inherit;
  transition: all 0.2s;
}

.step-input:focus,
.code-input:focus {
  outline: none;
  border-color: #ff9f43;
  box-shadow: 0 0 0 3px rgba(255, 159, 67, 0.14);
}

.code-input {
  letter-spacing: 0.5rem;
  font-weight: 600;
}

.step-btn-primary,
.step-btn-secondary {
  padding: 0.8rem 1.4rem;
  border: none;
  border-radius: 10px;
  font-size: 1rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.step-btn-primary {
  background: #f39a45;
  color: #fff;
  box-shadow: 0 6px 16px rgba(243, 154, 69, 0.18);
}

.step-btn-primary:hover:not(:disabled) {
  transform: translateY(-1px);
  background: #ea8d2d;
}

.step-btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.step-btn-secondary {
  background: #fffaf5;
  color: #2a241f;
  border: 1px solid #e9d4bd;
}

.step-btn-secondary:hover:not(:disabled) {
  background: #f5ebdf;
}

.verification-section {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #f1e5d8;
}

.verification-label {
  color: #6b7280;
  font-size: 0.95rem;
  margin: 0;
}

.step-error {
  background: #fee2e2;
  color: #991b1b;
  padding: 0.75rem 1rem;
  border-radius: 8px;
  font-size: 0.9rem;
  border-left: 4px solid #dc2626;
}

.step-success {
  background: #dcfce7;
  color: #166534;
  padding: 0.75rem 1rem;
  border-radius: 8px;
  font-size: 0.9rem;
  border-left: 4px solid #22c55e;
}

.documents-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
  gap: 1rem;
}

.document-item {
  background: #fffaf5;
  border: 1px dashed #e9d4bd;
  border-radius: 16px;
  padding: 1rem 0.9rem 1.05rem;
  text-align: center;
  box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.25);
}

.document-label {
  font-weight: 700;
  color: #1f1d1b;
  margin-bottom: 0.8rem;
  font-size: 1.05rem;
}

.document-upload {
  display: block;
  cursor: pointer;
}

.document-upload input {
  display: none;
}

.document-upload span {
  display: inline-block;
  background: #f39a45;
  color: #fff;
  padding: 0.7rem 1.25rem;
  border-radius: 10px;
  font-weight: 700;
  font-size: 1rem;
  transition: all 0.2s ease;
  min-width: 150px;
}

.document-upload:hover span {
  transform: translateY(-1px);
  background: #ea8d2d;
}

.document-preview {
  background: white;
  border: 1px solid #f1e5d8;
  border-radius: 6px;
  padding: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
}

.document-name {
  color: #9a4b12;
  font-size: 0.85rem;
  flex: 1;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.remove-btn {
  background: #fee2e2;
  color: #991b1b;
  border: none;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  cursor: pointer;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.remove-btn:hover {
  background: #fecaca;
}

.completion-step {
  text-align: center;
  padding: 2rem 0;
}

.completion-icon {
  width: 80px;
  height: 80px;
  background: #dcfce7;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 3rem;
  color: #22c55e;
  margin: 0 auto 1rem;
}

.completion-step h3 {
  font-size: 1.5rem;
  color: #1f2937;
  margin: 0 0 0.5rem;
}

.completion-step p {
  color: #6b7280;
  margin-bottom: 1.5rem;
}

@media (max-width: 768px) {
  .setup-modal-overlay {
    padding: 0;
  }

  .setup-modal-card {
    max-width: none;
    border-radius: 0;
    height: 100vh;
    display: flex;
    flex-direction: column;
  }

  .setup-steps {
    overflow-y: auto;
    flex: 1;
  }

  .setup-modal-header {
    padding: 1.5rem;
  }

  .setup-steps {
    padding: 1.25rem;
  }

  .step-header {
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-bottom: 1.25rem;
  }

  .setup-modal-header h2 {
    font-size: 1.5rem;
  }

  .documents-grid {
    grid-template-columns: 1fr;
  }

  .step-btn-primary,
  .step-btn-secondary {
    width: 100%;
  }
}
</style>
