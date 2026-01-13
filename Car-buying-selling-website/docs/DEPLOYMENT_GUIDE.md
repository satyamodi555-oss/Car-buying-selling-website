# InfinityFree Deployment Guide

This guide will help you deploy your **Car Buying and Selling Website** to InfinityFree, a free web hosting service.

## Prerequisites
- Your project files (PHP, HTML, CSS, JS).
- The `sql` file (found in your project root) which contains the database structure.

## Step 1: Sign Up and Create an Account
1. Go to [InfinityFree](https://infinityfree.net/) and sign up for an account.
2. After verifying your email, create a **Hosting Account**.
3. Choose a **Subdomain** (e.g., `mycarsite.rf.gd`) or connect your own domain.
4. Set a label and password for the account and click **Create Account**.

## Step 2: Create the Database
1. Open the **Control Panel** (cPanel) for your new account.
2. Scroll down to the **Databases** section and click on **MySQL Databases**.
3. Under **Create New Database**, enter a name (e.g., `carsell`) and click **Create Database**.
   - Note: Your database name will have a prefix (e.g., `if0_365445_carsell`).
4. **Important**: Note down the following details displayed on this page:
   - **MySQL Host Name** (e.g., `sql106.infinityfree.com`)
   - **MySQL User Name** (e.g., `if0_365445`)
   - **MySQL Password** (Your hosting account password)
   - **MySQL Database Name** (e.g., `if0_365445_carsell`)

## Step 3: Import the Database Schema (COMPLETED ✅)
1. In the Control Panel, go to **phpMyAdmin**.
2. Click on the **Connect Now** button designated for your new database.
3. In phpMyAdmin, click on your database name in the left sidebar.
4. Click on the **Import** tab at the top.
5. Click **Choose File** and select the file named `database/schema_v2.sql` from your project folder.
6. Click **Go** at the bottom of the page.
   - You should see a success message indicating tables have been created.

## Step 4: Configure Database Connection (DONE ✅)
We have updated `includes/db.php` to automatically handle the connection for you.

1. Open `includes/db.php`.
2. Locate the "Production" section:
   ```php
   } else {
       // Production (InfinityFree)
       define('DB_HOST', 'sql106.infinityfree.com');
       define('DB_NAME', 'if0_40862261_carsell');
       define('DB_USER', 'if0_40862261');
       define('DB_PASS', 'your_vpanel_password'); // <--- IMPORTANT: Update this!
   }
   ```
3. **CRITICAL**: You must replace `'your_vpanel_password'` with your actual InfinityFree/vPanel password before uploading.

## Step 5: Upload Files
1. Go back to the **Control Panel** and click on **Online File Manager**.
2. Navigate into the `htdocs` folder. (Delete default files).
3. **Upload** all project files/folders (`index.html`, `browse_cars.php`, `includes/`, `admin/`, etc.) into `htdocs`.
   - Ensure the `uploads` folder exists and has write permissions.

## Step 6: Test Your Website
1. Open your website URL (e.g., `http://mycarsite.rf.gd`) in a browser.
2. Test the following features:
   - **Registration**: Create a new account.
   - **Login**: Log in with the account.
   - **Sell Car**: Submit a booking request.
   - **Contact**: Send a message.
3. If you see "Connection failed", double-check your `config.php` credentials.

## Troubleshooting
- **White screen or 500 Error**: Check `php_errorlog` in the file manager or enable error display in PHP settings (Control Panel -> Alter PHP Config).
- **Database Error**: Ensure the user has privileges (should be automatic) and the `config.php` matches exactly (no spaces in host name, correct password).
mihey18580@neuraxo.com