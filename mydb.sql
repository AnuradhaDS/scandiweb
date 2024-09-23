CREATE DATABASE my_database;

USE my_database;

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sku VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    type VARCHAR(50) NOT NULL,
    size INT DEFAULT NULL,
    weight DECIMAL(10, 2) DEFAULT NULL,
    height INT DEFAULT NULL,
    width INT DEFAULT NULL,
    length INT DEFAULT NULL
);

-- Insert sample DVD
INSERT INTO products (sku, name, price, type, size) VALUES ('DVD001', 'Inception', 9.99, 'DVD', 700);

-- Insert sample Book
INSERT INTO products (sku, name, price, type, weight) VALUES ('BOOK001', 'Harry Potter', 15.00, 'Book', 1.2);

-- Insert sample Furniture
INSERT INTO products (sku, name, price, type, height, width, length) VALUES ('FURN001', 'Dining Table', 200.00, 'Furniture', 75, 120, 200);
