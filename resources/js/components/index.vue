<template>
  <main class="page">
    <!-- CHIKIN TAYO LOADING OVERLAY PAG CLICK NG LOGIN -->
    <transition name="fade">
      <div v-if="showLoginLoader" class="mr-loader-overlay">
        <div class="mr-loader-box">
          <img :src="mrLoaderImg" alt="Loading" class="mr-loader-img" />
          <p>{{ loaderText }}</p>
        </div>
      </div>
    </transition>

    <section class="hero" id="scaffold-1">
      <!-- LEFT content -->
      <div class="hero-left">
        <div class="badge">
          <span class="badge-dot"></span>
          <span>Admin Control Center</span>
        </div>

        <h1>
          <span class="highlight-korean">Your Korean Snack & Fun Stop!</span> <br>
          <span>WELCOME TO CHIKIN TAYO!</span>
        </h1>

        <p class="subheading">
          <b>CHIKIN TAYO</b> serves chicken, ramen, corndogs, ice cream, and more all in a cozy, Instagram-worthy space. 
          Fun food, warm vibes, happy tummies!
        </p>

        <p class="admin-tagline">
          For <strong>authorized staff</strong> access only.
        </p>

        <div class="cta-row">
          <!-- ADMIN LOGIN ONLY -->
          <RouterLink
            to=""
            class="btn-login"
            @click.prevent="goToAdminLogin"
          >
            <span class="icon">🔑</span>
            <span>Login (Authorized Personnel Only)</span>
          </RouterLink>

          <span class="note">
            No sign up. Credentials are provided by the system administrator.
          </span>
        </div>

      </div>

      <!-- RIGHT side: big logo only -->
      <div class="hero-right">
        <div class="logo-wrapper flip-card">
          <div class="flip-card-inner">
            <div class="flip-card-front">
              <img :src="chikintayoImg" alt="Chikintayo" />
            </div>
            <div class="flip-card-back">
              <div class="flip-content">
                <h2>CHIKIN TAYO</h2>
                <p class="tagline">Korean-Inspired Food & Snack Shop</p>
                <p class="description">A popular spot in the Philippines offering trendy and comforting Korean-style meals and treats. Riding the growing popularity of Korean culture, we provide delicious, affordable, and visually appealing food in a cozy and Instagrammable setting.</p>
                <p class="specialties"><strong>Specialties:</strong> Crispy fried chicken, Korean-style corndogs, flavorful ramen, ice cream, and trendy Korean desserts.</p>
                <p class="offerings"><strong>More Than Food:</strong> We also offer coffee, milk-based drinks, and refreshers—making us a complete hub for casual dining and hangouts.</p>
                <p class="mission">Making Korean-style cuisine accessible and fun for the local community!</p>
              </div>
            </div>
          </div>
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
        <h2>What We Offer</h2>
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

    <!-- SCAFFOLD 4: WHITE SECTION -->
    <section class="scaffold scaffold-white scaffold-4">
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
            
            <input
              v-model="verificationCode"
              type="text"
              placeholder="Enter 6-digit code"
              class="auth-input code-input"
              maxlength="6"
              @keyup.enter="verifyCode"
            />
            
            <button 
              type="button" 
              class="btn-auth-primary"
              @click="verifyCode"
              :disabled="authLoading"
            >
              {{ authLoading ? 'Verifying...' : 'Verify Code' }}
            </button>
            
            <button type="button" class="btn-resend" @click="sendVerificationCode">
              Resend Code
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
import { ref, onMounted, onUnmounted } from 'vue'
import axios from 'axios'
import { useRouter, RouterLink } from 'vue-router'

const router = useRouter()
const showLoginLoader = ref(false)
const loaderText = ref('Loading admin login...')
const showScrollTop = ref(false)

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

const products = ref([
  { id: 1, name: 'Yangyeom', img: yangyeomImg, comments: [] },
  { id: 2, name: 'Snow Cheese', img: snowcheeseImg, comments: [] },
  { id: 3, name: 'Corndog', img: corndogImg, comments: [] },
  { id: 4, name: 'Pastries', img: pastriesImg, comments: [] },
  { id: 5, name: 'Ramen', img: ramenImg, comments: [] },
  { id: 6, name: 'Ice Cream', img: icecreamImg, comments: [] }
])

const newComments = ref(
  Object.fromEntries(
    products.value.map(product => [product.id, { author: '', text: '', rating: 5 }])
  )
)

function handleScroll() {
  showScrollTop.value = window.scrollY > 400
}

onMounted(() => {
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
  loadComments()
  loadGoogleUser()
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
})

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
    const { data } = await axios.post('/api/product-comments', {
      product_id: productId,
      author: googleUser.value.email,
      text: comment.text.trim(),
      rating: comment.rating
    })

    const product = products.value.find(p => p.id === productId)
    if (product) {
      product.comments.unshift(data)
    }

    newComments.value[productId] = { author: googleUser.value.email, text: '', rating: 5 }
  } catch (error) {
    console.error('Failed to submit comment:', error)
    alert('Unable to post comment right now. Please try again.')
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
    router.push('/admin-login')
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
    alert('Please enter a valid email address')
    return
  }
  
  authLoading.value = true
  
  try {
    const { data } = await axios.post('/api/auth/send-verification', {
      email: email
    })
    
    authStep.value = 2
    alert('Verification code sent to your email!')
  } catch (error) {
    console.error('Failed to send code:', error)
    alert(error.response?.data?.message || 'Failed to send verification code. Please try again.')
  } finally {
    authLoading.value = false
  }
}

async function verifyCode() {
  const code = verificationCode.value.trim()
  
  if (!code || code.length !== 6) {
    alert('Please enter the 6-digit code')
    return
  }
  
  authLoading.value = true
  
  try {
    const { data } = await axios.post('/api/auth/verify-code', {
      email: authEmail.value,
      code: code
    })
    
    // Check if user exists or needs to register
    if (data.user_exists) {
      authStep.value = 4 // Login
    } else {
      authStep.value = 3 // Register
    }
  } catch (error) {
    console.error('Verification failed:', error)
    alert(error.response?.data?.message || 'Invalid verification code. Please try again.')
  } finally {
    authLoading.value = false
  }
}

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
    localStorage.setItem('authToken', data.token)
    
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
    localStorage.setItem('authToken', data.token)
    
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

<style src="../css/index.css"></style>
