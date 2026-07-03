<template>
  <main class="page">
    <!-- CHIKIN TAYO LOADING OVERLAY PAG CLICK NG LOGIN -->
    <LoadingOverlay :show="showLoginLoader" :text="loaderText" :logo-src="mrLoaderImg" />

    <section class="hero" id="scaffold-1">
      <!-- LEFT content -->
      <div class="hero-left">
        <h1>
          <span class="highlight-korean">Your Korean Snack & Fun Stop!</span> <br>
          <span>WELCOME TO CHIKIN TAYO!</span>
        </h1>

        <p class="subheading">
          <b>CHIKIN TAYO</b> serves chicken, ramen, corndogs, ice cream, and more all in a cozy, Instagram-worthy space.
          Fun food, warm vibes, happy tummies!
        </p>

        <div class="cta-row">
          <!-- Login moved to Staff area; intentionally left blank -->
        </div>

      </div>

      <!-- RIGHT side: big logo only -->
      <div class="hero-right">
        <div class="logo-wrapper">
          <img :src="chikintayoImg" alt="Chikintayo" class="logo-static" />
        </div>
      </div>

      <button
        type="button"
        class="scroll-down-btn hero-center"
        @click="scrollToScaffold3"
        aria-label="Go to Ratings and Comments"
      >
        <span class="scroll-down-icon">↓</span>
        <span class="scroll-down-text">Ratings and Comments</span>
      </button>

      <button
        type="button"
        class="hero-staff-landing-hitbox"
        @click="goToAdminLogin"
        aria-label="Go to Staff Landing"
      >
        <span class="sr-only">Staff Landing</span>
      </button>
    </section>

    <!-- SCAFFOLD 2: WHITE SECTION -->
    <section class="scaffold scaffold-white">
      <div class="scaffold-content">
        <h2>Our Branches</h2>
        <div class="branches-grid">
          <a href="https://tinyurl.com/2th2ayx7" target="_blank" class="branch-card">
            <div class="branch-image">
              <img :src="chikintayoImg" alt="Dasmariñas Branch" />
            </div>
            <h3>Dasmariñas Branch</h3>
            <p> 4606 Mangubat Ave, Zone 4</p>
          </a>
          <a href="https://tinyurl.com/yh9has4x" target="_blank" class="branch-card">
            <div class="branch-image">
              <img :src="chikintayoImg" alt="Sm Pampanga Branch" />
            </div>
            <h3>Pampanga Branch</h3>
            <p>Sm City Pampanga</p>
          </a>
          <a href="https://bit.ly/3ZQjZp4" target="_blank" class="branch-card">
            <div class="branch-image">
              <img :src="chikintayoImg" alt="Quezon City | Robinsons Magnolia Branch" />
            </div>
            <h3>Quezon City Branch</h3>
            <p>Robinsons Magnolia Residences</p>
          </a>
        </div>
      </div>
    </section>

    <!-- SCAFFOLD 3: ORANGE SECTION -->
    <section class="scaffold scaffold-orange" id="scaffold-3">
      <div class="scaffold-content">
        <div class="what-we-offer-header">
          <h2>What We Offer</h2>
          <div class="branch-filter">
            <label for="branch-select">Branch</label>
            <select id="branch-select" v-model="selectedBranch" @change="onBranchChange">
              <option :value="0">All Branches</option>
              <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
            </select>
          </div>
        </div>
        <div class="products-grid">
          <div class="product-card" v-for="product in products" :key="product.id">
            <div class="product-box">
              <img :src="product.img" :alt="product.name" />
            </div>
            <div class="product-comments-section">
              <div class="comments-header">
                <h4>{{ product.name }}</h4>
                <div class="rating-summary">
                  <div class="rating-stars">
                    <span
                      v-for="n in 5"
                      :key="n"
                      class="star"
                      :class="{ filled: n <= getAverageRating(product) }"
                    >★</span>
                  </div>
                  <span class="avg-rating">{{ getAverageLabel(product) }}</span>
                </div>
                <span class="comment-count">{{ product.comments.length }}</span>
              </div>

              <!-- Display Comments -->
              <div class="comments-list">
                <div v-if="product.comments.length === 0" class="no-comments">
                  No comments yet. Be the first! 👇
                </div>
                <div v-for="comment in product.comments" :key="comment.id" class="comment-item">
                    <div class="comment-author">{{ comment.author }}</div>
                    <div class="comment-rating">
                      <span
                        v-for="n in 5"
                        :key="n"
                        class="star"
                        :class="{ filled: n <= (comment.rating || 0) }"
                      >★</span>
                    </div>
                    <div class="comment-text">{{ comment.text }}</div>
                    <div class="comment-footer">
                      <div class="comment-time">{{ formatDate(comment.created_at || comment.date) }}</div>
                      <button type="button" class="reply-btn" @click="toggleReply(comment.id, product.id)">
                        💬 Reply
                      </button>
                    </div>

                    <!-- Reply Form -->
                    <div v-if="activeReplyCommentId === comment.id" class="reply-form">
                      <div v-if="googleUser" class="reply-user-badge">
                        <img v-if="googleUser.picture" :src="googleUser.picture" alt="Profile" class="reply-avatar" />
                        <span>{{ googleUser.name }}</span>
                      </div>
                      <textarea
                        v-model="replyData.text"
                        :placeholder="googleUser ? 'Write your reply...' : 'Please sign in to reply'"
                        class="reply-textarea"
                        :disabled="!googleUser"
                      ></textarea>
                      <div class="reply-actions">
                        <button type="button" class="btn-submit-reply" @click="submitReply(product.id, comment.id)">Post Reply</button>
                        <button type="button" class="btn-cancel-reply" @click="cancelReply()">Cancel</button>
                      </div>
                    </div>

                    <!-- Display Replies -->
                    <div v-if="comment.replies && comment.replies.length > 0" class="replies-list">
                      <div v-for="reply in comment.replies" :key="reply.id" class="reply-item">
                        <div class="reply-author">{{ reply.author }}</div>
                        <div class="reply-text">{{ reply.text }}</div>
                        <div class="reply-time">{{ formatDate(reply.created_at || reply.date) }}</div>
                      </div>
                    </div>
                  </div>
              </div>

              <!-- Add Comment Form -->
              <div class="comment-form">
                <!-- Sign In Prompt -->
                <div v-if="!googleUser" class="signin-prompt-section">
                  <button type="button" class="btn-signin-prompt" @click="openAuthModal">
                    🔐 Sign in to comment
                  </button>
                  <p class="signin-note">Create an account or sign in to rate and comment</p>
                </div>

                <!-- User Info Display -->
                <div v-else class="user-info-display">
                  <div class="user-details">
                    <img v-if="googleUser.picture" :src="googleUser.picture" alt="Profile" class="user-avatar" />
                    <div class="user-text">
                      <span class="user-name">{{ googleUser.name }}</span>
                      <span class="user-email">{{ googleUser.email }}</span>
                    </div>
                  </div>
                  <button type="button" class="btn-signout" @click="signOut" title="Sign out">Sign Out</button>
                </div>

                <div class="rating-input">
                  <span class="rating-label">Rating</span>
                  <div class="rating-stars">
                    <button
                      v-for="n in 5"
                      :key="n"
                      type="button"
                      class="star-btn"
                      :class="{ active: n <= newComments[product.id].rating }"
                      @click="setRating(product.id, n)"
                      :aria-label="`Rate ${n} stars`"
                      :disabled="!googleUser"
                    >★</button>
                  </div>
                </div>
                <div class="textarea-wrapper">
                  <textarea
                    v-model="newComments[product.id].text"
                    :placeholder="googleUser ? 'Share your thoughts about this product...' : 'Please sign in with Google to comment'"
                    class="comment-textarea"
                    @keyup.enter.ctrl="submitComment(product.id)"
                    :disabled="!googleUser"
                  ></textarea>
                  <div class="chat-actions">
                    <button
                      type="button"
                      class="emoji-btn"
                      @click="toggleEmojiPicker(product.id)"
                      title="Insert emoji"
                    >😊</button>
                    <button
                      type="button"
                      class="send-btn"
                      @click="submitComment(product.id)"
                      title="Post comment"
                      :disabled="!googleUser"
                    >➤</button>
                  </div>
                  <div v-if="activeEmojiPicker === product.id" class="emoji-picker">
                    <template v-for="(emojis, category) in commonEmojis" :key="category">
                      <div class="emoji-category">
                        <div class="emoji-category-label">{{ category }}</div>
                        <button
                          v-for="emoji in emojis"
                          :key="emoji"
                          type="button"
                          class="emoji-item"
                          @click="insertEmoji(product.id, emoji)"
                        >{{ emoji }}</button>
                      </div>
                    </template>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- SCAFFOLD 4: OPEN POSITIONS (CUSTOMER) -->
    <section class="scaffold scaffold-white scaffold-4">
      <div class="scaffold-content about-section">
        <div class="positions-section-intro">
          <span class="positions-section-kicker">Join the team</span>
          <h2>Open Positions</h2>
          <p class="positions-section-subtitle">
            Explore current openings across the branches and departments that are actively hiring.
          </p>
        </div>

        <div v-if="approvedPositions.length === 0" class="no-positions-empty">
          <div class="no-positions-icon">💼</div>
          <h3 class="no-positions-title">No open positions right now</h3>
          <p class="no-positions-subtitle">Check back soon—new roles may be posted anytime.</p>
        </div>

        <div v-else class="positions-grid" role="list">
          <div
            v-for="p in approvedPositions"
            :key="p.id"
            class="position-card"
            role="listitem"
          >
            <div class="position-card-header">
              <span class="position-card-status">Hiring now</span>
              <span class="position-qty">{{ p.quantity ?? 0 }} {{ (p.quantity ?? 0) === 1 ? 'opening' : 'openings' }}</span>
            </div>

            <div class="position-top">
              <h3 class="position-name" :title="p.position_name">
                {{ p.position_name || 'Position' }}
              </h3>

              <div class="position-pill-row">
                <span v-if="p.department" class="position-pill position-pill--dept">{{ p.department }}</span>
                <span v-if="p.department && p.branch_name" class="position-pill-separator" aria-hidden="true">•</span>
                <span v-if="p.branch_name" class="position-pill position-pill--branch">{{ p.branch_name }}</span>
              </div>
            </div>

            <p v-if="p.description" class="position-desc" :title="p.description">
              {{ p.description }}
            </p>

            <p v-else class="position-desc position-desc--muted">
              No description provided for this position.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- SCAFFOLD 5: WHITE SECTION -->
    <section class="scaffold scaffold-white scaffold-5">
      <div class="scaffold-content about-section">

        <h2>About CHIKIN TAYO</h2>

        <div class="about-content">
          <p>
            <b>CHIKIN TAYO</b> in Dasmariñas, Cavite aims to bring fun, trendy, and comforting Korean-inspired food and snacks to local consumers. It serves as a one-stop Korean food stop where customers can enjoy fried chicken, ramen, corn dogs, ice cream, and other popular Korean treats in a cozy and Instagrammable space. The brand focuses on creating a warm, casual, and enjoyable dining experience for friends, families, and food lovers.
          </p>

          <div class="mission-vision">
            <div class="mission">
              <h3>Mission</h3>
              <p>To provide delicious and high-quality Korean-inspired food and snacks in a friendly and cozy environment, making every visit fun, satisfying, and memorable for our customers.</p>
            </div>

            <div class="vision">
              <h3>Vision</h3>
              <p>To become a leading Korean-inspired food hub in the community, spreading joy and comfort through tasty and trendy meals, and making Korean-style food accessible and affordable to everyone.</p>
            </div>
          </div>
        </div>
      </div>
    </section>
    <transition name="scroll-top">
      <button
        v-show="showScrollTop"
        type="button"
        class="scroll-top-btn"
        @click="scrollToScaffold1"
        aria-label="Back to top"
      >
        ↑
      </button>
    </transition>
  </main>

  <!-- Authentication Modal -->
  <teleport to="body">
    <transition name="modal">
      <div v-if="showAuthModal" class="modal-overlay" @click.self="closeAuthModal">
        <div class="auth-modal">
          <button class="modal-close" @click="closeAuthModal">✕</button>

          <!-- Step 1: Enter Email -->
          <div v-if="authStep === 1" class="auth-step">
            <h2>Sign In / Sign Up</h2>
            <p class="auth-subtitle">Enter your email address to get started</p>

            <input
              v-model="authEmail"
              type="email"
              placeholder="your.email@example.com"
              class="auth-input"
              @keyup.enter="sendVerificationCode"
            />

            <button
              type="button"
              class="btn-auth-primary"
              @click="sendVerificationCode"
              :disabled="authLoading"
            >
              {{ authLoading ? 'Sending...' : 'Continue' }}
            </button>

            <div class="auth-toggle">
              <p>Already have an account? <button type="button" class="link-btn" @click="switchToLogin">Sign In</button></p>
            </div>
          </div>

          <!-- Step 2: Enter Verification Code -->
          <div v-if="authStep === 2" class="auth-step">
            <h2>Verify Your Email</h2>
            <p class="auth-subtitle">We sent a 6-digit code to <strong>{{ authEmail }}</strong></p>
            <div v-if="otpError" class="otp-error">{{ otpError }}</div>
            <input
              v-model="verificationCode"
              type="text"
              placeholder="Enter 6-digit code"
              class="auth-input code-input"
              maxlength="6"
              @keyup.enter="verifyCode"
              :disabled="authLoading"
            />
            <div class="otp-progress-bar" v-if="otpResendCooldown > 0">
              <div class="otp-progress-fill" :style="{ width: otpResendPercent + '%' }"></div>
              <span class="otp-progress-text">Resend available in {{ otpResendCooldown }}s</span>
            </div>
            <button
              type="button"
              class="btn-auth-primary"
              @click="verifyCode"
              :disabled="authLoading"
            >
              {{ authLoading ? 'Verifying...' : 'Verify Code' }}
            </button>
            <button
              type="button"
              class="btn-resend"
              @click="sendVerificationCode"
              :disabled="otpResendCooldown > 0 || authLoading"
            >
              {{ otpResendCooldown > 0 ? `Resend Code (${otpResendCooldown}s)` : 'Resend Code' }}
            </button>
          </div>

          <!-- Step 3: Create Username & Password -->
          <div v-if="authStep === 3" class="auth-step">
            <h2>Create Your Account</h2>
            <p class="auth-subtitle">Choose a username and password</p>

            <input
              v-model="authUsername"
              type="text"
              placeholder="Username"
              class="auth-input"
            />

            <div class="password-field-group">
              <input
                v-model="authPassword"
                :type="showPassword ? 'text' : 'password'"
                placeholder="Password"
                class="auth-input"
                @input="checkPasswordStrength"
              />
              <button type="button" class="toggle-password" @click="showPassword = !showPassword">
                {{ showPassword ? '👁️' : '👁️‍🗨️' }}
              </button>
            </div>

            <!-- Password Strength Indicator -->
            <div v-if="authPassword" class="password-strength">
              <div class="strength-bar">
                <div class="strength-fill" :class="passwordStrength.class" :style="{ width: passwordStrength.width }"></div>
              </div>
              <p class="strength-text" :class="passwordStrength.class">{{ passwordStrength.text }}</p>
            </div>

            <!-- Password Requirements -->
            <div class="password-requirements">
              <p class="req-title">Password must contain:</p>
              <ul>
                <li :class="{ met: passwordChecks.length }">✓ At least 8 characters</li>
                <li :class="{ met: passwordChecks.uppercase }">✓ One uppercase letter</li>
                <li :class="{ met: passwordChecks.lowercase }">✓ One lowercase letter</li>
                <li :class="{ met: passwordChecks.number }">✓ One number</li>
              </ul>
            </div>

            <div class="password-field-group">
              <input
                v-model="authPasswordConfirm"
                :type="showPasswordConfirm ? 'text' : 'password'"
                placeholder="Confirm Password"
                class="auth-input"
                @keyup.enter="createAccount"
              />
              <button type="button" class="toggle-password" @click="showPasswordConfirm = !showPasswordConfirm">
                {{ showPasswordConfirm ? '👁️' : '👁️‍🗨️' }}
              </button>
            </div>

            <button
              type="button"
              class="btn-auth-primary"
              @click="createAccount"
              :disabled="authLoading || !isPasswordValid"
            >
              {{ authLoading ? 'Creating Account...' : 'Create Account' }}
            </button>

            <div class="auth-toggle">
              <p>Already have an account? <button type="button" class="link-btn" @click="switchToDirectLogin">Sign In</button></p>
            </div>
          </div>

          <!-- Step 4: Login (Existing User) -->
          <div v-if="authStep === 4" class="auth-step">
            <h2>Welcome Back!</h2>
            <p class="auth-subtitle">Sign in to continue</p>

            <input
              v-model="authUsername"
              type="text"
              placeholder="Username or Email"
              class="auth-input"
            />

            <div class="password-field-group">
              <input
                v-model="authPassword"
                :type="showPassword ? 'text' : 'password'"
                placeholder="Password"
                class="auth-input"
                @keyup.enter="loginUser"
              />
              <button type="button" class="toggle-password" @click="showPassword = !showPassword">
                {{ showPassword ? '👁️' : '👁️‍🗨️' }}
              </button>
            </div>

            <button
              type="button"
              class="btn-auth-primary"
              @click="loginUser"
              :disabled="authLoading"
            >
              {{ authLoading ? 'Signing In...' : 'Sign In' }}
            </button>

            <div class="auth-toggle">
              <p>Don't have an account? <button type="button" class="link-btn" @click="switchToSignUp">Sign Up</button></p>
            </div>
          </div>

          <!-- Step 5: Direct Login (without email verification) -->
          <div v-if="authStep === 5" class="auth-step">
            <h2>Sign In</h2>
            <p class="auth-subtitle">Enter your credentials</p>

            <input
              v-model="authUsername"
              type="text"
              placeholder="Username"
              class="auth-input"
            />

            <div class="password-field-group">
              <input
                v-model="authPassword"
                :type="showPassword ? 'text' : 'password'"
                placeholder="Password"
                class="auth-input"
                @keyup.enter="loginUser"
              />
              <button type="button" class="toggle-password" @click="showPassword = !showPassword">
                {{ showPassword ? '👁️' : '👁️‍🗨️' }}
              </button>
            </div>

            <button
              type="button"
              class="btn-auth-primary"
              @click="loginUser"
              :disabled="authLoading"
            >
              {{ authLoading ? 'Signing In...' : 'Sign In' }}
            </button>

            <div class="auth-toggle">
              <p>Don't have an account? <button type="button" class="link-btn" @click="switchToSignUp">Sign Up</button></p>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </teleport>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue'
