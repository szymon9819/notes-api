<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.5
- laravel/framework (LARAVEL) - v12
- laravel/prompts (PROMPTS) - v0
- laravel/sanctum (SANCTUM) - v4
- larastan/larastan (LARASTAN) - v3
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- phpunit/phpunit (PHPUNIT) - v11
- rector/rector (RECTOR) - v2

## Conventions

- You must follow all existing code conventions used in this application.
- Use descriptive names for variables and methods.
- Check for existing components before creating new ones.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.

## Replies

- Be concise and focused.

=== clean architecture & clean code rules ===

# Clean Architecture & Clean Code (STRICT RULES)

## Core Principles

- Code must be readable, explicit, and maintainable.
- Clever solutions are forbidden if they reduce readability.
- Consistency is mandatory.

---

## Domain Rules

- Domain MUST contain business logic only.
- Domain MUST NOT depend on:
  - Laravel
  - Eloquent
  - Database
  - HTTP
- Entities MUST contain behavior (no anemic models).
- All invariants MUST be enforced inside domain.

### Forbidden

- Using Eloquent models as domain entities
- Public mutable properties
- Setters without behavior

---

## Application Layer

- Orchestrates use cases only.
- No business logic.
- Uses DTOs and interfaces.

---

## Infrastructure

- Implements repositories and integrations.
- Depends on domain, never the opposite.

---

## CQRS

- Commands → Domain
- Queries → DTO / Read Models

### Strict Rules

- Domain entities MUST NOT be returned in API
- Query models MUST be separate

---

## Data Rules

- Database structure MUST NOT define domain structure.

If data:
- is not used in business logic
- but is needed for API

→ it belongs to Query Model, NOT domain

---

## Naming

- Use intention-revealing names
- No abbreviations
- No generic names like: helper, util, manager

---

## Functions

- Max ~20 lines
- Single responsibility
- No side effects
- Max 3 arguments

---

## Magic Values (FORBIDDEN)

- No magic numbers
- No magic strings

Use:
- constants
- enums
- value objects

---

## Error Handling

- Use exceptions
- No silent failures
- Use domain-specific exceptions

---

## Testing

- Every change MUST be tested
- Test behavior, not implementation
- Use Arrange–Act–Assert
- No logic inside tests

---

## Dependency Injection

- No "new" in domain or application
- Use constructor injection

---

## Anti-Patterns (FORBIDDEN)

- God classes
- Anemic domain model
- Fat controllers
- Business logic in controllers
- Returning Eloquent models from API
- Mixing read/write models

---

## Final Rule

If code is hard to read or violates these rules → it is invalid.
</laravel-boost-guidelines>
