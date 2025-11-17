# Order Management System — Laravel 10


---
**Developer**: MD RAJEB CHOWDHURY 
**Email**: rajebraju@gmail.com
**GitHub**: https://github.com/rajebraju/bt-foundation-order-api

## Overview
This is an Order Management System (E-Commerce) built with Laravel 10 and MySQL. Features:
- Product & Variant management
- Real-time inventory tracking
- Low-stock alerts (queued)
- CSV product import (job)
- Order processing workflow (Pending → Processing → Shipped → Delivered → Cancelled)
- Inventory deduction on confirmation and restore on cancellation
- PDF invoice generation (queued)
- Email notifications (queued)
- JWT authentication + refresh tokens
- Role-based access (Admin, Vendor, Customer)
- API versioning (v1)
- Testing: feature & unit tests

## Requirements
- PHP 8.2+
- Composer
- MySQL 8+
- Node/npm (optional)
- Optional: Redis (not required; app uses database queue by default)

## Quick setup
1. Clone:
   ```bash
   git clone <repo-url> order-management
   cd order-management

2. Install composer:
    composer install
3. Create .env from .env.example and update DB credentials:

    cp .env.example .env
    php artisan key:generate
    php artisan jwt:secret

4. Run migrations and seeders:
    php artisan migrate
    php artisan db:seed

5. Create queue tables & start worker:

    php artisan queue:table
    php artisan migrate
    php artisan queue:work
6. Start local server:
    php artisan serve


Authentication

    Login: POST /api/v1/auth/login (email, password) → returns access_token, refresh_token.

    Use header: Authorization: Bearer {access_token}.

    Refresh: POST /api/v1/auth/refresh with refresh_token.

Default demo users (seeded)

    Admin: admin@email.com / password

    Vendor: vendor@email.com / password

    Customer: customer@email.com / password

    Running tests:  php artisan test


Notes

    Use php artisan queue:work in a separate terminal for jobs (invoices, emails).

    Invoice PDFs are stored in storage/app/invoices.

    For production, configure mail driver and queue supervisor.

Deliverables

    Source code (exclude vendor/, .env)

    .env.example, README.md, API docs

    Tests & seeders 

    
---

# File: `openapi.yaml` (skeleton; place at project root as `openapi.yaml`)
```yaml
openapi: 3.0.0
info:
  title: Order Management API
  description: API v1 for Order Management System
  version: 1.0.0
servers:
  - url: http://localhost/api/v1
paths:
  /auth/login:
    post:
      tags: [Auth]
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              properties:
                email: { type: string }
                password: { type: string }
      responses:
        '200':
          description: tokens
  /products:
    get:
      tags: [Products]
      parameters:
        - in: query
          name: per_page
          schema: { type: integer }
      responses:
        '200': { description: Product list }
    post:
      tags: [Products]
      security: [ bearerAuth: [] ]
      requestBody:
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/Product'
      responses:
        '201': { description: created }
  /products/search:
    get:
      tags: [Products]
      parameters:
        - in: query
          name: q
          schema: { type: string }
      responses:
        '200': { description: search results }
components:
  securitySchemes:
    bearerAuth:
      type: http
      scheme: bearer
      bearerFormat: JWT
  schemas:
    Product:
      type: object
      properties:
        sku: { type: string }
        name: { type: string }
        base_price: { type: number }



## API Rate Limiting

Authenticated endpoints are limited to **60 requests per minute per IP address** using Laravel's built-in throttle middleware.

## Caching Strategy

| Use Case                 | Driver | Key                     | TTL   | Implementation                         |
|--------------------------|--------|-------------------------|-------|----------------------------------------|
| Product search results   | Redis  | `search:{q}:{page}`     | 300s  | `Cache::remember()` in `ProductRepository` |
| Low-stock threshold list | Redis  | `low_stock_alerts`      | 3600s | Cached in `CheckLowStockJob`           |
| PDF invoice (retrieval)  | File   | `invoices/{order_id}.pdf` | ∞    | Stored in `storage/app/invoices/`      |

> **No cache is used in inventory/order transactions** to ensure ACID compliance.

## Database Sharding Strategy

### Approach: Shard by `vendor_id`
- **Why?** Vendor data is naturally isolated; enables horizontal scale.
- **Shard Key**: `vendor_id` (present in `products`, `orders`, `users`).
- **Routing Logic** (to add in `app/Providers/AppServiceProvider.php`):
  ```php
  if (auth()->check() && auth()->user()->hasRole('vendor')) {
      $shardId = auth()->id() % 4; // 4 shards: 0,1,2,3
      config(['database.connections.shard' => [
          'driver' => 'mysql',
          'host' => "shard{$shardId}.db.internal",
          'database' => "order_api_shard_{$shardId}",
          ...
      ]]);
      DB::purge('shard');
  }

## Postman Collection

A ready-to-use Postman collection is included in the `postman/` directory.  
Import `postman/BT-Foundation-Order.postman_collection.json` into Postman to test all API endpoints.