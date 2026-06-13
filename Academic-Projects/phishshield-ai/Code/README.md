# PhishShield AI – Setup Guide

A hybrid phishing detection system using PHP + MySQL + Google Gemini 2.5 Flash.

---

## 1. Install in XAMPP

1. Copy the entire `phishshield-ai` folder into:

   ```
   C:/xampp/htdocs/phishshield-ai/
   ```

2. Start **Apache** and **MySQL** in the XAMPP Control Panel.

   ⚠ This project expects **MySQL on port 3307** (not the default 3306).

---

## 2. Import the database

1. Open phpMyAdmin:

   ```
   http://localhost/phpmyadmin
   ```

   (If your phpMyAdmin runs against port 3307, use it; otherwise log in with your MySQL admin credentials.)

2. Click **Import** → choose `database.sql` from the project folder → click **Go**.

   This creates the `phishshield_db` database and all tables, plus the default admin account.

---

## 3. Paste your Gemini API key

Open `config/config.php` and replace:

```php
$gemini_api_key = "PASTE_YOUR_GEMINI_API_KEY_HERE";
```

Get a free key at https://aistudio.google.com/app/apikey.

The app uses model `gemini-2.5-flash`.

---

## 4. Run the project

In your browser visit:

```
http://localhost/phishshield-ai/
```

- **User**: register a new account, then log in.
- **Admin**: go to `http://localhost/phishshield-ai/login.php?admin=1`
  - Email: `admin@phishshield.ai`
  - Password: `admin123`

---

## 5. Database credentials

Configured in `config/db.php`:

```
Host:     localhost
Port:     3307
Database: phishshield_db
User:     root
Password: machine317
```

---

## 6. Troubleshooting MySQL port 3307

If you see **"Database connection failed"**:

1. Open `C:/xampp/mysql/bin/my.ini` and confirm:

   ```
   [mysqld]
   port=3307
   ```

2. In XAMPP Control Panel → MySQL → **Config → my.ini**, ensure the port is 3307.
3. Restart MySQL.
4. Make sure no other MySQL instance is using 3307 (`netstat -ano | findstr 3307`).
5. If your phpMyAdmin can't connect either, edit `C:/xampp/phpMyAdmin/config.inc.php`:

   ```php
   $cfg['Servers'][$i]['host'] = '127.0.0.1';
   $cfg['Servers'][$i]['port'] = '3307';
   ```

If the password `machine317` differs on your machine, update it in `config/db.php`.

---

## 7. Folder structure

```
phishshield-ai/
├── index.php          login.php   register.php   logout.php
├── dashboard.php      scanner.php history.php    chatbot.php
├── feedback.php       settings.php
├── admin.php          reports.php
├── database.sql       .htaccess
├── config/   db.php config.php gemini.php auth.php functions.php
├── includes/ header.php footer.php navbar.php sidebar.php
└── assets/   css/ js/ images/ uploads/
```

Enjoy 🛡️
