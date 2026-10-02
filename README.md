<div align="center">

# 🚗 Car Rental Management System


</div>

---

## 📖 About

**Car Rental Management System** is a complete web application designed for rental businesses to manage their fleet, customers, bookings, and payments — all from a single, beautiful dashboard.

Built with **pure PHP** (no frameworks) and **MySQL**, this project emphasizes **clean code, security best practices, and modern UI/UX** with smooth animations and a glassmorphism-inspired design.

> 💡 **Perfect for:** Learning PHP/MySQL, portfolio projects, small-to-medium rental businesses, or as a base for a SaaS product.

---

## ✨ Features

### 🔐 Authentication & Security
- Secure login with **bcrypt password hashing**
- **Session management** with regeneration (prevents hijacking)
- **Role-based access control** (Admin / Staff)
- **SQL injection prevention** via prepared statements
- **XSS protection** with HTML escaping
- CSRF-safe forms

### 🚗 Car Management
- Add, edit, delete cars
- Track status: **Available / Rented / Maintenance**
- Search, filter & sort by any column
- Pagination for large datasets
- CSV export + Print support

### 👥 Customer Management
- Full CRUD for customers
- Track rental history & total spending per customer
- Search by name, phone, email, license

### 🔑 Rental Operations
- One-click **Rent Car** with transaction safety
- **Return Car** with automatic total calculation (per-day pricing)
- Overdue alerts (7+ days)
- Invoice generation with print/PDF export

### 💳 Payment Tracking
- Mark rentals as **Paid / Unpaid / Refunded**
- Track payment method & date
- Unpaid revenue overview on dashboard

### 📊 Analytics & Charts
- Revenue trend (last 6 months) — line chart
- Fleet status breakdown — doughnut chart
- Top 5 most rented cars — horizontal bar chart
- Dashboard with 8 stat cards

### 🔔 Notifications
- Real-time bell icon with unread badge
- Auto-generated overdue alerts
- Mark all as read
- AJAX-powered (no page reload)

### 🔍 Advanced Reports
- Date range reports
- Monthly / Quarterly / Yearly breakdowns
- Per-car & per-customer performance
- Export-ready data tables

### 👤 User Management
- Admin panel for managing users
- Create/edit/delete staff or admin accounts
- Track last login timestamps

### 🎨 UI/UX
- **Modern glassmorphism design**
- **Smooth animations** (fade-in, slide, hover, ripple)
- **🌙 Dark Mode** with DB persistence
- **Toast notifications** (elegant flash messages)
- Fully **responsive** (mobile-friendly)
- Custom gradient scrollbar

---



## 🛠 Tech Stack

| Category | Technology |
|----------|-----------|
| **Backend** | PHP 7.4+ (Vanilla) |
| **Database** | MySQL / MariaDB |
| **Frontend** | HTML5, CSS3, Vanilla JS |
| **Charts** | Chart.js 4.x |
| **Icons** | Emoji + Unicode |
| **Server** | Apache (XAMPP / WAMP / MAMP) |

---

## 🚀 Installation

### Prerequisites
- PHP **7.4** or higher
- MySQL **5.7** or higher
- Apache server (XAMPP / WAMP / MAMP / Laragon)

### Step 1 — Clone the repository

```bash
cd C:\xampp\htdocs
https://github.com/dilshankumara001001/-Car-Rental-Management-System
cd car-rental-system
```

### Step 2 — Import the database

1. Open **phpMyAdmin** → `http://localhost/phpmyadmin`
2. Click **Import** tab
3. Choose file: `database/car_rental_db.sql`
4. Click **Go**

Or run from CLI:
```bash
mysql -u root -p < database/car_rental_db.sql
```

### Step 3 — Configure database credentials

Open `config/config.php` and update:

```php
define('BASE_URL', '/car-rental-system/');  // your folder name
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'car_rental_db');
```

### Step 4 — Create the admin user

Open in browser:
```
http://localhost/car-rental-system/setup.php
```

Enter your admin & staff passwords → click **Create Users** → then **delete `setup.php`**.

