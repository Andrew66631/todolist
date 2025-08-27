.PHONY: build up down restart logs shell-app shell-mysql init install first-setup

build:
	docker-compose build

up:
	docker-compose up -d

down:
	docker-compose down

restart: down up

logs:
	docker-compose logs -f

shell-app:
	docker-compose exec app bash

shell-mysql:
	docker-compose exec mysql mysql -ularavel -ppassword laravel

# Создание структуры
init:
	@echo "Creating project structure..."
	mkdir -p src docker/nginx/conf.d docker/php docker/mysql
	touch docker/nginx/conf.d/app.conf
	touch docker/php/php.ini
	touch docker/mysql/my.cnf
	@echo "Structure created!"

# Установка Laravel
install:
	@echo "Installing Laravel..."
	rm -rf src/ 2>/dev/null || true
	mkdir -p src
	docker run --rm -v $(pwd)/src:/app composer create-project laravel/laravel . --prefer-dist
	@echo "Laravel installed successfully!"

# Полная установка
full-install: init install
	@echo "Full installation complete!"
	@echo "Now run: make first-setup"

# Первая настройка
first-setup:
	make build
	make up
	@echo "Waiting for containers to start..."
	sleep 20
	cp src/.env.example src/.env
	docker-compose exec app composer install
	docker-compose exec app php artisan key:generate
	chmod -R 775 src/storage src/bootstrap/cache
	@echo "Setup complete! Run 'make artisan-migrate' to run migrations"

artisan-migrate:
	docker-compose exec app php artisan migrate

composer-install:
	docker-compose exec app composer install

npm-install:
	docker-compose exec app npm install

%:
	@: