
-- Main users table
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `first_name` VARCHAR(100),
  `last_name` VARCHAR(100),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Matches table (already exists - referenced as 2025_01_12_00_35_22_matches)
-- This table contains the original match data
-- +---------+---------+---------+---------+---------+---------+
-- | COL 1   | COL 2   | COL 3   | COL 4   | COL 5   | COL 6   |
-- +---------+---------+---------+---------+---------+---------+
-- Format: COL 1 = User email, COL 2-6 = Match emails

-- Completed matches and feedback table
CREATE TABLE IF NOT EXISTS `completed_matches` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_email` VARCHAR(255) NOT NULL,
  `match_email` VARCHAR(255) NOT NULL,
  `start_time` TIMESTAMP NULL,
  `end_time` TIMESTAMP NULL,
  `feedback_rating` INT NULL,
  `feedback_comments` TEXT NULL,
  `meet_again` BOOLEAN DEFAULT FALSE,
  `feedback_provided` BOOLEAN DEFAULT FALSE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `user_match_idx` (`user_email`, `match_email`),
  INDEX `match_user_idx` (`match_email`, `user_email`)
);

-- Table to track user sessions
CREATE TABLE IF NOT EXISTS `user_sessions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_email` VARCHAR(255) NOT NULL,
  `session_token` VARCHAR(255) NOT NULL,
  `expires_at` TIMESTAMP NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `session_token_idx` (`session_token`),
  INDEX `user_email_idx` (`user_email`)
);
