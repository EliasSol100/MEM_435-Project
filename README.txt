UniTrade CY - Setup Guide for XAMPP

1. Extract/copy the entire UniTradeCY folder into:
   C:\xampp\htdocs\UniTradeCY

2. Open phpMyAdmin:
   http://localhost/phpmyadmin

3. Import the file:
   schema.sql

4. Check database settings in:
   includes/config.php

   Default values are:
   DB_HOST = localhost
   DB_USER = root
   DB_PASS = (empty)
   DB_NAME = unitrade_cy

5. Start Apache and MySQL in XAMPP.

6. Open the website in your browser:
   http://localhost/UniTradeCY/

Main Features Included:
- Register / Login / Logout
- Home page
- Browse listings with filters
- Create listing
- Edit / delete listing
- Wishlist
- Seller profile
- Reviews / rating system
- File image upload or external image URL

Notes:
- Uploaded images are stored in the "uploads" folder.
- The project uses mysqli prepared statements.
- If you want, you can later extend it with admin panel, chat system, and verified .ac.cy emails.


Optional Demo Data:
- After importing schema.sql, you can also import demo_data.sql
- Demo login 1: elias@example.com / password123
- Demo login 2: anna@example.com / password123
