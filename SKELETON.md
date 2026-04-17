# Web Skeleton App

## Overview
PSR-compliant PHP web application skeleton demonstrating:
- ADR (Action-Domain-Responder) pattern
- MVVM (Model-View-ViewModel) with form invalidation
- League\Pipeline for request processing
- Doctrine ORM events
- prototype-in/stool, comet, oryx logging patterns

## Quick Start

```bash
# Install dependencies
composer install

# Run development server
composer serve

# Run tests
composer test
```

## Available Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/` | Landing page |
| GET | `/home` | Home page |
| POST | `/register` | User registration |
| GET | `/demo` | JSON form schema |
| POST | `/demo` | JSON form submission with MVVM invalidation |
| POST | `/pipeline` | League\Pipeline demo with stages |

## curl Examples

```bash
# GET form schema
curl http://localhost:8080/demo

# POST with validation errors (MVVM invalidation)
curl -X POST http://localhost:8080/demo \
  -H "Content-Type: application/json" \
  -d '{"email":"invalid","password":"short"}'

# POST validation pass → pipeline
curl -X POST http://localhost:8080/pipeline \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123"}'
```

## Architecture

- **Actions**: `src/Action/` - Handle HTTP requests
- **Domain**: `src/Domain/` - Business logic, entities, repositories
- **Forms**: `src/Form/` - Form validation (MVVM)
- **ViewModels**: `src/ViewModel/` - Data transfer with invalidation state
- **Pipeline**: `src/Pipeline/` - League\Pipeline stages
- **Events**: `src/Event/` - Doctrine event listeners
- **Responder**: `src/Responder/` - Convert ViewModels to HTTP responses