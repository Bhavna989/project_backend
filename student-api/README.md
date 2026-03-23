# Laravel 11 — Student Management REST API

A REST API built with Laravel 11 that demonstrates CRUD operations on a Student resource with hashed password storage.

---

## Requirements

| Tool | Version |
|------|---------|
| PHP | 8.2.12 |
| Composer | 2.x |
| Laravel | 11.x |
| XAMPP | Any (Apache + MySQL on Windows) |
| VS Code | Any (code editor) |
| Postman | Any (for API testing) |

---

## Installation

### Step 1 — Start XAMPP

Open the XAMPP Control Panel and start both **Apache** and **MySQL**. Both should show a green status. Keep XAMPP running the entire time you work on the project.

---

### Step 2 — Create the Database in phpMyAdmin

Open your browser and go to:

```
http://localhost/phpmyadmin
```

In the left panel click **New**, type the database name and click **Create**:

```
student_db
```

---

### Step 3 — Install Laravel via Composer

Open Command Prompt and navigate to the XAMPP htdocs folder:

```bash
cd C:\xampp\htdocs
```

Create the Laravel project:

```bash
composer create-project laravel/laravel student-api
cd student-api
```

---

### Step 4 — Open in VS Code

```bash
code .
```

> If the above command does not work, open VS Code manually via **File > Open Folder** and select `C:\xampp\htdocs\student-api`

---

### Step 5 — Add PHP to Windows PATH (one-time fix)

In the VS Code terminal (`Ctrl + '`) run:

```powershell
[System.Environment]::SetEnvironmentVariable("Path", $env:Path + ";C:\xampp\php", "Machine")
```

Close VS Code completely and reopen it. Verify PHP works:

```bash
php --version
```

---

### Step 6 — Configure Database in .env

Open the `.env` file in VS Code (not `.env.example`) and update these lines:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=student_db
DB_USERNAME=root
DB_PASSWORD=
```

Save with **Ctrl + S**. XAMPP MySQL default username is `root` with no password.

---

### Step 7 — Generate Application Key

```bash
php artisan key:generate
```

This fills in the `APP_KEY` in your `.env` file. Laravel will not start without it.

---

### Step 8 — Create the Students Table (Migration)

Generate the migration file:

```bash
php artisan make:migration create_students_table
```

Open the file in `database/migrations/` and replace its content:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('course');
            $table->integer('age');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
```

Run the migration:

```bash
php artisan migrate
```

Verify in phpMyAdmin — the `students` table will now appear inside `student_db`.

---

### Step 9 — Add Password Column

A separate migration was created to add the hashed password column:

```bash
php artisan make:migration add_password_to_students_table
```

Open the migration file and replace its content:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('password');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('password');
        });
    }
};
```

```bash
php artisan migrate
```

---

### Step 10 — Install API Routes (Laravel 11)

Laravel 11 does not include `routes/api.php` by default. Run:

```bash
php artisan install:api
```

This creates `routes/api.php` and wires it up automatically.

---

### Step 11 — Create Model and Controller

```bash
php artisan make:model Student
php artisan make:controller StudentController --api
```

---

### Step 12 — Start the Development Server

```bash
php artisan serve
```

Your API is now live at: `http://127.0.0.1:8000`

---

## Project Structure

```
student-api/
├── app/
│   ├── Http/Controllers/
│   │   └── StudentController.php   ← All CRUD logic
│   └── Models/
│       └── Student.php             ← Eloquent model
├── database/
│   └── migrations/                 ← Table definitions
├── routes/
│   └── api.php                     ← API route definitions
└── .env                            ← Database credentials and app key
```

---

## Configure Files

