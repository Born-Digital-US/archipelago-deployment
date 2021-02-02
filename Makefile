include make.env

build_update:
	docker-compose down
	git pull
	docker-compose up -d
	mv web/modules/contrib/webform_strawberryfield webform_strawberryfield
	docker exec -t -w /var/www/html $(APACHE_CONTAINER) bash -c "COMPOSER_MEMORY_LIMIT=-1 composer install -o --prefer-dist --no-interaction"
	rm -rf web/modules/contrib/webform_strawberryfield
	docker-compose exec -T -w /var/www/html php bash -c "chown -R :www-data /var/www/html/web"
	mv webform_strawberryfield web/modules/contrib/webform_strawberryfield
	docker exec -t -w /var/www/html/web $(APACHE_CONTAINER) bash -c "drush updb -y && drush cr"

merge_esmero:
	echo "Stop containers"
	docker-compose down
	echo "Ensure remotes exist"
	git config remote.origin.url >&- || git remote add origin $(CAR_DEPLOYMENT_REPO)
	git config remote.upstream.url >&- || git remote add upstream $(ESMERO_DEPLOYMENT_REPO)
	echo "Stash any changes, then rebase upstream deploy branch to this branch"
	git stash
	git fetch --all
	git checkout $(CAR_DEPLOYMENT_BRANCH)
	git pull
	git checkout $(ESMERO_DEPLOYMENT_BRANCH)
	git rebase upstream $(ESMERO_DEPLOYMENT_BRANCH)
	git checkout $(CAR_DEPLOYMENT_BRANCH)
	git rebase $(ESMERO_DEPLOYMENT_BRANCH)
	echo "Resolve composer.lock conflicts, reapply the stash to get any local composer.json changes, then delete composer.lock"
	git checkout --ours composer.lock
	git stash apply
  # Re-create composer.lock
	rm composer.lock
	echo "Restart containers"
	docker-compose up -d
	echo "Finally, re-create composer.lock"
	docker exec -t -w /var/www/html $(APACHE_CONTAINER) bash -c "COMPOSER_MEMORY_LIMIT=-1 composer install -o --prefer-dist --no-interaction"
	docker-compose exec -T -w /var/www/html php bash -c "chown -R :www-data /var/www/html/web"
	docker exec -t -w /var/www/html/web $(APACHE_CONTAINER) bash -c "drush updb -y && drush cr"