import axios from 'axios'
import { useRouter, RouterLink } from 'vue-router'
import LoadingOverlay from './LoadingOverlay.vue'

const router = useRouter()
const showLoginLoader = ref(false)
const loaderText = ref('Loading admin login...')
const showScrollTop = ref(false)
const hideAdminLogin = ref(false)

const chikintayoImg = new URL('../assets/chikintayo.jpg', import.meta.url).href
const mrLoaderImg   = new URL('../assets/chikinlogo.png', import.meta.url).href
const yangyeomImg   = new URL('../assets/yangyeom.png', import.meta.url).href
const snowcheeseImg = new URL('../assets/snowsheese.png', import.meta.url).href
const corndogImg    = new URL('../assets/corndog.png', import.meta.url).href
const ramenImg      = new URL('../assets/ramens.png', import.meta.url).href
const icecreamImg   = new URL('../assets/icecream.png', import.meta.url).href
const pastriesImg   = new URL('../assets/pastries.png', import.meta.url).href

const activeEmojiPicker = ref(null)
const activeReplyCommentId = ref(null)
const replyData = ref({ author: '', text: '' })
const googleUser = ref(null)
const showAuthModal = ref(false)
const authStep = ref(1) // 1=email, 2=verify code, 3=register, 4=login
const authEmail = ref('')
const verificationCode = ref('')
const authUsername = ref('')
const authPassword = ref('')
const authPasswordConfirm = ref('')
const authLoading = ref(false)
const showPassword = ref(false)
const showPasswordConfirm = ref(false)
// OTP/Verification Code UI state
const otpError = ref('')
const otpResendCooldown = ref(0)
const otpResendTotal = 30 // seconds
let otpResendTimer = null
const otpResendPercent = computed(() => otpResendCooldown.value > 0 ? 100 - Math.round((otpResendCooldown.value / otpResendTotal) * 100) : 100)

