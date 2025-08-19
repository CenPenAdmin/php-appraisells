// Configuration for Appraisells PHP Application
const CONFIG = {
    // Backend API URL - Using ngrok tunnel for XAMPP backend
    BACKEND_URL: 'https://4943480ece59.ngrok-free.app', // Your ngrok tunnel
    
    // Pi SDK Configuration
    PI_SDK: {
        version: "2.0",
        sandbox: true // Set to true for testnet
    },
    
    // Payment Configuration
    PAYMENT: {
        amount: 1,
        memo: "Appraisells subscription payment",
        defaultMetadata: {
            itemName: "Monthly Subscription"
        }
    }
};

// Initialize Pi SDK when it's available
function initializePiSDK() {
    if (typeof Pi !== 'undefined') {
        try {
            Pi.init({
                version: "2.0",
                sandbox: true // Set to true for testnet/sandbox
            });
            console.log('Pi SDK initialized successfully');
            return true;
        } catch (error) {
            console.error('Pi SDK initialization failed:', error);
            return false;
        }
    } else {
        console.warn('Pi SDK not available yet');
        return false;
    }
}

// Wait for Pi SDK to load with better error handling
function waitForPiSDK(callback, maxWait = 10000) {
    const startTime = Date.now();
    
    function checkPi() {
        if (typeof Pi !== 'undefined') {
            if (initializePiSDK()) {
                if (callback) callback();
            } else {
                if (callback) callback(new Error('Pi SDK initialization failed'));
            }
        } else if (Date.now() - startTime < maxWait) {
            setTimeout(checkPi, 200);
        } else {
            console.error('Pi SDK failed to load within timeout period');
            if (callback) callback(new Error('Pi SDK timeout - please ensure you are using Pi Browser'));
        }
    }
    
    checkPi();
}

// Connect login button to Pi authentication
document.addEventListener('DOMContentLoaded', function() {
    const loginBtn = document.getElementById("loginBtn");
    if (loginBtn) {
        loginBtn.onclick = function() {
            // Enhanced Pi Browser detection
            const userAgent = navigator.userAgent;
            console.log('User Agent:', userAgent);
            
            // More comprehensive Pi Browser detection
            const isPiBrowser = userAgent.includes('Pi Browser') || 
                               userAgent.includes('PiBrowser') || 
                               userAgent.includes('Pi/') ||
                               (userAgent.includes('iPhone') && userAgent.includes('Pi')) ||
                               (userAgent.includes('Android') && userAgent.includes('Pi'));
            
            console.log('Pi Browser detected:', isPiBrowser);
            
            if (!isPiBrowser) {
                alert(`Pi Browser not detected!\n\nYour User Agent: ${userAgent}\n\nThis app requires Pi Browser. Please:\n1. Download Pi Browser from the Pi Network app\n2. Open this URL in Pi Browser: https://cenpenadmin.github.io/php-appraisells/`);
                return;
            }
            
            // Check if Pi SDK is available before proceeding
            if (typeof Pi === 'undefined') {
                alert('Pi SDK not loaded. Please ensure you have a stable internet connection and try refreshing the page.');
                return;
            }
            
            console.log('Pi SDK available, proceeding to profile...');
            window.location.href = 'profile.html';
        };
    }
    
    // Initialize Pi SDK when DOM is loaded
    waitForPiSDK();
});

