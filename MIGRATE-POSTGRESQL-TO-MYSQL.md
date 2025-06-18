# 🔄 PostgreSQL to MySQL Migration Guide

## 📋 **Overview**
This guide will help you migrate your Hermosa Water District database from PostgreSQL (Supabase) to MySQL (Hostinger).

## 🎯 **Migration Steps Overview**

1. **Export data from Supabase (PostgreSQL)**
2. **Create MySQL database on Hostinger**
3. **Convert PostgreSQL schema to MySQL**
4. **Update Laravel configuration**
5. **Import data to MySQL**
6. **Test and verify**

---

## 📤 **Step 1: Export Data from Supabase**

### **Method 1: Using Supabase Dashboard**
1. **Login to Supabase Dashboard**: https://supabase.com
2. **Go to your project**: bpdfqqvnpjpvrqpgpoqf
3. **Navigate to**: Settings → Database → Database backups
4. **Download backup** or use SQL Editor

### **Method 2: Export Individual Tables**
1. **Go to**: Table Editor in Supabase
2. **For each table**, click **Export** → **CSV**
3. **Download all table data**

### **Method 3: SQL Export (Recommended)**
Run this in Supabase SQL Editor to get your data:

```sql
-- Export all tables structure and data
\copy (SELECT * FROM users) TO 'users.csv' WITH CSV HEADER;
\copy (SELECT * FROM customers) TO 'customers.csv' WITH CSV HEADER;
\copy (SELECT * FROM staff) TO 'staff.csv' WITH CSV HEADER;
\copy (SELECT * FROM bills) TO 'bills.csv' WITH CSV HEADER;
\copy (SELECT * FROM payments) TO 'payments.csv' WITH CSV HEADER;
\copy (SELECT * FROM rates_tb) TO 'rates_tb.csv' WITH CSV HEADER;
\copy (SELECT * FROM announcements_tb) TO 'announcements_tb.csv' WITH CSV HEADER;
-- Add other tables as needed
```

---

## 🗄️ **Step 2: Create MySQL Database on Hostinger**

### **In Hostinger Control Panel:**
1. **Go to**: Hosting → Manage → Databases → MySQL Databases
2. **Create new database**:
   - **Database name**: `hermosawaterdistrict_db`
   - **Username**: `hermosawaterdistrict_user`
   - **Password**: (generate strong password)
3. **Note down the credentials**:
   - Host: Usually `localhost` or specific host
   - Port: `3306`
   - Database name, username, password

---

## ⚙️ **Step 3: Update Laravel Configuration**

### **Update your `.env` file:**
```env
APP_NAME="Hermosa Water District"
APP_ENV=production
APP_KEY=base64:87ZG61ohphum1Y72rfhtdXAR3vT7FTNFik2sTdC39cE=
APP_DEBUG=false
APP_URL=https://hermosawaterdistrict.com

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

# MySQL Configuration (Updated)
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=hermosawaterdistrict_db
DB_USERNAME=hermosawaterdistrict_user
DB_PASSWORD=your_mysql_password

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=your-mail-host
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@hermosawaterdistrict.com"
MAIL_FROM_NAME="${APP_NAME}"

# Remove Supabase config or keep for backup
# SUPABASE_URL=https://bpdfqqvnpjpvrqpgpoqf.supabase.co
# SUPABASE_ANON_KEY=...
# SUPABASE_SERVICE_ROLE_KEY=...
```

---

## 🔧 **Step 4: Convert Laravel Migrations for MySQL**

### **Check Your Migrations for PostgreSQL-specific Code:**

Common PostgreSQL features that need conversion:

1. **UUID columns** → Use `string` or `bigint`
2. **JSON columns** → MySQL supports JSON since 5.7
3. **Array columns** → Convert to JSON or separate tables
4. **Boolean columns** → MySQL uses TINYINT(1)
5. **Text search** → Use MySQL FULLTEXT

### **Review Migration Files:**
Let me check your migrations for any PostgreSQL-specific code that needs updating.

---

## 📥 **Step 5: Run Migrations on MySQL**

### **Fresh Migration (Recommended):**
```bash
# Clear all caches
php artisan config:clear
php artisan cache:clear

# Run fresh migrations
php artisan migrate:fresh

# Or if you want to keep existing data structure
php artisan migrate
```

---

## 📊 **Step 6: Import Your Data**

### **Method 1: Using phpMyAdmin (Hostinger)**
1. **Access phpMyAdmin** from Hostinger control panel
2. **Select your database**
3. **Import** → Choose CSV files
4. **Map columns** correctly for each table

### **Method 2: SQL Import Script**
Create SQL INSERT statements from your exported data.

### **Method 3: Laravel Seeders (Recommended)**
I'll create seeders that can import your CSV data.

---

## ✅ **Step 7: Verification Checklist**

After migration, verify:

- [ ] All tables exist with correct structure
- [ ] All data imported correctly
- [ ] User authentication works
- [ ] Application functionality works
- [ ] No errors in logs

---

## 🚨 **Important Notes**

### **Data Type Differences:**
- **PostgreSQL UUID** → **MySQL VARCHAR(36)** or **CHAR(36)**
- **PostgreSQL SERIAL** → **MySQL AUTO_INCREMENT**
- **PostgreSQL BOOLEAN** → **MySQL TINYINT(1)**
- **PostgreSQL TIMESTAMP WITH TIME ZONE** → **MySQL TIMESTAMP**

### **Backup Strategy:**
- Keep Supabase database as backup until migration is fully verified
- Export all data before starting migration
- Test migration on a staging environment first

---

## 🔧 **Next Steps**

1. **Export your Supabase data** (CSV or SQL dump)
2. **Create MySQL database** on Hostinger  
3. **Send me your exported data** or table structure
4. **I'll help convert and create import scripts**
5. **Update Laravel configuration**
6. **Test migration**

Would you like me to start by examining your current database structure and creating the conversion scripts? 