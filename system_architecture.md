# System Architecture & Codebase Documentation

## 1. Project Overview
**Framework:** Laravel 11.9
**Architecture:** Modular (using `nwidart/laravel-modules`) combined with standard Monolithic Laravel structure.
**Domain:** Human Resource Management (HRM) System.
**Key Technologies:**
-   **Backend:** PHP 8.2+, Laravel 11.
-   **Frontend:** Blade Templates, Vite, Tailwind CSS.
-   **Database:** MySQL (inferred from `doctrine/dbal` and standard Laravel usage).
-   **Modules:** `nwidart/laravel-modules`.
-   **Permissions:** `spatie/laravel-permission`.
-   **Real-time:** Pusher (inferred from routes).
-   **Payment Gateways:** MercadoPago, Skrill (via packages).

## 2. Directory Structure
-   **`Modules/`**: Contains modularized features.
    -   **Current Modules:**
        -   `LandingPage`: Manages the public-facing landing page.
-   **`app/`**: Core application logic.
    -   **`Models/`**: Contains 96+ Eloquent models. Business logic is often embedded here (e.g., `Employee::get_net_salary`).
    -   **`Http/Controllers/`**: 105+ controllers, mostly resource-based.
-   **`resources/views/`**: Blade templates organized by feature (e.g., `employee`, `payslip`, `leave`).
-   **`routes/`**:
    -   `web.php`: Primary route file, extensive and grouped by middleware.
    -   `api.php`: API endpoints.
-   **`database/migrations/`**: Comprehensive schema definitions.

## 3. Core Modules & Features

### 3.1 Human Resource Management (HRM)
-   **Employee Management:**
    -   **Model:** `Employee`
    -   **Data:** Personal info, Bank details, Documents, Salary type.
    -   **Logic:** `get_net_salary` calculates net pay including allowances, commissions, loans, saturation deductions, other payments, and overtime.
-   **Department & Designation:** Managed via `Department` and `Designation` models/controllers.
-   **Branch Management:** `Branch` model.

### 3.2 Payroll System
-   **Components:**
    -   **Allowances:** Fixed or Percentage based.
    -   **Commissions:** Performance-based pay.
    -   **Loans:** Employee loan tracking.
    -   **Saturation Deductions:** Standard deductions.
    -   **Overtime:** Rate * Hours * Days.
    -   **Payslip:** Generation and PDF export (`PaySlipController`).
-   **Calculation Logic:**
    -   Net Salary = (Basic Salary + Allowances + Commissions + Other Payments + Overtime) - (Loans + Saturation Deductions).
    -   Logic resides in `Employee::get_net_salary()`.

### 3.3 Attendance & Leave
-   **Attendance:**
    -   **Biometric:** Support for biometric ID (`biometric_emp_id`).
    -   **Manual:** Bulk attendance and daily attendance logging.
    -   **Controller:** `AttendanceEmployeeController`.
-   **Leave:**
    -   **Types:** Managed via `LeaveType`.
    -   **Workflow:** Application -> Approval/Rejection (`LeaveController`).

### 3.4 Performance Management
-   **Appraisals:** Employee performance reviews.
-   **Goals:** Goal tracking and types.
-   **Indicators:** Key Performance Indicators (KPIs).

### 3.5 Recruitment
-   **Jobs:** Job postings and requirements.
-   **Applications:** `JobApplication` tracking.
-   **Interviews:** Scheduling and management.
-   **Onboarding:** Job on-boarding process.

### 3.6 Events & Meetings
-   **Events:** Company events, calendar integration.
-   **Meetings:** Zoom meeting integration (`ZoomMeetingController`) and internal meetings.

### 3.7 Support
-   **Tickets:** Internal support ticket system with replies and attachments.

## 4. User Roles & Flows

### 4.1 Roles (Based on Seeders & Logic)
1.  **Super Admin:** Manages the entire system, plans, and global settings.
2.  **Company / HR:** Main administrator for a company instance. Manages employees, payroll, and settings.
3.  **Employee:** End-user. Views payslips, marks attendance, requests leave.

### 4.2 Key Workflows

#### **A. Employee Onboarding (Admin Flow)**
1.  **Create Employee:** Admin navigates to Employee module -> Create.
2.  **Details:** Enters personal info, company details (Department, Designation, Branch), and bank details.
3.  **User Account:** System automatically creates a `User` account linked to the `Employee` record (via `user_id`).
4.  **Email:** Welcome email sent with login credentials (handled by `User::defaultEmail` logic).

#### **B. Payroll Generation (Admin Flow)**
1.  **Setup:** Admin defines Allowances, Commissions, and Deductions for employees.
2.  **Set Salary:** Admin sets the basic salary in `SetSalaryController`.
3.  **Generate:** Admin goes to Payslip module -> Generate for a specific month.
4.  **Calculation:** System calls `Employee::get_net_salary()` to compute totals.
5.  **Distribution:** Payslips are generated as PDFs and can be emailed to employees.

