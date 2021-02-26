Utilizing the conflig_split module, configuration for CAR is split into three config directories: sync, car and dev.

- `config/sync` contains the configuration inherited from [esmero/archipelago-deployment](https://github.com/esmero/archipelago-deployment)
- `config/car` contains the configuration split customized for California Revealed. It is a conditional split, only overriding the esmero/archipelago-deployment configuration where they are different.
- `config/dev` contains the configuration split used for local development. It enables and configures a couple modules like `devel` and `devel_php`, but that is all. Likewise, it is a conditional split, only overriding where different from `config/car` and `config/sync`.

# To use, add to the settings.php file on each deployment:

```php
// Enable the CAR config split.
// This should be enabled for all CAR deployment environments: local, sandbox, staging, and production.
$config['config_split.config_split.car']['status'] = TRUE;
// Enable the DEV configuration split for local development.
// Uncomment the following line only for development environments
//$config['config_split.config_split.dev']['status'] = TRUE;
```
(`settings.php` is located at `web/sites/default/settings.php`)
