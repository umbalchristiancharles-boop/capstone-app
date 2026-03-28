-- Update existing products with categories and expiration dates
-- Run this script to populate NULL values in category and expires_at columns

-- Update Samjang (Condiment)
UPDATE products SET category = 'Condiment', expires_at = DATE_ADD(NOW(), INTERVAL 90 DAY) WHERE id = 29 AND name = 'Samjang';

-- Update Seaweeds (Vegetable)
UPDATE products SET category = 'Vegetable', expires_at = DATE_ADD(NOW(), INTERVAL 60 DAY) WHERE id = 38 AND name = 'Seaweeds';

-- Update frozen hot Dogs (Meat)
UPDATE products SET category = 'Meat', expires_at = DATE_ADD(NOW(), INTERVAL 30 DAY) WHERE id = 45 AND name = 'frozen hot Dogs';

-- Update Flour (Grain)
UPDATE products SET category = 'Grain', expires_at = DATE_ADD(NOW(), INTERVAL 180 DAY) WHERE id = 46 AND name = 'Flour';

-- Update Hot Dog (Meat - Kitchen Dish)
UPDATE products SET category = 'Meat', expires_at = DATE_ADD(NOW(), INTERVAL 30 DAY) WHERE id = 47 AND name = 'Hot Dog';

-- Update Corn Dog (Meat - Kitchen Dish)
UPDATE products SET category = 'Meat', expires_at = DATE_ADD(NOW(), INTERVAL 30 DAY) WHERE id = 48 AND name = 'Corn Dog';

-- Update Water Bottle (Beverage)
UPDATE products SET category = 'Beverage', expires_at = DATE_ADD(NOW(), INTERVAL 365 DAY) WHERE id = 49 AND name = 'Water Bottle';

-- Update Salt (Condiment/Spice)
UPDATE products SET category = 'Spice', expires_at = DATE_ADD(NOW(), INTERVAL 365 DAY) WHERE id = 50 AND name = 'Salt';

-- Update Kimchi (Condiment)
UPDATE products SET category = 'Condiment', expires_at = DATE_ADD(NOW(), INTERVAL 90 DAY) WHERE id = 51 AND name = 'Kimchi';

-- Verify the updates
SELECT id, name, category, expires_at FROM products WHERE category IS NOT NULL ORDER BY id;
