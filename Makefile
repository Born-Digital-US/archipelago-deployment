include make.env

build_update:
	docker-compose down
	git pull
	docker-compose up -d
	docker exec -t -w /var/www/html $(APACHE_CONTAINER) bash -c "COMPOSER_MEMORY_LIMIT=-1 composer install -o --prefer-dist --no-interaction"
	docker-compose exec -T -w /var/www/html php bash -c "chown -R :www-data /var/www/html/web"
	docker exec -t -w /var/www/html/web $(APACHE_CONTAINER) bash -c "drush updb -y && drush cr"
