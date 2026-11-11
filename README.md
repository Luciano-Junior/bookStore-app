# Book Store API

RESTful API for managing a book catalog, built with Laravel, PHP 8 and MySQL/SQLite.

## Overview

The Book Store API provides authentication and CRUD operations for managing books through JSON endpoints.

## Technology Stack

- PHP 8+
- Laravel 7+
- MySQL or SQLite
- Laravel Sanctum for API authentication
- PHPUnit for automated tests

## Features

- User authentication with email and password.
- Token-based authentication using Laravel Sanctum.
- Login and logout endpoints.
- Book registration, listing, retrieval, updating and deletion.
- Request validation.
- Unique ISBN validation.
- Automated feature tests.

## Installation

### 1. Clone the repository

```bash
git clone <repository-url>
cd bookstore-app
```

### 2. Install dependencies

```bash
composer install
```

### 3. Configure environment

Copy the example environment file:

```bash
cp .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

Configure your database connection in `.env`.

Example using MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bookstore
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Run migrations and seeders

```bash
php artisan migrate --seed
```

### 5. Start the application

```bash
php artisan serve
```

The API will be available at:

```text
http://127.0.0.1:8000/api
```

## Demo Credentials

The database seeder creates a demonstration user:

| Field | Value |
|---|---|
| Email | admin@bookstore.dev |
| Password | BookStore@2026 |

These credentials are intended for local demonstration and testing only.

## Authentication

The API uses Laravel Sanctum Personal Access Tokens.

### Login

**POST** `/api/login`

Request:

```json
{
    "email": "admin@bookstore.dev",
    "password": "BookStore@2026"
}
```

The response includes an authentication token.

Use the returned token in subsequent requests:

```http
Authorization: Bearer YOUR_ACCESS_TOKEN
Accept: application/json
```

### Logout

**POST** `/api/logout`

Requires authentication. Revokes the current access token.

### Current User

**GET** `/api/user`

Requires authentication. Returns the authenticated user's information.

## Book Endpoints

All book endpoints require authentication.

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/books` | List books |
| POST | `/api/books` | Create a book |
| GET | `/api/books/{id}` | Retrieve a book |
| PUT | `/api/books/{id}` | Update a book |
| DELETE | `/api/books/{id}` | Delete a book |

### Create a Book

**POST** `/api/books`

Request:

```json
{
    "name": "Clean Code",
    "isbn": "9780132350884",
    "value": 45.90
}
```

The ISBN field is optional. If provided, it must contain numeric characters only and must be unique across the book catalog.

A book can also be created without an ISBN:

```json
{
    "name": "Domain-Driven Design",
    "value": 59.90
}
```

### Update a Book

**PUT** `/api/books/{id}`

Request:

```json
{
    "name": "Clean Code - Updated Edition",
    "isbn": "9780132350884",
    "value": 49.90
}
```

The ISBN uniqueness validation ignores the current book, allowing it to retain its existing ISBN while preventing duplication with another book.

### Delete a Book

**DELETE** `/api/books/{id}`

Returns HTTP 204 when the book is successfully deleted.

## Data Model

### Book

| Field | Type | Rules |
|---|---|---|
| id | Integer | Primary key |
| name | String | Required, maximum 255 characters |
| isbn | String | Optional, numeric characters only, unique |
| value | Decimal(10,2) | Optional, numeric, non-negative |
| created_at | Timestamp | Automatically managed |
| updated_at | Timestamp | Automatically managed |

### ISBN Business Rules

- ISBN is optional.
- When provided, ISBN must contain numbers only.
- Each registered ISBN must be unique.
- Duplicate ISBN values are rejected.
- Multiple books may have a NULL ISBN.
- During updates, a book may retain its own ISBN, but cannot use another book's ISBN.

## Validation Rules

| Field | Validation |
|---|---|
| name | Required, string, maximum 255 characters |
| isbn | Nullable, string, numeric characters only, unique |
| value | Nullable, numeric, minimum 0 |

Validation errors return HTTP 422 with a JSON response.

## HTTP Status Codes

| Status | Description |
|---|---|
| 200 | Successful request |
| 201 | Resource created |
| 204 | Resource deleted |
| 401 | Unauthenticated or invalid credentials |
| 404 | Resource not found |
| 422 | Validation error |

## Running Tests

Run the complete test suite:

```bash
php artisan test
```

Run authentication tests:

```bash
php artisan test tests/Feature/AuthTest.php
```

Run book tests:

```bash
php artisan test tests/Feature/BookTest.php
```

The test environment uses SQLite in-memory when configured in `phpunit.xml`, keeping automated tests isolated from the development database.

## Design Decisions

- **Laravel Sanctum:** provides lightweight token-based API authentication.
- **Form Requests:** centralize request validation and keep controllers focused on business operations.
- **API Resources:** standardize JSON responses.
- **Eloquent ORM:** handles database operations and model persistence.
- **Database constraints:** enforce ISBN uniqueness at the database level, in addition to application validation.
- **Feature Tests:** verify authentication, authorization, CRUD operations and validation behavior.

## License

This project was developed as a technical assessment.

## Developer

Developed by [Luciano Junior](https://www.linkedin.com/in/luciano-marques-jr/)