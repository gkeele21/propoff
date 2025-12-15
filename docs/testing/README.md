# Testing Overview

PropOff has a comprehensive test suite with **515 tests** covering all critical functionality.

## Quick Start

```bash
# Run all tests
php artisan test

# Run with verbose output
php artisan test --verbose

# Run specific test suite
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature
```

## Test Statistics

- **Total Tests**: 515
- **Pass Rate**: 98.6% (508 passing, 7 skipped)
- **Unit Tests**: 194
- **Feature Tests**: 321
- **Total Assertions**: 1546
- **Duration**: ~39 seconds

## What's Tested

✅ **Core Features**:
- Captain system (invitations, permissions, management)
- Question system (3-tier architecture, templates, variables)
- Dual grading system (captain vs admin modes)
- Authentication (password + passwordless guest)
- Group management and member roles
- Event management and status flow
- Scoring calculations and type-aware comparison
- Leaderboard ranking and tie-breaking

✅ **Technical Areas**:
- Model relationships and data integrity
- Service layer business logic
- Policy-based authorization
- Controller workflows
- Database constraints
- Edge cases and error handling

## Documentation

- **[test-coverage.md](test-coverage.md)** - Complete test inventory (moved from tests/)
- **[running-tests.md](running-tests.md)** - How to execute tests (moved from tests/)
- **[writing-tests.md](writing-tests.md)** - How to write new tests (coming soon)

## Test Organization

```
tests/
├── Unit/
│   ├── Models/ (5 model test classes)
│   ├── Services/ (3 service test classes)
│   └── Policies/ (4 policy test classes)
└── Feature/
    ├── Admin/ (Admin feature tests)
    ├── Captain/ (Captain feature tests)
    ├── Auth/ (Authentication tests)
    └── User/ (User feature tests)
```

## Coverage Goals

**Current**: 98.6% test pass rate
**Target**: 100% pass rate (7 skipped tests to implement)

See [test-coverage.md](test-coverage.md) for detailed test inventory.
