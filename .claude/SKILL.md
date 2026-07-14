---
name: content-hub-php-library-workflow
description: Reusable workflow for working in the acquia/content-hub-php library repo — running PHPUnit/Mockery tests, PHPCS/PHPStan checks, and contributing to the 3.x branch.
version: 1.0.0
tags: [contenthub, php, phpunit, guzzle, symfony, acquia]
---

# Content Hub PHP Library Workflow

## When to Use
- Writing or running PHPUnit tests for the PHP client library
- Running code quality checks before a PR
- Adding or modifying API client methods, DTOs, or event subscribers
- Debugging HTTP/HMAC auth issues with the Content Hub API

## Prerequisites
- PHP 7.3+
- Composer
- make

## Running Tests

    composer install

    # Via Makefile (recommended)
    make all_tests

    # Or directly
    vendor/bin/phpunit

    # Single test
    vendor/bin/phpunit test/ContentHubClientTest.php

## Code Quality

    vendor/bin/phpcs --standard=Drupal src/
    vendor/bin/phpstan analyse src/
    make help   # see all Makefile targets

## Branch Strategy
- Active branch: 3.x (NOT main)
- Feature branches: LCH-<ticket> or DIT-<ticket>
- PRs target 3.x (or specific release line e.g. 3.6.x)

## Pitfalls
- Default branch is 3.x, not main
- No Drupal dependency — standalone library; avoid Drupal-specific classes
- Autoload collision — src/ and test/ both autoload under Acquia\ContentHubClient\; don't confuse source and test files
- HMAC middleware — test with a mock client; real HMAC signing requires valid API credentials

## Verification Checklist
- [ ] make all_tests passes
- [ ] vendor/bin/phpcs --standard=Drupal src/ clean
- [ ] vendor/bin/phpstan analyse src/ passes
- [ ] PR targets 3.x
