# ER Diagram - Ishara Application

## Entity Relationship Diagram (Textual Representation)

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                           DATABASE SCHEMA - ISHARA                          │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────┐
│       USERS         │
├─────────────────────┤
│ PK  id              │
│     first_name      │
│     last_name       │
│     email (UNIQUE)  │
│     password        │
│     phone (UNIQUE) │
│     gender          │
│     date_of_birth   │
│     parent_name     │
│     parent_number   │
│     theme_preference│
│     is_verified     │
│     is_admin        │
│     email_verified_at│
│     otp_code        │
│     otp_expires_at  │
│     created_at      │
│     updated_at      │
│     deleted_at      │
└─────────────────────┘
         │
         │ 1
         │
         │ *
         ▼
┌─────────────────────┐
│     OTP_CODES       │
├─────────────────────┤
│ PK  id              │
│ FK  user_id         │──┐
│     email           │  │
│     otp_code        │  │
│     type            │  │
│     is_used         │  │
│     expires_at      │  │
│     created_at      │  │
│     updated_at      │  │
└─────────────────────┘  │
                         │
                         │ REFERENCES
                         │
                         │
┌─────────────────────┐  │
│       LEVELS        │  │
├─────────────────────┤  │
│ PK  id              │  │
│     level_number    │  │
│     name            │  │
│     letters_covered │  │
│     description     │  │
│     illustration_url│  │
│     order           │  │
│     created_at      │  │
│     updated_at      │  │
└─────────────────────┘  │
         │               │
         │ 1             │
         │               │
         │ *             │
         ▼               │
┌─────────────────────┐  │
│      LETTERS        │  │
├─────────────────────┤  │
│ PK  id              │  │
│ FK  level_id        │──┘
│     letter          │
│     name            │
│     video_url       │
│     animation_url   │
│     key_steps (JSON)│
│     common_mistakes │
│     order_in_level  │
│     created_at      │
│     updated_at      │
└─────────────────────┘
         │
         │ *
         │
         │ 1
         ▼
┌─────────────────────┐
│ USER_LEARN_PROGRESS │
├─────────────────────┤
│ PK  id              │
│ FK  user_id         │──┐
│ FK  letter_id       │──┼──┐
│     is_completed    │  │  │
│     completed_at    │  │  │
│     last_accessed_at│  │  │
│     created_at      │  │  │
│     updated_at      │  │  │
└─────────────────────┘  │  │
                         │  │
                         │  │
┌─────────────────────┐  │  │
│USER_PRACTICE_PROGRESS│  │  │
├─────────────────────┤  │  │
│ PK  id              │  │  │
│ FK  user_id         │──┘  │
│ FK  letter_id       │─────┘
│     is_completed    │
│     accuracy        │
│     attempts_count  │
│     completed_at    │
│     last_practiced_at│
│     created_at      │
│     updated_at      │
└─────────────────────┘

┌─────────────────────┐
│       LEVELS        │
├─────────────────────┤
│ PK  id              │
│     level_number    │
│     name            │
│     letters_covered │
│     description     │
│     illustration_url│
│     order           │
│     created_at      │
│     updated_at      │
└─────────────────────┘
         │
         │ 1
         │
         │ *
         ▼
┌─────────────────────┐
│       WORDS         │
├─────────────────────┤
│ PK  id              │
│ FK  level_id        │──┐
│     word            │  │
│     description     │  │
│     illustration_url│  │
│     order_in_level  │  │
│     created_at      │  │
│     updated_at      │  │
└─────────────────────┘  │
         │               │
         │ *             │
         │               │
         │ 1             │
         ▼               │
┌─────────────────────┐  │
│  USER_TEST_PROGRESS │  │
├─────────────────────┤  │
│ PK  id              │  │
│ FK  user_id         │──┼──┐
│ FK  word_id         │──┘  │
│     is_completed    │     │
│     accuracy        │     │
│     attempts_count  │     │
│     completed_at    │     │
│     last_attempted_at│    │
│     created_at      │     │
│     updated_at      │     │
└─────────────────────┘     │
                            │
                            │
                            │ REFERENCES
                            │
                            │
                    ┌───────┴───────┐
                    │     USERS     │
                    └───────────────┘
