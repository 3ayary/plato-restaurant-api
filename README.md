# Plato Restaurant API 🍽️

A RESTful API built with **Laravel** for managing a restaurant system — covering authentication, menu categories, products, and orders. Built as a backend portfolio project with a focus on clean architecture, role-based access control, and full test coverage across all modules.

---

## Tech Stack

- **PHP / Laravel**
- **MySQL**
- **Laravel Sanctum** (token-based auth)
- **Eloquent ORM**
- **Pest** (automated feature testing)

---

## Features

- **Authentication** — Register, login, and logout using Laravel Sanctum with full Pest test coverage
- **Categories** — Full CRUD for menu categories with automated tests
- **Products** — Full CRUD for restaurant products linked to categories, with automated tests
- **Orders** — Full CRUD for customer orders with automated tests
- **Admin Control** — Middleware-protected routes for admin-only operations
- **Relational Data Modeling** — Well-structured Eloquent relationships across all models
- **Automated Testing** — Pest feature tests covering auth, categories, products, and orders

---

## API Endpoints

### Auth

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/api/register` | Register a new user | ❌ |
| POST | `/api/login` | Login and get token | ❌ |
| POST | `/api/logout` | Logout and revoke token | ✅ |

### Categories

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/categories` | Get all categories | ❌ |
| POST | `/api/categories` | Create a category | ✅ Admin |
| GET | `/api/categories/{id}` | Get a single category | ❌ |
| PUT | `/api/categories/{id}` | Update a category | ✅ Admin |
| DELETE | `/api/categories/{id}` | Delete a category | ✅ Admin |

### Products

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/products` | Get all products | ❌ |
| POST | `/api/products` | Create a product | ✅ Admin |
| GET | `/api/products/{id}` | Get a single product | ❌ |
| PUT | `/api/products/{id}` | Update a product | ✅ Admin |
| DELETE | `/api/products/{id}` | Delete a product | ✅ Admin |

### Orders

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/orders` | Get all orders (admin only) | ✅ Admin |
| POST | `/api/orders` | Place a new order | ✅ |
| GET | `/api/orders/{id}` | Get a single order | ✅ |
| PUT | `/api/orders/{id}` | Update an order | ✅ Admin |
| DELETE | `/api/orders/{id}` | Delete an order | ✅ Admin |

---

## Database Structure

```
users
├── id
├── name
├── email
├── password
├── is_admin
└── timestamps

categories
├── id
├── name
└── timestamps

products
├── id
├── category_id (FK → categories)
├── name
├── description
├── price
└── timestamps

orders
├── id
├── user_id (FK → users)
└── timestamps

order_product (pivot)
├── order_id (FK → orders)
├── product_id (FK → products)
└── quantity
```

---

## Testing

This project uses **Pest** for automated feature testing across all modules.

```bash
php artisan test
```

Currently covered:
- User registration, login, and logout
- Categories CRUD
- Products CRUD
- Orders CRUD

---

## Getting Started

```bash
# Clone the repo
git clone https://github.com/3ayary/plato-restaurant-api
cd plato-restaurant-api

# Install dependencies
composer install

# Setup environment
cp .env.example .env
php artisan key:generate

# Configure your DB in .env then run:
php artisan migrate --seed

# Start the server
php artisan serve
```

---

## What I Learned Building This

This project pushed me to think about how a real-world restaurant backend is structured end-to-end — from modeling the relationships between users, products, categories, and orders, to protecting routes with role-based middleware, to making sure every module is backed by automated tests. Writing Pest tests for each resource reinforced the habit of building with confidence rather than just hoping things work.