### app/Models/Student.php

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'name',
        'email',
        'course',
        'age',
        'password',
    ];

    // Hides password from all API responses
    protected $hidden = [
        'password',
    ];
}
```

### app/Http/Controllers/StudentController.php

```php
<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StudentController extends Controller
{
    // READ ALL → GET /api/students
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'All students retrieved',
            'data'    => Student::all(),
        ], 200);
    }

    // CREATE → POST /api/students
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:students,email',
            'course'   => 'required|string|max:255',
            'age'      => 'required|integer|min:1|max:100',
            'password' => 'required|string|min:6',
        ]);

        $validated['password'] = bcrypt($validated['password']);

        $student = Student::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Student created successfully',
            'data'    => $student,
        ], 201);
    }

    // READ ONE → GET /api/students/{id}
    public function show(Student $student): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $student,
        ], 200);
    }

    // UPDATE → PUT /api/students/{id}
    public function update(Request $request, Student $student): JsonResponse
    {
        $validated = $request->validate([
            'name'     => 'sometimes|string|max:255',
            'email'    => 'sometimes|email|unique:students,email,' . $student->id,
            'course'   => 'sometimes|string|max:255',
            'age'      => 'sometimes|integer|min:1|max:100',
            'password' => 'sometimes|string|min:6',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        }

        $student->update($validated);
        $student->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Student updated successfully',
            'data'    => $student,
        ], 200);
    }

    // DELETE → DELETE /api/students/{id}
    public function destroy(Student $student): JsonResponse
    {
        $student->delete();

        return response()->json([
            'success' => true,
            'message' => 'Student deleted successfully',
        ], 200);
    }
}
```

### routes/api.php

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;

Route::apiResource('students', StudentController::class);
```

---

## API Endpoints

| Method | Endpoint | Action |
|--------|----------|--------|
| GET | /api/students | List all students |
| POST | /api/students | Create a new student |
| GET | /api/students/{id} | Get one student by ID |
| PUT | /api/students/{id} | Update a student |
| DELETE | /api/students/{id} | Delete a student |

---

## Password Hashing

Passwords are hashed using **bcrypt** via Laravel's built-in `bcrypt()` helper function. Plain text passwords are never stored in the database.

The `password` column is excluded from all API responses using Laravel's `$hidden` property on the Student model, so it is never exposed through the API.

When updating a student, the password is only re-hashed if a new password value is provided in the request. If no password is sent, the existing hashed password remains unchanged.

---

## Testing with Postman

Add these headers to **every request** in Postman:

| Key | Value |
|-----|-------|
| Accept | application/json |
| Content-Type | application/json |

---

### Create a Student — POST

```
POST  http://127.0.0.1:8000/api/students
```

Request Body (raw JSON):

```json
{
    "name": "Alice Johnson",
    "email": "alice@example.com",
    "course": "Computer Science",
    "age": 21,
    "password": "secret123"
}
```

Expected response status: **201 Created**

---

### Get All Students — GET

```
GET  http://127.0.0.1:8000/api/students
```

No body required. Returns all students. Password is hidden from response.

---

### Get One Student — GET

```
GET  http://127.0.0.1:8000/api/students/1
```

Replace `1` with a valid student ID. No body required.

---

### Update a Student — PUT

```
PUT  http://127.0.0.1:8000/api/students/1
```

Only send the fields you want to change:

```json
{
    "course": "Data Science",
    "age": 22
}
```

---

### Delete a Student — DELETE

```
DELETE  http://127.0.0.1:8000/api/students/1
```

No body required. The student is permanently removed from the database.

---

## Common Errors and Fixes

| Error | Cause | Fix |
|-------|-------|-----|
| 500 — MissingAppKeyException | APP_KEY is blank in .env | Run: `php artisan key:generate` |
| 500 — Server Error | MySQL not running or wrong .env credentials | Start MySQL in XAMPP. Check DB_DATABASE=student_db and DB_PASSWORD is empty |
| 422 — Validation Error | Missing or invalid field in request body | Check all required fields are present. age must be a number not a string |
| 404 — Not Found | Student ID in URL does not exist | Do GET /api/students first to find valid IDs |
| HTML returned instead of JSON | Accept header missing in Postman | Add header: Accept = application/json |
| php not recognized in terminal | PHP not in Windows PATH | Run the SetEnvironmentVariable command and restart VS Code |
| api.php not found in routes/ | Laravel 11 does not include it by default | Run: `php artisan install:api` |
| RouteServiceProvider not found | Removed in Laravel 11 | Not needed — routing is handled automatically |
