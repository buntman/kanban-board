# Kanban Board

A simple Kanban board built with Laravel, Inertia.js, and React. Organize work into projects, columns, and tasks.

## Tech Stack

- Laravel 13 (PHP 8.3+)
- Inertia.js 2 + React 18
- Laravel Breeze (authentication)
- Tailwind CSS + Vite
- PostgreSQL
- PHPUnit

## Features

- Register, log in, and reset your password (Breeze)
- Create projects with a name and optional description
- Add columns (cards) to a project
- Add tasks to a column
- Mark tasks complete or incomplete

## Requirements

- PHP 8.3+
- Composer
- Node.js 20+ and npm
- PostgreSQL (or change `DB_CONNECTION` in `.env` for another database)

## Getting Started

```bash
git clone <repository-url>
cd kanban-board

composer install
cp .env.example .env
php artisan key:generate

# Update DB_* values in .env, then create the database
php artisan migrate
# Optional: creates test@example.com with password "password"
php artisan migrate --seed

npm install
npm run build
```

## Running

```bash
composer dev
```

This starts the Laravel dev server, Vite, the queue listener, and the log tail (Pail) together.

Alternatively, run them separately:

```bash
php artisan serve
npm run dev
```

## Tests

```bash
composer test
# or
php artisan test
```

## Roadmap

- Link projects to their owner and add authorization
- Edit and delete projects, columns, and tasks
- Drag and drop tasks between columns (using `position`)
- Complete/total counts per column and a real dashboard
- API endpoints for cards and tasks

## License

MIT
