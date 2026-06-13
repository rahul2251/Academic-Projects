# PhishShield AI — XAMPP Setup Guide

## Step 1: Start XAMPP Services

1. Open **XAMPP Control Panel**
2. Click **Start** next to **Apache**
3. Click **Start** next to **MySQL**
4. Both should show green "Running" status

---

## Step 2: Copy Project Files

1. Copy the entire `phishshield-ai/` folder to:
   ```
   C:\xampp\htdocs\phishshield-ai\
   ```
2. Final path should be: `C:\xampp\htdocs\phishshield-ai\index.php`

---

## Step 3: Import the Database

1. Open your browser and go to: `http://localhost/phpmyadmin`
2. Click **"New"** in the left sidebar to create a database
3. Name it: `phishshield_db` → Click **Create**
4. Click on `phishshield_db` in the sidebar
5. Click the **Import** tab at the top
6. Click **Choose File** → Select `phishshield-ai/database.sql`
7. Scroll down and click **Import**
8. You should see "Import has been successfully finished"

---

## Step 4: Configure Database (if needed)

Open `config/db.php` and check these settings:
```php
define('DB_HOST', 'localhost');   // Usually localhost
define('DB_USER', 'root');        // Default XAMPP user
define('DB_PASS', '');            // Default XAMPP has no password
define('DB_NAME', 'phishshield_db');
```

---

## Step 5: Set Your Gemini API Key

1. Open `config/config.php`
2. Find this line:
   ```php
   define('GEMINI_API_KEY', 'YOUR_GEMINI_API_KEY_HERE');
   ```
3. Replace `YOUR_GEMINI_API_KEY_HERE` with your actual key
4. Get a free key at: https://aistudio.google.com/

---

## Step 6: Run the Project

Open your browser and visit:
```
http://localhost/phishshield-ai/
```

---

## Default Login Credentials

### User Login
- Go to: `http://localhost/phishshield-ai/login.php`
- Email: `john@example.com`
- Password: `password`

### Admin Login
- Go to: `http://localhost/phishshield-ai/admin/login.php`
- Email: `admin@phishshield.ai`
- Password: `admin123`

---

## Project Structure Overview

```
phishshield-ai/
├── index.php              ← Landing page
├── login.php              ← User login
├── register.php           ← User registration
├── logout.php             ← Logout
├── chatbot.php            ← AI chatbot (requires login)
├── features.php           ← Features page
├── about.php              ← About page
├── contact.php            ← Contact page
├── forgot-password.php    ← Password reset
├── database.sql           ← Import this in phpMyAdmin
├── .htaccess              ← Apache config
│
├── config/
│   ├── db.php             ← Database connection
│   ├── config.php         ← Site settings + Gemini key
│   ├── gemini.php         ← Gemini API functions
│   └── auth.php           ← Session auth helpers
│
├── includes/
│   ├── header.php         ← HTML <head> + CSS
│   ├── footer.php         ← Footer + JS
│   ├── navbar.php         ← Top navigation bar
│   ├── sidebar.php        ← User dashboard sidebar
│   ├── alerts.php         ← Flash message alerts
│   └── functions.php      ← Utility functions
│
├── assets/
│   ├── css/
│   │   ├── style.css      ← Main cybersecurity theme
│   │   ├── dashboard.css  ← Dashboard styles
│   │   └── admin.css      ← Admin panel styles
│   ├── js/
│   │   ├── main.js        ← General JS
│   │   ├── charts.js      ← Chart.js helpers
│   │   ├── scanner.js     ← Scanner animations
│   │   └── chatbot.js     ← Chatbot UI (backup)
│   ├── images/            ← Place logo/images here
│   └── uploads/profile/   ← User avatar uploads
│
├── user/
│   ├── dashboard.php      ← User analytics dashboard
│   ├── url-scanner.php    ← URL phishing scanner
│   ├── email-scanner.php  ← Email analysis
│   ├── history.php        ← Scan history
│   ├── reports.php        ← Download reports
│   ├── feedback.php       ← Submit feedback
│   ├── profile.php        ← Edit profile
│   └── settings.php       ← Change password
│
├── admin/
│   ├── login.php          ← Admin login
│   ├── logout.php         ← Admin logout
│   ├── dashboard.php      ← Admin overview
│   ├── users.php          ← Manage users
│   ├── user-edit.php      ← Edit individual user
│   ├── scans-url.php      ← All URL scans
│   ├── scans-email.php    ← All email scans
│   ├── blacklist.php      ← Domain blacklist
│   ├── whitelist.php      ← Domain whitelist
│   ├── feedback.php       ← User feedback inbox
│   ├── analytics.php      ← Charts & analytics
│   ├── logs.php           ← Activity logs
│   └── settings.php       ← Admin settings
│
└── api/
    ├── scan-url.php       ← AJAX URL scan endpoint
    ├── scan-email.php     ← AJAX email scan endpoint
    ├── chatbot.php        ← AI chatbot API
    └── stats.php          ← Dashboard stats + CSV/PDF export
```

---

## Editing the UI

### Change Colors
Edit `assets/css/style.css` — look for the `:root` block at the top:
```css
:root {
    --ps-accent:  #e94560;   /* Red accent color */
    --ps-cyan:    #16c79a;   /* Green/safe color */
    --ps-dark:    #0d0d1a;   /* Dark background */
    --ps-card:    #16213e;   /* Card background */
}
```

### Add New Pages
1. Create `yourpage.php` in root or `user/`
2. Include config/auth/functions at the top
3. Include header, navbar/sidebar, footer

### Modify Detection Logic
Edit `includes/functions.php`:
- `calculate_url_risk()` for URL scanning
- `calculate_email_risk()` for email scanning

---

## Troubleshooting

| Problem | Solution |
|---------|----------|
| Blank page | Enable error display in config.php temporarily |
| DB connection error | Check db.php credentials, make sure MySQL is running |
| AI not working | Add your Gemini API key to config.php |
| Images not loading | Check that `assets/uploads/profile/` folder exists and is writable |
| Login fails | Make sure database.sql was imported correctly |
| 404 errors | Ensure mod_rewrite is enabled in XAMPP |

---

## Requirements

- XAMPP with PHP 8.0+
- Apache with mod_rewrite enabled
- MySQL 5.7+ or MariaDB
- cURL enabled in PHP (for Gemini API)
- Internet connection (for Gemini API + CDN assets)
