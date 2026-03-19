# Admin Dashboard

User management dashboard for Laravel authentication systems.

## Features

- User CRUD operations
- User statistics and analytics
- Activity logs
- Role-based access control
- Search and filtering
- Bulk actions
- CSV export
- Vue.js frontend with Tailwind CSS

## Requirements

- PHP 8.1+
- Composer
- Node.js 18+
- MySQL, PostgreSQL, or SQLite

## Installation

```bash
cd admin-dashboard
composer install
npm install

cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed

php artisan serve &
npm run dev
```

Default admin credentials are created by the seeder. Check `database/seeders/AdminSeeder.php`.

## Routes

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /admin/users | List users |
| GET | /admin/users/{id} | User details |
| PUT | /admin/users/{id} | Update user |
| DELETE | /admin/users/{id} | Delete user |
| GET | /admin/stats | Dashboard statistics |
| GET | /admin/activity | Activity logs |
| GET | /admin/export | Export users to CSV |

## License

MIT
php artisan serve
npm run dev
```

5. Login with default admin credentials:
   - Email: `admin@example.com`
   - Password: `password`

## API Endpoints

### Authentication

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/auth/login` | Admin login |
| POST | `/api/auth/logout` | Logout |
| GET | `/api/auth/user` | Get current admin |

### Users Management

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/admin/users` | List users (paginated) |
| GET | `/api/admin/users/{id}` | Get user details |
| POST | `/api/admin/users` | Create user |
| PUT | `/api/admin/users/{id}` | Update user |
| DELETE | `/api/admin/users/{id}` | Delete user |
| POST | `/api/admin/users/bulk-delete` | Bulk delete |
| GET | `/api/admin/users/export` | Export to CSV |

### Statistics

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/admin/stats` | Dashboard statistics |
| GET | `/api/admin/stats/registrations` | Registration trends |
| GET | `/api/admin/stats/activity` | User activity |

### Activity Logs

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/admin/logs` | List activity logs |
| GET | `/api/admin/logs/{userId}` | User's activity log |

## Architecture

```
admin-dashboard/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── UserController.php
│   │   │   └── StatsController.php
│   │   └── Middleware/
│   │       └── AdminMiddleware.php
│   ├── Models/
│   │   ├── User.php
│   │   └── ActivityLog.php
│   └── Services/
│       └── StatsService.php
├── database/
│   ├── migrations/
│   └── seeders/
│       └── AdminSeeder.php
└── resources/
    └── js/
        ├── components/
        │   ├── Sidebar.vue
        │   ├── DataTable.vue
        │   └── StatsCard.vue
        └── pages/
            ├── Dashboard.vue
            ├── Users.vue
            ├── UserEdit.vue
            └── ActivityLogs.vue
```

## Extending

The admin dashboard can be integrated with any auth kit:

1. Copy the admin routes to your auth kit's `routes/api.php`
2. Copy the admin controllers and middleware
3. Add the `is_admin` column to your users table
4. Import the Vue components

## Security

- Admin-only middleware protection
- Activity logging for all admin actions
- Rate limiting on all endpoints
- Input validation and sanitization
- CSRF protection

## License

MIT License
