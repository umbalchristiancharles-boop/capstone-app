<template>
  <OwnerPanelLayout ref="ownerLayout"
    :userProfile="userProfile"
    :panelTitle="'Manager Procurement Panel'"
    :panelDescription="'Manage procurement staff, view procurement reports, and monitor procurement status.'"
    :enableProfileUpdate="true"
    :canEditProfile="userProfile.role === 'OWNER'"
    :canChangePassword="true"
    :showProfileColumn="false"
    :ownerTwoColumnLayout="true"
    @logout="askLogout"
    @profile-updated="onProfileUpdated"
  >
    <template #main>
      <div class="hr-stats-grid">
        <div class="hr-stat-card hr-stat-card--total">
          <div class="hr-stat-icon">
            <!-- icon reused from HR panel -->
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
          </div>
          <div class="hr-stat-content">
            <span class="hr-stat-label">Total Suppliers</span>
{{ dashboardTotals.totalSuppliers }}
          </div>
        </div>
        <div class="hr-stat-card hr-stat-card--active">
          <div class="hr-stat-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
          </div>
          <div class="hr-stat-content">
            <span class="hr-stat-label">Active Suppliers</span>
{{ dashboardTotals.activeSuppliers }}
          </div>
        </div>
        <div class="hr-stat-card hr-stat-card--leave" :class="{ 'stat-alert': procurementPendingCount > 0 }">
          <div class="hr-stat-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
          </div>
          <div class="hr-stat-content">
            <span class="hr-stat-label">Pending Requests</span>
            <span class="hr-stat-value">{{ dashboardTotals.pendingRequests }}</span>
          </div>
          <span v-if="procurementPendingCount > 0" class="panel-badge">{{ procurementPendingCount }}</span>
        </div>
      </div>
      <section class="manual-procurement mt-1">
        <h2>Manual Procurement</h2>
        <p class="section-description">Create a manual procurement (attach receipt/product image and optionally request budget).</p>

        <div class="mb-1">
          <button class="btn-primary" v-if="!showProcRequestFormManager" @click="showProcRequestFormManager = true">+ Custom Procurement Request</button>
        </div>

        <div v-if="showProcRequestFormManager" class="form-container" style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
            <h3 style="margin:0; font-size:16px;">Create Manual Procurement</h3>
            <button type="button" @click="cancelProcRequestManager" style="background:none;border:none;color:#9ca3af;font-size:18px;cursor:pointer;padding:0">✕</button>
          </div>
          <form @submit.prevent="submitProcRequestManager">
            <div class="form-group" style="margin-bottom:12px;">
              <label>Product *</label>
              <select v-model="procRequestFormManager.product_id" required style="width:100%; padding:8px;">
                <option value="">— Select product —</option>
                <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }} (₱{{ formatPrice(p.price) }})</option>
              </select>
            </div>
            <div class="form-group" style="margin-bottom:12px;">
              <label>Quantity *</label>
              <input type="number" v-model.number="procRequestFormManager.quantity" min="1" required style="width:100%; padding:8px;" />
            </div>
            <div class="form-group" style="margin-bottom:12px;">
              <label>Unit Price (PHP)</label>
              <input type="number" v-model.number="procRequestFormManager.price" step="0.01" min="0" placeholder="Optional - enter price per unit" style="width:100%; padding:8px;" />
              <small style="color:#9ca3af">If provided, this price will be used to compute the total and sent to Finance.</small>
            </div>
            <div class="form-group" style="display:flex; gap:12px; margin-bottom:12px;">
              <div style="flex:1">
                <label>Receipt *</label>
                <input type="file" accept="image/*" @change="onReceiptChangeManager" required />
              </div>
              <div style="flex:1">
                <label>Product image *</label>
                <input type="file" accept="image/*" @change="onProductImageChangeManager" required />
              </div>
            </div>
            <div class="form-group" style="margin-bottom:12px;">
              <label><input type="checkbox" v-model="procRequestFormManager.request_budget" /> Request budget from Finance</label>
            </div>
            <div v-if="procRequestFormManagerError" class="error-msg" style="color:#dc2626;margin-bottom:12px">{{ procRequestFormManagerError }}</div>
            <div class="form-actions" style="display:flex; gap:10px; justify-content:flex-end;">
              <button type="button" class="btn-secondary" @click="cancelProcRequestManager">Cancel</button>
              <button type="submit" class="btn-primary" :disabled="procRequestSubmittingManager">{{ procRequestSubmittingManager ? 'Submitting...' : 'Submit' }}</button>
            </div>
          </form>
        </div>
      </section>
      <div class="panel-actions mt-1">
        <button class="btn-primary" @click="openAddSupplier">Add Supplier</button>
      </div>
      <section class="supplier-products mt-1">
        <h2>Supplier Products (this branch)</h2>
        <div v-if="loadingProducts">Loading products...</div>
        <div v-else-if="!products.length">No products available in your branch.</div>
        <div v-else>
          <!-- Pending Supplier Products UI removed per request -->

          <div>
            <h3 class="section-subtitle">Published Products ({{ publishedProducts.length }})</h3>
            <div v-for="cat in publishedProductCategories" :key="cat" class="category-section mb-1">
              <h4 class="category-title">{{ cat || 'Uncategorized' }}</h4>
              <div class="product-grid">
                <div v-for="p in getPublishedProductsByCategory(cat)" :key="p.id" class="product-card">
                  <div class="product-name">{{ p.name }}</div>
                  <div v-if="p.per_pack_or_individual" class="product-type-badge" :class="'type-' + p.per_pack_or_individual">
                    {{ formatPricingType(p.per_pack_or_individual) }}
                  </div>
                  <div class="product-meta">
                    <div class="product-price">{{ formatPrice(p.price) }}</div>
                    <div class="supplier-badge">{{ p.supplier_name || 'Unknown Supplier' }}</div>
                    <div v-if="p.expires_at" class="expiry-info">Expires: {{ formatDate(p.expires_at) }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
      <section class="requests-history mt-1">
        <h2>Requests History</h2>
        <p class="section-description">All procurement requests for this branch (most recent first).</p>

        <div v-if="procurementHistoryLoading">Loading history...</div>
        <div v-else-if="!procurementHistory.length">No procurement requests found.</div>
        <div v-else>
          <div class="requests-container">
            <div class="requests-scroll">
              <table class="data-table">
                <thead>
                  <tr><th>Date</th><th>Product</th><th>Qty</th><th>Variance</th><th>Total</th><th>Status</th><th>Updated</th></tr>
                </thead>
                <tbody>
                  <tr v-for="r in procurementHistory" :key="'ph-'+r.id">
                    <td>{{ formatDate(r.created_at) }}</td>
                    <td><div class="product-name">{{ r.product?.name || r.purpose || '(no product)' }}</div></td>
                    <td>{{ r.quantity }}</td>
                    <td>{{ formatVariance(r.variance_quantity) }}</td>
                    <td class="amount">{{ formatPrice(r.total_amount || r.price || 0) }}</td>
                    <td>
                      <span :class="['status-badge', getProcStatusClass(r.status)]">
                        {{ formatProcStatus(r.status, r.budget_approved) }}
                      </span>
                    </td>
                    <td>{{ formatDate(r.updated_at) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </section>
      <section class="budget-requests mt-1">
        <h2>Budget Requests</h2>
        <p class="section-description">Create and view your branch budget requests.</p>

        <div class="mb-1">
          <button class="btn-primary" v-if="!showBudgetForm" @click="showBudgetForm = true">+ New Budget Request</button>
        </div>

        <div v-if="showBudgetForm" class="budget-form mt-sm">
          <div class="form-grid">
            <div class="form-label">Purpose</div>
            <div class="form-field">
              <textarea v-model="budgetForm.purpose" rows="3" placeholder="Describe the purpose of the budget" @input="clearBudgetFieldError"></textarea>
            </div>

            <div class="form-label">Requested Amount</div>
            <div class="form-field inline-controls">
              <div class="amount-input">
                <span class="currency">₱</span>
                <input v-model="budgetForm.requested_amount" type="number" step="0.01" placeholder="0.00" @input="validateAmountField" />
              </div>
              <div class="action-row">
                <button class="btn-budget" @click="submitBudgetRequest" :disabled="budgetSubmitting">{{ budgetSubmitting ? 'Submitting...' : 'Submit Request' }}</button>
                <button class="btn-outline btn-cancel-inline" @click="cancelBudgetForm" :disabled="budgetSubmitting">Cancel</button>
              </div>
              <div class="field-note">
                <div v-if="budgetFieldError" class="error-msg">{{ budgetFieldError }}</div>
              </div>
            </div>
          </div>

          <div v-if="budgetError" class="error-msg mt-sm">{{ budgetError }}</div>
        </div>

        <div class="mt-1">
          <h3>My Budget Requests</h3>
          <div v-if="budgetLoading">Loading...</div>
          <div v-else-if="!budgetRequests.length">No budget requests.</div>
          <table v-else class="data-table">
            <thead>
              <tr><th>Date</th><th>Purpose</th><th>Amount</th><th>Status</th></tr>
            </thead>
            <tbody>
              <tr v-for="r in budgetRequests" :key="r.id">
                <td>{{ formatDate(r.date_requested) }}</td>
                <td>{{ r.purpose }}</td>
                <td>₱{{ r.requested_amount }}</td>
                <td>{{ r.status }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
      <section class="requested-products mt-1">
        <h2>
          Requests From Logistics
          <span v-if="procurementPendingCount > 0" class="panel-badge">{{ procurementPendingCount }}</span>
        </h2>
        <p class="section-description">Inventory requests sent by Logistics Managers in your branch.</p>

        <div v-if="requestedProductsLoading">Loading requests...</div>
        <div v-else-if="!requestedProducts.length">No requests from logistics.</div>
        <div v-else>
          <div class="inline-row gap-sm align-center mb-1">
            <h3 class="no-margin">Pending Logistics Requests ({{ requestedProducts.length }})</h3>
            <button class="btn-refresh" @click="loadRequestedProducts">🔄 Refresh</button>
          </div>
          <div class="product-grid">
            <div v-for="p in requestedProducts" :key="'req-'+p.id" class="product-card">
              <div class="product-name">{{ p.name }}</div>
              <div v-if="p.per_pack_or_individual" class="product-type-badge" :class="'type-' + p.per_pack_or_individual">
                {{ formatPricingType(p.per_pack_or_individual) }}
              </div>
              <div class="product-meta">
                <div class="product-price">{{ formatPrice(p.price) }}</div>
                <div>
                  <template v-if="(p.procurement_status === 'pending' || p.status === 'pending') && !p.needs_supplier && (p.acknowledge_allowed === undefined ? true : p.acknowledge_allowed)">
                    <button class="btn-small btn-primary" @click="acknowledgeRequest(p)">Acknowledge</button>
                  </template>
                  <template v-else-if="(p.procurement_status === 'pending' || p.status === 'pending') && p.needs_supplier">
                    <button class="btn-small btn-warning" @click="requestSupplier(p)" :disabled="requestingSupplierIds[(p.procurement_request_id || p.id)]">{{ requestingSupplierIds[(p.procurement_request_id || p.id)] ? 'Requesting...' : 'Request Supplier for Product' }}</button>
                  </template>
                  <template v-else-if="p.procurement_status === 'budget_pending' || p.status === 'budget_pending'">
                    <button class="btn-small btn-outline" disabled>Budget to be received</button>
                  </template>
                   <template v-else-if="p.procurement_status === 'pending_order_to_supplier' || p.status === 'pending_order_to_supplier' || p.procurement_status === 'ongoing_delivery' || p.status === 'ongoing_delivery' || p.procurement_status === 'receipt_confirmed' || p.receipt_confirmed || isReceiptChecking(p) || (p.procurement_budget_approved && (p.procurement_status === 'cash_in_transit' || p.status === 'cash_in_transit'))">
                     <div v-if="isReceiptChecking(p)">
                       <button class="btn-small btn-outline" disabled>Waiting receipt confirmation</button>
                     </div>
                     <div v-else-if="p.existingOrder" class="inline-row gap-sm align-center">
                       <div class="status-badge status-warning">
                         Transaction Pending (ID: {{ p.existingOrder.id }})
                       </div>
                       <div v-if="(p.existingOrder && (p.existingOrder.status === 'on_delivery' || p.existingOrder.status === 'ongoing_delivery' || p.existingOrder.status === 'fulfilled')) || p.procurement_status === 'delivery_pending' || p.procurement_status === 'ongoing_delivery'">
                           <template v-if="p.receipt_confirmed || p.procurement_status === 'ongoing_delivery' || p.procurement_status === 'receipt_confirmed' || (p.existingOrder && p.existingOrder.receipt_confirmed)">
                             <button class="btn-small btn-primary" @click="markDeliveryComplete(p)" :disabled="completingDeliveryIds[(p.procurement_request_id || p.id)]">
                               {{ completingDeliveryIds[(p.procurement_request_id || p.id)] ? 'Submitting...' : 'Complete Order' }}
                             </button>
                           </template>
                           <template v-else>
                             <button class="btn-small btn-primary" @click="openReceiptModal(p)" :disabled="completingDeliveryIds[(p.procurement_request_id || p.id)]">
                               {{ completingDeliveryIds[(p.procurement_request_id || p.id)] ? 'Submitting...' : 'Upload Receipt' }}
                             </button>
                           </template>
                         </div>
                       <div v-else-if="p.receipt_confirmed || p.procurement_status === 'receipt_confirmed' || (p.existingOrder && p.existingOrder.receipt_confirmed)">
                         <button class="btn-small btn-primary" @click="markDeliveryComplete(p)" :disabled="completingDeliveryIds[(p.procurement_request_id || p.id)]">
                           {{ completingDeliveryIds[(p.procurement_request_id || p.id)] ? 'Submitting...' : 'Complete Order' }}
                         </button>
                       </div>
                       <div v-else>
                         <button class="btn-small btn-primary" @click="openReceiptModal(p)" :disabled="completingDeliveryIds[(p.procurement_request_id || p.id)]">
                           {{ completingDeliveryIds[(p.procurement_request_id || p.id)] ? 'Submitting...' : 'Upload Receipt' }}
                         </button>
                       </div>
                       <div v-if="p.existingOrder?.estimated_delivery_datetime" class="estimated-delivery-info">
                         <span class="estimated-delivery-label">Est. Delivery:</span>
                         <span class="estimated-delivery-value">{{ formatDateTime(p.existingOrder.estimated_delivery_datetime) }}</span>
                       </div>
                     </div>
                       <div v-else-if="p.procurement_status === 'ongoing_delivery' || p.status === 'ongoing_delivery' || p.procurement_status === 'receipt_confirmed' || p.receipt_confirmed">
                       <button class="btn-small btn-primary"
                         @click="openReceiptModal(p)"
                         :disabled="completingDeliveryIds[(p.procurement_request_id || p.id)]">
                         {{ completingDeliveryIds[(p.procurement_request_id || p.id)] ? 'Submitting...' : 'Upload Receipt' }}
                       </button>
                       <div v-if="p.existingOrder?.estimated_delivery_datetime" class="estimated-delivery-info">
                         <span class="estimated-delivery-label">Est. Delivery:</span>
                         <span class="estimated-delivery-value">{{ formatDateTime(p.existingOrder.estimated_delivery_datetime) }}</span>
                       </div>
                     </div>
                       <div v-else>
                       <button class="btn-small btn-primary"
                         @click="placeOrder(p)"
                         :disabled="placingOrderIds[p.id] || orderPlacedIds[p.id] || p.waiting_for_supplier">
                         {{ orderPlacedIds[p.id] ? 'Order placed' : (placingOrderIds[p.id] ? 'Placing...' : 'Place Order') }}
                       </button>
                       <div v-if="p.waiting_for_supplier" class="note-warning">Waiting for supplier confirmation</div>
                     </div>
                   </template>
                  <template v-else>
                    <button class="btn-small btn-outline" disabled>Unavailable</button>
                  </template>
                </div>
              </div>
              <div class="supplier-badge mt-sm">{{ p.supplier_name || (p.supplier?.full_name || 'Unknown Supplier') }}</div>
            </div>
          </div>
        </div>
      </section>
      <transition name="fade">
        <div v-if="showAddModal" class="modal-backdrop" @click.self="closeAddSupplier">
          <div class="modal">
            <div class="modal-card">
              <div class="modal-header">
                <h3>Create Supplier Account</h3>
              </div>
              <div class="modal-body">
                <div class="form-group full-span">
                  <label>Full Name</label>
                  <input v-model="supplierForm.fullName" type="text" placeholder="Supplier full name" />
                </div>

                <div class="form-group full-span">
                  <label>Business Name</label>
                  <input v-model="supplierForm.businessName" type="text" placeholder="Company/Business name" />
                </div>

                <div class="form-group">
                  <label>Username</label>
                  <input v-model="supplierForm.username" type="text" placeholder="username" />
                </div>

                <div class="form-group">
                  <label>Email</label>
                  <input v-model="supplierForm.email" type="email" placeholder="supplier@example.com" />
                </div>

                <div class="form-group">
                  <label>Phone</label>
                  <input v-model="supplierForm.phone" type="text" placeholder="optional" />
                </div>

                <div class="form-group password-group">
                  <label>Default Password</label>
                  <div class="password-display-container">
                    <!-- Password Display Card -->
                    <div class="password-display-card">
                      <div class="password-display-label">Default Password (will be set automatically):</div>
                      <div class="password-display-value">
                        <span class="password-text">{{ fetchedDefaultPassword || 'Chikintayo_123' }}</span>
                        <button type="button" class="btn btn-primary btn-copy" @click="copyDefaultToClipboard">
                          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                          </svg>
                          Copy Password
                        </button>
                      </div>
                      <div class="form-hint">This password will be assigned to the supplier account. Leave blank to use default (backend auto-generates if needed).</div>
                    </div>

                    <!-- Loading state -->
                    <div v-if="fetchingDefaultPassword" class="password-loading">
                      <span class="muted small-text">Loading default password...</span>
                    </div>
                  </div>
                </div>

                <div v-if="formError" class="error-msg">{{ formError }}</div>
                <div v-if="formSuccess" class="success-msg">{{ formSuccess }}</div>
              </div>
              <div class="modal-footer">
                <button class="btn-outline" @click="closeAddSupplier" :disabled="isSubmitting">Cancel</button>
                <button class="btn-primary" @click="submitAddSupplier" :disabled="isSubmitting">Create</button>
              </div>
            </div>
          </div>
        </div>
      </transition>
    </template>

    <template #sideTop>
      <div class="header-profile-wrapper" style="margin-bottom:12px;" @click.stop>
        <button class="header-profile-btn" @click="toggleProfileDropdown">
          <div class="header-avatar">
            <div v-if="userProfile.avatarUrl" class="header-avatar-img" :style="{ backgroundImage: 'url('+userProfile.avatarUrl+')' }"></div>
            <div v-else class="header-avatar-initials">{{ (userProfile.fullName || userProfile.full_name || 'U').charAt(0) }}</div>
          </div>
          <div class="header-name">{{ (userProfile.fullName || userProfile.full_name || '').toUpperCase() }}</div>
        </button>
        <div v-if="profileDropdownVisible" class="header-profile-dropdown" @click.stop>
          <button class="dropdown-item" @click="openInfoFromHeader">Info</button>
          <button class="dropdown-item" @click="triggerLogoutFromHeader">Logout</button>
        </div>
      </div>
    </template>

    <template #side>
      <section class="panel-block hr-settings-panel">
        <div class="panel-header"><h2>Procurement Settings</h2></div>
        <div class="panel-body panel-body--list">
          <div class="side-item"><span>View procurement orders and supplier info</span></div>
        </div>
      </section>
    </template>

  </OwnerPanelLayout>

  <transition name="fade">
    <div v-if="showLogoutConfirm" class="logout-confirm-backdrop">
      <div class="logout-confirm-box">
        <h3>Logout from Procurement Manager Panel?</h3>
        <p>This will end your current session for Chikin Tayo Manager.</p>
        <div class="logout-actions">
          <button class="btn-cancel" @click="cancelLogout" :disabled="isLoggingOut">Cancel</button>
          <button class="btn-confirm" @click="confirmLogout" :disabled="isLoggingOut">Yes, logout</button>
        </div>
      </div>
    </div>
  </transition>

  <!-- Receipt upload modal (supplier must paste/upload physical receipt) -->
  <transition name="fade">
    <div v-if="showReceiptModal" class="modal-backdrop" @click.self="closeReceiptModal">
      <div class="modal">
        <div class="modal-card">
          <div class="modal-header">
            <h3>Upload Physical Receipt</h3>
          </div>
          <div class="modal-body">
            <div class="form-group full-span">
              <label>Please upload a clear photo of the physical receipt (required)</label>
              <input type="file" accept="image/*" @change="onReceiptSelected" />
            </div>
            <div class="form-group full-span" v-if="receiptPreview">
              <label>Preview</label>
              <img :src="receiptPreview" alt="receipt preview" class="receipt-preview" />
            </div>
            <div v-if="receiptError" class="error-msg">{{ receiptError }}</div>
            <div class="form-note">After you submit the receipt, Finance must confirm it before status becomes On Delivery.</div>
          </div>
          <div class="modal-footer">
            <button class="btn-outline" @click="closeReceiptModal" :disabled="receiptUploading">Cancel</button>
            <button class="btn-primary" @click="submitReceipt" :disabled="!receiptFile || receiptUploading">{{ receiptUploading ? 'Uploading...' : 'Submit Receipt' }}</button>
          </div>
        </div>
      </div>
    </div>
  </transition>
  <!-- Supplier selection modal - shown during Acknowledge button -->
  <transition name="fade">
    <div v-if="supplierModalVisible" class="modal-backdrop" @click.self="closeSupplierModal">
      <div class="modal">
        <div class="modal-card">
          <div class="modal-header">
            <h3>{{ showingConfirmedSuppliersOnly ? 'Select Confirmed Supplier' : 'Select Supplier' }}</h3>
          </div>
          <div class="modal-body">
            <div class="form-group full-span">
              <label>Choose a supplier to fulfill: <strong>{{ pendingOrderProduct?.name || '' }}</strong></label>
            </div>
            <div v-if="showingConfirmedSuppliersOnly" class="form-note mb-1">
              <strong style="color: #28a745;">✓ These suppliers have confirmed they have this product available.</strong>
            </div>
            <div class="form-group full-span">
              <div v-if="supplierLoading">Loading suppliers...</div>
              <div v-else-if="!supplierList.length">No suppliers available.</div>
              <div v-else class="supplier-list-scroll">
                <div v-for="s in supplierList" :key="s.id" class="supplier-row">
                  <input type="radio" :id="'sup-'+s.id" :value="s.id" v-model="selectedSupplierId" />
                  <label :for="'sup-'+s.id" class="supplier-label">
                    <div class="supplier-name">{{ s.full_name || s.username }}</div>
                    <div class="supplier-contact">{{ s.email || 'no-email' }}</div>
                    <div v-if="showingConfirmedSuppliersOnly && confirmedSuppliers" class="supplier-details">
                      <template v-for="cs in confirmedSuppliers.filter(x => x.supplier_id === s.id)" :key="cs.order_id">
                        <div class="detail-line">Price: {{ formatPrice(cs.product_price) }} | Stock: {{ cs.product_stock }} | Expires: {{ formatDate(cs.product_expiry) }}</div>
                        <div v-if="cs.per_pack_or_individual" class="detail-line">Type: {{ formatPricingType(cs.per_pack_or_individual) }}</div>
                      </template>
                    </div>
                  </label>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label>Quantity (optional)</label>
              <input type="number" v-model.number="pendingOrderQty" min="1" />
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn-outline" @click="closeSupplierModal">Cancel</button>
            <button class="btn-primary" @click="confirmSupplierSelection">{{ pendingAcknowledgeProduct ? 'Select & Acknowledge' : 'Confirm' }}</button>
          </div>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { ref, onMounted, computed, onUnmounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import OwnerPanelLayout from './OwnerPanelLayout.vue'
import axios from 'axios'
import { showToast } from './toastStore'

const router = useRouter()
const userProfile = ref({})
const dashboardTotals = ref({ totalSuppliers: 0, activeSuppliers: 0, pendingRequests: 0 })
const showLogoutConfirm = ref(false)
const isLoggingOut = ref(false)

// Budget request state
const budgetRequests = ref([])
const budgetLoading = ref(false)
const showBudgetForm = ref(false)
const budgetForm = ref({ purpose: '', requested_amount: '' })
const budgetSubmitting = ref(false)
const budgetError = ref('')
const budgetFieldError = ref('')

function clearBudgetFieldError() { budgetFieldError.value = '' }

function validateAmountField() {
  const amt = budgetForm.value.requested_amount
  if (amt === '' || amt === null || amt === undefined || Number(amt) <= 0) {
    budgetFieldError.value = 'Please enter an amount greater than 0.'
  } else {
    budgetFieldError.value = ''
  }
}

function cancelBudgetForm() {
  if (budgetSubmitting.value) return
  showBudgetForm.value = false
  budgetForm.value.purpose = ''
  budgetForm.value.requested_amount = ''
  budgetError.value = ''
  budgetFieldError.value = ''
}

// Manual procurement form (manager panel)
const showProcRequestFormManager = ref(false)
  const procRequestFormManager = ref({ product_id: '', quantity: 1, price: null, request_budget: false, receipt: null, product_image: null })
const procRequestSubmittingManager = ref(false)
const procRequestFormManagerError = ref('')

function cancelProcRequestManager() {
  if (procRequestSubmittingManager.value) return
  showProcRequestFormManager.value = false
  procRequestFormManager.value = { product_id: '', quantity: 1, request_budget: false, receipt: null, product_image: null }
  procRequestFormManagerError.value = ''
}

function onReceiptChangeManager(e) {
  const f = e?.target?.files?.[0] ?? null
  procRequestFormManager.value.receipt = f
}

function onProductImageChangeManager(e) {
  const f = e?.target?.files?.[0] ?? null
  procRequestFormManager.value.product_image = f
}

async function submitProcRequestManager() {
  procRequestFormManagerError.value = ''
  if (!procRequestFormManager.value.product_id) {
    procRequestFormManagerError.value = 'Please select a product'
    return
  }
  const quantity = Number(procRequestFormManager.value.quantity)
  if (!quantity || quantity <= 0) {
    procRequestFormManagerError.value = 'Please enter a quantity greater than 0'
    return
  }
    // Ensure required files are present
    if (!procRequestFormManager.value.receipt) {
      procRequestFormManagerError.value = 'Receipt image is required.'
      procRequestSubmittingManager.value = false
      return
    }
    if (!procRequestFormManager.value.product_image) {
      procRequestFormManagerError.value = 'Product image is required.'
      procRequestSubmittingManager.value = false
      return
    }
  procRequestSubmittingManager.value = true
  try {
    // ensure CSRF cookie and set token header
    await ensureCsrf()
    const form = new FormData()
    form.append('product_id', procRequestFormManager.value.product_id)
    form.append('quantity', quantity)
    if (procRequestFormManager.value.request_budget) form.append('request_budget', '1')
    if (procRequestFormManager.value.price !== null && procRequestFormManager.value.price !== undefined) {
      form.append('price', String(procRequestFormManager.value.price))
    }

    // attempt to include supplier_id from loaded products
    try {
      const selected = (products.value || []).find(p => Number(p.id) === Number(procRequestFormManager.value.product_id))
      if (selected && selected.supplier_id) form.append('supplier_id', selected.supplier_id)
    } catch (e) {}

    if (procRequestFormManager.value.receipt) form.append('receipt', procRequestFormManager.value.receipt)
    if (procRequestFormManager.value.product_image) form.append('product_image', procRequestFormManager.value.product_image)

    const res = await axios.post('/api/procurement-requests/manual', form, { withCredentials: true, headers: { 'Content-Type': 'multipart/form-data' } })
    const created = res.data?.data ?? res.data ?? null
    showToast(`✓ Procurement request created`, 'success')
    showProcRequestFormManager.value = false
    procRequestFormManager.value = { product_id: '', quantity: 1, request_budget: false, receipt: null, product_image: null }
    procRequestFormManagerError.value = ''
    // refresh lists
    try { await loadRequestedProducts() } catch (e) {}
    try { await loadProducts() } catch (e) {}
    await refreshAllData()
  } catch (e) {
    console.error('submitProcRequestManager error', e)
    procRequestFormManagerError.value = e.response?.data?.message || 'Failed to create procurement request'
    showToast(procRequestFormManagerError.value, 'error')
  } finally {
    procRequestSubmittingManager.value = false
  }
}

// Products for procurement manager (branch-scoped)
const products = ref([])
const loadingProducts = ref(false)

const pendingProducts = computed(() => (products.value || []).filter(p => !p.is_published))
const publishedProducts = computed(() => (products.value || []).filter(p => p.is_published))

// Organize published products by category
const publishedProductCategories = computed(() => {
  const cats = new Set()
  publishedProducts.value.forEach(p => {
    cats.add(p.category || 'Uncategorized')
  })
  return Array.from(cats).sort()
})

function getPublishedProductsByCategory(category) {
  return publishedProducts.value.filter(p => (p.category || 'Uncategorized') === category)
}

// Requested products (logistics requests)
const requestedProducts = ref([])
const requestedProductsLoading = ref(false)
const hasNotified = ref(false)
const procurementPendingCount = computed(() => {
  const dashboardPending = Number(dashboardTotals.value?.pendingRequests || 0)
  const logisticsPending = (requestedProducts.value || []).length
  return Math.max(dashboardPending, logisticsPending, 0)
})

watch(procurementPendingCount, (count) => {
  if (!hasNotified.value && count > 0) {
    showToast('You have pending procurement requests.', 'info')
    hasNotified.value = true
  }
})
// track per-item placing/completing state to avoid global disable for all items
const placingOrderIds = ref({})
const orderPlacedIds = ref({})
const completingDeliveryIds = ref({})
const receiptPendingIds = ref({})
const requestingSupplierIds = ref({})

function setPlacingFlag(id, val) {
  placingOrderIds.value = { ...(placingOrderIds.value || {}), [id]: val }
}

function setRequestingFlag(id, val) {
  requestingSupplierIds.value = { ...(requestingSupplierIds.value || {}), [id]: val }
}

function setOrderPlacedFlag(id, val) {
  orderPlacedIds.value = { ...(orderPlacedIds.value || {}), [id]: val }
}

function setCompletingFlag(id, val) {
  completingDeliveryIds.value = { ...(completingDeliveryIds.value || {}), [id]: val }
}

function setReceiptPendingFlag(id, val) {
  receiptPendingIds.value = { ...(receiptPendingIds.value || {}), [id]: val }
}

function isReceiptChecking(item) {
  const status = (item?.procurement_status || item?.status || '').toLowerCase()
  const orderStatus = (item?.existingOrder?.status || '').toLowerCase()
  if (receiptPendingIds.value?.[item?.id]) return true
  return status === 'receipt_submitted' || status === 'pending_receipt_check' || status === 'pending_receipt' || orderStatus === 'receipt_submitted' || orderStatus === 'pending_receipt_check' || orderStatus === 'pending_receipt'
}

// Header profile dropdown (procurement-specific)
const profileDropdownVisible = ref(false)
const ownerLayout = ref(null)

function toggleProfileDropdown() {
  profileDropdownVisible.value = !profileDropdownVisible.value
}

function closeProfileDropdown() { profileDropdownVisible.value = false }

// Open the OwnerPanelLayout info modal by clicking the left 'Info' button (UI-only trigger)
function openInfoFromHeader() {
  closeProfileDropdown()
  // Try calling OwnerPanelLayout exposed method first (works when left column hidden)
  try {
    if (ownerLayout.value && typeof ownerLayout.value.openInfoModal === 'function') {
      ownerLayout.value.openInfoModal()
      return
    }
  } catch (e) {}
  // Fallback: dispatch a global event the layout listens for (works even if ref not available)
  try { window.dispatchEvent(new Event('open-owner-info')); return } catch (e) {}
  // Last resort: find the left-side Info button and click it
  const infoBtn = document.querySelector('.admin-info-btn')
  if (infoBtn) infoBtn.click()
}

// Open info modal and try to click 'Edit Information' inside it to enter edit mode
function openEditProfileFromHeader() {
  closeProfileDropdown()
  // Try opening the global avatar picker exposed by OwnerPanelLayout
  try {
    if (ownerLayout.value && typeof ownerLayout.value.openAvatarPicker === 'function') {
      ownerLayout.value.openAvatarPicker()
      return
    }
  } catch (e) {}

  // Fallback: dispatch the existing edit-profile event so older layouts still work
  try { window.dispatchEvent(new Event('open-owner-edit-profile')); return } catch (e) {}

  // Last resort: attempt to find any visible avatar file input and click it
  const fileInput = document.querySelector('#avatar-input') || document.querySelector('#avatar-input-modal') || document.querySelector('#global-avatar-input')
  if (fileInput) fileInput.click()
}

async function triggerLogoutFromHeader() {
  closeProfileDropdown()
  // show SweetAlert confirmation then proceed
  try {
    const ok = await (window.swalConfirm ? window.swalConfirm('This will end your current session for Chikin Tayo Manager.', 'Confirm logout') : Promise.resolve(false))
    if (ok) await confirmLogout()
  } catch (e) { console.error('triggerLogoutFromHeader failed', e) }
}

// Close dropdown when clicking outside
window.addEventListener('click', (e) => {
  try { if (profileDropdownVisible.value) closeProfileDropdown() } catch (e) {}
})

// Procurement requests history
const procurementHistory = ref([])
const procurementHistoryLoading = ref(false)

// Add Supplier modal state
const showAddModal = ref(false)

const isSubmitting = ref(false)
const supplierForm = ref({
  username: '',
  email: '',
  fullName: '',
  businessName: '',
  phone: '',
  password: ''
})
const formError = ref('')
const formSuccess = ref('')

// Default password state
const fetchedDefaultPassword = ref(null)
const fetchingDefaultPassword = ref(false)

async function refreshAllData() {
  try {
    const dash = await axios.get('/api/manager/procurement/dashboard', { withCredentials: true })
    dashboardTotals.value = dash.data || {}
  } catch (e) {
    dashboardTotals.value = { totalSuppliers: 0, activeSuppliers: 0, pendingRequests: 0 }
  }
}

function goToStaffManagement() {
  window.location.href = '/manager/procurement/staff-management'
}

onMounted(async () => {
  try {
    const res = await axios.get('/api/manager/procurement/profile', { withCredentials: true })
    userProfile.value = res.data.user || {}
  } catch (e) {
    // ignore
  }
  await refreshAllData()
  try {
    await loadProducts()
  } catch (e) {}
})

function cancelLogout() { showLogoutConfirm.value = false }
async function confirmLogout() {
  try { await axios.post('/api/logout', {}, { withCredentials: true })
  } catch (e) {} finally {
    localStorage.clear();
    sessionStorage.clear();
    window.location.replace('/staff-landing')
  }
}

async function askLogout() {
  try {
    const ok = await (window.swalConfirm ? window.swalConfirm('This will end your current session for Chikin Tayo Manager.', 'Confirm logout') : Promise.resolve(false))
    if (ok) await confirmLogout()
  } catch (e) { console.error('askLogout failed', e) }
}

function onProfileUpdated(updatedProfile) {
  userProfile.value = { ...userProfile.value, ...updatedProfile }
}

defineExpose({ refreshAllData, onProfileUpdated })

async function fetchDefaultPassword() {
  const userRole = window.userRole || '';
  if (userRole !== 'OWNER' && userRole !== 'ADMIN' && userRole !== 'SUPER_ADMIN' && userRole !== 'SUPERADMIN') {
    fetchedDefaultPassword.value = 'Chikintayo_123';
    return;
  }

  if (fetchingDefaultPassword.value) return
  fetchingDefaultPassword.value = true
  try {
    const res = await axios.get('/api/admin/config/default-password', { withCredentials: true })
    if (res.data && res.data.success && res.data.default_password) {
      fetchedDefaultPassword.value = res.data.default_password
    } else {
      fetchedDefaultPassword.value = 'Chikintayo_123'
    }
  } catch (e) {
    fetchedDefaultPassword.value = 'Chikintayo_123'
  } finally {
    fetchingDefaultPassword.value = false
  }
}

function copyDefaultToClipboard() {
  const passwordToCopy = fetchedDefaultPassword.value || 'Chikintayo_123'
  if (!passwordToCopy) return
  try {
    navigator.clipboard?.writeText(passwordToCopy)
    alert('Password copied to clipboard: ' + passwordToCopy)
  } catch (e) {
    const textArea = document.createElement('textarea')
    textArea.value = passwordToCopy
    document.body.appendChild(textArea)
    textArea.select()
    document.execCommand('copy')
    document.body.removeChild(textArea)
    alert('Password copied to clipboard: ' + passwordToCopy)
  }
}

function openAddSupplier() {
  supplierForm.value = {
    username: '',
    email: '',
    fullName: '',
    businessName: '',
    phone: '',
    password: ''
  }
  formError.value = ''
  formSuccess.value = ''
  fetchedDefaultPassword.value = null
  showAddModal.value = true
  fetchDefaultPassword()
  console.log('openAddSupplier called')
}

function closeAddSupplier() {
  if (isSubmitting.value) return
  showAddModal.value = false
}

async function submitAddSupplier() {
  if (isSubmitting.value) return
  isSubmitting.value = true
  try {
    const payload = {
      username: supplierForm.value.username,
      email: supplierForm.value.email,
      fullName: supplierForm.value.fullName,
      businessName: supplierForm.value.businessName,
      phone: supplierForm.value.phone,
      password: supplierForm.value.password || undefined, // optional
    }
    const res = await axios.post('/api/manager/procurement/suppliers', payload, { withCredentials: true })
    // refresh and close
    await refreshAllData()
    showAddModal.value = false
    alert(res.data.message || 'Supplier created successfully')
  } catch (err) {
    const msg = err?.response?.data?.message || 'Failed to create supplier'
    formError.value = msg
    alert(msg)
  } finally {
    isSubmitting.value = false
  }
}

async function loadProducts() {
  loadingProducts.value = true
  try {
    const pres = await axios.get('/api/manager/procurement/products', { withCredentials: true })
    if (pres && pres.data) {
      // supports both {data: [...] } and direct array
      if (Array.isArray(pres.data)) products.value = pres.data
      else if (Array.isArray(pres.data.data)) products.value = pres.data.data
      else products.value = []
    }
  } catch (e) {
    console.warn('Failed to load procurement products', e)
    products.value = []
  } finally {
    loadingProducts.value = false
  }
}

async function loadRequestedProducts() {
  requestedProductsLoading.value = true
  try {
    const res = await axios.get('/api/procurement-requests/requested-products', { withCredentials: true })
    requestedProducts.value = res.data || []
  } catch (e) {
    console.warn('Failed to load requested products', e)
    requestedProducts.value = []
  } finally {
    requestedProductsLoading.value = false
  }
}

async function acknowledgeRequest(product) {
  // Check if there are multiple confirmed suppliers to choose from
  if (product.procurement_request_id) {
    try {
      const confirmedRes = await axios.get(`/api/procurement-requests/${product.procurement_request_id}/confirmed-suppliers`, { withCredentials: true })
      if (confirmedRes.data && confirmedRes.data.ok && confirmedRes.data.suppliers) {
        const confirmedList = confirmedRes.data.suppliers
        // Treat entries with zero stock/price as not confirmed yet
        const filteredConfirmed = confirmedList.filter(s => Number(s.product_stock || 0) > 0 || Number(s.product_price || 0) > 0)
        // If there are multiple confirmed suppliers, open modal for selection
        if (filteredConfirmed.length > 1) {
          pendingAcknowledgeProduct.value = product  // Store for later when user selects supplier
          openSupplierModal(product, true)  // true = isForAcknowledge
          return
        }
        // If exactly one supplier confirmed, auto-assign it and skip modal
        if (filteredConfirmed.length === 1) {
          const onlySupplier = filteredConfirmed[0]
          product.supplier_id = onlySupplier.supplier_id
          product.supplier_name = onlySupplier.supplier_name
        }
        // If none have confirmed and none is already set, block acknowledgement
        if (filteredConfirmed.length === 0 && !product.supplier_id) {
          alert('This product has no supplier yet. Please request a supplier or wait for confirmation.')
          return
        }
      }
    } catch (e) {
      console.warn('Failed to check confirmed suppliers', e)
      // Continue with acknowledgement regardless
    }
  }

  if (!(await window.swalConfirm(`Acknowledge logistics request for ${product.name}? (Sends to finance for budget)`))) return

  try {
    const requestId = product.procurement_request_id || product.id
    const payload = {}
    // Send selected supplier_id if available (for single confirmed supplier scenario)
    if (product.supplier_id) {
      payload.supplier_id = product.supplier_id
    }

    const res = await axios.post(`/api/procurement-requests/${requestId}/status`, payload, { withCredentials: true })

    // Use message from backend if available
    const successMsg = res.data?.message || '✓ Request acknowledged and sent to Finance for budget approval!'
    alert(successMsg)

    await loadRequestedProducts()
    await loadProducts()
    await refreshAllData()  // Refresh to show updated status
  } catch (e) {
    // Show actual error message from backend
    const errorMsg = e.response?.data?.error || e.response?.data?.message || 'Failed to acknowledge request'
    console.error('acknowledgeRequest error:', errorMsg, e)

      // If backend indicates the selected supplier is not among confirmed suppliers,
      // open the supplier modal so the user can pick a valid confirmed supplier.
      if (typeof errorMsg === 'string' && errorMsg.includes('Selected supplier not found')) {
        try {
          openSupplierModal(product, true)
          return
        } catch (openErr) {
          console.warn('Failed to open supplier modal automatically', openErr)
        }
      }

    // If error is about needing supplier selection
    if (errorMsg.includes('supplier selection')) {
      alert('✓ Multiple suppliers available - please select one from the list')
      // Modal should already be open if this was called from multi-supplier flow
      return
    }

    // If error is about missing supplier, offer to broadcast
    if (errorMsg.includes('supplier') || errorMsg.includes('price')) {
      const shouldBroadcast = await window.swalConfirm(`${errorMsg}\n\nWould you like to request suppliers to provide this product?`)
      if (shouldBroadcast) {
        await requestSupplier(product)
      }
    } else {
      alert('❌ ' + errorMsg)
    }
  }
}

async function requestSupplier(product) {
  const productId = product.procurement_request_id || product.id
  if (requestingSupplierIds.value[productId]) return

  if (!(await window.swalConfirm(`Request suppliers to provide ${product.name}?`))) return

  setRequestingFlag(productId, true)
  try {
    const res = await axios.post(`/api/procurement-requests/${productId}/broadcast`, {}, { withCredentials: true })
    alert('✓ ' + (res.data?.message || 'Supplier request broadcasted successfully!\n\nSuppliers will receive the request and can submit their products.'))
    await loadRequestedProducts()
    await loadProducts()
    await refreshAllData()
  } catch (e) {
    console.error('requestSupplier failed', e)
    const errorMsg = e.response?.data?.error || e.response?.data?.message || 'Failed to request supplier'
    alert('❌ ' + errorMsg)
    setRequestingFlag(productId, false)
  }
}

async function ensureCsrf() {
  try {
    await axios.get('/sanctum/csrf-cookie', { withCredentials: true })
    const match = document.cookie.match(new RegExp('(^|; )' + 'XSRF-TOKEN' + '=([^;]*)'))
    const token = match ? decodeURIComponent(match[2]) : null
    if (token) axios.defaults.headers.common['X-XSRF-TOKEN'] = token
    return true
  } catch (e) {
    console.error('ensureCsrf failed:', e)
    return false
  }
}

onMounted(async () => {
  try {
    const res = await axios.get('/api/manager/procurement/profile', { withCredentials: true })
    userProfile.value = res.data.user || {}
  } catch (e) {
    // ignore
  }
  await refreshAllData()
  await loadProducts()
  await loadRequestedProducts()
  await fetchBudgetRequests()
  await loadProcurementHistory()
})

async function loadProcurementHistory() {
  procurementHistoryLoading.value = true
  try {
    const res = await axios.get('/api/procurement-requests', { withCredentials: true })
    const data = res.data?.data ?? res.data ?? (res.data ? [res.data] : [])
    procurementHistory.value = Array.isArray(data) ? data : []
  } catch (e) {
    console.warn('Failed to load procurement history', e)
    procurementHistory.value = []
  } finally {
    procurementHistoryLoading.value = false
  }
}

// Supplier selection modal state
const supplierModalVisible = ref(false)
const supplierList = ref([])
const supplierLoading = ref(false)
const selectedSupplierId = ref(null)
const pendingOrderProduct = ref(null)
const pendingOrderQty = ref(null)
const confirmedSuppliers = ref([])
const showingConfirmedSuppliersOnly = ref(false)
const pendingAcknowledgeProduct = ref(null)  // Track product waiting for acknowledgement after supplier selection

async function openSupplierModal(product, isForAcknowledge = false) {
  pendingOrderProduct.value = product
  pendingOrderQty.value = null
  selectedSupplierId.value = null
  pendingAcknowledgeProduct.value = isForAcknowledge ? product : null
  supplierModalVisible.value = true
  supplierLoading.value = true

  try {
    // First, try to fetch suppliers who have already confirmed they have the product
    confirmedSuppliers.value = []
    showingConfirmedSuppliersOnly.value = false

    if (product.procurement_request_id) {
      try {
        const confirmedRes = await axios.get(`/api/procurement-requests/${product.procurement_request_id}/confirmed-suppliers`, { withCredentials: true })
        if (confirmedRes.data && confirmedRes.data.ok && confirmedRes.data.suppliers) {
          const confirmedList = confirmedRes.data.suppliers
          // Treat entries with zero stock/price as not confirmed yet
          const filteredConfirmed = confirmedList.filter(s => Number(s.product_stock || 0) > 0 || Number(s.product_price || 0) > 0)
          if (filteredConfirmed.length > 0) {
            confirmedSuppliers.value = filteredConfirmed
            // Convert the confirmed suppliers to the same format as regular suppliers
            supplierList.value = filteredConfirmed.map(s => ({
              id: s.supplier_id,
              full_name: s.supplier_name,
              username: s.supplier_username,
              email: s.supplier_email
            }))
            showingConfirmedSuppliersOnly.value = true
            return
          }
        }
      } catch (e) {
        console.warn('Failed to fetch confirmed suppliers, falling back to all suppliers', e)
      }
    }

    // If no confirmed suppliers or failed to fetch, get all available suppliers
    const res = await axios.get('/api/manager/logistics/suppliers', { withCredentials: true })
    supplierList.value = (res.data && res.data.suppliers) || []
  } catch (e) {
    console.error('openSupplierModal error:', e)
    supplierList.value = []
  } finally {
    supplierLoading.value = false
  }
}

function closeSupplierModal() {
  supplierModalVisible.value = false
  pendingOrderProduct.value = null
  pendingOrderQty.value = null
  selectedSupplierId.value = null
  pendingAcknowledgeProduct.value = null
}

async function confirmSupplierSelection() {
  if (!pendingOrderProduct.value) return

  // Ensure a supplier is selected
  if (!selectedSupplierId.value) {
    alert('Please select a supplier');
    return
  }

  // Save product data before we close the modal (which clears pendingOrderProduct)
  const product = pendingOrderProduct.value
  const isAcknowledge = pendingAcknowledgeProduct.value !== null

  // Get the selected supplier ID (should be a number from radio button)
  const supplierId = parseInt(selectedSupplierId.value) || selectedSupplierId.value

  console.log('[confirmSupplierSelection] Starting', {
    isAcknowledge,
    productId: product.id,
    productName: product.name,
    procurementRequestId: product.procurement_request_id,
    selectedSupplierId: supplierId,
    selectedSupplierIdType: typeof supplierId
  })

  // mark this product id as placing/acknowledging
  setPlacingFlag(product.id, true)
  try {
    // DIFFERENT FLOWS BASED ON WHETHER THIS IS ACKNOWLEDGE OR PLACE ORDER

    if (isAcknowledge) {
      // ===== ACKNOWLEDGE FLOW (with supplier selection) =====
      const confirmed = await window.swalConfirm(`Acknowledge logistics request for ${product.name}?\n(Sends to finance for budget)\n\nSupplier: ${selectedSupplierId.value}`)
      if (!confirmed) {
        setPlacingFlag(product.id, false)
        return
      }

      // Call acknowledge endpoint with supplier_id
      const requestId = product.procurement_request_id || product.id

      console.log('[confirmSupplierSelection] Sending acknowledge request', {
        url: `/api/procurement-requests/${requestId}/status`,
        payload: { supplier_id: supplierId }
      })

      const res = await axios.post(`/api/procurement-requests/${requestId}/status`, {
        supplier_id: supplierId
      }, { withCredentials: true })

      const successMsg = res.data?.message || '✓ Request acknowledged! Budget request sent to Finance.'
      console.log('[confirmSupplierSelection] Acknowledge succeeded', {
        message: successMsg,
        supplierId,
        procReqId: requestId
      })
      alert(successMsg)

      closeSupplierModal()

      // Update local UI to show supplier was selected
      product.supplier_id = supplierId
      product.procurement_status = 'budget_pending'
      product.status = 'budget_pending'

      // Refresh to get updated state from server
      await loadRequestedProducts()
      await loadProducts()
      await refreshAllData()

    } else {
      // ===== PLACE ORDER FLOW =====
      // Note: Quantity is locked to what logistics requested and cannot be changed
      const payload = {
        supplier_id: supplierId,
        procurement_request_id: product.procurement_request_id
      }

      console.log('[confirmSupplierSelection] Sending place-order request', {
        url: `/api/manager/procurement/products/${product.id}/place-order`,
        payload
      })

      const res = await axios.post(`/api/manager/procurement/products/${product.id}/place-order`, payload, { withCredentials: true })
      const supplierOrder = res.data.supplier_order
      const procReq = res.data.procurement_request

      // Check if budget is still pending (202 Accepted response)
      if (res.data.budget_pending) {
        // Backend returned 202: Budget request created, waiting for approval
        closeSupplierModal()
        alert('✓ Request acknowledged! Budget request sent to Finance.\n\nPlease wait for Finance to approve the budget before placing the order.')

        // Update UI to show budget pending state
        product.supplier_id = supplierId
        product.procurement_status = 'budget_pending'
        product.status = 'budget_pending'
        product.waiting_for_budget_approval = true

        await loadRequestedProducts()
        await loadProducts()
        return  // Exit early - don't proceed with order placement
      }

      // Order placed successfully
      alert(res.data.message || 'Order placed successfully')
      setOrderPlacedFlag(product.id, true)

      // Optimistically update local product entries so the Place Order button hides
      try {
        // Update products list
        const idx = products.value.findIndex(p => p.id === product.id)
        if (idx > -1) {
          if (supplierOrder) products.value[idx].existingOrder = supplierOrder
          if (procReq && procReq.status) {
            products.value[idx].procurement_status = procReq.status
            products.value[idx].status = procReq.status
          }
        }

        // Update requestedProducts list (if present)
        const ridx = requestedProducts.value.findIndex(p => p.id === product.id)
        if (ridx > -1) {
          if (supplierOrder) requestedProducts.value[ridx].existingOrder = supplierOrder
          if (procReq && procReq.status) {
            requestedProducts.value[ridx].procurement_status = procReq.status
            requestedProducts.value[ridx].status = procReq.status
          }
        }
      } catch (e) {
        // ignore local update failures
      }

      // Refresh lists to ensure server canonical state (non-blocking)
      await loadProducts()
      await loadRequestedProducts()
      await refreshAllData()
    }
  } catch (e) {
    console.error('confirmSupplierSelection failed', e)
    const errorData = e.response?.data
    let errorMsg = errorData?.message || errorData?.error || 'Failed to complete action'
    const debugInfo = errorData?.debug ? ` [Debug: ${JSON.stringify(errorData.debug)}]` : ''

    // Better error message for multi-supplier scenario
    if (errorData?.need_supplier_selection && errorData?.confirmed_suppliers > 1) {
      errorMsg = `Please select a supplier from the list. There are ${errorData.confirmed_suppliers} suppliers available for this product.`
    }

    console.error('[confirmSupplierSelection] Error details', {
      status: e.response?.status,
      error: errorMsg,
      needSelection: errorData?.need_supplier_selection,
      confirmedCount: errorData?.confirmed_suppliers,
      isAcknowledge: isAcknowledge,
      selectedSupplierId: selectedSupplierId.value
    })

    alert('❌ ' + errorMsg + debugInfo)
  } finally {
    // clear placing flag
    setPlacingFlag(product.id, false)
    closeSupplierModal()
  }
}

async function fetchBudgetRequests() {
  budgetLoading.value = true
  try {
    const res = await axios.get('/api/procurement/budget/my-requests', { withCredentials: true })
    if (res.data && res.data.ok) {
      budgetRequests.value = res.data.requests || []
    } else {
      budgetRequests.value = []
    }
  } catch (e) {
    console.error('Failed to load budget requests', e)
    budgetRequests.value = []
  } finally {
    budgetLoading.value = false
  }
}

async function submitBudgetRequest() {
  if (budgetSubmitting.value) return
  budgetError.value = ''
  if (!budgetForm.value.purpose || !budgetForm.value.requested_amount) {
    budgetError.value = 'Please fill purpose and amount.'
    return
  }
  budgetSubmitting.value = true
  try {
    const payload = {
      purpose: budgetForm.value.purpose,
      requested_amount: budgetForm.value.requested_amount
    }
    const res = await axios.post('/api/procurement/budget/create', payload, { withCredentials: true })
    if (res.data && res.data.ok) {
      alert('Budget request created')
      showBudgetForm.value = false
      budgetForm.value.purpose = ''
      budgetForm.value.requested_amount = ''
      await fetchBudgetRequests()
    } else {
      budgetError.value = res.data?.message || 'Failed to create request'
    }
  } catch (e) {
    console.error('Create budget request failed', e)
    budgetError.value = e.response?.data?.message || 'Failed to create request'
  } finally {
    budgetSubmitting.value = false
  }
}

// Helper to format price nicely for display
function formatPrice(val) {
  if (val === null || val === undefined) return '₱0.00'
  const n = Number(val)
  if (Number.isNaN(n)) return '₱0.00'
  return '₱' + n.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function formatDate(d) {
  if (!d) return ''
  try { return new Date(d).toLocaleString() } catch (e) { return d }
}

function formatVariance(val) {
  if (val === null || val === undefined || val === 0) return '-'
  const n = Number(val)
  if (Number.isNaN(n)) return '-'
  return n > 0 ? `+${n}` : String(n)
}

function getProcStatusClass(status) {
  switch ((status || '').toLowerCase()) {
    case 'completed': return 'status-approved'
    case 'approved': return 'status-approved'
    case 'pending': return 'status-pending'
    default: return 'status-pending'
  }
}

function formatProcStatus(status, budgetApproved) {
  if (budgetApproved) return 'BUDGET APPROVED'
  return (status || '').toUpperCase()
}

function formatPricingType(type) {
  const typeMap = {
    'individual': 'Individual',
    'per_pack': 'Per Pack',
    'both': 'Both'
  }
  return typeMap[type] || type
}

function formatDateTime(dt) {
  if (!dt) return ''
  try {
    const d = new Date(dt)
    if (isNaN(d.getTime())) return ''
    return d.toLocaleString('en-PH', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  } catch (e) {
    return ''
  }
}

async function placeOrder(product) {
  if (!product || !product.id || placingOrderIds.value[product.id]) return

  // set placing flag for this product
  setPlacingFlag(product.id, true)

  try {
    // Note: Quantity is locked to what logistics requested and cannot be changed by procurement
    // Product should always have supplier_id by this point (set during acknowledge with supplier selection)
    const payload = {}
    if (product.supplier_id) payload.supplier_id = product.supplier_id

    // Use manager procurement endpoint which creates the SupplierOrder record
    const res = await axios.post(`/api/manager/procurement/products/${product.id}/place-order`, payload, { withCredentials: true })

    // Handle response and update local UI immediately
    const supplierOrder = res.data.supplier_order
    const procReq = res.data.procurement_request

    if (res.data.message?.includes('already placed')) {
      alert(res.data.message)
    } else {
      alert(res.data.message || 'Order placed successfully')
    }

    setOrderPlacedFlag(product.id, true)

    // Optimistically update local product entries so the Place Order button hides
    try {
      // Update products list
      const idx = products.value.findIndex(p => p.id === product.id)
      if (idx > -1) {
        if (supplierOrder) products.value[idx].existingOrder = supplierOrder
        if (procReq && procReq.status) {
          products.value[idx].procurement_status = procReq.status
          products.value[idx].status = procReq.status
        }
      }

      // Update requestedProducts list (if present)
      const ridx = requestedProducts.value.findIndex(p => p.id === product.id)
      if (ridx > -1) {
        if (supplierOrder) requestedProducts.value[ridx].existingOrder = supplierOrder
        if (procReq && procReq.status) {
          requestedProducts.value[ridx].procurement_status = procReq.status
          requestedProducts.value[ridx].status = procReq.status
        }
      }
    } catch (e) {
      // ignore local update failures
    }

    // Refresh lists to ensure server canonical state (non-blocking)
    await loadProducts()
    await loadRequestedProducts()
    await refreshAllData()
  } catch (e) {
    console.error('Place order failed', e)
    alert(e.response?.data?.error || e.response?.data?.message || 'Failed to place order')
  } finally {
    // clear placing flag
    setPlacingFlag(product.id, false)
  }
}

// Receipt / completion workflow:
// Supplier must upload a photo of the physical receipt. The receipt is posted
// to the same complete endpoint as multipart/form-data. Finance must confirm
// the receipt on the backend before status becomes 'on_delivery'.
const showReceiptModal = ref(false)
const receiptFile = ref(null)
const receiptPreview = ref(null)
const receiptError = ref('')
const receiptUploading = ref(false)
const receiptPreviewProduct = ref(null)

function openReceiptModal(product) {
  if (!product || !product.procurement_request_id) return
  receiptPreviewProduct.value = product
  receiptFile.value = null
  receiptPreview.value = null
  receiptError.value = ''
  showReceiptModal.value = true
}

function closeReceiptModal() {
  if (receiptUploading.value) return
  showReceiptModal.value = false
  receiptFile.value = null
  receiptPreview.value = null
  receiptError.value = ''
  receiptPreviewProduct.value = null
}

function onReceiptSelected(e) {
  const f = (e.target && e.target.files && e.target.files[0]) || null
  if (!f) { receiptFile.value = null; receiptPreview.value = null; return }
  // Basic client-side check
  if (!f.type.startsWith('image/')) { receiptError.value = 'Please select an image file.'; receiptFile.value = null; return }
  receiptFile.value = f
  receiptError.value = ''
  const reader = new FileReader()
  reader.onload = (ev) => { receiptPreview.value = ev.target.result }
  reader.readAsDataURL(f)
}

async function submitReceipt() {
  if (receiptUploading.value) return
  if (!receiptPreviewProduct.value || !receiptPreviewProduct.value.procurement_request_id) return
  if (!receiptFile.value) { receiptError.value = 'Receipt image is required.'; return }
  receiptUploading.value = true
  // mark this procurement id as completing
  const rid = receiptPreviewProduct.value.procurement_request_id
  setCompletingFlag(rid, true)
  receiptError.value = ''
  try {
    const id = receiptPreviewProduct.value.procurement_request_id
    const fd = new FormData()
    fd.append('receipt', receiptFile.value)
    // optional: include note identifying supplier user
    const res = await axios.post(`/api/procurement-requests/${id}/complete`, fd, { headers: { 'Content-Type': 'multipart/form-data' }, withCredentials: true })
    alert(res.data?.message || 'Receipt submitted. Awaiting finance confirmation.')

    // Update local item and list entries to indicate receipt submitted and awaiting finance
    try {
      const statusVal = res.data?.procurement_status || 'receipt_submitted'
      const orderStatusVal = res.data?.order_status || 'receipt_submitted'
      const prod = receiptPreviewProduct.value
      prod.procurement_status = statusVal
      prod.status = statusVal
      prod.receipt_confirmed = false
      setReceiptPendingFlag(prod.id, true)
      if (prod.existingOrder) {
        prod.existingOrder.status = orderStatusVal
        prod.existingOrder.receipt_confirmed = false
      }

      // sync in products list
      const pIdx = products.value.findIndex(p => p.id === prod.id)
      if (pIdx > -1) {
        products.value[pIdx].procurement_status = statusVal
        products.value[pIdx].status = statusVal
        products.value[pIdx].receipt_confirmed = false
        setReceiptPendingFlag(products.value[pIdx].id, true)
        if (products.value[pIdx].existingOrder) {
          products.value[pIdx].existingOrder.status = orderStatusVal
          products.value[pIdx].existingOrder.receipt_confirmed = false
        }
      }

      // sync in requestedProducts list
      const rIdx = requestedProducts.value.findIndex(p => p.id === prod.id)
      if (rIdx > -1) {
        requestedProducts.value[rIdx].procurement_status = statusVal
        requestedProducts.value[rIdx].status = statusVal
        requestedProducts.value[rIdx].receipt_confirmed = false
        setReceiptPendingFlag(requestedProducts.value[rIdx].id, true)
        if (requestedProducts.value[rIdx].existingOrder) {
          requestedProducts.value[rIdx].existingOrder.status = orderStatusVal
          requestedProducts.value[rIdx].existingOrder.receipt_confirmed = false
        }
      }
    } catch (e) { /* ignore local update failures */ }
    await loadRequestedProducts()
    await loadProducts()
    await refreshAllData()
  } catch (e) {
    console.error('Receipt upload failed', e)
    receiptError.value = e.response?.data?.message || 'Failed to upload receipt'
    alert(receiptError.value)
  } finally {
    receiptUploading.value = false
    // ensure we clear the flag for the request id we captured earlier
    try {
      if (typeof rid !== 'undefined' && rid !== null) setCompletingFlag(rid, false)
    } catch (err) {
      // ignore
    }
    // Close modal after attempt so UI does not stay open
    closeReceiptModal()
  }
}

async function markDeliveryComplete(product) {
  if (!product || !product.procurement_request_id) return
  
  // Check if receipt is required but not yet uploaded or confirmed
  const needsReceipt = !product.receipt_confirmed && 
                       product.procurement_status !== 'receipt_confirmed' &&
                       (!product.existingOrder || !product.existingOrder.receipt_confirmed)
  
  if (needsReceipt) {
    // Open receipt modal instead of proceeding to completion
    openReceiptModal(product)
    return
  }
  
  if (!(await window.swalConfirm(`Mark delivery complete for ${product.name || 'this item'}? This will set the request as completed.`))) return
  const rid = product.procurement_request_id
  if (completingDeliveryIds.value[rid]) return
  setCompletingFlag(rid, true)
  try {
    const id = product.procurement_request_id
    const payload = {}
    const res = await axios.post(`/api/procurement-requests/${id}/complete`, payload, { withCredentials: true })
    alert(res.data?.message || 'Procurement request marked completed')
    await loadRequestedProducts()
    await loadProducts()
    await refreshAllData()
  } catch (e) {
    console.error('Mark delivery complete failed', e)
    alert(e.response?.data?.error || e.response?.data?.message || 'Failed to mark delivery complete')
  } finally {
    setCompletingFlag(rid, false)
  }
}

// Listen for global receiptConfirmed events (dispatched by Finance panel) and refresh lists
function onReceiptConfirmed(e) {
  try {
    loadRequestedProducts()
    loadProducts()
    refreshAllData()
  } catch (err) { console.warn('onReceiptConfirmed handler failed', err) }
}

window.addEventListener('receiptConfirmed', onReceiptConfirmed)

onUnmounted(() => {
  try { window.removeEventListener('receiptConfirmed', onReceiptConfirmed) } catch (e) { /* ignore */ }
})

</script>

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

.hr-stat-card {
  position: relative;
}

.requested-products h2 {
  position: relative;
  display: inline-block;
}
/* Use StaffIndex theme tokens for this panel to match color, font, and UI */
:deep(.admin-page) {
  background: var(--bg-main) !important;
  color: var(--text-dark) !important;
  font-family: 'Inter', 'Poppins', sans-serif !important;
}

:deep(.admin-layout) {
  background: transparent !important;
  border-radius: 12px !important;
  padding: 20px !important;
  box-shadow: 0 8px 24px rgba(16,24,40,0.06) !important;
}

/* When the left profile column is hidden for procurement, make the main area wider
   and keep the announcements/side panel at a comfortable width. */
:deep(.admin-layout.no-profile-column) {
  display: grid;
  grid-template-columns: 1fr 360px;
  gap: 1rem;
}

:deep(.admin-layout.no-profile-column) .admin-main { width: 100%; }
:deep(.admin-layout.no-profile-column) .admin-side { width: 360px; }

/* Make internal cards follow the same surface / dirty-white look */
.hr-stat-card,
.product-card,
.modal,
.password-display-card {
  background: var(--surface-card) !important;
  color: var(--text-dark) !important;
  border: 1px solid var(--border-stroke) !important;
}

.hr-stat-card { box-shadow: 0 8px 24px rgba(16,24,40,0.06); border-radius: 12px; }
.product-card { box-shadow: 0 10px 20px rgba(0,0,0,0.08); border-radius: 10px; }
.modal { box-shadow: 0 18px 40px rgba(0,0,0,0.12); }

/* Buttons use same rounded 'pill' visual for primary actions */
.modal-footer .btn-primary,
.btn-primary {
  background: var(--dirty-white);
  color: var(--text-dark);
  border: none;
  border-radius: 999px;
  box-shadow: 0 10px 20px rgba(0,0,0,0.08);
}

/* Ensure inputs and labels use shared text color */
.modal-body label,
.modal-body input,
.form-group label,
.product-meta,
.password-display-label {
  color: var(--text-dark);
  font-family: 'Inter', 'Poppins', sans-serif;
}

/* Slight layout tweak so the panel feels less tight inside admin wrapper */
:deep(.admin-page) > .admin-layout {
  gap: 1rem;
}

/* Reuse styles from HR panel; keep minimal overrides */
.hr-stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; align-items: start; }
.hr-stat-card { background: white; border-radius: 8px; padding: 1rem; display:flex; gap:0.75rem; align-items:center; color: #1b1b1f; }
.hr-stat-value { font-weight:700; font-size:1.25rem; }

/* Procurement panel layout improvements */
/* Align header actions and profile neatly */
.admin-main-header .admin-main-header-top { align-items: center; gap: 1rem; }
.header-actions-top { display:flex; align-items:center; gap:12px; }

/* Make stat cards consistent height and spacing */
.hr-stats-grid { align-items: stretch; }
.hr-stat-card { min-height: 84px; display:flex; align-items:center; padding: 1rem; }

/* Slightly reduce vertical spacing of sections to compact layout */
.panel-section { margin-top: 0.75rem; }

/* Narrow the right-side column slightly for this panel for better balance */
.admin-side { width: 300px; }

/* Ensure announcements card fits nicely inside the right column */
.announcements-panel { max-width: 100%; }

/* Modal overrides for better contrast and layout inside this panel */
.modal {
  background: #ffffff;
  color: #1b1b1f;
  border-radius: 12px;
  width: 92%;
  max-width: 720px;
  margin: 0 12px;
  box-shadow: 0 18px 40px rgba(0,0,0,0.35);
  z-index: 100101; /* ensure modal floats above sticky table headers */
}

.modal-card { overflow: hidden; }

.modal-header h3 {
  margin: 0;
  font-size: 1.1rem;
  color: #1b1b1f;
}

.modal-body {
  padding: 1rem 1.25rem;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.75rem;
}
.modal-body .form-group { display: flex; flex-direction: column; gap: 6px; }
.modal-body .form-group.full-span { grid-column: 1 / -1; }
.modal-body label { color: #333; font-size: 0.85rem; }
.modal-body input { padding: 8px 10px; border-radius: 8px; border: 1px solid #ddd; background: #fff; color: #111; }

.error-msg { color: #a33; grid-column: 1 / -1; padding-top: 6px; }
.success-msg { color: #167a3e; grid-column: 1 / -1; padding-top: 6px; }

.modal-footer { padding: 10px 14px; display:flex; justify-content:flex-end; gap:0.5rem; background: #fafafa; }
.modal-footer .btn-outline { background: transparent; border: 1px solid #ccc; color: #333; }

/* Requests history table tweaks */
.requests-history .data-table th,
.requests-history .data-table td { padding: 10px 12px; }
.requests-history .data-table td.amount { text-align: right; white-space: nowrap; font-weight:600 }
.requests-history .product-name { white-space: normal; word-break: break-word; max-width: 420px }
.modal-footer .btn-primary { background: #4b1ddf; color: #fff; }

/* Requests History: outer card allows overflow; inner element handles scrolling */
.requests-container {
  overflow: visible; /* allow popovers/expanded info to escape rounded corners */
  background: var(--surface-card);
  padding: 0; /* inner scrollable area will include padding */
  border-radius: 10px;
  border: 1px solid var(--border-stroke);
}

.requests-scroll {
  max-height: 320px;
  overflow-y: auto;
  padding: 0 12px 12px 12px; /* keep previous padding inside scroll area */
  width: 100%;
  box-sizing: border-box;
  border-radius: 10px;
  box-shadow: 0 8px 20px rgba(0,0,0,0.04);
  background: transparent;
}
.requests-scroll .data-table { margin: 0; }
.requests-scroll .data-table thead th {
  position: sticky;
  top: 0;
  background: var(--dirty-white);
  z-index: 2;
  box-shadow: 0 2px 6px rgba(0,0,0,0.04);
}

/* Password Display Styles */
.password-display-container {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.password-display-card {
  background: linear-gradient(135deg, #fef3e2 0%, #fde8d4 100%);
  border: 2px solid #ff9a56;
  border-radius: 10px;
  padding: 1.25rem;
}

.password-display-label {
  font-size: 0.85rem;
  font-weight: 600;
  color: #92400e;
  margin-bottom: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.password-display-value {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex-wrap: wrap;
}

.password-text {
  font-family: 'Courier New', monospace;
  font-size: 1.25rem;
  font-weight: 700;
  color: #1f2937;
  background: #fff;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  border: 1px solid #d1d5db;
  letter-spacing: 1px;
}

.btn-copy {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  font-size: 0.9rem;
  white-space: nowrap;
  background: #4b1ddf;
  color: #fff;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}

.password-display-card .form-hint {
  margin-top: 0.75rem;
  font-size: 0.85rem;
  color: #92400e;
}

.password-loading {
  display: flex;
  align-items: center;
  padding: 0.5rem;
}

/* Ensure backdrop has high z-index inside component scope */
.modal-backdrop { z-index: 100100; }

/* Product grid styles for supplier products */
.product-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 0.75rem;
  margin-top: 0.75rem;
}

.product-card {
  background: #ffffff;
  border-radius: 10px;
  padding: 0.9rem;
  box-shadow: 0 6px 18px rgba(15,23,42,0.06);
  border: 1px solid #eef2f6;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  justify-content: space-between;
  min-height: 110px;
}

.product-name { font-weight: 700; color: #111827; font-size: 1rem }
.product-meta { display:flex; justify-content:space-between; align-items:center; gap:0.5rem; margin-top: 0.5rem }
.product-price { color: #0b6e3a; font-weight:700 }
.supplier-badge { background: #f3f4f6; color: #374151; padding: 4px 8px; border-radius: 12px; font-size: 0.85rem; margin-top: 0.5rem }
.product-type-badge { display: inline-block; padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 600; margin-top: 4px }
.product-type-badge.type-individual { background: #dbeafe; color: #1e40af; }
.product-type-badge.type-per_pack { background: #d1fae5; color: #065f46; }
.product-type-badge.type-both { background: #fef3c7; color: #92400e; }

/* Ensure cards stretch to same height in grid */
.product-grid > .product-card { height: 100%; }

/* Utility spacing classes */
.mt-1 { margin-top: 1rem; }
.mt-sm { margin-top: 8px; }
.mb-1 { margin-bottom: 1rem; }
.no-margin { margin: 0; }

/* Layout helpers */
.inline-row { display:flex; }
.gap-sm { gap: 0.5rem; }
.align-center { align-items: center; }

.section-subtitle { margin: 0 0 8px 0; font-size: 1rem; font-weight: 700; }

/* Budget form specific styles */
.budget-form { display: block; background: transparent; padding: 8px 0; }
.budget-form .form-row { display: flex; gap: 12px; align-items: end; flex-wrap:wrap }
.budget-form .form-group.amount { width: 260px; display:flex; flex-direction:column }
.budget-form .form-group.amount label { font-size:0.9rem }
.budget-form .input-amount { padding:8px 10px; border-radius:8px; border:1px solid #ddd; width:100%; max-width:260px }
.budget-form .form-actions { display:flex; align-items:center }
.btn-budget { background: linear-gradient(180deg,#ff781a,#ff5a00); color: #fff; border: none; padding: 8px 14px; border-radius: 999px; box-shadow: 0 8px 18px rgba(255,90,0,0.18); cursor:pointer }
.btn-budget:disabled { opacity:0.6; cursor:default }
.btn-outline { background: #f0f0f0; color: #333; border-radius: 999px; padding: 6px 10px }

/* Improved budget form grid and controls */
.budget-form .form-grid { display: grid; grid-template-columns: 140px 1fr; gap: 10px 18px; align-items: start; max-width: 760px }
.budget-form .form-label { color: #4b1d1d; font-weight:700; padding-top:6px }
.budget-form textarea { width:100%; min-height:68px; max-width:100%; padding:8px 10px; border-radius:8px; border:1px solid #e6e6e6; background:#fff }
.budget-form .inline-controls { display:flex; gap:12px; align-items:center }
.budget-form .amount-input { display:flex; align-items:center; gap:6px; background:#fff; border:1px solid #eee; padding:6px 8px; border-radius:8px }
.budget-form .amount-input .currency { color:#0b6e3a; font-weight:700 }
.budget-form .amount-input input { border: none; outline: none; width:120px; font-weight:700 }
.budget-form .form-field { display:flex; flex-direction:column }
.budget-form .btn-budget { padding: 8px 12px; height:40px }
.budget-form .btn-outline { margin-bottom: 8px }


/* Button variants */
.btn-small { padding: 6px 10px; border-radius: 8px; font-size: 0.95rem; border: 1px solid var(--border-stroke); background: var(--surface-card); color: var(--text-dark); cursor: pointer; }
.btn-small:focus { outline: 3px solid rgba(3,37,65,0.08); }
.btn-refresh {
  padding: 6px 12px;
  font-size: 0.85rem;
  border-radius: 8px;
  border: none;
  background: transparent;
  box-shadow: none;
}
.btn-refresh:focus { outline: none; box-shadow: none; }
.btn-primary { background: var(--dirty-white); color: var(--text-dark); border: none; border-radius: 999px; padding: 8px 14px; box-shadow: 0 8px 18px rgba(0,0,0,0.06); cursor: pointer; font-weight:600; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-outline { background: transparent; border: 1px solid var(--border-stroke); color: var(--text-dark); padding: 8px 12px; border-radius: 8px; cursor: pointer; }
.btn-outline[disabled], .btn-outline.disabled { opacity: 0.6; cursor: not-allowed; }
.btn-warning { background: var(--orange); color: var(--dirty-white); border: none; box-shadow: 0 8px 18px rgba(255,107,28,0.12); }

/* Status badge variants */
.status-badge.status-warning { background: #fbbf24; color: #92400e; padding:6px 10px; border-radius:8px; font-size:0.9rem; font-weight:600; }

/* Table polish */
.data-table { width: 100%; border-collapse: collapse; font-size: 0.95rem; }
.data-table thead th { background: var(--dirty-white); position: sticky; top: 0; z-index: 2; }
.data-table tbody tr:nth-child(odd) { background: rgba(3,37,65,0.02); }
.data-table th, .data-table td { padding: 8px 10px; }

/* Requests History: make columns equal and aligned */
.requests-scroll .data-table { table-layout: fixed; width: 100%; }
.requests-scroll .data-table thead th { text-align: left; font-weight: 700; color: #5b4030; }
.requests-scroll .data-table tbody td { vertical-align: middle; color: #423022; }
.requests-scroll .data-table th:nth-child(1), .requests-scroll .data-table td:nth-child(1) { width: 18%; }
.requests-scroll .data-table th:nth-child(2), .requests-scroll .data-table td:nth-child(2) { width: 30%; }
.requests-scroll .data-table th:nth-child(3), .requests-scroll .data-table td:nth-child(3) { width: 6%; text-align: center; }
.requests-scroll .data-table th:nth-child(4), .requests-scroll .data-table td:nth-child(4) { width: 12%; text-align: right; }
.requests-scroll .data-table th:nth-child(5), .requests-scroll .data-table td:nth-child(5) { width: 18%; text-align: left; }
.requests-scroll .data-table th:nth-child(6), .requests-scroll .data-table td:nth-child(6) { width: 16%; text-align: left; }
.requests-scroll .data-table td.product-name { font-weight: 700; color: #1f2937; }
.requests-scroll .data-table td.amount { text-align: right; font-weight:700 }
.requests-scroll .data-table td, .requests-scroll .data-table th { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

/* Ensure the sticky header spans full width and matches body cells */
.requests-scroll .data-table thead th { padding-top: 12px; padding-bottom: 12px; }
.requests-scroll::-webkit-scrollbar { width: 12px; height: 12px; }

/* Scrollbar styling - warm beige/orange theme to match panel */
.requests-scroll::-webkit-scrollbar-track {
  background: linear-gradient(180deg, rgba(247,242,239,1), rgba(255,255,255,1));
  border-radius: 999px;
  margin: 8px 0;
}
.requests-scroll::-webkit-scrollbar-thumb {
  background: linear-gradient(180deg, #ffb07a, #ff7a2c);
  border-radius: 999px;
  border: 2px solid rgba(255,255,255,0.9);
  box-shadow: 0 2px 6px rgba(0,0,0,0.08);
}
.requests-scroll::-webkit-scrollbar-thumb:hover { background: linear-gradient(180deg, #ff964d, #ff6511); }

/* Firefox scrollbar support */
.requests-scroll { scrollbar-width: thin; scrollbar-color: #ff7a2c rgba(247,242,239,1); }

/* Apply same styling for supplier list and other internal scrollable areas */
.supplier-list-scroll::-webkit-scrollbar { width: 10px; }
.supplier-list-scroll::-webkit-scrollbar-track { background: rgba(250,247,244,1); border-radius: 999px; }
.supplier-list-scroll::-webkit-scrollbar-thumb { background: linear-gradient(180deg,#ffb07a,#ff7a2c); border-radius: 999px; border: 2px solid rgba(255,255,255,0.9); }
.supplier-list-scroll { scrollbar-width: thin; scrollbar-color: #ff7a2c rgba(250,247,244,1); }

/* Stat cards consistent */
.hr-stat-card { min-height: 88px; display:flex; align-items:center; gap:0.75rem; padding: 1rem; }

/* Accessibility focus */
button:focus, a:focus, input:focus, select:focus { outline: 3px solid rgba(3,37,65,0.08); outline-offset: 2px; }

/* Small utility and component tweaks */
.muted { color: #6b7280; }
.small-text { font-size: 0.9rem; }
.receipt-preview { max-width: 100%; border-radius: 8px; border: 1px solid #e5e7eb; }
.form-note { font-size: 0.9rem; color: #374151; grid-column: 1 / -1; margin-top: 6px; }
.supplier-list-scroll { max-height: 260px; overflow: auto; }
.supplier-row { display:flex; align-items:center; gap:0.5rem; padding:6px 0; }
.note-warning { margin-top:6px; color:#92400e; font-weight:600; font-size:0.9rem }

/* Estimated delivery info display */
.estimated-delivery-info {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  margin-top: 8px;
  padding: 6px 10px;
  background: #fffbeb;
  border: 1px solid #f59e0b;
  border-radius: 6px;
  font-size: 0.85rem;
}

.estimated-delivery-label {
  font-weight: 600;
  color: #92400e;
}

.estimated-delivery-value {
  font-weight: 700;
  color: #78350f;
}

/* Header profile dropdown styles (procurement panel only) */
.header-profile-wrapper { position:relative; display:flex; align-items:center }
.header-profile-btn { display:flex; gap:8px; align-items:center; background:transparent; border:none; cursor:pointer; padding:6px 8px; border-radius:8px }
.header-avatar { width:36px; height:36px; border-radius:50%; overflow:hidden; display:flex; align-items:center; justify-content:center; background:#f3f4f6 }
.header-avatar-img { width:100%; height:100%; background-size:cover; background-position:center }
.header-avatar-initials { font-weight:700; color:#374151 }
.header-name { font-weight:700; color:#333; font-size:0.86rem }
.header-profile-dropdown { position:absolute; right:0; top:46px; background:#fff; border-radius:8px; box-shadow:0 8px 24px rgba(16,24,40,0.12); padding:6px; min-width:160px; z-index:100200 }
.dropdown-item { display:block; width:100%; text-align:left; padding:8px 12px; background:transparent; border:none; color:#374151; cursor:pointer }
.dropdown-item:hover { background:#f7f7f8 }

/* Supplier selection modal styles */
.supplier-label { display:flex; flex-direction:column; gap:0.25rem; align-items:flex-start; cursor:pointer; flex:1 }
.supplier-name { font-weight:700; color:#111827; font-size:0.95rem }
.supplier-contact { font-size:0.85rem; color:#6b7280 }
.supplier-details { display:flex; flex-direction:column; gap:0.25rem; margin-top:0.5rem; padding-top:0.5rem; border-top:1px solid #e5e7eb; width:100% }
.detail-line { font-size:0.8rem; color:#374151; margin:0; padding:0 }

/* Hide the small account ID in the left profile column for Procurement panel
   — the account ID will be visible inside the Info modal only. */
:deep(.admin-profile-column .admin-id-block) { display: none !important }

/* Info modal: procurement-specific visual refresh (colors, font, spacing) */
:deep(.info-modal) {
  max-width: 520px;
  background: #ffffff;
  color: #1f2937;
  border-radius: 12px;
  padding: 18px;
  box-shadow: 0 18px 40px rgba(3,37,65,0.08);
  font-family: 'Inter', 'Poppins', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;
}
:deep(.info-modal h3) { margin:0; font-size:1.05rem; color:#111827; font-weight:700 }
:deep(.info-modal .info-sub) { color:#6b7280; margin-bottom:12px }
:deep(.info-modal .info-grid) { display:flex; flex-direction:column; gap:10px }
:deep(.info-modal .info-row) { display:flex; justify-content:space-between; align-items:center; gap:8px; padding:8px 0; border-bottom: 1px solid #f3f4f6 }
:deep(.info-modal .info-row:last-child) { border-bottom: none }
:deep(.info-modal .info-label) { color:#6b7280; font-size:0.9rem; font-weight:600 }
:deep(.info-modal .info-value) { color:#111827; font-size:0.95rem; font-weight:700 }
</style>
