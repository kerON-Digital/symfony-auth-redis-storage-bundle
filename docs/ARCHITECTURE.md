# Architecture Overview

This bundle is structured following principles inspired by Domain-Driven Design (DDD) to ensure a clear separation of concerns, testability, and maintainability.

## Layers

The codebase is primarily divided into two main layers within the `src/` directory:

1.  **Domain Layer (`src/Domain/`)**:
    * **Purpose:** Contains the core business logic and rules, completely independent of any specific framework or infrastructure details (like Redis or Symfony).
    * **Components:**
        * `Contract/`: Defines PHP interfaces (contracts) that specify the capabilities the bundle needs (e.g., `TokenBlacklistInterface`, `ActiveTokenStorageInterface`). These interfaces represent the "language" of the domain.
        * `Exception/`: (Optional) Domain-specific exceptions that represent business rule violations (e.g., `TokenAlreadyBlacklistedException` if that were a domain rule).
        * `ValueObject/`: (Optional) Value Objects specific to the domain, if any were needed.
    * **Dependencies:** This layer should have minimal dependencies, ideally only on PHP itself and potentially PSR interfaces. It **must not** depend on the Infrastructure layer or specific libraries like Redis clients or Symfony components.

2.  **Infrastructure Layer (`src/Infrastructure/`)**:
    * **Purpose:** Provides the concrete implementations of the domain contracts using specific technologies and integrates with the framework (Symfony). It deals with the "how" things are done.
    * **Components:**
        * `Persistence/`: Contains the classes that implement the Domain contracts using a specific storage mechanism (e.g., `RedisTokenBlacklist`, `RedisActiveTokenStorage` implementing `TokenBlacklistInterface` and `ActiveTokenStorageInterface` respectively, using Redis).
        * `DependencyInjection/`: Holds the Symfony-specific classes (`Configuration.php`, `*Extension.php`) responsible for processing configuration and wiring services into Symfony's Dependency Injection container.
        * `EventListener/` (If added later): Symfony event listeners that might use Domain or Application services.
        * `Command/` (If added later): Symfony console commands.
        * `Exception/`: Infrastructure-specific exceptions related to technical issues (e.g., `StorageException` wrapping a Redis connection error).
    * **Dependencies:** This layer depends on the Domain layer (to implement its interfaces) and on external libraries and the framework (e.g., Redis client, Symfony components).

## Key Principles Applied

* **Dependency Inversion Principle (DIP):** The Infrastructure layer depends on abstractions (interfaces) defined in the Domain layer, not the other way around. This is achieved through Symfony's service container and the use of interfaces for dependency injection.
* **Separation of Concerns:** Domain logic is cleanly separated from infrastructure details. This makes the core logic easier to understand and test.
* **Testability:** The Domain layer can be unit-tested without any infrastructure dependencies. The Infrastructure layer can be tested using mocks (unit tests) or against real services like Redis (integration tests).
* **Flexibility:** If support for a different storage mechanism (e.g., Memcached, Doctrine) were needed in the future, only a new implementation within the Infrastructure layer would be required, without changing the Domain contracts or how the application interacts with the bundle's interfaces.

## Bundle Integration (`*Bundle.php`)

The `KeronDigitalAuthRedisStorageBundle.php` class at the root of `src/` acts as the entry point for Symfony to recognize the package as a bundle.

This layered approach aims to create a robust, adaptable, and maintainable bundle.
