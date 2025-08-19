# Appraisells Pi Browser Integration Setup

## Current Configuration

Your app is now configured to work with:
- **ngrok tunnel**: `https://4943480ece59.ngrok-free.app`
- **Frontend (Pi Browser)**: `https://cenpenadmin.github.io/php-appraisells/`
- **Backend (XAMPP)**: Local PHP server tunneled via ngrok

## Setup Steps Required

### 1. XAMPP Setup
```bash
# Ensure these are running:
- Apache Web Server
- MySQL Database
```

### 2. Database Setup
```bash
# Run this in your browser or command line:
http://localhost/setup.php
# OR visit: https://4943480ece59.ngrok-free.app/setup.php
```

### 3. ngrok Tunnel
```bash
# Make sure your ngrok tunnel is running:
ngrok http 80
# Should show: https://4943480ece59.ngrok-free.app -> http://localhost:80
```

### 4. Pi API Key Setup
1. Visit https://developers.minepi.com/
2. Create an app or get your API key
3. Update the `.env` file with your PI_API_KEY

### 5. Test the Setup
1. Open Pi Browser
2. Navigate to: `https://cenpenadmin.github.io/php-appraisells/`
3. Click "Login with Pi Browser"
4. Allow authentication permissions
5. Check the debug section for connection status

## Key Changes Made

### JavaScript (script.js)
- Updated BACKEND_URL to use your ngrok tunnel
- Enhanced Pi SDK initialization with better error handling
- Added Pi Browser detection
- Improved authentication flow with scope handling
- Better error messages for debugging

### PHP Backend (api.php, config.php)
- Added ngrok-skip-browser-warning header support
- Updated CORS headers
- Set APP_URL to your ngrok tunnel
- Enhanced health check endpoint

### HTML Files
- Improved Pi SDK loading order
- Added Pi Browser detection warnings
- Enhanced user experience

### Configuration
- Created .env file with your ngrok settings
- Updated .htaccess for better API routing
- Added setup script for easy database initialization

## Troubleshooting

### If Pi Authentication Fails:
1. Ensure you're using Pi Browser (not regular browser)
2. Check that Pi SDK loads properly (see debug section)
3. Allow all requested permissions when prompted
4. Make sure ngrok tunnel is active

### If Backend Connection Fails:
1. Verify XAMPP Apache is running
2. Check ngrok tunnel is active: `ngrok http 80`
3. Test health endpoint: https://4943480ece59.ngrok-free.app/health
4. Check debug console for specific errors

### Common Issues:
- **CORS Errors**: Headers are configured, ensure ngrok tunnel is active
- **Pi SDK Not Found**: Make sure you're in Pi Browser, not regular browser
- **Database Errors**: Run setup.php to initialize database
- **API Errors**: Check XAMPP Apache service is running

## URLs for Testing

- **Health Check**: https://4943480ece59.ngrok-free.app/health
- **Frontend**: https://cenpenadmin.github.io/php-appraisells/
- **Local Admin**: http://localhost/admin.html
- **Database Setup**: https://4943480ece59.ngrok-free.app/setup.php

## Pi Browser Specific Notes

The app now properly:
- Detects Pi Browser environment
- Initializes Pi SDK with correct version (2.0)
- Handles sandbox mode for testing
- Requests appropriate permissions (username, payments)
- Provides clear error messages when not in Pi Browser
- Uses your ngrok tunnel for all backend communication

Remember to keep your ngrok tunnel running while testing!
