#!/bin/bash

### drop db tables if this is being repeated. Not needed otherwise.
## drop all db tables prior to import of db dump from sandbox
#  docker exec -it esmero-php bash -c "drush sqlq 'show tables'"
#  # then, for each table, you can...
#  docker exec -it esmero-php bash -c "drush sqlq 'drop table [xxx]'"

# import saved db from sandbox (sample, you have to get the copy yourself and adjust the file name here)
  scp ubuntu@sandbox.californiarevealed.org:$(ssh ubuntu@sandbox.californiarevealed.org 'ls -t /home/ubuntu/archipelago-deployment/private/backup_migrate/*.gz | head -1') /tmp/
  gunzip /tmp/*.gz
  mv /tmp/*.mysql /tmp/car2.mysql
  docker cp /tmp/car2.mysql esmero-php:/tmp/car2.mysql
  docker exec -it esmero-php bash -c "drush sql-cli < /tmp/car2.mysql"

# rsync public files
  rsync -rlv --exclude=private --exclude=profiler ubuntu@sandbox.californiarevealed.org:/home/ubuntu/archipelago-deployment/web/sites/default/files/ web/sites/default/files/
  docker exec -it esmero-php bash -c "chown -R www-data:www-data /var/www/html/web/sites/default/files"

# rsync repository assets
  rsync -rlv ubuntu@sandbox.californiarevealed.org:/home/ubuntu/archipelago-deployment/persistent/miniodata/ persistent/miniodata/

# re-index solr - the following doesn't seem to work,
  # docker exec -it esmero-php bash -c "drush search-api-solr:finalize-index --force default_solr_index"
  # since that doesn't work just  so just go to
    #! /admin/config/search/search-api/index/default_solr_index. There, "queue all items for reindexing", and then "index now"

########################## the following are if sandbox has not been deployed to with all the new stuff yet ################
## import configs twice
  docker exec -it esmero-php bash -c "drush cim -y"
  docker exec -it esmero-php bash -c "drush cr"
  docker exec -it esmero-php bash -c "drush cim -y"
  docker exec -it esmero-php bash -c "drush cr"
#
## reinstall cls_migrate to get its migration configs loaded
#  docker exec -it esmero-php bash -c "drush dre cls_migrate"
#
## run the non-ado migrations (requires that a copy of the cls database be created on the esmero-db container
## and that a configuration be defined for it in settings.php with the name "cls"
##  sh web/modules/custom/cls_migrate/scripts/migrate_cls.sh
