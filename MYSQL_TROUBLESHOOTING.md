# XAMPP MySQL Troubleshooting Guide

## 🔍 Finding XAMPP Logs

### Apache Logs Location:
- **Error Log**: `C:\xampp\apache\logs\error.log`
- **Access Log**: `C:\xampp\apache\logs\access.log`

### MySQL Logs Location:
- **Error Log**: `C:\xampp\mysql\data\mysql_error.log`
- **Data Directory**: `C:\xampp\mysql\data\`

## 🛠️ MySQL Fix Steps

### Step 1: Check MySQL Error Log
1. Open: `C:\xampp\mysql\data\mysql_error.log`
2. Look for the latest error messages

### Step 2: Reset MySQL Data (if corrupted)
**⚠️ WARNING: This will delete all databases. Backup first!**

1. Stop XAMPP MySQL service
2. Navigate to: `C:\xampp\mysql\data\`
3. Delete everything EXCEPT:
   - `mysql` folder
   - `performance_schema` folder
   - `phpmyadmin` folder
4. Copy backup files from: `C:\xampp\mysql\backup\`
5. Restart MySQL

### Step 3: Alternative MySQL Port
If port 3306 conflicts:
1. Edit: `C:\xampp\mysql\bin\my.ini`
2. Change: `port=3306` to `port=3307`
3. Update PHP config accordingly

### Step 4: Run as Administrator
1. Close XAMPP
2. Right-click XAMPP Control Panel
3. Select "Run as Administrator"
4. Try starting MySQL

## 🔧 Quick Database Setup (Alternative)

If MySQL continues failing, you can use SQLite for development:
1. Update `config.php` to use SQLite
2. No separate MySQL service needed
3. Database stored as a file

Would you like me to implement the SQLite fallback option?
