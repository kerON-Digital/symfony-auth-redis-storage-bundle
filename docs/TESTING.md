# Testing and Quality Assurance

This bundle includes a suite of automated tests and quality assurance tools to ensure its functionality, stability, and code quality. We encourage contributors to run these checks before submitting pull requests and to add new tests for any new features or bug fixes.

## Prerequisites

* **Docker:** Required for running the development and testing environment. [Install Docker](https://docs.docker.com/get-docker/).
* **Docker Compose:** Required for orchestrating the Docker containers. Usually included with Docker Desktop or installed separately. [Install Docker Compose](https://docs.docker.com/compose/install/).

## Setting up the Test Environment

The test environment is managed using Docker Compose, based on the `docker-compose.yml` and `docker/Dockerfile` included in the bundle's root directory.

1.  **Navigate** to the bundle's root directory in your terminal.
2.  **Build and start** the Docker containers (this only needs the `--build` flag the first time or after Dockerfile changes):
    ```bash
    docker compose up -d --build
    ```
3.  **Install Composer dependencies** inside the PHP container:
    ```bash
    docker compose exec bundle-dev composer install
    ```

This sets up a container (`bundle-dev`) with the correct PHP version, extensions (including Redis, PCOV), and Composer, along with a separate container running a Redis instance (`redis`) for integration tests.

## Running All Checks (Recommended before commit/PR)

It's recommended to run all checks sequentially. You can create a script for this or run them individually:

```bash
# 1. Composer Validate & Audit
docker compose exec bundle-dev composer validate --strict
docker compose exec bundle-dev composer audit

# 2. Composer Normalize Check
docker compose exec bundle-dev composer normalize --dry-run

# 3. Code Style Check (PHP CS Fixer)
docker compose exec bundle-dev vendor/bin/php-cs-fixer fix --dry-run --diff --config=.php-cs-fixer.dist.php --allow-risky=yes

# 4. Code Quality Analysis (PHPMD)
docker compose exec bundle-dev vendor/bin/phpmd src text phpmd.xml.dist

# 5. Static Analysis (PHPStan)
docker compose exec bundle-dev vendor/bin/phpstan analyse

# 6. Tests (PHPUnit)
docker compose exec bundle-dev vendor/bin/phpunit
```

## Individual Checks

### Unit and Integration Tests (PHPUnit)

Runs all automated tests.

```bash
docker compose exec bundle-dev vendor/bin/phpunit
```

*To run only a specific suite (e.g., Integration):*
```bash
docker compose exec bundle-dev vendor/bin/phpunit --testsuite Integration
```

### Code Coverage Report (PHPUnit + PCOV)

Generates an HTML code coverage report in the `build/coverage/` directory. PCOV is installed via the Dockerfile.

```bash
mkdir -p build/coverage # Ensure directory exists
docker compose exec bundle-dev vendor/bin/phpunit --coverage-html build/coverage
```
Open `build/coverage/index.html` in your browser.

*To generate a Clover XML report (for CI):*
```bash
mkdir -p build/logs # Ensure directory exists
docker compose exec bundle-dev vendor/bin/phpunit --coverage-clover build/logs/clover.xml
```

### Static Analysis (PHPStan)

Checks the code for type errors and potential bugs.

```bash
docker compose exec bundle-dev vendor/bin/phpstan analyse
```

### Code Style Check (PHP CS Fixer)

Checks if the code adheres to the defined coding standards.

```bash
# Check only (shows diff, fails if changes needed)
docker compose exec bundle-dev vendor/bin/php-cs-fixer fix --dry-run --diff --config=.php-cs-fixer.dist.php --allow-risky=yes

# Automatically fix style issues
docker compose exec bundle-dev vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php --allow-risky=yes
```

### Code Quality Analysis (PHPMD)

Detects potential code smells and overly complex code.

```bash
docker compose exec bundle-dev vendor/bin/phpmd src text phpmd.xml.dist
```

### Composer Dependencies Security Audit

Checks installed dependencies for known security vulnerabilities.

```bash
docker compose exec bundle-dev composer audit
```

### Composer Normalize Check

Checks if the `composer.json` file is formatted correctly.

```bash
# Check only
docker compose exec bundle-dev composer normalize --dry-run

# Automatically normalize composer.json
docker compose exec bundle-dev composer normalize
```

## Continuous Integration (CI)

Most of these checks are automated using GitHub Actions. Refer to the `.github/workflows/ci.yaml` file for the workflow configuration. Running the checks locally before pushing is highly recommended.
```