// Password strength state
const passwordStrength = ref({ text: '', class: '', width: '0%' })
const passwordChecks = ref({
  length: false,
  uppercase: false,
  lowercase: false,
  number: false
})
const isPasswordValid = ref(false)

const commonEmojis = {
  'Smileys': ['😀', '😃', '😄', '😁', '😆', '😅', '🤣', '😂', '🙂', '🙃', '😉', '😊', '😇', '🥰', '😍', '🤩', '😘', '😗', '😚', '😙', '🥲', '😋', '😛', '😜', '🤪', '😌', '😔', '☹️', '🙁', '😲', '😞', '😖', '😢', '😭', '😤', '😠', '😡', '🤬', '😈', '👿', '💀', '☠️', '💩', '🤡', '👹', '👺', '👻', '👽', '👾', '🤖', '😺', '😸', '😹', '😻', '😼', '😽', '🙀', '😿', '😾'],
  'Hand Gestures': ['👋', '🤚', '🖐️', '✋', '🖖', '👌', '🤌', '🤞', '🫰', '🤟', '🤘', '🤙', '👍', '👎', '✊', '👊', '🤛', '🤜', '👏', '🙌', '👐', '🤲', '🤝'],
  'Hearts & Love': ['🙈', '🙉', '🙊', '💋', '💌', '💘', '💝', '💖', '💗', '💓', '💕', '💞', '💟', '❣️', '💤', '😇', '👼', '🎅', '🎄'],
  'Animals': ['🐶', '🐱', '🐭', '🐹', '🐰', '🦊', '🐻', '🐼', '🐨', '🐯', '🦁', '🐮', '🐷', '🐸', '🐵', '🐒', '🐔', '🐧', '🐦', '🐤', '🦆', '🦅', '🐺', '🐗', '🐴', '🦄', '🐝', '🪱', '🐛', '🦋', '🐌', '🐞', '🐜', '🪰', '🪟', '🦗', '🕷️', '🦂', '🐢', '🐍', '🦎', '🦖', '🦕', '🐙', '🦑', '🦐', '🦞', '🦀', '🐡', '🐠', '🐟', '🐬', '🐳', '🐋', '🦈', '🐊', '🐅', '🐆', '🦓', '🦍', '🦧', '🐘', '🦛', '🦏', '🐪', '🐫', '🦒', '🦘', '🐃', '🐂', '🐄', '🐎', '🐖', '🐏', '🐑', '🧀', '🐐', '🦌', '🐕', '🐩', '🦮', '🐈', '🐓', '🦃', '🦚', '🦜', '🦢', '🦗', '🥚', '🍳', '🍗'],
  'Food & Drink': ['🍕', '🍔', '🍟', '🍗', '🌭', '🌮', '🌯', '🥙', '🨱', '🍝', '🍜', '🍲', '🍛', '🍣', '🍱', '🥘', '🍢', '🍙', '🍚', '🍤', '🍠', '🥟', '🥠', '🍥', '🥮', '🍡', '🍧', '🍨', '🍦', '🍰', '🎂', '🧁', '🍮', '🍭', '🍬', '🍫', '🍿', '🍩', '🍪', '🌰', '🍯', '🥛', '🍼', '☕', '🍵', '🍶', '🍾', '🍷', '🍸', '🍹', '🍺', '🍻', '🥂', '🥃', '🍎', '🍊', '🍋', '🍌', '🍉', '🍇', '🍓', '🍒', '🍑', '🥭', '🍍', '🥥', '🥝', '🍅', '🍆', '🥑', '🥦', '🥬', '🥒', '🌶️', '🌽', '🥕', '🧄', '🧅', '🥔'],
  'Travel': ['🚗', '🚕', '🚙', '🚌', '🚎', '🏎️', '🚓', '🚑', '🚒', '🚐', '🛻', '🚚', '🚛', '🚜', '🏍️', '🛵', '🦯', '🛴', '🚲', '🛺', '🚨', '🚔', '🚍', '🚘', '🚖', '🚡', '🚠', '🎡', '🎢', '🎠', '⛵', '🚤', '🛳️', '⛴️', '🛥️', '🛶', '🚧', '⚓', '⛽'],
  'Symbols': ['❤️', '🧡', '💛', '💚', '💙', '💜', '🖤', '🤍', '🤎', '💔', '💕', '💞', '💓', '💗', '💖', '💝', '💟', '👑', '💍', '💎', '📱', '📲', '💻', '⌨️', '🖥️', '🖨️', '🖱️', '🖲️', '🕹️', '🗜️', '💽', '💾', '💿', '📀', '🧮', '🎥', '🎬', '📺', '📷', '📸', '📼', '🔍', '🔎', '🕯️', '💡', '🔦', '🏮', '📔', '📕', '📖', '📗', '📘', '📙', '📚', '📓', '📒', '📑', '🧷', '🪃', '📎', '🖇️', '📐', '📏', '📌', '📍', '✂️', '🖊️', '🖋️', '✒️', '🖌️', '🖍️', '📝', '✏️', '🔏', '🔐', '🔒', '🔓', '❌', '✅', '✔️', '☑️', '⚠️', '🚨', '🚫', '⛔', '🆘', '🚩', '🏁', '⚡', '☄️', '💥', '✨', '🌟', '⭐', '🌠', '💫', '🔥', '💨', '💧', '🌪️', '☔', '🍀', '🎈', '🎊', '🎉', '🎁', '🎀', '🎯', '🏆', '🥇', '🥈', '🥉', '🏅', '⚽', '⚾', '🥎', '🎾', '🏀', '🏐', '🏈', '🏉', '🥏', '🎳', '🏓', '🏸', '🏒', '🏑', '🥍', '🏏', '🥅', '⛳', '⛸️', '🎣', '🎽', '🎿', '⛷️', '🏂', '🪂', '🛹']
}