#### **C. Leave Request (Employee Flow)**
1.  **Request:** Employee logs in -> Leaves -> Create Leave Request.
2.  **Notification:** Admin/HR receives notification/email.
3.  **Action:** Admin approves or rejects the request.
4.  **Update:** Leave balance is updated (logic in `LeaveController`).

#### **D. Attendance (Employee Flow)**
1.  **Clock In:** Employee logs in -> Dashboard -> Clicks "Clock In".
2.  **Tracking:** System records time and IP.
3.  **Clock Out:** Employee clicks "Clock Out" at end of day.
4.  **Reporting:** Admin views attendance reports.

## 5. Development Patterns & Examples

### 5.1 Standard Controller Pattern
Controllers typically follow the Resource pattern with permission checks.
**Example: `DepartmentController`**
```php
class DepartmentController extends Controller
{
    public function index()
    {
        if(\Auth::user()->can('Manage Department')) // Permission Check
        {
            $departments = Department::where('created_by', '=', \Auth::user()->creatorId())->with('branch')->get();
            return view('department.index', compact('departments'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function store(Request $request)
    {
        if(\Auth::user()->can('Create Department'))
        {
            // Validation
            $validator = \Validator::make($request->all(), [
                'branch_id' => 'required',
                'name' => 'required|max:20',
            ]);

            if($validator->fails()) {
                return redirect()->back()->with('error', $validator->getMessageBag()->first());
            }

            // Logic
            $department             = new Department();
            $department->branch_id  = $request->branch_id;
            $department->name       = $request->name;
            $department->created_by = \Auth::user()->creatorId();
            $department->save();

            return redirect()->route('department.index')->with('success', __('Department successfully created.'));
        }
    }
}
```

### 5.2 Standard Model Pattern
Models use `$fillable` for mass assignment and define relationships.
**Example: `Department`**
```php
class Department extends Model
{
    protected $fillable = [
        'name',
        'created_by',
    ];

    // Relationship
    public function branch(){
        return $this->hasOne('App\Models\Branch','id','branch_id');
    }
}
```

### 5.3 Module Structure (`nwidart/laravel-modules`)
New modules should be created in `Modules/`.
**Structure Example (`LandingPage`):**
```
Modules/
  LandingPage/
    Config/
    Console/
    Database/
      Migrations/
      Seeders/
    Entities/ (Models)
    Http/
      Controllers/
      Middleware/
      Requests/
    Providers/
    Resources/
      views/
    Routes/
      web.php
      api.php
    module.json
```
**`module.json` Example:**
```json
{
    "name": "LandingPage",
    "alias": "landingpage",
    "description": "",
    "keywords": [],
    "priority": 0,
    "providers": [
        "Modules\\LandingPage\\Providers\\LandingPageServiceProvider",
        "Modules\\LandingPage\\Providers\\AddMenuProvider"
    ],
    "files": []
}
```

## 6. How to Add a New Feature
1.  **Database:** Create a migration in `database/migrations/` (or `Modules/YourModule/Database/Migrations`).
2.  **Model:** Create a model in `app/Models/` (or `Modules/YourModule/Entities`). Define `$fillable` and relationships.
3.  **Controller:** Create a controller in `app/Http/Controllers/`. Implement `index`, `create`, `store`, `edit`, `update`, `destroy`. Add permission checks.
4.  **Routes:** Register routes in `routes/web.php` (wrapped in `auth` middleware) or `Modules/YourModule/Routes/web.php`.
5.  **Views:** Create Blade files in `resources/views/your_feature/` (index, create, edit).
6.  **Permissions:** Add new permissions via `spatie/laravel-permission` (usually in a seeder).

## 7. Database Schema Highlights
-   **`employees`**: Central table linking to User, Department, Designation, Branch.
-   **`pay_slips`**: Stores generated salary slips.
-   **`attendances`**: Tracks daily attendance status.
-   **`leaves`**: Stores leave requests and status.
-   **`jobs` & `job_applications`**: Recruitment flow.

## 8. Routing & Middleware
-   **Middleware:**
    -   `auth`: Requires login.
    -   `verified`: Requires email verification.
    -   `XSS`: Protection against XSS attacks.
-   **Groups:**
    -   Most HRM routes are wrapped in `auth` and `XSS` middleware.
    -   `Route::resource` is heavily used for standard CRUD operations.

## 9. Frontend Architecture
-   **Views:** Located in `resources/views`.
    -   `layouts/`: Master layouts (sidebar, header).
    -   `employee/`, `payslip/`, etc.: Feature-specific views.
-   **Assets:** Managed via Vite (`vite.config.js`) and Tailwind CSS (`tailwind.config.js`).
