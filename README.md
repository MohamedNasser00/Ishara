# 🤟 Ishara – AI Sign Language Learning App Backend API

Ishara Backend API is the engine powering the Ishara mobile application. It manages user authentication, records learning progress, logs practice sessions, tracks test evaluations, and provides structured educational resources (levels, lessons, and test words).

---

## 📖 Overview
The backend application provides a secure RESTful API that handles:
* 🔐 **User Authentication & Email OTP verification**
* 📈 **Real-Time Progress Synchronization** (Lessons, Practice sessions, and Tests)
* 👤 **Account & Profile Management**
* 📊 **Structured Curriculum Data** (Levels, Alphabet Lessons, Practice Exercises, and Test Words)

---

## ✨ Key Features
* 🔐 **Token-Based Authentication:** Secured using Laravel Sanctum for mobile clients.
* ✉️ **OTP Verification:** Verification code generation and email handling for account activation and password recovery.
* 📈 **Progress Tracker:** Database models tracking completed lessons, practice runs, and tests per user.
* 🔄 **Progress Reset:** Ability for users to wipe all of their progress (Learn, Practice, and Test) and start fresh.
* 📂 **Seeded Curriculum Dataset:** Automated seeders that populate learning levels (A-Z alphabets) and corresponding test words.
* 🛠 **Standardized Response API:** Unified JSON responses (`success`, `message`, `data`, and `errors`) handled through a responses helper.

---

## 🛠 Tech Stack
* **Backend Framework:** Laravel 12.x
* **Language:** PHP 8.2+
* **Package Manager:** Composer
* **Database:** SQLite (default / zero-config), MySQL / PostgreSQL compatible
* **Authentication:** Laravel Sanctum
* **Mail / OTP:** Local Log Mailer / SMTP
* **Testing:** PHPUnit / Laravel Test suite

---

## 🧩 Architecture
The backend is structured using a clean Service-Layer pattern to isolate business logic from HTTP controllers.

```text
app/
 ├── Http/
 │   ├── Controllers/
 │   │   └── Website/
 │   │       ├── Account/       # Profile management & progress deletion
 │   │       ├── Auth/          # Login, Register, OTP verify, Forgot password
 │   │       └── Learn/         # Levels, Lessons, Practice & Test completion
 │   └── Requests/
 │       └── Website/Auth/      # Input validation logic
 │
 ├── Models/                    # Eloquent ORM Models
 │   ├── User.php
 │   ├── Level.php
 │   ├── Lesson.php
 │   ├── UserLesson.php
 │   ├── UserPracticeProgress.php
 │   ├── TestWord.php
 │   └── UserTestProgress.php
 │
 ├── Services/                  # Core Business Logic Layer
 │   ├── Website/
 │   │   ├── Account/
 │   │   ├── Auth/
 │   │   └── Learn/
 │   └── HandleResponse.php     # Standardized JSON response helper
 │
 └── routes/
     └── api.php                # API route definitions
```

---

## ⚡ API Endpoints
All API responses follow a uniform structure:
* **Success (200/201):** `{"success": true, "message": "...", "data": {...}}`
* **Failure (422/400/401):** `{"success": false, "message": "...", "errors": [...]}`

### Auth Endpoints (Public)
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `POST` | `/api/register` | Register a new user account (generates & mails activation OTP) |
| `POST` | `/api/verify` | Verify email OTP code to activate the account |
| `POST` | `/api/resend-otp` | Resend verification OTP code to the registered email |
| `POST` | `/api/login` | Log in and receive a Sanctum Bearer token |
| `POST` | `/api/forgot-password/send` | Request a password reset OTP code |
| `POST` | `/api/forgot-password/verify` | Verify password reset OTP code |
| `POST` | `/api/forgot-password/reset` | Reset account password using the verified reset OTP |

### Account Endpoints (Protected - Bearer Token Required)
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/api/profile` | Retrieve user profile details and current progress summaries |
| `POST` | `/api/profile/update-name` | Update user first name and last name |
| `DELETE` | `/api/profile/clear-progress` | Reset and clear all learning, practice, and test progress data |
| `POST` | `/api/logout` | Revoke active Sanctum token and log out |

### Learn & Practice Modules (Protected - Bearer Token Required)
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/api/learn/levels` | Fetch all learning levels, lessons, and completion status |
| `POST` | `/api/learn/lessons/{id}/complete` | Mark a specific alphabet lesson as completed |
| `GET` | `/api/practice/levels` | Fetch all practice levels and complete status |
| `POST` | `/api/practice/lessons/{id}/complete` | Mark a specific practice lesson as completed |

### Test Module (Protected - Bearer Token Required)
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/api/test/levels` | Fetch test levels containing words for evaluation |
| `POST` | `/api/test/words/{id}/complete` | Mark a specific word sign-test as completed |

---

## ⚙️ Installation & Running Locally

### 1. Clone & Navigate
Clone the repository and go to the backend project root:
```bash
cd ishara/Ishara
```

### 2. Automatic Application Setup
Execute the composer setup command which installs packages, configures files, generates keys, and executes database migrations:
```bash
composer run setup
```

### 3. Seed Database Content
Populate the lessons database (letters A-Z) and testing words:
```bash
php artisan db:seed --class=LearnSeeder
php artisan db:seed --class=TestSeeder
```

### 4. Run Development Server
Run the unified developer environment containing servers, background queues, logs, and Vite asset builders:
```bash
composer run dev
```
*API is accessible locally at `http://127.0.0.1:8000`*

### 5. Running Tests
Verify application integrity using PHPUnit:
```bash
composer run test
```

---

## 🚀 Future Improvements
* 🏆 **Gamified Progress API:** Endpoint routes for logging user XP points, levels, and daily streaks.
* 🤖 **AI Integration Endpoint:** Offload custom gesture frame processing to a dedicated Flask/FastAPI worker.
* 📊 **Advanced Learning Reports:** Custom reports endpoint summarizing performance and completion stats over time.
* 🖥️ **Admin Control Panel:** Panel view for administrators to manage lessons, words, and student records.
