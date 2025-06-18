# 🔄 Step-by-Step Migration: Supabase → phpMyAdmin

## 📋 **Migration Checklist**

### **Phase 1: Setup MySQL Database on Hostinger**
- [ ] Create MySQL database on Hostinger
- [ ] Note down database credentials
- [ ] Access phpMyAdmin

### **Phase 2: Export Data from Supabase**
- [ ] Export all table data from Supabase
- [ ] Download CSV files
- [ ] Verify data integrity

### **Phase 3: Create MySQL Structure**
- [ ] Run database setup script in phpMyAdmin
- [ ] Verify tables are created
- [ ] Check table structure

### **Phase 4: Import Data**
- [ ] Import CSV data into MySQL tables
- [ ] Verify data import
- [ ] Check record counts

### **Phase 5: Update Laravel Configuration**
- [ ] Update .env file with MySQL credentials
- [ ] Test database connection
- [ ] Deploy and test website

---

## 🎯 **PHASE 1: Setup MySQL Database on Hostinger**

### **Step 1.1: Create MySQL Database**
1. **Login to Hostinger Control Panel**
2. **Go to**: Website → Manage → Databases → MySQL Databases
3. **Click**: "Create Database"
4. **Fill in details**:
   - **Database Name**: `hermosawaterdistrict_db`
   - **Username**: `hermosawaterdistrict_user`
   - **Password**: (Generate strong password - save this!)
   - **Host**: Usually `localhost`

### **Step 1.2: Access phpMyAdmin**
1. **In Hostinger Control Panel**: Go to Databases → phpMyAdmin
2. **Click**: "Access phpMyAdmin"
3. **Login** with your database credentials
4. **Select** your database from the left sidebar

---

## 📤 **PHASE 2: Export Data from Supabase**

### **Step 2.1: Access Supabase SQL Editor**
1. **Go to**: https://supabase.com/dashboard
2. **Select your project**: bpdfqqvnpjpvrqpgpoqf
3. **Navigate to**: SQL Editor

### **Step 2.2: Export Each Table**
**Run each query from `export-supabase-data.sql` and export as CSV:**

1. **Users Table**:
   ```sql
   SELECT id, name, email, email_verified_at, password, remember_token, username, created_at, updated_at
   FROM users ORDER BY id;
   ```
   - Click **Run** → **Download CSV** → Save as `users.csv`

2. **Admin Table**:
   ```sql
   SELECT id, name, email, password, created_at, updated_at
   FROM admin ORDER BY id;
   ```
   - Save as `admin.csv`

3. **Customers Table**:
   ```sql
   SELECT id, name, username, password, customer_type, address, contact_number, email, account_number, meter_number, created_at, updated_at
   FROM customers_tb ORDER BY id;
   ```
   - Save as `customers_tb.csv`

4. **Continue for all tables**:
   - `staff_tb.csv`
   - `bills.csv`
   - `payments.csv`
   - `rates_tb.csv`
   - `announcements_tb.csv`

### **Step 2.3: Get Record Counts**
Run this query to verify how many records you have:
```sql
SELECT 'users' as table_name, COUNT(*) as record_count FROM users
UNION ALL SELECT 'admin', COUNT(*) FROM admin
UNION ALL SELECT 'customers_tb', COUNT(*) FROM customers_tb
UNION ALL SELECT 'staff_tb', COUNT(*) FROM staff_tb
UNION ALL SELECT 'bills', COUNT(*) FROM bills
UNION ALL SELECT 'payments', COUNT(*) FROM payments
UNION ALL SELECT 'rates_tb', COUNT(*) FROM rates_tb
UNION ALL SELECT 'announcements_tb', COUNT(*) FROM announcements_tb;
```
**Write down these numbers** - you'll verify them after import.

---

## 🏗️ **PHASE 3: Create MySQL Structure**

### **Step 3.1: Import Database Structure**
1. **In phpMyAdmin**: Select your database
2. **Click**: "SQL" tab
3. **Copy and paste** the entire content from `mysql-database-setup.sql`
4. **Click**: "Go" to execute
5. **Verify**: You should see "MySQL database structure created successfully!"

### **Step 3.2: Verify Tables Created**
Check that these tables exist in the left sidebar:
- admin
- users  
- customers_tb
- staff_tb
- bills
- payments
- rates_tb
- announcements_tb
- migrations
- password_resets
- personal_access_tokens
- failed_jobs

