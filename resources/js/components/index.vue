<template>
  <main class="page">
    <!-- CHIKIN TAYO LOADING OVERLAY PAG CLICK NG LOGIN -->
    <LoadingOverlay :show="showLoginLoader" :text="loaderText" :logo-src="mrLoaderImg" />

    <!-- Professional Navigation Bar -->
    <nav class="navbar" :class="{ 'navbar-scrolled': scrolled }">
      <div class="navbar-container">
        <div class="navbar-brand">
          <img :src="chikintayoImg" alt="Chikin Tayo" class="navbar-logo" />
          <span class="navbar-brand-text">CHIKIN TAYO</span>
        </div>

        <button 
          class="navbar-toggle" 
          :class="{ active: mobileMenuOpen }"
          @click="toggleMobileMenu"
          aria-label="Toggle navigation menu"
        >
          <span></span>
          <span></span>
          <span></span>
        </button>

        <div class="navbar-menu" :class="{ active: mobileMenuOpen }">
          <a href="#hero" class="navbar-link" @click="scrollToSection('hero')">Home</a>
          <a href="#branches" class="navbar-link" @click="scrollToSection('branches')">Branches</a>
          <a href="#menu" class="navbar-link" @click="scrollToSection('menu')">Menu</a>
          <a href="#careers" class="navbar-link" @click="scrollToSection('careers')">Careers</a>
          <a href="#about" class="navbar-link" @click="scrollToSection('about')">About</a>
          <a href="#contact" class="navbar-link" @click="scrollToSection('contact')">Contact Admin</a>
        </div>
      </div>
    </nav>

    <section class="hero" id="hero">
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
    <section class="scaffold scaffold-white" id="branches">
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
    <section class="scaffold scaffold-orange" id="menu">
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
    <section class="scaffold scaffold-white scaffold-4" id="careers">
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

            <div class="position-actions">
              <button type="button" class="btn-apply-now" @click="openApplyModal(p)">
                Apply Now
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- SCAFFOLD 5: WHITE SECTION -->
    <section class="scaffold scaffold-white scaffold-5" id="about">
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

    <!-- Contact Admin Section -->
    <CustomerContactForm />
  </main>

  <footer class="site-footer">
    <div class="site-footer-inner">
      <div class="site-footer-brand">
        <img :src="chikintayoImg" alt="Chikin Tayo" class="site-footer-logo" />
        <div>
          <strong>CHIKIN TAYO</strong>
          <p>Korean-inspired comfort food, snacks, and happy moments.</p>
        </div>
      </div>

      <nav class="site-footer-links" aria-label="Footer navigation">
        <h2>Explore</h2>
        <a href="#hero" @click="scrollToSection('hero')">Home</a>
        <a href="#branches" @click="scrollToSection('branches')">Branches</a>
        <a href="#menu" @click="scrollToSection('menu')">Menu</a>
        <a href="#careers" @click="scrollToSection('careers')">Careers</a>
        <a href="#about" @click="scrollToSection('about')">About</a>
        <a href="#contact" @click="scrollToSection('contact')">Contact Admin</a>
      </nav>

      <div class="site-footer-contact">
        <h2>Visit Us</h2>
        <p>4606 Mangubat Ave, Zone 4<br />Dasmariñas, Cavite</p>
        <a href="#branches" @click="scrollToSection('branches')">View all branches <span aria-hidden="true">→</span></a>
      </div>

      <div class="site-footer-hours">
        <h2>Hours</h2>
        <p>Monday - Sunday</p>
        <strong>10:00 AM - 10:00 PM</strong>
        <a href="#contact" @click="scrollToSection('contact')">Send us a message <span aria-hidden="true">→</span></a>
      </div>
    </div>
    <div class="site-footer-bottom">
      <span>© {{ new Date().getFullYear() }} Chikin Tayo. All rights reserved.</span>
      <span>Good food. Warm vibes. Happy tummies.</span>
    </div>
  </footer>

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

  <!-- Apply Now Modal -->
  <teleport to="body">
    <transition name="modal">
      <div v-if="showApplyModal" class="modal-overlay" @click.self="closeApplyModal">
        <div class="apply-modal">
          <div class="apply-modal-header">
            <div class="apply-modal-title">
              <h2>Apply for <span class="apply-modal-job">{{ applyForm.job_title }}</span></h2>
              <div class="apply-modal-kicker">
                <span v-if="applyForm.department" class="apply-pill">{{ applyForm.department }}</span>
                <span v-if="applyForm.branch_name" class="apply-pill">{{ applyForm.branch_name }}</span>
                <span class="apply-pill">Job ID: {{ applyForm.job_id }}</span>
              </div>
            </div>
            <button class="modal-close" type="button" aria-label="Close" @click="closeApplyModal">✕</button>
          </div>

          <div class="apply-form">
            <div v-if="applyErrorMessage" class="banner banner--error">{{ applyErrorMessage }}</div>
            <div v-if="applySuccessMessage" class="banner banner--success">{{ applySuccessMessage }}</div>

            <form @submit.prevent="submitApplyNow" class="apply-modal-body">
              <div class="apply-grid">
                <div class="apply-col">
                  <!-- Left Column -->
                  <div class="field" :class="{ 'field--error': applyFieldErrors.full_name }">
                    <label for="full_name" class="field-label">
                      Full Name <span class="req">*</span>
                    </label>
                    <input id="full_name" v-model.trim="applyForm.full_name" type="text" placeholder="e.g., Juan Dela Cruz" />
                    <div class="inline-error" v-if="applyFieldErrors.full_name">{{ applyFieldErrors.full_name }}</div>
                  </div>

                  <div class="field" :class="{ 'field--error': applyFieldErrors.email }">
                    <label for="email" class="field-label">
                      Email <span class="req">*</span>
                    </label>
                    <input id="email" v-model.trim="applyForm.email" type="email" placeholder="you@example.com" />
                    <div class="inline-error" v-if="applyFieldErrors.email">{{ applyFieldErrors.email }}</div>
                  </div>

                  <div class="field" :class="{ 'field--error': applyFieldErrors.phone }">
                    <label for="phone" class="field-label">
                      Phone <span class="req">*</span>
                    </label>
                    <input id="phone" v-model.trim="applyForm.phone" type="tel" placeholder="09xxxxxxxxx" />
                    <div class="inline-error" v-if="applyFieldErrors.phone">{{ applyFieldErrors.phone }}</div>
                  </div>

                  <div class="field" :class="{ 'field--error': applyFieldErrors.years_of_experience }">
                    <label for="years_of_experience" class="field-label">
                      Years of Experience <span class="req">*</span>
                    </label>
                    <input id="years_of_experience" v-model.trim.number="applyForm.years_of_experience" type="number" min="0" step="1" />
                    <div class="inline-error" v-if="applyFieldErrors.years_of_experience">{{ applyFieldErrors.years_of_experience }}</div>
                  </div>

                  <div class="field" :class="{ 'field--error': applyFieldErrors.education }">
                    <label for="education" class="field-label">
                      Education <span class="req">*</span>
                    </label>
                    <input id="education" v-model.trim="applyForm.education" type="text" placeholder="e.g., BSIT - University" />
                    <div class="inline-error" v-if="applyFieldErrors.education">{{ applyFieldErrors.education }}</div>
                  </div>
                </div>

                <div class="field" style="grid-column: 1 / -1;" :class="{ 'field--error': applyFieldErrors.address }">
                  <label class="field-label">
                    Address & Location <span class="req">*</span>
                  </label>

                  <div v-if="applyAddressSaved" class="address-saved-card">
                    <div class="address-card-header">
                      <div class="address-card-content">
                        <strong class="address-label">Pinned Address</strong>
                        <span class="address-text">{{ savedApplyAddress }}</span>
                        <div v-if="applyLocation.lat && applyLocation.lng" class="address-coordinates">
                          📍 {{ applyLocation.lat?.toFixed(6) }}, {{ applyLocation.lng?.toFixed(6) }}
                        </div>
                      </div>
                      <div class="address-card-actions">
                        <button type="button" class="address-edit-btn" @click="editApplyAddress">Edit</button>
                      </div>
                    </div>
                  </div>

                  <div v-else class="address-input-section">
                    <AddressCascaderWithMap
                      :initialLocation="applyLocation"
                      @update:location="onApplyLocationUpdate"
                      @save:location="onApplyAddressSaved"
                    />
                  </div>

                  <div class="inline-error" v-if="applyFieldErrors.address">{{ applyFieldErrors.address }}</div>
                </div>

                <!-- Right Column -->
                <div class="apply-col">
                  <div class="field" :class="{ 'field--error': applyFieldErrors.available_start_date }">
                    <label for="available_start_date" class="field-label">
                      Available Start Date <span class="req">*</span>
                    </label>
                    <input id="available_start_date" v-model="applyForm.available_start_date" type="date" />
                    <div class="inline-error" v-if="applyFieldErrors.available_start_date">{{ applyFieldErrors.available_start_date }}</div>
                  </div>

                  <div class="field" :class="{ 'field--error': applyFieldErrors.linkedin_url }">
                    <label for="linkedin_url" class="field-label">
                      LinkedIn <span class="opt">(Optional)</span>
                    </label>
                    <input id="linkedin_url" v-model.trim="applyForm.linkedin_url" type="url" placeholder="https://linkedin.com/in/..." />
                    <div class="inline-error" v-if="applyFieldErrors.linkedin_url">{{ applyFieldErrors.linkedin_url }}</div>
                  </div>

                  <div class="field" :class="{ 'field--error': applyFieldErrors.portfolio_url }">
                    <label for="portfolio_url" class="field-label">
                      Portfolio <span class="opt">(Optional)</span>
                    </label>
                    <input id="portfolio_url" v-model.trim="applyForm.portfolio_url" type="url" placeholder="https://your-portfolio.com" />
                    <div class="inline-error" v-if="applyFieldErrors.portfolio_url">{{ applyFieldErrors.portfolio_url }}</div>
                  </div>

                  <div class="field" :class="{ 'field--error': applyFieldErrors.resume_cv }">
                    <label class="field-label">
                      Resume/CV <span class="req">*</span>
                    </label>

                    <div class="upload-box" :class="{ 'upload-box--error': applyFieldErrors.resume_cv }">
                      <div class="upload-box-inner">
                        <div class="upload-icon">📄</div>
                        <div class="upload-title">📄 Upload Resume</div>
                        <div class="upload-sub">Drag & drop or <span class="upload-link">Choose File</span></div>
                      </div>
                      <input
                        id="resume_cv"
                        class="upload-input"
                        type="file"
                        accept=".pdf,.doc,.docx"
                        @change="onResumeSelected"
                      />
                    </div>

                    <div class="file-hint" v-if="resumeCvFile">Selected: <b>{{ resumeCvFile.name }}</b></div>
                    <div class="file-hint" v-else>PDF/DOC/DOCX only (max 5MB)</div>
                    <div class="inline-error" v-if="applyFieldErrors.resume_cv">{{ applyFieldErrors.resume_cv }}</div>
                  </div>

                  <div class="field" :class="{ 'field--error': applyFieldErrors.supporting_documents }">
                    <label class="field-label">
                      Supporting Documents <span class="opt">(Optional)</span>
                    </label>

                    <div class="upload-box">
                      <div class="upload-box-inner">
                        <div class="upload-icon">🗂️</div>
                        <div class="upload-title">Upload Files</div>
                        <div class="upload-sub">Drag & drop or choose files</div>
                      </div>
                      <input
                        id="supporting_documents"
                        class="upload-input"
                        type="file"
                        multiple
                        accept=".pdf,.doc,.docx"
                        @change="onSupportingDocsSelected"
                      />
                    </div>

                    <div class="file-hint" v-if="supportingDocumentsFiles?.length">
                      Selected: <b>{{ supportingDocumentsFiles.length }}</b> file(s)
                    </div>
                    <div class="file-hint" v-else>You can upload multiple files (max 10MB each)</div>
                    <div class="inline-error" v-if="applyFieldErrors.supporting_documents">{{ applyFieldErrors.supporting_documents }}</div>
                  </div>
                </div>
              </div>

              <div class="apply-stack">
                <div class="field" style="grid-column: 1 / -1;" :class="{ 'field--error': applyFieldErrors.cover_letter }">
                  <label for="cover_letter" class="field-label">
                    Cover Letter <span class="req">*</span>
                  </label>
                  <textarea id="cover_letter" v-model.trim="applyForm.cover_letter" placeholder="Tell us about yourself..." ></textarea>
                  <div class="inline-error" v-if="applyFieldErrors.cover_letter">{{ applyFieldErrors.cover_letter }}</div>
                </div>

                <div class="field consent-field" style="grid-column: 1 / -1;">
                  <div class="check-row">
                    <input type="checkbox" id="privacy_consent" v-model="applyForm.privacy_consent" />
                    <label for="privacy_consent" style="cursor:pointer;">
                      I consent to the collection and use of my application data for recruitment purposes. <span class="req">*</span>
                    </label>
                  </div>
                  <div class="inline-error" v-if="applyFieldErrors.privacy_consent">{{ applyFieldErrors.privacy_consent }}</div>
                </div>

                <!-- Honeypot (hidden) -->
                <input type="text" v-model="applyForm.website" name="website" autocomplete="off" style="display:none;" />

                <div class="apply-actions">
                  <button type="button" class="btn btn-cancel" @click="closeApplyModal" :disabled="applyLoading">Cancel</button>
                  <button type="submit" class="btn btn-primary" :disabled="applyLoading">
                    {{ applyLoading ? 'Submitting...' : 'Submit Application' }}
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </transition>
  </teleport>

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
import AddressCascaderWithMap from './AddressCascaderWithMap.vue'
import CustomerContactForm from './CustomerContactForm.vue'

