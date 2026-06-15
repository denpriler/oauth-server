# OAuth Authorization Server

A custom OAuth 2.0 Authorization Server built with **Symfony 8.1 + PHP 8.4**.

Educational project covering OAuth 2.0, DDD, CQRS, Event-Driven Design, and Go.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Language | PHP 8.4 |
| Framework | Symfony 8.1 |
| ORM | Doctrine ORM 3.6 + PostgreSQL (Supabase) |
| Message Bus | Symfony Messenger (CQRS) |
| Auth tokens | lexik/jwt-authentication-bundle |
| Code style | laravel/pint (PSR-12) |
| Tests | PHPUnit 13 |
| Planned | Go microservice (token introspection) |

---

## Architecture

The project follows **DDD + CQRS** with distinct bounded contexts.

```
src/
├── Identity/          # Users: registration, login
├── OAuth/             # Clients, authorization codes, tokens, scopes
├── Consent/           # User consent grants
└── Shared/            # Cross-cutting infrastructure
```

Each context uses a strict layered structure:

```
{Context}/
├── Domain/
│   ├── Entity/        # Doctrine entities
│   ├── ValueObject/   # Embeddable value objects
│   ├── Repository/    # Repository interfaces
│   └── Event/         # Domain events
├── Application/
│   ├── Command/       # Commands + handlers
│   └── Query/         # Queries + handlers
└── Infrastructure/
    ├── Repository/    # Doctrine implementations
    └── Http/          # Controllers
```

**Key conventions:**
- UUID v7 primary keys
- Private constructors + static factory methods on entities
- No public setters — state changes through named methods
- Domain events collected in entity, dispatched after persistence
- Commands implement `RequestDataInterface` — resolved directly as controller arguments
- Handlers return values via Messenger `HandledStamp` when needed

---

## API Endpoints

| Method | Path | Description | Auth |
|--------|------|-------------|------|
| `POST` | `/api/identity/register` | User registration | — |
| `POST` | `/api/identity/login` | Login → JWT | — |
| `POST` | `/oauth/clients` | Register OAuth client | JWT |
| `GET` | `/oauth/authorize` | Authorization endpoint | JWT |
| `POST` | `/oauth/token` | Token endpoint | — |
| `POST` | `/oauth/token/revoke` | Revoke token | JWT |
| `GET` | `/oauth/introspect` | Token introspection | — |
| `GET` | `/.well-known/openid-configuration` | Discovery | — |

### Register

```bash
curl -X POST http://localhost:8000/api/identity/register \
  -H "Content-Type: application/json" \
  -d '{"email": "user@example.com", "password": "Str0ng!Pass#99"}'
# 201 Created
```

### Login

```bash
curl -X POST http://localhost:8000/api/identity/login \
  -H "Content-Type: application/json" \
  -d '{"email": "user@example.com", "password": "Str0ng!Pass#99"}'
# 200 OK
# {"access_token": "eyJ...", "token_type": "Bearer", "expires_in": 3600}
```

---

## Setup

### Requirements

- PHP 8.4
- Composer
- Symfony CLI
- PostgreSQL (or a [Supabase](https://supabase.com) project)

### Install

```bash
composer install
```

### Configure

```bash
cp .env .env.local
# Edit .env.local — set DATABASE_URL and JWT_PASSPHRASE
```

### Generate JWT keys

```bash
php bin/console lexik:jwt:generate-keypair
```

### Database

```bash
php bin/console doctrine:migrations:migrate
```

### Run

```bash
symfony server:start -d
```

---

## Testing

Tests use an in-memory repository double — no database or network required.

```bash
composer test
```

```
Login User Controller
  ✔ Login succeeds
  ✔ Login fails on wrong password
  ✔ Login fails on unknown email
  ✔ Login fails on blank fields

Register User Controller
  ✔ Register succeeds
  ✔ Register fails on invalid email
  ✔ Register fails on short password
  ✔ Register fails on weak password
  ✔ Register fails on duplicate email
```

---

## Code Style

```bash
composer pint
```

---

## OAuth 2.0 Roadmap

- [x] User registration + login (JWT)
- [ ] OAuth Client registration
- [ ] Authorization Code + PKCE flow
- [ ] Token endpoint
- [ ] Refresh token
- [ ] Scopes
- [ ] Consent screen
- [ ] Token introspection (Go microservice)
- [ ] OpenID Connect discovery
