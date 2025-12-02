# Database Documentation - Ishara Application

## 📋 نظرة عامة

هذا المجلد يحتوي على جميع ملفات قاعدة البيانات لتطبيق Ishara لتعليم لغة الإشارة.

## 📁 الملفات المتوفرة

### 1. **DATABASE_SCHEMA.md**
يحتوي على وصف تفصيلي لجميع الجداول والحقول والعلاقات في قاعدة البيانات.

### 2. **ER_DIAGRAM.md**
يحتوي على مخطط العلاقات (Entity Relationship Diagram) بصيغة نصية توضح العلاقات بين الجداول.

### 3. **Migration Files**
جميع ملفات الهجرة (Migrations) اللازمة لإنشاء قاعدة البيانات:

- `0001_01_01_000000_create_users_table.php` - جدول المستخدمين الأساسي
- `2024_01_01_000001_update_users_table_add_fields.php` - إضافة حقول جديدة لجدول المستخدمين
- `2024_01_01_000002_create_otp_codes_table.php` - جدول رموز OTP
- `2024_01_01_000003_create_levels_table.php` - جدول المستويات
- `2024_01_01_000004_create_letters_table.php` - جدول الحروف
- `2024_01_01_000005_create_words_table.php` - جدول الكلمات
- `2024_01_01_000006_create_user_learn_progress_table.php` - جدول تقدم Learn Module
- `2024_01_01_000007_create_user_practice_progress_table.php` - جدول تقدم Practice Module
- `2024_01_01_000008_create_user_test_progress_table.php` - جدول تقدم Test Module

## 🗄️ هيكل قاعدة البيانات

### الجداول الأساسية:

1. **users** - المستخدمون
2. **otp_codes** - رموز التحقق وإعادة تعيين كلمة المرور
3. **levels** - المستويات التعليمية (4 مستويات)
4. **letters** - الحروف في كل مستوى
5. **words** - الكلمات في Test Module
6. **user_learn_progress** - تقدم المستخدم في Learn Module
7. **user_practice_progress** - تقدم المستخدم في Practice Module
8. **user_test_progress** - تقدم المستخدم في Test Module

## 🚀 كيفية الاستخدام

### 1. تشغيل Migrations

```bash
php artisan migrate
```

### 2. إعادة تعيين قاعدة البيانات (للتطوير فقط)

```bash
php artisan migrate:fresh
```

### 3. إعادة تعيين مع Seeders

```bash
php artisan migrate:fresh --seed
```

## 📊 العلاقات الرئيسية

- **User** → **OTP Codes** (One-to-Many)
- **User** → **Learn Progress** (One-to-Many)
- **User** → **Practice Progress** (One-to-Many)
- **User** → **Test Progress** (One-to-Many)
- **Level** → **Letters** (One-to-Many)
- **Level** → **Words** (One-to-Many)
- **Letter** → **Learn Progress** (One-to-Many)
- **Letter** → **Practice Progress** (One-to-Many)
- **Word** → **Test Progress** (One-to-Many)

## 🔑 المفاتيح والقيود

- جميع Foreign Keys تستخدم `ON DELETE CASCADE`
- Unique Constraints على:
  - `(user_id, letter_id)` في جداول التقدم
  - `(user_id, word_id)` في جدول Test Progress
  - `(level_id, letter)` في جدول Letters
  - `(level_id, word)` في جدول Words

## 📝 ملاحظات مهمة

1. جدول `users` يستخدم Soft Deletes
2. الحقول `key_steps` و `common_mistakes` في جدول `letters` من نوع JSON
3. جميع جداول التقدم تحتوي على `is_completed` لتتبع حالة الإكمال
4. جداول Practice و Test تحتوي على `accuracy` و `attempts_count` لتتبع الأداء

## 🔄 الخطوات التالية

بعد إنشاء قاعدة البيانات، ستحتاج إلى:

1. إنشاء Models لكل جدول
2. إنشاء Seeders لملء البيانات الأولية (Levels, Letters, Words)
3. إنشاء Controllers للـ APIs
4. إنشاء Requests للتحقق من البيانات
5. إنشاء Services للـ Business Logic

---

**تم إنشاء هذا الملف بواسطة:** Database Schema Generator  
**التاريخ:** 2024

