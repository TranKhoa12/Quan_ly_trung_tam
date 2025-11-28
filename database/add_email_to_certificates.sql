-- Add email column to certificates table
-- Run this SQL in your database

ALTER TABLE `certificates` ADD COLUMN `email` VARCHAR(255) NULL AFTER `phone`;

-- Update existing records with default empty email (optional)
-- UPDATE `certificates` SET `email` = '' WHERE `email` IS NULL;
