include make.env

build_update:
	docker-compose down
	git pull
	docker-compose up -d
	mv web/modules/contrib/webform_strawberryfield webform_strawberryfield
	mv web/modules/contrib/strawberryfield strawberryfield	
	docker exec -t -w /var/www/html $(APACHE_CONTAINER) bash -c "COMPOSER_MEMORY_LIMIT=-1 composer install -o --prefer-dist --no-interaction"
	rm -rf web/modules/contrib/webform_strawberryfield
	rm -rf web/modules/contrib/strawberryfield
	docker-compose exec -T -w /var/www/html php bash -c "chown -R :www-data /var/www/html/web"
	mv webform_strawberryfield web/modules/contrib/webform_strawberryfield
	mv strawberryfield web/modules/contrib/strawberryfield
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
	git checkout $(CAR_DEPLOYMENT_BRANCH)
	git pull
	git checkout $(ESMERO_DEPLOYMENT_BRANCH)
	git rebase upstream $(ESMERO_DEPLOYMENT_BRANCH)
	git checkout $(CAR_DEPLOYMENT_BRANCH)
	git rebase $(ESMERO_DEPLOYMENT_BRANCH)
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
	sed "s/version: '3.5'/version: '3.7'" docker-compose.yml || true
	sed "s/image: mysql:8.0.22/image: mysql:5.7.33" docker-compose.yml || true
	docker-compose up -d
	sudo chown -R 100:100 persistent/iiifcache
	sudo chown -R 8983:8983 persistent/solrcore
	docker exec -t $(APACHE_CONTAINER) bash -c "chown -R www-data:www-data private"
	docker exec -t esmero-minio mkdir data/archipelago || true
	docker exec -t esmero-minio chown -R 82:82 data/archipelago
	docker exec -t -w /var/www/html $(APACHE_CONTAINER) bash -c "COMPOSER_MEMORY_LIMIT=-1 composer install -o --prefer-dist --no-interaction"
	docker exec -t -w /var/www/html $(APACHE_CONTAINER) bash -c "cd web/modules/contrib; rm -rf webform_strawberryfield"
	docker-compose exec -T -w /var/www/html php bash -c "chown -R :www-data /var/www/html/web"
	git clone --branch $(WEBFORM_SBF_BRANCH) $(WEBFORM_SBF_REPO)
	sudo mv webform_strawberryfield web/modules/contrib/
	docker-compose exec -T -w /var/www/html php bash -c "chown -R :www-data /var/www/html/web"
	docker exec -t -w /var/www/html $(APACHE_CONTAINER) bash -c "cd web/modules/contrib; rm -rf strawberryfield"
	docker-compose exec -T -w /var/www/html php bash -c "chown -R :www-data /var/www/html/web"
	git clone --branch $(SBF_BRANCH) $(SBF_REPO)
	sudo mv strawberryfield web/modules/contrib/
	docker-compose exec -T -w /var/www/html php bash -c "chown -R :www-data /var/www/html/web"
	docker exec -ti $(APACHE_CONTAINER) bash -c 'scripts/archipelago/setup.sh'
	docker exec -ti $(APACHE_CONTAINER) bash -c "cd web;../vendor/bin/drush -y si --verbose config_installer	config_installer_sync_configure_form.sync_directory=/var/www/html/config/sync/ --db-url=mysql://root:esmerodb@esmero-db/drupal8 --account-name=admin --account-pass=archipelago -r=/var/www/html/web --sites-subdir=default --notify=false install_configure_form.enable_update_status_module=NULL install_configure_form.enable_update_status_emails=NULL;drush cr;chown -R www-data:www-data sites;"
	docker exec -ti $(APACHE_CONTAINER) bash -c 'drush ucrt demo --password="demo"; drush urol metadata_pro "demo"'
	docker exec -ti $(APACHE_CONTAINER) bash -c 'drush ucrt jsonapi --password="jsonapi"; drush urol metadata_api "jsonapi"'
	docker exec -ti $(APACHE_CONTAINER) bash -c 'scripts/archipelago/deploy.sh'