const router = useRouter()
const showLoginLoader = ref(false)
const loaderText = ref('Loading admin login...')
const showScrollTop = ref(false)
const hideAdminLogin = ref(false)
const scrolled = ref(false)
const mobileMenuOpen = ref(false)

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

// Apply modal state
const showApplyModal = ref(false)
const applyLoading = ref(false)
const applySuccessMessage = ref('')
const applyErrorMessage = ref('')
const applyFieldErrors = ref({})
const applyLocation = ref({ lat: 14.5994, lng: 120.9842 })
const savedApplyAddress = ref('')
const applyAddressSaved = ref(false)

const applyForm = ref({
  job_open_request_id: null,
  job_title: '',
  position_id: null,
  branch_id: null,
  branch_name: '',
  department: '',
  job_id: '',

  full_name: '',
  email: '',
  phone: '',
  address: '',
  cover_letter: '',
  years_of_experience: '',
  education: '',
  available_start_date: '',

  linkedin_url: '',
  portfolio_url: '',

  privacy_consent: false,
  website: '', // honeypot
})

const resumeCvFile = ref(null)
const supportingDocumentsFiles = ref([])

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
  scrolled.value = window.scrollY > 50
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

  // Apply modal ESC close
  window.addEventListener('keydown', (evt) => {
    if (evt.key === 'Escape' && showApplyModal.value) {
      closeApplyModal()
    }
  })


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
  window.addEventListener('resize', handleResize)
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
  const target = document.getElementById('hero')
  if (target) {
    target.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }
}

