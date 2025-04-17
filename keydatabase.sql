-- Create the database if it doesn't already exist
CREATE DATABASE IF NOT EXISTS keytest;

-- Select the database to use
USE keytest;

-- Create the access_keys table
CREATE TABLE IF NOT EXISTS access_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    key_value CHAR(64) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expiration_date TIMESTAMP
);
CREATE TABLE IF NOT EXISTS used_keys (
    key_value CHAR(64) PRIMARY KEY,
    used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);