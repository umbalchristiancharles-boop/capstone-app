#!/bin/bash

# JWT Cross-Domain Authentication Deployment Script
# Run this after deploying your staff and customer frontends on separate domains

echo "=== JWT Cross-Domain Authentication Setup ==="
echo ""

# Step 1: Update CORS Configuration
echo "Step 1: Updating CORS configuration..."
echo "Edit config/cors.php and add your production domains:"
echo ""
echo "  'allowed_origins' => ["
echo "      'https://staff.yourdomain.com',"
echo "      'https://customer.yourdomain.com',"
echo "      'https://api.yourdomain.com',"
echo "  ],"
echo ""
read -p "Press Enter after updating config/cors.php..."

# Step 2: Clear Laravel cache
echo ""
echo "Step 2: Clearing Laravel configuration cache..."
php artisan config:clear
php artisan config:cache

# Step 3: Test JWT endpoints
echo ""
echo "Step 3: Testing JWT endpoints..."
echo ""
echo "Test login endpoint:"
echo "  curl -X POST https://api.yourdomain.com/api/jwt/login \\"
echo "    -H 'Content-Type: application/json' \\"
echo "    -d '{\"username\":\"testuser\",\"password\":\"password\"}'"
echo ""

# Step 4: Environment setup
echo ""
echo "Step 4: Environment checklist:"
echo ""
echo "  [ ] All domains configured with HTTPS/SSL"
echo "  [ ] .env APP_ENV=production"
echo "  [ ] .env APP_DEBUG=false"
echo "  [ ] CORS origins in config/cors.php"
echo "  [ ] Rate limiting configured on /api/jwt/login"
echo "  [ ] Database migrations up to date"
echo ""

echo "=== Setup Complete ==="
echo ""
echo "Frontend Implementation:"
echo "  1. Copy JWT auth service code from JWT_CROSS_DOMAIN_DEPLOYMENT.md"
echo "  2. Update API_URL to your production domain:"
echo "     const API_URL = 'https://api.yourdomain.com/api';"
echo "  3. Test login from both staff and customer frontends"
echo "  4. Verify refresh token flow when access token expires"
