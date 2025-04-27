CREATE DATABASE projectDB;
USE projectDB;

CREATE USER 'android_user'@'%' IDENTIFIED BY 'mysql_password';
GRANT ALL PRIVILEGES ON your_database.* TO 'android_user'@'%';
FLUSH PRIVILEGES;

CREATE TABLE users(
    user_ID INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    account_status ENUM('Enable','Disable') DEFAULT 'Enable',
    user_role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE news (
    news_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    news_date DATE NOT NULL,
    content TEXT NOT NULL,
    question TEXT NOT NULL,
    option_a VARCHAR(255) NOT NULL,
    option_b VARCHAR(255) NOT NULL,
    option_c VARCHAR(255) NOT NULL,
    correct_answer enum('option_a','option_b','option_c') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by INT NULL,
    FOREIGN KEY (updated_by) REFERENCES users(user_ID) ON DELETE SET NULL
);

CREATE TABLE map_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    worksite_id INT NOT NULL,
    question TEXT NOT NULL,
    option_a VARCHAR(255) NOT NULL,
    option_b VARCHAR(255) NOT NULL,
    option_c VARCHAR(255) NOT NULL,
    correct_answer enum('option_a','option_b','option_c') NOT NULL,
    CONSTRAINT fk_user FOREIGN KEY (user_id) REFERENCES users(user_ID) ON UPDATE CASCADE,
    CONSTRAINT fk_worksite FOREIGN KEY (worksite_id) REFERENCES worksites(worksite_id) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE alert_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type_name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE alert_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    alert_type_id INT NOT NULL,
    alert_message TEXT NOT NULL,
    alert_time DATETIME NOT NULL,
    alert_end_time DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_ID),
    FOREIGN KEY (alert_type_id) REFERENCES alert_types(id)
);

CREATE TABLE worksites (
    worksite_id INT AUTO_INCREMENT PRIMARY KEY,
    worksite_name VARCHAR(255),
    latitude DECIMAL(8, 6),
    longitude DECIMAL(9, 6)
);


CREATE TABLE check_in_record (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    worksite_id INT NOT NULL,
    checkIn_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_ID),
    FOREIGN KEY (worksite_id) REFERENCES worksites(worksite_id)
);