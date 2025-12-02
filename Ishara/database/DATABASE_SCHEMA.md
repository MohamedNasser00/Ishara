# Database Schema - Ishara Application

## نظرة عامة
هذا الملف يحتوي على مخطط قاعدة البيانات الكامل لتطبيق Ishara لتعليم لغة الإشارة.

---

## الجداول (Tables)

### 1. **users** (المستخدمون)
جدول المستخدمين الأساسي

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PRIMARY KEY, AUTO_INCREMENT | المعرف الفريد |
| first_name | string(255) | NOT NULL | الاسم الأول |
| last_name | string(255) | NOT NULL | الاسم الأخير |
| email | string(255) | UNIQUE, NOT NULL | البريد الإلكتروني |
| password | string(255) | NOT NULL | كلمة المرور (مشفرة) |
| phone | string(255) | UNIQUE, NULLABLE | رقم الهاتف |
| gender | enum('male', 'female') | NOT NULL | النوع |
| date_of_birth | date | NULLABLE | تاريخ الميلاد |
| parent_name | string(255) | NULLABLE | اسم ولي الأمر |
| parent_number | string(255) | NULLABLE | رقم ولي الأمر |
| theme_preference | enum('light', 'dark') | DEFAULT 'light' | تفضيل الوضع (فاتح/داكن) |
| email_verified_at | timestamp | NULLABLE | تاريخ التحقق من البريد |
| is_verified | boolean | DEFAULT false | حالة التحقق |
| is_admin | boolean | DEFAULT false | هل هو مدير |
| otp_code | string(255) | NULLABLE | رمز OTP الحالي |
| otp_expires_at | timestamp | NULLABLE | تاريخ انتهاء OTP |
| remember_token | string(100) | NULLABLE | Token للتذكر |
| created_at | timestamp | NULLABLE | تاريخ الإنشاء |
| updated_at | timestamp | NULLABLE | تاريخ التحديث |
| deleted_at | timestamp | NULLABLE | تاريخ الحذف (Soft Delete) |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (email)
- UNIQUE (phone)
- INDEX (email)

---

### 2. **otp_codes** (رموز التحقق)
جدول لتخزين رموز OTP للتحقق من البريد وإعادة تعيين كلمة المرور

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PRIMARY KEY, AUTO_INCREMENT | المعرف الفريد |
| user_id | bigint | FOREIGN KEY → users.id, NULLABLE | معرف المستخدم |
| email | string(255) | NOT NULL | البريد الإلكتروني |
| otp_code | string(6) | NOT NULL | رمز OTP (6 أرقام) |
| type | enum('verification', 'password_reset') | NOT NULL | نوع OTP |
| is_used | boolean | DEFAULT false | هل تم استخدامه |
| expires_at | timestamp | NOT NULL | تاريخ انتهاء الصلاحية |
| created_at | timestamp | NULLABLE | تاريخ الإنشاء |
| updated_at | timestamp | NULLABLE | تاريخ التحديث |

**Indexes:**
- PRIMARY KEY (id)
- FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
- INDEX (email, otp_code)
- INDEX (expires_at)

---

### 3. **levels** (المستويات)
جدول المستويات التعليمية (4 مستويات)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PRIMARY KEY, AUTO_INCREMENT | المعرف الفريد |
| level_number | integer | UNIQUE, NOT NULL | رقم المستوى (1, 2, 3, 4) |
| name | string(255) | NOT NULL | اسم المستوى (مثل "Level One") |
| letters_covered | string(255) | NULLABLE | الحروف المشمولة (مثل "ABCELOVWUY") |
| description | text | NULLABLE | وصف المستوى |
| order | integer | NOT NULL | ترتيب المستوى |
| created_at | timestamp | NULLABLE | تاريخ الإنشاء |
| updated_at | timestamp | NULLABLE | تاريخ التحديث |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (level_number)
- INDEX (order)

---

