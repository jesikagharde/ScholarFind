# 🎓 Scholarship Finder & Eligibility Checker

A full-stack web application built with **PHP 8.x (PDO)**, **MySQL**, **CSS3**, and **JavaScript** to help students discover and verify eligibility for government and private scholarships across India in real time.

---

## 🌟 Key Features

1. **Smart Eligibility & Match Score Engine:**
   - Evaluates Education Level, Course/Stream, Marks Percentage, Annual Family Income, Caste Category, State, and Gender.
   - Calculates a **0% – 100% Match Score** and provides a granular checklist of passed vs failed requirements.
2. **Instant Eligibility Calculator (`eligibility_checker.php`):**
   - Interactive sliders and dropdowns allowing any student to test criteria and view matched schemes instantly via AJAX without reloading.
3. **Comprehensive Scholarship Explorer (`scholarships.php`):**
   - Multi-filter sidebar (Education Level, Category, State, Gender, Income, Minimum %, Sort by Deadline/Amount).
4. **Student Portal & Dashboard (`dashboard.php`):**
   - 🎯 **Recommended For You:** Auto-matches scholarships based on the student's saved academic profile.
   - 🔖 **Saved / Bookmarks:** One-click scholarship bookmarking.
   - 📋 **Application Tracker:** Monitor application progress (`Applied` ➔ `Under Review` ➔ `Awarded` / `Rejected`) with notes.
5. **Admin Management Portal (`admin/`):**
   - Overview metrics (Total scholarships, registered students, submitted applications, total disbursable aid).
   - Complete CRUD operations (Add, Edit, Delete scholarships).
6. **Secure Architecture:**
   - Prepared statements with PDO (SQL injection immune).
   - Secure Bcrypt password hashing.
   - Role-Based Access Control (Student vs Admin).

---

## 🚀 Quick Setup Instructions (XAMPP + VS Code)

### Step 1: Start XAMPP

1. Open the **XAMPP Control Panel**.
2. Click **Start** for **Apache** and **MySQL**.

---

### Step 2: Import the Database

1. Open your web browser and go to: **http://localhost/phpmyadmin**
2. Click on the **Import** tab at the top.
3. Click **Choose File** and select `database.sql` from this project folder.
4. Click **Import** (or **Go** at the bottom).
   *(This creates the **`scholarship_db`** database, all tables, and pre-populates sample scholarships).*

---

### Step 3: Put the Project in XAMPP

Choose **Option A** or **Option B**:

#### Option A (Recommended — Desktop Symlink):

If your folder is on your Desktop, open Windows PowerShell as Administrator and run:

```powershell
New-Item -ItemType SymbolicLink -Path "C:\xampp\htdocs\ScholarFind" -Target "$HOME\Desktop\ScholarFind"
```

#### Option B (Direct Copy):

Copy this `ScholarFind` folder directly into:

```text
C:\xampp\htdocs\ScholarFind
```

---

### Step 4: Open in Browser

Visit in your web browser:

👉 **http://localhost/ScholarFind**

---

## 🔑 Demo Login Credentials

| Role | Email | Password |
| :--- | :--- | :--- |
| **Student** | `rahul@example.com` | `admin123` |
| **Admin** | `admin@scholarship.com` | `admin123` |

*(You can also register your own new student account directly from the Sign Up page!)*

---

## 🛠️ Opening in VS Code

1. Open **Visual Studio Code**.
2. Go to **File ➔ Open Folder...** and select the `ScholarFind` folder.
3. You can edit the PHP, CSS, JavaScript, and database files directly!
