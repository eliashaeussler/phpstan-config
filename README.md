<div align="center">

# PHPStan config

[![Maintainability](https://api.codeclimate.com/v1/badges/c334b9eef94a1ff7bd5d/maintainability)](https://codeclimate.com/github/eliashaeussler/phpstan-config/maintainability)
[![Tests](https://github.com/eliashaeussler/phpstan-config/actions/workflows/tests.yaml/badge.svg)](https://github.com/eliashaeussler/phpstan-config/actions/workflows/tests.yaml)
[![CGL](https://github.com/eliashaeussler/phpstan-config/actions/workflows/cgl.yaml/badge.svg)](https://github.com/eliashaeussler/phpstan-config/actions/workflows/cgl.yaml)
[![Release](https://github.com/eliashaeussler/phpstan-config/actions/workflows/release.yaml/badge.svg)](https://github.com/eliashaeussler/phpstan-config/actions/workflows/release.yaml)
[![Latest Stable Version](http://poser.pugx.org/eliashaeussler/phpstan-config/v)](https://packagist.org/packages/eliashaeussler/phpstan-config)
[![License](http://poser.pugx.org/eliashaeussler/phpstan-config/license)](LICENSE)

</div>

This package contains basic [PHPStan](https://phpstan.org/) config for use in my
personal projects. It is not meant to be used anywhere else. I won't provide
support and don't accept pull requests for this repo.

## 🔥 Installation

```bash
composer require eliashaeussler/phpstan-config
```

## ⚡ Usage

### With extension installer

If you have the [`phpstan/extension-installer`](https://github.com/phpstan/extension-installer)
package installed, there's nothing more to do. The [base configuration](phpstan-base.neon.dist)
is automatically included.

### Manual include

Create a `phpstan.neon` and include the [`phpstan.neon.dist`](phpstan.neon.dist):

```neon
# phpstan.neon

includes:
  - %rootDir%/../../eliashaeussler/phpstan-config/phpstan.neon.dist
```

## ⭐ License

This project is licensed under [GNU General Public License 3.0 (or later)](LICENSE).