const products = ref([])
const branches = ref([])
const selectedBranch = ref(0)
const approvedPositions = ref([])


const newComments = ref({})

// Watch products and ensure newComments is synchronized
watch(products, (newProducts) => {
  newProducts.forEach(product => {
    if (!(product.id in newComments.value)) {
      newComments.value[product.id] = { author: '', text: '', rating: 5 }
    }
  })
}, { immediate: true })

function handleScroll() {
  showScrollTop.value = window.scrollY > 400
}

onMounted(() => {
  // allow wrapper to request hiding the admin login via sessionStorage
  try {
    hideAdminLogin.value = sessionStorage.getItem('hideAdminLogin') === '1'
  } catch (e) {}
  // Clear any stale user session on landing page
  try {
    localStorage.removeItem('user');
       localStorage.removeItem('token');
    sessionStorage.removeItem('user');
       sessionStorage.removeItem('token');
  } catch (e) {}

  if (sessionStorage.getItem('chikin_show_home_overlay') === '1') {
    sessionStorage.removeItem('chikin_show_home_overlay')
    loaderText.value = 'Loading CHIKIN TAYO...'
    showLoginLoader.value = true
    setTimeout(() => {
      showLoginLoader.value = false
      loaderText.value = 'Loading admin login...'
    }, 900)
  }

  handleScroll()
  window.addEventListener('scroll', handleScroll, { passive: true })
  loadBranches()
  loadProducts()
  loadComments()
  loadApprovedPositions()
  loadGoogleUser()
})


onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
})

async function loadProducts() {
  try {
    const params = {}
    if (selectedBranch.value && Number(selectedBranch.value) > 0) params.branch_id = Number(selectedBranch.value)
    const { data } = await axios.get('/api/products-for-comments', { params })
    
    console.debug('[PRODUCTS] Loaded from API:', data)
    
    if (!Array.isArray(data) || data.length === 0) {
      console.warn('[PRODUCTS] No products returned from API!')
      alert('⚠️ No products available for comments. Please contact support.')
      return
    }
    
    // Map product names to images (case-insensitive)
    const imageMap = {
      'yangyeom': yangyeomImg,
      'snow cheese': snowcheeseImg,
      'corndog': corndogImg,
      'pastries': pastriesImg,
      'ramen': ramenImg,
      'ice cream': icecreamImg,
    }
    
    products.value = data.map(product => ({
      ...product,
      comments: [],
      img: imageMap[product.name.toLowerCase()] || chikintayoImg // Use mapped image or fallback to logo
    }))
    
    console.debug('[PRODUCTS] Processed products:', products.value.map(p => ({ id: p.id, name: p.name })))
    
    // Initialize newComments for each product
    newComments.value = Object.fromEntries(
      products.value.map(product => [product.id, { author: '', text: '', rating: 5 }])
    )
    
    console.debug('[PRODUCTS] newComments initialized with keys:', Object.keys(newComments.value))
  } catch (error) {
    console.error('[PRODUCTS] Failed to load:', error)
    alert('❌ Failed to load products: ' + (error.message || 'Unknown error'))
  }
}