### 4. **letters** (الحروف)
جدول الحروف التي يتم تعلمها في كل مستوى

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PRIMARY KEY, AUTO_INCREMENT | المعرف الفريد |
| level_id | bigint | FOREIGN KEY → levels.id, NOT NULL | معرف المستوى |
| letter | string(1) | NOT NULL | الحرف (A, B, C, etc.) |
| name | string(255) | NOT NULL | اسم الحرف (مثل "Letter A") |
| key_steps | json | NULLABLE | الخطوات الأساسية (JSON Array) |
| common_mistakes | json | NULLABLE | الأخطاء الشائعة (JSON Array) |
| order_in_level | integer | NOT NULL | ترتيب الحرف في المستوى |
| created_at | timestamp | NULLABLE | تاريخ الإنشاء |
| updated_at | timestamp | NULLABLE | تاريخ التحديث |

**Indexes:**
- PRIMARY KEY (id)
- FOREIGN KEY (level_id) REFERENCES levels(id) ON DELETE CASCADE
- UNIQUE (level_id, letter)
- INDEX (level_id, order_in_level)

---

### 5. **words** (الكلمات)
جدول الكلمات المستخدمة في Test Module

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PRIMARY KEY, AUTO_INCREMENT | المعرف الفريد |
| level_id | bigint | FOREIGN KEY → levels.id, NOT NULL | معرف المستوى |
| word | string(255) | NOT NULL | الكلمة (مثل "Able", "Risk") |
| description | text | NULLABLE | وصف الكلمة |
| order_in_level | integer | NOT NULL | ترتيب الكلمة في المستوى |
| created_at | timestamp | NULLABLE | تاريخ الإنشاء |
| updated_at | timestamp | NULLABLE | تاريخ التحديث |

**Indexes:**
- PRIMARY KEY (id)
- FOREIGN KEY (level_id) REFERENCES levels(id) ON DELETE CASCADE
- UNIQUE (level_id, word)
- INDEX (level_id, order_in_level)

---

### 6. **user_learn_progress** (تقدم المستخدم في Learn Module)
جدول لتتبع تقدم المستخدم في تعلم الحروف (Learn Module)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PRIMARY KEY, AUTO_INCREMENT | المعرف الفريد |
| user_id | bigint | FOREIGN KEY → users.id, NOT NULL | معرف المستخدم |
| letter_id | bigint | FOREIGN KEY → letters.id, NOT NULL | معرف الحرف |
| is_completed | boolean | DEFAULT false | هل تم إكمال التعلم |
| completed_at | timestamp | NULLABLE | تاريخ إكمال التعلم |
| last_accessed_at | timestamp | NULLABLE | آخر مرة تم الوصول للحرف |
| created_at | timestamp | NULLABLE | تاريخ الإنشاء |
| updated_at | timestamp | NULLABLE | تاريخ التحديث |

**Indexes:**
- PRIMARY KEY (id)
- FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
- FOREIGN KEY (letter_id) REFERENCES letters(id) ON DELETE CASCADE
- UNIQUE (user_id, letter_id)
- INDEX (user_id, is_completed)

---

### 7. **user_practice_progress** (تقدم المستخدم في Practice Module)
جدول لتتبع تقدم المستخدم في ممارسة الحروف (Practice Module)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PRIMARY KEY, AUTO_INCREMENT | المعرف الفريد |
| user_id | bigint | FOREIGN KEY → users.id, NOT NULL | معرف المستخدم |
| letter_id | bigint | FOREIGN KEY → letters.id, NOT NULL | معرف الحرف |
| is_completed | boolean | DEFAULT false | هل تم إكمال الممارسة بنجاح |
| accuracy | decimal(5,2) | NULLABLE | نسبة الدقة (0-100) |
| attempts_count | integer | DEFAULT 0 | عدد المحاولات |
| completed_at | timestamp | NULLABLE | تاريخ إكمال الممارسة بنجاح |
| last_practiced_at | timestamp | NULLABLE | آخر مرة تمت الممارسة |
| created_at | timestamp | NULLABLE | تاريخ الإنشاء |
| updated_at | timestamp | NULLABLE | تاريخ التحديث |

