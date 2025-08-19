# Appraisells PHP - Digital Art Auction Platform

A complete PHP-based recreation of the Pay Me Pi app, designed for Spaceship hosting with MySQL database support, Pi Network integration, and full auction functionality.

## 🌟 Features

- **User Authentication**: Pi Network SDK integration for secure login
- **Live Auctions**: Real-time countdown timer and bidding system identical to Pay Me Pi
- **Payment Processing**: Pi Network payment integration with winner notifications
- **User Profiles**: Complete profile management system
- **Digital Art Gallery**: Display and manage digital art collections
- **Admin Dashboard**: Complete admin panel for auction management
- **Subscription System**: User subscription management
- **Security**: Environment variable configuration, CORS protection, SQL injection prevention

## 🚀 Quick Start

### 1. Environment Configuration

Create a `.env` file in the root directory based on `.env.example`:

```bash
# Copy the example file
cp .env.example .env
```

Edit `.env` with your actual configuration:

```env
# Database Configuration
DB_HOST=localhost
DB_NAME=appraisells_db
DB_USER=your_username
DB_PASS=your_password

# Pi Network API Configuration
PI_API_KEY=your_pi_api_key_here
PI_WALLET_PRIVATE_SEED=your_pi_wallet_private_seed_here

# Application Configuration
AUCTION_PAYMENT_TIMEOUT_HOURS=48
ADMIN_USERNAME=admin
ADMIN_EMAIL=admin@appraisells.com

# Environment
ENVIRONMENT=production
DEBUG_MODE=false
```

### 2. Database Setup

1. Create a MySQL database named `appraisells_db`
2. Import the database schema:

```bash
mysql -u your_username -p appraisells_db < database_schema.sql
```

### 3. File Upload

Upload all files to your Spaceship hosting file manager:

- Upload to your domain's public_html folder
- Ensure `.env` file is uploaded with your actual configuration
- Verify file permissions (755 for directories, 644 for files)

### 4. Verification

Visit your domain and check:
- Homepage loads correctly
- Profile page works with Pi authentication
- Live auction page displays timer and items identical to Pay Me Pi
- API endpoints respond correctly

## 📁 File Structure

```
Appraisells PHP/
├── .env                     # Environment configuration (create from .env.example)
├── .env.example            # Environment configuration template
├── .htaccess               # Apache configuration and routing
├── config.php              # Database connection and configuration loader
├── api.php                 # Main API endpoints and business logic
├── index.html              # Homepage
├── profile.html            # User profile page
├── profile.php             # Profile backend logic
├── save_user.php           # User registration backend
├── liveAuction.html        # Live auction with timer and bidding (identical to Pay Me Pi)
├── auction-winner.html     # Winner display and payment processing
├── my-digital-art.html     # Digital art gallery
├── admin.html              # Admin dashboard
├── style.css               # Application styles
├── script.js               # Client-side JavaScript utilities
├── users.json              # User data storage (if not using database)
├── database_schema.sql     # MySQL database schema
├── DEPLOYMENT_CHECKLIST.md # Deployment guide
├── PROJECT_OVERVIEW.md     # Project documentation
└── images/                 # Digital art and assets
    ├── SueHipple1.jpg
    ├── SueHipple2.jpg
    ├── SueHipple3.jpg
    ├── SueHipple4.jpg
    ├── SueHipple5.jpg
    └── middle finger man.png
```

## 🔐 Environment Variables (.env Configuration)

### How to Connect API Keys and Sensitive Data

The application now uses a `.env` file for secure configuration management, just like the original Pay Me Pi app:

1. **Copy the example file**: `cp .env.example .env`
2. **Edit with your credentials**: Open `.env` and replace placeholder values
3. **Never commit `.env`**: The actual `.env` file with real credentials should never be committed to version control

### Required Variables

| Variable | Description | Example |
|----------|-------------|---------|
| `DB_HOST` | Database host | `localhost` |
| `DB_NAME` | Database name | `appraisells_db` |
| `DB_USER` | Database username | `your_username` |
| `DB_PASS` | Database password | `your_password` |
| `PI_API_KEY` | Pi Network API key | `your_pi_api_key` |

