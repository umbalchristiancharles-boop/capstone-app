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
                      <input
                        v-model="replyData.author"
                        type="text"
                        placeholder="Your name"
                        class="reply-input"
                      />
                      <textarea
                        v-model="replyData.text"
                        placeholder="Write your reply..."
                        class="reply-textarea"
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
                    >★</button>
                  </div>
                </div>
                <input
                  v-model="newComments[product.id].author"
                  type="text"
                  placeholder="Your name"
                  class="comment-input"
                  @keyup.enter="submitComment(product.id)"
                />
                <div class="textarea-wrapper">
                  <textarea
                    v-model="newComments[product.id].text"
                    placeholder="Share your thoughts about this product..."
                    class="comment-textarea"
                    @keyup.enter.ctrl="submitComment(product.id)"
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
  const comment = newComments.value[productId]
  if (!comment.author.trim() || !comment.text.trim() || !comment.rating) {
    alert('Please enter name, comment, and rating!')
    return
  }

  try {
    const { data } = await axios.post('/api/product-comments', {
      product_id: productId,
      author: comment.author.trim(),
      text: comment.text.trim(),
      rating: comment.rating
    })

    const product = products.value.find(p => p.id === productId)
    if (product) {
      product.comments.unshift(data)
    }

    newComments.value[productId] = { author: '', text: '', rating: 5 }
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
  if (!replyData.value.author.trim() || !replyData.value.text.trim()) {
    alert('Please fill in both name and reply text.')
    return
  }

  try {
    const { data } = await axios.post('/api/product-comment-replies', {
      parent_comment_id: parentCommentId,
      author: replyData.value.author.trim(),
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

    replyData.value = { author: '', text: '' }
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

  setTimeout(() => {
    router.push('/admin-login')
  }, 1000)
}
</script>

<style src="../css/index.css"></style>