function login() {
    // Enhanced Pi Browser detection
    const userAgent = navigator.userAgent;
    console.log('Login function - User Agent:', userAgent);
    
    // More comprehensive Pi Browser detection
    const isPiBrowser = userAgent.includes('Pi Browser') || 
                       userAgent.includes('PiBrowser') || 
                       userAgent.includes('Pi/') ||
                       (userAgent.includes('iPhone') && userAgent.includes('Pi')) ||
                       (userAgent.includes('Android') && userAgent.includes('Pi'));
    
    if (!isPiBrowser) {
        alert(`Pi Browser not detected!\n\nYour User Agent: ${userAgent}\n\nThis app requires Pi Browser. Please:\n1. Download Pi Browser from the Pi Network app\n2. Open this URL in Pi Browser: https://cenpenadmin.github.io/php-appraisells/`);
        return;
    }
    
    // Wait for Pi SDK and then redirect
    waitForPiSDK(function(error) {
        if (error) {
            console.error('Pi SDK not available:', error);
            alert('Pi SDK not available. Please ensure you are using Pi Browser with internet connection and try again.');
        } else {
            console.log('Redirecting to profile page...');
            window.location.href = 'profile.html';
        }
    });
}
 let piUser = null; // Will hold the authenticated Pi user
    
    // Debug function
    function addDebugInfo(message) {
      const debugContent = document.getElementById('debugContent');
      const timestamp = new Date().toLocaleTimeString();
      debugContent.innerHTML += `[${timestamp}] ${message}<br>`;
      console.log(`[PROFILE DEBUG] ${message}`);
    }
    
    // Initialize debug info
    addDebugInfo('🔄 Profile page loaded');
    addDebugInfo(`Backend URL: ${CONFIG.BACKEND_URL}`);
    addDebugInfo(`User Agent: ${navigator.userAgent}`);
    
    if (navigator.userAgent.includes('Pi Browser') || navigator.userAgent.includes('iPhone')) {
      addDebugInfo('✅ Pi Browser detected');
    } else {
      addDebugInfo('⚠️ Not Pi Browser - authentication may not work');
    }
    
    // Test backend connection
    async function testBackendConnection() {
      addDebugInfo('=== BACKEND CONNECTION TEST ===');
      
      // Test 1: Simple health endpoint
      try {
        addDebugInfo('Test 1: Health endpoint...');
        
        const response1 = await fetch(`${CONFIG.BACKEND_URL}/health`, {
          method: 'GET',
          headers: {
            'ngrok-skip-browser-warning': 'true',
            'Content-Type': 'application/json'
          }
        });
        
        if (response1.ok) {
          const result1 = await response1.json();
          addDebugInfo(`✅ Health test successful: ${result1.status}`);
          addDebugInfo(`📊 Backend info: PHP ${result1.php_version}, DB: ${result1.databaseConnected ? 'Connected' : 'Not connected'}`);
        } else {
          addDebugInfo(`❌ Health test failed: ${response1.status} ${response1.statusText}`);
        }
        
      } catch (error) {
        addDebugInfo(`❌ Health test error: ${error.message}`);
        addDebugInfo(`🔧 Make sure your ngrok tunnel is running and XAMPP Apache is started`);
      }
      
      // Test 2: Direct subscription check for test user
      try {
        addDebugInfo('Test 2: Direct subscription check...');
        
        const response2 = await fetch(`${CONFIG.BACKEND_URL}/subscription-status/testuser`, {
          method: 'GET',
          headers: {
            'ngrok-skip-browser-warning': 'true',
            'Content-Type': 'application/json'
          }
        });
        
        if (response2.ok) {
          const result2 = await response2.json();
          addDebugInfo(`✅ Subscription test successful: ${JSON.stringify(result2)}`);
        } else {
          addDebugInfo(`❌ Subscription test failed: ${response2.status} ${response2.statusText}`);
        }
        
      } catch (error) {
        addDebugInfo(`❌ Subscription test error: ${error.message}`);
      }
      
      addDebugInfo('=== TEST COMPLETE ===');
    }
    

    

