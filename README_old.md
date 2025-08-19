# Appraisells PHP - Digital Art Auction Platform

A PHP-based digital art auction platform integrated with Pi Network payments, designed for deployment on Spaceship hosting.

## Features

- **Pi Network Integration**: Secure payments and authentication using Pi Network
- **Digital Art Auctions**: Live bidding system for digital artwork
- **Subscription System**: Monthly subscription model for platform access
- **User Management**: Complete user profile and activity tracking
- **Digital Art Delivery**: Secure delivery of purchased digital artwork
- **Admin Dashboard**: Backend analytics and management tools

## File Structure

```
Appraisells PHP/
├── index.html              # Landing page
├── profile.html             # User profile and subscription page
├── liveAuction.html        # Live auction bidding interface
├── my-digital-art.html     # User's purchased digital art
├── script.js               # Frontend JavaScript configuration
├── style.css               # Stylesheet
├── config.php              # PHP configuration and utilities
├── api.php                 # Main API router and endpoints
├── database_schema.sql     # MySQL database schema
├── .htaccess              # Apache configuration
├── README.md              # This file
└── images/                # Artwork images
    ├── middle finger man.png
    ├── SueHipple1.jpg
    ├── SueHipple2.jpg
    ├── SueHipple3.jpg
    ├── SueHipple4.jpg
    └── SueHipple5.jpg
```

## Setup Instructions for Spaceship Hosting

### 1. Database Setup

1. **Create MySQL Database**:
   - Log into your Spaceship hosting control panel
   - Create a new MySQL database
   - Note down the database name, username, and password

2. **Import Database Schema**:
   - Use phpMyAdmin or MySQL command line to import `database_schema.sql`
   - This will create all necessary tables and sample data

### 2. Configuration

1. **Update config.php**:
   ```php
   // Update these values in config.php
   define('DB_HOST', 'your_mysql_host');
   define('DB_NAME', 'your_database_name');
   define('DB_USER', 'your_db_username');
   define('DB_PASS', 'your_db_password');
   define('APP_URL', 'https://yourdomain.com'); // Your Spaceship domain
   ```

2. **Set Pi API Key**:
   - Get your Pi API key from [Pi Developer Portal](https://developers.minepi.com/)
   - Set as environment variable or update config.php:
   ```php
   define('PI_API_KEY', 'your_pi_api_key_here');
   ```

3. **Update Domain in JavaScript**:
   - The `script.js` file automatically uses `window.location.origin`
   - No changes needed unless using a different API subdomain

### 3. File Upload

1. **Upload Files**:
   - Upload all files to your Spaceship hosting public_html directory
   - Ensure file permissions are correctly set (644 for files, 755 for directories)

2. **Image Upload**:
   - Upload all images to the `images/` directory
   - Ensure images are accessible via web browser

### 4. Pi Network Developer Setup

1. **Register Your App**:
   - Go to [Pi Developer Portal](https://developers.minepi.com/)
   - Create a new app
   - Add your domain to allowed domains
   - Get your API key

2. **Configure Sandbox/Mainnet**:
   - For testing: Set `sandbox: true` in `script.js`
   - For production: Set `sandbox: false` in `script.js`

### 5. Testing

1. **Health Check**:
   ```
   https://yourdomain.com/api.php/health
   ```
   Should return server status and database connection info

2. **Test Authentication**:
   - Visit your site in Pi Browser
   - Click "Login with Pi Browser"
   - Complete authentication flow

3. **Test Subscription**:
   - After authentication, try subscribing
   - Check database for payment and subscription records

## API Endpoints

### Authentication
- `POST /api.php/log-authentication` - Log user authentication
- `POST /api.php/create-user-profile` - Create/update user profile

### Payments & Subscriptions
- `POST /api.php/approve-payment` - Approve Pi Network payment
- `POST /api.php/complete-payment` - Complete Pi Network payment
- `GET /api.php/subscription-status/{username}` - Check subscription status
- `POST /api.php/test-create-subscription` - Create test subscription

### Auctions
- `POST /api.php/auction-bid` - Submit auction bid
- `GET /api.php/auction-bids/{username}` - Get user's bids
- `GET /api.php/auction-highest-bids` - Get highest bids for all items
- `POST /api.php/close-auction` - Close auction and determine winners

### Data & Analytics
- `GET /api.php/health` - Server health check
- `GET /api.php/payments` - Get all payments
- `GET /api.php/user-profiles` - Get all user profiles
- `GET /api.php/user-activities` - Get user activities
- `GET /api.php/analytics` - Get platform analytics
- `GET /api.php/digital-art/{username}` - Get user's digital art

## Database Tables

- **user_profiles** - User account information
- **user_activities** - Activity logging
- **payments** - Pi Network payment records
- **user_subscriptions** - Subscription management
- **auction_bids** - Auction bidding records
- **auction_winners** - Auction results
- **digital_art_delivery** - Digital art access records
- **sessions** - Page visit tracking
- **auction_items** - Auction item definitions

## Security Features

- Input sanitization and validation
- SQL injection prevention using prepared statements
- CORS headers for API security
- Environment variable support for sensitive data
- Activity logging for audit trails
- File access restrictions via .htaccess

## Troubleshooting

### Common Issues

1. **Database Connection Failed**:
   - Check database credentials in config.php
   - Verify database server is accessible
   - Check MySQL service status

2. **Pi Authentication Not Working**:
   - Ensure using Pi Browser
   - Check Pi API key is set correctly
   - Verify domain is registered in Pi Developer Portal

3. **CORS Errors**:
   - Check .htaccess file is uploaded
   - Verify CORS headers in API responses
   - Test with browser developer tools

4. **Images Not Loading**:
   - Check image file paths and permissions
   - Verify images directory is accessible
   - Check for case-sensitive filenames

### Debug Mode

Enable debug output in profile.html:
```javascript
let debugMode = true; // Set to true to enable debug output
```

### Log Files

Check your hosting error logs for PHP errors:
- cPanel Error Logs
- PHP error log (if configured)

## Production Deployment Checklist

- [ ] Database created and schema imported
- [ ] Pi API key configured
- [ ] Domain registered with Pi Network
- [ ] All files uploaded with correct permissions
- [ ] Health endpoint returns success
- [ ] Authentication flow tested
- [ ] Payment flow tested
- [ ] Auction system tested
- [ ] Error reporting disabled in production
- [ ] SSL certificate installed
- [ ] Backup system configured

## Support

For issues specific to this application, check:
1. PHP error logs
2. Browser console for JavaScript errors
3. Network tab for API call failures
4. Pi Developer Portal for Pi Network issues

## License

This project is configured for deployment on Spaceship hosting with Pi Network integration. Modify as needed for your specific use case.

---

**Important**: Remember to keep your Pi API key secure and never commit it to version control. Always use environment variables or secure configuration files in production.
