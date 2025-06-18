-- Check if rates_tb table exists and what columns it has
SELECT table_name, table_schema 
FROM information_schema.tables 
WHERE table_name = 'rates_tb';

-- Get all columns in rates_tb
SELECT column_name, data_type, is_nullable, column_default
FROM information_schema.columns 
WHERE table_name = 'rates_tb' 
ORDER BY ordinal_position;

-- Check what data is currently in the table
SELECT COUNT(*) as total_rows FROM rates_tb;

-- Show a sample of existing data to see the structure
SELECT * FROM rates_tb LIMIT 3; 