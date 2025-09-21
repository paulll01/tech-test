# CAVU Tech Test – Parking API

Laravel + Docker demo project with availability, pricing, and bookings (create/amend/cancel).  
Includes Swagger docs, PHPStan, Pint, and tests.

## 1) Get the code (from git bundle)

```bash
git clone cavu-tech-test.bundle cavu-tech-test
cd cavu-tech-test
git branch -a
```

## 2) Prerequisites

Docker + Docker Compose installed

Ports 8080 (web) and 3306 (MySQL) available

```bash

cp .env.example .env

# Build & start containers
docker compose build
docker compose up -d

# Install composer deps inside the app container (volume mount overwrites vendor)
docker compose exec app composer install

# Generate app key
docker compose exec app php artisan key:generate --force

# Run migrations
docker compose exec app php artisan migrate

# Run seeders
docker compose exec app php artisan db:seed

# Generate Swagger docs
docker compose exec app php artisan l5-swagger:generate
```

# Open the app: http://localhost:8080

# Swagger UI: http://localhost:8080/api/documentation

## Env config

```bash
# DB via docker compose (service name: db)
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=tech_test
DB_USERNAME=user
DB_PASSWORD=root
```

## Useful commands inside app container

```bash

# Run tests
 php artisan test

# Static analysis
vendor/bin/phpstan analyse --memory-limit=1G

# Coding standards
vendor/bin/pint --test

# Regenerate Swagger
php artisan l5-swagger:generate

# Stop / Start
docker compose down
docker compose up -d
```

## API overview

### Availability

#### GET /{carPark}/availability?from=YYYY-MM-DD&to=YYYY-MM-DD

### Bookings

#### POST /{carPark}/bookings (create)

#### PATCH /bookings/{booking} (amend)

#### POST /bookings/{booking}/cancel (cancel)

## 8) CI

## The project is set up to run:

#### Pint (coding standards): vendor/bin/pint --test

#### PHPStan (static analysis): vendor/bin/phpstan analyse --memory-limit=1G

#### Tests: php artisan test

#### You can run the same commands locally using the snippets above.

## Implementation limitations

#### A few notes on the current implementation:

-   Seasons: the system assumes that only one season can be active at a time. Handling overlaps or more complex rules between seasons is not implemented.

-   Users & security: bookings are implemented for guests only, without user accounts, authentication, or permissions.

-   Rate limiting & hardening: there is no rate limiting, throttling, or additional security layers in place.

#### These areas could be extended in the future, along with improvements to the overall architecture (e.g. stricter separation of domains, clearer layering, and more robust validation and error handling) to make the system production-ready.