function logout() {
      window.location.href = "index.html";
    }


    

    function logout() {
      window.location.href = "index.html";
    }

    // Navigation functions for user collection
    function goToDigitalArt() {
      if (!piUser) {
        alert('Please authenticate first');
        return;
      }
      addDebugInfo(`🎨 Navigating to digital art collection for: ${piUser.username}`);
      window.location.href = `my-digital-art.html?username=${encodeURIComponent(piUser.username)}`;
    }

    function goToAuctionWinners() {
      if (!piUser) {
        alert('Please authenticate first');
        return;
      }
      addDebugInfo(`🏆 Navigating to auction winners for: ${piUser.username}`);
      window.location.href = `auction-winner.html?username=${encodeURIComponent(piUser.username)}`;
    }

    // Congratulations popup functions
    function showCongratsPopup(pendingWins) {
      const popup = document.getElementById('congratulationsPopup');
      const message = document.getElementById('congratsMessage');
      
      if (!popup || !message) {
        console.error('❌ Congratulations popup elements not found!');
        return;
      }
      
      if (pendingWins.length === 1) {
        message.textContent = `You won 1 auction item! Payment required within 48 hours.`;
      } else {
        message.textContent = `You won ${pendingWins.length} auction items! Payment required within 48 hours.`;
      }
      
      popup.style.display = 'flex';
      addDebugInfo(`🎉 Showing congratulations popup for ${pendingWins.length} wins`);
    }

    async function closeCongratsPopup() {
      const popup = document.getElementById('congratulationsPopup');
      if (popup) {
        popup.style.display = 'none';
        
        // Mark wins as viewed in the backend when user closes popup
        if (piUser) {
          try {
            const response = await fetch(`${CONFIG.BACKEND_URL}/mark-wins-viewed`, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'ngrok-skip-browser-warning': 'true'
              },
              body: JSON.stringify({ username: piUser.username })
            });
            
            if (response.ok) {
              const result = await response.json();
              addDebugInfo(`✅ Marked ${result.viewedCount} wins as viewed (popup closed)`);
            } else {
              addDebugInfo(`⚠️ Failed to mark wins as viewed: ${response.status}`);
            }
          } catch (error) {
            addDebugInfo(`❌ Error marking wins as viewed: ${error.message}`);
          }
        }
      }
    }

    async function viewWinsAndPay() {
      if (!piUser) {
        alert('Please authenticate first');
        return;
      }
      
      try {
        // Mark wins as viewed in the backend
        const response = await fetch(`${CONFIG.BACKEND_URL}/mark-wins-viewed`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'ngrok-skip-browser-warning': 'true'
          },
          body: JSON.stringify({ username: piUser.username })
        });
        
        if (response.ok) {
          const result = await response.json();
          addDebugInfo(`✅ Marked ${result.viewedCount} wins as viewed in backend`);
        } else {
          addDebugInfo(`⚠️ Failed to mark wins as viewed: ${response.status}`);
        }
      } catch (error) {
        addDebugInfo(`❌ Error marking wins as viewed: ${error.message}`);
      }
      
      addDebugInfo(`🏆 Redirecting to auction winners for: ${piUser.username}`);
      
      // Close popup and redirect
      closeCongratsPopup();
      window.location.href = `auction-winner.html?username=${encodeURIComponent(piUser.username)}`;
    }

    // Check if user has new wins to show popup for
    async function checkForNewWins(username) {
      try {
        addDebugInfo(`🎉 Checking for new wins for: ${username}`);
        
        const response = await fetch(`${CONFIG.BACKEND_URL}/user-wins/${username}`, {
          headers: { 'ngrok-skip-browser-warning': 'true' }
        });
        
        if (response.ok) {
          const result = await response.json();
          
          if (result.success && result.wins && result.wins.length > 0) {
            const pendingWins = result.wins.filter(win => 
              win.paymentStatus === 'pending' && !win.viewed
            );
            
            if (pendingWins.length > 0) {
              addDebugInfo(`🎉 User has ${pendingWins.length} new unviewed wins - showing popup`);
              showCongratsPopup(pendingWins);
            } else {
              addDebugInfo(`✅ User has wins but all are viewed or paid`);
            }
          } else {
            addDebugInfo(`📭 No wins found for user`);
          }
        } else {
          addDebugInfo(`❌ Failed to fetch wins: ${response.status}`);
        }
        
      } catch (error) {
        addDebugInfo(`❌ Error checking for new wins: ${error.message}`);
      }
    }

    // Load collection stats
    async function loadCollectionStats(username) {
      try {
        addDebugInfo(`📊 Loading collection stats for: ${username}`);
        
        // Fetch digital art collection
        const artResponse = await fetch(`${CONFIG.BACKEND_URL}/user-digital-art/${username}`, {
          headers: { 'ngrok-skip-browser-warning': 'true' }
        });
        
        // Fetch auction wins
        const winsResponse = await fetch(`${CONFIG.BACKEND_URL}/user-wins/${username}`, {
          headers: { 'ngrok-skip-browser-warning': 'true' }
        });
        
        let artCount = 0;
        let winsCount = 0;
        let pendingCount = 0;
        
        if (artResponse.ok) {
          const artData = await artResponse.json();
          addDebugInfo(`📊 Art response: ${JSON.stringify(artData)}`);
          artCount = artData.success ? (artData.digitalArt ? artData.digitalArt.length : 0) : 0;
        } else {
          addDebugInfo(`❌ Art fetch failed: ${artResponse.status} ${artResponse.statusText}`);
        }
        
        if (winsResponse.ok) {
          const winsData = await winsResponse.json();
          addDebugInfo(`📊 Wins response: ${JSON.stringify(winsData)}`);
          if (winsData.success) {
            winsCount = winsData.wins ? winsData.wins.length : 0;
            // Count pending items (not completed)
            pendingCount = winsData.wins ? winsData.wins.filter(win => !win.completed).length : 0;
          }
        } else {
          addDebugInfo(`❌ Wins fetch failed: ${winsResponse.status} ${winsResponse.statusText}`);
        }
        
        // Update stats display
        const statsContent = document.getElementById('statsContent');
        if (statsContent) {
          statsContent.innerHTML = `
            <div style="display: flex; justify-content: space-around; flex-wrap: wrap; gap: 10px;">
              <div style="text-align: center;">
                <div style="font-size: 1.5em; font-weight: bold; color: #030006;">${artCount}</div>
                <div style="font-size: 0.9em;">Digital Artworks</div>
              </div>
              <div style="text-align: center;">
                <div style="font-size: 1.5em; font-weight: bold; color: #030006;">${winsCount}</div>
                <div style="font-size: 0.9em;">Total Wins</div>
              </div>
              <div style="text-align: center;">
                <div style="font-size: 1.5em; font-weight: bold; color: #030006;">${pendingCount}</div>
                <div style="font-size: 0.9em;">Pending Items</div>
              </div>
            </div>
          `;
        } else {
          addDebugInfo('❌ ERROR: statsContent element not found!');
        }
        
        addDebugInfo(`📊 Collection stats loaded: ${artCount} artworks, ${winsCount} wins, ${pendingCount} pending`);
        
      } catch (error) {
        addDebugInfo(`❌ Failed to load collection stats: ${error.message}`);
        const statsContent = document.getElementById('statsContent');
        if (statsContent) {
          statsContent.innerHTML = 'Failed to load stats';
        } else {
          addDebugInfo('❌ ERROR: statsContent element not found in catch block!');
        }
      }
    }

    function displayWalletInfo(wallet) {
      // Display wallet information if available
      const walletElement = document.getElementById("walletInfo");
      if (walletElement) {
        walletElement.innerHTML = wallet 
          ? `<strong>Wallet:</strong> ${wallet}`
          : "";
      }
    }

    function subscribe() {
      // Use authenticated Pi user for subscription
      if (!piUser) {
        addDebugInfo('❌ Subscribe clicked but no piUser available');
        alert('Please authenticate with Pi Network first');
        return;
      }
      
      addDebugInfo(`🔄 Redirecting to subscription payment for: ${piUser.username}`);
      
      // Redirect to auctionPayment.html with Pi user parameters
      let redirectUrl = "auctionPayment.html";
      redirectUrl += `?username=${encodeURIComponent(piUser.username)}`;
      if (piUser.wallet) {
        redirectUrl += `&wallet=${encodeURIComponent(piUser.wallet)}`;
      }
      
      addDebugInfo(`🔗 Redirect URL: ${redirectUrl}`);
      window.location.href = redirectUrl;
    }

    // Authenticate with Pi Network and load user profile
    async function authenticateAndLoadProfile() {
      try {
        addDebugInfo('🔐 Starting Pi Network authentication...');
        console.log('🔐 Starting Pi Network authentication...');
        
        // Check if we're in Pi Browser
        if (!navigator.userAgent.includes('Pi Browser') && !navigator.userAgent.includes('iPhone')) {
          throw new Error('This app requires Pi Browser. Please open it in Pi Browser.');
        }
        
        addDebugInfo('✅ Pi Browser detected');
        
        // Check if Pi SDK is available
        if (typeof Pi === 'undefined') {
          throw new Error('Pi SDK not loaded. Please ensure you are using Pi Browser and refresh the page.');
        }
        
        addDebugInfo('✅ Pi SDK detected');
        
        // Initialize Pi SDK with proper error handling
        try {
          await Pi.init({
            version: "2.0",
            sandbox: true // Set to true for testnet
          });
          addDebugInfo('✅ Pi SDK initialized successfully');
        } catch (initError) {
          throw new Error(`Pi SDK initialization failed: ${initError.message}`);
        }
        
        console.log('🔐 Pi SDK initialized, authenticating user...');
        
        // Authenticate user with Pi Network
        const auth = await Pi.authenticate(['username', 'payments'], function(scopes) {
          // Handle scope callback if needed
          console.log('Pi Authentication scopes approved:', scopes);
          addDebugInfo(`🔐 Pi Authentication scopes approved: ${JSON.stringify(scopes)}`);
        });
        
        piUser = auth.user;
        
        addDebugInfo(`✅ Pi Network authentication successful! User: ${piUser.username}`);
        addDebugInfo(`🔍 User UID: ${piUser.uid}`);
        addDebugInfo(`🔍 User wallet: ${piUser.wallet || 'Not provided'}`);
        console.log(`✅ Pi Network authentication successful! User: ${piUser.username}`);
        console.log('Full Pi user object:', piUser);
        
        // Display user information
        const userInfoElement = document.getElementById("userInfo");
        if (userInfoElement) {
          userInfoElement.innerHTML = `<strong>Username:</strong> ${piUser.username}`;
        } else {
          addDebugInfo('❌ ERROR: userInfo element not found!');
        }
        displayWalletInfo(piUser.wallet);
        
        addDebugInfo('🔄 Creating/updating user profile in backend...');
        
        // Create/update user profile in backend
        await createUserProfileIfNeeded(piUser.username, piUser.wallet, piUser.uid);
        
        addDebugInfo('🔄 Checking subscription status and updating UI...');
        
        // Check subscription status for authenticated user and update UI accordingly
        await checkSubscriptionStatusAndUpdateUI(piUser.username);
        
        // Show collection section and load stats
        const collectionSection = document.getElementById('userCollectionSection');
        if (collectionSection) {
          collectionSection.style.display = 'block';
          addDebugInfo('✅ Collection section shown');
        } else {
          addDebugInfo('❌ ERROR: userCollectionSection element not found!');
        }
        await loadCollectionStats(piUser.username);
        
        // Check for new wins and show congratulations popup if needed
        await checkForNewWins(piUser.username);
        
      } catch (error) {
        addDebugInfo(`❌ Pi Network authentication failed: ${error.message}`);
        console.error('❌ Pi Network authentication failed:', error);
        showAuthenticationError();
      }
    }

    // Show authentication error state
    function showAuthenticationError() {
      addDebugInfo('🔄 Showing authentication error state...');
      console.log('🔄 Showing authentication error state...');
      
      // Show error message to user
      document.getElementById("userInfo").innerHTML = `
        <div style="color: red; text-align: center; margin: 20px;">
          <strong>Authentication Required</strong><br>
          Please open this app in Pi Browser and allow authentication.
        </div>
      `;
      
      // Hide subscription text
      document.getElementById('subscriptionText').style.display = 'none';
      
      // Show login button instead of subscribe button
      document.getElementById('subscribeSection').innerHTML = `
        <div style="text-align: center; margin: 20px 0;">
          <button onclick="authenticateAndLoadProfile()" style="padding:12px 25px; font-size:1.5rem; background-color:#030006; color: white; border:none; border-radius:8px; cursor:pointer;">
            Login with Pi Network
          </button>
        </div>
      `;
      
      addDebugInfo('✅ Authentication error UI displayed');
    }

    // Show subscription state based on user's subscription status
    function showSubscriptionState(hasActiveSubscription, subscriptionData = null) {
      addDebugInfo(`🎯 Updating UI state - hasActiveSubscription: ${hasActiveSubscription}`);
      console.log(`🎯 Updating UI state - hasActiveSubscription: ${hasActiveSubscription}`);
      
      const subscriptionTextElement = document.getElementById('subscriptionText');
      const subscribeSectionElement = document.getElementById('subscribeSection');
      
      // Log current element states before changes
      addDebugInfo(`📊 BEFORE CHANGES:`);
      addDebugInfo(`   - subscriptionText display: ${subscriptionTextElement.style.display}`);
      addDebugInfo(`   - subscribeSection display: ${subscribeSectionElement.style.display}`);
      
      if (hasActiveSubscription) {
        // User HAS active subscription - show "Enjoy Appraisells auctions" text
        addDebugInfo('✅ Showing SUBSCRIBED state - "Enjoy Appraisells auctions"');
        console.log('✅ Showing SUBSCRIBED state - "Enjoy Appraisells auctions"');
        
        if (subscriptionData) {
          addDebugInfo(`📋 Subscription details: End date: ${subscriptionData.endDate}, Days remaining: ${subscriptionData.daysRemaining}`);
        }
        
        // Reset the subscribe section to original button (in case it was changed to login)
        subscribeSectionElement.innerHTML = `
          <button onclick="subscribe()" id="subscribeBtn" style="padding:12px 25px; font-size:2rem; background-color:#030006; color: white; border:none; border-radius:8px; cursor:pointer;">Subscribe</button>
        `;
        
        // Show subscription text, hide subscribe button
        subscriptionTextElement.style.display = 'block';
        subscriptionTextElement.style.visibility = 'visible';
        subscriptionTextElement.className = 'subscription-active';
        
        subscribeSectionElement.style.display = 'none';
        subscribeSectionElement.style.visibility = 'hidden';
        subscribeSectionElement.className = 'subscription-hidden';
        
        addDebugInfo('🎉 SUBSCRIBED USER: Should see "Enjoy Appraisells auctions"');
        console.log('🎉 SUBSCRIBED USER: Should see "Enjoy Appraisells auctions"');
        
      } else {
        // User has NO active subscription - show Subscribe button
        addDebugInfo('❌ Showing UNSUBSCRIBED state - Subscribe button');
        console.log('❌ Showing UNSUBSCRIBED state - Subscribe button');
        
        // Reset the subscribe section to original button (in case it was changed to login)
        subscribeSectionElement.innerHTML = `
          <button onclick="subscribe()" id="subscribeBtn" style="padding:12px 25px; font-size:2rem; background-color:#030006; color: white; border:none; border-radius:8px; cursor:pointer;">Subscribe</button>
        `;
        
        // Hide subscription text, show subscribe button
        subscriptionTextElement.style.display = 'none';
        subscriptionTextElement.style.visibility = 'hidden';
        subscriptionTextElement.className = 'subscription-hidden';
        
        subscribeSectionElement.style.display = 'block';
        subscribeSectionElement.style.visibility = 'visible';
        subscribeSectionElement.className = 'subscription-active';
        
        addDebugInfo('💳 UNSUBSCRIBED USER: Should see Subscribe button');
        console.log('💳 UNSUBSCRIBED USER: Should see Subscribe button');
      }
      
      // Final verification
      addDebugInfo(`📊 AFTER CHANGES:`);
      addDebugInfo(`   - subscriptionText display: ${subscriptionTextElement.style.display}`);
      addDebugInfo(`   - subscribeSection display: ${subscribeSectionElement.style.display}`);
      addDebugInfo(`   - subscriptionText className: ${subscriptionTextElement.className}`);
      addDebugInfo(`   - subscribeSection className: ${subscribeSectionElement.className}`);
      console.log(`🔍 Final UI state verification:`);
      console.log(`   - subscriptionText display: ${subscriptionTextElement.style.display}`);
      console.log(`   - subscribeSection display: ${subscribeSectionElement.style.display}`);
    }

    // Create user profile in backend if it doesn't exist
    async function createUserProfileIfNeeded(username, wallet, userUid) {
      try {
        addDebugInfo(`🔄 Ensuring user profile exists for: ${username}`);
        console.log(`🔄 Ensuring user profile exists for: ${username}`);
        
        const response = await fetch(`${CONFIG.BACKEND_URL}/create-user-profile`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'ngrok-skip-browser-warning': 'true'
          },
          body: JSON.stringify({
            username: username,
            wallet: wallet,
            userUid: userUid
          })
        });
        
        addDebugInfo(`📬 Profile creation response: ${response.status} ${response.statusText}`);
        
        const result = await response.json();
        
        if (result.success) {
          addDebugInfo(`✅ User profile ready for: ${username}`);
          console.log(`✅ User profile ready for: ${username}`);
        } else {
          addDebugInfo(`⚠️ Profile creation failed for ${username}: ${result.error}`);
          console.warn(`⚠️ Profile creation failed for ${username}:`, result.error);
        }
        
      } catch (error) {
        addDebugInfo(`❌ Failed to create user profile: ${error.message}`);
        console.error('❌ Failed to create user profile:', error);
        // Don't block subscription checking if this fails
      }
    }

    // Check subscription status and update UI accordingly
    async function checkSubscriptionStatusAndUpdateUI(username) {
      try {
        addDebugInfo(`🔍 Starting subscription check for: ${username}`);
        console.log(`🔍 Starting subscription check for: ${username}`);
        
        // Add cache busting to ensure fresh data
        const timestamp = Date.now();
        const url = `${CONFIG.BACKEND_URL}/subscription-status/${username}?t=${timestamp}`;
        addDebugInfo(`📡 Fetching from: ${url}`);
        console.log(`📡 Fetching from: ${url}`);
        
        addDebugInfo(`🔧 Preparing fetch request with headers...`);
        
        const response = await fetch(url, {
          method: 'GET',
          headers: {
            'ngrok-skip-browser-warning': 'true',
            'Cache-Control': 'no-cache, no-store, must-revalidate',
            'Pragma': 'no-cache',
            'Expires': '0'
          }
        });
        
        addDebugInfo(`📬 Response status: ${response.status}`);
        addDebugInfo(`📬 Response ok: ${response.ok}`);
        addDebugInfo(`📬 Response statusText: ${response.statusText}`);
        console.log(`📬 Response status: ${response.status}`);
        
        if (!response.ok) {
          throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        addDebugInfo(`🔄 Parsing response JSON...`);
        const result = await response.json();
        addDebugInfo(`📊 Raw subscription result: ${JSON.stringify(result)}`);
        addDebugInfo(`🔍 result.success: ${result.success}`);
        addDebugInfo(`🔍 result.subscribed: ${result.subscribed}`);
        console.log(`📊 Full subscription result:`, result);
        console.log(`🔍 result.success: ${result.success}`);
        console.log(`🔍 result.subscribed: ${result.subscribed}`);
        
        // Use the new UI management function
        if (result.success && result.subscribed) {
          addDebugInfo(`✅ ${username} HAS ACTIVE SUBSCRIPTION!`);
          console.log(`✅ ${username} HAS ACTIVE SUBSCRIPTION!`);
          showSubscriptionState(true, result);
        } else {
          addDebugInfo(`❌ ${username} has no active subscription`);
          console.log(`❌ ${username} has no active subscription`);
          showSubscriptionState(false, result);
        }
        
      } catch (error) {
        addDebugInfo(`❌ Failed to check subscription status: ${error.message}`);
        addDebugInfo(`❌ Error name: ${error.name}`);
        addDebugInfo(`❌ Error stack: ${error.stack}`);
        console.error('❌ Failed to check subscription status:', error);
        console.error('❌ Full error object:', error);
        
        // Try a simpler request as fallback
        addDebugInfo(`🔄 Attempting simplified fallback request...`);
        try {
          const fallbackResponse = await fetch(`${CONFIG.BACKEND_URL}/subscription-status/${username}`, {
            headers: {
              'ngrok-skip-browser-warning': 'true'
            }
          });
          
          if (fallbackResponse.ok) {
            const fallbackResult = await fallbackResponse.json();
            addDebugInfo(`✅ Fallback request succeeded: ${JSON.stringify(fallbackResult)}`);
            
            if (fallbackResult.success && fallbackResult.subscribed) {
              addDebugInfo(`✅ FALLBACK: ${username} HAS ACTIVE SUBSCRIPTION!`);
              showSubscriptionState(true, fallbackResult);
              return;
            }
          }
        } catch (fallbackError) {
          addDebugInfo(`❌ Fallback request also failed: ${fallbackError.message}`);
        }
        
        // On error, show unsubscribed state (safe default)
        addDebugInfo(`⚠️ Error occurred - showing unsubscribed state as default`);
        console.log(`⚠️ Error occurred - showing unsubscribed state as default`);
        showSubscriptionState(false);
      }
    }

    // Initialize page with Pi Network authentication
    window.addEventListener('load', function() {
      addDebugInfo('🔄 Profile page loaded - starting Pi Network authentication...');
      console.log('🔄 Profile page loaded - starting Pi Network authentication...');
      
      // Hide both elements initially
      document.getElementById('subscriptionText').style.display = 'none';
      document.getElementById('subscribeSection').style.display = 'none';
      addDebugInfo('🔄 Initial UI elements hidden');
      
      // Start Pi Network authentication
      authenticateAndLoadProfile();
    });