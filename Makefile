.PHONY: up down logs restart shell-backend shell-frontend migrate

up:
	docker compose up -d --build

down:
	docker compose down

logs:
	docker compose logs -f

restart:
	docker compose restart

shell-backend:
	docker compose exec backend bash

shell-frontend:
	docker compose exec frontend sh

migrate:
	docker compose exec backend php bin/console doctrine:migrations:migrate --no-interaction

migration:
	docker compose exec backend php bin/console doctrine:migrations:diff
