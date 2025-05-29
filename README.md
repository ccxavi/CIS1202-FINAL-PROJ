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
5. Enter the name `veroDB` database name and click **Create**.

---

### 3. Create Database Table

1. On the top bar of phpMyAdmin, click on the **SQL** tab.
2. In the SQL query box, paste the following code:

```sql

CREATE TABLE users (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    profile_pic VARCHAR(255) DEFAULT NULL,
    verification_status ENUM('unverified', 'pending', 'verified', 'rejected') DEFAULT 'unverified',
    verification_document VARCHAR(255) DEFAULT NULL,
    verification_submitted_at DATETIME DEFAULT NULL,
    remember_token VARCHAR(64) DEFAULT NULL,
    token_expiry DATETIME DEFAULT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    status ENUM('active', 'banned') DEFAULT 'active'
);

CREATE TABLE articles (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    article_link TEXT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    topic VARCHAR(100),
    source_type VARCHAR(100),
    credibility VARCHAR(50),
    region VARCHAR(100),
    published_date DATE,
    created_at DATE,
    author TEXT
);

CREATE TABLE collections (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE collection_articles (
    collection_id INT(11) NOT NULL,
    article_id INT(11) NOT NULL,
    added_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (collection_id, article_id),
    FOREIGN KEY (collection_id) REFERENCES collections(id),
    FOREIGN KEY (article_id) REFERENCES articles(id),
    INDEX (article_id)
);

CREATE TABLE posts (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_hidden BOOLEAN DEFAULT FALSE,
    is_flagged BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE comments (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    post_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    parent_comment_id INT DEFAULT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_hidden BOOLEAN DEFAULT FALSE,
    is_flagged BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_comment_id) REFERENCES comments(id) ON DELETE CASCADE,
    INDEX (post_id),
    INDEX (user_id),
    INDEX (parent_comment_id)
);

CREATE TABLE comment_likes (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    comment_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (comment_id) REFERENCES comments(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_comment_like (comment_id, user_id),
    INDEX (user_id)
);

CREATE TABLE reactions (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    post_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    reaction_type ENUM('like') DEFAULT 'like',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_reaction (post_id, user_id),
    INDEX (post_id),
    INDEX (user_id)
);


SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS comment_likes;
DROP TABLE IF EXISTS comments;
DROP TABLE IF EXISTS reactions;
DROP TABLE IF EXISTS bookmarks;
DROP TABLE IF EXISTS collection_articles;
DROP TABLE IF EXISTS collections;
DROP TABLE IF EXISTS posts;
DROP TABLE IF EXISTS articles;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;
