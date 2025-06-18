-- Export Data from Supabase PostgreSQL Database
-- Run these queries in Supabase SQL Editor to export your data

-- 1. Export Users Table
SELECT 
    id,
    name,
    email,
    email_verified_at,
    password,
    remember_token,
    username,
    created_at,
    updated_at
FROM users
ORDER BY id;

-- 2. Export Admin Table  
SELECT 
    id,
    name,
    email,
    password,
    created_at,
    updated_at
FROM admin
ORDER BY id;

-- 3. Export Customers Table
SELECT 
    id,
    name,
    username,
    password,
    customer_type,
    address,
    contact_number,
    email,
    account_number,
    meter_number,
    created_at,
    updated_at
FROM customers_tb
ORDER BY id;

-- 4. Export Staff Table
SELECT 
    id,
    name,
    email,
    email_verified_at,
    password,
    role,
    is_active,
    remember_token,
    created_at,
    updated_at
FROM staff_tb
ORDER BY id;

-- 5. Export Bills Table
SELECT 
    id,
    customer_id,
    bill_number,
    account_number,
    meter_number,
    billing_period_start,
    billing_period_end,
    due_date,
    previous_reading,
    current_reading,
    consumption,
    rate_per_cubic_meter,
    amount_due,
    status,
    created_at,
    updated_at
FROM bills
ORDER BY id;

-- 6. Export Payments Table
SELECT 
    id,
    customer_id,
    bill_id,
    payment_number,
    amount,
    payment_type,
    payment_method,
    proof_of_payment,
    account_number,
    meter_number,
    remarks,
    status,
    remaining_balance,
    approved_by,
    approved_at,
    created_at,
    updated_at
FROM payments
ORDER BY id;

-- 7. Export Rates Table
SELECT 
    id,
    customer_type,
    min_consumption,
    max_consumption,
    rate_per_cubic_meter,
    base_rate,
    created_at,
    updated_at
FROM rates_tb
ORDER BY id;

-- 8. Export Announcements Table
SELECT 
    id,
    title,
    content,
    is_active,
    duration,
    created_at,
    updated_at
FROM announcements_tb
ORDER BY id;

-- 9. Get table record counts for verification
SELECT 
    'users' as table_name, 
    COUNT(*) as record_count 
FROM users
UNION ALL
SELECT 
    'admin' as table_name, 
    COUNT(*) as record_count 
FROM admin
UNION ALL
SELECT 
    'customers_tb' as table_name, 
    COUNT(*) as record_count 
FROM customers_tb
UNION ALL
SELECT 
    'staff_tb' as table_name, 
    COUNT(*) as record_count 
FROM staff_tb
UNION ALL
SELECT 
    'bills' as table_name, 
    COUNT(*) as record_count 
FROM bills
UNION ALL
SELECT 
    'payments' as table_name, 
    COUNT(*) as record_count 
FROM payments
UNION ALL
SELECT 
    'rates_tb' as table_name, 
    COUNT(*) as record_count 
FROM rates_tb
UNION ALL
SELECT 
    'announcements_tb' as table_name, 
    COUNT(*) as record_count 
FROM announcements_tb
ORDER BY table_name;

-- Instructions:
-- 1. Run each SELECT query individually in Supabase SQL Editor
-- 2. Export results as CSV for each table
-- 3. Save CSV files with table names (e.g., users.csv, customers_tb.csv)
-- 4. Use these CSV files to import into MySQL database 