```

## العلاقات التفصيلية:

### 1. USERS → OTP_CODES
- **Type**: One-to-Many
- **Relationship**: مستخدم واحد يمكن أن يكون له عدة رموز OTP
- **Foreign Key**: `otp_codes.user_id` → `users.id`
- **On Delete**: CASCADE

### 2. USERS → USER_LEARN_PROGRESS
- **Type**: One-to-Many
- **Relationship**: مستخدم واحد يمكن أن يكون له تقدم في عدة حروف
- **Foreign Key**: `user_learn_progress.user_id` → `users.id`
- **On Delete**: CASCADE
- **Unique Constraint**: (user_id, letter_id)

### 3. USERS → USER_PRACTICE_PROGRESS
- **Type**: One-to-Many
- **Relationship**: مستخدم واحد يمكن أن يكون له تقدم في ممارسة عدة حروف
- **Foreign Key**: `user_practice_progress.user_id` → `users.id`
- **On Delete**: CASCADE
- **Unique Constraint**: (user_id, letter_id)

### 4. USERS → USER_TEST_PROGRESS
- **Type**: One-to-Many
- **Relationship**: مستخدم واحد يمكن أن يكون له تقدم في اختبار عدة كلمات
- **Foreign Key**: `user_test_progress.user_id` → `users.id`
- **On Delete**: CASCADE
- **Unique Constraint**: (user_id, word_id)

### 5. LEVELS → LETTERS
- **Type**: One-to-Many
- **Relationship**: مستوى واحد يحتوي على عدة حروف
- **Foreign Key**: `letters.level_id` → `levels.id`
- **On Delete**: CASCADE
- **Unique Constraint**: (level_id, letter)

### 6. LEVELS → WORDS
- **Type**: One-to-Many
- **Relationship**: مستوى واحد يحتوي على عدة كلمات
- **Foreign Key**: `words.level_id` → `levels.id`
- **On Delete**: CASCADE
- **Unique Constraint**: (level_id, word)

### 7. LETTERS → USER_LEARN_PROGRESS
- **Type**: One-to-Many
- **Relationship**: حرف واحد يمكن أن يكون له تقدم من عدة مستخدمين
- **Foreign Key**: `user_learn_progress.letter_id` → `letters.id`
- **On Delete**: CASCADE

### 8. LETTERS → USER_PRACTICE_PROGRESS
- **Type**: One-to-Many
- **Relationship**: حرف واحد يمكن أن يكون له تقدم ممارسة من عدة مستخدمين
- **Foreign Key**: `user_practice_progress.letter_id` → `letters.id`
- **On Delete**: CASCADE

### 9. WORDS → USER_TEST_PROGRESS
- **Type**: One-to-Many
- **Relationship**: كلمة واحدة يمكن أن يكون لها تقدم اختبار من عدة مستخدمين
- **Foreign Key**: `user_test_progress.word_id` → `words.id`
- **On Delete**: CASCADE

---

## Cardinality Summary:

- **USERS (1) : (N) OTP_CODES**
- **USERS (1) : (N) USER_LEARN_PROGRESS**
- **USERS (1) : (N) USER_PRACTICE_PROGRESS**
- **USERS (1) : (N) USER_TEST_PROGRESS**
- **LEVELS (1) : (N) LETTERS**
- **LEVELS (1) : (N) WORDS**
- **LETTERS (1) : (N) USER_LEARN_PROGRESS**
- **LETTERS (1) : (N) USER_PRACTICE_PROGRESS**
- **WORDS (1) : (N) USER_TEST_PROGRESS**

---

## Notes:
- جميع العلاقات تستخدم CASCADE DELETE لضمان سلامة البيانات
- Unique Constraints تمنع التكرار في سجلات التقدم
- Soft Deletes مستخدم في جدول USERS فقط

