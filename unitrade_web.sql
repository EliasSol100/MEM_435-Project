CREATE DATABASE IF NOT EXISTS unitrade_cy CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE unitrade_cy;

DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS wishlists;
DROP TABLE IF EXISTS listing_reports;
DROP TABLE IF EXISTS listings;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS password_resets;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    first_name VARCHAR(100) NULL,
    middle_name VARCHAR(100) NULL,
    last_name VARCHAR(100) NULL,
    username VARCHAR(80) NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    is_verified TINYINT(1) NOT NULL DEFAULT 0,
    verification_expires_at DATETIME NULL,
    verification_code VARCHAR(10) NULL,
    university VARCHAR(150) NOT NULL,
    phone VARCHAR(30) NULL,
    country VARCHAR(100) NULL,
    city VARCHAR(100) NULL,
    address VARCHAR(255) NULL,
    postcode VARCHAR(20) NULL,
    dob DATE NULL,
    profile_complete TINYINT(1) NOT NULL DEFAULT 0,
    bio TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME NULL
) ENGINE=InnoDB;

CREATE TABLE password_resets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    token_hash CHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_password_resets_user_id (user_id),
    CONSTRAINT fk_password_resets_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE listings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    condition_label ENUM('New', 'Like New', 'Good', 'Fair') NOT NULL DEFAULT 'Good',
    item_type ENUM('Item', 'Notes', 'Service') NOT NULL DEFAULT 'Item',
    image_path VARCHAR(255) NULL,
    image_url VARCHAR(255) NULL,
    university_target VARCHAR(150) NULL,
    status ENUM('Active', 'Sold') NOT NULL DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_listings_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_listings_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE wishlists (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    listing_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_wishlist (user_id, listing_id),
    CONSTRAINT fk_wishlists_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_wishlists_listing FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE listing_reports (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    listing_id INT UNSIGNED NOT NULL,
    reporter_user_id INT UNSIGNED NOT NULL,
    reason VARCHAR(50) NOT NULL,
    details TEXT NULL,
    status ENUM('Open', 'Reviewed', 'Resolved', 'Dismissed') NOT NULL DEFAULT 'Open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_listing_reports_listing_id (listing_id),
    INDEX idx_listing_reports_reporter_user_id (reporter_user_id),
    INDEX idx_listing_reports_status (status),
    CONSTRAINT fk_listing_reports_listing FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE,
    CONSTRAINT fk_listing_reports_reporter FOREIGN KEY (reporter_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE reviews (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reviewer_id INT UNSIGNED NOT NULL,
    reviewed_user_id INT UNSIGNED NOT NULL,
    rating TINYINT UNSIGNED NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_review_pair (reviewer_id, reviewed_user_id),
    CONSTRAINT fk_reviews_reviewer FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_reviews_reviewed FOREIGN KEY (reviewed_user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT chk_reviews_rating CHECK (rating BETWEEN 1 AND 5)
) ENGINE=InnoDB;

INSERT INTO categories (name) VALUES
('Books'),
('Notes'),
('Electronics'),
('Accessories'),
('Tutoring'),
('Design Services'),
('Programming Help'),
('Other');

-- Demo accounts (password for both: password123)
INSERT INTO users (
    full_name,
    first_name,
    middle_name,
    last_name,
    username,
    email,
    password_hash,
    is_verified,
    university,
    phone,
    country,
    city,
    address,
    postcode,
    dob,
    profile_complete,
    bio
) VALUES
(
    'Elias Solomonides',
    'Elias',
    NULL,
    'Solomonides',
    'elias_sol',
    'elias@example.com',
    '$2y$12$.ST.wDRrLgsSZtGnUz.o6uk0khtmI5tECgY9c8eNfaVlQEJgh8idK',
    1,
    'Cyprus University of Technology',
    '+35799111222',
    'Cyprus',
    'Limassol',
    '5 Darvinou Street',
    '3041',
    '2003-11-26',
    1,
    'Computer Science student selling useful academic items.'
),
(
    'Anna Nicolaou',
    'Anna',
    NULL,
    'Nicolaou',
    'anna.n',
    'anna@example.com',
    '$2y$12$jlfpAqG24U4WSQtw7P5oHeFmYEpOKHlA3bN7oerbHqpQ51ogF1ouS',
    1,
    'University of Cyprus',
    '+35799111333',
    'Cyprus',
    'Nicosia',
    '21 Student Avenue',
    '2008',
    '2002-05-14',
    1,
    'Design student offering notes and tutoring.'
);

-- Demo listings
INSERT INTO listings (user_id, category_id, title, description, price, condition_label, item_type, image_url, university_target, status) VALUES
(1, 1, 'Data Structures Textbook', 'Used textbook in very good condition. Ideal for first-year CS students.', 18.00, 'Good', 'Item', 'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=1200&q=80', 'Cyprus University of Technology', 'Active'),
(2, 2, 'Marketing Notes Bundle', 'Complete semester notes with summaries, diagrams, and exam tips.', 8.50, 'Like New', 'Notes', 'https://images.unsplash.com/photo-1455390582262-044cdead277a?auto=format&fit=crop&w=1200&q=80', 'University of Cyprus', 'Active'),
(2, 5, '1-on-1 Design Tutoring', 'Hourly tutoring sessions for Adobe Illustrator, Canva, and presentation design.', 15.00, 'New', 'Service', 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=1200&q=80', 'University of Cyprus', 'Active');

-- Demo reviews
INSERT INTO reviews (reviewer_id, reviewed_user_id, rating, comment) VALUES
(1, 2, 5, 'Very helpful and friendly seller. The notes were clean and useful.'),
(2, 1, 4, 'Smooth communication and fair pricing.');

-- Demo wishlist entries
INSERT INTO wishlists (user_id, listing_id) VALUES
(1, 2),
(2, 1);
