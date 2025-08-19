# Appraisells PHP Deployment Checklist

Use this checklist when deploying to Spaceship hosting or any other web hosting provider.

## Pre-Deployment Setup

### 1. Domain & Hosting Setup
- [ ] Domain registered and pointed to hosting
- [ ] SSL certificate installed and active
- [ ] Hosting account configured with PHP 7.4+ and MySQL 5.7+
- [ ] File manager or FTP access confirmed

### 2. Pi Network Developer Setup
- [ ] Pi Developer Portal account created
- [ ] Application registered in Pi Developer Portal
- [ ] Pi API key obtained
- [ ] Domain added to allowed domains in Pi Developer Portal
- [ ] Sandbox/mainnet mode decided

### 3. Database Preparation
- [ ] MySQL database created via hosting control panel
- [ ] Database name, username, password noted
- [ ] Database host/server information obtained
- [ ] phpMyAdmin or similar database access confirmed

## File Configuration

### 1. Update config.php
```php
// Database Configuration
define('DB_HOST', 'your_mysql_host');        // ✓ Updated
define('DB_NAME', 'your_database_name');     // ✓ Updated  
define('DB_USER', 'your_db_username');       // ✓ Updated
define('DB_PASS', 'your_db_password');       // ✓ Updated

// Pi Network Configuration  
define('PI_API_KEY', 'your_pi_api_key');     // ✓ Updated

// Application Configuration
define('APP_URL', 'https://yourdomain.com'); // ✓ Updated
```

### 2. Update JavaScript Configuration
- [ ] Verify `script.js` uses `window.location.origin` for API calls
- [ ] Set correct sandbox mode in Pi SDK initialization
- [ ] Test that BACKEND_URL resolves correctly

### 3. Environment Variables (if supported)
- [ ] Set PI_API_KEY as environment variable (preferred)
- [ ] Configure other sensitive settings via environment

## File Upload

### 1. Upload Core Files
- [ ] index.html
- [ ] profile.html  
- [ ] liveAuction.html
- [ ] my-digital-art.html
- [ ] admin.html
- [ ] script.js
- [ ] style.css
- [ ] config.php
- [ ] api.php
- [ ] profile.php
- [ ] save_user.php
- [ ] .htaccess
- [ ] README.md

### 2. Upload Images
- [ ] images/middle finger man.png
- [ ] images/SueHipple1.jpg
- [ ] images/SueHipple2.jpg
- [ ] images/SueHipple3.jpg
- [ ] images/SueHipple4.jpg
- [ ] images/SueHipple5.jpg

### 3. Set File Permissions
- [ ] Files: 644 permissions
- [ ] Directories: 755 permissions
- [ ] Verify web server can read all files

## Database Setup

### 1. Import Schema
- [ ] Access phpMyAdmin or database management tool
- [ ] Import database_schema.sql
- [ ] Verify all tables created successfully:
  - [ ] user_profiles
  - [ ] user_activities  
  - [ ] payments
  - [ ] user_subscriptions
  - [ ] auction_bids
  - [ ] auction_winners
  - [ ] digital_art_delivery
  - [ ] sessions
  - [ ] auction_items

### 2. Verify Sample Data
- [ ] auction_items table has sample artwork data
- [ ] Indexes created properly
- [ ] Foreign key constraints working

## Testing & Verification

### 1. Health Check
- [ ] Visit: `https://yourdomain.com/api.php/health`
- [ ] Verify response shows:
  - [ ] Server running
  - [ ] Database connected: true
  - [ ] Pi API key set: true

### 2. Frontend Testing
- [ ] Visit main site: `https://yourdomain.com`
- [ ] Click "Login with Pi Browser"
- [ ] Verify redirect to profile page works
- [ ] Check browser console for JavaScript errors

### 3. Pi Authentication Testing
- [ ] Test in Pi Browser on mobile device
- [ ] Complete authentication flow
- [ ] Verify user profile creation in database
- [ ] Check authentication logging in user_activities table

### 4. Subscription Testing
- [ ] Complete authentication
- [ ] Click "Subscribe" button
- [ ] Test payment flow (use testnet Pi)
- [ ] Verify subscription creation in database
- [ ] Check subscription status display

### 5. Auction Testing
- [ ] Navigate to live auction page
- [ ] Verify auction items load
- [ ] Submit test bid
- [ ] Check bid storage in database
- [ ] Verify highest bid updates

### 6. Admin Dashboard Testing
- [ ] Visit: `https://yourdomain.com/admin.html`
- [ ] Verify all statistics load
- [ ] Test data refresh functions
- [ ] Try creating test subscription

## Security Verification

### 1. SSL/HTTPS
- [ ] Site loads over HTTPS
- [ ] SSL certificate valid
- [ ] HTTP redirects to HTTPS

### 2. File Security
- [ ] .htaccess file working
- [ ] Sensitive files blocked (.env, .sql, .log)
- [ ] CORS headers present in API responses

### 3. Input Validation
- [ ] Test SQL injection protection
- [ ] Verify input sanitization working
- [ ] Check error handling doesn't expose sensitive info

### 4. API Security
- [ ] API endpoints require proper parameters
- [ ] Authentication checks working
- [ ] Rate limiting considered (if needed)

## Production Optimization

### 1. Performance
- [ ] Enable gzip compression
- [ ] Set appropriate cache headers
- [ ] Optimize image sizes
- [ ] Minify CSS/JS if needed

### 2. Monitoring
- [ ] Set up error logging
- [ ] Configure PHP error reporting for production
- [ ] Plan for backup strategy
- [ ] Consider uptime monitoring

### 3. Error Handling
- [ ] Disable PHP error display in production
- [ ] Set up error logging to files
- [ ] Test 404 and 500 error pages

## Post-Deployment

### 1. Documentation
- [ ] Update README with live URLs
- [ ] Document any hosting-specific configurations
- [ ] Record database credentials securely

### 2. Backup Strategy
- [ ] Set up automatic database backups
- [ ] Plan file backup strategy
- [ ] Test backup restoration process

### 3. Monitoring Setup
- [ ] Set up uptime monitoring
- [ ] Configure error alerting
- [ ] Plan regular health checks

## Pi Network Mainnet Preparation

### 1. When Ready for Mainnet
- [ ] Switch Pi SDK to mainnet mode
- [ ] Update Pi Developer Portal settings
- [ ] Test with real Pi payments (small amounts first)
- [ ] Update documentation about mainnet usage

### 2. User Communication
- [ ] Inform users about testnet vs mainnet
- [ ] Provide clear payment instructions
- [ ] Set up user support channels

## Troubleshooting Checklist

### Common Issues
- [ ] Database connection fails → Check credentials and host
- [ ] Pi authentication fails → Verify API key and domain registration
- [ ] CORS errors → Check .htaccess and API headers
- [ ] Images not loading → Check file paths and permissions
- [ ] 500 errors → Check PHP error logs

### Debug Tools
- [ ] Browser developer tools for frontend issues
- [ ] PHP error logs for backend issues
- [ ] Database logs for query issues
- [ ] Pi Developer Portal for Pi Network issues

---

## Final Sign-off

- [ ] All tests passing
- [ ] Performance acceptable
- [ ] Security measures in place
- [ ] Backup strategy implemented
- [ ] Documentation complete
- [ ] Ready for production traffic

**Deployment Date:** ___________  
**Deployed By:** ___________  
**Version:** ___________
