<template>
  <div class="main-branch-hr-panel">
    <OwnerPanelLayout
      ref="ownerLayout"
      :userProfile="userProfile"
      :panelTitle="'Main Branch HR Management'"
      :panelDescription="'Human Resources management for Main Branch'"
      :enableProfileUpdate="true"
      :canEditProfile="false"
      :canChangePassword="true"
      :showHeader="false"
      :showProfileColumn="false"
      :showOwnerSidebar="true"
      :showOwnerTopbar="true"
      :notificationDetailsInModal="true"
      topbarLabel="HR - Main Branch"
      accountInfoStyle="finance"
      @logout="askLogout"
      @profile-updated="onProfileUpdated"
    >
      <template #ownerTopbarActions>
        <button
          ref="hrTutorialTrigger"
          class="main-branch-hr-tutorial-trigger"
          type="button"
          aria-label="Open Main Branch HR tutorial"
          title="Main Branch HR tutorial"
          @click="openHrTutorial"
        >?</button>
      </template>
      <template #ownerSidebar>
        <nav class="main-branch-hr-sidebar-nav" aria-label="HR sections">
          <button type="button" class="main-branch-hr-sidebar-link" style="display: grid !important; grid-template-columns: 1.8rem minmax(0, 1fr); align-items: center; column-gap: 0.45rem; text-align: left !important;" :style="hrSidebarItemStyle('overview')" :class="{ 'main-branch-hr-sidebar-link--active': activeHrSection === 'overview' }" :aria-current="activeHrSection === 'overview' ? 'page' : undefined" @mouseenter="hoveredHrSection = 'overview'" @mouseleave="hoveredHrSection = ''" @click="showHrSection('overview')">
            <span :style="hrSidebarIconStyle('overview')" aria-hidden="true"><svg style="display: block; width: 18px; height: 18px;" viewBox="0 0 32 32" fill="none"><path d="m8.5 15 7.5-6 7.5 6v8.5h-5v-5h-5v5h-5V15Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" /></svg></span><span style="min-width: 0; overflow-wrap: normal; word-break: normal;">HR Overview</span>
          </button>
          <button type="button" class="main-branch-hr-sidebar-link" style="display: grid !important; grid-template-columns: 1.8rem minmax(0, 1fr); align-items: center; column-gap: 0.45rem; text-align: left !important;" :style="hrSidebarItemStyle('requests')" :class="{ 'main-branch-hr-sidebar-link--active': activeHrSection === 'requests' }" :aria-current="activeHrSection === 'requests' ? 'page' : undefined" @mouseenter="hoveredHrSection = 'requests'" @mouseleave="hoveredHrSection = ''" @click="showHrSection('requests')">
            <span :style="hrSidebarIconStyle('requests')" aria-hidden="true"><svg style="display: block; width: 18px; height: 18px;" viewBox="0 0 32 32" fill="none"><path d="M11 8.5h7l4 4v11H11z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" /><path d="M18 8.5v4h4M16 15.5v5M13.5 18h5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" /></svg></span><span style="min-width: 0; overflow-wrap: normal; word-break: normal;">Position Requests</span>
          </button>
          <button type="button" class="main-branch-hr-sidebar-link" style="display: grid !important; grid-template-columns: 1.8rem minmax(0, 1fr); align-items: center; column-gap: 0.45rem; text-align: left !important;" :style="hrSidebarItemStyle('accounts')" :class="{ 'main-branch-hr-sidebar-link--active': activeHrSection === 'accounts' }" :aria-current="activeHrSection === 'accounts' ? 'page' : undefined" @mouseenter="hoveredHrSection = 'accounts'" @mouseleave="hoveredHrSection = ''" @click="showHrSection('accounts')">
            <span :style="hrSidebarIconStyle('accounts')" aria-hidden="true"><svg style="display: block; width: 18px; height: 18px;" viewBox="0 0 32 32" fill="none"><circle cx="13" cy="13" r="3" stroke="currentColor" stroke-width="1.7" /><path d="M7.5 23c.7-3 2.5-4.5 5.5-4.5s4.8 1.5 5.5 4.5M20 11a3 3 0 0 1 0 5.9M21 19c1.9.4 3.1 1.7 3.5 4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" /></svg></span><span style="min-width: 0; overflow-wrap: normal; word-break: normal;">Branch Accounts</span>
          </button>
        </nav>
      </template>

      <template #ownerSidebarFooter>
        <div class="main-branch-hr-sidebar-actions">
          <button type="button" class="main-branch-hr-sidebar-account" @click="ownerLayout?.openInfoModal()">Account Info</button>
          <button type="button" class="main-branch-hr-sidebar-logout" @click="askLogout">Logout</button>
        </div>
      </template>

      <template #main>
        <div class="main-branch-hr-page">
          <Transition name="main-branch-hr-section" mode="out-in">
          <div :key="activeHrSection" class="main-branch-hr-section-view">
          <template v-if="activeHrSection === 'overview'">
          <header id="hr-overview" class="main-branch-hr-hero">
            <div>
              <span class="main-branch-hr-eyebrow">HR dashboard</span>
              <h2 class="main-branch-hr-title">HR overview</h2>
              <p class="main-branch-hr-subtitle">Review position requests and manage workforce accounts across all branches.</p>
            </div>
            <div class="main-branch-hr-hero__actions">
              <button class="pill-btn main-branch-hr-hero__action" @click="loadPositionRequests" :disabled="loadingPositionRequests">
                {{ loadingPositionRequests ? 'Loading...' : 'Refresh Requests' }}
              </button>
            </div>
          </header>

          <div v-if="activeHrSection === 'overview'" class="info-box">
            <p>This panel provides HR functions for Main Branch headquarters personnel.</p>
            <p>Manage staff schedules, attendance, benefits, and performance from this location.</p>
          </div>

          <div class="hr-summary-grid" aria-label="HR summary">
            <div class="hr-summary-card hr-summary-card--pending">
              <span class="hr-summary-label">Pending Requests</span>
              <strong>{{ positionRequestsPendingCount }}</strong>
            </div>
            <div class="hr-summary-card">
              <span class="hr-summary-label">Branches</span>
              <strong>{{ branchSections.length }}</strong>
            </div>
            <div class="hr-summary-card">
              <span class="hr-summary-label">Total Accounts</span>
              <strong>{{ totalStaffCount }}</strong>
            </div>
            <div class="hr-summary-card hr-summary-card--active">
              <span class="hr-summary-label">Active Accounts</span>
              <strong>{{ activeStaffCount }}</strong>
            </div>
          </div>

          <section class="applications-section">
            <div class="section-header">
              <div>
                <h3>Job Applications</h3>
                <p class="muted">Applications submitted for positions on your branch.</p>
              </div>
              <span v-if="applicationsCount > 0" class="pending-badge">{{ applicationsCount }} total</span>
            </div>

            <div v-if="loadingApplications" class="loading-box">Loading applications...</div>
            <div v-else-if="applications.length === 0" class="empty-box">No applications found.</div>
            <div v-else class="positions-list">
              <div v-for="application in applications" :key="application.id" class="position-row">
                <div class="position-row__meta">
                  <div class="position-row__name">{{ application.applicant_full_name }}</div>
                  <div class="position-row__dept">{{ application.job_title }}</div>
                </div>
                <div class="request-card__info"><span class="label">Department:</span><span class="value">{{ application.department || '—' }}</span></div>
                <div class="request-card__info"><span class="label">Branch:</span><span class="value">{{ application.branch_name || '—' }}</span></div>
                <div class="request-card__info"><span class="label">Status:</span><span class="value">{{ application.status || 'Submitted' }}</span></div>
                <div class="request-card__info"><span class="label">Interview:</span><span class="value">{{ formatInterviewSchedule(application) }}</span></div>
                <div class="request-card__info"><span class="label">Applied On:</span><span class="value">{{ formatDate(application.created_at) }}</span></div>
                <div class="request-card__info"><span class="label">Contact:</span><span class="value">{{ application.applicant_email }} • {{ application.applicant_phone }}</span></div>
                <div class="request-card__actions">
                  <button class="btn-success btn-sm" @click="openApplicationDetails(application)">View Application Details</button>
                  <button v-if="!isPassedForHiring(application.status)" class="btn-primary btn-sm" @click="openInterviewScheduleModal(application)" :disabled="sendingInterviewEmail[application.id]">
                    {{ sendingInterviewEmail[application.id] ? 'Sending...' : (hasInterviewSchedule(application) ? 'Change Interview Schedule' : 'Schedule Interview') }}
                  </button>
                  <button v-if="isReadyForInterview(application.status) && !isPassedForHiring(application.status)" class="btn-success btn-sm" @click="markAsPassed(application)" :disabled="markingAsPassed[application.id] || !isInterviewTimeReached(application)" :title="!isInterviewTimeReached(application) ? 'Available at the scheduled interview time' : undefined">
                    {{ markingAsPassed[application.id] ? 'Processing...' : '✓ Mark as Passed' }}
                  </button>
                  <button v-if="isReadyForInterview(application.status) && !isPassedForHiring(application.status)" class="btn-danger btn-sm" @click="markAsNotPassed(application)" :disabled="markingAsNotPassed[application.id]">
                    {{ markingAsNotPassed[application.id] ? 'Processing...' : '✗ Mark as Not Passed' }}
                  </button>
                  <span v-else-if="isPassedForHiring(application.status)" class="status-approved">✓ Passed - Ready for Hiring</span>
                </div>
              </div>
            </div>
          </section>
          </template>

          <div v-if="activeHrSection === 'requests'" id="hr-position-requests" class="position-requests-section">
            <div class="section-header">
              <div>
                <h3>Position Requests</h3>
                <p class="muted">Approve or reject open position requests from branches.</p>
                <span v-if="positionRequestsPendingCount > 0" class="pending-badge">{{ positionRequestsPendingCount }} pending</span>
              </div>
              <button class="pill-btn" @click="loadPositionRequests" :disabled="loadingPositionRequests">
                {{ loadingPositionRequests ? 'Loading...' : 'Refresh' }}
              </button>
            </div>



            <div v-if="loadingPositionRequests" class="loading-box">Loading position requests...</div>
            <div v-else-if="positionRequests.length === 0" class="empty-box">No position requests found.</div>

            <div v-else class="requests-list">
              <div v-for="req in positionRequests" :key="req.id" class="request-card" :class="'request-card--' + req.status.toLowerCase()">

                <div class="request-card__header">
                  <span class="request-card__position">{{ req.position?.name || 'Unknown Position' }}</span>
                  <span class="request-card__status" :class="'status-' + req.status.toLowerCase()">{{ req.status }}</span>
                </div>
                <div class="request-card__info">
                  <span class="label">Branch:</span>
                  <span class="value">{{ req.branch?.name || 'Main Branch' }}</span>
                </div>
                <div class="request-card__info">
                  <span class="label">Quantity:</span>
                  <span class="value">{{ req.quantity }}</span>
                </div>
                <div class="request-card__info">
                  <span class="label">Requested by:</span>
                  <span class="value">{{ req.requested_by?.full_name || req.requested_by?.username || 'Unknown' }}</span>
                </div>
                <div class="request-card__info">
                  <span class="label">Date:</span>
                  <span class="value">{{ formatDate(req.created_at) }}</span>
                </div>
                <div v-if="req.notes" class="request-card__notes">
                  <span class="label">Notes:</span>
                  <p>{{ req.notes }}</p>
                </div>
                <div v-if="req.rejection_reason" class="request-card__notes request-card__notes--rejection">
                  <span class="label">Rejection reason:</span>
                  <p>{{ req.rejection_reason }}</p>
                </div>

                <div v-if="req.status === 'Pending'" class="request-card__actions">
                  <button @click="approveRequest(req)" class="btn-success btn-sm" :disabled="processingRequestId === req.id">
                    {{ processingRequestId === req.id ? 'Processing...' : 'Approve' }}
                  </button>
                  <button @click="openRejectModal(req)" class="btn-danger btn-sm" :disabled="processingRequestId === req.id">
                    Reject
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div v-if="activeHrSection === 'accounts'" id="hr-branch-accounts" class="branch-stats">
            <div class="section-header">
              <div>
                <h3>Accounts by Branch</h3>
                <p class="muted">Main Branch HR can review every account, grouped per branch.</p>
              </div>
              <button class="pill-btn" @click="loadBranchStaff" :disabled="loading">
                {{ loading ? 'Loading...' : 'Refresh' }}
              </button>
            </div>

            <div class="account-filters">
              <label class="sr-only" for="account-search">Search accounts</label>
              <input
                id="account-search"
                v-model="accountSearch"
                type="search"
                placeholder="Search name, username, role, or department"
                class="account-search"
              />
              <label class="sr-only" for="account-status">Filter account status</label>
              <select id="account-status" v-model="accountStatusFilter" class="account-status-filter">
                <option value="all">All statuses</option>
                <option value="active">Active only</option>
                <option value="inactive">Inactive only</option>
              </select>
            </div>

            <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>
            <div v-else-if="loading" class="loading-box">Loading accounts...</div>
            <div v-else-if="filteredBranchSections.length === 0" class="empty-box">No accounts match the current filters.</div>

            <div v-else class="branch-grid">
              <div v-for="branch in filteredBranchSections" :key="branch.branch_id" class="branch-card">
                <div class="branch-card__header">
                  <div class="header-content">
                    <div class="header-top">
                      <h4 class="branch-name">{{ branch.branch_name || 'Unassigned Branch' }}</h4>
                      <span class="badge-branch">Branch #{{ branch.branch_id || 'N/A' }}</span>
                    </div>
                    <p class="branch-subtitle">
                      <span class="stat-total">{{ branch.total_staff }} total</span>
                      <span class="stat-active">{{ branch.active_staff }} active</span>
                    </p>
                  </div>
                </div>

                <div class="table-container" v-if="branch.staff && branch.staff.length">
                  <table class="staff-table data-table">
                    <thead>
                      <tr>
                        <th class="col-name">Name</th>
                        <th class="col-username">Username</th>
                        <th class="col-role">Role</th>
                        <th class="col-dept">Department</th>
                        <th class="col-status">Status</th>
                        <th class="col-account-action">Details</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="member in branch.staff" :key="member.id" class="staff-row">
                        <td class="col-name">
                          <span class="name-badge">{{ (member.full_name || member.username).charAt(0) }}</span>
                          {{ member.full_name || member.username }}
                        </td>
                        <td class="col-username"><code class="code-text">{{ member.username }}</code></td>
                        <td class="col-role">
                          <span class="role-badge" :class="'role-' + (member.role || 'unknown').toLowerCase()">
                            {{ displayRole(member.role) }}
                          </span>
                        </td>
                        <td class="col-dept">{{ member.department || '—' }}</td>
                        <td class="col-status">
                          <span :class="['status-badge', member.is_active ? 'status-active' : 'status-inactive']">
                            <span class="status-dot" :class="member.is_active ? 'dot-active' : 'dot-inactive'"></span>
                            {{ member.is_active ? 'Active' : 'Inactive' }}
                          </span>
                        </td>
                        <td class="col-account-action">
                          <button type="button" class="btn-account-details" @click="openAccountDetails(member)">View details</button>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <div v-else class="empty-branch">No accounts for this branch.</div>
              </div>
            </div>
          </div>
          </div>
          </Transition>
        </div>
      </template>

      <template #sideTop>
        <div class="header-profile-wrapper main-branch-hr-profile" @click.stop>
          <button class="header-profile-btn" @click="toggleProfileDropdown">
            <div class="header-avatar">
              <div v-if="userProfile.avatarUrl" class="header-avatar-img" :style="{ backgroundImage: 'url('+userProfile.avatarUrl+')' }"></div>
              <div v-else class="header-avatar-initials">{{ (userProfile.fullName || 'H').charAt(0) }}</div>
            </div>
            <div class="header-name">{{ ((userProfile.fullName || userProfile.full_name) || 'HR MANAGER').toUpperCase() }}</div>
          </button>
          <div v-if="profileDropdownVisible" class="header-profile-dropdown" @click.stop>
            <button class="dropdown-item" @click="openInfoFromHeader">Info</button>
            <button class="dropdown-item" @click="triggerLogoutFromHeader">Logout</button>
          </div>
        </div>
      </template>
    </OwnerPanelLayout>

    <transition name="fade">
      <div
        v-if="showHrTutorial"
        class="main-branch-hr-tutorial-backdrop"
        role="presentation"
        @click.self="closeHrTutorial"
      >
        <section
          ref="hrTutorialDialog"
          class="main-branch-hr-tutorial-modal"
          role="dialog"
          aria-modal="true"
          aria-labelledby="main-branch-hr-tutorial-title"
          tabindex="-1"
          @keydown.esc="closeHrTutorial"
        >
          <header class="main-branch-hr-tutorial-header">
            <div>
              <p class="main-branch-hr-tutorial-eyebrow">Main Branch HR guide</p>
              <h2 id="main-branch-hr-tutorial-title">How to use this panel</h2>
            </div>
            <button
              class="main-branch-hr-tutorial-close"
              type="button"
              aria-label="Close Main Branch HR tutorial"
              @click="closeHrTutorial"
            >✕</button>
          </header>

          <div class="main-branch-hr-tutorial-progress" aria-live="polite">
            <span
              v-for="(step, index) in hrTutorialSteps"
              :key="step.title"
              class="main-branch-hr-tutorial-progress__dot"
              :class="{ 'main-branch-hr-tutorial-progress__dot--active': index === hrTutorialStep }"
              aria-hidden="true"
            ></span>
            <span>Step {{ hrTutorialStep + 1 }} of {{ hrTutorialSteps.length }}</span>
          </div>

          <div class="main-branch-hr-tutorial-body">
            <div class="main-branch-hr-tutorial-icon" aria-hidden="true">{{ hrTutorialSteps[hrTutorialStep].icon }}</div>
            <h3>{{ hrTutorialSteps[hrTutorialStep].title }}</h3>
            <p>{{ hrTutorialSteps[hrTutorialStep].description }}</p>
            <ul>
              <li v-for="item in hrTutorialSteps[hrTutorialStep].items" :key="item">{{ item }}</li>
            </ul>
          </div>

          <footer class="main-branch-hr-tutorial-footer">
            <button
              class="main-branch-hr-tutorial-button main-branch-hr-tutorial-button--secondary"
              type="button"
              :disabled="hrTutorialStep === 0"
              @click="hrTutorialStep -= 1"
            >Previous</button>
            <button
              v-if="hrTutorialSteps[hrTutorialStep].section"
              class="main-branch-hr-tutorial-button main-branch-hr-tutorial-button--section"
              type="button"
              @click="openHrTutorialSection(hrTutorialSteps[hrTutorialStep].section)"
            >{{ hrTutorialSteps[hrTutorialStep].actionLabel }}</button>
            <button
              class="main-branch-hr-tutorial-button main-branch-hr-tutorial-button--primary"
              type="button"
              @click="hrTutorialStep === hrTutorialSteps.length - 1 ? closeHrTutorial() : hrTutorialStep += 1"
            >{{ hrTutorialStep === hrTutorialSteps.length - 1 ? 'Done' : 'Next' }}</button>
          </footer>
        </section>
      </div>
    </transition>

    <div v-if="showLogoutOverlay" class="main-branch-hr-logout-overlay">
      <div class="main-branch-hr-logout-box">
        <img :src="logoImg" alt="Chikin Tayo" class="main-branch-hr-logout-logo" />
        <p>Logging out...</p>
      </div>
    </div>
  </div>

  <!-- Applications Modal -->
  <transition name="fade">
    <div v-if="showApplicationsModal" class="positions-modal-backdrop" @click.self="closeApplicationsModal">
      <div class="positions-modal application-list-modal">
        <div class="positions-modal__header">
          <div>
            <h3>Job Applications (HR View)</h3>
            <p class="muted">Applications submitted for positions on your branch.</p>
          </div>
          <button class="modal-close" @click="closeApplicationsModal" aria-label="Close">✕</button>
        </div>

        <div class="positions-modal__body">
          <div v-if="loadingApplications" class="loading-box">Loading applications...</div>
          <div v-else-if="applications.length === 0" class="empty-box">No applications found.</div>

          <div v-else class="positions-list">
            <div v-for="a in applications" :key="a.id" class="position-row">
              <div class="position-row__meta">
                <div class="position-row__name">{{ a.applicant_full_name }}</div>
                <div class="position-row__dept">{{ a.job_title }}</div>
              </div>

              <div class="request-card__info" style="margin-bottom: 0;">
                <span class="label">Department:</span>
                <span class="value">{{ a.department || '—' }}</span>
              </div>
              <div class="request-card__info" style="margin-top: 6px;">
                <span class="label">Branch:</span>
                <span class="value">{{ a.branch_name || '—' }}</span>
              </div>
              <div class="request-card__info" style="margin-top: 6px;">
                <span class="label">Status:</span>
                <span class="value">{{ a.status || 'Submitted' }}</span>
              </div>

              <div class="request-card__info" style="margin-top: 6px;">
                <span class="label">Interview:</span>
                <span class="value">{{ formatInterviewSchedule(a) }}</span>
              </div>

              <div class="request-card__info" style="margin-top: 6px;">
                <span class="label">Applied On:</span>
                <span class="value">{{ formatDate(a.created_at) }}</span>
              </div>

              <div class="request-card__info" style="margin-top: 6px;">
                <span class="label">Contact:</span>
                <span class="value">{{ a.applicant_email }} • {{ a.applicant_phone }}</span>
              </div>

              <div class="request-card__actions" style="margin-top: 12px; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                <button class="btn-success btn-sm" @click="openApplicationDetails(a)">
                  View Application Details
                </button>
                <button
                  v-if="!isPassedForHiring(a.status)"
                  class="btn-primary btn-sm"
                  @click="openInterviewScheduleModal(a)"
                  :disabled="sendingInterviewEmail[a.id]"
                >
                  {{ sendingInterviewEmail[a.id] ? 'Sending...' : (hasInterviewSchedule(a) ? 'Change Interview Schedule' : 'Schedule Interview') }}
                </button>
                <button
                  v-if="isReadyForInterview(a.status) && !isPassedForHiring(a.status)"
                  class="btn-success btn-sm"
                  @click="markAsPassed(a)"
                  :disabled="markingAsPassed[a.id] || !isInterviewTimeReached(a)"
                  :title="!isInterviewTimeReached(a) ? 'Available at the scheduled interview time' : undefined"
                >
                  {{ markingAsPassed[a.id] ? 'Processing...' : '✓ Mark as Passed' }}
                </button>
                <button
                  v-if="isReadyForInterview(a.status) && !isPassedForHiring(a.status)"
                  class="btn-danger btn-sm"
                  @click="markAsNotPassed(a)"
                  :disabled="markingAsNotPassed[a.id]"
                >
                  {{ markingAsNotPassed[a.id] ? 'Processing...' : '✗ Mark as Not Passed' }}
                </button>
                <span v-else-if="isPassedForHiring(a.status)" class="status-approved">✓ Passed - Ready for Hiring</span>
              </div>
            </div>
          </div>
        </div>

        <div class="positions-modal__footer">
          <button class="btn-secondary" @click="closeApplicationsModal">Close</button>
        </div>
      </div>
    </div>
  </transition>

  <!-- Interview Schedule Modal -->
  <div v-if="showInterviewScheduleModal" class="positions-modal-backdrop" @click.self="closeInterviewScheduleModal">
    <div class="positions-modal interview-schedule-modal">
      <div class="positions-modal__header">
        <div>
          <h3>{{ hasInterviewSchedule(selectedApplication) ? 'Change Interview Schedule' : 'Schedule Interview' }}</h3>
          <p class="muted">Select date and time for {{ selectedApplication?.applicant_full_name }}. The applicant will be emailed.</p>
        </div>
        <button class="modal-close" @click="closeInterviewScheduleModal" aria-label="Close">✕</button>
      </div>
      <div class="positions-modal__body">
        <label class="field"><span class="field-label">Interview Date *</span><input v-model="interviewSchedule.date" class="field-input" type="date" :min="getMinDate()"></label>
        <label class="field"><span class="field-label">Interview Time *</span><input v-model="interviewSchedule.time" class="field-input" type="time"></label>
        <label class="field"><span class="field-label">Additional Notes</span><textarea v-model="interviewSchedule.notes" class="field-textarea" rows="3"></textarea></label>
      </div>
      <div class="positions-modal__footer">
        <button class="btn-secondary" @click="closeInterviewScheduleModal">Cancel</button>
        <button class="btn-primary" @click="confirmInterviewSchedule" :disabled="!isInterviewScheduleValid() || sendingInterviewEmail[selectedApplication?.id]">
          {{ sendingInterviewEmail[selectedApplication?.id] ? 'Sending...' : (hasInterviewSchedule(selectedApplication) ? 'Send Updated Interview Email' : 'Send Interview Email') }}
        </button>
      </div>
    </div>
  </div>

  <!-- Application Details Modal -->
  <div v-if="showApplicationDetailsModal && selectedApplicationDetails" class="positions-modal-backdrop" @click.self="closeApplicationDetailsModal">
    <div class="positions-modal application-details-modal">
      <div class="positions-modal__header">
        <div><h3>Application Details</h3><p class="muted">Complete application information</p></div>
        <button class="modal-close" @click="closeApplicationDetailsModal" aria-label="Close">✕</button>
      </div>
      <div class="positions-modal__body">
        <div class="request-card__info"><span class="label">Full Name:</span><span class="value">{{ selectedApplicationDetails.applicant_full_name || '-' }}</span></div>
        <div class="request-card__info"><span class="label">Email:</span><span class="value">{{ selectedApplicationDetails.applicant_email || '-' }}</span></div>
        <div class="request-card__info"><span class="label">Phone:</span><span class="value">{{ selectedApplicationDetails.applicant_phone || '-' }}</span></div>
        <div class="request-card__info"><span class="label">Address:</span><span class="value">{{ selectedApplicationDetails.applicant_address || '-' }}</span></div>
        <div class="request-card__info"><span class="label">Position:</span><span class="value">{{ selectedApplicationDetails.job_title || '-' }}</span></div>
        <div class="request-card__info"><span class="label">Department:</span><span class="value">{{ selectedApplicationDetails.department || '-' }}</span></div>
        <div class="request-card__info"><span class="label">Branch:</span><span class="value">{{ selectedApplicationDetails.branch_name || '-' }}</span></div>
        <div class="request-card__info"><span class="label">Experience:</span><span class="value">{{ selectedApplicationDetails.years_of_experience || '-' }}</span></div>
        <div class="request-card__info"><span class="label">Education:</span><span class="value">{{ selectedApplicationDetails.education || '-' }}</span></div>
        <div class="request-card__info"><span class="label">Ready to Work Date:</span><span class="value">{{ formatDate(selectedApplicationDetails.available_start_date) }}</span></div>
        <div v-if="selectedApplicationDetails.cover_letter" class="request-card__notes"><span class="label">Cover Letter</span><p>{{ selectedApplicationDetails.cover_letter }}</p></div>
        <div class="request-card__actions">
          <a v-if="selectedApplicationDetails.resume_path" :href="getStorageUrl(selectedApplicationDetails.resume_path)" target="_blank" rel="noopener noreferrer" class="btn-primary">Open Resume</a>
        </div>
      </div>
      <div class="positions-modal__footer"><button class="btn-secondary" @click="closeApplicationDetailsModal">Close</button></div>
    </div>
  </div>

  <!-- Account Details Modal -->
  <div v-if="showAccountDetailsModal && selectedAccountDetails" class="positions-modal-backdrop" @click.self="closeAccountDetails">
    <section class="positions-modal account-details-modal" role="dialog" aria-modal="true" aria-labelledby="account-details-title">
      <div class="positions-modal__header">
        <div class="account-details-heading">
          <img v-if="selectedAccountDetails.avatar_url" :src="selectedAccountDetails.avatar_url" alt="" class="account-details-avatar" />
          <span v-else class="account-details-avatar account-details-avatar--fallback">
            {{ (selectedAccountDetails.full_name || selectedAccountDetails.username || '?').charAt(0).toUpperCase() }}
          </span>
          <div>
            <h3 id="account-details-title">{{ selectedAccountDetails.full_name || selectedAccountDetails.username || 'Account details' }}</h3>
            <p class="muted">{{ displayRole(selectedAccountDetails.role) }}<span v-if="selectedAccountDetails.department"> · {{ selectedAccountDetails.department }}</span></p>
          </div>
        </div>
        <button type="button" class="modal-close" @click="closeAccountDetails" aria-label="Close account details">✕</button>
      </div>
      <div class="positions-modal__body account-details-body">
        <div class="account-details-grid">
          <div class="account-detail"><span>Account ID</span><strong>{{ selectedAccountDetails.id }}</strong></div>
          <div class="account-detail"><span>Username</span><strong>{{ selectedAccountDetails.username || '—' }}</strong></div>
          <div class="account-detail"><span>Email</span><strong>{{ selectedAccountDetails.email || '—' }}</strong></div>
          <div class="account-detail"><span>Phone</span><strong>{{ selectedAccountDetails.phone_number || '—' }}</strong></div>
          <div class="account-detail"><span>Address</span><strong>{{ selectedAccountDetails.address || '—' }}</strong></div>
          <div class="account-detail"><span>Role</span><strong>{{ displayRole(selectedAccountDetails.role) }}</strong></div>
          <div class="account-detail"><span>Department</span><strong>{{ selectedAccountDetails.department || '—' }}</strong></div>
          <div class="account-detail"><span>Branch</span><strong>{{ selectedAccountDetails.branch_name || 'Unassigned Branch' }}<span v-if="selectedAccountDetails.branch_id"> (#{{ selectedAccountDetails.branch_id }})</span></strong></div>
          <div class="account-detail"><span>Account status</span><strong>{{ selectedAccountDetails.is_active ? 'Active' : 'Inactive' }}</strong></div>
          <div class="account-detail"><span>Presence</span><strong>{{ selectedAccountDetails.is_online ? 'Online' : 'Offline' }}</strong></div>
          <div class="account-detail"><span>Email verified</span><strong>{{ selectedAccountDetails.email_verified_at ? formatAccountDate(selectedAccountDetails.email_verified_at) : 'Not verified' }}</strong></div>
          <div class="account-detail"><span>Last login</span><strong>{{ formatAccountDate(selectedAccountDetails.last_login_at) }}</strong></div>
          <div class="account-detail"><span>Account created</span><strong>{{ formatAccountDate(selectedAccountDetails.created_at) }}</strong></div>
        </div>
      </div>
      <div class="positions-modal__footer">
        <button type="button" class="btn-secondary" @click="closeAccountDetails">Close</button>
      </div>
    </section>
  </div>

  <!-- Reject Reason Modal -->
  <div v-if="showRejectModal" class="reject-modal-backdrop" @click.self="closeRejectModal">

    <div class="reject-modal">
      <div class="reject-modal__header">
        <h3>Reject Position Request</h3>
        <button class="modal-close" @click="closeRejectModal" aria-label="Close">✕</button>
      </div>
      <div class="reject-modal__body">
        <p>You are about to reject the position request for <strong>{{ rejectingRequest?.position?.name }}</strong> ({{ rejectingRequest?.quantity }} position(s)).</p>
        <label class="field">
          <span class="field-label">Rejection Reason (optional)</span>
          <textarea
            class="field-textarea"
            rows="3"
            v-model.trim="rejectReason"
            placeholder="Provide a reason for rejection..."
          ></textarea>
        </label>
      </div>
      <div class="reject-modal__footer">
        <button class="btn-secondary" @click="closeRejectModal">Cancel</button>
        <button class="btn-danger" @click="confirmReject" :disabled="processingRequestId">
          {{ processingRequestId ? 'Processing...' : 'Confirm Rejection' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, nextTick, onUnmounted } from 'vue'
import OwnerPanelLayout from './OwnerPanelLayout.vue'
import axios from 'axios'
import { swalAlert, swalConfirm } from '../sweet-alerts'

const userProfile = ref({})
const ownerLayout = ref(null)
const profileDropdownVisible = ref(false)
const showLogoutOverlay = ref(false)
const logoImg = new URL('../assets/chikinlogo.png', import.meta.url).href
const branchSections = ref([])
const loading = ref(false)
const errorMessage = ref('')

// Position Requests for approval
const positionRequests = ref([])
const loadingPositionRequests = ref(false)
const processingRequestId = ref(null)
const showRejectModal = ref(false)
const rejectingRequest = ref(null)
const rejectReason = ref('')
const accountSearch = ref('')
const accountStatusFilter = ref('all')
const showAccountDetailsModal = ref(false)
const selectedAccountDetails = ref(null)

// Job Applications modal state
const showApplicationsModal = ref(false)
const loadingApplications = ref(false)
const applications = ref([])
const interviewClock = ref(Date.now())
const interviewClockInterval = window.setInterval(() => {
  interviewClock.value = Date.now()
}, 15000)
const applicationsCount = computed(() => {
  return Array.isArray(applications.value) ? applications.value.length : 0
})
const sendingInterviewEmail = ref({})
const markingAsPassed = ref({})
const markingAsNotPassed = ref({})
const showInterviewScheduleModal = ref(false)
const selectedApplication = ref(null)
const interviewSchedule = ref({ date: '', time: '', notes: '' })
const showApplicationDetailsModal = ref(false)
const selectedApplicationDetails = ref(null)
const activeHrSection = ref('overview')
const hoveredHrSection = ref('')
const hrSidebarItemStyle = section => {
  const active = activeHrSection.value === section
  const highlighted = active || hoveredHrSection.value === section
  return {
    background: active ? '#fffaf5' : highlighted ? 'rgba(255, 255, 255, 0.68)' : 'transparent',
    borderColor: active ? '#efb47f' : highlighted ? 'rgba(215, 125, 61, 0.22)' : 'transparent',
    color: active ? '#a34f21' : highlighted ? '#9b4e21' : '#29384a',
    boxShadow: active ? '0 5px 12px rgba(184, 111, 61, 0.08)' : 'none',
    transition: 'all 160ms ease',
  }
}
const hrSidebarIconStyle = section => {
  const highlighted = activeHrSection.value === section || hoveredHrSection.value === section
  return {
    display: 'grid',
    width: '1.8rem',
    height: '1.8rem',
    placeItems: 'center',
    borderRadius: '0.7rem',
    background: highlighted ? '#f97316' : '#fff1e6',
    color: highlighted ? '#ffffff' : '#f97316',
    transition: 'color 160ms ease, background-color 160ms ease',
  }
}
const showHrTutorial = ref(false)
const hrTutorialStep = ref(0)
const hrTutorialDialog = ref(null)
const hrTutorialTrigger = ref(null)
const hrTutorialSteps = [
  {
    icon: '⌂',
    title: 'Main Branch HR overview',
    description: 'Use the overview to monitor workforce totals and review branch-level hiring activity.',
    section: 'overview',
    actionLabel: 'Open HR Overview',
    items: [
      'Pending Requests shows position requests awaiting review. Branches, Total Accounts, and Active Accounts summarize workforce coverage.',
      'Job Applications are listed directly on the overview. Use the sidebar to switch between HR Overview, Position Requests, and Branch Accounts.',
      'Refresh Requests reloads the position request data. The message and notification controls remain available in the topbar.',
    ],
  },
  {
    icon: '▣',
    title: 'Job applications',
    description: 'Review applicants listed on the HR Overview and follow their interview progress.',
    section: 'overview',
    actionLabel: 'View Job Applications',
    items: [
      'Each application shows the applicant, job title, department, branch, current status, interview schedule, submission date, and contact information.',
      'Select View Application Details to inspect the submitted application and available resume.',
      'Use Schedule Interview or Change Interview Schedule to set an interview date and time, add optional notes, and send the applicant an email.',
    ],
  },
  {
    icon: '✓',
    title: 'Applicant review and status',
    description: 'Application actions are available on each applicant row in the HR Overview.',
    section: 'overview',
    actionLabel: 'Open HR Overview',
    items: [
      'Review the application details and interview schedule before making a decision.',
      'For applicants Ready for Interview, Mark as Passed is enabled at the scheduled interview time. Mark as Not Passed is also available.',
      'The row shows Passed - Ready for Hiring after a pass. Use the displayed status and interview information to track the next step.',
    ],
  },
  {
    icon: '＋',
    title: 'Position request approvals',
    description: 'Main Branch HR reviews requests for open positions submitted by branches.',
    section: 'requests',
    actionLabel: 'Open Position Requests',
    items: [
      'Open Position Requests to review each request’s position, branch, quantity, requester, date, notes, and current status.',
      'For Pending requests, select Approve to approve the request or Reject to provide an optional rejection reason.',
      'Use Refresh to load the latest requests. The pending count on this page updates from the requests currently loaded.',
    ],
  },
  {
    icon: '⌕',
    title: 'Branch accounts',
    description: 'Review staff accounts grouped by branch and narrow the list with the available filters.',
    section: 'accounts',
    actionLabel: 'Open Branch Accounts',
    items: [
      'Use the search box to match an account by name, username, role, department, or branch.',
      'Use the status selector to show all accounts, active accounts only, or inactive accounts only.',
      'Select View details on an account row to review its contact details, role, department, branch, status, presence, verification, and account dates.',
      'Refresh reloads the branch account data.',
    ],
  },
  {
    icon: '?',
    title: 'Account and panel controls',
    description: 'Use the persistent topbar and sidebar controls to get help and manage your session.',
    section: 'overview',
    actionLabel: 'Return to HR Overview',
    items: [
      'Select the ? icon at any time to restart this Main Branch HR guide.',
      'Use Account Info in the sidebar or Info in the profile menu to view account information.',
      'Use the message and notification controls in the topbar for communications and updates. Select Logout from the sidebar or profile menu to end the session.',
    ],
  },
]

function showHrSection(section) {
  activeHrSection.value = section
}

function openHrTutorial() {
  hrTutorialStep.value = 0
  showHrTutorial.value = true
  nextTick(() => hrTutorialDialog.value?.focus())
}

function closeHrTutorial(restoreFocus = true) {
  showHrTutorial.value = false
  if (restoreFocus) nextTick(() => hrTutorialTrigger.value?.focus())
}

function openHrTutorialSection(section) {
  closeHrTutorial(false)
  showHrSection(section)
}

const positionRequestsPendingCount = computed(() => {
  return positionRequests.value.filter(r => r.status === 'Pending').length
})

const totalStaffCount = computed(() => branchSections.value.reduce((total, branch) => total + Number(branch.total_staff || 0), 0))
const activeStaffCount = computed(() => branchSections.value.reduce((total, branch) => total + Number(branch.active_staff || 0), 0))
const filteredBranchSections = computed(() => {
  const query = accountSearch.value.trim().toLowerCase()
  const status = accountStatusFilter.value

  return branchSections.value
    .map(branch => ({
      ...branch,
      staff: (branch.staff || []).filter(member => {
        const matchesQuery = !query || [
          member.full_name,
          member.username,
          member.role,
          member.department,
          branch.branch_name
        ].some(value => String(value || '').toLowerCase().includes(query))
        const matchesStatus = status === 'all' || (status === 'active' ? member.is_active : !member.is_active)
        return matchesQuery && matchesStatus
      })
    }))
    .filter(branch => branch.staff.length > 0)
})



function toggleProfileDropdown() {
  profileDropdownVisible.value = !profileDropdownVisible.value
}

function closeProfileDropdown() {
  profileDropdownVisible.value = false
}

function openInfoFromHeader() {
  closeProfileDropdown()
  try {
    window.dispatchEvent(new Event('open-owner-info'))
  } catch (e) {}
}

async function triggerLogoutFromHeader() {
  closeProfileDropdown()
  try {
    const ok = await (window.swalConfirm ? window.swalConfirm('Logout from Main Branch HR Panel?', 'Confirm logout') : Promise.resolve(false))
    if (ok) await confirmLogout()
  } catch (e) {}
}

async function confirmLogout() {
  if (showLogoutOverlay.value) return
  showLogoutOverlay.value = true
  try {
    await axios.post('/api/logout', {}, { withCredentials: true })
  } catch (e) {}
  try { localStorage.clear(); sessionStorage.clear() } catch (e) {}
  setTimeout(() => {
    try { window.location.replace('/admin-login') } catch (e) {}
  }, 600)
}

function askLogout() {
  try {
    window.swalConfirm('Logout from Main Branch HR Panel?', 'Confirm logout').then(ok => {
      if (ok) confirmLogout()
    })
  } catch (e) {}
}

function onProfileUpdated(updatedProfile) {
  userProfile.value = { ...userProfile.value, ...updatedProfile }
}

function displayRole(r) {
  const role = (r || '').toString().toUpperCase()
  if (role === 'BRANCH_MANAGER') return 'Manager'
  if (role === 'STAFF') return 'Staff'
  if (role === 'HR') return 'HR'
  return role.replace(/_/g, ' ')
}

function openAccountDetails(account) {
  selectedAccountDetails.value = account
  showAccountDetailsModal.value = true
}

function closeAccountDetails() {
  showAccountDetailsModal.value = false
  selectedAccountDetails.value = null
}

function formatAccountDate(value) {
  if (!value) return '—'
  const date = new Date(value)
  return Number.isNaN(date.getTime()) ? '—' : date.toLocaleString()
}

function closeApplicationsModal() {
  showApplicationsModal.value = false
}

function openApplicationDetails(application) {
  selectedApplicationDetails.value = application
  showApplicationDetailsModal.value = true
}

function closeApplicationDetailsModal() {
  showApplicationDetailsModal.value = false
  selectedApplicationDetails.value = null
}

function isReadyForInterview(status) {
  const value = String(status || '').toLowerCase().trim()
  return ['ready for interview', 'ready_for_interview', 'interview scheduled'].includes(value)
}

function isPassedForHiring(status) {
  const value = String(status || '').toLowerCase().trim()
  return ['passed - ready for hiring', 'passed_ready_for_hiring', 'passed'].includes(value)
}

function isInterviewTimeReached(application) {
  const interviewAt = getInterviewDateTime(application)
  return Boolean(interviewAt && interviewClock.value >= interviewAt.getTime())
}

function hasInterviewSchedule(application) {
  return Boolean(getInterviewDateTime(application))
}

function getInterviewDateTime(application) {
  if (!application?.interview_date || !application?.interview_time) return null

  const dateParts = String(application.interview_date).slice(0, 10).split('-').map(Number)
  const timeParts = String(application.interview_time).slice(0, 8).split(':').map(Number)
  if (dateParts.length !== 3 || timeParts.length < 2 || [...dateParts, ...timeParts].some(part => !Number.isFinite(part))) return null

  const [year, month, day] = dateParts
  const [hours, minutes, seconds = 0] = timeParts
  const interviewAt = new Date(year, month - 1, day, hours, minutes, seconds)
  if (
    interviewAt.getFullYear() !== year ||
    interviewAt.getMonth() !== month - 1 ||
    interviewAt.getDate() !== day ||
    interviewAt.getHours() !== hours ||
    interviewAt.getMinutes() !== minutes
  ) return null

  return interviewAt
}

function formatInterviewSchedule(application) {
  const interviewAt = getInterviewDateTime(application)
  if (!interviewAt) return 'Not scheduled'

  return interviewAt.toLocaleString('en-PH', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
  })
}

function openInterviewScheduleModal(application) {
  if (!application?.id || !application?.applicant_email) {
    alert('Invalid application data')
    return
  }
  selectedApplication.value = application
  interviewSchedule.value = {
    date: application.interview_date ? String(application.interview_date).slice(0, 10) : '',
    time: application.interview_time ? String(application.interview_time).slice(0, 5) : '',
    notes: application.interview_notes || '',
  }
  showInterviewScheduleModal.value = true
}

function closeInterviewScheduleModal() {
  showInterviewScheduleModal.value = false
  selectedApplication.value = null
  interviewSchedule.value = { date: '', time: '', notes: '' }
}

function getMinDate() {
  const date = new Date()
  return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`
}

function isInterviewScheduleValid() {
  return Boolean(interviewSchedule.value.date && interviewSchedule.value.time)
}

async function confirmInterviewSchedule() {
  const applicationId = selectedApplication.value?.id
  if (!applicationId || !isInterviewScheduleValid()) return
  sendingInterviewEmail.value[applicationId] = true
  try {
    const res = await axios.post(`/api/hr/positions/applications/${applicationId}/send-interview-email`, {
      interview_date: interviewSchedule.value.date,
      interview_time: interviewSchedule.value.time,
      notes: interviewSchedule.value.notes
    }, { withCredentials: true })
    if (res.data?.ok) {
      const application = applications.value.find(item => item.id === applicationId)
      if (application) {
        application.status = 'Ready for Interview'
        application.interview_date = interviewSchedule.value.date
        application.interview_time = interviewSchedule.value.time
        application.interview_notes = interviewSchedule.value.notes
      }
      alert(res.data.message || 'Interview email sent successfully!')
      closeInterviewScheduleModal()
    } else {
      alert(res.data?.message || 'Failed to send interview email')
    }
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to send interview email. Please try again.')
  } finally {
    sendingInterviewEmail.value[applicationId] = false
  }
}

async function markAsPassed(application) {
  if (!application?.id) return
  const confirmed = await swalConfirm(`Are you sure you want to mark ${application.applicant_full_name} as passed?`, 'Mark as Passed')
  if (!confirmed) return
  markingAsPassed.value[application.id] = true
  try {
    const res = await axios.post(`/api/hr/positions/applications/${application.id}/mark-as-passed`, {}, { withCredentials: true })
    if (res.data?.ok) application.status = 'Passed - Ready for Hiring'
    await swalAlert(res.data?.message || 'Applicant status updated.', 'success')
  } catch (err) {
    await swalAlert(err.response?.data?.message || 'Failed to mark applicant as passed.', 'error')
  } finally {
    markingAsPassed.value[application.id] = false
  }
}

async function markAsNotPassed(application) {
  if (!application?.id) return
  const confirmed = await swalConfirm(`Are you sure you want to mark ${application.applicant_full_name} as not passed?`, 'Mark as Not Passed')
  if (!confirmed) return
  markingAsNotPassed.value[application.id] = true
  try {
    const res = await axios.post(`/api/hr/positions/applications/${application.id}/mark-as-not-passed`, {}, { withCredentials: true })
    if (res.data?.ok) application.status = 'Not Passed'
    await swalAlert(res.data?.message || 'Applicant status updated.', 'success')
  } catch (err) {
    await swalAlert(err.response?.data?.message || 'Failed to mark applicant as not passed.', 'error')
  } finally {
    markingAsNotPassed.value[application.id] = false
  }
}

function getStorageUrl(path) {
  if (!path) return ''
  // resume_path is stored using Laravel's public disk under /storage
  // The stored string is usually like: position-applications/.../resume_cv.pdf
  return path.startsWith('/') ? path : `/storage/${path}`
}

async function loadApplications() {
  loadingApplications.value = true
  try {
    const res = await axios.get('/api/hr/positions/applications', { withCredentials: true })
    if (res.data?.ok) {
      applications.value = Array.isArray(res.data.applications) ? res.data.applications : []
    } else {
      applications.value = []
    }
  } catch (err) {
    console.error('[MainBranchHrPanel] Failed loading applications:', err)
    applications.value = []
    alert(err.response?.data?.message || 'Failed to load applications.')
  } finally {
    loadingApplications.value = false
  }
}

onUnmounted(() => window.clearInterval(interviewClockInterval))

async function openApplicationsModal() {
  showApplicationsModal.value = true
  await loadApplications()
}


async function loadBranchStaff() {
  loading.value = true
  errorMessage.value = ''
  try {
    const res = await axios.get('/api/manager/hr/staff', { withCredentials: true })
    if (res.data && res.data.ok) {
      if (Array.isArray(res.data.branches)) {
        branchSections.value = res.data.branches
      } else if (Array.isArray(res.data.staff)) {
        const grouped = res.data.staff.reduce((acc, member) => {
          const key = member.branch_id || 'unassigned'
          if (!acc[key]) {
            acc[key] = {
              branch_id: member.branch_id || null,
              branch_name: member.branch_name || 'Unassigned Branch',
              total_staff: 0,
              active_staff: 0,
              staff: []
            }
          }
          acc[key].staff.push(member)
          acc[key].total_staff += 1
          acc[key].active_staff += member.is_active ? 1 : 0
          return acc
        }, {})
        branchSections.value = Object.values(grouped)
      } else {
        branchSections.value = []
      }
    } else {
      errorMessage.value = res.data?.message || 'Failed to load accounts'
      branchSections.value = []
    }
  } catch (err) {
    errorMessage.value = err.response?.data?.message || 'Error loading accounts'
    branchSections.value = []
  } finally {
    loading.value = false
  }
}

// Initial load
loadBranchStaff()
loadPositionRequests()
loadApplications()

// Position Requests (for approval)
async function loadPositionRequests() {
  loadingPositionRequests.value = true
  try {
    const res = await axios.get('/api/hr/positions/requests/pending', { withCredentials: true })
    if (res.data && res.data.ok) {
      positionRequests.value = res.data.requests || []
    } else {
      positionRequests.value = []
    }
  } catch (e) {
    console.error('[MainBranchHrPanel] Failed loading position requests:', e.response || e.message || e)
    positionRequests.value = []
  } finally {
    loadingPositionRequests.value = false
  }
}

async function approveRequest(req) {
  processingRequestId.value = req.id
  try {
    const res = await axios.post(`/api/hr/positions/requests/${req.id}/approve`, {}, { withCredentials: true })
    if (res.data && res.data.ok) {
      alert('Request approved successfully.')
      await loadPositionRequests()
    } else {
      alert(res.data?.message || 'Failed to approve request.')
    }
  } catch (e) {
    alert(e.response?.data?.message || 'Failed to approve request.')
  } finally {
    processingRequestId.value = null
  }
}

function openRejectModal(req) {
  rejectingRequest.value = req
  rejectReason.value = ''
  showRejectModal.value = true
}

function closeRejectModal() {
  showRejectModal.value = false
  rejectingRequest.value = null
  rejectReason.value = ''
}

async function confirmReject() {
  if (!rejectingRequest.value) return
  processingRequestId.value = rejectingRequest.value.id
  try {
    const res = await axios.post(`/api/hr/positions/requests/${rejectingRequest.value.id}/reject`, {
      reason: rejectReason.value
    }, { withCredentials: true })
    if (res.data && res.data.ok) {
      alert('Request rejected.')
      closeRejectModal()
      await loadPositionRequests()
    } else {
      alert(res.data?.message || 'Failed to reject request.')
    }
  } catch (e) {
    alert(e.response?.data?.message || 'Failed to reject request.')
  } finally {
    processingRequestId.value = null
  }
}

function formatDate(dateStr) {
  if (!dateStr) return '-'
  const d = new Date(dateStr)
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

// Close dropdown when clicking outside
window.addEventListener('click', () => {
  try { if (profileDropdownVisible.value) closeProfileDropdown() } catch (e) {}
})
</script>

<style scoped>
.main-branch-hr-tutorial-trigger {
  display: inline-grid;
  place-items: center;
  width: 36px;
  height: 36px;
  flex: 0 0 36px;
  padding: 0;
  border: 1px solid #e3d6cd;
  border-radius: 50%;
  background: #fffaf5;
  color: #593b2a;
  font: inherit;
  font-size: 1rem;
  font-weight: 800;
  cursor: pointer;
  transition: background-color 160ms ease, border-color 160ms ease, color 160ms ease, transform 160ms ease;
}

.main-branch-hr-tutorial-trigger:hover {
  border-color: #f2a45f;
  background: #fff0df;
  color: #a84b19;
  transform: translateY(-1px);
}

.main-branch-hr-tutorial-trigger:focus-visible,
.main-branch-hr-tutorial-button:focus-visible,
.main-branch-hr-tutorial-close:focus-visible {
  outline: 3px solid #ff9f43;
  outline-offset: 3px;
}

.main-branch-hr-tutorial-backdrop {
  position: fixed;
  inset: 0;
  z-index: 5000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
  background: rgba(31, 35, 41, 0.58);
  backdrop-filter: blur(5px);
}

.main-branch-hr-tutorial-modal {
  display: flex;
  flex-direction: column;
  width: min(620px, 100%);
  max-height: min(88vh, 760px);
  overflow: hidden;
  border: 1px solid rgba(222, 200, 183, 0.9);
  border-radius: 24px;
  background: linear-gradient(180deg, #fff 0%, #fffaf5 100%);
  box-shadow: 0 28px 80px rgba(22, 25, 29, 0.28);
  color: #4a3325;
}

.main-branch-hr-tutorial-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
  padding: 22px 24px 18px;
  border-bottom: 1px solid #f0e4da;
}

.main-branch-hr-tutorial-eyebrow {
  margin: 0 0 6px;
  color: #bd6334;
  font-size: 0.7rem;
  font-weight: 800;
  letter-spacing: 0.13em;
  line-height: 1.4;
  text-transform: uppercase;
}

.main-branch-hr-tutorial-header h2 {
  margin: 0;
  color: #3d2a1f;
  font-size: clamp(1.25rem, 3vw, 1.55rem);
  font-weight: 850;
  letter-spacing: -0.035em;
  line-height: 1.2;
}

.main-branch-hr-tutorial-close {
  display: grid;
  place-items: center;
  width: 36px;
  height: 36px;
  flex: 0 0 36px;
  padding: 0;
  border: 0;
  border-radius: 50%;
  background: #f7f1ec;
  color: #8c6e5b;
  font-size: 1.1rem;
  cursor: pointer;
  transition: background-color 160ms ease, color 160ms ease;
}

.main-branch-hr-tutorial-close:hover {
  background: #f3e4d8;
  color: #593b2a;
}

.main-branch-hr-tutorial-progress {
  display: flex;
  align-items: center;
  gap: 7px;
  padding: 16px 24px 0;
  color: #593b2a;
  font-size: 0.78rem;
  font-weight: 750;
}

.main-branch-hr-tutorial-progress__dot {
  width: 8px;
  height: 8px;
  flex: 0 0 8px;
  border-radius: 50%;
  background: #e8ded7;
}

.main-branch-hr-tutorial-progress__dot--active {
  width: 22px;
  flex-basis: 22px;
  border-radius: 999px;
  background: #ff9f43;
}

.main-branch-hr-tutorial-body {
  min-height: 0;
  overflow-y: auto;
  padding: 24px 24px 20px;
  scrollbar-color: #d9bda7 transparent;
  scrollbar-width: thin;
}

.main-branch-hr-tutorial-icon {
  display: grid;
  place-items: center;
  width: 52px;
  height: 52px;
  margin-bottom: 15px;
  border-radius: 16px;
  background: #fff0dc;
  color: #e77b22;
  font-size: 1.4rem;
  font-weight: 750;
}

.main-branch-hr-tutorial-body h3 {
  margin: 0 0 8px;
  color: #3d2a1f;
  font-size: 1.2rem;
  font-weight: 850;
  letter-spacing: -0.025em;
  line-height: 1.3;
}

.main-branch-hr-tutorial-body > p {
  margin: 0;
  color: #826c5c;
  font-size: 0.98rem;
  line-height: 1.55;
}

.main-branch-hr-tutorial-body ul {
  display: grid;
  gap: 9px;
  margin: 18px 0 0;
  padding: 0 0 0 19px;
}

.main-branch-hr-tutorial-body li {
  padding-left: 1px;
  color: #523a2a;
  font-size: 0.94rem;
  line-height: 1.5;
}

.main-branch-hr-tutorial-body li::marker {
  color: #ff983e;
}

.main-branch-hr-tutorial-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  padding: 16px 24px 20px;
  border-top: 1px solid #f0e4da;
}

.main-branch-hr-tutorial-button {
  min-height: 42px;
  padding: 0 17px;
  border: 1px solid transparent;
  border-radius: 11px;
  font: inherit;
  font-size: 0.86rem;
  font-weight: 750;
  cursor: pointer;
  transition: background-color 160ms ease, border-color 160ms ease, color 160ms ease, opacity 160ms ease;
}

.main-branch-hr-tutorial-button:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}

.main-branch-hr-tutorial-button--secondary {
  border-color: #eadfd6;
  background: #fffaf5;
  color: #745d4d;
}

.main-branch-hr-tutorial-button--secondary:hover:not(:disabled) {
  border-color: #dbc5b4;
  background: #f9f0e8;
}

.main-branch-hr-tutorial-button--section {
  border-color: #ffd0a4;
  background: transparent;
  color: #a8441d;
}

.main-branch-hr-tutorial-button--section:hover {
  background: #fff0df;
}

.main-branch-hr-tutorial-button--primary {
  min-width: 100px;
  background: #ff9f43;
  color: #fff;
}

.main-branch-hr-tutorial-button--primary:hover {
  background: #ed872a;
}

.main-branch-hr-logout-overlay {
  position: fixed;
  inset: 0;
  z-index: 10000;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(0, 0, 0, 0.35);
  -webkit-backdrop-filter: blur(4px);
  backdrop-filter: blur(4px);
}

.main-branch-hr-logout-box {
  min-width: 168px;
  padding: 12px 18px 14px;
  border-radius: 12px;
  background: #ffffff;
  text-align: center;
  box-shadow: 0 16px 40px rgba(15, 23, 42, 0.18);
}

.main-branch-hr-logout-logo {
  display: block;
  width: 80px;
  height: auto;
  margin: 0 auto 8px;
}

.main-branch-hr-logout-box p {
  margin: 0;
  color: #6b6b6b;
  font-size: 0.9rem;
  font-weight: 500;
}

.positions-modal-backdrop {
  position: fixed;
  inset: 0;
  padding: 20px;
  box-sizing: border-box;
  background: rgba(42, 29, 20, 0.54);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 3000;
  -webkit-backdrop-filter: blur(3px);
  backdrop-filter: blur(3px);
}

.positions-modal {
  width: 92%;
  max-width: 860px;
  max-height: min(88vh, 900px);
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 24px 70px rgba(42, 29, 20, 0.24);
  overflow: hidden;
  border: 1px solid #f1e5d8;
  display: flex;
  flex-direction: column;
}

.positions-modal__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  padding: 20px 24px;
  background: linear-gradient(135deg, #fffaf5 0%, #fff 100%);
  border-bottom: 1px solid #f1e5d8;
}

.positions-modal__header h3 {
  margin: 0;
  font-size: 19px;
  line-height: 1.3;
  font-weight: 700;
  color: #42210b;
}

.positions-modal__body {
  padding: 22px 24px;
  min-height: 0;
  overflow: auto;
}

.positions-modal__footer {
  padding: 16px 24px;
  border-top: 1px solid #f1e5d8;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  background: #fffdfa;
}

.modal-close {
  flex: 0 0 auto;
  width: 36px;
  height: 36px;
  border: none;
  border-radius: 9px;
  background: #fff;
  font-size: 18px;
  cursor: pointer;
  color: #72563f;
  transition: background-color 160ms ease, color 160ms ease;
}
.modal-close:hover { color: #42210b; background: #f7eee5; }

.positions-list { display: flex; flex-direction: column; gap: 12px; }
.position-row {
  border: 1px solid #eadfd4;
  background: #fff;
  border-radius: 12px;
  padding: 16px;
  box-shadow: 0 2px 8px rgba(66, 33, 11, 0.035);
}

.position-row__meta {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 14px;
  margin-bottom: 12px;
}

.position-row__name { font-weight: 700; color: #42210b; font-size: 15px; line-height: 1.35; }
.position-row__dept { color: #a4511b; font-size: 13px; font-weight: 600; text-align: right; }

.position-row__inputs {
  display: grid;
  grid-template-columns: 180px 1fr;
  gap: 10px;
}

.field { display: flex; flex-direction: column; gap: 4px; }
.field-label { font-weight: 700; font-size: 12px; color: #51351f; }
.field-input {
  width: 100%;
  padding: 8px 10px;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  outline: none;
  font-size: 12px;
}
.field-input:focus {
  border-color: #4b5563;
  box-shadow: 0 0 0 3px rgba(75, 85, 99, 0.1);
}
.field-textarea {
  width: 100%;
  padding: 8px 10px;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  outline: none;
  resize: vertical;
  font-size: 12px;
}

@media (max-width: 768px) {
  .position-row__inputs { grid-template-columns: 1fr; }
  .positions-modal__footer { flex-direction: column; align-items: stretch; }
}

.applications-section {
  padding: 22px;
}

.applications-section .section-header {
  align-items: center;
  gap: 16px;
  margin-bottom: 18px;
}

.applications-section .section-header h3 {
  margin: 0;
  color: #42210b;
  font-size: 19px;
  line-height: 1.3;
}

.applications-section .muted {
  margin-top: 5px;
  font-size: 13px;
}

.applications-section .pending-badge {
  margin: 0;
  padding: 6px 11px;
  border-color: #f2c9a5;
  border-radius: 999px;
  background: #fff3e8;
  color: #9a4c17;
  font-size: 12px;
  white-space: nowrap;
}

.applications-section .positions-list {
  margin-top: 0;
  gap: 14px;
}

.applications-section .position-row {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  column-gap: 22px;
  row-gap: 0;
  padding: 18px;
  transition: box-shadow 180ms ease, border-color 180ms ease;
}

.hr-summary-card {
  transition: box-shadow 180ms ease, border-color 180ms ease;
}

@media (hover: hover) and (prefers-reduced-motion: no-preference) {
  .hr-summary-card:hover,
  .applications-section .position-row:hover {
    animation: hr-application-card-bounce 480ms ease;
    box-shadow: 0 10px 22px rgba(66, 33, 11, 0.1);
  }
}

@keyframes hr-application-card-bounce {
  0%,
  100% { transform: translateY(0); }
  35% { transform: translateY(-5px); }
  65% { transform: translateY(1px); }
  85% { transform: translateY(-2px); }
}

.applications-section .position-row__meta,
.applications-section .request-card__actions {
  grid-column: 1 / -1;
}

.applications-section .request-card__info {
  grid-template-columns: minmax(82px, auto) minmax(0, 1fr);
  gap: 8px;
  margin: 0 0 9px;
  font-size: 13px;
  line-height: 1.45;
}

.applications-section .request-card__info .label {
  color: #806a58;
}

.applications-section .request-card__info .value {
  min-width: 0;
  color: #382b21;
  overflow-wrap: anywhere;
}

.applications-section .request-card__actions {
  flex-wrap: wrap;
  align-items: center;
  gap: 9px;
  margin-top: 8px;
  padding-top: 14px;
  border-color: #eee3d8;
}

.applications-section .btn-success,
.applications-section .btn-primary,
.applications-section .btn-danger,
.positions-modal .btn-success,
.positions-modal .btn-primary,
.positions-modal .btn-danger,
.positions-modal .btn-secondary {
  min-height: 38px;
  padding: 9px 14px;
  border-radius: 8px;
  font-size: 12px;
  line-height: 1.3;
  transition: background-color 160ms ease, border-color 160ms ease, transform 160ms ease;
}

.applications-section .btn-success,
.positions-modal .btn-success {
  background: #a4511b;
  color: #fff;
}

.applications-section .btn-success:hover,
.positions-modal .btn-success:hover {
  background: #873f14;
}

.request-card__actions .btn-success:disabled {
  background: #9ca3af;
  border-color: #9ca3af;
  color: #fff;
  opacity: 0.75;
  cursor: not-allowed;
  filter: grayscale(0.4);
  transform: none;
  box-shadow: none;
}

.applications-section .btn-primary,
.positions-modal .btn-primary {
  background: #42210b;
  color: #fff;
}

.applications-section .btn-primary:hover,
.positions-modal .btn-primary:hover {
  background: #2f1808;
}

.positions-modal .btn-secondary {
  border: 1px solid #e1d3c5;
  background: #fff;
  color: #51351f;
}

.positions-modal .btn-secondary:hover {
  background: #f8f1ea;
}

.positions-modal .request-card__info {
  grid-template-columns: 125px minmax(0, 1fr);
  gap: 12px;
  margin-bottom: 10px;
  font-size: 13px;
  line-height: 1.5;
}

.positions-modal .request-card__info .label,
.positions-modal .request-card__notes .label {
  color: #806a58;
  font-weight: 700;
}

.positions-modal .request-card__info .value,
.positions-modal .request-card__notes p {
  min-width: 0;
  color: #382b21;
  overflow-wrap: anywhere;
}

.positions-modal .request-card__notes {
  margin-top: 16px;
  padding-top: 14px;
  border-color: #eee3d8;
}

.positions-modal .request-card__notes p {
  margin-top: 7px;
  font-size: 13px;
  line-height: 1.6;
  white-space: pre-wrap;
}

.application-details-modal,
.interview-schedule-modal {
  max-width: 680px;
}

.interview-schedule-modal .positions-modal__body {
  display: grid;
  gap: 18px;
}

.interview-schedule-modal .field {
  gap: 7px;
}

.interview-schedule-modal .field-input,
.interview-schedule-modal .field-textarea {
  box-sizing: border-box;
  min-height: 44px;
  padding: 10px 12px;
  border-color: #e1d3c5;
  border-radius: 9px;
  background: #fffdfa;
  color: #382b21;
  font: inherit;
  font-size: 14px;
}

.interview-schedule-modal .field-input:focus,
.interview-schedule-modal .field-textarea:focus {
  border-color: #c8773b;
  box-shadow: 0 0 0 3px rgba(194, 90, 18, 0.12);
}

.interview-schedule-modal .field-textarea {
  min-height: 100px;
}

@media (max-width: 768px) {
  .applications-section { padding: 16px; }
  .applications-section .position-row { grid-template-columns: minmax(0, 1fr); padding: 14px; }
  .applications-section .section-header { align-items: flex-start; }
  .applications-section .position-row__meta { flex-direction: column; gap: 4px; }
  .applications-section .position-row__dept { text-align: left; }
  .positions-modal-backdrop { padding: 12px; }
  .positions-modal { width: 100%; max-height: 92vh; border-radius: 13px; }
  .positions-modal__header { padding: 16px; }
  .positions-modal__header h3 { font-size: 17px; }
  .positions-modal__body { padding: 16px; }
  .positions-modal__footer { padding: 14px 16px; }
  .positions-modal .request-card__info { grid-template-columns: 100px minmax(0, 1fr); gap: 8px; }
}

@media (max-width: 420px) {
  .applications-section .request-card__info { grid-template-columns: 1fr; gap: 1px; }
  .positions-modal .request-card__info { grid-template-columns: 1fr; gap: 2px; }
}

.main-branch-hr-panel { width: 100%; padding: 0; background: transparent; height: auto; min-height: 0; display: block; }

.main-branch-hr-panel :deep(.owner-panel-topbar) {
  position: fixed;
  top: 0;
  right: 0;
  z-index: 300;
  box-shadow: 0 10px 18px rgba(15, 23, 42, 0.12);
}

.main-branch-hr-panel :deep(.admin-main) {
  margin-top: 66px !important;
  scrollbar-width: thin;
  scrollbar-color: #6b7280 #eee5df;
}

.main-branch-hr-panel :deep(.admin-main::-webkit-scrollbar) {
  width: 9px;
}

.main-branch-hr-panel :deep(.admin-main::-webkit-scrollbar-track) {
  background: #eee5df;
}

.main-branch-hr-panel :deep(.admin-main::-webkit-scrollbar-thumb) {
  background: #6b7280;
  border-radius: 999px;
  border: 2px solid #eee5df;
}

.main-branch-hr-panel :deep(.admin-main::-webkit-scrollbar-thumb:hover) {
  background: #4b5563;
}

@media (max-width: 767px) {
  .main-branch-hr-panel :deep(.owner-panel-topbar) {
    left: 0;
    width: 100%;
    margin-left: 0;
  }
}

.main-branch-hr-sidebar-nav {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  width: 100%;
}

.main-branch-hr-sidebar-link {
  width: 100%;
  padding: 0.7rem 0.75rem;
  border: 1px solid transparent;
  border-radius: 12px;
  background: transparent;
  color: #1f2937;
  font-size: 0.78rem;
  font-weight: 600;
  line-height: 1.25;
  text-align: left;
  cursor: pointer;
}

.main-branch-hr-sidebar-link:hover,
.main-branch-hr-sidebar-link--active {
  background: rgba(255, 255, 255, 0.85);
  border-color: rgba(148, 163, 184, 0.3);
  color: #111827;
  box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
}

.main-branch-hr-sidebar-actions {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.main-branch-hr-sidebar-account,
.main-branch-hr-sidebar-logout {
  width: 100%;
  padding: 0.7rem 0.75rem;
  border-radius: 12px;
  font-size: 0.78rem;
  font-weight: 600;
  cursor: pointer;
}

.main-branch-hr-sidebar-account:focus,
.main-branch-hr-sidebar-account:focus-visible,
.main-branch-hr-sidebar-logout:focus,
.main-branch-hr-sidebar-logout:focus-visible {
  outline: none;
  box-shadow: none;
}

.main-branch-hr-sidebar-account {
  border: 1px solid rgba(59, 130, 246, 0.25);
  background: rgba(59, 130, 246, 0.12);
  color: #30445a;
}

.main-branch-hr-sidebar-logout {
  border: 1px solid rgba(138, 113, 95, 0.25);
  background: rgba(255, 159, 67, 0.12);
  color: #a23d32;
}

.main-branch-hr-page { width: 100%; }
.main-branch-hr-section-view { min-height: 1px; }

.main-branch-hr-section-enter-active,
.main-branch-hr-section-leave-active {
  transition: opacity 220ms ease, transform 220ms cubic-bezier(0.22, 1, 0.36, 1);
}

.main-branch-hr-section-enter-from {
  opacity: 0;
  transform: translateY(10px);
}

.main-branch-hr-section-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}

.main-branch-hr-hero {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 20px;
  padding: 18px 16px;
  margin-bottom: 14px;
  background: linear-gradient(135deg, #fffaf5 0%, #fff 72%);
  border: 1px solid #f1e5d8;
  border-radius: 14px;
  box-shadow: 0 4px 14px rgba(66,33,11,0.05);
}
.main-branch-hr-eyebrow { display: inline-block; margin-bottom: 6px; color: #c25a12; font-size: 10px; font-weight: 800; letter-spacing: 0.12em; text-transform: uppercase; }
.main-branch-hr-title { margin: 0; color: #1f2937; font-size: 26px; line-height: 1.1; }
.main-branch-hr-subtitle { margin: 6px 0 0; color: #64748b; font-size: 13px; max-width: 560px; }
.main-branch-hr-hero__action { flex-shrink: 0; margin-top: 2px; }
.main-branch-hr-profile { margin-bottom: 14px; }


/* Position Requests Section */
.position-requests-section {
  background: linear-gradient(135deg, #fffaf5 0%, #fff 72%);
  border: 1px solid #f1e5d8;
  border-radius: 14px;
  padding: 18px 16px;
  margin-bottom: 14px;
  box-shadow: 0 4px 14px rgba(66, 33, 11, 0.05);
  display: block;
  height: auto;
}

.applications-section {
  margin-top: 14px;
  padding: 18px 16px;
  background: linear-gradient(135deg, #fffaf5 0%, #fff 72%);
  border: 1px solid #f1e5d8;
  border-radius: 14px;
  box-shadow: 0 4px 14px rgba(66, 33, 11, 0.05);
}

.pending-badge {
  display: inline-block;
  background: #fef3c7;
  color: #92400e;
  border: 1px solid #fcd34d;
  padding: 2px 8px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: bold;
  margin-top: 3px;
}

.requests-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-top: 10px;
}

.request-card {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 10px;
}

.request-card--approved { border-left: 4px solid #22c55e; }
.request-card--rejected { border-left: 4px solid #ef4444; }
.request-card--pending { border-left: 4px solid #f59e0b; }

.request-card__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.request-card__position {
  font-weight: 700;
  font-size: 13px;
  color: #111827;
}

.request-card__status {
  padding: 2px 7px;
  border-radius: 4px;
  font-size: 10px;
  font-weight: 600;
}

.status-approved { background: #dcfce7; color: #166534; }
.status-rejected { background: #fee2e2; color: #991b1b; }
.status-pending { background: #fef3c7; color: #92400e; }

.request-card__info {
  display: grid;
  grid-template-columns: 92px minmax(0, 1fr);
  gap: 6px;
  margin-bottom: 4px;
  font-size: 12px;
}

.request-card__info .label {
  color: #6b7280;
  font-weight: 600;
}

.request-card__info .value {
  color: #111827;
}

.request-card__notes {
  margin-top: 8px;
  padding-top: 8px;
  border-top: 1px solid #e5e7eb;
}

.request-card__notes .label {
  font-weight: 600;
  font-size: 11px;
  color: #6b7280;
}

.request-card__notes p {
  margin: 2px 0 0 0;
  font-size: 12px;
  color: #111827;
}

.request-card__notes--rejection {
  background: #fef2f2;
  padding: 8px;
  border-radius: 6px;
}

.request-card__actions {
  display: flex;
  gap: 8px;
  margin-top: 10px;
  padding-top: 10px;
  border-top: 1px solid #e5e7eb;
}

.btn-success,
.btn-danger,
.btn-secondary,
.btn-primary {
  background: #4b5563;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  font-size: 12px;
}

.position-requests-section .btn-success,
.position-requests-section .btn-primary {
  background: #1f2937;
}

.position-requests-section .btn-danger {
  background: #fff8f7;
  border: 1px solid #e3b1a5;
  color: #a23d32;
}

.btn-success:hover,
.btn-danger:hover,
.btn-secondary:hover,
.btn-primary:hover {
  background: #374151;
}

.position-requests-section .btn-danger:hover {
  background: #fee2e2;
}

.position-requests-section .section-header {
  align-items: center;
  margin-bottom: 18px;
}

.position-requests-section .section-header h3 {
  margin: 0;
  color: #42210b;
  font-size: 20px;
  line-height: 1.3;
}

.position-requests-section .section-header .muted {
  margin-top: 5px;
  font-size: 13px;
  text-align: left;
}

.position-requests-section .section-header .pending-badge {
  margin-top: 9px;
  padding: 5px 10px;
  border-color: #f2c9a5;
  border-radius: 999px;
  background: #fff3e8;
  color: #9a4c17;
  font-size: 12px;
}

.position-requests-section > .section-header .pill-btn {
  width: auto;
  min-height: 38px;
  padding: 9px 16px;
  border-radius: 9px;
  background: #42210b;
  box-shadow: 0 3px 8px rgba(66, 33, 11, 0.12);
}

.position-requests-section > .section-header .pill-btn:hover:not(:disabled) {
  background: #5b3011;
  transform: translateY(-1px);
}

.position-requests-section .requests-list {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 14px;
  margin-top: 0;
}

.position-requests-section .request-card {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  align-content: start;
  column-gap: 20px;
  row-gap: 2px;
  min-width: 0;
  padding: 17px;
  border: 1px solid #eadfd4;
  border-left: 4px solid #c8b8a8;
  border-radius: 12px;
  background: #fff;
  box-shadow: 0 3px 10px rgba(66, 33, 11, 0.045);
  transition: transform 180ms ease, box-shadow 180ms ease, border-color 180ms ease;
}

.position-requests-section .request-card--approved { border-left-color: #22a35a; }
.position-requests-section .request-card--rejected { border-left-color: #d95b50; }
.position-requests-section .request-card--pending { border-left-color: #d97706; }

.position-requests-section .request-card__header,
.position-requests-section .request-card__notes,
.position-requests-section .request-card__actions {
  grid-column: 1 / -1;
}

.position-requests-section .request-card__header {
  align-items: flex-start;
  gap: 12px;
  margin-bottom: 12px;
}

.position-requests-section .request-card__position {
  color: #42210b;
  font-size: 15px;
  line-height: 1.4;
}

.position-requests-section .request-card__status {
  flex: 0 0 auto;
  padding: 5px 9px;
  border-radius: 999px;
  font-size: 10px;
  letter-spacing: 0.025em;
  text-transform: uppercase;
}

.position-requests-section .request-card__info {
  grid-template-columns: minmax(78px, auto) minmax(0, 1fr);
  gap: 8px;
  margin-bottom: 7px;
  font-size: 12px;
  line-height: 1.45;
}

.position-requests-section .request-card__info .label {
  color: #806a58;
}

.position-requests-section .request-card__info .value {
  min-width: 0;
  color: #382b21;
  overflow-wrap: anywhere;
}

.position-requests-section .request-card__notes {
  margin-top: 7px;
  padding: 12px 0 0;
  border-top: 1px solid #eee3d8;
}

.position-requests-section .request-card__notes .label {
  color: #806a58;
  font-size: 11px;
}

.position-requests-section .request-card__notes p {
  margin-top: 5px;
  color: #382b21;
  font-size: 12px;
  line-height: 1.55;
  overflow-wrap: anywhere;
}

.position-requests-section .request-card__notes--rejection {
  padding: 10px;
  border: 1px solid #f3d0cd;
  border-radius: 8px;
  background: #fff5f4;
}

.position-requests-section .request-card__actions {
  align-items: center;
  gap: 9px;
  margin-top: 10px;
  padding-top: 13px;
  border-color: #eee3d8;
}

.position-requests-section .request-card__actions .btn-success,
.position-requests-section .request-card__actions .btn-danger {
  min-height: 36px;
  padding: 8px 13px;
  border-radius: 8px;
}

.position-requests-section .request-card__actions .btn-success {
  background: #a4511b;
}

.position-requests-section .request-card__actions .btn-success:hover:not(:disabled) {
  background: #873f14;
}

.position-requests-section .request-card__actions .btn-danger {
  border-color: #e8c9c4;
  background: #fff;
  color: #a23d32;
}

.position-requests-section .request-card__actions .btn-danger:hover:not(:disabled) {
  background: #fff1ef;
}

@media (hover: hover) {
  .position-requests-section .request-card:hover {
    transform: translateY(-2px);
    border-color: #dfc8b4;
    box-shadow: 0 10px 22px rgba(66, 33, 11, 0.09);
  }
}

@media (max-width: 900px) {
  .position-requests-section .requests-list {
    grid-template-columns: minmax(0, 1fr);
  }
}

@media (max-width: 600px) {
  .position-requests-section {
    padding: 16px 12px;
  }

  .position-requests-section .section-header {
    align-items: stretch;
  }

  .position-requests-section > .section-header .pill-btn {
    align-self: flex-start;
  }

  .position-requests-section .request-card {
    grid-template-columns: minmax(0, 1fr);
    padding: 14px;
  }

  .position-requests-section .request-card__header {
    flex-wrap: wrap;
  }

  .position-requests-section .request-card__info {
    grid-template-columns: 94px minmax(0, 1fr);
  }

  .position-requests-section .request-card__actions {
    flex-wrap: wrap;
  }
}

.btn-success:disabled,
.btn-danger:disabled,
.btn-secondary:disabled,
.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Reject Modal */
.reject-modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 3000;
}

.reject-modal {
  width: 92%;
  max-width: 480px;
  background: #fff;
  border-radius: 14px;
  box-shadow: 0 12px 50px rgba(0,0,0,0.25);
  overflow: hidden;
}

.reject-modal__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 14px;
  background: #f9fafb;
  border-bottom: 1px solid #e5e7eb;
}

.reject-modal__header h3 {
  margin: 0;
  font-size: 16px;
  font-weight: 700;
  color: #111827;
}

.reject-modal__body {
  padding: 12px 14px;
}

.reject-modal__body p {
  margin: 0 0 12px 0;
  color: #374151;
  font-size: 13px;
}

.reject-modal__footer {
  padding: 12px 14px;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}
.hero { margin-bottom: 0; display: none; }
.panel-section { display: block; background: transparent; padding: 0; }
.position-requests-section + .branch-stats {
  margin-top: 0;
}
.section-title { font-size: 20px; font-weight: 700; color: #1f2937; margin: 0 0 3px 0; letter-spacing: -0.2px; }
.section-description { margin: 0 0 4px 0; color: #6b7280; font-size: 13px; }
.info-box { background: transparent; border: none; border-radius: 0; padding: 0; width: 100%; box-shadow: none; height: auto; margin-bottom: 0; display: none; }
.info-box p { margin: 0; color: #374151; font-size: 13px; line-height: 1.4; }
.info-box p:first-child { margin-top: 0; }
.info-box p:last-child { margin-bottom: 0; }

.hr-summary-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; margin: 0 0 14px; }
.hr-summary-card { display: flex; flex-direction: column; gap: 4px; padding: 12px 14px; background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 8px; }
.hr-summary-card strong { color: #111827; font-size: 22px; line-height: 1; }
.hr-summary-label { color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; }
.hr-summary-card--pending { border-left: 3px solid #f59e0b; }
.hr-summary-card--active { border-left: 3px solid #22c55e; }

.branch-stats {
  width: 100%;
  padding: 16px;
  background: linear-gradient(135deg, #fffaf5 0%, #fff 72%);
  border: 1px solid #f1e5d8;
  border-radius: 14px;
  box-shadow: 0 4px 14px rgba(66, 33, 11, 0.05);
  height: auto;
  min-height: 0;
  box-sizing: border-box;
}
.section-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; margin: 0 0 6px 0; flex-wrap: wrap; }
.muted { color: #6b7280; margin: 2px 0 0 0; font-size: 12px; }
.pill-btn { background: #4b5563; color: white; border: none; padding: 8px 14px; border-radius: 6px; font-weight: 600; cursor: pointer; box-shadow: 0 1px 3px rgba(75, 85, 99, 0.1); transition: all 0.2s ease; font-size: 12px; }
.pill-btn:hover { transform: translateY(-1px); box-shadow: 0 2px 6px rgba(75, 85, 99, 0.12); }
.pill-btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
.branch-stats > .section-header {
  align-items: center;
  gap: 12px;
  margin-bottom: 12px;
}
.branch-stats > .section-header h3 {
  margin: 0;
  color: #42210b;
  font-size: 21px;
  line-height: 1.3;
}
.branch-stats > .section-header .muted {
  margin-top: 3px;
  text-align: left;
  font-size: 14px;
}
.branch-stats > .section-header .pill-btn {
  width: auto;
  min-height: 36px;
  padding: 8px 14px;
  border-radius: 8px;
  background: #42210b;
}
.account-filters { display: flex; gap: 8px; margin: 0 0 12px; }
.account-search, .account-status-filter { min-height: 40px; border: 1px solid #e1d3c5; border-radius: 8px; background: #fff; color: #382b21; font-size: 14px; padding: 8px 12px; }
.account-search { flex: 1; min-width: 180px; }
.account-search:focus, .account-status-filter:focus { outline: 3px solid rgba(194,90,18,0.12); border-color: #c8773b; }
.sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); white-space: nowrap; border: 0; }

.branch-grid { display: flex; flex-direction: column; gap: 10px; width: 100%; align-items: stretch; }
.branch-card { background: #fff; border: 1px solid #eadfd4; border-radius: 10px; box-shadow: 0 2px 8px rgba(66, 33, 11, 0.035); padding: 0; display: flex; flex-direction: column; justify-content: flex-start; align-items: stretch; gap: 0; aspect-ratio: auto; text-align: left; cursor: default; transition: border-color 160ms ease, box-shadow 160ms ease; width: 100%; min-width: 0; align-self: stretch; margin: 0; overflow: hidden; }
.branch-card:hover { border-color: #dfc8b4; box-shadow: 0 5px 14px rgba(66, 33, 11, 0.07); }

.branch-card__header { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 10px 12px; border-bottom: 1px solid #eadfd4; flex-shrink: 0; background: #fffaf5; margin: 0; border-radius: 0; }
.header-content { flex: 1; }
.header-top { display: flex; align-items: center; gap: 8px; margin-bottom: 3px; }
.branch-name { margin: 0; font-size: 17px; font-weight: 700; color: #111827; }
.badge-branch { background: #fff0e2; color: #9a4c17; padding: 4px 8px; border-radius: 999px; font-weight: 700; font-size: 10px; text-transform: uppercase; letter-spacing: 0.3px; }
.branch-subtitle { margin: 0; color: #6b7280; font-size: 13px; display: flex; gap: 12px; }
.stat-total { font-weight: 600; color: #374151; }
.stat-active { color: #16834a; font-weight: 600; }

.table-container { width: 100%; overflow-x: auto; border: 0; border-radius: 0; background: #ffffff; margin-top: 0; }
.staff-table { width: 100%; border-collapse: collapse; background: #ffffff; table-layout: fixed; font-size: 12px; }
.staff-table thead { position: sticky; top: 0; z-index: 10; }
.staff-table th { padding: 8px 10px; text-align: left; font-size: 12px; font-weight: 700; color: #72563f; background: #f8f4ef; border-bottom: 1px solid #eadfd4; text-transform: uppercase; letter-spacing: 0.35px; white-space: nowrap; }
.staff-table td { padding: 8px 10px; text-align: left; font-size: 13px; color: #382b21; border-bottom: 1px solid #f0e9e2; height: auto; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.staff-table tbody tr:last-child td { border-bottom: 0; }
.staff-row { transition: background-color 0.2s ease; }
.staff-row:hover { background-color: #fffaf5; }

.col-name { width: 27%; }
.col-username { width: 19%; }
.col-role { width: 14%; }
.col-dept { width: 14%; }
.col-status { width: 13%; }
.col-account-action { width: 13%; }

.btn-account-details {
  border: 1px solid #e6c5a8;
  border-radius: 7px;
  padding: 5px 9px;
  background: #fff8f1;
  color: #8f4514;
  font-size: 11px;
  font-weight: 700;
  cursor: pointer;
}
.btn-account-details:hover,
.btn-account-details:focus-visible {
  border-color: #c8773b;
  background: #fff0e2;
  outline: none;
}

.account-details-modal { max-width: 720px; }
.account-details-heading { display: flex; align-items: center; gap: 12px; min-width: 0; }
.account-details-heading h3 { overflow-wrap: anywhere; }
.account-details-avatar {
  width: 48px;
  height: 48px;
  flex: 0 0 48px;
  border-radius: 50%;
  object-fit: cover;
  background: #fce7d5;
}
.account-details-avatar--fallback {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: #9a4c17;
  font-size: 18px;
  font-weight: 800;
}
.account-details-body { padding-top: 18px; }
.account-details-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 10px;
}
.account-detail {
  display: flex;
  flex-direction: column;
  gap: 5px;
  min-width: 0;
  padding: 11px 12px;
  border: 1px solid #f0e4d9;
  border-radius: 9px;
  background: #fffaf5;
}
.account-detail span { color: #8a705e; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; }
.account-detail strong { color: #382b21; font-size: 13px; font-weight: 600; overflow-wrap: anywhere; }

.name-badge { display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; border-radius: 7px; background: #fce7d5; color: #9a4c17; font-weight: 700; font-size: 11px; margin-right: 7px; vertical-align: middle; }
.code-text { background: #f0f1f3; padding: 2px 4px; border-radius: 3px; font-family: 'Courier New', monospace; font-size: 11px; color: #6b7280; }

.role-badge { display: inline-block; padding: 3px 6px; border-radius: 4px; font-weight: 600; font-size: 10px; text-transform: uppercase; letter-spacing: 0.1px; }
.role-admin { background: #fee2e2; color: #991b1b; }
.role-branch_manager { background: #fef3c7; color: #92400e; }
.role-hr { background: #e0e7ff; color: #3730a3; }
.role-manager { background: #dbeafe; color: #0c4a6e; }
.role-staff { background: #dcfce7; color: #166534; }
.role-finance { background: #f3e8ff; color: #6b21a8; }
.role-logistics { background: #fce7f3; color: #831843; }
.role-procurement { background: #fef8e0; color: #78350f; }
.role-custom { background: #e0f2fe; color: #0369a1; }
.role-unknown { background: #f3f4f6; color: #6b7280; }

.status-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 7px; border-radius: 5px; font-weight: 600; font-size: 11px; }
.status-active { background: #dcfce7; color: #166534; }
.status-inactive { background: #fee2e2; color: #991b1b; }
.status-dot { width: 6px; height: 6px; border-radius: 50%; display: inline-block; }
.dot-active { background: #22c55e; box-shadow: 0 0 2px rgba(34, 197, 94, 0.3); }
.dot-inactive { background: #ef4444; }

.empty-branch { color: #9ca3af; font-style: italic; padding: 12px; text-align: center; font-size: 12px; background: #ffffff; border: 1px solid #d1d5db; border-top: none; }
.loading-box, .empty-box { padding: 12px; color: #6b7280; background: #f9fafb; border: 1px dashed #d1d5db; border-radius: 6px; text-align: center; font-size: 12px; }
.alert-danger { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; padding: 10px 12px; border-radius: 6px; font-size: 12px; }

@media (max-width: 1024px) {
  .branch-grid { gap: 6px; }
}

@media (max-width: 768px) {
  .main-branch-hr-tutorial-backdrop { padding: 12px; }
  .main-branch-hr-tutorial-modal { max-height: 92vh; border-radius: 18px; }
  .main-branch-hr-tutorial-header,
  .main-branch-hr-tutorial-body { padding-right: 18px; padding-left: 18px; }
  .main-branch-hr-tutorial-progress { gap: 5px; padding-right: 18px; padding-left: 18px; }
  .main-branch-hr-tutorial-footer { flex-wrap: wrap; padding: 14px 18px 18px; }
  .main-branch-hr-tutorial-button--section { order: 3; width: 100%; }
  .main-branch-hr-panel { padding: 0; }
  .main-branch-hr-hero { flex-direction: column; gap: 12px; }
  .main-branch-hr-hero__action { width: 100%; }
  .branch-stats { padding: 12px; }
  .section-header { flex-direction: column; align-items: stretch; gap: 8px; }
  .branch-stats > .section-header { align-items: stretch; }
  .branch-stats > .section-header .pill-btn { align-self: flex-start; }
  .pill-btn { width: 100%; padding: 8px 12px; }
  .hr-summary-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  .account-filters { flex-direction: column; }
  .account-search, .account-status-filter { width: 100%; box-sizing: border-box; }
  .col-dept { display: none; }
  .branch-card { padding: 0; }
  .staff-table th, .staff-table td { padding: 7px 6px; font-size: 11px; }
  .account-details-grid { grid-template-columns: 1fr; }
  .name-badge { width: 22px; height: 22px; font-size: 10px; margin-right: 4px; }
  .header-top { flex-direction: column; align-items: flex-start; gap: 2px; }
  .branch-grid { gap: 2px; }
}

/* Hide header title and description, keep actions */
:deep(.admin-main-header > .admin-main-header-top > div:nth-child(1)),
:deep(.admin-main-header > .admin-main-header-top > div:nth-child(2)) {
  display: none;
}

/* Make header actions full width and centered */
:deep(.admin-main-header) {
  padding: 0;
}

:deep(.admin-main-header-top) {
  justify-content: flex-end;
  gap: 0;
}
</style>
