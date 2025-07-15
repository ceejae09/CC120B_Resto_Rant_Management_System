# 🍽️ Resto Rant Management System

This project is a **Restaurant and Rage Room Management System**, supporting room bookings, food orders, testimonials, and user transactions. Below is a full reference of the SQL database schema used in this project.

---

## 📦 Database Initialization

```sql
CREATE DATABASE resto_rant_management_system;
USE resto_rant_management_system;
```

---

## 🏨 Table: `rage_rooms`

Stores information about rooms available for booking or rage room use.

```sql
CREATE TABLE rage_rooms (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    description TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
    room_type VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
    price DECIMAL(10,2),
    image_path VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    props TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
    status VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'available',
    PRIMARY KEY (id)
);
```

---

## 🍔 Table: `resto_menu`

Stores all menu items available to order.

```sql
CREATE TABLE resto_menu (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    description TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
    category ENUM('Meal', 'Drink', 'Snack', 'Dessert', 'Other') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    image_path VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    photo VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
    PRIMARY KEY (id)
);
```

---

## 👥 Table: `users`

Contains user accounts and profiles.

```sql
CREATE TABLE users (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
    email VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
    phone VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
    address VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
    username VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
    password TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
    role TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    PRIMARY KEY (id),
    INDEX (username)
);
```

---

## 💳 Table: `transactions`

Captures all room booking transactions.

```sql
CREATE TABLE transactions (
    transaction_id INT(11) NOT NULL AUTO_INCREMENT,
    username VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    phone_number VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
    room_id INT(11) NOT NULL,
    room_name VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    date_to_avail DATE NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Pending',
    PRIMARY KEY (transaction_id)
);
```

---

## 🧽 Table: `ordered_foods`

Tracks food orders tied to transactions.

```sql
CREATE TABLE ordered_foods (
    id INT(11) NOT NULL AUTO_INCREMENT,
    transaction_id INT(11),
    username VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
    room_id INT(11),
    room_name VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
    food_id INT(11),
    food_name VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
    quantity INT(11),
    total_price DECIMAL(10,2),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);
```

---

## 📜 Table: `history_transactions`

Archive of completed transactions for reporting or audit.

```sql
CREATE TABLE history_transactions (
    transaction_id INT PRIMARY KEY,
    username VARCHAR(100),
    room_id INT,
    room_name VARCHAR(255),
    price DECIMAL(10,2),
    date_to_avail DATE,
    created_at DATETIME,
    status VARCHAR(50),
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## 💬 Table: `testimonials`

User-submitted testimonials with ratings and timestamps.

```sql
CREATE TABLE testimonials (
    id INT(11) NOT NULL AUTO_INCREMENT,
    username VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    rating INT(1) NOT NULL CHECK (rating BETWEEN 1 AND 5),
    message TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (username) REFERENCES users(username)
);
```

---

## 🛠 Recommendations

- Always hash passwords using strong algorithms like `bcrypt`.
- Sanitize user inputs and escape queries to prevent SQL injection.
- Use `FOREIGN KEY` constraints to enforce data relationships (optional for now).
- Add `UNIQUE` constraints on usernames and emails in `users`.

---

## 📈 Future Enhancements

- Add `updated_at` and `deleted_at` timestamps for better record tracking.
- Normalize tables to reduce redundant data.
- Implement triggers or stored procedures for automatic archiving.

📋 Table: order_receipts
Stores food order receipts for active or pending transactions.

```sql
CREATE TABLE order_receipts (
    id INT(11) NOT NULL AUTO_INCREMENT,
    username VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
    room_id INT(11),
    room_name VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
    summary TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
    total DECIMAL(10,2),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Pending', 'Done') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Pending',
    PRIMARY KEY (id)
);



📋 Table: history_order_receipts
Stores archived or historical records of completed food orders along with summaries.

CREATE TABLE history_order_receipts (
    id INT(11) NOT NULL AUTO_INCREMENT,
    username VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    room_id INT(11) NOT NULL,
    room_name VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    status VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    created_at DATETIME NOT NULL,
    summary TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    completed_at DATETIME,
    PRIMARY KEY (id)
);
```
