# JWT Cross-Domain Authentication Deployment Guide

## Overview
This system uses JWT (JSON Web Tokens) for cross-domain authentication, allowing staff and customer frontends on separate domains to access a shared API backend.

## Architecture

```
┌─────────────────────────────────────────┐
│     API Backend (api.yourdomain.com)    │
│     - JWT Token Endpoints                 │
│     - Protected API Routes                │
└─────────────────────────────────────────┘
          ↑                      ↑
          │                      │
   Bearer Token            Bearer Token
    (Access Token)         (Access Token)
          │                      │
┌─────────────────┐       ┌──────────────────┐
│ Staff Frontend  │       │ Customer Frontend│
│ (staff.you...)  │       │ (customer.you..) │
└─────────────────┘       └──────────────────┘
```

## Setup for Deployment

### 1. Update .env with Your Domains

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.yourdomain.com

# Add any domain-specific configuration
STAFF_DOMAIN=https://staff.yourdomain.com
CUSTOMER_DOMAIN=https://customer.yourdomain.com
```

### 2. Update CORS Configuration

Edit `config/cors.php` and add your production domains:

```php
'allowed_origins' => [
    'https://staff.yourdomain.com',
    'https://customer.yourdomain.com',
    'https://api.yourdomain.com',
    // ... keep dev origins if needed
],
```

### 3. HTTPS Required for Production

- All domains **must use HTTPS** in production
- Tokens are vulnerable over HTTP
- Use Let's Encrypt for free SSL certificates

## API Endpoints

### POST /api/jwt/login
Issue tokens for a user.

**Request:**
```json
POST https://api.yourdomain.com/api/jwt/login
Content-Type: application/json

{
  "username": "user123",
  "password": "password123"
}
```

**Response (Success):**
```json
{
  "access_token": "6|Cw7xbSzYp...",
  "refresh_token": "7|Kz9mLp2Aq...",
  "token_type": "Bearer",
  "expires_in": 900,
  "user": {
    "id": 1,
    "username": "user123",
    "email": "user@example.com",
    "role": "STAFF",
    "branch_id": 1,
    "full_name": "John Doe"
  }
}
```

**Response (Error):**
```json
{
  "error": "Invalid username or password"
}
```

---

### POST /api/jwt/refresh
Get a new access token using a refresh token.

**Request:**
```json
POST https://api.yourdomain.com/api/jwt/refresh
Content-Type: application/json

{
  "refresh_token": "7|Kz9mLp2Aq..."
}
```

**Response:**
```json
{
  "access_token": "6|NewAccessToken...",
  "refresh_token": "7|Kz9mLp2Aq...",
  "token_type": "Bearer",
  "expires_in": 900,
  "user": { ... }
}
```

---

### GET /api/jwt/me
Get current user info (requires valid access token).

**Request:**
```
GET https://api.yourdomain.com/api/jwt/me
Authorization: Bearer 6|Cw7xbSzYp...
```

**Response:**
```json
{
  "id": 1,
  "username": "user123",
  "email": "user@example.com",
  "full_name": "John Doe",
  "role": "STAFF",
  "department": "INVENTORY",
  "branch_id": 1,
  "avatar_url": "..."
}
```

---

### POST /api/jwt/logout
Revoke a refresh token.

**Request:**
```json
POST https://api.yourdomain.com/api/jwt/logout
Content-Type: application/json

{
  "refresh_token": "7|Kz9mLp2Aq..."
}
```

---

### POST /api/jwt/logout-all
Revoke all tokens for current user (requires access token).

**Request:**
```
POST https://api.yourdomain.com/api/jwt/logout-all
Authorization: Bearer 6|Cw7xbSzYp...
```

## Frontend Implementation

### Token Storage Strategy

**Access Token:**
- Short-lived (15 minutes)
- Store in **memory** or secure HTTP-only cookie
- Send in `Authorization: Bearer` header

**Refresh Token:**
- Long-lived (7 days)
- Store in **HTTP-only Secure cookie** OR in localStorage (if JS access is acceptable)
- Never expose in JavaScript (use HTTP-only cookies if possible)

---

## Example: Vue Composition API (Staff Domain)

**`src/services/authService.js`**
```javascript
import axios from 'axios';

const API_URL = 'https://api.yourdomain.com/api';

// In-memory storage for access token (short-lived)
let accessToken = null;

// Create axios instance with credentials support
const api = axios.create({
  baseURL: API_URL,
  withCredentials: true, // Send/receive cookies
});

// Add token to every request
api.interceptors.request.use((config) => {
  if (accessToken) {
    config.headers.Authorization = `Bearer ${accessToken}`;
  }
  return config;
});

// Handle token expiry and refresh
api.interceptors.response.use(
  (response) => response,
  async (error) => {
    const originalRequest = error.config;

    // If 401 and not already retried, try refreshing token
    if (error.response?.status === 401 && !originalRequest._retry) {
      originalRequest._retry = true;

      try {
        const refreshToken = localStorage.getItem('refresh_token');
        if (!refreshToken) {
          throw new Error('No refresh token');
        }

        const response = await axios.post(`${API_URL}/jwt/refresh`, {
          refresh_token: refreshToken,
        });

        accessToken = response.data.access_token;
        localStorage.setItem('refresh_token', response.data.refresh_token);

        // Retry original request
        originalRequest.headers.Authorization = `Bearer ${accessToken}`;
        return api(originalRequest);
      } catch (refreshError) {
        // Refresh failed, logout user
        logout();
        throw refreshError;
      }
    }

    return Promise.reject(error);
  }
);

