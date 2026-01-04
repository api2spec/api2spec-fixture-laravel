# Tea Brewing API - Laravel Fixture

A TIF-compliant (Teapot Integration Framework) REST API for managing tea brewing sessions, built with Laravel 11.

## Overview

This fixture API provides endpoints for:

- **Teapots** - CRUD operations for teapot management
- **Teas** - Catalog of teas with brewing parameters
- **Brews** - Brewing sessions tracking
- **Steeps** - Individual steep cycles within a brew

## Requirements

- PHP 8.3+
- Composer 2.x

## Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
```

## Running the Server

```bash
php artisan serve
```

The API will be available at `http://localhost:8000/api`.

## API Endpoints

### Health Checks

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/health` | Basic health check |
| GET | `/api/health/live` | Liveness probe |
| GET | `/api/health/ready` | Readiness probe |
| GET | `/api/brew` | TIF 418 signature endpoint |

### Teapots

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/teapots` | List all teapots |
| POST | `/api/teapots` | Create a teapot |
| GET | `/api/teapots/{id}` | Get teapot by ID |
| PUT | `/api/teapots/{id}` | Update teapot (full) |
| PATCH | `/api/teapots/{id}` | Update teapot (partial) |
| DELETE | `/api/teapots/{id}` | Delete teapot |
| GET | `/api/teapots/{id}/brews` | List brews for teapot |

### Teas

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/teas` | List all teas |
| POST | `/api/teas` | Create a tea |
| GET | `/api/teas/{id}` | Get tea by ID |
| PUT | `/api/teas/{id}` | Update tea (full) |
| PATCH | `/api/teas/{id}` | Update tea (partial) |
| DELETE | `/api/teas/{id}` | Delete tea |

### Brews

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/brews` | List all brews |
| POST | `/api/brews` | Create a brew |
| GET | `/api/brews/{id}` | Get brew by ID |
| PATCH | `/api/brews/{id}` | Update brew |
| DELETE | `/api/brews/{id}` | Delete brew |
| GET | `/api/brews/{id}/steeps` | List steeps for brew |
| POST | `/api/brews/{id}/steeps` | Add steep to brew |

## Example Usage

```bash
# Check health
curl http://localhost:8000/api/health

# TIF 418 response
curl http://localhost:8000/api/brew

# Create a teapot
curl -X POST http://localhost:8000/api/teapots \
  -H "Content-Type: application/json" \
  -d '{"name":"My Kyusu","material":"clay","capacityMl":350}'

# Create a tea
curl -X POST http://localhost:8000/api/teas \
  -H "Content-Type: application/json" \
  -d '{"name":"Dragon Well","type":"green","steepTempCelsius":80,"steepTimeSeconds":120}'

# List teapots
curl http://localhost:8000/api/teapots
```

## Testing

```bash
php artisan test
```

## TIF Compliance

This API is TIF-compliant. The `GET /api/brew` endpoint returns HTTP 418 (I'm a teapot) per RFC 2324.

## License

MIT License
