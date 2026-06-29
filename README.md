<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Job Automation & Application Scheduler

This application includes an automated background job finder and auto-apply system that scans Naukri.com, matches positions against your AI-parsed resume profile, and automatically submits applications.

### ⚙️ Scheduler Configuration

You can fully customize the scheduler behavior in the **Configuration Settings** page of the web panel:
- **Search Frequency**: Choose between **Hourly Run** or **Daily Run**.
- **Daily Run Time**: Specify the exact time of day (e.g., `07:00` for 7 AM) when the daily run should execute. (Disabled when Hourly Run is selected).
- **Scheduler Timezone**: Select your preferred timezone (e.g., `Asia/Kolkata` or `UTC`) to align the scheduled execution with your local time.

---

### 🚀 Running the Scheduler

To ensure that the automated job search and application runner executes at the configured time, you need to run the Laravel schedule runner.

#### 1. Local Development (Windows & macOS/Linux)
During local development, you can run a daemon process in your terminal that runs the schedule every minute:
```bash
php artisan schedule:work
```
Keep this process running, and it will trigger the job finder/auto-applier exactly at your configured time (e.g., `07:00` daily).

#### 2. Manual Immediate Trigger
If you want to run the job finder and auto-apply routine immediately without waiting for the scheduled time, trigger the artisan command directly:
```bash
php artisan jobs:automation-run
```

#### 3. Production Deployment (Linux Server Cron)
In a production environment, set up a standard cron entry on your server:
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

---

## Technical Stack & Verification

- **Framework**: Laravel 11 (PHP 8.2+)
- **Testing**: Run automated tests using `php artisan test` to verify setting validation and persistence rules.
- **Logs**: Automation logs are stored in `storage/logs/laravel.log` and can be monitored for discovery and application details.

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
