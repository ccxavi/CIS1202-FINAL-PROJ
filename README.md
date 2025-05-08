# CIS1202-FINAL-PROJ

## Database Setup Instructions

Follow the steps below to set up the database for this project:

---

### 1. Open XAMPP Control Panel

1. Open the XAMPP control panel on your local machine.
2. Start the following services:
   - **Apache**: This will run the web server.
   - **MySQL**: This will run the database server.

---

### 2. Open MySQL Admin (phpMyAdmin)

1. Open your web browser.
2. In the address bar, type `localhost` and press **Enter**.
3. Click on **phpMyAdmin** to open the MySQL admin interface.
4. In the left sidebar, click **New** to create a new database.
5. Enter the name `verifindDB` database name and click **Create**.

---

### 3. Create Database Table

1. On the top bar of phpMyAdmin, click on the **SQL** tab.
2. In the SQL query box, paste the following code:

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,     
    username VARCHAR(100) NOT NULL,          
    email VARCHAR(255) NOT NULL,             
    password VARCHAR(255) NOT NULL,          
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,  
    profile_picture VARCHAR(255) DEFAULT NULL
);

CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    content TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT,
    user_id INT,
    content TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE reactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT,
    user_id INT,
    reaction_type ENUM('like') DEFAULT 'like',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);