async function loadBranches() {
  try {
    const { data } = await axios.get('/api/public/branches')
    branches.value = Array.isArray(data) ? data : []
  } catch (error) {
    console.error('Failed to load branches:', error)
    branches.value = []
  }
}

function onBranchChange() {
  loadProducts()
  loadComments()
}

async function loadApprovedPositions() {
  try {
    const { data } = await axios.get('/api/public/positions/approved')
    approvedPositions.value = Array.isArray(data?.approved_positions) ? data.approved_positions : []
  } catch (error) {
    console.error('Failed to load approved positions:', error)
    approvedPositions.value = []
  }
}

async function loadComments() {

  try {
    const { data } = await axios.get('/api/product-comments')
    const grouped = data.reduce((acc, comment) => {
      if (!acc[comment.product_id]) acc[comment.product_id] = []
      acc[comment.product_id].push(comment)
      return acc
    }, {})

    products.value.forEach(product => {
      product.comments = grouped[product.id] || []
    })
  } catch (error) {
    console.error('Failed to load comments:', error)
  }
}

async function submitComment(productId) {
  if (!googleUser.value) {
    alert('Please sign in with Google to comment!')
    return
  }

  const comment = newComments.value[productId]
  if (!comment.text.trim() || !comment.rating) {
    alert('Please enter comment and rating!')
    return
  }

  try {
    // Truncate author to 60 characters (database constraint)
    // Prefer using name, but fallback to email and truncate if necessary
    let author = googleUser.value.name || googleUser.value.email
    if (author && author.length > 60) {
      author = author.substring(0, 60)
    }

    const payload = {
      product_id: productId,
      author: author,
      text: comment.text.trim(),
      rating: comment.rating
    }

    console.debug('[COMMENT] Submitting payload:', payload)

    const { data } = await axios.post('/api/product-comments', payload)

    const product = products.value.find(p => p.id === productId)
    if (product) {
      product.comments.unshift(data)
    }

    newComments.value[productId] = { author: '', text: '', rating: 5 }
    alert('Comment posted successfully!')
  } catch (error) {
    console.error('[COMMENT] Failed to submit:', error)
    
    // Show detailed error message from validation
    let errorMsg = 'Unable to post comment right now. Please try again.'
    if (error.response?.status === 422 && error.response?.data?.errors) {
      const errors = error.response.data.errors
      const errorField = Object.keys(errors)[0]
      if (errorField) {
        errorMsg = `Validation error: ${errors[errorField][0]}`
      }
    } else if (error.response?.data?.message) {
      errorMsg = error.response.data.message
    }
    
    alert(errorMsg)
  }
}


