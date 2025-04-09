CREATE DATABASE agrimarket;
USE agrimarket;

-- User Table 
CREATE TABLE user (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    username VARCHAR(255) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('Customer', 'Vendor', 'Staff', 'Admin') NOT NULL,
    phone_number VARCHAR(20) UNIQUE NOT NULL,
    home_address VARCHAR(200) NOT NULL,
    last_online DATETIME
);

-- Subscription Table 
CREATE TABLE subscription (
    subscription_id INT AUTO_INCREMENT PRIMARY KEY,
    plan_name VARCHAR(50) NOT NULL,
    subscription_price DECIMAL(10,2) NOT NULL,
    upload_limit INT NOT NULL,
    has_low_stock_alert BOOLEAN DEFAULT FALSE,
    has_staff_support BOOLEAN DEFAULT FALSE,
    has_analytics_access BOOLEAN DEFAULT FALSE
);

-- Category Table
CREATE TABLE category (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(255) NOT NULL
);

-- Discount Table
CREATE TABLE discount (
    discount_id INT AUTO_INCREMENT PRIMARY KEY,
    discount_code VARCHAR(50) UNIQUE NOT NULL,
    discount_percentage DECIMAL(5,2) NOT NULL,
    min_amount_purchase DECIMAL(10,2) NOT NULL
);

-- Orders Table 
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    order_date DATE,
    delivery_date DATE,
    shipping_address VARCHAR(200) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE
);

-- Vendor Table
CREATE TABLE vendor (
    vendor_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subscription_id INT,
    store_name VARCHAR(255) NOT NULL,
    subscription_start_date DATE,
    subscription_end_date DATE,
    staff_assisstance_id INT NULL,
    FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE,
    FOREIGN KEY (subscription_id) REFERENCES subscription(subscription_id) ON DELETE SET NULL
);

-- Product Table
CREATE TABLE product (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    vendor_id INT,
    category_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    product_image VARCHAR(255) NOT NULL,
    description VARCHAR(255) NOT NULL,
    stock_quantity INT NOT NULL,
    weight DECIMAL(10,2),
    unit_price DECIMAL(10,2) NOT NULL,
    product_status ENUM('Approved', 'Pending', 'Rejected') NOT NULL,
    sold_quantity INT DEFAULT 0,
    FOREIGN KEY (vendor_id) REFERENCES vendor(vendor_id) ON DELETE SET NULL,
    FOREIGN KEY (category_id) REFERENCES category(category_id) ON DELETE CASCADE
);

-- Product Order Table
CREATE TABLE product_order (
    product_id INT NOT NULL,
    order_id INT NOT NULL,
    quantity INT NOT NULL,
    sub_price DECIMAL(10,2) NOT NULL,
    packaging ENUM('Normal', 'More Protection') NOT NULL,
    status ENUM('Completed', 'Refunded') NOT NULL,
    PRIMARY KEY (product_id, order_id),
    FOREIGN KEY (product_id) REFERENCES product(product_id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE
);

-- Payment Table 
CREATE TABLE payment (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('Credit Card', 'Bank Transfer', 'Cash On Delivery') NOT NULL, /*Add Yourself*/
    payment_status ENUM('Pending', 'Completed', 'Refunded') NOT NULL,
    transaction_date DATETIME,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE
);

-- Shipment Table 
CREATE TABLE shipment (
    shipping_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    tracking_number VARCHAR(255) UNIQUE NOT NULL,
    status ENUM('Pending', 'Shipped', 'Delivered', 'Cancelled') NOT NULL,
    update_timestamp DATETIME,
    estimated_delivery_date DATE,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE
);

-- Refund Table
CREATE TABLE refund (
    refund_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    order_id INT NOT NULL,
    payment_id INT NOT NULL,
    user_id INT NOT NULL,
    refund_amount DECIMAL(10,2) NOT NULL,
    refund_date DATE NOT NULL,
    refund_status ENUM('Pending', 'Approved', 'Rejected') NOT NULL,
    reason VARCHAR(255),
    refund_approve_date DATE,
    approve_by INT,
    FOREIGN KEY (product_id) REFERENCES product(product_id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (payment_id) REFERENCES payment(payment_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE,
    FOREIGN KEY (approve_by) REFERENCES user(user_id) ON DELETE SET NULL
);

-- Review Table 
CREATE TABLE review (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    review_description VARCHAR(244),
    review_date DATE,
    FOREIGN KEY (product_id) REFERENCES product(product_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE
);

-- Customer Order History Table
CREATE TABLE customer_order_history (
    customer_order_history_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    status ENUM('Completed', 'Refunded') NOT NULL,
    order_date DATE NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE
);

-- Promotion Table 
CREATE TABLE promotion (
    promotion_id INT AUTO_INCREMENT PRIMARY KEY,
    discount_id INT NOT NULL,
    promotion_title VARCHAR(255) NOT NULL,
    promotion_message VARCHAR(200) NOT NULL,
    promotion_start_date DATE NOT NULL,
    promotion_end_date DATE NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT NOT NULL,
    FOREIGN KEY (discount_id) REFERENCES discount(discount_id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES user(user_id) ON DELETE CASCADE
);

-- Request Table 
CREATE TABLE request (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    vendor_id INT NOT NULL,
    request_description VARCHAR(200),
    request_type ENUM('Feature Request', 'Account Issue', 'Technical Support', 'Billing Inquiry', 'General Inquiry') NOT NULL,
    request_date DATETIME NOT NULL,
    is_completed BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (vendor_id) REFERENCES vendor(vendor_id) ON DELETE CASCADE
);

-- Cart Table
CREATE TABLE cart (
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    PRIMARY KEY (user_id, product_id),  
    FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES product(product_id) ON DELETE CASCADE
);