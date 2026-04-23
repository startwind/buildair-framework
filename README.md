# buildair-framework

**buildair-framework** is one of the systems that lets you implement greenfield applications quickly — without spending the first days on infrastructure, auth boilerplate, or Docker setup. Clone it, rename things, and start building your actual product.

It is opinionated by design. The stack is fixed, the patterns are set, and the wiring is done. You bring the domain logic.

---

## Stack

| Layer | Technology |
|---|---|
| Frontend | Nuxt 4 (Vue 3, TypeScript) |
| Backend | Symfony 8, PHP 8.4 |
| Database | MySQL 8.4 |
| Auth | JWT via lexik/jwt-authentication-bundle |
| Infrastructure | Podman / Docker Compose |

---

## What it already does

### Authentication
- User registration with email + password
- Login returning a signed JWT (RS256, 1h TTL)
- Protected routes via JWT on every `GET/POST /api/*` endpoint
- `/api/auth/me` returns the current user's profile

### Double-Opt-In (optional)
- Flip `DOUBLE_OPT_IN=true` in `.env` to require email confirmation after registration
- Verification link is sent via Symfony Mailer — plug in any SMTP provider
- Unverified users are blocked at login via a `UserChecker`

### User types
- **Normal vs. Admin** — users carry `ROLE_USER` by default; `ROLE_ADMIN` can be granted and revoked
- **Free vs. Paying** — a dedicated `isPaying` flag is independent of roles

Both attributes are controlled via Symfony console commands (see below), not through the API — intentionally, so your product code decides when and why status changes.

### Database
- Doctrine ORM with attribute-based mapping
- Migrations are run automatically on container startup — no manual step needed
- MySQL 8.4 LTS

### Infrastructure
- Single `podman compose up -d --build` brings up everything
- JWT keys are auto-generated on first start
- Apache passes `Authorization` headers correctly out of the box
- Named volumes keep vendor/ and node_modules/ inside containers — host directory stays clean

---

## How to use it (without forking)

This repository is a **GitHub Template**. Use it to create your own repo with a clean git history:

```bash
gh repo create my-company/my-app \
  --template startwind/buildair-framework \
  --private \
  --clone
```

Or via the GitHub UI: click **"Use this template"** → **"Create a new repository"**.

You get a fresh repository with all files but none of this repo's commit history.

---

## Getting started

```bash
# 1. Copy the environment file and adjust values
cp .env.example .env

# 2. Start everything
podman compose up -d --build

# 3. Open the app
open http://localhost:4000
```

Services after startup:

| Service | URL |
|---|---|
| Frontend | http://localhost:4000 |
| Backend API | http://localhost:8000 |
| MySQL | localhost:3309 |

---

## Console commands

```bash
# Grant / revoke admin role
podman compose exec backend php bin/console app:user:role user@example.com
podman compose exec backend php bin/console app:user:role user@example.com --revoke

# Grant / revoke paying status
podman compose exec backend php bin/console app:user:paying user@example.com
podman compose exec backend php bin/console app:user:paying user@example.com --revoke
```

---

## Environment variables

| Variable | Default | Description |
|---|---|---|
| `DOUBLE_OPT_IN` | `false` | Require email verification after registration |
| `MAILER_DSN` | `null://null` | Mailer transport — set to SMTP for real emails |
| `APP_URL` | `http://localhost:4000` | Frontend base URL (used in verification emails) |
| `JWT_PASSPHRASE` | *(set this)* | Passphrase for the RSA key pair |
| `APP_SECRET` | *(set this)* | Symfony app secret |
| `DB_PASSWORD` | `app_password` | MySQL user password |

---

## API reference

| Method | Endpoint | Auth | Description |
|---|---|---|---|
| `POST` | `/api/auth/register` | — | Register a new user |
| `POST` | `/api/auth/login` | — | Login, returns `{ token }` |
| `GET` | `/api/auth/verify/{token}` | — | Confirm email address |
| `GET` | `/api/auth/me` | JWT | Current user profile |

All protected endpoints expect `Authorization: Bearer <token>`.

---

## Project structure

```
├── backend/          Symfony 8 application
│   ├── src/
│   │   ├── Command/       app:user:role, app:user:paying
│   │   ├── Controller/    AuthController
│   │   ├── Entity/        User
│   │   ├── Security/      UserChecker
│   │   └── Service/       MailService
│   ├── migrations/
│   └── config/
├── frontend/         Nuxt 4 application
│   └── app/
│       ├── pages/         login, register, index, verify/[token]
│       ├── composables/   useAuth
│       └── middleware/    auth, guest
├── docker-compose.yml
└── .env.example
```

---

## What to build next

This framework deliberately stops at auth and user management. From here, typical next steps are:

- Add your domain entities and API endpoints in `backend/src/`
- Add pages and composables in `frontend/app/`
- Wire up a real mailer DSN for production (`MAILER_DSN=smtp://...`)
- Tighten CORS in `backend/config/packages/nelmio_cors.yaml` before going live