function setRating(productId, rating) {
  newComments.value[productId].rating = rating
}

function toggleEmojiPicker(productId) {
  activeEmojiPicker.value = activeEmojiPicker.value === productId ? null : productId
}

function insertEmoji(productId, emoji) {
  newComments.value[productId].text += emoji
}

function scrollToScaffold1() {
  const target = document.getElementById('scaffold-1')
  if (target) {
    target.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }
}

function scrollToScaffold3() {
  const target = document.getElementById('scaffold-3')
  if (target) {
    target.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }
}

function toggleReply(commentId, productId) {
  if (activeReplyCommentId.value === commentId) {
    activeReplyCommentId.value = null
    replyData.value = { author: '', text: '' }
  } else {
    activeReplyCommentId.value = commentId
    replyData.value = { author: '', text: '' }
  }
}

function cancelReply() {
  activeReplyCommentId.value = null
  replyData.value = { author: '', text: '' }
}

async function submitReply(productId, parentCommentId) {
  if (!googleUser.value) {
    alert('Please sign in with Google to reply!')
    return
  }

  if (!replyData.value.text.trim()) {
    alert('Please enter your reply text.')
    return
  }

  try {
    const { data } = await axios.post('/api/product-comment-replies', {
      parent_comment_id: parentCommentId,
      author: googleUser.value.email,
      text: replyData.value.text.trim()
    })

    const product = products.value.find(p => p.id === productId)
    if (product) {
      const comment = product.comments.find(c => c.id === parentCommentId)
      if (comment) {
        if (!comment.replies) comment.replies = []
        comment.replies.push(data)
      }
    }

    replyData.value = { author: googleUser.value.email, text: '' }
    activeReplyCommentId.value = null
  } catch (error) {
    console.error('Failed to submit reply:', error)
    alert('Unable to post reply right now. Please try again.')
  }
}

function getAverageRating(product) {
  if (!product.comments.length) return 0
  const total = product.comments.reduce((sum, c) => sum + (c.rating || 0), 0)
  return Math.round(total / product.comments.length)
}

function getAverageLabel(product) {
  if (!product.comments.length) return 'No ratings'
  const total = product.comments.reduce((sum, c) => sum + (c.rating || 0), 0)
  const avg = total / product.comments.length
  return `${avg.toFixed(1)} / 5`
}

function formatDate(date) {
  if (!date) return ''
  const d = new Date(date)
  return d.toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' })
}

function goToAdminLogin() {
  if (showLoginLoader.value) return
  loaderText.value = 'Loading admin login...'
  showLoginLoader.value = true

  try { sessionStorage.setItem('skipRouteOverlay', '1') } catch (e) {}

  setTimeout(() => {
    router.push('/staff-landing')
  }, 1000)
}

function openAuthModal() {
  showAuthModal.value = true
  authStep.value = 1
  authEmail.value = ''
  verificationCode.value = ''
  authUsername.value = ''
  authPassword.value = ''
  authPasswordConfirm.value = ''
}

function closeAuthModal() {
  showAuthModal.value = false
}

function checkPasswordStrength() {
  const password = authPassword.value

  // Check requirements
  passwordChecks.value = {
    length: password.length >= 8,
    uppercase: /[A-Z]/.test(password),
    lowercase: /[a-z]/.test(password),
    number: /[0-9]/.test(password)
  }

  // Calculate strength
  const checks = Object.values(passwordChecks.value)
  const metCount = checks.filter(Boolean).length

  isPasswordValid.value = metCount === 4

  if (metCount === 0) {
    passwordStrength.value = { text: '', class: '', width: '0%' }
  } else if (metCount === 1) {
    passwordStrength.value = { text: 'Very Weak', class: 'very-weak', width: '25%' }
  } else if (metCount === 2) {
    passwordStrength.value = { text: 'Weak', class: 'weak', width: '50%' }
  } else if (metCount === 3) {
    passwordStrength.value = { text: 'Good', class: 'good', width: '75%' }
  } else {
    passwordStrength.value = { text: 'Strong', class: 'strong', width: '100%' }
  }
}

function switchToLogin() {
  authStep.value = 5
  authPassword.value = ''
  authUsername.value = ''
}

function switchToDirectLogin() {
  authStep.value = 5
  authPassword.value = ''
  authUsername.value = ''
}

function switchToSignUp() {
  authStep.value = 1
  authEmail.value = ''
  authPassword.value = ''
  authUsername.value = ''
  authPasswordConfirm.value = ''
  verificationCode.value = ''
}

async function sendVerificationCode() {
  const email = authEmail.value.trim()
  if (!email || !email.includes('@')) {
    otpError.value = 'Please enter a valid email address.'
    return
  }
  if (otpResendCooldown.value > 0) return
  authLoading.value = true
  otpError.value = ''
  let retries = 0
  const maxRetries = 2
  while (retries <= maxRetries) {
    try {
      const { data } = await axios.post('/api/auth/send-verification', { email })
      authStep.value = 2
      otpError.value = ''
      startOtpResendCooldown()
      break
    } catch (error) {
      retries++
      const status = error.response?.status
      // If server rate-limited us, show message and stop retrying
      if (status === 429) {
        otpError.value = error.response?.data?.message || 'Too many verification requests. Please try later.'
        break
      }
      if (retries > maxRetries) {
        otpError.value = error.response?.data?.message || 'Failed to send verification code. Please try again.'
        break
      }
      await new Promise(res => setTimeout(res, 1000 * retries))
    }
  }
  authLoading.value = false
}

