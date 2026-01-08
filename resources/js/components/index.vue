<template>
  <main class="page">
    <section class="hero">
      <!-- Right block (card) -->
      <div class="hero-right">
        <div class="card">
          <div class="card-header">
            <div class="brand">
              <span class="brand-title">MR. PARK'S</span>
              <span class="brand-sub">Bread &amp; Cake Admin</span>
            </div>
            <span class="tag-pill">Live Orders</span>
          </div>

          <div class="cake-preview">
            <div class="cake-circle" aria-hidden="true"></div>
            <div class="cake-text">
              <h3>Heart Party Cake</h3>
              <p>Update stocks, promos, and seasonal flavors from this panel.</p>
            </div>
          </div>

          <div class="mini-stats">
            <span>Today’s orders<strong>42</strong></span>
            <span>Ready to ship<strong>18</strong></span>
            <span>Low stocks<strong>5</strong></span>
          </div>

          <p class="admin-label">
            <span>Admin Only</span>
          </p>
        </div>
      </div>

      <!-- Left content -->
      <div class="hero-left">
        <div class="badge">
          <span class="badge-dot"></span>
          <span>Admin Control Center</span>
        </div>

        <h1>
          <span class="highlight-korean">Your fun Korean</span>
          <span>Bakery Admin Bake Stop</span>
        </h1>

        <p class="subheading">
          Manage cakes, breads, and holiday specials from a single, playful
          dashboard styled just like the shop front.
        </p>

        <p class="admin-tagline">
          For <strong>authorized staff</strong> access only. Customers use a
          separate site.
        </p>

        <div class="cta-row">
          <!-- ADMIN LOGIN ONLY -->
          <RouterLink to="/admin-login" class="btn-login">
            <span class="icon">🔑</span>
            <span>Login to Admin Panel</span>
          </RouterLink>

          <span class="note">
            No sign up. Credentials are provided by the system administrator.
          </span>
        </div>
      </div>
    </section>

    <div class="village-line">
      <span class="house">🏠</span>
      <span>Freshly baked logs · Sweet reports · Warm order tracking</span>
      <span class="house">🏡</span>
    </div>
  </main>
</template>

<script setup>
// static landing page; logic can be added here if needed later
</script>

<!-- Option A: use src attribute so Vue CLI / Vite bundles the CSS with this component -->
<style src="./index.css"></style>
