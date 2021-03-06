#!/bin/bash

# run this from the root directory so that the files rsync will happen in the right place. E.g. `sh web/modules/custom/cls_migrate/scripts/migrate_cls.sh`

# rsync the files directory to /web/migrate_files
rsync -rltv --exclude=js  --exclude=ctools --exclude=css --exclude=private --exclude=backup_migrate/* --exclude=feeds/* islandora@repository.californialightandsound.org:/opt/data/apache_cls/html/sites/default/files/ web/migrate_files/
#docker exec -it esmero-php bash -c "drush config-split:import car -y"

#Reinstall cls_migrate to get the latest configs.
docker exec -it esmero-php bash -c "drush dre cls_migrate"

docker exec -it esmero-php bash -c "drush mim cls_file"
docker exec -it esmero-php bash -c "drush mim cls_user_role"
# Even though the vocabs exist. We run this to populate the migrate map with the vocabulary identifiers
# for subsequent taxonomy imports to look up.
docker exec -it esmero-php bash -c "drush mim cls_taxonomy_vocabularies"
docker exec -it esmero-php bash -c "drush mim cls_taxonomy_term_vendor_services"
docker exec -it esmero-php bash -c "drush mim cls_taxonomy_term_production_stream"
docker exec -it esmero-php bash -c "drush mim cls_taxonomy_term_lto_generations"
docker exec -it esmero-php bash -c "drush mim cls_taxonomy_term_carrier"
docker exec -it esmero-php bash -c "drush mim cls_taxonomy_term_running_speed"
docker exec -it esmero-php bash -c "drush mim cls_taxonomy_term_aspect_ratio"
docker exec -it esmero-php bash -c "drush mim cls_taxonomy_term_grant_cycle"
docker exec -it esmero-php bash -c "drush mim cls_taxonomy_term_generation"
docker exec -it esmero-php bash -c "drush mim cls_taxonomy_term_metadata_shares"

docker exec -it esmero-php bash -c "drush mim cls_node_complete_partner"
docker exec -it esmero-php bash -c "drush mim cls_user"

docker exec -it esmero-php bash -c "drush mim cls_node_complete_vendor"
docker exec -it esmero-php bash -c "drush mim cls_node_complete_hdd"
docker exec -it esmero-php bash -c "drush mim cls_node_complete_shipment"
docker exec -it esmero-php bash -c "drush mim cls_paragraph_inspection"
docker exec -it esmero-php bash -c "drush mim cls_node_complete_lto"
docker exec -it esmero-php bash -c "drush mim cls_node_complete_partner_set"
