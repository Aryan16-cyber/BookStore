# 📚 BookHaven — Online Bookstore
### BCA Project | Aryan | Enrollment No: 2353701350 | IGNOU

---

## 🛠️ Tech Stack
| Layer      | Technology                  |
|------------|-----------------------------|
| Frontend   | HTML5, CSS3, JavaScript     |
| Backend    | PHP 7/8                     |
| Database   | MySQL 5/8                   |
| Server     | Apache (XAMPP / WAMP / LAMP)|

---

## 📁 Project Structure

```
bookhaven/
├── index.php               ← Homepage
├── shop.php                ← Browse & search books
├── book.php                ← Book detail page
├── cart.php                ← Shopping cart (add/update/remove)
├── checkout.php            ← Checkout & order placement
├── login.php               ← Customer/Admin login
├── register.php            ← Customer registration
├── profile.php             ← Customer profile & order history
├── logout.php              ← Session destroy
├── database.sql            ← Full DB schema + seed data
│
├── includes/
│   ├── db.php              ← MySQL connection
│   ├── auth.php            ← Session & auth helpers
│   ├── header.php          ← Nav + HTML head
│   ├── footer.php          ← Footer + closing tags
│   └── book_card.php       ← Reusable book card component
│
├── admin/
│   ├── header.php          ← Admin nav
│   ├── index.php           ← Admin dashboard
│   ├── books.php           ← Add/Edit/Delete books
│   ├── orders.php          ← View & update orders
│   ├── customers.php       ← Manage customers
│   ├── categories.php      ← Manage book categories
│   └── reports.php         ← Revenue & analytics reports
│
└── assets/
    ├── css/style.css       ← Main stylesheet
    └── js/main.js          ← Frontend JS
```

---

## ⚡ Installation (XAMPP — Windows)

### Step 1 — Install XAMPP
Download from: https://www.apachefriends.org/
Start **Apache** and **MySQL** in the XAMPP Control Panel.

### Step 2 — Copy Project
Copy the entire `bookhaven/` folder to:
```
C:\xampp\htdocs\bookhaven\
```

### Step 3 — Create Database
1. Open your browser → go to `http://localhost/phpmyadmin`
2. Click **New** → name it `bookhaven` → click **Create**
3. Click the `bookhaven` database → go to **Import** tab
4. Choose the file `bookhaven/database.sql` → click **Go**

### Step 4 — Configure DB (if needed)
Edit `includes/db.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');      // your MySQL username
define('DB_PASS', '');          // your MySQL password (blank for XAMPP default)
define('DB_NAME', 'bookhaven');
```

### Step 5 — Run
Open browser → `http://localhost/bookhaven/`

---

## 🔑 Default Login Credentials

| Role     | Email                    | Password   |
|----------|--------------------------|------------|
| Admin    | admin@bookhaven.com      | password   |
| Customer | Register a new account   | —          |

> **Important:** After first login, change the admin password from the database or update the hash using:
> ```php
> echo password_hash('your_new_password', PASSWORD_DEFAULT);
> ```

---

## ✅ Features

### Customer Side
- 🏠 **Homepage** — Hero banner, featured books, bestsellers, category chips
- 🔍 **Browse/Shop** — Search, filter by genre, sort by price/rating
- 📖 **Book Detail** — Full info, quantity selector, stock status, related books
- 🛒 **Cart** — Add, update quantity, remove items, clear cart
- 💳 **Checkout** — Shipping form, payment method selection, order confirmation
- 👤 **Profile** — Edit profile, change password, view order history
- 🔐 **Auth** — Secure login with `password_verify()`, registration with validation

### Admin Panel (`/admin/`)
- 📊 **Dashboard** — Live stats, recent orders, top books, alert badges
- 📚 **Books** — Add new books (with all fields), inline edit price/stock/badge, delete
- 📦 **Orders** — View all orders, filter by status, update order status
- 👥 **Customers** — View all customers with order count and total spent, remove
- 🏷️ **Categories** — Add/delete book genres
- 📈 **Reports** — Revenue summary, order status breakdown, monthly revenue, top books & customers

---

## 🗃️ Database Tables

| Table         | Description                            |
|---------------|----------------------------------------|
| `users`       | Customer & admin accounts              |
| `categories`  | Book genres/categories                 |
| `books`       | Book catalog with stock & metadata     |
| `cart`        | User shopping cart items               |
| `orders`      | Placed orders with status              |
| `order_items` | Individual items within each order     |
| `payments`    | Payment records per order              |

---

## 🔒 Security Features
- Passwords hashed with `password_hash()` / verified with `password_verify()`
- SQL Injection prevented via `mysqli_real_escape_string()` and prepared statements
- Session-based authentication with role checking (`customer` / `admin`)
- Access control: Admin pages require `requireAdmin()`, cart/checkout require `requireLogin()`
- Input sanitization on all POST data

---

*Submitted to the School of Computer and Information Sciences, IGNOU*
*in partial fulfilment of the requirement for the award of BCA Degree*
