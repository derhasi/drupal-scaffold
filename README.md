# drupal-scaffold

Composer plugin for automatically downloading Drupal scaffold files (like
`index.php`, `update.php`, …) when using `drupal/core` via Composer.

## Usage

`composer require drupal-composer/drupal-scaffold:dev-master` in your composer
project before installing or updating `drupal/core`.

## Configuration

You can configure the plugin with providing some settings in the `extra` section
of your root `composer.json`.

```json
{
  "extra": {
    "drupal-scaffold": {
      "source": "https://ftp.drupal.org/files/projects/drupal-{version}.tar.gz",
      "excludes": [
        "google123.html",
        "robots.txt"
      ],
      "includes": [
        "sites/default/example.settings.my.php"
      ],
      "omit-defaults": false,
      "post-install-cmd-action": [ 
        "file-missing": "index.php"
      ],
      "post-update-cmd-action": true
    }
  }
}
```
The `source` option may be used to specify the URL to download the
scaffold files from; the default source is drupal.org. The literal string
`{version}` in the `source` option is replaced with the current version of
Drupal core being updated prior to download.

With the `drupal-scaffold` option `excludes`, you can provide additional paths
that should not be copied or overwritten. Default excludes are provided by the
plugin:
```
.gitkeep
autoload.php
composer.json
composer.lock
core
drush
example.gitignore
LICENSE.txt
README.txt
vendor
themes
profiles
modules
sites/*
```

If there are some files inside of an excluded location that should be
copied over, they can be individually selected for inclusion via the
`includes` option. Default includes are provided by the plugin:
```
sites
sites/default
sites/default/default.settings.php
sites/default/default.services.yml
sites/development.services.yml
sites/example.settings.local.php
sites/example.sites.php
```

When setting `omit-defaults` to `true`, neither the default excludes nor the
default includes will be provided; in this instance, only those files explicitly
listed in the `excludes` and `includes` options will be considered. If
`omit-defaults` is `false` (the default), then any items listed in `excludes`
or `includes` will be in addition to the usual defaults.

The `post-install-cmd-action` controls whether or not drupal-scaffold will
download the scaffold files after `composer install` installs `drupal/core`.
By default, this is done only if the file `index.php` is missing on the local
filesystem. It is recommended that you commit your scaffold files to your
repository after they are downloaded the first time. To alter drupal-composer's
behavior to omit downloading the scaffold files after `composer install`
when running on the CI server, change the `post-install-cmd-action` option
as follows:
```
  "post-install-cmd-action": [ 
    "if-not-env": "CI"
  ],
```
The `post-update-cmd-action` option behaves similarly, but controls whether
the scaffold files are downloaded after `composer update` updates the version
of the `drupal/core` package. By default, drupal-composer will refresh the
scaffold files every time this happens. It is not recommended to change this
preference, as doing so might cause you to miss changes to the scaffold files
when updating.

## Limitation

When using Composer to install or update the Drupal development branch, the
scaffold files are always taken from the HEAD of the branch (or, more
specifically, from the most recent development .tar.gz archive). This might
not be what you want when using an old development version (e.g. when the
version is fixed via composer.lock). To avoid problems, always commit your
scaffold files to the repository any time that composer.lock is committed.

## Custom command

The plugin by default is only downloading the scaffold files when installing or
updating `drupal/core`. If you want to call it manually, you have to add the
command callback to the `scripts`-section of your root `composer.json`, like this:

```json
{
  "scripts": {
    "drupal-scaffold": "DrupalComposer\\DrupalScaffold\\Plugin::scaffold"
  }
}
```

After that you can manually download the scaffold files according to your
configuration by using `composer drupal-scaffold`.
