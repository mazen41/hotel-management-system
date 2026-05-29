# Hotel Management SaaS — Master Setup Guide

## Project Overview

```
hotel-system/
├── backend/     ← Laravel 12 REST API (Port 8000)
└── frontend/    ← Next.js 15 Dashboard (Port 3000)
```

---

## 1. Backend Setup (Laravel 12 + Sanctum)

```bash
cd D:\hotel-system\backend

# Step 1: Install PHP dependencies (this installs Sanctum too)
composer install

# Step 2: Generate application key
php artisan key:generate

# Step 3: Run all migrations (users, cache, jobs, sanctum tokens)
php artisan migrate

# Step 4: Start the API server
php artisan serve
```

✅ API available at: http://localhost:8000

---

## 2. Frontend Setup (Next.js 15)

```bash
cd D:\hotel-system\frontend

# Step 1: Install Node dependencies
npm install

# Step 2: Start dev server
npm run dev
```

✅ Dashboard available at: http://localhost:3000

---

## 3. Test the Full Flow

1. Open http://localhost:3000/register
2. Create an account
3. You'll be redirected to the dashboard
4. Try collapsing the sidebar
5. Click Logout → redirected to login
6. Login with your credentials → back to dashboard

---

## API Endpoints Reference

| Method | Endpoint                        | Auth | Description              |
|--------|---------------------------------|------|--------------------------|
| POST   | /api/auth/register              | No   | Register new user        |
| POST   | /api/auth/login                 | No   | Login + get token        |
| POST   | /api/auth/logout                | Yes  | Logout (revoke token)    |
| GET    | /api/auth/me                    | Yes  | Get current user         |
| GET    | /api/dashboard/kpis             | Yes  | Dashboard KPI cards      |
| GET    | /api/dashboard/recent-activity  | Yes  | Recent activity feed     |
| GET    | /api/dashboard/occupancy-trend  | Yes  | 7-day occupancy trend    |

---

## Architecture Notes

### Backend Structure
```
app/
├── Http/
│   ├── Controllers/Api/
│   │   ├── Auth/AuthController.php
│   │   └── Dashboard/DashboardController.php
│   └── Requests/Auth/
│       ├── LoginRequest.php
│       └── RegisterRequest.php
├── Models/User.php          ← HasApiTokens trait added
config/
├── cors.php                 ← Allows localhost:3000
└── sanctum.php              ← Stateful domains configured
routes/
└── api.php                  ← All API routes
```

### Frontend Structure
```
app/
├── login/page.tsx           ← Auth page
├── register/page.tsx        ← Auth page
└── dashboard/
    ├── layout.tsx           ← Protected layout guard
    └── page.tsx             ← Dashboard with KPIs
components/
├── layout/Sidebar.tsx       ← Collapsible sidebar
├── layout/Topbar.tsx        ← Top navigation
└── dashboard/
    ├── KPICard.tsx
    ├── ActivityFeed.tsx
    └── OccupancyChart.tsx
contexts/AuthContext.tsx     ← Token-based auth state
lib/api.ts                   ← HTTP client
```

### Authentication Flow
```
[Register/Login] → POST /api/auth/* → JWT Token
     ↓
localStorage.setItem('auth_token', token)
     ↓
Every API Request → Authorization: Bearer {token}
     ↓
On App Load → GET /api/auth/me → Validate token
     ↓
Invalid/Missing → Redirect to /login
```

---

## Switching to MySQL (Optional)

In `backend/.env`, replace the SQLite config with:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hotel_saas
DB_USERNAME=root
DB_PASSWORD=your_password
```

Then re-run: `php artisan migrate:fresh`

---

## Future Modules (Planned)

This foundation is designed to scale into:

- **Rooms** — Room types, availability, pricing
- **Reservations** — Booking lifecycle management
- **Channel Manager** — Booking.com, Expedia, Airbnb sync
- **Guests** — Guest profiles & history
- **Reports** — Revenue, occupancy analytics
- **Settings** — Hotel profile, policies, users
