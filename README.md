# MultilingualPress 3 Yoast SEO Sync
Synchronize the post metadata of the Yoast SEO plugin between translated posts.

### Documentation
[MultilingualPress-Yoast-Seo-Sync Documentation](https://multilingualpress.org/docs/multilingualpress-yoast-seo-sync/)

### MultilingualPress 2
This branch (`master` branch) contains the code for MultilingualPress 3, for MultilingualPress 2 there is [version-1-mlp2](https://github.com/inpsyde/MultilingualPress-Yoast-SEO-Sync/tree/version-1-mlp2) branch, also here is the [.zip for MultilingualPress 2](https://github.com/inpsyde/MultilingualPress-Yoast-SEO-Sync/releases/tag/v1.0.1).

### Development
```
$ composer install
$ npm install
```

## Testing

### Unit testing
```
$ vendor/bin/phpunit
```

### Acceptance testing
Add multilingualpress plugin to `bin` folder and follow instructions in `tests/codeception/README.md`

## Code Style

MultilingualPress follow [Inpsyde coding standard](https://github.com/inpsyde/php-coding-standards) which are enforced via [`php_codesniffer`](https://packagist.org/packages/squizlabs/php_codesniffer).

A `phpcs.xml.dist` is available on the repository.

The repository also contains a `PhpStorm.xml` for code styles settings to be imported in PhpStorm IDE.

The  Inpsyde coding standard repository contains information on how to setup PhpStorm to integrate with `phpcs`.

## Robo Commands

The plugin ships with a set of [Robo](https://robo.li/) commands to run different development tasks, the most relevant are:

- `$ ./vendor/bin/robo build:assets` to "build" both scripts and styles
- `$ ./vendor/bin/robo build:scripts` to "build" only scripts
- `$ ./vendor/bin/robo build:styles` to "build" only styles
- `$ ./vendor/bin/robo makepot` to create the pot file within languages directory
- `$ ./vendor/bin/robo update:potandpo` to create the pot file within languages directory and update po files. This will include all of the new strings in the .po files.
- `$ ./vendor/bin/robo tests` to run both PHPUnit tests and php_codesniffer checks
- `$ ./vendor/bin/robo build` to run all the above.
- `$ ./vendor/bin/robo build {version-number}` to run all the above and, on success, create a zip file ready to be published. E.g. `$ ./vendor/bin/robo build 1.0.0`  will lint and compile assets, run unit tests and code styles check and if all of those are successful will create a `multilingualpress-3-0-0.zip` file in the root of the plugin. This file is git-ignored, but is what will be released to users.
- `$ ./vendor/bin/robo build {version-number} --git`  will do the same  as before and will also create a Git tag with the given version number. Use carefully.
