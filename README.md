# ✨ Glowly — Skincare E-Commerce Website

Glowly is a modern skincare e-commerce web application built using PHP, MySQL, HTML, CSS, and JavaScript.  
The platform provides a realistic online shopping experience with authentication, email verification, cart management, simulated payment gateway integration, and order confirmation system.

# 🚀 Features

## 👤 User Authentication
- User Signup & Signin
- Email Verification via OTP
- Secure Password Hashing
- Session-based Authentication

## 🛍️ Shopping System
- Browse skincare products
- Add to Cart
- Dynamic Cart Management
- Quantity Handling
- Empty Cart Handling

## 💳 Simulated Payment Gateway
- UPI Payment UI
- Credit Card Payment UI
- Debit Card Payment UI
- Cash on Delivery
- OTP Verification for Card Payments
- Simulated Transaction IDs

## 📦 Order Management
- Place Orders
- Order Summary
- Payment Success Page
- Order Confirmation Email
- Cart Auto-Clear After Purchase

## 💌 Email System
- Email Verification OTP
- Payment OTP
- Order Confirmation Emails
- SMTP Integration using Brevo + PHPMailer

## 🎨 Modern UI
- Responsive Design
- Animated Components
- Ecommerce Style Product Cards
- Modern Checkout Experience

# 🛠️ Tech Stack

| Technology | Usage         |
|------------|---------------|
| PHP        | Backend       |
| MySQL      | Database      |     
| HTML5      | Structure     |
| CSS3       | Styling       |
| JavaScript | Interactivity |
| PHPMailer  | Email Sending |
| Brevo SMTP | Email Service |
| XAMPP/LAMPP| Local Server  |

# 📂 Project Structure

Glowly/
│
├── assets/
│   └── images/
│
├── config/
│   ├── db.php
│   └── mail.php
│
├── vendor/
│
├── index.php
├── signup.php
├── signin.php
├── verify.php
├── dashboard.php
├── shop.php
├── checkout.php
├── payment-otp.php
├── payment-success.php
├── logout.php
└── cart-empty.php

⚙️ Installation & Setup

1️⃣ Clone Repository
git clone https://github.com/sanika-kulkarni-sel/Glow_Beauty.git

2️⃣ Move Project to XAMPP/LAMPP htdocs
sudo mv Glow_Beauty /opt/lampp/htdocs/

3️⃣ Start Apache & MySQL
sudo /opt/lampp/lampp start

4️⃣ Create Database

Open:

http://localhost/phpmyadmin

Create database:

CREATE DATABASE glowly;

5️⃣ Import Tables

Run all SQL table commands provided in the project setup.

6️⃣ Install PHPMailer

Inside project folder:

composer install

7️⃣ Configure Database

Update: config/db.php

Example:

$host = "127.0.0.1";
$username = "root";
$password = "";
$database = "glowly";

8️⃣ Configure SMTP

Update: config/mail.php

Add your Brevo SMTP credentials.

▶️ Run Project

Open browser:

http://localhost/Glowly
