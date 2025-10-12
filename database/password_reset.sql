-- Add password reset tokens table
CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Add email column to users table if not exists
ALTER TABLE users ADD COLUMN IF NOT EXISTS email VARCHAR(100) AFTER full_name;

-- Update sample users with email
UPDATE users SET email = 'admin@example.com' WHERE username = 'admin';
UPDATE users SET email = 'staff1@example.com' WHERE username = 'staff1';