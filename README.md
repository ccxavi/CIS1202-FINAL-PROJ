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
    profile_pic VARCHAR(255) DEFAULT NULL
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
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE bookmarks (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    article_id INT(11) NOT NULL,
    collection_id INT(11) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (article_id) REFERENCES articles(id),
    FOREIGN KEY (collection_id) REFERENCES collections(id),
    INDEX (article_id),
    INDEX (collection_id)
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
    user_id INT(11),
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE comments (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    post_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    parent_comment_id INT(11),
    content TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (parent_comment_id) REFERENCES comments(id),
    INDEX (post_id),
    INDEX (user_id),
    INDEX (parent_comment_id)
);

CREATE TABLE comment_likes (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    comment_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (comment_id) REFERENCES comments(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    UNIQUE KEY unique_comment_like (comment_id, user_id),
    INDEX (user_id)
);

CREATE TABLE reactions (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    post_id INT(11),
    user_id INT(11),
    reaction_type ENUM('like') DEFAULT 'like',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX (post_id),
    INDEX (user_id)
);