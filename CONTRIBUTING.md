# Contributing to Keron Digital Auth Redis Storage Bundle

First off, thank you for considering contributing! We welcome any help, whether it's reporting a bug, proposing a feature, improving documentation, or writing code.

## How Can I Contribute?

### Reporting Bugs

* **Ensure the bug was not already reported** by searching on GitHub under [Issues](https://github.com/keron-digital/auth-redis-storage-bundle/issues). * If you're unable to find an open issue addressing the problem, [open a new one](https://github.com/keron-digital/auth-redis-storage-bundle/issues/new). Be sure to include a **title and clear description**, as much relevant information as possible, and a **code sample or an executable test case** demonstrating the expected behavior that is not occurring.

### Suggesting Enhancements

* Open a new issue describing the enhancement you have in mind. Explain clearly why this enhancement would be useful and provide examples if possible.

### Pull Requests

We welcome pull requests for bug fixes and features.

1.  **Fork the repo** and create your branch from `main` (or the relevant development branch).
2.  **Add tests!** Your patch won't be accepted if it doesn't have tests covering the changes.
3.  **Ensure the test suite and all quality checks pass.** Run all checks as described in [docs/testing.md](docs/TESTING.md).
4.  **Follow the coding standards.** The project uses PHP CS Fixer with PSR-12 and custom rules (see `.php-cs-fixer.dist.php`). Run the fixer before committing.
5.  **Update documentation** if you change behavior or add features.
6.  **Issue that pull request!**

## Development Setup

We recommend using the provided Docker environment for development:

```bash
# Build and start containers (first time)
docker compose up -d --build

# Install dependencies
docker compose exec bundle-dev composer install

# Run tests and quality checks (see docs/testing.md for details)
docker compose exec bundle-dev vendor/bin/phpunit
docker compose exec bundle-dev vendor/bin/phpstan analyse
# ... etc.
```

## Coding Standards

* Please try to follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standards, enforced by PHP CS Fixer.
* Use strict types (`declare(strict_types=1);`).
* Add PHPDoc blocks for public/protected methods and properties.

Thank you for your contribution!