### Optional Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `PI_WALLET_PRIVATE_SEED` | Pi wallet seed | `` |
| `AUCTION_PAYMENT_TIMEOUT_HOURS` | Payment timeout | `48` |
| `ADMIN_USERNAME` | Admin username | `admin` |
| `ADMIN_EMAIL` | Admin email | `admin@example.com` |
| `ENVIRONMENT` | Environment mode | `production` |
| `DEBUG_MODE` | Debug mode | `false` |

## 🎯 API Endpoints

### Authentication
- `GET /api.php?endpoint=user-profile&username={username}` - Get user profile
- `POST /api.php` with `endpoint: save-user` - Save/update user

### Auction System (Identical to Pay Me Pi)
- `GET /api.php?endpoint=auction-status` - Get auction status and timer
- `POST /api.php` with `endpoint: auction-bid` - Place a bid
- `POST /api.php` with `endpoint: remove-auction-bid` - Remove a bid
- `GET /api.php?endpoint=user-bid-status&username={username}` - Get user's bid status
- `GET /api.php?endpoint=calculate-winners&auctionId={id}` - Calculate auction winners
- `GET /api.php?endpoint=user-wins&username={username}` - Get user's wins

### Payment Processing
- `POST /api.php` with `endpoint: approve-payment` - Approve Pi payment
- `POST /api.php` with `endpoint: complete-payment` - Complete Pi payment

### Subscription Management
- `GET /api.php?endpoint=subscription-status&username={username}` - Check subscription
- `POST /api.php` with `endpoint: test-create-subscription` - Create test subscription

### Admin & Debug
- `GET /api.php?endpoint=debug-auction` - Get debug auction data
- `GET /api.php?endpoint=admin-stats` - Get admin statistics

## 🔧 Configuration

### .env File Loading

The application automatically loads configuration from `.env` file using the `loadEnvFile()` function in `config.php`. This ensures:
- Secure credential management
- Environment-specific configuration
- Easy deployment across different environments

### Apache/.htaccess Setup

The `.htaccess` file is configured for:
- Clean URL routing
- CORS headers for Pi SDK
- Security headers
- Error handling

### Pi Network Integration

Configure Pi Network integration by:
1. Setting `PI_API_KEY` in `.env`
2. Configuring Pi SDK in frontend files
3. Testing Pi authentication flow

## 🎨 Auction System (Identical to Pay Me Pi)

### Timer Logic
- Fixed auction end time in backend (configurable)
- Real-time countdown display identical to Pay Me Pi
- Automatic winner calculation when timer expires
- Seamless transition to winner notification

### Bidding System
- Users can place/remove bids (identical UX to Pay Me Pi)
- Real-time bid status updates
- Subscription verification for bidding
- Visual feedback for bid actions

### Payment Flow
1. Auction ends, winners calculated automatically
2. Winners redirected to auction-winner.html
3. Payment deadline display with countdown
4. Pi Network payment processing
5. Payment approval and completion
6. Digital art delivery confirmation

## 🔍 Debugging

### Debug Mode
Enable debug mode in `.env`:
```env
DEBUG_MODE=true
```

### Debug Endpoints
- `/api.php?endpoint=debug-auction` - View all auction data
- Check browser console for JavaScript errors
- Monitor PHP error logs

### Common Issues
1. **CORS Errors**: Check .htaccess CORS headers
2. **Database Connection**: Verify .env database credentials
3. **Pi SDK Errors**: Check Pi API key configuration in .env
4. **Timer Issues**: Verify auction end time in API
5. **.env Loading**: Ensure .env file exists and is readable

## 📋 Deployment to Spaceship Hosting

### Step 1: Prepare Files
1. Create `.env` file from `.env.example`
2. Add your actual database and Pi Network credentials
3. Test locally if possible

### Step 2: Upload to Spaceship
1. Upload all files to public_html
2. Ensure .env file is uploaded
3. Set correct file permissions

### Step 3: Database Setup
1. Create MySQL database via Spaceship control panel
2. Import database_schema.sql
3. Update .env with Spaceship database credentials

### Step 4: Test
1. Visit your domain
2. Test Pi authentication
3. Test auction timer and bidding
4. Verify API endpoints

## 🤝 Support

For technical support or questions:
1. Check the deployment checklist
2. Review error logs
3. Test API endpoints individually
4. Verify .env configuration
5. Ensure auction timer logic matches Pay Me Pi

## 📄 License

This project recreates the Pay Me Pi app functionality using PHP and MySQL, configured for Spaceship hosting deployment with secure environment variable management.
#   a p p r a i s e l l s - p h p  
 