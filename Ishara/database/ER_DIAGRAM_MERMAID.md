# ER Diagram - Ishara Application (Mermaid Format)

يمكنك استخدام هذا الملف لعرض ER Diagram في أي محرر يدعم Mermaid (مثل GitHub, GitLab, VS Code مع Mermaid extension).

```mermaid
erDiagram
    USERS ||--o{ OTP_CODES : "has"
    USERS ||--o{ USER_LEARN_PROGRESS : "has"
    USERS ||--o{ USER_PRACTICE_PROGRESS : "has"
    USERS ||--o{ USER_TEST_PROGRESS : "has"
    
    LEVELS ||--o{ LETTERS : "contains"
    LEVELS ||--o{ WORDS : "contains"
    
    LETTERS ||--o{ USER_LEARN_PROGRESS : "tracked_in"
    LETTERS ||--o{ USER_PRACTICE_PROGRESS : "practiced_in"
    
    WORDS ||--o{ USER_TEST_PROGRESS : "tested_in"

    USERS {
        bigint id PK
        string first_name
        string last_name
        string email UK
        string password
        string phone UK
        enum gender
        date date_of_birth
        boolean is_verified
        boolean is_admin
        timestamp email_verified_at
        string otp_code
        timestamp otp_expires_at
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }

    OTP_CODES {
        bigint id PK
        bigint user_id FK
        string email
        string otp_code
        enum type
        boolean is_used
        timestamp expires_at
        timestamp created_at
        timestamp updated_at
    }

    LEVELS {
        bigint id PK
        integer level_number UK
        string name
        string letters_covered
        text description
        integer order
        timestamp created_at
        timestamp updated_at
    }

    LETTERS {
        bigint id PK
        bigint level_id FK
        string letter
        string name
        json key_steps
        json common_mistakes
        integer order_in_level
        timestamp created_at
        timestamp updated_at
    }

    WORDS {
        bigint id PK
        bigint level_id FK
        string word
        text description
        integer order_in_level
        timestamp created_at
        timestamp updated_at
    }

    USER_LEARN_PROGRESS {
        bigint id PK
        bigint user_id FK
        bigint letter_id FK
        boolean is_completed
        timestamp completed_at
        timestamp last_accessed_at
        timestamp created_at
        timestamp updated_at
    }

    USER_PRACTICE_PROGRESS {
        bigint id PK
        bigint user_id FK
        bigint letter_id FK
        boolean is_completed
        decimal accuracy
        integer attempts_count
        timestamp completed_at
        timestamp last_practiced_at
        timestamp created_at
        timestamp updated_at
    }

    USER_TEST_PROGRESS {
        bigint id PK
        bigint user_id FK
        bigint word_id FK
        boolean is_completed
        decimal accuracy
        integer attempts_count
        timestamp completed_at
        timestamp last_attempted_at
        timestamp created_at
        timestamp updated_at
    }
```

## كيفية العرض

### في VS Code:
1. تثبيت extension: "Markdown Preview Mermaid Support"
2. فتح الملف
3. عرض Preview

### في GitHub/GitLab:
- الملف سيُعرض تلقائياً عند رفعه

### Online:
- استخدم [Mermaid Live Editor](https://mermaid.live/)

---

## Legend

- **PK** = Primary Key
- **FK** = Foreign Key
- **UK** = Unique Key
- **||--o{** = One-to-Many relationship