export const authService = {
  async login(username, password) {
    const response = await api.post('/jwt/login', { username, password });
    
    accessToken = response.data.access_token;
    localStorage.setItem('refresh_token', response.data.refresh_token);
    
    return response.data.user;
  },

  async logout() {
    const refreshToken = localStorage.getItem('refresh_token');
    if (refreshToken) {
      try {
        await api.post('/jwt/logout', { refresh_token: refreshToken });
      } catch (error) {
        console.error('Logout error:', error);
      }
    }
    
    accessToken = null;
    localStorage.removeItem('refresh_token');
  },

  async getCurrentUser() {
    try {
      const response = await api.get('/jwt/me');
      return response.data;
    } catch (error) {
      accessToken = null;
      throw error;
    }
  },

  isAuthenticated() {
    return !!accessToken;
  },

  getAccessToken() {
    return accessToken;
  },
};

export default api;
```

---

**`src/composables/useAuth.js`**
```javascript
import { ref, computed } from 'vue';
import { authService } from '@/services/authService';

export const useAuth = () => {
  const user = ref(null);
  const isLoading = ref(false);
  const error = ref(null);

  const isAuthenticated = computed(() => authService.isAuthenticated());

  const login = async (username, password) => {
    isLoading.value = true;
    error.value = null;
    try {
      user.value = await authService.login(username, password);
      return user.value;
    } catch (err) {
      error.value = err.response?.data?.error || 'Login failed';
      throw err;
    } finally {
      isLoading.value = false;
    }
  };

  const logout = async () => {
    try {
      await authService.logout();
      user.value = null;
    } catch (err) {
      console.error('Logout failed:', err);
    }
  };

  const fetchCurrentUser = async () => {
    try {
      user.value = await authService.getCurrentUser();
      return user.value;
    } catch (err) {
      user.value = null;
      throw err;
    }
  };

  return {
    user,
    isLoading,
    error,
    isAuthenticated,
    login,
    logout,
    fetchCurrentUser,
  };
};
```

---

**`src/views/LoginPage.vue`**
```vue
<template>
  <div class="login-container">
    <form @submit.prevent="handleLogin">
      <h1>Staff Login</h1>

      <div v-if="error" class="error-message">{{ error }}</div>

      <div class="form-group">
        <label>Username</label>
        <input
          v-model="username"
          type="text"
          required
          :disabled="isLoading"
        />
      </div>

      <div class="form-group">
        <label>Password</label>
        <input
          v-model="password"
          type="password"
          required
          :disabled="isLoading"
        />
      </div>

      <button type="submit" :disabled="isLoading">
        {{ isLoading ? 'Logging in...' : 'Login' }}
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuth } from '@/composables/useAuth';

const router = useRouter();
const { login, isLoading, error } = useAuth();

const username = ref('');
const password = ref('');

const handleLogin = async () => {
  try {
    await login(username.value, password.value);
    router.push('/dashboard');
  } catch (err) {
    // Error is handled in the composable
  }
};
</script>

<style scoped>
.login-container {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100vh;
}

form {
  width: 300px;
  padding: 2rem;
  border: 1px solid #ddd;
  border-radius: 8px;
}

.form-group {
  margin-bottom: 1rem;
}

label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: bold;
}

input {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #ddd;
  border-radius: 4px;
}

button {
  width: 100%;
  padding: 0.75rem;
  background: #007bff;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

button:disabled {
  background: #6c757d;
  cursor: not-allowed;
}

.error-message {
  color: #dc3545;
  margin-bottom: 1rem;
  padding: 0.75rem;
  background: #f8d7da;
  border-radius: 4px;
}
</style>
```

---

## Production Deployment Checklist

- [ ] Update `.env` with production domain URLs
- [ ] Update `config/cors.php` with production domains
- [ ] Configure HTTPS/SSL certificates on all domains
- [ ] Set `APP_ENV=production` and `APP_DEBUG=false`
- [ ] Cache Laravel configuration: `php artisan config:cache`
- [ ] Set strong session timeout on backend (15 minutes for access token)
- [ ] Test login flow from staff domain
- [ ] Test login flow from customer domain
- [ ] Test token refresh when access token expires
- [ ] Test logout and token revocation
- [ ] Monitor logs for authentication errors
- [ ] Set up rate limiting on JWT endpoints (prevent brute force)

---

## Security Notes

1. **HTTPS Only:** Never use HTTP for token transmission
2. **Token Expiry:** Access tokens expire in 15 minutes; refresh tokens in 7 days
3. **Secure Cookies:** Store refresh tokens as HTTP-only Secure cookies
4. **Same-Site Policy:** Set `SameSite=Lax` on refresh token cookies
5. **CORS Restrictions:** Never use `*` for allowed origins when credentials are needed
6. **Rate Limiting:** Implement rate limiting on `/api/jwt/login` to prevent brute force
7. **Token Rotation:** Refresh tokens are rotated on each refresh

---

## Troubleshooting

### "CORS policy: No 'Access-Control-Allow-Origin' header"
- Check that your frontend domain is in `config/cors.php` `allowed_origins`
- Restart Laravel after updating CORS config

### "Invalid or expired token"
- Access token has 15-minute expiry
- Use refresh endpoint to get a new access token
- Refresh token may be revoked (7-day expiry)

### Tokens work in dev but not in production
- Ensure all domains use HTTPS
- Check that `SameSite` and `Secure` flags are correct in production
- Verify domain names match exactly in CORS config

