include make.env

build_update:
	docker-compose down
	git pull
	docker-compose up -d
	docker exec -t -w /var/www/html $(APACHE_CONTAINER) bash -c "COMPOSER_MEMORY_LIMIT=-1 composer install -o --prefer-dist --no-interaction"
	docker-compose exec -T -w /var/www/html php bash -c "chown -R :www-data /var/www/html/web"
	docker exec -t -w /var/www/html/web $(APACHE_CONTAINER) bash -c "drush updb -y && drush cr"

local_build_update:
	docker-compose down
	git pull
	docker-compose up -d
	sudo rm composer.lock || true
	sudo mv web/modules/contrib/webform_strawberryfield . || true
	sudo mv web/modules/contrib/strawberryfield . || true
	sudo mv web/modules/contrib/format_strawberryfield . || true
	sudo mv web/modules/contrib/ami . || true
	nmcli con down id BD || true
	docker exec -it -w /var/www/html $(APACHE_CONTAINER) bash -c "COMPOSER_MEMORY_LIMIT=-1 composer install -o --prefer-dist"
	nmcli con up id BD || true
	sudo rm -rf web/modules/contrib/webform_strawberryfield
	sudo rm -rf web/modules/contrib/strawberryfield
	sudo rm -rf web/modules/contrib/format_strawberryfield
	sudo rm -rf web/modules/contrib/ami
	sudo mv webform_strawberryfield web/modules/contrib/
	sudo mv strawberryfield web/modules/contrib/
	sudo mv format_strawberryfield web/modules/contrib/
	sudo mv ami web/modules/contrib/
	docker-compose exec -T -w /var/www/html php bash -c "chown -R 1000:www-data /var/www/html/web"
	docker exec -t -w /var/www/html/web $(APACHE_CONTAINER) bash -c "drush updb -y && drush cr"

merge_esmero:
	echo "Stop containers"
	docker-compose down
	echo "Ensure remotes exist"
	git config remote.origin.url >&- || git remote add origin $(CAR_DEPLOYMENT_REPO)
	git config remote.upstream.url >&- || git remote add upstream $(ESMERO_DEPLOYMENT_REPO)
	echo "Stash any changes, then rebase upstream deploy branch to this branch"
	git stash || true
	git fetch --all
	# Ensure that local version of esmero deployment branch exists.
	git checkout -b $(ESMERO_DEPLOYMENT_BRANCH) origin/$(ESMERO_DEPLOYMENT_BRANCH) || true
	git checkout $(CAR_DEPLOYMENT_BRANCH)
	git pull
	git checkout $(ESMERO_DEPLOYMENT_BRANCH)
	git merge -Xtheirs upstream/$(ESMERO_DEPLOYMENT_BRANCH)
	git checkout $(CAR_DEPLOYMENT_BRANCH)
	git merge -Xtheirs $(ESMERO_DEPLOYMENT_BRANCH)
	echo "Resolve composer.lock conflicts, reapply the stash to get any local composer.json changes, then delete composer.lock"
	git checkout --ours composer.lock
	git stash apply || true
# Re-create composer.lock
	rm composer.lock
	echo "Restart containers"
	docker-compose up -d
	echo "Finally, re-create composer.lock"
	docker exec -t -w /var/www/html $(APACHE_CONTAINER) bash -c "COMPOSER_MEMORY_LIMIT=-1 composer install -o --prefer-dist --no-interaction"
	docker-compose exec -T -w /var/www/html php bash -c "chown -R :www-data /var/www/html/web"
	docker exec -t -w /var/www/html/web $(APACHE_CONTAINER) bash -c "drush updb -y && drush cr"


rebase_esmero_webform_strawberryfield:
	cd web/modules/contrib/webform_strawberryfield && git stash && git checkout $(WEBFORM_SBF_BRANCH) && git rebase upstream/$(WEBFORM_SBF_BRANCH) && git stash apply

build_linux:
	cp docker-compose-linux.yml docker-compose.yml
	sed 's/version: '\''3.5'\''/version: '\''3.7'\''/' docker-compose.yml
	sed 's/image: mysql:8.0.22/image: mysql:5.7.33/' docker-compose.yml
	docker-compose up -d
	sudo chown -R 100:100 persistent/iiifcache
	sudo chown -R 8983:8983 persistent/solrcore
	docker exec -t $(APACHE_CONTAINER) bash -c "chown -R www-data:www-data private"
	docker-compose exec -T mc bash -c "mc alias set minio http://minio:9000 minio minio123"
	docker-compose exec -T mc bash -c "mc mb minio/archipelago"
	docker-compose exec -T mc bash -c "mc ls minio"
	docker exec -t -w /var/www/html $(APACHE_CONTAINER) bash -c "COMPOSER_MEMORY_LIMIT=-1 composer install -o --prefer-dist --no-interaction"
	#docker-compose exec -T -w /var/www/html php bash -c "chown -R :www-data /var/www/html/web"
	docker exec -ti $(APACHE_CONTAINER) bash -c 'scripts/archipelago/setup.sh'
	docker exec -ti $(APACHE_CONTAINER) bash -c "cd web;../vendor/bin/drush -y si --verbose config_installer config_installer_sync_configure_form.sync_directory=/var/www/html/config/sync/ --db-url=mysql://root:esmerodb@esmero-db/drupal8 --account-name=admin --account-pass=archipelago -r=/var/www/html/web --sites-subdir=default --notify=false install_configure_form.enable_update_status_module=NULL install_configure_form.enable_update_status_emails=NULL;drush cr;chown -R www-data:www-data sites;"
	docker exec -ti $(APACHE_CONTAINER) bash -c 'drush ucrt demo --password="demo"; drush urol metadata_pro "demo"'
	docker exec -ti $(APACHE_CONTAINER) bash -c 'drush ucrt jsonapi --password="jsonapi"; drush urol metadata_api "jsonapi"'
	docker exec -ti $(APACHE_CONTAINER) bash -c 'scripts/archipelago/deploy.sh'
	
# this is for Pat, in case he needs it. Can be deleted if he does not
strawberry_build_linux: build_linux
	docker exec -t -w /var/www/html $(APACHE_CONTAINER) bash -c "cd web/modules/contrib; rm -rf webform_strawberryfield"
	docker exec -t -w /var/www/html $(APACHE_CONTAINER) bash -c "cd web/modules/contrib; rm -rf strawberryfield"
	docker exec -t -w /var/www/html $(APACHE_CONTAINER) bash -c "COMPOSER_MEMORY_LIMIT=-1 composer install -o --prefer-dist --no-interaction"
	git clone --branch $(WEBFORM_SBF_BRANCH) $(WEBFORM_SBF_REPO)
	sudo mv webform_strawberryfield web/modules/contrib/
	git clone --branch $(SBF_BRANCH) $(SBF_REPO)
	sudo mv strawberryfield web/modules/contrib/

clean:
	docker-compose down -v
	rm -f docker-compose.yml
	sudo rm -rf ./vendor
	sudo rm -rf ./persistent/db
	sudo rm -rf ./persistent/iiifcache
	sudo rm -rf ./persistent/minio-client-data
	sudo rm -rf ./persistent/miniodata
	sudo rm -rf ./persistent/solrcore

down:
	docker-compose down --remove-orphans