---

## 📥 **PHASE 4: Import Data into MySQL**

### **Step 4.1: Import CSV Files**

**For each table, follow these steps:**

1. **Click on table name** in left sidebar (e.g., "users")
2. **Click "Import" tab**
3. **Choose file**: Select your CSV file (e.g., users.csv)
4. **Set format**: CSV
5. **Configure import options**:
   - ✅ **"The first line of the file contains the table column names"**
   - **Columns separated by**: `,` (comma)
   - **Columns enclosed by**: `"` (double quote)
   - **Lines terminated by**: `auto`
6. **Click**: "Go"

### **Step 4.2: Import Order (Important!)**
Import tables in this order to avoid foreign key issues:

1. **admin** (no dependencies)
2. **users** (no dependencies)
3. **customers_tb** (no dependencies)
4. **staff_tb** (no dependencies)
5. **rates_tb** (no dependencies)
6. **announcements_tb** (no dependencies)
7. **bills** (depends on customers_tb)
8. **payments** (depends on customers_tb, bills, users)

### **Step 4.3: Handle Import Errors**

**If you get foreign key errors:**
1. **Temporarily disable foreign key checks**:
   ```sql
   SET foreign_key_checks = 0;
   ```
2. **Import your data**
3. **Re-enable foreign key checks**:
   ```sql
   SET foreign_key_checks = 1;
   ```

**If you get date/time format errors:**
- Open CSV in Excel/text editor
- Make sure dates are in format: `YYYY-MM-DD HH:MM:SS`
- NULL values should be empty (not "NULL" text)

### **Step 4.4: Verify Import**
After importing each table, check:
1. **Click on table name**
2. **Click "Browse" tab**
3. **Verify**: Record count matches your Supabase export
4. **Check**: Data looks correct

---

## ⚙️ **PHASE 5: Update Laravel Configuration**

### **Step 5.1: Update Environment File**
1. **Replace your `.env` file** with content from `env-mysql-production.txt`
2. **Update these values** with your actual Hostinger MySQL credentials:
   ```env
   DB_HOST=localhost  # or your Hostinger DB host
   DB_DATABASE=hermosawaterdistrict_db  # your actual DB name
   DB_USERNAME=hermosawaterdistrict_user  # your actual username
   DB_PASSWORD=your_actual_mysql_password  # your actual password
   ```

### **Step 5.2: Test Database Connection**
1. **Upload** the updated `.env` file to your Hostinger
2. **Visit**: `https://hermosawaterdistrict.com/test-database.php`
3. **Expected result**: MySQL connection successful ✅

### **Step 5.3: Clear Laravel Cache**
If you have SSH access, run:
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

---

## ✅ **PHASE 6: Final Verification**

### **Verification Checklist**
- [ ] Website loads without 500 errors
- [ ] Login functionality works
- [ ] Customer data displays correctly
- [ ] Bills and payments show up
- [ ] All Laravel features work
- [ ] No database connection errors

### **Test These Functions**:
1. **Admin Login**: Try logging in as admin
2. **Customer Login**: Try customer login
3. **View Customers**: Check customer list
4. **View Bills**: Check if bills display
5. **View Payments**: Check payment records
6. **Create Test Data**: Try creating a new record

---

## 🚨 **Troubleshooting Common Issues**

### **Issue 1: CSV Import Fails**
**Solution**: 
- Check CSV format (UTF-8 encoding)
- Remove any special characters
- Make sure date formats are correct

### **Issue 2: Foreign Key Errors**
**Solution**:
```sql
SET foreign_key_checks = 0;
-- Import your data
SET foreign_key_checks = 1;
```

### **Issue 3: Laravel Still Connects to PostgreSQL**
**Solution**:
- Clear Laravel config cache
- Make sure `.env` file is correctly uploaded
- Check file permissions on `.env`

### **Issue 4: Missing Records After Import**
**Solution**:
- Check for duplicate key errors in phpMyAdmin
- Verify CSV file wasn't truncated
- Re-export and re-import problem table

---

## 🎉 **Success!**

Once all steps are complete:
- ✅ Your Supabase PostgreSQL data is now in MySQL
- ✅ Laravel is configured for MySQL
- ✅ Website should work perfectly on Hostinger
- ✅ You can keep Supabase as backup until fully verified

---

**Need help with any specific step? Let me know which phase you're working on!** 