CREATE DATABASE IF NOT EXISTS scandiweb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE scandiweb;

-- ─── Tables ────────────────────────────────────────────────────────────────

-- name is VARCHAR(100) UNIQUE — allows FK reference from products.category.
-- ENUM was considered but MySQL requires exact type match for FK columns,
-- so ENUM('all','clothes','tech') on categories.name would break the FK
-- when products.category is VARCHAR. Valid values are enforced by the
-- ProductCategory PHP enum in application code.
CREATE TABLE IF NOT EXISTS categories (
    id   INT          AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS currencies (
    id     INT         AUTO_INCREMENT PRIMARY KEY,
    label  VARCHAR(10) NOT NULL,
    symbol VARCHAR(5)  NOT NULL
);

-- CHANGED:
--   in_stock → BOOLEAN (semantic alias for TINYINT(1), communicates intent)
--   gallery  → removed, moved to product_gallery table
--   category → NOT NULL + FOREIGN KEY referencing categories(name)
--              Products must belong to a real DB category.
CREATE TABLE IF NOT EXISTS products (
    id          VARCHAR(100) PRIMARY KEY,
    name        VARCHAR(255) NOT NULL,
    in_stock    BOOLEAN      NOT NULL DEFAULT TRUE,
    description TEXT,
    category    VARCHAR(100) NOT NULL,
    brand       VARCHAR(100) NOT NULL,
    FOREIGN KEY (category) REFERENCES categories(name) ON UPDATE CASCADE
);

-- NEW: one row per image, replaces JSON gallery column in products.
-- Normalised: easy to query, order, add/remove images individually.
CREATE TABLE IF NOT EXISTS product_gallery (
    id         INT          AUTO_INCREMENT PRIMARY KEY,
    product_id VARCHAR(100) NOT NULL,
    url        TEXT         NOT NULL,
    sort_order INT          NOT NULL DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS attributes (
    id         INT          AUTO_INCREMENT PRIMARY KEY,
    product_id VARCHAR(100) NOT NULL,
    name       VARCHAR(100) NOT NULL,
    type       VARCHAR(50)  NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS attribute_items (
    id            VARCHAR(100) NOT NULL,
    attribute_id  INT          NOT NULL,
    product_id    VARCHAR(100) NOT NULL,
    display_value VARCHAR(255) NOT NULL,
    value         VARCHAR(255) NOT NULL,
    sort_order    INT          DEFAULT 0,
    PRIMARY KEY (id, attribute_id),
    FOREIGN KEY (attribute_id) REFERENCES attributes(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS prices (
    id          INT            AUTO_INCREMENT PRIMARY KEY,
    product_id  VARCHAR(100)   NOT NULL,
    amount      DECIMAL(10, 2) NOT NULL,
    currency_id INT            NOT NULL,
    FOREIGN KEY (product_id)  REFERENCES products(id)   ON DELETE CASCADE,
    FOREIGN KEY (currency_id) REFERENCES currencies(id)
);

CREATE TABLE IF NOT EXISTS orders (
    id         INT      AUTO_INCREMENT PRIMARY KEY,
    created_at DATETIME NOT NULL
);

-- CHANGED: product_id now has FOREIGN KEY.
-- Prevents order items referencing non-existent products.
-- ON DELETE RESTRICT: an ordered product cannot be deleted from the catalogue.
CREATE TABLE IF NOT EXISTS order_items (
    id                  INT          AUTO_INCREMENT PRIMARY KEY,
    order_id            INT          NOT NULL,
    product_id          VARCHAR(100) NOT NULL,
    quantity            INT          NOT NULL,
    selected_attributes TEXT,
    FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
);

-- ─── Seed Data ─────────────────────────────────────────────────────────────

INSERT INTO categories (name) VALUES ('all'), ('clothes'), ('tech')
ON DUPLICATE KEY UPDATE name = name;

INSERT INTO currencies (label, symbol) VALUES ('USD', '$')
ON DUPLICATE KEY UPDATE label = label;

-- ── Products ─────────────────────────────────────────────────────────────────

INSERT INTO products (id, name, in_stock, description, category, brand) VALUES

('huarache-x-stussy-le', 'Nike Air Huarache Le', TRUE,
 '<p>Great sneakers for everyday use!</p>', 'clothes', 'Nike x Stussy'),

('jacket-canada-goosee', 'Jacket', TRUE,
 '<p>Awesome winter jacket</p>', 'clothes', 'Canada Goose'),

('ps-5', 'PlayStation 5', TRUE,
 '<p>A good gaming console. Plays games of PS4! Enjoy if you can buy it mwahahahaha</p>',
 'tech', 'Sony'),

('xbox-series-s', 'Xbox Series S 512GB', FALSE,
 '<div><ul><li><span>Hardware-beschleunigtes Raytracing macht dein Spiel noch realistischer</span></li><li><span>Spiele Games mit bis zu 120 Bilder pro Sekunde</span></li><li><span>Minimiere Ladezeiten mit einer speziell entwickelten 512GB NVMe SSD und wechsle mit Quick Resume nahtlos zwischen mehreren Spielen.</span></li><li><span>Xbox Smart Delivery stellt sicher, dass du die beste Version deines Spiels spielst, egal, auf welcher Konsole du spielst</span></li><li><span>Spiele deine Xbox One-Spiele auf deiner Xbox Series S weiter. Deine Fortschritte, Erfolge und Freundesliste werden automatisch auf das neue System übertragen.</span></li><li><span>Erwecke deine Spiele und Filme mit innovativem 3D Raumklang zum Leben</span></li><li><span>Der brandneue Xbox Wireless Controller zeichnet sich durch höchste Präzision, eine neue Share-Taste und verbesserte Ergonomie aus</span></li><li><span>Ultra-niedrige Latenz verbessert die Reaktionszeit von Controller zum Fernseher</span></li><li><span>Verwende dein Xbox One-Gaming-Zubehör -einschließlich Controller, Headsets und mehr</span></li><li><span>Erweitere deinen Speicher mit der Seagate 1 TB-Erweiterungskarte für Xbox Series X (separat erhältlich) und streame 4K-Videos von Disney+, Netflix, Amazon, Microsoft Movies &amp; TV und mehr</span></li></ul></div>',
 'tech', 'Microsoft'),

('apple-imac-2021', 'iMac 2021', TRUE,
 'The new iMac!', 'tech', 'Apple'),

('apple-iphone-12-pro', 'iPhone 12 Pro', TRUE,
 'This is iPhone 12. Nothing else to say.', 'tech', 'Apple'),

('apple-airpods-pro', 'AirPods Pro', FALSE,
 '<h3>Magic like you\'ve never heard</h3><p>AirPods Pro have been designed to deliver Active Noise Cancellation for immersive sound, Transparency mode so you can hear your surroundings, and a customizable fit for all-day comfort. Just like AirPods, AirPods Pro connect magically to your iPhone or Apple Watch. And they\'re ready to use right out of the case.</p><h3>Active Noise Cancellation</h3><p>Incredibly light noise-cancelling headphones, AirPods Pro block out your environment so you can focus on what you\'re listening to. AirPods Pro use two microphones, an outward-facing microphone and an inward-facing microphone, to create superior noise cancellation. By continuously adapting to the geometry of your ear and the fit of the ear tips, Active Noise Cancellation silences the world to keep you fully tuned in to your music, podcasts, and calls.</p><h3>Transparency mode</h3><p>Switch to Transparency mode and AirPods Pro let the outside sound in, allowing you to hear and connect to your surroundings. Outward- and inward-facing microphones enable AirPods Pro to undo the sound-isolating effect of the silicone tips so things sound and feel natural, like when you\'re talking to people around you.</p><h3>All-new design</h3><p>AirPods Pro offer a more customizable fit with three sizes of flexible silicone tips to choose from. With an internal taper, they conform to the shape of your ear, securing your AirPods Pro in place and creating an exceptional seal for superior noise cancellation.</p><h3>Amazing audio quality</h3><p>A custom-built high-excursion, low-distortion driver delivers powerful bass. A superefficient high dynamic range amplifier produces pure, incredibly clear sound while also extending battery life. And Adaptive EQ automatically tunes music to suit the shape of your ear for a rich, consistent listening experience.</p><h3>Even more magical</h3><p>The Apple-designed H1 chip delivers incredibly low audio latency. A force sensor on the stem makes it easy to control music and calls and switch between Active Noise Cancellation and Transparency mode. Announce Messages with Siri gives you the option to have Siri read your messages through your AirPods. And with Audio Sharing, you and a friend can share the same audio stream on two sets of AirPods — so you can play a game, watch a movie, or listen to a song together.</p>',
 'tech', 'Apple'),

('apple-airtag', 'AirTag', TRUE,
 '<h1>Lose your knack for losing things.</h1><p>AirTag is an easy way to keep track of your stuff. Attach one to your keys, slip another one in your backpack. And just like that, they\'re on your radar in the Find My app. AirTag has your back.</p>',
 'tech', 'Apple')

ON DUPLICATE KEY UPDATE name = name;

-- ── Gallery ──────────────────────────────────────────────────────────────────

INSERT INTO product_gallery (product_id, url, sort_order) VALUES
('huarache-x-stussy-le', 'https://cdn.shopify.com/s/files/1/0087/6193/3920/products/DD1381200_DEOA_2_720x.jpg?v=1612816087', 0),
('huarache-x-stussy-le', 'https://cdn.shopify.com/s/files/1/0087/6193/3920/products/DD1381200_DEOA_1_720x.jpg?v=1612816087', 1),
('huarache-x-stussy-le', 'https://cdn.shopify.com/s/files/1/0087/6193/3920/products/DD1381200_DEOA_3_720x.jpg?v=1612816087', 2),
('huarache-x-stussy-le', 'https://cdn.shopify.com/s/files/1/0087/6193/3920/products/DD1381200_DEOA_5_720x.jpg?v=1612816087', 3),
('huarache-x-stussy-le', 'https://cdn.shopify.com/s/files/1/0087/6193/3920/products/DD1381200_DEOA_4_720x.jpg?v=1612816087', 4),

('jacket-canada-goosee', 'https://images.canadagoose.com/image/upload/w_480,c_scale,f_auto,q_auto:best/v1576016105/product-image/2409L_61.jpg', 0),
('jacket-canada-goosee', 'https://images.canadagoose.com/image/upload/w_480,c_scale,f_auto,q_auto:best/v1576016107/product-image/2409L_61_a.jpg', 1),
('jacket-canada-goosee', 'https://images.canadagoose.com/image/upload/w_480,c_scale,f_auto,q_auto:best/v1576016108/product-image/2409L_61_b.jpg', 2),
('jacket-canada-goosee', 'https://images.canadagoose.com/image/upload/w_480,c_scale,f_auto,q_auto:best/v1576016109/product-image/2409L_61_c.jpg', 3),
('jacket-canada-goosee', 'https://images.canadagoose.com/image/upload/w_480,c_scale,f_auto,q_auto:best/v1576016110/product-image/2409L_61_d.jpg', 4),
('jacket-canada-goosee', 'https://images.canadagoose.com/image/upload/w_1333,c_scale,f_auto,q_auto:best/v1634058169/product-image/2409L_61_o.png', 5),
('jacket-canada-goosee', 'https://images.canadagoose.com/image/upload/w_1333,c_scale,f_auto,q_auto:best/v1634058159/product-image/2409L_61_p.png', 6),

('ps-5', 'https://images-na.ssl-images-amazon.com/images/I/510VSJ9mWDL._SL1262_.jpg', 0),
('ps-5', 'https://images-na.ssl-images-amazon.com/images/I/610%2B69ZsKCL._SL1500_.jpg', 1),
('ps-5', 'https://images-na.ssl-images-amazon.com/images/I/51iPoFwQT3L._SL1230_.jpg', 2),
('ps-5', 'https://images-na.ssl-images-amazon.com/images/I/61qbqFcvoNL._SL1500_.jpg', 3),
('ps-5', 'https://images-na.ssl-images-amazon.com/images/I/51HCjA3rqYL._SL1230_.jpg', 4),

('xbox-series-s', 'https://images-na.ssl-images-amazon.com/images/I/71vPCX0bS-L._SL1500_.jpg', 0),
('xbox-series-s', 'https://images-na.ssl-images-amazon.com/images/I/71q7JTbRTpL._SL1500_.jpg', 1),
('xbox-series-s', 'https://images-na.ssl-images-amazon.com/images/I/71iQ4HGHtsL._SL1500_.jpg', 2),
('xbox-series-s', 'https://images-na.ssl-images-amazon.com/images/I/61IYrCrBzxL._SL1500_.jpg', 3),
('xbox-series-s', 'https://images-na.ssl-images-amazon.com/images/I/61RnXmpAmIL._SL1500_.jpg', 4),

('apple-imac-2021', 'https://store.storeimages.cdn-apple.com/4982/as-images.apple.com/is/imac-24-blue-selection-hero-202104?wid=904&hei=840&fmt=jpeg&qlt=80&.v=1617492405000', 0),

('apple-iphone-12-pro', 'https://store.storeimages.cdn-apple.com/4982/as-images.apple.com/is/iphone-12-pro-family-hero?wid=940&hei=1112&fmt=jpeg&qlt=80&.v=1604021663000', 0),

('apple-airpods-pro', 'https://store.storeimages.cdn-apple.com/4982/as-images.apple.com/is/MWP22?wid=572&hei=572&fmt=jpeg&qlt=95&.v=1591634795000', 0),

('apple-airtag', 'https://store.storeimages.cdn-apple.com/4982/as-images.apple.com/is/airtag-double-select-202104?wid=445&hei=370&fmt=jpeg&qlt=95&.v=1617761672000', 0);

-- ── Attributes ───────────────────────────────────────────────────────────────

INSERT INTO attributes (product_id, name, type) VALUES ('huarache-x-stussy-le', 'Size', 'text');
SET @attr_id = LAST_INSERT_ID();
INSERT INTO attribute_items (id, attribute_id, product_id, display_value, value, sort_order) VALUES
('40', @attr_id, 'huarache-x-stussy-le', '40', '40', 0),
('41', @attr_id, 'huarache-x-stussy-le', '41', '41', 1),
('42', @attr_id, 'huarache-x-stussy-le', '42', '42', 2),
('43', @attr_id, 'huarache-x-stussy-le', '43', '43', 3);

INSERT INTO attributes (product_id, name, type) VALUES ('jacket-canada-goosee', 'Size', 'text');
SET @attr_id = LAST_INSERT_ID();
INSERT INTO attribute_items (id, attribute_id, product_id, display_value, value, sort_order) VALUES
('Small',       @attr_id, 'jacket-canada-goosee', 'Small',       'S',  0),
('Medium',      @attr_id, 'jacket-canada-goosee', 'Medium',      'M',  1),
('Large',       @attr_id, 'jacket-canada-goosee', 'Large',       'L',  2),
('Extra Large', @attr_id, 'jacket-canada-goosee', 'Extra Large', 'XL', 3);

INSERT INTO attributes (product_id, name, type) VALUES ('ps-5', 'Color', 'swatch');
SET @attr_id = LAST_INSERT_ID();
INSERT INTO attribute_items (id, attribute_id, product_id, display_value, value, sort_order) VALUES
('Green', @attr_id, 'ps-5', 'Green', '#44FF03', 0),
('Cyan',  @attr_id, 'ps-5', 'Cyan',  '#03FFF7', 1),
('Blue',  @attr_id, 'ps-5', 'Blue',  '#030BFF', 2),
('Black', @attr_id, 'ps-5', 'Black', '#000000', 3),
('White', @attr_id, 'ps-5', 'White', '#FFFFFF', 4);

INSERT INTO attributes (product_id, name, type) VALUES ('ps-5', 'Capacity', 'text');
SET @attr_id = LAST_INSERT_ID();
INSERT INTO attribute_items (id, attribute_id, product_id, display_value, value, sort_order) VALUES
('512G', @attr_id, 'ps-5', '512G', '512G', 0),
('1T',   @attr_id, 'ps-5', '1T',   '1T',   1);

INSERT INTO attributes (product_id, name, type) VALUES ('xbox-series-s', 'Color', 'swatch');
SET @attr_id = LAST_INSERT_ID();
INSERT INTO attribute_items (id, attribute_id, product_id, display_value, value, sort_order) VALUES
('Green', @attr_id, 'xbox-series-s', 'Green', '#44FF03', 0),
('Cyan',  @attr_id, 'xbox-series-s', 'Cyan',  '#03FFF7', 1),
('Blue',  @attr_id, 'xbox-series-s', 'Blue',  '#030BFF', 2),
('Black', @attr_id, 'xbox-series-s', 'Black', '#000000', 3),
('White', @attr_id, 'xbox-series-s', 'White', '#FFFFFF', 4);

INSERT INTO attributes (product_id, name, type) VALUES ('xbox-series-s', 'Capacity', 'text');
SET @attr_id = LAST_INSERT_ID();
INSERT INTO attribute_items (id, attribute_id, product_id, display_value, value, sort_order) VALUES
('512G', @attr_id, 'xbox-series-s', '512G', '512G', 0),
('1T',   @attr_id, 'xbox-series-s', '1T',   '1T',   1);

INSERT INTO attributes (product_id, name, type) VALUES ('apple-imac-2021', 'Capacity', 'text');
SET @attr_id = LAST_INSERT_ID();
INSERT INTO attribute_items (id, attribute_id, product_id, display_value, value, sort_order) VALUES
('256GB', @attr_id, 'apple-imac-2021', '256GB', '256GB', 0),
('512GB', @attr_id, 'apple-imac-2021', '512GB', '512GB', 1);

INSERT INTO attributes (product_id, name, type) VALUES ('apple-imac-2021', 'With USB 3 ports', 'text');
SET @attr_id = LAST_INSERT_ID();
INSERT INTO attribute_items (id, attribute_id, product_id, display_value, value, sort_order) VALUES
('Yes', @attr_id, 'apple-imac-2021', 'Yes', 'Yes', 0),
('No',  @attr_id, 'apple-imac-2021', 'No',  'No',  1);

INSERT INTO attributes (product_id, name, type) VALUES ('apple-imac-2021', 'Touch ID in keyboard', 'text');
SET @attr_id = LAST_INSERT_ID();
INSERT INTO attribute_items (id, attribute_id, product_id, display_value, value, sort_order) VALUES
('Yes', @attr_id, 'apple-imac-2021', 'Yes', 'Yes', 0),
('No',  @attr_id, 'apple-imac-2021', 'No',  'No',  1);

INSERT INTO attributes (product_id, name, type) VALUES ('apple-iphone-12-pro', 'Capacity', 'text');
SET @attr_id = LAST_INSERT_ID();
INSERT INTO attribute_items (id, attribute_id, product_id, display_value, value, sort_order) VALUES
('512G', @attr_id, 'apple-iphone-12-pro', '512G', '512G', 0),
('1T',   @attr_id, 'apple-iphone-12-pro', '1T',   '1T',   1);

INSERT INTO attributes (product_id, name, type) VALUES ('apple-iphone-12-pro', 'Color', 'swatch');
SET @attr_id = LAST_INSERT_ID();
INSERT INTO attribute_items (id, attribute_id, product_id, display_value, value, sort_order) VALUES
('Green', @attr_id, 'apple-iphone-12-pro', 'Green', '#44FF03', 0),
('Cyan',  @attr_id, 'apple-iphone-12-pro', 'Cyan',  '#03FFF7', 1),
('Blue',  @attr_id, 'apple-iphone-12-pro', 'Blue',  '#030BFF', 2),
('Black', @attr_id, 'apple-iphone-12-pro', 'Black', '#000000', 3),
('White', @attr_id, 'apple-iphone-12-pro', 'White', '#FFFFFF', 4);

-- ── Prices ───────────────────────────────────────────────────────────────────

INSERT INTO prices (product_id, amount, currency_id) VALUES
('huarache-x-stussy-le',  144.69, 1),
('jacket-canada-goosee',  518.47, 1),
('ps-5',                  844.02, 1),
('xbox-series-s',         333.99, 1),
('apple-imac-2021',      1688.03, 1),
('apple-iphone-12-pro',  1000.76, 1),
('apple-airpods-pro',     300.23, 1),
('apple-airtag',          120.57, 1);
