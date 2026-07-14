# CLAUDE.md — acquia/content-hub-php (PHP Client Library)

## Project Overview
A **PHP client library** for consuming the Acquia Content Hub API. Used by acquia/acquia_contenthub Drupal module and other PHP consumers of the Content Hub service.

## Tech Stack
- **Language:** PHP 7.3+
- **Type:** PHP library (PSR-4, MIT license)
- **HTTP:** Guzzle >= 6.5
- **Framework deps:** Symfony (event-dispatcher, http-foundation, serializer) >= 4.4
- **Auth:** Acquia HTTP HMAC (acquia/http-hmac-php >= 3.4)
- **Testing:** PHPUnit 9, Mockery 1.2+, Prophecy (phpspec/prophecy-phpunit 2)
- **Code quality:** PHP_CodeSniffer 3.5+, PHPStan 1.8+, Drupal Coder

## Active Development Branch
3.x is the current default/active branch.

## Common Commands

### Install dependencies
    composer install

### Run tests
    make all_tests
    # or:
    vendor/bin/phpunit

### Code standards check
    vendor/bin/phpcs --standard=Drupal src/

### Static analysis
    vendor/bin/phpstan analyse src/

## Repository Structure
- src/          — Library source (Acquia\ContentHubClient\ namespace)
- test/         — PHPUnit tests (same namespace root for autoload)
- composer.json — Library dependencies
- Makefile      — Dev task runner

## Conventions
- PSR-4 autoloading under Acquia\ContentHubClient\
- All public API changes must include PHPUnit tests
- Use Mockery or Prophecy for mocking
- Run PHPCS before submitting PRs
- PRs target the active release branch (3.x)

## CI/CD
- GitHub Actions on PRs
- All checks must pass before merging

## Notes
- Default branch: 3.x (NOT main)
- Standalone library — does NOT require Drupal
```

---