**Default credentials:**
| Username | Password | Role |
|----------|----------|------|
| `admin`  | `admin123` | Admin |
| `staff`  | `staff123` | Staff |

> ⚠️ **Change these immediately after first login!**

### Step 5 — Launch 🎉

```
http://localhost/car-rental-system/
```

---

## 📁 Project Structure

```
car-rental-system/
├── assets/
│   └── css/
│       └── style.css              # World-class UI + dark mode
├── config/
│   ├── config.php                 # BASE_URL + DB constants
│   ├── db.php                     # MySQL connection
│   └── auth.php                   # require_login() / require_admin()
├── includes/
│   ├── functions.php              # 30+ helper functions
│   └── header.php                 # Nav bar + notifications + theme
├── auth/
│   ├── login.php
│   └── logout.php
├── cars/
│   ├── add_car.php
│   ├── edit_car.php
│   ├── delete_car.php
│   └── view_car.php
├── customers/
│   ├── add_customer.php
│   └── view_customer.php
├── rentals/
│   ├── rent_car.php
│   ├── return_car.php
│   ├── view_rentals.php
│   └── invoice.php                # Printable invoice
├── users/
│   ├── manage_users.php
│   ├── add_user.php
│   ├── edit_user.php
│   └── delete_user.php
├── database/
│   └── car_rental_db.sql          # Full DB + sample data
├── notifications_api.php          # AJAX endpoint
├── reports.php                    # Advanced reports
├── index.php                      # Dashboard
└── README.md
```

---

## 🎯 Usage

### Login as Admin
- Full access to all modules
- Manage users
- Delete cars/customers

### Login as Staff
- Rent / return cars
- Add customers
- View reports
- **Cannot** delete or manage users

### Typical Workflow

```
1. Add Cars         → Cars → + Add Car
2. Add Customer     → Customers → + Add
3. Rent a Car       → Rentals → Rent → select car + customer
4. Return a Car     → Rentals → Return → select rental (auto-calculates total)
5. Mark Paid        → Rentals table → 💰 button
6. Generate Invoice → Rentals → 🧾 icon → Print/Save as PDF
7. View Reports     → Reports → choose Range / Monthly / Quarterly / Yearly
```

---

## 🔒 Security Highlights

| Threat | Mitigation |
|--------|-----------|
| SQL Injection | ✅ Prepared statements (everywhere) |
| XSS | ✅ `htmlspecialchars()` on all output |
| Password theft | ✅ Bcrypt hashing |
| Session hijacking | ✅ `session_regenerate_id(true)` |
| Unauthorized access | ✅ `require_login()` / `require_admin()` guards |
| CSRF (basic) | ✅ POST-only state changes |
| Duplicate actions | ✅ DB transactions |

---

## 🗺 Roadmap

- [x] Core CRUD (cars, customers, rentals)
- [x] Authentication & roles
- [x] Payment tracking
- [x] Charts & dashboard
- [x] Notifications
- [x] Dark mode
- [x] Invoice / PDF
- [x] Advanced reports
- [x] User management
- [ ] 📧 Real email notifications (PHPMailer)
- [ ] 📱 SMS alerts (Twilio / Dialog API)
- [ ] 💳 Online payments (Stripe / PayPal)
- [ ] 🖼 Car image upload
- [ ] 📅 Booking calendar
- [ ] 🌐 Multi-language support
- [ ] 🔗 REST API for mobile app

---

## 🤝 Contributing

Contributions are welcome! Here's how:

1. **Fork** the repo
2. Create your branch: `git checkout -b feature/AmazingFeature`
3. Commit: `git commit -m 'Add some AmazingFeature'`
4. Push: `git push origin feature/AmazingFeature`
5. Open a **Pull Request**

Please follow **[PSR-12](https://www.php-fig.org/psr/psr-12/)** coding standards.

---

## 📝 License

Distributed under the **MIT License**. See `LICENSE` for more information.

---


## ⭐ Show Your Support

If this project helped you, please give it a ⭐️ **star**!

<div align="center">

**Made with ❤️ using PHP & MySQL**

[⬆ Back to Top](#-car-rental-management-system)

</div>