async function verifyCode() {
  const code = verificationCode.value.trim()
  if (!code || code.length !== 6) {
    otpError.value = 'Please enter the 6-digit code.'
    return
  }
  authLoading.value = true
  otpError.value = ''
  let retries = 0
  const maxRetries = 2
  while (retries <= maxRetries) {
    try {
      const { data } = await axios.post('/api/auth/verify-code', {
        email: authEmail.value,
        code: code
      })
      if (data.user_exists) {
        authStep.value = 4 // Login
      } else {
        authStep.value = 3 // Register
      }
      otpError.value = ''
      break
    } catch (error) {
      retries++
      if (retries > maxRetries) {
        otpError.value = error.response?.data?.message || 'Invalid verification code. Please try again.'
        break
      }
      await new Promise(res => setTimeout(res, 1000 * retries))
    }
  }
  authLoading.value = false
}
function startOtpResendCooldown() {
  otpResendCooldown.value = otpResendTotal
  if (otpResendTimer) clearInterval(otpResendTimer)
  otpResendTimer = setInterval(() => {
    otpResendCooldown.value--
    if (otpResendCooldown.value <= 0) {
      clearInterval(otpResendTimer)
      otpResendTimer = null
    }
  }, 1000)
}
onUnmounted(() => {
  if (otpResendTimer) clearInterval(otpResendTimer)
})
// Add minimal styles for progress bar and error
/* Add to <style> or your CSS file if not present */
/*
.otp-error {
  color: #dc2626;
  margin-bottom: 0.5rem;
  font-size: 0.95em;
}
.otp-progress-bar {
  position: relative;
  height: 8px;
  background: #eee;
  border-radius: 4px;
  margin-bottom: 10px;
  margin-top: 8px;
}
.otp-progress-fill {
  background: #f59e42;
  height: 100%;
  border-radius: 4px;
  transition: width 0.3s;
}
.otp-progress-text {
  position: absolute;
  left: 50%;
  top: 50%;
  transform: translate(-50%, -50%);
  font-size: 0.9em;
  color: #f59e42;
}
*/

async function createAccount() {
  if (!authUsername.value.trim()) {
    alert('Please enter a username')
    return
  }

  if (!isPasswordValid.value) {
    alert('Password must be at least 8 characters and contain uppercase, lowercase, and numbers')
    return
  }

  if (authPassword.value !== authPasswordConfirm.value) {
    alert('Passwords do not match')
    return
  }

  authLoading.value = true

  try {
    const { data } = await axios.post('/api/auth/register', {
      email: authEmail.value,
      username: authUsername.value.trim(),
      password: authPassword.value,
      verification_code: verificationCode.value
    })

    // Set user data
    googleUser.value = {
      email: data.user.email,
      name: data.user.username,
      picture: null,
      id: data.user.id
    }

    localStorage.setItem('googleUser', JSON.stringify(googleUser.value))
    localStorage.setItem('token', data.token)

    // Update comment forms
    products.value.forEach(product => {
      newComments.value[product.id].author = googleUser.value.email
    })

    closeAuthModal()
    alert('Account created successfully! You can now comment.')
  } catch (error) {
    console.error('Registration failed:', error)
    alert(error.response?.data?.message || 'Failed to create account. Please try again.')
  } finally {
    authLoading.value = false
  }
}

async function loginUser() {
  if (!authUsername.value.trim() || !authPassword.value) {
    alert('Please enter username and password')
    return
  }

  authLoading.value = true

  try {
    const { data } = await axios.post('/api/auth/login', {
      username: authUsername.value.trim(),
      password: authPassword.value
    })

    // Set user data
    googleUser.value = {
      email: data.user.email,
      name: data.user.username,
      picture: null,
      id: data.user.id
    }

    localStorage.setItem('googleUser', JSON.stringify(googleUser.value))
    localStorage.setItem('token', data.token)

    // Update comment forms
    products.value.forEach(product => {
      newComments.value[product.id].author = googleUser.value.email
    })

    closeAuthModal()
    alert('Welcome back!')
  } catch (error) {
    console.error('Login failed:', error)
    alert(error.response?.data?.message || 'Invalid credentials. Please try again.')
  } finally {
    authLoading.value = false
  }
}

function loadGoogleUser() {
  const stored = localStorage.getItem('googleUser')
  if (stored) {
    try {
      googleUser.value = JSON.parse(stored)
      // Update all comment forms with email
      products.value.forEach(product => {
        newComments.value[product.id].author = googleUser.value.email
      })
    } catch (error) {
      console.error('Failed to load stored user:', error)
      localStorage.removeItem('googleUser')
    }
  }
}

function signOut() {
  googleUser.value = null
  localStorage.removeItem('googleUser')
  // Clear author fields
  products.value.forEach(product => {
    newComments.value[product.id].author = ''
  })
  replyData.value.author = ''
}
</script>

<style src="../css/adminpanel.css"></style>
