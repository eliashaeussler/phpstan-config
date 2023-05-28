<div align="center">

# PHPStan config

[![Coverage](https://img.shields.io/codecov/c/github/eliashaeussler/phpstan-config?logo=codecov&token=jej8oVuu4h)](https://codecov.io/gh/eliashaeussler/phpstan-config)
[![Maintainability](https://img.shields.io/codeclimate/maintainability/eliashaeussler/phpstan-config?logo=codeclimate)](https://codeclimate.com/github/eliashaeussler/phpstan-config/maintainability)
[![CGL](https://img.shields.io/github/actions/workflow/status/eliashaeussler/phpstan-config/cgl.yaml?label=cgl&logo=github)](https://github.com/eliashaeussler/phpstan-config/actions/workflows/cgl.yaml)
[![Tests](https://img.shields.io/github/actions/workflow/status/eliashaeussler/phpstan-config/tests.yaml?label=tests&logo=github)](https://github.com/eliashaeussler/phpstan-config/actions/workflows/tests.yaml)
[![Supported PHP Versions](https://img.shields.io/packagist/dependency-v/eliashaeussler/phpstan-config/php?logo=php)](https://packagist.org/packages/eliashaeussler/phpstan-config)

</div>

This package contains basic [PHPStan](https://phpstan.org/) config for use in my
personal projects. It is not meant to be used anywhere else. I won't provide
support and don't accept pull requests for this repo.

## 🔥 Installation

[![Packagist](https://img.shields.io/packagist/v/eliashaeussler/phpstan-config?label=version&logo=packagist)](https://packagist.org/packages/eliashaeussler/phpstan-config)
[![Packagist Downloads](https://img.shields.io/packagist/dt/eliashaeussler/phpstan-config?color=brightgreen)](https://packagist.org/packages/eliashaeussler/phpstan-config)

```bash
composer require eliashaeussler/phpstan-config
```

## ⚡ Usage

### With extension installer

If you have the [`phpstan/extension-installer`](https://github.com/phpstan/extension-installer)
package installed, there's nothing more to do. The [base configuration](phpstan-base.neon.dist)
is automatically included.

### Manual include

Create a `phpstan.neon` file and include the
[`phpstan.neon.dist`](phpstan.neon.dist) file:

```neon
# phpstan.neon

includes:
  - %rootDir%/../../eliashaeussler/phpstan-config/phpstan.neon.dist
```

### PHP API

The package provides a PHP configuration API for PHPStan. Add this
to your `phpstan.php` file:

```php
# phpstan.php

use EliasHaeussler\PHPStanConfig;

$config = PHPStanConfig\Config\Config::create(__DIR__)->in(
    'src',
    'tests',
);

// Exclude specific paths
$config->not(
    'src/lib/*',
    'tests/test-application/vendor/*',
);

// Configure rule level
$config->level(9);
$config->maxLevel();

// Enable bleeding edge
$config->withBleedingEdge();

// Include baseline file
$config->withBaseline();

// Include additional config files
$config->with(
    'phpstan-custom-rules.neon',
    'vendor/foo/baz/optional-phpstan-rules.neon',
);

// Define bootstrap files
$config->bootstrapFiles(
    'tests/build/phpstan-bootstrap.php',
);

// Define stub files
$config->stubFiles(
    'tests/stubs/ThirdPartyClass.stub',
    'tests/stubs/AnotherStubFile.stub',
);

// Override cache path
$config->useCacheDir('var/cache/phpstan');

// Ignore errors
$config->ignoreError('Access to constant EXTENSIONS on an unknown class PHPStan\ExtensionInstaller\GeneratedConfig.');
$config->ignoreError('#^Access to constant EXTENSIONS on an unknown class .+\\.$#');

// Configure unmatched error reporting
$config->reportUnmatchedIgnoredErrors(false);

// Define error formatter
$config->formatAs(PHPStanConfig\Enums\ErrorFormat::Json);

// Include Symfony set
$symfonySet = PHPStanConfig\Set\SymfonySet::create()
    ->withConsoleApplicationLoader('tests/build/console-application.php')
    ->withContainerXmlPath('var/cache/test-container.xml')
    ->disableConstantHassers();
$config->withSets($symfonySet);

// Include TYPO3 set
$typo3Set = PHPStanConfig\Set\TYPO3Set::create()
    ->withCustomAspect('myCustomAspect', \FlowdGmbh\MyProject\Context\MyCustomAspect::class)
    ->withCustomRequestAttribute('myAttribute', \FlowdGmbh\MyProject\Http\MyAttribute::class)
    ->withCustomSiteAttribute('myArrayAttribute', 'array');
$config->withSets($typo3Set);

return $config->toArray();
```

## ⭐ License

This project is licensed under [GNU General Public License 3.0 (or later)](LICENSE).
