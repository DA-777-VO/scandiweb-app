-- Create and use database
CREATE DATABASE IF NOT EXISTS scandiweb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE scandiweb;

-- Categories
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);

-- Currencies
CREATE TABLE IF NOT EXISTS currencies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    label VARCHAR(10) NOT NULL,
    symbol VARCHAR(5) NOT NULL
);

-- Products
CREATE TABLE IF NOT EXISTS products (
    id VARCHAR(100) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    in_stock TINYINT(1) NOT NULL DEFAULT 1,
    gallery JSON NOT NULL,
    description TEXT,
    category VARCHAR(100),
    brand VARCHAR(100)
);

-- Attributes
CREATE TABLE IF NOT EXISTS attributes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id VARCHAR(100) NOT NULL,
    name VARCHAR(100) NOT NULL,
    type VARCHAR(50) NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Attribute Items
CREATE TABLE IF NOT EXISTS attribute_items (
    id VARCHAR(100) NOT NULL,
    attribute_id INT NOT NULL,
    product_id VARCHAR(100) NOT NULL,
    display_value VARCHAR(255) NOT NULL,
    value VARCHAR(255) NOT NULL,
    sort_order INT DEFAULT 0,
    PRIMARY KEY (id, attribute_id),
    FOREIGN KEY (attribute_id) REFERENCES attributes(id) ON DELETE CASCADE
);

-- Prices
CREATE TABLE IF NOT EXISTS prices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id VARCHAR(100) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    currency_id INT NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (currency_id) REFERENCES currencies(id)
);

-- Orders
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    created_at DATETIME NOT NULL
);

-- Order Items
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id VARCHAR(100) NOT NULL,
    quantity INT NOT NULL,
    selected_attributes TEXT,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- ============ SEED DATA ============

-- Categories
INSERT INTO categories (name) VALUES ('all'), ('clothes'), ('tech')
ON DUPLICATE KEY UPDATE name = name;

-- Currencies
INSERT INTO currencies (label, symbol) VALUES ('USD', '$')
ON DUPLICATE KEY UPDATE label = label;

-- Products
INSERT INTO products (id, name, in_stock, gallery, description, category, brand) VALUES
('huarache-x-stussy-le', 'Nike Air Huarache Le', 1,
 '["https://cdn.shopify.com/s/files/1/0087/6193/3920/products/DD1381200_DEOA_2_720x.jpg?v=1612816087","https://cdn.shopify.com/s/files/1/0087/6193/3920/products/DD1381200_DEOA_1_720x.jpg?v=1612816087","https://cdn.shopify.com/s/files/1/0087/6193/3920/products/DD1381200_DEOA_3_720x.jpg?v=1612816087","https://cdn.shopify.com/s/files/1/0087/6193/3920/products/DD1381200_DEOA_5_720x.jpg?v=1612816087","https://cdn.shopify.com/s/files/1/0087/6193/3920/products/DD1381200_DEOA_4_720x.jpg?v=1612816087"]',
 '<p>Great sneakers for everyday use!</p>', 'clothes', 'Nike x Stussy'),

('jacket-canada-goosee', 'Jacket', 1,
 '["https://images.canadagoose.com/image/upload/w_480,c_scale,f_auto,q_auto:best/v1576016105/product-image/2409L_61.jpg","https://images.canadagoose.com/image/upload/w_480,c_scale,f_auto,q_auto:best/v1576016107/product-image/2409L_61_a.jpg","https://images.canadagoose.com/image/upload/w_480,c_scale,f_auto,q_auto:best/v1576016108/product-image/2409L_61_b.jpg","https://images.canadagoose.com/image/upload/w_480,c_scale,f_auto,q_auto:best/v1576016109/product-image/2409L_61_c.jpg","https://images.canadagoose.com/image/upload/w_480,c_scale,f_auto,q_auto:best/v1576016110/product-image/2409L_61_d.jpg"]',
 '<p>Awesome winter jacket</p>', 'clothes', 'Canada Goose'),

('ps-5', 'PlayStation 5', 1,
 '["https://images-na.ssl-images-amazon.com/images/I/510VSJ9mWDL._SL1262_.jpg","https://images-na.ssl-images-amazon.com/images/I/610%2B69ZsKCL._SL1500_.jpg","https://images-na.ssl-images-amazon.com/images/I/51iPoFwQT3L._SL1230_.jpg","https://images-na.ssl-images-amazon.com/images/I/61qbqFcvoNL._SL1500_.jpg","https://images-na.ssl-images-amazon.com/images/I/51HCjA3rqYL._SL1230_.jpg"]',
 '<p>A good gaming console. Plays games of PS4! Enjoy if you can buy it mwahahahaha</p>', 'tech', 'Sony'),

