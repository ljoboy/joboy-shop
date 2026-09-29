.PHONY: help setup install env fresh migrate seed build serve dev test lint format

help: ## Affiche les commandes disponibles
	@grep -E '^[a-zA-Z_-]+:.*?## ' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-12s\033[0m %s\n", $$1, $$2}'

setup: install env fresh build ## Lance le projet de zéro : deps + .env + base seedée + assets
	@echo ""
	@echo "✔ Projet prêt ! Démarre le serveur avec : make serve"

install: ## Installe les dépendances PHP et JS
	composer install
	bun install --ignore-scripts

env: ## Crée le fichier .env (si absent) et génère la clé applicative
	@if not exist .env (copy .env.example .env)
	@php artisan key:generate --ansi

fresh: ## Purge puis recrée la base avec les seeders
	@php artisan migrate:fresh --seed --ansi

migrate: ## Applique uniquement les migrations
	@php artisan migrate --ansi

seed: ## Exécute uniquement les seeders
	@php artisan db:seed --ansi

build: ## Compile les assets front (Vite)
	bun run build

serve: ## Lance le serveur de développement Laravel
	@php artisan serve

dev: ## Lance Vite en hot reload (fenêtre séparée)
	@start "Vite" /min cmd /c "bun run dev"

test: ## Exécute la suite de tests PHPUnit
	@php artisan test

lint: ## Vérifie le style de code avec Pint
	@vendor/bin/pint --test

format: ## Corrige le style de code avec Pint
	@vendor/bin/pint