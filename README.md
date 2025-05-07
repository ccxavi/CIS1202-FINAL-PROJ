# CIS1202-FINAL-PROJ

## Database Setup Instructions

Follow the steps below to set up the database for this project:

### 1. Open Xampp Control Panel

Start Apache and MySql

### 2. Open MySql Admin

Go to any search engine and type "localhost"
Click "phpMyAdmin"
On the left side, find "New" and click it
Enter "verifindDB" for Database name and click "create"
On top bar, go to "SQL"
Copy the code below and click "go" at the bottom part

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,     
    username VARCHAR(100) NOT NULL,          
    email VARCHAR(255) NOT NULL,             
    password VARCHAR(255) NOT NULL,          
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,  
    profile_picture VARCHAR(255) DEFAULT NULL
);