**Indexes:**
- PRIMARY KEY (id)
- FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
- FOREIGN KEY (letter_id) REFERENCES letters(id) ON DELETE CASCADE
- UNIQUE (user_id, letter_id)
- INDEX (user_id, is_completed)

---

### 8. **user_test_progress** (تقدم المستخدم في Test Module)
جدول لتتبع تقدم المستخدم في اختبار الكلمات (Test Module)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PRIMARY KEY, AUTO_INCREMENT | المعرف الفريد |
| user_id | bigint | FOREIGN KEY → users.id, NOT NULL | معرف المستخدم |
| word_id | bigint | FOREIGN KEY → words.id, NOT NULL | معرف الكلمة |
| is_completed | boolean | DEFAULT false | هل تم إكمال الاختبار بنجاح |
| accuracy | decimal(5,2) | NULLABLE | نسبة الدقة (0-100) |
| attempts_count | integer | DEFAULT 0 | عدد المحاولات |
| completed_at | timestamp | NULLABLE | تاريخ إكمال الاختبار بنجاح |
| last_attempted_at | timestamp | NULLABLE | آخر مرة تمت المحاولة |
| created_at | timestamp | NULLABLE | تاريخ الإنشاء |
| updated_at | timestamp | NULLABLE | تاريخ التحديث |

**Indexes:**
- PRIMARY KEY (id)
- FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
- FOREIGN KEY (word_id) REFERENCES words(id) ON DELETE CASCADE
- UNIQUE (user_id, word_id)
- INDEX (user_id, is_completed)

---

## العلاقات (Relationships)

### One-to-Many Relationships:
1. **users** → **otp_codes** (مستخدم واحد يمكن أن يكون له عدة رموز OTP)
2. **users** → **user_learn_progress** (مستخدم واحد يمكن أن يكون له تقدم في عدة حروف)
3. **users** → **user_practice_progress** (مستخدم واحد يمكن أن يكون له تقدم في عدة حروف)
4. **users** → **user_test_progress** (مستخدم واحد يمكن أن يكون له تقدم في عدة كلمات)
5. **levels** → **letters** (مستوى واحد يحتوي على عدة حروف)
6. **levels** → **words** (مستوى واحد يحتوي على عدة كلمات)

### Many-to-Many Relationships (through pivot tables):
1. **users** ↔ **letters** (من خلال `user_learn_progress` و `user_practice_progress`)
2. **users** ↔ **words** (من خلال `user_test_progress`)

---

## ملاحظات مهمة:

1. **Soft Deletes**: جدول `users` يستخدم Soft Deletes لحذف البيانات بشكل منطقي
2. **Cascade Deletes**: عند حذف مستخدم، يتم حذف جميع سجلات التقدم المرتبطة به تلقائياً
3. **Unique Constraints**: 
   - كل مستخدم يمكن أن يكون له سجل تقدم واحد فقط لكل حرف/كلمة
   - كل حرف فريد داخل المستوى الواحد
   - كل كلمة فريدة داخل المستوى الواحد
4. **JSON Fields**: الحقول `key_steps` و `common_mistakes` في جدول `letters` تخزن كـ JSON
5. **Progress Tracking**: يتم تتبع التقدم بشكل منفصل لكل Module (Learn, Practice, Test)

---

## API Endpoints المتوقعة:

### Authentication:
- `POST /api/register` - تسجيل مستخدم جديد
- `POST /api/login` - تسجيل الدخول
- `POST /api/forgot-password` - طلب إعادة تعيين كلمة المرور
- `POST /api/reset-password` - إعادة تعيين كلمة المرور
- `POST /api/verify-email` - التحقق من البريد الإلكتروني

### User Profile:
- `GET /api/profile` - الحصول على بيانات المستخدم
- `PUT /api/profile` - تحديث بيانات المستخدم
- `POST /api/clear-progress` - مسح تقدم المستخدم

### Progress Tracking:
- `POST /api/learn-progress` - تسجيل تقدم في Learn Module
- `POST /api/practice-progress` - تسجيل تقدم في Practice Module
- `POST /api/test-progress` - تسجيل تقدم في Test Module
- `GET /api/progress` - الحصول على تقدم المستخدم الكامل