('xbox-series-s', 'Xbox Series S 512GB', 0,
 '["https://images-na.ssl-images-amazon.com/images/I/71vPCX0bS-L._SL1500_.jpg","https://images-na.ssl-images-amazon.com/images/I/71q7JTbRTpL._SL1500_.jpg","https://images-na.ssl-images-amazon.com/images/I/71iQ4HGHtsL._SL1500_.jpg","https://images-na.ssl-images-amazon.com/images/I/61IYrCrBzxL._SL1500_.jpg","https://images-na.ssl-images-amazon.com/images/I/61RnXmpAmIL._SL1500_.jpg"]',
 '<p>Xbox Series S 512GB</p>', 'tech', 'Microsoft'),

('apple-imac-2021', 'iMac 2021', 1,
 '["https://store.storeimages.cdn-apple.com/4982/as-images.apple.com/is/imac-24-blue-selection-hero-202104?wid=904&hei=840&fmt=jpeg&qlt=80&.v=1617492405000"]',
 'The new iMac!', 'tech', 'Apple'),

('apple-iphone-12-pro', 'iPhone 12 Pro', 1,
 '["https://store.storeimages.cdn-apple.com/4982/as-images.apple.com/is/iphone-12-pro-family-hero?wid=940&hei=1112&fmt=jpeg&qlt=80&.v=1604021663000"]',
 'This is iPhone 12. Nothing else to say.', 'tech', 'Apple'),

('apple-airpods-pro', 'AirPods Pro', 0,
 '["https://store.storeimages.cdn-apple.com/4982/as-images.apple.com/is/MWP22?wid=572&hei=572&fmt=jpeg&qlt=95&.v=1591634795000"]',
 '<p>Magic like you have never heard</p>', 'tech', 'Apple'),

('apple-airtag', 'AirTag', 1,
 '["https://store.storeimages.cdn-apple.com/4982/as-images.apple.com/is/airtag-double-select-202104?wid=445&hei=370&fmt=jpeg&qlt=95&.v=1617761672000"]',
 '<h1>Lose your knack for losing things.</h1>', 'tech', 'Apple')

ON DUPLICATE KEY UPDATE name = name;

-- Attributes for huarache-x-stussy-le
INSERT INTO attributes (product_id, name, type) VALUES ('huarache-x-stussy-le', 'Size', 'text');
SET @attr_id = LAST_INSERT_ID();
INSERT INTO attribute_items (id, attribute_id, product_id, display_value, value, sort_order) VALUES
('40', @attr_id, 'huarache-x-stussy-le', '40', '40', 0),
('41', @attr_id, 'huarache-x-stussy-le', '41', '41', 1),
('42', @attr_id, 'huarache-x-stussy-le', '42', '42', 2),
('43', @attr_id, 'huarache-x-stussy-le', '43', '43', 3);

-- Attributes for jacket-canada-goosee
INSERT INTO attributes (product_id, name, type) VALUES ('jacket-canada-goosee', 'Size', 'text');
SET @attr_id = LAST_INSERT_ID();
INSERT INTO attribute_items (id, attribute_id, product_id, display_value, value, sort_order) VALUES
('Small', @attr_id, 'jacket-canada-goosee', 'Small', 'S', 0),
('Medium', @attr_id, 'jacket-canada-goosee', 'Medium', 'M', 1),
('Large', @attr_id, 'jacket-canada-goosee', 'Large', 'L', 2),
('Extra Large', @attr_id, 'jacket-canada-goosee', 'Extra Large', 'XL', 3);

-- Attributes for ps-5
INSERT INTO attributes (product_id, name, type) VALUES ('ps-5', 'Color', 'swatch');
SET @attr_id = LAST_INSERT_ID();
INSERT INTO attribute_items (id, attribute_id, product_id, display_value, value, sort_order) VALUES
('Green', @attr_id, 'ps-5', 'Green', '#44FF03', 0),
('Cyan', @attr_id, 'ps-5', 'Cyan', '#03FFF7', 1),
('Blue', @attr_id, 'ps-5', 'Blue', '#030BFF', 2),
('Black', @attr_id, 'ps-5', 'Black', '#000000', 3),
('White', @attr_id, 'ps-5', 'White', '#FFFFFF', 4);

INSERT INTO attributes (product_id, name, type) VALUES ('ps-5', 'Capacity', 'text');
SET @attr_id = LAST_INSERT_ID();
INSERT INTO attribute_items (id, attribute_id, product_id, display_value, value, sort_order) VALUES
('512G', @attr_id, 'ps-5', '512G', '512G', 0),
('1T', @attr_id, 'ps-5', '1T', '1T', 1);

