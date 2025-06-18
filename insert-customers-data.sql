-- ============================================
-- INSERT CUSTOMERS DATA into customers_tb
-- Copy and paste these into phpMyAdmin SQL tab
-- ============================================

-- Note: Your table already has 1 customer (Gian Carlo S. Victorino)
-- These queries will add more sample customers

-- 1. Insert Residential Customers
INSERT INTO customers_tb (
    username, 
    password, 
    email, 
    first_name, 
    last_name, 
    full_name, 
    phone_number, 
    account_number, 
    customer_type, 
    address, 
    created_at, 
    updated_at
) VALUES 
(
    'maria_santos',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'maria.santos@gmail.com',
    'Maria',
    'Santos',
    'Maria Santos',
    '09171234567',
    '25-551679',
    'residential',
    '123 Mabini Street, Hermosa, Bataan',
    NOW(),
    NOW()
),
(
    'juan_dela_cruz',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'juan.delacruz@yahoo.com',
    'Juan',
    'Dela Cruz',
    'Juan Dela Cruz',
    '09281234567',
    '25-551680',
    'residential',
    '456 Rizal Avenue, Hermosa, Bataan',
    NOW(),
    NOW()
),
(
    'ana_garcia',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'ana.garcia@gmail.com',
    'Ana',
    'Garcia',
    'Ana Garcia',
    '09391234567',
    '25-551681',
    'residential',
    '789 Bonifacio Street, Hermosa, Bataan',
    NOW(),
    NOW()
),
(
    'pedro_reyes',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'pedro.reyes@hotmail.com',
    'Pedro',
    'Reyes',
    'Pedro Reyes',
    '09451234567',
    '25-551682',
    'residential',
    '321 Luna Street, Hermosa, Bataan',
    NOW(),
    NOW()
),
(
    'rosa_martinez',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'rosa.martinez@gmail.com',
    'Rosa',
    'Martinez',
    'Rosa Martinez',
    '09561234567',
    '25-551683',
    'residential',
    '654 Del Pilar Street, Hermosa, Bataan',
    NOW(),
    NOW()
);

-- 2. Insert Commercial Customers
INSERT INTO customers_tb (
    username, 
    password, 
    email, 
    first_name, 
    last_name, 
    full_name, 
    phone_number, 
    account_number, 
    customer_type, 
    address, 
    created_at, 
    updated_at
) VALUES 
(
    'hermosa_sari_store',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'hermosa.saristore@business.com',
    'Store',
    'Manager',
    'Hermosa Sari-Sari Store',
    '09671234567',
    '30-551001',
    'commercial',
    '100 Market Street, Hermosa, Bataan',
    NOW(),
    NOW()
),
(
    'bataan_hotel',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'bataan.hotel@business.com',
    'Hotel',
    'Manager',
    'Bataan Peninsula Hotel',
    '09781234567',
    '30-551002',
    'commercial',
    '200 Tourism Road, Hermosa, Bataan',
    NOW(),
    NOW()
),
(
    'pacific_restaurant',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'pacific.restaurant@business.com',
    'Restaurant',
    'Owner',
    'Pacific View Restaurant',
    '09891234567',
    '30-551003',
    'commercial',
    '300 Coastal Highway, Hermosa, Bataan',
    NOW(),
    NOW()
);

-- 3. Insert Government Customers
INSERT INTO customers_tb (
    username, 
    password, 
    email, 
    first_name, 
    last_name, 
    full_name, 
    phone_number, 
    account_number, 
    customer_type, 
    address, 
    created_at, 
    updated_at
) VALUES 
(
    'hermosa_municipal',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'municipal.office@hermosa.gov.ph',
    'Municipal',
    'Office',
    'Hermosa Municipal Office',
    '09901234567',
    '40-551001',
    'government',
    'Municipal Building, Hermosa, Bataan',
    NOW(),
    NOW()
),
(
    'hermosa_school',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'hermosa.school@deped.gov.ph',
    'School',
    'Principal',
    'Hermosa Elementary School',
    '09011234567',
    '40-551002',
    'government',
    'School Compound, Hermosa, Bataan',
    NOW(),
    NOW()
),
(
    'barangay_hall',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'barangay.hall@hermosa.gov.ph',
    'Barangay',
    'Captain',
    'Barangay Hall Hermosa',
    '09121234567',
    '40-551003',
    'government',
    'Barangay Center, Hermosa, Bataan',
    NOW(),
    NOW()
);

-- ============================================
-- VERIFY INSERTED DATA
-- ============================================

-- 4. Count customers after insert
SELECT 
    'Total Customers' as description,
    COUNT(*) as count 
FROM customers_tb
UNION ALL
SELECT 
    'Residential Customers',
    COUNT(*) 
FROM customers_tb 
WHERE customer_type = 'residential'
UNION ALL
SELECT 
    'Commercial Customers',
    COUNT(*) 
FROM customers_tb 
WHERE customer_type = 'commercial'
UNION ALL
SELECT 
    'Government Customers',
    COUNT(*) 
FROM customers_tb 
WHERE customer_type = 'government';

-- 5. Show all customers summary
SELECT 
    id,
    username,
    full_name,
    email,
    customer_type,
    account_number,
    phone_number,
    address
FROM customers_tb 
ORDER BY customer_type, full_name;

-- ============================================
-- ADDITIONAL USEFUL QUERIES
-- ============================================

-- 6. Update address for existing customer (Gian Carlo)
UPDATE customers_tb 
SET 
    address = '10 Harvard Street, Hermosa, Bataan',
    updated_at = NOW()
WHERE username = 'giancarlo';

-- 7. Add more residential customers with different account number pattern
INSERT INTO customers_tb (
    username, 
    password, 
    email, 
    first_name, 
    last_name, 
    full_name, 
    phone_number, 
    account_number, 
    customer_type, 
    address, 
    created_at, 
    updated_at
) VALUES 
(
    'carlos_mendoza',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'carlos.mendoza@gmail.com',
    'Carlos',
    'Mendoza',
    'Carlos Mendoza',
    '09131234567',
    '25-551684',
    'residential',
    '987 Quezon Avenue, Hermosa, Bataan',
    NOW(),
    NOW()
),
(
    'lucia_torres',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'lucia.torres@yahoo.com',
    'Lucia',
    'Torres',
    'Lucia Torres',
    '09241234567',
    '25-551685',
    'residential',
    '147 Magsaysay Street, Hermosa, Bataan',
    NOW(),
    NOW()
);

-- ============================================
-- CUSTOMER DATA MANAGEMENT QUERIES
-- ============================================

-- 8. Find customers without addresses
SELECT 
    id,
    username,
    full_name,
    email,
    account_number
FROM customers_tb 
WHERE address IS NULL OR address = '';

-- 9. Update all NULL addresses to a default value
UPDATE customers_tb 
SET 
    address = 'Address Not Provided, Hermosa, Bataan',
    updated_at = NOW()
WHERE address IS NULL OR address = '';

-- 10. Generate account numbers for customers without them
-- (This is just an example - adjust as needed)
UPDATE customers_tb 
SET 
    account_number = CONCAT(
        CASE 
            WHEN customer_type = 'residential' THEN '25-'
            WHEN customer_type = 'commercial' THEN '30-'
            WHEN customer_type = 'government' THEN '40-'
        END,
        LPAD(id + 551677, 6, '0')
    ),
    updated_at = NOW()
WHERE account_number IS NULL OR account_number = '';

-- ============================================
-- INSTRUCTIONS:
-- 1. Run INSERT statements one by one or all at once
-- 2. Use the verification queries to check results
-- 3. Modify the sample data as needed for your requirements
-- ============================================ 