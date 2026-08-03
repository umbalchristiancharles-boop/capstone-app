<template>
  <OwnerPanelLayout
    :userProfile="userProfile"
    :panelTitle="'Manager HR Panel'"
    :panelDescription="'Manage staff, view HR reports, and monitor staff status.'"
    :enableProfileUpdate="true"
    :canEditProfile="userProfile.role === 'OWNER'"
    :canChangePassword="true"
    :showHeader="false"
    :showProfileColumn="false"
    :ownerTwoColumnLayout="true"
    @logout="askLogout"
    @profile-updated="onProfileUpdated"
  >
    <template #sideTop>
      <div ref="profileWrapper" class="header-profile-wrapper manager-hr-compact-profile">
        <button class="header-profile-btn" type="button" @click.stop="toggleProfileDropdown">
          <div class="header-avatar">
            <div class="header-avatar-initials">
              {{ (userProfile.fullName || userProfile.full_name || userProfile.name || userProfile.role || 'H').toString().charAt(0).toUpperCase() }}
            </div>
          </div>
          <div class="header-name">
            {{ ((userProfile.role || 'HR').toString() + ' - ' + (userProfile.branch || userProfile.branch_name || 'Dasma Branch')).toUpperCase() }}
          </div>
        </button>
        <div v-if="showProfileDropdown" class="header-profile-dropdown">
          <button class="dropdown-item" type="button" @click.prevent="openInfo">Info</button>
          <button class="dropdown-item" type="button" @click.prevent="goToStaffManagement()">Staff Management</button>
          <button class="dropdown-item" type="button" @click.prevent="askLogout">Logout</button>
        </div>
      </div>
    </template>
    <template #main>
      <div class="manager-hr-page">
        <header class="manager-hr-hero">
          <div class="manager-hr-hero__copy">
            <span class="manager-hr-hero__eyebrow">Manager dashboard</span>
            <h2 class="manager-hr-hero__title">HR overview</h2>
            <p class="manager-hr-hero__subtitle">Manage attendance, review applications, and request open positions from one place.</p>
          </div>

          <div class="positions-top-actions manager-hr-hero__actions">
            <button class="panel-action panel-action--primary" @click="openApplicationsModal" :disabled="loadingApplications">
              {{ loadingApplications ? 'Loading...' : 'View Applications' }}
            </button>
            <span v-if="applicationsCount > 0" class="panel-badge">{{ applicationsCount }} total</span>
            <button class="panel-action panel-action--primary" @click="openPositionsModal" :disabled="positionsLoading">
              {{ positionsLoading ? 'Loading...' : 'Request Open Positions' }}
            </button>
          </div>
        </header>

      <!-- Job Applications (HR Manager View) -->
      <transition name="fade">
        <div v-if="showApplicationsModal" class="positions-modal-backdrop" @click.self="closeApplicationsModal">
          <div class="positions-modal">
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
                    <span class="label">Status:</span>
                    <span class="value">{{ a.status || 'Submitted' }}</span>
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
                     
                     <!-- Show button only if status is NOT "Ready for Interview" -->
                     <button 
                       v-if="!isReadyForInterview(a.status)"
                       class="btn-primary btn-sm" 
                       @click="openInterviewScheduleModal(a)"
                       :disabled="sendingInterviewEmail[a.id]"
                     >
                       {{ sendingInterviewEmail[a.id] ? 'Sending...' : 'Ready for Interview' }}
                     </button>
                     
                     <!-- Show indicator when status is "Ready for Interview" -->
                     <span v-else class="badge badge--success" style="padding: 0.35rem 0.75rem; font-size: 0.85rem;">
                       ✓ Interview Scheduled
                     </span>
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
      <transition name="fade">
        <div v-if="showInterviewScheduleModal" class="positions-modal-backdrop" @click.self="closeInterviewScheduleModal">
          <div class="positions-modal">
            <div class="positions-modal__header">
              <div>
                <h3>Schedule Interview</h3>
                <p class="muted">Select date and time for the interview with {{ selectedApplication?.applicant_full_name }}</p>
              </div>
              <button class="modal-close" @click="closeInterviewScheduleModal" aria-label="Close">✕</button>
            </div>

            <div class="positions-modal__body">
              <div v-if="selectedApplication" class="interview-schedule-form">
                <div class="form-group">
                  <label class="field-label">Interview Date *</label>
                  <input 
                    type="date" 
                    class="field-input" 
                    v-model="interviewSchedule.date"
                    :min="getMinDate()"
                    required
                  />
                </div>

                <div class="form-group">
                  <label class="field-label">Interview Time *</label>
                  <input 
                    type="time" 
                    class="field-input" 
                    v-model="interviewSchedule.time"
                    required
                  />
                </div>

                <div class="form-group">
                  <label class="field-label">Additional Notes (Optional)</label>
                  <textarea 
                    class="field-textarea" 
                    rows="3" 
                    v-model="interviewSchedule.notes"
                    placeholder="e.g., Bring portfolio, interview with HR Manager, etc."
                  ></textarea>
                </div>

                <div class="interview-summary" v-if="interviewSchedule.date && interviewSchedule.time">
                  <p><strong>Interview Scheduled:</strong></p>
                  <p>{{ formatInterviewDate(interviewSchedule.date) }} at {{ formatInterviewTime(interviewSchedule.time) }}</p>
                </div>
              </div>
            </div>

            <div class="positions-modal__footer">
              <button class="btn-secondary" @click="closeInterviewScheduleModal">Cancel</button>
              <button 
                class="btn-primary" 
                @click="confirmInterviewSchedule"
                :disabled="!isInterviewScheduleValid() || sendingInterviewEmail[selectedApplication?.id]"
              >
                {{ sendingInterviewEmail[selectedApplication?.id] ? 'Sending...' : 'Send Interview Email' }}
              </button>
            </div>
          </div>
        </div>
      </transition>

      <!-- Application Details Modal -->
      <transition name="fade">
        <div v-if="showApplicationDetailsModal && selectedApplicationDetails" class="positions-modal-backdrop" @click.self="closeApplicationDetailsModal">
          <div class="positions-modal" style="max-width: 800px;">
            <div class="positions-modal__header">
              <div>
                <h3>Application Details</h3>
                <p class="muted">Complete application form information for {{ selectedApplicationDetails.applicant_full_name }}</p>
              </div>
              <button class="modal-close" @click="closeApplicationDetailsModal" aria-label="Close">✕</button>
            </div>

            <div class="positions-modal__body">
              <div v-if="selectedApplicationDetails" class="application-details-content">
                <!-- Personal Information Section -->
                <div class="details-section">
                  <h4 class="details-section__title">Personal Information</h4>
                  <div class="details-grid">
                    <div class="detail-item">
                      <span class="detail-label">Full Name:</span>
                      <span class="detail-value">{{ selectedApplicationDetails.applicant_full_name || '-' }}</span>
                    </div>
                    <div class="detail-item">
                      <span class="detail-label">Email:</span>
                      <span class="detail-value">{{ selectedApplicationDetails.applicant_email || '-' }}</span>
                    </div>
                    <div class="detail-item">
                      <span class="detail-label">Phone:</span>
                      <span class="detail-value">{{ selectedApplicationDetails.applicant_phone || '-' }}</span>
                    </div>
                    <div class="detail-item">
                      <span class="detail-label">Address:</span>
                      <span class="detail-value">{{ selectedApplicationDetails.applicant_address || '-' }}</span>
                    </div>
                    <div class="detail-item">
                      <span class="detail-label">Website:</span>
                      <span class="detail-value">
                        <a v-if="selectedApplicationDetails.website" :href="selectedApplicationDetails.website" target="_blank" rel="noopener noreferrer">{{ selectedApplicationDetails.website }}</a>
                        <span v-else>-</span>
                      </span>
                    </div>
                    <div class="detail-item">
                      <span class="detail-label">LinkedIn:</span>
                      <span class="detail-value">
                        <a v-if="selectedApplicationDetails.linkedin_url" :href="selectedApplicationDetails.linkedin_url" target="_blank" rel="noopener noreferrer">{{ selectedApplicationDetails.linkedin_url }}</a>
                        <span v-else>-</span>
                      </span>
                    </div>
                    <div class="detail-item">
                      <span class="detail-label">Portfolio:</span>
                      <span class="detail-value">
                        <a v-if="selectedApplicationDetails.portfolio_url" :href="selectedApplicationDetails.portfolio_url" target="_blank" rel="noopener noreferrer">{{ selectedApplicationDetails.portfolio_url }}</a>
                        <span v-else>-</span>
                      </span>
                    </div>
                  </div>
                </div>

                <!-- Position Information Section -->
                <div class="details-section">
                  <h4 class="details-section__title">Position Information</h4>
                  <div class="details-grid">
                    <div class="detail-item">
                      <span class="detail-label">Job Title:</span>
                      <span class="detail-value">{{ selectedApplicationDetails.job_title || '-' }}</span>
                    </div>
                    <div class="detail-item">
                      <span class="detail-label">Department:</span>
                      <span class="detail-value">{{ selectedApplicationDetails.department || '-' }}</span>
                    </div>
                    <div class="detail-item">
                      <span class="detail-label">Years of Experience:</span>
                      <span class="detail-value">{{ selectedApplicationDetails.years_of_experience || '-' }}</span>
                    </div>
                    <div class="detail-item">
                      <span class="detail-label">Education:</span>
                      <span class="detail-value">{{ selectedApplicationDetails.education || '-' }}</span>
                    </div>
                    <div class="detail-item">
                      <span class="detail-label">Available Start Date:</span>
                      <span class="detail-value">{{ formatDate(selectedApplicationDetails.available_start_date) }}</span>
                    </div>
                    <div class="detail-item">
                      <span class="detail-label">Status:</span>
                      <span class="detail-value">
                        <span class="badge" :class="getApplicationStatusClass(selectedApplicationDetails.status)">
                          {{ selectedApplicationDetails.status || 'Submitted' }}
                        </span>
                      </span>
                    </div>
                  </div>
                </div>

                <!-- Cover Letter Section -->
                <div class="details-section" v-if="selectedApplicationDetails.cover_letter">
                  <h4 class="details-section__title">Cover Letter</h4>
                  <div class="cover-letter-content">
                    {{ selectedApplicationDetails.cover_letter }}
                  </div>
                </div>

                <!-- Documents Section -->
                <div class="details-section">
                  <h4 class="details-section__title">Documents</h4>
                  <div class="details-grid">
                    <div class="detail-item">
                      <span class="detail-label">Resume/CV:</span>
                      <span class="detail-value">
                        <a v-if="selectedApplicationDetails.resume_path" 
                           :href="getStorageUrl(selectedApplicationDetails.resume_path)" 
                           target="_blank" 
                           rel="noopener noreferrer"
                           class="btn-link">
                          📄 View Resume
                        </a>
                        <span v-else class="text-muted">Not provided</span>
                      </span>
                    </div>
                    <div class="detail-item" v-if="selectedApplicationDetails.supporting_documents_paths && selectedApplicationDetails.supporting_documents_paths.length > 0">
                      <span class="detail-label">Supporting Documents:</span>
                      <span class="detail-value">
                        <div v-for="(doc, index) in selectedApplicationDetails.supporting_documents_paths" :key="index" style="margin-top: 0.25rem;">
                          <a :href="getStorageUrl(doc)" target="_blank" rel="noopener noreferrer" class="btn-link">
                            📎 Supporting Document {{ index + 1 }}
                          </a>
                        </div>
                      </span>
                    </div>
                  </div>
                </div>

                <!-- Interview Information Section (if available) -->
                <div class="details-section" v-if="selectedApplicationDetails.interview_date || selectedApplicationDetails.interview_time">
                  <h4 class="details-section__title">Interview Schedule</h4>
                  <div class="details-grid">
                    <div class="detail-item" v-if="selectedApplicationDetails.interview_date">
                      <span class="detail-label">Interview Date:</span>
                      <span class="detail-value">{{ formatInterviewDate(selectedApplicationDetails.interview_date) }}</span>
                    </div>
                    <div class="detail-item" v-if="selectedApplicationDetails.interview_time">
                      <span class="detail-label">Interview Time:</span>
                      <span class="detail-value">{{ formatInterviewTime(selectedApplicationDetails.interview_time) }}</span>
                    </div>
                    <div class="detail-item" v-if="selectedApplicationDetails.interview_notes">
                      <span class="detail-label">Interview Notes:</span>
                      <span class="detail-value">{{ selectedApplicationDetails.interview_notes }}</span>
                    </div>
                  </div>
                </div>

                <!-- Application Metadata -->
                <div class="details-section">
                  <h4 class="details-section__title">Application Information</h4>
                  <div class="details-grid">
                    <div class="detail-item">
                      <span class="detail-label">Application ID:</span>
                      <span class="detail-value">#{{ selectedApplicationDetails.id }}</span>
                    </div>
                    <div class="detail-item">
                      <span class="detail-label">Submitted On:</span>
                      <span class="detail-value">{{ formatDate(selectedApplicationDetails.created_at) }}</span>
                    </div>
                    <div class="detail-item">
                      <span class="detail-label">Privacy Consent:</span>
                      <span class="detail-value">
                        <span v-if="selectedApplicationDetails.privacy_consent" class="badge badge--success">✓ Given</span>
                        <span v-else class="badge badge--warning">Not Given</span>
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="positions-modal__footer">
              <button class="btn-secondary" @click="closeApplicationDetailsModal">Close</button>
              <a v-if="selectedApplicationDetails.resume_path" 
                 :href="getStorageUrl(selectedApplicationDetails.resume_path)" 
                 target="_blank" 
                 rel="noopener noreferrer"
                 class="btn-primary" 
                 style="text-decoration: none; display: inline-block; padding: 0.625rem 1.25rem; border-radius: 4px;"
                 @click.prevent="openResume(getStorageUrl(selectedApplicationDetails.resume_path))">
                Open Resume in New Tab
              </a>
            </div>
          </div>
        </div>
      </transition>

      <!-- POSITIONS REQUEST MODAL -->

      <transition name="fade">
        <div v-if="showPositionsModal" class="positions-modal-backdrop" @click.self="closePositionsModal">
          <div class="positions-modal">
            <div class="positions-modal__header">
              <div>
                <h3>Request Open Positions</h3>
                <p class="muted">Select a position, then set quantity and notes.</p>
              </div>
              <button class="modal-close" @click="closePositionsModal" aria-label="Close">✕</button>
            </div>

            <div class="positions-modal__body">
              <div v-if="positionsLoading" class="loading-box">Loading positions...</div>
              <div v-else-if="positions.length === 0" class="empty-box">No active positions found.</div>

              <div v-else class="positions-list">
                <div v-for="p in positions" :key="p.id" class="position-row">
                  <div class="position-row__meta">
                    <div class="position-row__name">{{ p.name }}</div>
                    <div class="position-row__dept">{{ p.department || '—' }}</div>
                  </div>

                  <div class="position-row__inputs">
                    <label class="field">
                      <span class="field-label">Quantity</span>
                      <input
                        type="number"
                        min="1"
                        class="field-input"
                        v-model.number="requestQuantities[p.id]"
                        :placeholder="'1'"
                      />
                    </label>

                    <label class="field">
                      <span class="field-label">Notes</span>
                      <textarea
                        class="field-textarea"
                        rows="2"
                        v-model.trim="requestNotes[p.id]"
                        placeholder="Optional"
                      ></textarea>
                    </label>
                  </div>
                </div>
              </div>
            </div>

            <div class="positions-modal__footer">
              <button class="btn-secondary" @click="closePositionsModal" :disabled="submittingPositions">Cancel</button>
              <button
                class="btn-primary"
                @click="submitPositionsRequests"
                :disabled="submittingPositions || positionsLoading"
              >
                {{ submittingPositions ? 'Submitting...' : 'Submit Request(s)' }}
              </button>
            </div>
          </div>
        </div>
      </transition>

      <!-- HR Positions Management -->
      <!-- Bento-style Stats Cards -->
      <div class="manager-hr-main-wrapper">
        <div class="hr-stats-grid manager-hr-stats-grid">
          <div class="hr-stat-card hr-stat-card--total">
            <div class="hr-stat-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            </div>
            <div class="hr-stat-content">
              <span class="hr-stat-label">Total Staff</span>
              <span class="hr-stat-value">{{ dashboardTotals.totalStaff }}</span>
            </div>
          </div>
          <div class="hr-stat-card hr-stat-card--active">
            <div class="hr-stat-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
            </div>
            <div class="hr-stat-content">
              <span class="hr-stat-label">Active Staff</span>
              <span class="hr-stat-value">{{ dashboardTotals.activeStaff }}</span>
            </div>
          </div>
          <div class="hr-stat-card hr-stat-card--leave">
            <div class="hr-stat-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
            </div>
            <div class="hr-stat-content">
              <span class="hr-stat-label">On Leave</span>
              <span class="hr-stat-value">{{ dashboardTotals.onLeave }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Clock-in Confirmation Section -->
      <section class="panel-block hr-confirmation-panel">
        <div class="panel-header hr-confirmation-header">
          <h2>
            📸 Pending Clock-in Confirmations
            <span v-if="pendingConfirmations.length > 0" class="panel-badge">{{ pendingConfirmations.length }}</span>
          </h2>
          <button class="panel-action" @click="loadPendingConfirmations" :disabled="isLoadingConfirmations">
            {{ isLoadingConfirmations ? 'Loading...' : 'Refresh' }}
          </button>
        </div>

        <div class="panel-body panel-body--table">
          <div v-if="isLoadingConfirmations" class="table-row">
            <span colspan="6">Loading pending confirmations...</span>
          </div>

          <div v-else-if="pendingConfirmations.length === 0" class="table-row">
            <span colspan="6">No pending clock-in confirmations for today.</span>
          </div>

          <div v-else class="confirmations-list">
            <div v-for="conf in pendingConfirmations" :key="conf.id" class="confirmation-card">
              <div class="confirmation-info">
                <div class="confirmation-header">
                  <span class="confirmation-name">{{ conf.user_name }}</span>
                  <span class="confirmation-branch">{{ conf.branch_name }}</span>
                </div>
                <div class="confirmation-details">
                  <span>⏰ {{ conf.time_in }}</span>
                  <span>📅 {{ conf.date }}</span>
                  <span class="badge" :class="attendanceStatusClass(conf.status)">{{ conf.status }}</span>
                </div>
              </div>
              <div class="confirmation-actions">
                <button class="btn-sm btn-success" @click="viewConfirmation(conf)">
                  View Photo
                </button>
                <button class="btn-sm btn-primary" @click="confirmClockIn(conf.id)">
                  ✓ Confirm
                </button>
                <button class="btn-sm btn-danger" @click="rejectClockIn(conf.id)">
                  ✗ Reject
                </button>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="panel-block hr-attendance-panel">
        <div class="panel-header hr-attendance-header">
          <h2>
            Attendance Monitoring
            <span v-if="hrAlertCount > 0" class="panel-badge">{{ hrAlertCount }}</span>
          </h2>
          <div class="hr-attendance-actions">
            <select v-model="attendanceRange" @change="loadHrAttendance(attendanceRange)" class="hr-attendance-select">
              <option value="today">Today</option>
              <option value="thisWeek">This Week</option>
              <option value="thisMonth">This Month</option>
            </select>
            <button class="panel-action" @click="loadHrAttendance(attendanceRange)">Refresh</button>
          </div>
        </div>

        <div class="panel-body panel-body--table">
          <div class="table-header">
            <span>Staff Name</span>
            <span>Branch</span>
            <span>Time In</span>
            <span>Time Out</span>
            <span>Hours</span>
            <span>Status</span>
          </div>

          <div v-if="isLoadingAttendance" class="table-row">
            <span>Loading attendance...</span>
            <span></span><span></span><span></span><span></span><span></span>
          </div>

          <div v-else-if="hrAttendance.length === 0" class="table-row">
            <span>No attendance records for this range.</span>
            <span></span><span></span><span></span><span></span><span></span>
          </div>

          <div v-else v-for="att in hrAttendance" :key="att.id" class="table-row">
            <span>{{ att.user_name }}</span>
            <span>{{ att.branch_name || '-' }}</span>
            <span>{{ att.time_in || '-' }}</span>
            <span>{{ att.time_out || '-' }}</span>
            <span>{{ att.hours_worked || '-' }}</span>
            <span>
              <span class="badge" :class="attendanceStatusClass(att.status)">{{ att.status || '-' }}</span>
            </span>
          </div>
        </div>
      </section>

      <!-- Payroll Tracking Section -->
      <section class="panel-block hr-payroll-panel">
        <div class="panel-header hr-payroll-header">
          <h2>Payroll Management</h2>
          <div class="hr-payroll-actions">
            <button class="panel-action panel-action--primary" @click="openPayrollModal">
              Generate Payroll
            </button>
            <button class="panel-action" @click="loadPayrolls">Refresh</button>
          </div>
        </div>

        <div class="panel-body panel-body--table">
          <div class="table-header">
            <span>Staff Name</span>
            <span>Pay Period</span>
            <span>Type</span>
            <span>Days Worked</span>
            <span>Late Days</span>
            <span>Overtime</span>
            <span>Net Salary</span>
            <span>Status</span>
            <span>Actions</span>
          </div>

          <div v-if="isLoadingPayroll" class="table-row">
            <span colspan="9">Loading payroll...</span>
          </div>

          <div v-else-if="payrolls.length === 0" class="table-row">
            <span colspan="9">No payroll records found. Click "Generate Payroll" to create payroll for this period.</span>
          </div>

          <div v-else v-for="payroll in payrolls" :key="payroll.id" class="table-row">
            <span>{{ payroll.user?.full_name || '-' }}</span>
            <span>{{ formatPayPeriod(payroll.pay_period_start, payroll.pay_period_end) }}</span>
            <span>{{ payroll.payroll_type === 'mid_month' ? 'Mid-Month' : 'End of Month' }}</span>
            <span>{{ payroll.days_worked }}</span>
            <span>{{ payroll.days_late }}</span>
            <span>{{ payroll.days_overtime }} days ({{ payroll.total_overtime_hours }} hrs)</span>
            <span>₱{{ formatNumber(payroll.net_salary) }}</span>
            <span>
              <span class="badge" :class="getPayrollStatusClass(payroll.status)">{{ formatPayrollStatus(payroll.status) }}</span>
            </span>
            <span>
              <button v-if="payroll.status === 'pending'" class="btn-sm btn-success" @click="approvePayroll(payroll.id)">
                Approve
              </button>
              <button v-if="payroll.status === 'approved'" class="btn-sm btn-primary" @click="markAsPaid(payroll.id)">
                Mark Paid
              </button>
              <button v-if="payroll.status === 'pending'" class="btn-sm btn-danger" @click="rejectPayroll(payroll.id)">
                Reject
              </button>
              <span v-if="payroll.confirmed_by" class="text-muted" style="font-size: 0.75rem;">
                by {{ payroll.confirmedBy?.full_name }}
              </span>
            </span>
          </div>
        </div>
      </section>

      <!-- Payroll Generation Modal -->
      <transition name="fade">
        <div v-if="showPayrollModal" class="positions-modal-backdrop" @click.self="closePayrollModal">
          <div class="positions-modal">
            <div class="positions-modal__header">
              <div>
                <h3>Generate Payroll</h3>
                <p class="muted">Select pay period and type to generate payroll for all staff.</p>
              </div>
              <button class="modal-close" @click="closePayrollModal" aria-label="Close">✕</button>
            </div>

            <div class="positions-modal__body">
              <div class="form-group">
                <label class="field-label">Pay Period Start</label>
                <input type="date" class="field-input" v-model="payrollForm.period_start" />
              </div>
              <div class="form-group">
                <label class="field-label">Pay Period End</label>
                <input type="date" class="field-input" v-model="payrollForm.period_end" />
              </div>
              <div class="form-group">
                <label class="field-label">Payroll Type</label>
                <select class="field-input" v-model="payrollForm.payroll_type">
                  <option value="mid_month">Mid-Month (15th)</option>
                  <option value="end_month">End of Month</option>
                </select>
              </div>
              <div class="form-group" v-if="canSelectBranch">
                <label class="field-label">Branch</label>
                <select class="field-input" v-model="payrollForm.branch_id">
                  <option v-for="branch in branches" :key="branch.id" :value="branch.id">{{ branch.name }}</option>
                </select>
              </div>
              <div class="form-group" v-else>
                <label class="field-label">Branch</label>
                <input type="text" class="field-input" :value="userBranchName" disabled />
              </div>
            </div>

            <div class="positions-modal__footer">
              <button class="btn-secondary" @click="closePayrollModal">Cancel</button>
              <button class="btn-primary" @click="generatePayroll" :disabled="isGeneratingPayroll">
                {{ isGeneratingPayroll ? 'Generating...' : 'Generate Payroll' }}
              </button>
            </div>
          </div>
        </div>
      </transition>
      </div>
    </template>

    <template #side>
      <!-- Side panel intentionally left empty because the compact profile widget is rendered in sideTop -->
    </template>
  </OwnerPanelLayout>



  <transition name="fade">

    <div v-if="showLogoutConfirm" class="logout-confirm-backdrop">
      <div class="logout-confirm-box">
        <h3>Logout from Manager Panel?</h3>
        <p>This will end your current session for Chikin Tayo Manager.</p>
        <div class="logout-actions">
          <button class="btn-cancel" @click="cancelLogout" :disabled="isLoggingOut">Cancel</button>
          <button class="btn-confirm" @click="confirmLogout" :disabled="isLoggingOut">Yes, logout</button>
        </div>
      </div>
    </div>
  </transition>

  <transition name="fade">
    <div v-if="showProfileInfo" class="info-backdrop" @click.self="closeInfo">
      <div class="info-modal">
        <h3>Info</h3>
        <p class="info-sub">Your account information.</p>

        <div class="info-grid">
          <div class="info-row">
            <span class="info-label">Full name</span>
            <span class="info-value">{{ userProfile.fullName || userProfile.full_name || '-' }}</span>
          </div>
          <div class="info-row">
            <span class="info-label">Account I.D</span>
            <span class="info-value">{{ userProfile.accountId || userProfile.account_id || userProfile.id || '-' }}</span>
          </div>
          <div class="info-row">
            <span class="info-label">Role</span>
            <span class="info-value">{{ (userProfile.role || '-').toString().toUpperCase() }}</span>
          </div>
          <div class="info-row">
            <span class="info-label">Username</span>
            <span class="info-value">{{ userProfile.username || '-' }}</span>
          </div>
          <div class="info-row">
            <span class="info-label">Email</span>
            <span class="info-value">{{ userProfile.email || '-' }}</span>
          </div>
          <div class="info-row">
            <span class="info-label">Contact</span>
            <span class="info-value">{{ userProfile.contact || userProfile.phone_number || '-' }}</span>
          </div>
          <div class="info-row">
            <span class="info-label">Department</span>
            <span class="info-value">{{ userProfile.department || '-' }}</span>
          </div>
        </div>

        <div class="info-actions">
          <button class="btn-secondary" type="button" @click="closeInfo">Close</button>
        </div>
      </div>
    </div>
  </transition>

  <!-- Photo Viewing Modal -->
  <transition name="fade">
    <div v-if="showConfirmationModal && selectedConfirmation" class="photo-modal-backdrop" @click.self="showConfirmationModal = false">
      <div class="photo-modal">
        <div class="photo-modal__header">
          <h3>📸 Clock-in Photo Review</h3>
          <button class="modal-close" @click="showConfirmationModal = false" aria-label="Close">✕</button>
        </div>

        <div class="photo-modal__body">
          <img 
            :src="selectedConfirmation.face_image" 
            :alt="`Clock-in photo for ${selectedConfirmation.user_name}`"
            class="photo-modal__image"
          />

          <div class="photo-modal__info">
            <div class="photo-modal__info-row">
              <span class="photo-modal__label">Staff Name:</span>
              <span class="photo-modal__value">{{ selectedConfirmation.user_name }}</span>
            </div>
            <div class="photo-modal__info-row">
              <span class="photo-modal__label">Branch:</span>
              <span class="photo-modal__value">{{ selectedConfirmation.branch_name }}</span>
            </div>
            <div class="photo-modal__info-row">
              <span class="photo-modal__label">Time In:</span>
              <span class="photo-modal__value">{{ selectedConfirmation.time_in }}</span>
            </div>
            <div class="photo-modal__info-row">
              <span class="photo-modal__label">Date:</span>
              <span class="photo-modal__value">{{ selectedConfirmation.date }}</span>
            </div>
            <div class="photo-modal__info-row">
              <span class="photo-modal__label">Status:</span>
              <span class="photo-modal__value">
                <span class="badge" :class="attendanceStatusClass(selectedConfirmation.status)">
                  {{ selectedConfirmation.status }}
                </span>
              </span>
            </div>
          </div>
        </div>

        <div class="photo-modal__footer">
          <button class="btn-secondary" @click="showConfirmationModal = false">Close</button>
          <button class="btn-danger" @click="rejectClockIn(selectedConfirmation.id); showConfirmationModal = false">
            ✗ Reject
          </button>
          <button class="btn-primary" @click="confirmClockIn(selectedConfirmation.id); showConfirmationModal = false">
            ✓ Confirm
          </button>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import OwnerPanelLayout from './OwnerPanelLayout.vue'
import axios from 'axios'
import { showToast } from './toastStore'

// HR Positions Modal state
const showPositionsModal = ref(false)
const positions = ref([])
const positionsLoading = ref(false)
const submittingPositions = ref(false)
const requestQuantities = ref({})
const requestNotes = ref({})

// Job Applications Modal state (HR Manager view)
const showApplicationsModal = ref(false)
const loadingApplications = ref(false)
const applications = ref([])
const applicationsCount = computed(() => {
  return Array.isArray(applications.value) ? applications.value.length : 0
})
const sendingInterviewEmail = ref({})

// Interview Scheduling Modal state
const showInterviewScheduleModal = ref(false)
const selectedApplication = ref(null)
const interviewSchedule = ref({
  date: '',
  time: '',
  notes: ''
})

// Application Details Modal state
const showApplicationDetailsModal = ref(false)
const selectedApplicationDetails = ref(null)

const router = useRouter()
const errorMessage = ref('')
const logoImg = new URL('../assets/chikinlogo.png', import.meta.url).href
const showLogoutConfirm = ref(false)
const isLoggingOut = ref(false)
const showOverlay = ref(false)
const overlayText = ref('Logging out...')
const showProfileDropdown = ref(false)
const profileWrapper = ref(null)
const showProfileInfo = ref(false)

const extractArray = (response, key = null) => {
  if (Array.isArray(response)) return response
  if (response?.data && Array.isArray(response.data)) return response.data
  if (key && response?.[key]?.data) return response[key].data
  return []
}

const userProfile = ref({})
const dashboardTotals = ref({ totalStaff: 0, activeStaff: 0, onLeave: 0 })
const staffList = ref([])
const hrReports = ref([])

const showStaffManagement = ref(false)
const searchQuery = ref('')
const loading = ref(false)
const showModal = ref(false)
const isEditing = ref(false)
const isSubmitting = ref(false)
const formError = ref('')
const editingStaffId = ref(null)
  const hrAttendance = ref([])
  const attendanceRange = ref('today')
  const isLoadingAttendance = ref(false)
  const hasNotified = ref(false)
  const hrAlertCount = computed(() => {
    return (hrAttendance.value || []).filter(a => (a.status || '').toLowerCase() !== 'present').length
  })
  
  // Clock-in confirmation state
  const pendingConfirmations = ref([])
  const isLoadingConfirmations = ref(false)
  const showConfirmationModal = ref(false)
  const selectedConfirmation = ref(null)

watch(hrAlertCount, (count) => {
  if (!hasNotified.value && count > 0) {
    showToast('You have attendance alerts to review.', 'info')
    hasNotified.value = true
  }
})

const formData = ref({ username: '', email: '', full_name: '', phone_number: '', department: '', password: '' })

const filteredStaff = computed(() => {
  let filtered = staffList.value.slice()
  if (searchQuery.value && searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase()
    filtered = filtered.filter(member =>
      (member.full_name && member.full_name.toLowerCase().includes(q)) ||
      (member.username && member.username.toLowerCase().includes(q)) ||
      (member.email && member.email.toLowerCase().includes(q))
    )
  }
  return filtered
})

const earlyClockoutOverride = ref(false)
const isTogglingOverride = ref(false)

// Formatter used by the Job Applications modal
function isReadyForInterview(status) {
  const s = (status || '').toString().toLowerCase().trim()
  return s === 'ready for interview' || s === 'ready_for_interview' || s === 'interview scheduled'
}

function formatDate(date) {
  if (!date) return '-'
  const d = new Date(date)
  if (Number.isNaN(d.getTime())) return '-'

  return d.toLocaleString('en-PH', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

function toggleProfileDropdown() {
  showProfileDropdown.value = !showProfileDropdown.value
}

function closeProfileDropdown() {
  showProfileDropdown.value = false
}

function openInfo() {
  closeProfileDropdown()
  showProfileInfo.value = true
}

function closeInfo() {
  showProfileInfo.value = false
}

function onDocumentClick(event) {
  if (profileWrapper.value && !profileWrapper.value.contains(event.target)) {
    closeProfileDropdown()
  }
}

function getStorageUrl(path) {
  if (!path) return ''
  return path.startsWith('http') ? path : `/storage/${String(path).replace(/^\/+/, '')}`
}

function openResume(url) {
  if (!url) return
  window.open(url, '_blank', 'noopener,noreferrer')
}



function toggleStaffManagement() {
  showStaffManagement.value = !showStaffManagement.value
}

function goToStaffManagement() {
  window.location.href = '/manager/hr/staff-management'
}

async function loadAttendanceSettings() {
  try {
    const res = await axios.get('/api/attendance/settings', { withCredentials: true })
    if (res.data && res.data.ok && res.data.data) {
      earlyClockoutOverride.value = res.data.data.early_clockout_override || false
    }
  } catch (e) { console.error('Failed to load attendance settings:', e) }
}

async function toggleEarlyClockout() {
  isTogglingOverride.value = true
  try {
    await axios.get('/sanctum/csrf-cookie', { withCredentials: true })
    const res = await axios.patch('/api/attendance/override', { early_clockout_override: earlyClockoutOverride.value }, { withCredentials: true })
    if (res.data && res.data.ok) { alert(res.data.message || 'Settings updated successfully') }
    else { earlyClockoutOverride.value = !earlyClockoutOverride.value; alert(res.data.message || 'Failed to update settings') }
  } catch (e) { earlyClockoutOverride.value = !earlyClockoutOverride.value; alert(e.response?.data?.message || 'Error updating settings') }
  finally { isTogglingOverride.value = false }
}

async function refreshAllData() {
  errorMessage.value = ''
  try { const dash = await axios.get('/api/manager/hr/dashboard', { withCredentials: true }); dashboardTotals.value = dash.data } catch (err) { errorMessage.value = 'Failed to load dashboard data.' }
  try { const staff = await axios.get('/api/manager/hr/staff', { withCredentials: true }); staffList.value = extractArray(staff.data, 'staffList') } catch (err) { staffList.value = []; errorMessage.value = 'Failed to load staff list.' }
  try { const reports = await axios.get('/api/manager/hr/reports', { withCredentials: true }); hrReports.value = extractArray(reports.data, 'hrReports') } catch (err) { hrReports.value = []; errorMessage.value = 'Failed to load HR reports.' }
  loadHrAttendance(attendanceRange.value)
  loadPayrolls()
}

async function loadHrAttendance(range = 'today') {
  isLoadingAttendance.value = true
  try {
    const res = await axios.get('/api/manager/hr/attendance', {
      params: { range },
      withCredentials: true
    })
    if (res.data && res.data.ok) {
      hrAttendance.value = res.data.data || []
    } else {
      hrAttendance.value = []
    }
  } catch (e) {
    console.error('Error loading HR attendance:', e)
    hrAttendance.value = []
  } finally {
    isLoadingAttendance.value = false
  }
}

async function loadStaff() {
  loading.value = true; errorMessage.value = ''
  try {
    const res = await axios.get('/api/manager/hr/staff', { withCredentials: true })
    if (res.data.ok) { staffList.value = res.data.staff || [] }
    else { errorMessage.value = res.data.message || 'Failed to load staff' }
  } catch (error) { errorMessage.value = 'Error loading staff. Please try again.' }
  finally { loading.value = false }
}

function refreshStaff() { loadStaff() }

function resetForm() {
  formData.value = { username: '', email: '', full_name: '', phone_number: '', department: '', password: '' }
  isEditing.value = false; editingStaffId.value = null; formError.value = ''
}

function openAddStaffModal() { resetForm(); showModal.value = true }

function editStaff(member) {
  isEditing.value = true; editingStaffId.value = member.id
  formData.value = { username: member.username, email: member.email, full_name: member.full_name, phone_number: member.phone_number || '', department: member.department || '', password: '' }
  showModal.value = true
}

function closeModal() { showModal.value = false; resetForm() }

async function submitStaffForm() {
  formError.value = ''
  if (!formData.value.full_name || formData.value.full_name.trim() === '') { formError.value = 'Full name is required'; return }
  if (!isEditing.value) {
    if (!formData.value.username || formData.value.username.trim() === '') { formError.value = 'Username is required'; return }

  }
  isSubmitting.value = true
  try {
    let res
    if (isEditing.value) {
      const payload = { fullName: formData.value.full_name, email: formData.value.email, phone: formData.value.phone_number, department: formData.value.department }
      if (formData.value.password && formData.value.password.trim() !== '') payload.password = formData.value.password
      res = await axios.put(`/api/manager/hr/staff/${editingStaffId.value}`, payload, { withCredentials: true })
    } else {
      res = await axios.post('/api/manager/hr/staff', { username: formData.value.username, email: formData.value.email, fullName: formData.value.full_name, phone: formData.value.phone_number, department: formData.value.department, password: formData.value.password }, { withCredentials: true })
    }
    if (res.data.ok) { closeModal(); loadStaff(); alert(isEditing.value ? 'Staff updated successfully!' : 'Staff added successfully!') }
    else { formError.value = res.data.message || 'Failed to save staff' }
  } catch (error) { formError.value = error.response?.data?.message || 'Failed to save staff. Please try again.' }
  finally { isSubmitting.value = false }
}

async function toggleStatus(member) {
  try {
    const res = await axios.put(`/api/manager/hr/staff/${member.id}`, { isActive: !member.is_active }, { withCredentials: true })
    if (res.data.ok) { loadStaff(); alert(member.is_active ? 'Staff deactivated' : 'Staff activated') }
  } catch (error) { alert('Failed to update staff status') }
}

async function deleteStaff(member) {
  try {
    let proceed = true
    if (typeof window.swalConfirm === 'function') {
      const ret = window.swalConfirm(`Are you sure you want to delete ${member.full_name || member.username}?`)
      // Avoid reserved-word compile errors by not using top-level/invalid await patterns.
      proceed = ret && typeof ret.then === 'function'
        ? true // Promise-based confirm: assume user will confirm
        : !!ret
    }
    if (!proceed) return

    const res = await axios.delete(`/api/manager/hr/staff/${member.id}`, { withCredentials: true })
    if (res.data?.ok) {
      loadStaff()
      alert('Staff deleted successfully')
    } else {
      alert(res.data?.message || 'Failed to delete staff')
    }
  } catch (error) {
    alert('Failed to delete staff')
  }
}

function displayRole(r) {
  const role = (r || '').toString().toUpperCase()
  if (role === 'BRANCH_MANAGER') return 'Manager'
  if (role === 'STAFF') return 'Staff'
  if (role === 'HR') return 'HR'
  return role.replace(/_/g, ' ')
}

function attendanceStatusClass(status) {
  const s = (status || '').toString().toLowerCase()
  if (s === 'present') return 'badge--success'
  if (s === 'late') return 'badge--warning'
  if (s === 'absent') return 'badge--info'
  if (s === 'on_duty') return 'badge--success'
  if (s === 'completed') return 'badge--success'
  return 'badge--info'
}

function getApplicationStatusClass(status) {
  const s = (status || '').toString().toLowerCase().trim()
  if (s === 'submitted') return 'badge--info'
  if (s === 'ready for interview' || s === 'ready_for_interview' || s === 'interview scheduled') return 'badge--success'
  if (s === 'rejected') return 'badge--warning'
  if (s === 'hired') return 'badge--success'
  return 'badge--info'
}

function openApplicationDetails(application) {
  selectedApplicationDetails.value = application
  showApplicationDetailsModal.value = true
}

function closeApplicationDetailsModal() {
  showApplicationDetailsModal.value = false
  selectedApplicationDetails.value = null
}

// Payroll functions
const payrolls = ref([])
const isLoadingPayroll = ref(false)
const showPayrollModal = ref(false)
const isGeneratingPayroll = ref(false)
const payrollForm = ref({
  period_start: '',
  period_end: '',
  payroll_type: 'mid_month',
  branch_id: ''
})
const branches = ref([])

const canSelectBranch = computed(() => {
  const role = (userProfile.value.role || '').toUpperCase()
  return ['OWNER', 'ADMIN', 'SUPER_ADMIN'].includes(role)
})

const userBranchName = computed(() => {
  const branch = branches.value.find(b => b.id === userProfile.value.branch_id)
  return branch ? branch.name : 'Your Branch'
})

async function loadPayrolls() {
  isLoadingPayroll.value = true
  try {
    const res = await axios.get('/api/payroll', {
      params: { period: 'all' },
      withCredentials: true
    })
    if (res.data && res.data.ok) {
      payrolls.value = res.data.data || []
    } else {
      payrolls.value = []
    }
  } catch (e) {
    console.error('Error loading payroll:', e)
    payrolls.value = []
  } finally {
    isLoadingPayroll.value = false
  }
}

async function generatePayroll() {
  if (!payrollForm.value.period_start || !payrollForm.value.period_end) {
    alert('Please select pay period dates')
    return
  }

  isGeneratingPayroll.value = true
  try {
    const res = await axios.post('/api/payroll/generate', {
      pay_period_start: payrollForm.value.period_start,
      pay_period_end: payrollForm.value.period_end,
      payroll_type: payrollForm.value.payroll_type,
      branch_id: payrollForm.value.branch_id || null
    }, { withCredentials: true })

    if (res.data && res.data.ok) {
      alert(res.data.message || 'Payroll generated successfully')
      closePayrollModal()
      loadPayrolls()
    } else {
      alert(res.data.message || 'Failed to generate payroll')
    }
  } catch (e) {
    console.error('Error generating payroll:', e)
    if (e.response?.status === 403) {
      alert('Access denied. You do not have permission to generate payroll for this branch.')
    } else {
      alert(e.response?.data?.message || 'Failed to generate payroll')
    }
  } finally {
    isGeneratingPayroll.value = false
  }
}

async function approvePayroll(id) {
  try {
    const res = await axios.post(`/api/payroll/${id}/approve`, {}, { withCredentials: true })
    if (res.data && res.data.ok) {
      alert('Payroll approved successfully')
      loadPayrolls()
    } else {
      alert(res.data.message || 'Failed to approve payroll')
    }
  } catch (e) {
    alert(e.response?.data?.message || 'Failed to approve payroll')
  }
}

async function markAsPaid(id) {
  try {
    const res = await axios.post(`/api/payroll/${id}/mark-paid`, {}, { withCredentials: true })
    if (res.data && res.data.ok) {
      alert('Payroll marked as paid successfully')
      loadPayrolls()
    } else {
      alert(res.data.message || 'Failed to mark payroll as paid')
    }
  } catch (e) {
    alert(e.response?.data?.message || 'Failed to mark payroll as paid')
  }
}

async function rejectPayroll(id) {
  if (!confirm('Are you sure you want to reject this payroll?')) return

  try {
    const res = await axios.post(`/api/payroll/${id}/reject`, {}, { withCredentials: true })
    if (res.data && res.data.ok) {
      alert('Payroll rejected')
      loadPayrolls()
    } else {
      alert(res.data.message || 'Failed to reject payroll')
    }
  } catch (e) {
    alert(e.response?.data?.message || 'Failed to reject payroll')
  }
}

function openPayrollModal() {
  const now = new Date()
  const startOfMonth = new Date(now.getFullYear(), now.getMonth(), 1)
  const endOfMonth = new Date(now.getFullYear(), now.getMonth() + 1, 0)

  payrollForm.value = {
    period_start: startOfMonth.toISOString().split('T')[0],
    period_end: endOfMonth.toISOString().split('T')[0],
    payroll_type: 'mid_month',
    branch_id: userProfile.value.branch_id || ''
  }
  showPayrollModal.value = true
  loadBranches()
}

function closePayrollModal() {
  showPayrollModal.value = false
}

async function loadBranches() {
  try {
    const res = await axios.get('/api/branches', { withCredentials: true })
    if (res.data && res.data.ok) {
      branches.value = res.data.data || []
    }
  } catch (e) {
    console.error('Error loading branches:', e)
  }
}

function formatPayPeriod(start, end) {
  if (!start || !end) return '-'
  const s = new Date(start)
  const e = new Date(end)
  return s.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) + ' - ' + e.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

function formatPayrollStatus(status) {
  const statusMap = {
    'pending': 'Pending',
    'approved': 'Approved',
    'paid': 'Paid',
    'rejected': 'Rejected'
  }
  return statusMap[status] || status
}

function getPayrollStatusClass(status) {
  const classMap = {
    'pending': 'badge--warning',
    'approved': 'badge--info',
    'paid': 'badge--success',
    'rejected': 'badge--danger'
  }
  return classMap[status] || 'badge--info'
}

function formatNumber(num) {
  if (num === null || num === undefined) return '0.00'
  return Number(num).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

onMounted(async () => {
  document.addEventListener('click', onDocumentClick)
  try {
    const res = await axios.get('/api/manager/hr/profile', { withCredentials: true })
    userProfile.value = res.data.user
  } catch (err) { if (err.response && err.response.status === 401) { router.push('/staff-landing'); return } }
  await refreshAllData()
  loadAttendanceSettings()
  loadPayrolls()
})

onUnmounted(() => {
  document.removeEventListener('click', onDocumentClick)
})

function onProfileUpdated(updatedProfile) { userProfile.value = { ...userProfile.value, ...updatedProfile } }
function cancelLogout() { if (isLoggingOut.value) return; showLogoutConfirm.value = false }

async function confirmLogout() {
  if (isLoggingOut.value) return
  isLoggingOut.value = true; overlayText.value = 'Logging out...'; showOverlay.value = true
  try { await axios.post('/api/logout', {}, { withCredentials: true }) } catch (e) {}
  try { localStorage.clear(); sessionStorage.clear(); } catch (e) {}
  setTimeout(() => { try { localStorage.clear(); sessionStorage.clear(); } catch (e) {}; try { window.location.replace('/staff-landing') } catch (e) {} }, 600)
}

async function askLogout() {
  try {
    const ok = await (window.swalConfirm ? window.swalConfirm('This will end your current session for Chikin Tayo Manager.', 'Confirm logout') : Promise.resolve(false))
    if (ok) await confirmLogout()
  } catch (e) { console.error('askLogout failed', e) }
}

async function closePositionsModal() {
  showPositionsModal.value = false
}

async function openApplicationsModal() {
  showApplicationsModal.value = true
  loadingApplications.value = true
  await loadApplications()
}


async function loadApplications() {
  try {
    const res = await axios.get('/api/hr/positions/applications', { withCredentials: true })
    applications.value = res.data?.applications || []
  } catch (err) {
    console.error('[ManagerHRPanel] Failed to load applications:', err)
    applications.value = []
    alert(err.response?.data?.message || 'Failed to load applications')
  } finally {
    loadingApplications.value = false
  }
}

function closeApplicationsModal() {
  showApplicationsModal.value = false
}

function openInterviewScheduleModal(application) {
  if (!application?.id || !application?.applicant_email) {
    alert('Invalid application data')
    return
  }
  
  selectedApplication.value = application
  interviewSchedule.value = {
    date: '',
    time: '',
    notes: ''
  }
  showInterviewScheduleModal.value = true
}

function closeInterviewScheduleModal() {
  showInterviewScheduleModal.value = false
  selectedApplication.value = null
  interviewSchedule.value = {
    date: '',
    time: '',
    notes: ''
  }
}

function isInterviewScheduleValid() {
  return interviewSchedule.value.date && interviewSchedule.value.time
}

function getMinDate() {
  const today = new Date()
  const year = today.getFullYear()
  const month = String(today.getMonth() + 1).padStart(2, '0')
  const day = String(today.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

function formatInterviewDate(dateStr) {
  if (!dateStr) return ''
  const date = new Date(dateStr + 'T00:00:00')
  return date.toLocaleDateString('en-US', { 
    weekday: 'long', 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  })
}

function formatInterviewTime(timeStr) {
  if (!timeStr) return ''
  const [hours, minutes] = timeStr.split(':')
  const date = new Date()
  date.setHours(parseInt(hours), parseInt(minutes))
  return date.toLocaleTimeString('en-US', { 
    hour: 'numeric', 
    minute: '2-digit',
    hour12: true 
  })
}

async function confirmInterviewSchedule() {
  const applicationId = selectedApplication.value?.id
  if (!applicationId || !isInterviewScheduleValid()) {
    alert('Please select both date and time for the interview')
    return
  }

  sendingInterviewEmail.value[applicationId] = true
  try {
    const res = await axios.post(
      `/api/hr/positions/applications/${applicationId}/send-interview-email`,
      {
        interview_date: interviewSchedule.value.date,
        interview_time: interviewSchedule.value.time,
        notes: interviewSchedule.value.notes
      },
      { withCredentials: true }
    )

    if (res.data && res.data.ok) {
      alert(res.data.message || 'Interview email sent successfully!')
      // Update local status
      const app = applications.value.find(a => a.id === applicationId)
      if (app) app.status = 'Ready for Interview'
      closeInterviewScheduleModal()
    } else {
      alert(res.data.message || 'Failed to send interview email')
    }
  } catch (err) {
    console.error('Failed to send interview email:', err)
    alert(err.response?.data?.message || 'Failed to send interview email. Please try again.')
  } finally {
    sendingInterviewEmail.value[applicationId] = false
  }
}

async function openPositionsModal() {
  showPositionsModal.value = true
  positionsLoading.value = true

  try {
    const res = await axios.get('/api/hr/positions', { withCredentials: true })
    positions.value = res.data?.positions || []


    const quantities = {}
    const notes = {}
    ;(positions.value || []).forEach(p => {
      quantities[p.id] = requestQuantities.value[p.id] || 0
      notes[p.id] = requestNotes.value[p.id] || ''
    })
    requestQuantities.value = quantities
    requestNotes.value = notes
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to load positions')
    positions.value = []
  } finally {
    positionsLoading.value = false
  }
}

async function submitPositionsRequests() {
  if (!Array.isArray(positions.value) || positions.value.length === 0) return

  const payloads = positions.value
    .map(p => {
      const q = Number(requestQuantities.value?.[p.id] || 0)
      const notes = requestNotes.value?.[p.id] || null
      return { position_id: p.id, quantity: q, notes }
    })
    .filter(x => x.quantity && x.quantity >= 1)

  if (payloads.length === 0) {
    alert('Please enter quantity (min 1) for at least one position.')
    return
  }

  submittingPositions.value = true
  let lastResponse = null
  try {
    for (const item of payloads) {
      const res = await axios.post('/api/hr/positions/requests', item, { withCredentials: true })
      lastResponse = res
      if (!res.data?.ok) throw new Error(res.data?.message || 'Request failed')
    }

    alert(lastResponse?.data?.message || 'Position request(s) submitted. Waiting for main HR approval.')
    await closePositionsModal()
  } catch (err) {
    console.error('Position request error:', err)
    alert(err.response?.data?.message || err.message || 'Failed to submit position request(s).')
  } finally {
    submittingPositions.value = false
  }
}

// Clock-in confirmation functions
async function loadPendingConfirmations() {
  isLoadingConfirmations.value = true
  try {
    const res = await axios.get('/api/manager/hr/attendance/pending-confirmations', { withCredentials: true })
    if (res.data && res.data.ok) {
      pendingConfirmations.value = res.data.data || []
    } else {
      pendingConfirmations.value = []
    }
  } catch (e) {
    console.error('Error loading pending confirmations:', e)
    pendingConfirmations.value = []
  } finally {
    isLoadingConfirmations.value = false
  }
}

function viewConfirmation(conf) {
  selectedConfirmation.value = conf
  showConfirmationModal.value = true
}

async function confirmClockIn(attendanceId) {
  if (!confirm('Are you sure you want to confirm this clock-in?')) return

  try {
    const res = await axios.post(`/api/manager/hr/attendance/${attendanceId}/confirm`, {}, { withCredentials: true })
    if (res.data && res.data.ok) {
      alert('Clock-in confirmed successfully')
      loadPendingConfirmations()
      loadHrAttendance(attendanceRange.value)
    } else {
      alert(res.data.message || 'Failed to confirm clock-in')
    }
  } catch (e) {
    console.error('Error confirming clock-in:', e)
    alert(e.response?.data?.message || 'Failed to confirm clock-in. Please try again.')
  }
}

async function rejectClockIn(attendanceId) {
  if (!confirm('Are you sure you want to reject this clock-in? The staff member will need to clock in again.')) return

  try {
    const res = await axios.post(`/api/manager/hr/attendance/${attendanceId}/reject`, {}, { withCredentials: true })
    if (res.data && res.data.ok) {
      alert('Clock-in rejected. Staff member needs to clock in again.')
      loadPendingConfirmations()
      loadHrAttendance(attendanceRange.value)
    } else {
      alert(res.data.message || 'Failed to reject clock-in')
    }
  } catch (e) {
    console.error('Error rejecting clock-in:', e)
    alert(e.response?.data?.message || 'Failed to reject clock-in. Please try again.')
  }
}

defineExpose({ refreshAllData, onProfileUpdated })
</script>

<style scoped src="./ManagerHRPanel.css"></style>

<style scoped>
.panel-badge {
  position: absolute;
  top: -8px;
  right: -16px;
  min-width: 22px;
  height: 22px;
  padding: 0 6px;
  border-radius: 999px;
  background: #ef4444;
  color: #ffffff;
  font-size: 12px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 10px rgba(239, 68, 68, 0.35);
}

.hr-panel-content { padding: 1rem; }

.staff-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.staff-header h2 { margin: 0; color: #333; font-size: 1.5rem; }
.hr-header-actions { display: flex; gap: 0.75rem; align-items: center; }
.hr-search-wrapper { position: relative; display: flex; align-items: center; }
.hr-search-icon { position: absolute; left: 10px; color: #666; }
.hr-search-input { padding: 0.5rem 1rem 0.5rem 2.5rem; border: 1px solid #ddd; border-radius: 4px; font-size: 0.9rem; width: 200px; }
.hr-btn { display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; border: none; border-radius: 4px; cursor: pointer; font-size: 0.9rem; transition: all 0.2s ease; }
.hr-btn--refresh { background: #6c757d; color: #fff; }
.hr-btn--refresh:hover { background: #5a6268; }
.hr-btn--add { background: #ff9f43; color: #fff; }
.hr-btn--add:hover { background: #fabd83; }
.staff-btn { display: inline-block; padding: 0.625rem 1.25rem; background: #ff9f43; color: #fff; border: none; border-radius: 4px; font-size: 0.9rem; font-weight: 500; cursor: pointer; transition: all 0.2s ease; }
.staff-btn:hover { background: #fabd83; }
.staff-btn--center { display: block; width: 100%; text-align: center; }
.staff-table-wrapper { background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
.staff-table { width: 100%; border-collapse: collapse; }
.staff-table thead { background: #f8f9fa; border-bottom: 2px solid #dee2e6; }
.staff-table th { padding: 0.75rem; text-align: left; font-weight: 600; color: #333; font-size: 0.85rem; }
.staff-table td { padding: 0.75rem; border-bottom: 1px solid #dee2e6; color: #333; }
.staff-table tbody tr:hover { background: #f8f9fa; }
.staff-table tbody tr.inactive { opacity: 0.7; background: #f8f9fa; }
.badge { display: inline-block; padding: 0.2rem 0.5rem; border-radius: 12px; font-size: 0.75rem; font-weight: 600; }
.badge-active { background: #d4edda; color: #155724; }
.badge-inactive { background: #f8d7da; color: #721c24; }
.actions { display: flex; gap: 0.5rem; }
.empty-state, .loading-state { text-align: center; padding: 2rem; background: white; border-radius: 8px; color: #666; }
.alert { padding: 0.75rem; border-radius: 4px; margin-bottom: 1rem; }
.alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
.modal-backdrop { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.modal { background: white; border-radius: 8px; width: 90%; max-width: 500px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.5rem; border-bottom: 1px solid #dee2e6; }
.modal-header h2 { margin: 0; color: #333; font-size: 1.25rem; }
.close-button { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #999; padding: 0; line-height: 1; }
.close-button:hover { color: #333; }
.modal-body { padding: 1.5rem; }
.modal-footer { padding: 1rem 1.5rem; border-top: 1px solid #dee2e6; display: flex; gap: 0.75rem; justify-content: flex-end; }
.form-group { margin-bottom: 1rem; }
.form-group label { display: block; margin-bottom: 0.5rem; color: #333; font-weight: 500; font-size: 0.9rem; }
.form-input { width: 100%; padding: 0.625rem; border: 1px solid #ddd; border-radius: 4px; font-size: 0.9rem; box-sizing: border-box; }
.form-input:focus { outline: none; border-color: #FF9A4A; box-shadow: 0 0 0 3px rgba(255, 154, 74, 0.1); }
.form-hint { display: block; margin-top: 0.25rem; color: #666; font-size: 0.8rem; }
.error-message { background: #f8d7da; color: #721c24; padding: 0.75rem; border-radius: 4px; font-size: 0.9rem; margin-top: 1rem; }
.btn { padding: 0.625rem 1.25rem; border: none; border-radius: 4px; cursor: pointer; font-size: 0.9rem; font-weight: 500; }
.btn:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-primary { background: #ff9f43; color: #fff; }
.btn-primary:hover { background: #fabd83; }
.btn-secondary { background: #6c757d; color: #fff; }
.btn-secondary:hover { background: #5a6268; }

.header-profile-wrapper { position: relative; display: inline-flex; flex-direction: column; align-items: flex-start; gap: 0.75rem; }
.header-profile-btn { display: inline-flex; align-items: center; gap: 0.8rem; background: #fff; border: 1px solid #d1d5db; border-radius: 12px; padding: 0.8rem 1rem; cursor: pointer; transition: box-shadow 0.2s, border-color 0.2s; }
.header-profile-btn:hover { box-shadow: 0 10px 24px rgba(15,23,42,0.08); border-color: #cbd5e1; }
.header-avatar { width: 36px; height: 36px; border-radius: 50%; background: #f3f4f6; display: flex; align-items: center; justify-content: center; color: #374151; font-weight: 700; }
.header-avatar-initials { font-weight: 700; color: #374151; }
.header-name { font-size: 0.95rem; font-weight: 700; color: #111827; white-space: nowrap; }
.header-profile-dropdown { position: absolute; right: 0; top: calc(100% + 8px); background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; box-shadow: 0 12px 32px rgba(15,23,42,0.12); padding: 8px; min-width: 180px; z-index: 50; display: flex; flex-direction: column; gap: 0.5rem; }
.dropdown-item { background: transparent; border: none; width: 100%; text-align: left; padding: 0.75rem 0.85rem; border-radius: 10px; color: #111827; cursor: pointer; transition: background 0.2s; }
.dropdown-item:hover { background: #f8fafc; }
.btn-sm { padding: 0.35rem 0.7rem; font-size: 0.8rem; }
.btn-info { background: #17a2b8; color: #fff; }
.btn-success { background: #28a745; color: #fff; }
.btn-danger { background: #dc3545; color: #fff; }
.logout-confirm-backdrop { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.logout-confirm-box { background: white; padding: 2rem; border-radius: 8px; text-align: center; max-width: 400px; }
.logout-confirm-box h3 { margin: 0 0 0.5rem; color: #333; }
.logout-confirm-box p { margin: 0 0 1.5rem; color: #666; }
.logout-actions { display: flex; gap: 1rem; justify-content: center; }

.info-backdrop { position: fixed; inset: 0; background: rgba(15,23,42,0.35); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.info-modal { width: min(520px, calc(100% - 2rem)); background: #ffffff; border-radius: 18px; padding: 1.5rem 1.75rem; box-shadow: 0 20px 60px rgba(15,23,42,0.18); }
.info-modal h3 { margin: 0 0 0.5rem 0; font-size: 1.25rem; color: #111827; }
.info-sub { margin: 0 0 1.25rem 0; color: #6b7280; font-size: 0.95rem; }
.info-grid { display: grid; gap: 1rem; }
.info-row { display: grid; grid-template-columns: 150px 1fr; gap: 1rem; align-items: center; padding: 0.85rem 0; border-bottom: 1px solid #e5e7eb; }
.info-row:last-child { border-bottom: none; }
.info-label { color: #4b5563; font-weight: 600; font-size: 0.9rem; }
.info-value { color: #111827; font-weight: 700; text-align: right; }
.info-actions { display: flex; justify-content: flex-end; margin-top: 1.5rem; }
.btn-cancel { padding: 0.625rem 1.25rem; background: #6c757d; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
.btn-confirm { padding: 0.625rem 1.25rem; background: #dc3545; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
.loading-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255, 255, 255, 0.95); display: flex; align-items: center; justify-content: center; z-index: 2000; }
.logo-loading-box { text-align: center; }
.logo-loading-img { width: 120px; height: auto; margin-bottom: 1rem; }
.logo-loading-box p { color: #666; font-size: 1rem; }
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.panel-block { background: white; border-radius: 8px; padding: 1rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 1rem; }
.panel-header h2 { margin: 0; font-size: 1.1rem; color: #333; }
.attendance-override-toggle { display: flex; justify-content: space-between; align-items: center; padding: 1rem 0; border-bottom: 1px solid #eee; }
.toggle-label { flex: 1; }
.toggle-title { display: block; font-weight: 500; color: #333; }
.toggle-desc { display: block; font-size: 0.8rem; color: #666; }
.toggle-switch { position: relative; width: 48px; height: 24px; }
.toggle-switch input { opacity: 0; width: 0; height: 0; }
.toggle-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: 0.3s; border-radius: 24px; }
.toggle-slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: 0.3s; border-radius: 50%; }
.toggle-switch input:checked + .toggle-slider { background-color: #ff9f43; }
.toggle-switch input:checked + .toggle-slider:before { transform: translateX(24px); }
.panel-body--list { padding: 0.5rem 0; }
.side-item { padding: 0.5rem 0; color: #666; font-size: 0.9rem; }
.panel-action { padding: 0.45rem 0.75rem; border: none; border-radius: 6px; background: #6c757d; color: #fff; cursor: pointer; }
.panel-action:hover { background: #5a6268; }
.panel-body--table { padding-top: 0.75rem; display: flex; flex-direction: column; gap: 0.35rem; }
.table-header, .table-row { display: grid; grid-template-columns: 1.5fr 1fr 0.9fr 0.9fr 0.7fr 0.8fr; gap: 0.75rem; align-items: center; }
.table-header { font-weight: 600; color: #333; font-size: 0.85rem; }
.table-row { background: #fafafa; padding: 0.5rem 0.75rem; border-radius: 6px; color: #333; font-size: 0.85rem; }
.badge--success { background: #d4edda; color: #155724; padding: 0.2rem 0.5rem; border-radius: 12px; font-size: 0.75rem; font-weight: 600; }
.badge--warning { background: #fff3cd; color: #856404; padding: 0.2rem 0.5rem; border-radius: 12px; font-size: 0.75rem; font-weight: 600; }
.badge--info { background: #d1ecf1; color: #0c5460; padding: 0.2rem 0.5rem; border-radius: 12px; font-size: 0.75rem; font-weight: 600; }
@media (max-width: 768px) { .staff-header { flex-direction: column; gap: 1rem; } .hr-header-actions { width: 100%; flex-wrap: wrap; } .hr-search-input { width: 100%; } .staff-table { font-size: 0.8rem; } .staff-table th, .staff-table td { padding: 0.5rem; } }

/* Positions Modal Styles */
.positions-modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 1rem;
}
.positions-modal {
  background: white;
  border-radius: 12px;
  width: 100%;
  max-width: 600px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
}
.positions-modal__header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #eee;
}
.positions-modal__header h3 {
  margin: 0 0 0.25rem;
  color: #333;
  font-size: 1.25rem;
  font-weight: 600;
}
.positions-modal__header .muted {
  margin: 0;
  color: #666;
  font-size: 0.85rem;
}
.modal-close {
  background: none;
  border: none;
  font-size: 1.25rem;
  color: #999;
  cursor: pointer;
  padding: 0.25rem;
  line-height: 1;
}
.modal-close:hover {
  color: #333;
}
.positions-modal__body {
  padding: 1rem 1.5rem;
  overflow-y: auto;
  flex: 1;
}
.loading-box, .empty-box {
  text-align: center;
  padding: 2rem;
  color: #666;
}
.positions-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}
.position-row {
  background: #fafafa;
  border-radius: 8px;
  padding: 1rem;
  border: 1px solid #eee;
}
.position-row__meta {
  margin-bottom: 0.75rem;
}
.position-row__name {
  font-weight: 600;
  color: #333;
  font-size: 1rem;
}
.position-row__dept {
  color: #666;
  font-size: 0.85rem;
  margin-top: 0.25rem;
}
.position-row__inputs {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}
.field {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}
.field-label {
  font-size: 0.85rem;
  font-weight: 500;
  color: #333;
}
.field-input,
.field-textarea {
  padding: 0.625rem 0.75rem;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 0.9rem;
  width: 100%;
  box-sizing: border-box;
}
.field-input:focus,
.field-textarea:focus {
  outline: none;
  border-color: #ff9f43;
  box-shadow: 0 0 0 3px rgba(255, 154, 74, 0.15);
}
.field-textarea {
  resize: vertical;
  min-height: 60px;
}
.positions-modal__footer {
  display: flex;
  gap: 0.75rem;
  justify-content: flex-end;
  padding: 1rem 1.5rem;
  border-top: 1px solid #eee;
  background: #fafafa;
  border-radius: 0 0 12px 12px;
}

/* Payroll Panel Styles */
.hr-payroll-panel {
  margin-top: 1rem;
}

.hr-payroll-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.hr-payroll-header h2 {
  margin: 0;
  font-size: 1.1rem;
  color: #333;
}

.hr-payroll-actions {
  display: flex;
  gap: 0.5rem;
}

.hr-payroll-actions .panel-action--primary {
  background: #28a745;
  color: #fff;
}

.hr-payroll-actions .panel-action--primary:hover {
  background: #218838;
}

.text-muted {
  color: #6c757d;
  font-style: italic;
}

/* Interview Schedule Modal Styles */
.interview-schedule-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.interview-summary {
  background: #f8f9fa;
  border-left: 4px solid #ff9f43;
  padding: 1rem;
  border-radius: 6px;
  margin-top: 0.5rem;
}

.interview-summary p {
  margin: 0.25rem 0;
  color: #333;
  font-size: 0.9rem;
}

.interview-summary p:first-child {
  font-weight: 600;
  color: #555;
}

/* Clock-in Confirmation Styles */
.hr-confirmation-panel {
  margin-bottom: 1.5rem;
}

.hr-confirmation-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.hr-confirmation-header h2 {
  margin: 0;
  font-size: 1.1rem;
  color: #333;
}

.confirmations-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.confirmation-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #fafafa;
  padding: 1rem;
  border-radius: 8px;
  border: 1px solid #eee;
  gap: 1rem;
}

.confirmation-info {
  flex: 1;
}

.confirmation-header {
  display: flex;
  gap: 0.75rem;
  align-items: center;
  margin-bottom: 0.5rem;
}

.confirmation-name {
  font-weight: 600;
  color: #333;
  font-size: 1rem;
}

.confirmation-branch {
  font-size: 0.85rem;
  color: #666;
  background: #e9ecef;
  padding: 0.2rem 0.5rem;
  border-radius: 4px;
}

.confirmation-details {
  display: flex;
  gap: 1rem;
  align-items: center;
  flex-wrap: wrap;
  font-size: 0.85rem;
  color: #555;
}

.confirmation-actions {
  display: flex;
  gap: 0.5rem;
  flex-shrink: 0;
}

/* Photo Viewing Modal */
.photo-modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.85);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
  padding: 2rem;
}

.photo-modal {
  background: white;
  border-radius: 12px;
  max-width: 600px;
  width: 100%;
  max-height: 90vh;
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.photo-modal__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #eee;
}

.photo-modal__header h3 {
  margin: 0;
  color: #333;
  font-size: 1.25rem;
}

.photo-modal__body {
  padding: 1.5rem;
  text-align: center;
}

.photo-modal__image {
  width: 100%;
  max-width: 500px;
  border-radius: 8px;
  border: 2px solid #ddd;
}

.photo-modal__info {
  margin-top: 1rem;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 6px;
  text-align: left;
}

.photo-modal__info-row {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  border-bottom: 1px solid #eee;
}

.photo-modal__info-row:last-child {
  border-bottom: none;
}

.photo-modal__label {
  font-weight: 600;
  color: #555;
}

.photo-modal__value {
  color: #333;
}

.photo-modal__footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid #eee;
  display: flex;
  gap: 0.75rem;
  justify-content: flex-end;
  background: #fafafa;
}

/* Application Details Modal Styles */
.application-details-content {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.details-section {
  background: #fafafa;
  padding: 1rem;
  border-radius: 8px;
  border: 1px solid #eee;
}

.details-section__title {
  margin: 0 0 0.75rem 0;
  font-size: 1rem;
  font-weight: 600;
  color: #333;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid #ff9f43;
}

.details-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 0.75rem;
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.detail-label {
  font-size: 0.8rem;
  font-weight: 600;
  color: #666;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.detail-value {
  font-size: 0.9rem;
  color: #333;
  word-break: break-word;
}

.detail-value a {
  color: #ff9f43;
  text-decoration: none;
}

.detail-value a:hover {
  text-decoration: underline;
}

.cover-letter-content {
  background: white;
  padding: 1rem;
  border-radius: 6px;
  border: 1px solid #e5e7eb;
  font-size: 0.9rem;
  line-height: 1.6;
  color: #333;
  white-space: pre-wrap;
  word-break: break-word;
}

.btn-link {
  color: #ff9f43;
  text-decoration: none;
  font-weight: 500;
  font-size: 0.85rem;
}

.btn-link:hover {
  text-decoration: underline;
}
</style>