function scrollToScaffold3() {
  const target = document.getElementById('menu')
  if (target) {
    target.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }
}

function scrollToSection(sectionId) {
  const target = document.getElementById(sectionId)
  if (target) {
    target.scrollIntoView({ behavior: 'smooth', block: 'start' })
    // Close mobile menu after clicking
    mobileMenuOpen.value = false
  }
}

function toggleMobileMenu() {
  mobileMenuOpen.value = !mobileMenuOpen.value
}

function handleResize() {
  if (window.innerWidth > 768 && mobileMenuOpen.value) {
    mobileMenuOpen.value = false
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

function resetApplyForm() {
  applyForm.value = {
    job_open_request_id: null,
    job_title: '',
    position_id: null,
    branch_id: null,
    branch_name: '',
    department: '',
    job_id: '',

    full_name: '',
    email: '',
    phone: '',
    address: '',
    cover_letter: '',
    years_of_experience: '',
    education: '',
    available_start_date: '',

    linkedin_url: '',
    portfolio_url: '',

    privacy_consent: false,
    website: '',
  }
  applyLocation.value = { lat: 14.5994, lng: 120.9842 }
  savedApplyAddress.value = ''
  applyAddressSaved.value = false
  resumeCvFile.value = null
  supportingDocumentsFiles.value = []
  applyLoading.value = false
  applySuccessMessage.value = ''
  applyErrorMessage.value = ''
  applyFieldErrors.value = {}
}

function openApplyModal(p) {
  // p is an approved position record from backend
  resetApplyForm()
  applyFieldErrors.value = {}
  applyErrorMessage.value = ''

  applyForm.value.job_open_request_id = p.id
  applyForm.value.job_id = p.id
  applyForm.value.position_id = p.position_id
  applyForm.value.job_title = p.position_name || ''
  applyForm.value.department = p.department || ''
  applyForm.value.branch_id = p.branch_id ?? null
  applyForm.value.branch_name = p.branch_name || ''

  // Prefill branch/department/job fields only; applicant details remain blank.
  showApplyModal.value = true
}

function closeApplyModal() {
  showApplyModal.value = false
  // keep values only until close; reset to be safe
  resetApplyForm()
}

function onApplyLocationUpdate(location) {
  applyLocation.value = {
    lat: location?.lat ?? applyLocation.value.lat,
    lng: location?.lng ?? applyLocation.value.lng,
  }
}

function onApplyAddressSaved(payload) {
  const addressComponents = payload?.addressComponents || {}
  const parts = []
  const lat = payload?.lat ?? applyLocation.value.lat
  const lng = payload?.lng ?? applyLocation.value.lng

  if (payload?.address && payload.address.trim()) parts.push(payload.address.trim())
  if (addressComponents.barangay) parts.push(addressComponents.barangay)
  if (addressComponents.city) parts.push(addressComponents.city)
  if (addressComponents.province) parts.push(addressComponents.province)
  if (addressComponents.region) parts.push(addressComponents.region)

  savedApplyAddress.value = parts.join(', ') || `Pinned location: ${Number(lat).toFixed(6)}, ${Number(lng).toFixed(6)}`
  applyForm.value.address = savedApplyAddress.value
  applyLocation.value = {
    lat,
    lng,
  }
  applyAddressSaved.value = true
}

function editApplyAddress() {
  applyAddressSaved.value = false
  savedApplyAddress.value = ''
  applyForm.value.address = ''
}

function onResumeSelected(e) {
  const file = e.target.files?.[0] || null
  applyFieldErrors.value.resume_cv = ''
  resumeCvFile.value = file
}

function onSupportingDocsSelected(e) {
  const files = Array.from(e.target.files || [])
  applyFieldErrors.value.supporting_documents = ''
  supportingDocumentsFiles.value = files
}

function getCsrfToken() {
  // If app is rendered with a csrf meta tag, use it.
  const meta = document.querySelector('meta[name="csrf-token"]')
  return meta ? meta.getAttribute('content') : ''
}

function validateClientSideBeforeSubmit() {
  const errors = {}

  if (!applyForm.value.full_name.trim()) errors.full_name = 'Full name is required.'
  if (!applyForm.value.email.trim()) errors.email = 'Email is required.'
  else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(applyForm.value.email)) errors.email = 'Please enter a valid email.'

  if (!applyForm.value.phone.trim()) errors.phone = 'Phone is required.'
  if (!applyForm.value.address.trim()) errors.address = 'Address is required.'
  if (!applyForm.value.cover_letter.trim()) errors.cover_letter = 'Cover letter is required.'

  if (applyForm.value.years_of_experience === '' || applyForm.value.years_of_experience === null) {
    errors.years_of_experience = 'Years of experience is required.'
  }

  if (!String(applyForm.value.education).trim()) errors.education = 'Education is required.'
  if (!applyForm.value.available_start_date) errors.available_start_date = 'Available start date is required.'

  if (!resumeCvFile.value) errors.resume_cv = 'Resume/CV is required.'

  if (!applyForm.value.privacy_consent) errors.privacy_consent = 'Please confirm data privacy consent.'

  // File type validation (client)
  if (resumeCvFile.value) {
    const allowed = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/octet-stream']
    const nameOk = /\.(pdf|doc|docx)$/i.test(resumeCvFile.value.name)
    if (!nameOk) errors.resume_cv = 'Resume must be a PDF, DOC, or DOCX file.'
  }

  if (supportingDocumentsFiles.value?.length) {
    const bad = supportingDocumentsFiles.value.some(f => !/\.(pdf|doc|docx)$/i.test(f.name))
    if (bad) errors.supporting_documents = 'Supporting documents must be PDF, DOC, or DOCX.'
  }

  applyFieldErrors.value = errors
  return Object.keys(errors).length === 0
}

async function submitApplyNow() {
  console.log('[APPLY] submitApplyNow called')
  console.log('[APPLY] applyForm', JSON.parse(JSON.stringify(applyForm.value)))
  console.log('[APPLY] resumeCvFile', resumeCvFile.value)
  console.log('[APPLY] supportingDocumentsFiles', supportingDocumentsFiles.value)
  if (applyLoading.value) return

  applyErrorMessage.value = ''
  applySuccessMessage.value = ''

  const ok = validateClientSideBeforeSubmit()
  if (!ok) return

  if (!applyForm.value.job_open_request_id) {
    applyErrorMessage.value = 'Invalid job application context.'
    return
  }

  applyLoading.value = true

  try {
    const fd = new FormData()

    fd.append('position_open_request_id', String(applyForm.value.job_open_request_id))
    fd.append('position_id', String(applyForm.value.position_id))
    if (applyForm.value.branch_id) fd.append('branch_id', String(applyForm.value.branch_id))
    fd.append('department', String(applyForm.value.department || ''))
    fd.append('job_title', String(applyForm.value.job_title || ''))

    fd.append('full_name', applyForm.value.full_name)
    fd.append('email', applyForm.value.email)
    fd.append('phone', applyForm.value.phone)
    fd.append('address', applyForm.value.address)
    fd.append('cover_letter', applyForm.value.cover_letter)
    fd.append('years_of_experience', String(applyForm.value.years_of_experience))
    fd.append('education', applyForm.value.education)
    fd.append('available_start_date', applyForm.value.available_start_date)

    if (applyForm.value.linkedin_url) fd.append('linkedin_url', applyForm.value.linkedin_url)
    if (applyForm.value.portfolio_url) fd.append('portfolio_url', applyForm.value.portfolio_url)

    fd.append('privacy_consent', applyForm.value.privacy_consent ? '1' : '0')
    if (applyForm.value.website) fd.append('website', applyForm.value.website)

    if (resumeCvFile.value) {
      fd.append('resume_cv', resumeCvFile.value)
    }

    if (supportingDocumentsFiles.value?.length) {
      supportingDocumentsFiles.value.forEach((file) => {
        fd.append('supporting_documents[]', file)
      })
      // Laravel expects supporting_documents as array; sending [] will match.
    }

    const headers = {
      'X-Requested-With': 'XMLHttpRequest',
    }
    const csrf = getCsrfToken()
    if (csrf) headers['X-CSRF-TOKEN'] = csrf

    const res = await fetch('/api/public/positions/apply', {
      method: 'POST',
      headers,
      body: fd,
    })

    const payload = await res.json().catch(() => null)

    if (!res.ok) {
      if (res.status === 422 && payload?.errors) {
        // Map Laravel errors to inline fields
        applyFieldErrors.value = Object.fromEntries(
          Object.entries(payload.errors).map(([k, v]) => [k, Array.isArray(v) ? v[0] : v])
        )
        applyErrorMessage.value = 'Please fix the highlighted fields.'
        return
      }
      applyErrorMessage.value = payload?.message || 'Unable to submit application right now.'
      return
    }

    applySuccessMessage.value = payload?.message || 'Application submitted successfully.'
    // Reset state after success
    resetApplyForm()
    // Keep modal visible momentarily? requirement says reset after successful submission.
  } catch (e) {
    console.error('submitApplyNow error', e)
    applyErrorMessage.value = 'Something went wrong. Please try again.'
  } finally {
    applyLoading.value = false
  }
}

</script>

<style src="../css/adminpanel.css"></style>
