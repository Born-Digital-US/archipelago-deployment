## CAR Local build process

Open up a terminal and:
1.  Clone the repo:
    ```git clone git@github.com:Born-Digital-US/archipelago-deployment.git```
2.  Cd into archipelago-deployment, and run this make command:
    ```make build_dev_linux```
    _This will take 10-20 minutes the first time you install, mainly to download the containers. It performs generic archipelago site setup as well._
3.  Sync from sandbox (this will change when we have staging and, later, production environments):
    ```sh scripts/car/sandbox-config-deploy.sh```
    _This script performs the following:_
    1.  _Downloads the latest bam-backup from the sandbox, uploads it to the php container, and uses drush sql-cli to load the database._
    2.  _Uses rsync to get the public files directory from the sandbox_
    3.  _Uses rsync to copy the repository assets (may want to comment out to save time)._
    4.  _Uses config-sync:import to sync configs._

_At this point, you can log in using repository.californialightandsound.org user credentials - from 1Pass._
4.  **Manually re-index solr** by going to /admin/config/search/search-api/index/default_solr_index and click, first on "Queue all items for re-indexing", and then on "Index now"

# Update local

1.  From the archipelago-deployment root, run:
    ```make update_dev_linux```
    _Stops containers, pulls update, restarts containers, runs composer update to pull in all updated code, fixes permissions, runs db updates and clears cache._

# Developing

*   This is a fork of [https://github.com/esmero/archipelago-deployment](https://github.com/esmero/archipelago-deployment) but there is no intention to PR back upstream. To avoid confusion, we are using "CAR" as our default branch. All custom CAR development (modules, themes, config, composer.json) occurs in this repository.
*   Any work on archipelago modules needs to happen through a different setup that is created using the `make build_archipelago_contributor_linux` command.
*   Custom CAR/BD modules go into `web/modules/custom/`
*   Custom theme goes into `web/themes/custom/`
*   For now, we're putting all configs in the default config/sync/ directory (having gotten hugely messed up trying to use config-split to isolate CAR-specific configs from those that come from upstream archipelago-deployment).


**Please note:** _This build process will take at least 20 - 40 mins depending on your internet connection_