-- Attributes for xbox-series-s
INSERT INTO attributes (product_id, name, type) VALUES ('xbox-series-s', 'Color', 'swatch');
SET @attr_id = LAST_INSERT_ID();
INSERT INTO attribute_items (id, attribute_id, product_id, display_value, value, sort_order) VALUES
('Green', @attr_id, 'xbox-series-s', 'Green', '#44FF03', 0),
('Cyan', @attr_id, 'xbox-series-s', 'Cyan', '#03FFF7', 1),
('Blue', @attr_id, 'xbox-series-s', 'Blue', '#030BFF', 2),
('Black', @attr_id, 'xbox-series-s', 'Black', '#000000', 3),
('White', @attr_id, 'xbox-series-s', 'White', '#FFFFFF', 4);

INSERT INTO attributes (product_id, name, type) VALUES ('xbox-series-s', 'Capacity', 'text');
SET @attr_id = LAST_INSERT_ID();
INSERT INTO attribute_items (id, attribute_id, product_id, display_value, value, sort_order) VALUES
('512G', @attr_id, 'xbox-series-s', '512G', '512G', 0),
('1T', @attr_id, 'xbox-series-s', '1T', '1T', 1);

-- Attributes for apple-imac-2021
INSERT INTO attributes (product_id, name, type) VALUES ('apple-imac-2021', 'Capacity', 'text');
SET @attr_id = LAST_INSERT_ID();
INSERT INTO attribute_items (id, attribute_id, product_id, display_value, value, sort_order) VALUES
('256GB', @attr_id, 'apple-imac-2021', '256GB', '256GB', 0),
('512GB', @attr_id, 'apple-imac-2021', '512GB', '512GB', 1);

INSERT INTO attributes (product_id, name, type) VALUES ('apple-imac-2021', 'With USB 3 ports', 'text');
SET @attr_id = LAST_INSERT_ID();
INSERT INTO attribute_items (id, attribute_id, product_id, display_value, value, sort_order) VALUES
('Yes', @attr_id, 'apple-imac-2021', 'Yes', 'Yes', 0),
('No', @attr_id, 'apple-imac-2021', 'No', 'No', 1);

INSERT INTO attributes (product_id, name, type) VALUES ('apple-imac-2021', 'Touch ID in keyboard', 'text');
SET @attr_id = LAST_INSERT_ID();
INSERT INTO attribute_items (id, attribute_id, product_id, display_value, value, sort_order) VALUES
('Yes', @attr_id, 'apple-imac-2021', 'Yes', 'Yes', 0),
('No', @attr_id, 'apple-imac-2021', 'No', 'No', 1);

-- Attributes for apple-iphone-12-pro
INSERT INTO attributes (product_id, name, type) VALUES ('apple-iphone-12-pro', 'Capacity', 'text');
SET @attr_id = LAST_INSERT_ID();
INSERT INTO attribute_items (id, attribute_id, product_id, display_value, value, sort_order) VALUES
('512G', @attr_id, 'apple-iphone-12-pro', '512G', '512G', 0),
('1T', @attr_id, 'apple-iphone-12-pro', '1T', '1T', 1);

INSERT INTO attributes (product_id, name, type) VALUES ('apple-iphone-12-pro', 'Color', 'swatch');
SET @attr_id = LAST_INSERT_ID();
INSERT INTO attribute_items (id, attribute_id, product_id, display_value, value, sort_order) VALUES
('Green', @attr_id, 'apple-iphone-12-pro', 'Green', '#44FF03', 0),
('Cyan', @attr_id, 'apple-iphone-12-pro', 'Cyan', '#03FFF7', 1),
('Blue', @attr_id, 'apple-iphone-12-pro', 'Blue', '#030BFF', 2),
('Black', @attr_id, 'apple-iphone-12-pro', 'Black', '#000000', 3),
('White', @attr_id, 'apple-iphone-12-pro', 'White', '#FFFFFF', 4);

-- Prices
INSERT INTO prices (product_id, amount, currency_id) VALUES
('huarache-x-stussy-le', 144.69, 1),
('jacket-canada-goosee', 518.47, 1),
('ps-5', 844.02, 1),
('xbox-series-s', 333.99, 1),
('apple-imac-2021', 1688.03, 1),
('apple-iphone-12-pro', 1000.76, 1),
('apple-airpods-pro', 300.23, 1),
('apple-airtag', 120.57, 1);
