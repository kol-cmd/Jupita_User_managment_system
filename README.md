# Jupita User Management System

A full-stack User Management Application with Role-Based Access Control (RBAC) and a shared Notes feature. Built using **Vanilla PHP**, **MySQL**, and **Vue.js 3** (Composition API).

## 🚀 Features
* **User CRUD:** Complete Create, Read, Update, and Delete functionality for users.
* **Authentication:** Secure Login and Registration system.
* **Role-Based Access Control:**
    * **Admins:** Can manage all users, ban accounts, and delete any note.
    * **Users:** Can only manage their own profile and create/edit their own notes.
* **Shared Notes Feed:** A relational feature allowing users to post and share notes.
* **Security:**
    * SQL Injection protection via **PDO Prepared Statements**.
    * **Bcrypt** password hashing.
    * **XSS** protection via input sanitization.

## 🛠️ Tech Stack
* **Backend:** PHP 8.0+ (Vanilla, no frameworks)
* **Database:** MySQL
* **Frontend:** Vue.js 3 (via CDN), Tailwind CSS (via CDN)
* **HTTP Client:** Axios

---

## ⚙️ Setup Instructions

### 1. Prerequisites
Ensure you have a local server environment installed (e.g., **XAMPP**, **WAMP**, or **MAMP**) that runs PHP and MySQL.

### 2. Installation
1.  **Clone the repository** (or unzip the project folder) into your server's root directory (e.g., `htdocs` or `www`).
    ```bash
    git clone [https://github.com/YOUR_USERNAME/jupita-user-management.git](https://github.com/YOUR_USERNAME/jupita-user-management.git)
    ```

2.  **Database Setup:**
    * Open your MySQL tool (e.g., phpMyAdmin).
    * Create a new database named `jupita_db`.
    * Import the `database.sql` file provided in the root directory of this project.
    * *This will create the `users` and `notes` tables and insert a default Super Admin.*

3.  **Configuration:**
    * Open `myapi/db.php`.
    * Update the database credentials if yours differ from the defaults:
        ```php
        $username = "root";
        $password = ""; // Your MySQL password
        ```

4.  **Run the App:**
    * Open your browser and navigate to the project folder:
        `http://localhost/jupita-user-management/`

---

## 🔑 Default Credentials
You can use the pre-seeded Admin account to test full functionality:

* **Email:** `admin@jupita.com`
* **Password:** `password123`

---

## 🗄️ Database Schema
The project uses a relational schema with a one-to-many relationship between Users and Notes.

**1. Users Table**
| Column | Type | Notes |
| :--- | :--- | :--- |
| `id` | INT | Primary Key, Auto Increment |
| `name` | VARCHAR(100) | Full Name |
| `email` | VARCHAR(100) | Unique |
| `password` | VARCHAR(255) | Hashed (Bcrypt) |
| `role` | ENUM | 'admin', 'user' |
| `status` | ENUM | 'active', 'banned' |

**2. Notes Table**
| Column | Type | Notes |
| :--- | :--- | :--- |
| `id` | INT | Primary Key |
| `user_id` | INT | Foreign Key (Linked to Users) |
| `title` | VARCHAR(150) | |
| `content` | TEXT | |

---

## ⚖️ Assumptions & Trade-offs

### 1. Vue.js via CDN (No Build Step)
* **Decision:** I chose to use Vue.js via CDN rather than the CLI (Vite/Webpack).
* **Reasoning:** This simplifies the review process for the assessment. The reviewer can run the app immediately on any PHP server without needing to run `npm install` or configure a Node.js environment.
* **Trade-off:** In a large-scale production app, I would use a build step for better tree-shaking and component modularity.

### 2. Client-Side Session Management
* **Decision:** The application stores the active user state in `localStorage` and sends the User ID with requests for verification.
* **Reasoning:** This allows for a stateless, lightweight REST API architecture that is easy to demonstrate.
* **Trade-off:** For a high-security production environment, I would implement **JWT (JSON Web Tokens)** or server-side PHP Sessions (`$_SESSION`) to prevent ID spoofing via API tools.

### 3. Vanilla PHP vs. Framework
* **Decision:** Used raw PHP without a framework (like Laravel).
* **Reasoning:** To demonstrate a strong understanding of core engineering concepts: Database connections (PDO), HTTP headers, CORS handling, and raw SQL queries, rather than relying on framework abstraction.