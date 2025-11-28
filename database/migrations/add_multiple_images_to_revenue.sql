-- Migration: Change revenue confirmation_image to support multiple images
-- Created: 2025-11-28

-- Step 1: Add new column for JSON array of images
ALTER TABLE revenue_reports 
ADD COLUMN confirmation_images TEXT AFTER confirmation_image
COMMENT 'JSON array of multiple confirmation images';

-- Step 2: Migrate existing single image data to array format
UPDATE revenue_reports 
SET confirmation_images = CONCAT('["', confirmation_image, '"]')
WHERE confirmation_image IS NOT NULL AND confirmation_image != '';

-- Step 3: For records with no image, set to empty array
UPDATE revenue_reports 
SET confirmation_images = '[]'
WHERE confirmation_image IS NULL OR confirmation_image = '';

-- Step 4: Drop old single image column (optional - uncomment if you want to remove it)
-- ALTER TABLE revenue_reports DROP COLUMN confirmation_image;

-- Verification query
SELECT id, confirmation_image, confirmation_images 
FROM revenue_reports 
LIMIT 5;
