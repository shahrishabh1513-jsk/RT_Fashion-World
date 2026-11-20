-- =====================================================================
-- RT FASHION HUB  –  MySQL / MariaDB schema + seed data (XAMPP)
-- Database name: rt_fashion
-- Import: phpMyAdmin -> Import -> choose this file   (or: mysql -u root < rt_fashion.sql)
-- =====================================================================
CREATE DATABASE IF NOT EXISTS `rt_fashion` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `rt_fashion`;
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `password_resets`, `newsletter_subscribers`, `contact_messages`, `order_history`, `order_items`, `orders`, `wishlist`, `cart_items`, `products`, `addresses`, `users`;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `customer_id` VARCHAR(24) NOT NULL UNIQUE,          -- e.g. RT1504-USER-0001
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `mobile` VARCHAR(15) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,              -- password_hash() / bcrypt, never plain text
  `role` ENUM('customer','admin') NOT NULL DEFAULT 'customer',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` TIMESTAMP NULL
) ENGINE=InnoDB;

CREATE TABLE `addresses` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `address_text` TEXT NOT NULL,
  `pincode` VARCHAR(6) NULL,
  `is_default` TINYINT(1) NOT NULL DEFAULT 0,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `products` (
  `id` VARCHAR(12) PRIMARY KEY,                        -- rt-001 ... (also the image file name)
  `name` VARCHAR(255) NOT NULL,
  `category` ENUM('Men','Women','Accessories','Bags','Jewelry') NOT NULL,
  `collection` ENUM('men','women','accessories') NOT NULL,
  `subcategory` VARCHAR(60) NOT NULL,
  `rating` DECIMAL(2,1) NOT NULL DEFAULT 0,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 999,           -- PLACEHOLDER prices: edit in Admin > Products
  `sale_price` DECIMAL(10,2) NULL,                      -- lower than price => SALE tag shows automatically
  `badge` VARCHAR(30) NULL,
  `image` VARCHAR(255) NOT NULL DEFAULT '',
  `link` VARCHAR(255) NOT NULL DEFAULT '',
  `description` TEXT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX (`category`), INDEX (`collection`), INDEX (`is_active`)
) ENGINE=InnoDB;

CREATE TABLE `cart_items` (
  `user_id` INT UNSIGNED NOT NULL,
  `product_id` VARCHAR(12) NOT NULL,
  `qty` INT UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (`user_id`,`product_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `wishlist` (
  `user_id` INT UNSIGNED NOT NULL,
  `product_id` VARCHAR(12) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`,`product_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `orders` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_no` VARCHAR(24) NULL UNIQUE,                  -- RT-ORD-2026-00001
  `user_id` INT UNSIGNED NOT NULL,
  `customer_name` VARCHAR(100) NOT NULL,
  `customer_email` VARCHAR(150) NOT NULL,
  `customer_mobile` VARCHAR(15) NOT NULL,
  `shipping_address` TEXT NOT NULL,
  `subtotal` DECIMAL(10,2) NOT NULL,
  `shipping` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `gst` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `total` DECIMAL(10,2) NOT NULL,
  `payment_method` VARCHAR(40) NOT NULL DEFAULT 'Cash on Delivery',
  `payment_status` VARCHAR(40) NOT NULL DEFAULT 'Pay on delivery',
  `status` VARCHAR(30) NOT NULL DEFAULT 'Order Placed',  -- Order Placed|Confirmed|Packed|Shipped|Out for Delivery|Delivered|Cancelled
  `tracking` VARCHAR(255) NOT NULL DEFAULT '',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX (`user_id`), INDEX (`status`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB;

CREATE TABLE `order_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT UNSIGNED NOT NULL,
  `product_id` VARCHAR(12) NULL,
  `product_name` VARCHAR(255) NOT NULL,                -- snapshot so old bills never change
  `unit_price` DECIMAL(10,2) NOT NULL,
  `qty` INT UNSIGNED NOT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `order_history` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT UNSIGNED NOT NULL,
  `status` VARCHAR(30) NOT NULL,
  `note` VARCHAR(255) NOT NULL DEFAULT '',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `contact_messages` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `subject` VARCHAR(200) NOT NULL,
  `message` TEXT NOT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE `newsletter_subscribers` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE `password_resets` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `token_hash` CHAR(64) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `used` TINYINT(1) NOT NULL DEFAULT 0,
  INDEX (`token_hash`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Default ADMIN account  (change the password after first login!)
--   email: admin@rtfashionhub.com     password: Admin@123
-- ---------------------------------------------------------------------
INSERT INTO `users` (`customer_id`,`name`,`email`,`mobile`,`password_hash`,`role`)
VALUES ('RT1504-ADMIN-0001','RT Admin','admin@rtfashionhub.com','7861976128','$2y$10$DQo5VpmMcii7Yt1ggQuj1uZoSvwLAvaMtOrCSsGilPk87q/g/bX9S','admin');

-- ---------------------------------------------------------------------
-- Products (58). Prices are PLACEHOLDERS (999) – edit in Admin > Products.
-- ---------------------------------------------------------------------
INSERT INTO `products` (`id`,`name`,`category`,`collection`,`subcategory`,`rating`,`price`,`sale_price`,`badge`,`image`,`link`,`description`,`is_active`,`sort_order`) VALUES
('rt-001','U.S. POLO ASSN. Men Solid Polo Neck Pure Cotton White T-Shirt','Men','men','T-Shirts',4.3,999,NULL,NULL,'images/man/rt-001.webp','https://dl.flipkart.com/s/Ih9q8fNNNN',NULL,1,1),
('rt-002','U.S. POLO ASSN. Men Graphic Print Polo Neck Pure Cotton Blue T-Shirt','Men','men','T-Shirts',4.5,999,NULL,'Trending','images/man/rt-002.webp','https://dl.flipkart.com/s/URxmMcuuuN',NULL,1,2),
('rt-003','U.S. POLO ASSN. Men Striped Polo Neck Cotton Blend White T-Shirt','Men','men','T-Shirts',4.4,999,NULL,NULL,'images/man/rt-003.webp','https://dl.flipkart.com/s/UePYgCuuuN',NULL,1,3),
('rt-004','KAJARU Men Striped Polo Neck Polyester Grey T-Shirt','Men','men','T-Shirts',3.5,999,NULL,NULL,'images/man/rt-004.webp','https://dl.flipkart.com/s/I5iG5dNNNN',NULL,1,4),
('rt-005','MYEONN Men Solid Polo Neck Polycotton Pink T-Shirt','Men','men','T-Shirts',3.7,999,NULL,NULL,'images/man/rt-005.webp','https://dl.flipkart.com/s/URiqY2uuuN',NULL,1,5),
('rt-006','Try This Men Printed Zip Neck Cotton Blend Green, White T-Shirt','Men','men','T-Shirts',4,999,NULL,NULL,'images/man/rt-006.webp','https://dl.flipkart.com/s/Ih405fNNNN',NULL,1,6),
('rt-007','U.S. POLO ASSN. Men Full Sleeve Graphic Print Sweatshirt','Men','men','T-Shirts',4.2,999,NULL,NULL,'images/man/rt-007.webp','https://dl.flipkart.com/s/I5cXL5NNNN',NULL,1,7),
('rt-008','WROGN Men Comfort Fit Solid Casual Shirt','Men','men','Shirts',3.6,999,NULL,NULL,'images/man/rt-008.webp','https://dl.flipkart.com/s/Ue6xeNuuuN',NULL,1,8),
('rt-009','THE BEAR HOUSE Men Slim Fit Solid Button Down Collar Casual Shirt','Men','men','Shirts',4.3,999,NULL,NULL,'images/man/rt-009.webp','https://dl.flipkart.com/s/UeAiMluuuN',NULL,1,9),
('rt-010','THE BEAR HOUSE Men Slim Fit Checkered Button Down Collar Casual Shirt','Men','men','Shirts',4.3,999,NULL,NULL,'images/man/rt-010.webp','https://dl.flipkart.com/s/Ue4YSSuuuN',NULL,1,10),
('rt-011','JACKBELLA Men Regular Fit Striped Spread Collar Casual Shirt','Men','men','Shirts',4.2,999,NULL,NULL,'images/man/rt-011.webp','https://dl.flipkart.com/s/UeEd5cuuuN',NULL,1,11),
('rt-012','The Indian Garage Co. Men Regular Fit Checkered Spread Collar Casual Shirt','Men','men','Shirts',4.1,999,NULL,NULL,'images/man/rt-012.webp','https://dl.flipkart.com/s/I5jz0zNNNN',NULL,1,12),
('rt-013','Snitch Men Straight Fit Mid Rise Light Blue Jeans','Men','men','Jeans & Trousers',4.1,999,NULL,NULL,'images/man/rt-013.webp','https://dl.flipkart.com/s/UeyPcQuuuN',NULL,1,13),
('rt-014','Snitch Men Baggy Fit Mid Rise Black Jeans','Men','men','Jeans & Trousers',3.9,999,NULL,NULL,'images/man/rt-014.webp','https://dl.flipkart.com/s/UeQzTluuuN',NULL,1,14),
('rt-015','Creation By London Hills Men Regular Mid Rise Dark Blue Jeans','Men','men','Jeans & Trousers',3.7,999,NULL,NULL,'images/man/rt-015.webp','https://dl.flipkart.com/s/I5!jQ8NNNN',NULL,1,15),
('rt-016','Pepe Jeans Men Relaxed Fit Mid Rise Black Jeans','Men','men','Jeans & Trousers',4.2,999,NULL,NULL,'images/man/rt-016.webp','https://dl.flipkart.com/s/U0NDaCuuuN',NULL,1,16),
('rt-017','The Indian Garage Co. Men Regular Fit Green Cotton Blend Trousers','Men','men','Jeans & Trousers',4.1,999,NULL,NULL,'images/man/rt-017.webp','',NULL,1,17),
('rt-018','PARK AVENUE Men Regular Fit Beige Polyester Trousers','Men','men','Jeans & Trousers',4.1,999,NULL,NULL,'images/man/rt-018.webp','https://dl.flipkart.com/s/U0pLPsuuuN',NULL,1,18),
('rt-019','PARK AVENUE Men Slim Fit Black Polyester Trousers','Men','men','Jeans & Trousers',4.2,999,NULL,NULL,'images/man/rt-019.webp','https://dl.flipkart.com/s/IVrvxmNNNN',NULL,1,19),
('rt-020','BELLSTONE Solid Men Grey Cargo Shorts','Men','men','Shorts',3.4,999,NULL,NULL,'images/man/rt-020.webp','https://dl.flipkart.com/s/I581DWNNNN',NULL,1,20),
('rt-021','MAX Solid Men Black Casual Shorts','Men','men','Shorts',4.5,999,NULL,'Trending','images/man/rt-021.webp','https://dl.flipkart.com/s/Ue2kyluuuN',NULL,1,21),
('rt-022','IKHLAS Pack of 3 Printed Women Multicolor Basic Shorts','Women','women','Shorts & Skorts',4.4,999,NULL,NULL,'images/woman/rt-022.webp','https://dl.flipkart.com/s/IVCjvnNNNN',NULL,1,22),
('rt-023','ADDYVERO Solid Women Black Skort','Women','women','Shorts & Skorts',4.3,999,NULL,NULL,'images/woman/rt-023.webp','https://dl.flipkart.com/s/U05T7suuuN',NULL,1,23),
('rt-024','MAX Solid Women Blue Skort','Women','women','Shorts & Skorts',4,999,NULL,NULL,'images/woman/rt-024.webp','https://dl.flipkart.com/s/I5BnhWNNNN',NULL,1,24),
('rt-025','Dazzle Clothes Women Solid Regular Blue Skirt','Women','women','Skirts',3.4,999,NULL,NULL,'images/woman/rt-025.webp','https://dl.flipkart.com/s/IVzRXfNNNN',NULL,1,25),
('rt-026','snkidsfashion Women Printed Regular White Skirt','Women','women','Skirts',3.6,999,NULL,NULL,'images/woman/rt-026.webp','https://dl.flipkart.com/s/IVMvcmNNNN',NULL,1,26),
('rt-027','Gravel Grit Women Printed Flared Blue Skirt','Women','women','Skirts',4.1,999,NULL,NULL,'images/woman/rt-027.webp','https://dl.flipkart.com/s/U0ndhcuuuN',NULL,1,27),
('rt-028','The Label Life Women Solid Pencil Blue Skirt','Women','women','Skirts',4,999,NULL,NULL,'images/woman/rt-028.webp','https://dl.flipkart.com/s/U0kF5euuuN',NULL,1,28),
('rt-029','MERCEL Women Wide Leg High Rise Blue Jeans','Women','women','Jeans',4,999,NULL,NULL,'images/woman/rt-029.webp','https://dl.flipkart.com/s/IVndZ_NNNN',NULL,1,29),
('rt-030','MERCEL Women Wide Leg High Rise Black Jeans','Women','women','Jeans',3.9,999,NULL,NULL,'images/woman/rt-030.webp','https://dl.flipkart.com/s/U0adAluuuN',NULL,1,30),
('rt-031','KOTTY Women Wide Leg High Rise Blue Jeans','Women','women','Jeans',4,999,NULL,NULL,'images/woman/rt-031.webp','https://dl.flipkart.com/s/IVINHfNNNN',NULL,1,31),
('rt-032','FashFun Women Fit and Flare Multicolor Maxi/Full Length Dress','Women','women','Dresses',4.2,999,NULL,NULL,'images/woman/rt-032.webp','https://dl.flipkart.com/s/U0TwmeuuuN',NULL,1,32),
('rt-033','Balajii Fashion Women Fit and Flare Multicolor, Blue Above Knee/Mid Thigh Length Dress','Women','women','Dresses',4.6,999,NULL,'Trending','images/woman/rt-033.webp','https://dl.flipkart.com/s/IVTdzzNNNN',NULL,1,33),
('rt-034','IKLO Women Maxi Blue Midi/Calf Length Dress','Women','women','Dresses',4.3,999,NULL,NULL,'images/woman/rt-034.webp','https://dl.flipkart.com/s/IVNJ_dNNNN',NULL,1,34),
('rt-035','KATECLO Women Layered Light Blue Above Knee/Mid Thigh Length Dress','Women','women','Dresses',4.1,999,NULL,NULL,'images/woman/rt-035.webp','https://dl.flipkart.com/s/UwPjlKuuuN',NULL,1,35),
('rt-036','buaGi collection Women Comfort Fit Solid Spread Collar Casual Shirt','Women','women','Topwear',4.2,999,NULL,NULL,'images/woman/rt-036.webp','https://dl.flipkart.com/s/IBy3MmNNNN',NULL,1,36),
('rt-037','Femibazaar Women Oversized Fit Solid Spread Collar Casual Shirt','Women','women','Topwear',4.5,999,NULL,'Trending','images/woman/rt-037.webp','https://dl.flipkart.com/s/IByJgmNNNN',NULL,1,37),
('rt-038','ENGLISHPREMIUM Women Printed Round Neck Cotton Blend White, Black T-Shirt','Women','women','Topwear',4,999,NULL,NULL,'images/woman/rt-038.webp','https://dl.flipkart.com/s/IV8AULNNNN',NULL,1,38),
('rt-039','THE DRY STATE Women Printed Round Neck Pure Cotton White T-Shirt','Women','women','Topwear',4.1,999,NULL,NULL,'images/woman/rt-039.webp','https://dl.flipkart.com/s/IBW!U4NNNN',NULL,1,39),
('rt-040','Greyscale Casual Regular Sleeves Solid Women Light Green Top','Women','women','Topwear',3.4,999,NULL,NULL,'images/woman/rt-040.webp','https://dl.flipkart.com/s/IVPjsRNNNN',NULL,1,40),
('rt-041','S F ENTERPRISES Casual Regular Sleeves Printed Women White, Black Top','Women','women','Topwear',4,999,NULL,NULL,'images/woman/rt-041.webp','https://dl.flipkart.com/s/IBaWKLNNNN',NULL,1,41),
('rt-042','Fastrack Tees Black Dial Black Silicone Strap Unisex Analog Watch - For Men & Women','Accessories','accessories','Watches',4.5,999,NULL,'Trending','images/Accessories/rt-042.webp','https://dl.flipkart.com/s/INRC8dNNNN',NULL,1,42),
('rt-043','Fastrack Vyb Bli Dial Pink Stainless Steel Strap Analog Watch','Accessories','accessories','Watches',4.4,999,NULL,NULL,'images/Accessories/rt-043.webp','https://dl.flipkart.com/s/IBONNLNNNN',NULL,1,43),
('rt-044','Fastrack Upgrades Analog Watch - For Men','Accessories','accessories','Watches',4.2,999,NULL,NULL,'images/Accessories/rt-044.webp','https://dl.flipkart.com/s/UuZJKNuuuN',NULL,1,44),
('rt-045','LINO PERROS Brown Women Sling Bag Ella Sylvia','Bags','accessories','Handbags',4,999,NULL,NULL,'images/Bags/rt-045.webp','https://dl.flipkart.com/s/Uw1mPCuuuN',NULL,1,45),
('rt-046','Roadster Large 37 L Laptop Backpack Water Resistant with Raincover & Bottom Pad (Black, Green)','Bags','accessories','Backpacks',4.1,999,NULL,NULL,'images/Bags/rt-046.webp','https://dl.flipkart.com/s/INmVtRNNNN',NULL,1,46),
('rt-047','Wildcraft Large 32 L Laptop Backpack','Bags','accessories','Backpacks',4.2,999,NULL,NULL,'images/Bags/rt-047.webp','https://dl.flipkart.com/s/UuUBJKuuuN',NULL,1,47),
('rt-048','Salty Celestial Sparkle Crystal Ring for Women & Girls Copper Ring','Jewelry','accessories','Rings',3.7,999,NULL,NULL,'images/Jewelry/rt-048.webp','https://dl.flipkart.com/s/!ip14ZNNNN',NULL,1,48),
('rt-049','Salty Love Gift Box for Women | Necklace | Ring','Jewelry','accessories','Gift Sets',4.5,999,NULL,'Trending','images/Jewelry/rt-049.webp','https://dl.flipkart.com/s/TDBu_wuuuN',NULL,1,49),
('rt-050','Salty Butterfly Necklace for Women','Jewelry','accessories','Necklaces',4.4,999,NULL,NULL,'images/Jewelry/rt-050.webp','https://dl.flipkart.com/s/!i1YNBNNNN',NULL,1,50),
('rt-051','Salty Butterfly Dangle Drop Earrings','Jewelry','accessories','Earrings',4,999,NULL,NULL,'images/Jewelry/rt-051.webp','https://dl.flipkart.com/s/TZe8XMuuuN',NULL,1,51),
('rt-052','Salty Glass Bracelet','Jewelry','accessories','Bracelets',3.8,999,NULL,NULL,'images/Jewelry/rt-052.webp','https://dl.flipkart.com/s/TZJyT1uuuN',NULL,1,52),
('rt-053','Salty Alpha Stainless Steel Bracelet','Jewelry','accessories','Bracelets',3.9,999,NULL,NULL,'images/Jewelry/rt-053.webp','https://dl.flipkart.com/s/TZ2wd1uuuN',NULL,1,53),
('rt-054','Salty Alpha Clavicle Diamond Necklace','Jewelry','accessories','Necklaces',4.5,999,NULL,'Trending','images/Jewelry/rt-054.webp','https://dl.flipkart.com/s/Op!yAgNNNN',NULL,1,54),
('rt-055','Skin Hunt Men Wallet','Accessories','accessories','Wallets',4.1,999,NULL,NULL,'images/Accessories/rt-055.webp','https://dl.flipkart.com/s/!iULzGNNNN',NULL,1,55),
('rt-056','Classic Carryalls waist bag (Black)','Bags','accessories','Waist Bags',4.1,999,NULL,NULL,'images/Bags/rt-056.webp','https://dl.flipkart.com/s/OpKJoGNNNN',NULL,1,56),
('rt-057','AUREN korean beauty Hair Clutches','Accessories','accessories','Hair Accessories',3.8,999,NULL,NULL,'images/Accessories/rt-057.webp','https://dl.flipkart.com/s/OpW92ZNNNN',NULL,1,57),
('rt-058','Women Casual Black Fabric Belt','Accessories','accessories','Belts',3.5,999,NULL,NULL,'images/Accessories/rt-058.webp','https://dl.flipkart.com/s/aYFQgJuuuN',NULL,1,58);
