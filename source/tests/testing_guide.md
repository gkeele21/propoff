# PropOff Testing Guide

**Quick reference for running and writing tests**

---

## Quick Start

```bash
# From project root
cd C:\Bert\PropOff\source

# Run all tests
php artisan test

# Run with verbose output
php artisan test --verbose

# Run only unit tests
php artisan test --testsuite=Unit

# Run only feature tests
php artisan test --testsuite=Feature
```

---

## Current Status

**240 tests passing (100%)** 🎉

- ✅ All core business logic tested
- ✅ **Dual grading system fully tested** ⭐
- ✅ **Leaderboard system fully tested** ⭐
- ✅ **Event management fully tested** ⭐
- ✅ **Authorization fully tested** ⭐
- ✅ All model tests passing (5 models)
- ✅ All service tests passing (3 services)
- ✅ All policy tests passing (4 policies)
- ✅ All feature tests passing

---

## Running Specific Tests

### Run by Test File

```bash
# Run a specific test class
php artisan test tests/Unit/Services/EntryServiceTest.php
php artisan test tests/Unit/Services/LeaderboardServiceTest.php
php artisan test tests/Unit/Services/EventServiceTest.php

# Run all model tests
php artisan test tests/Unit/Models/

# Run all service tests
php artisan test tests/Unit/Services/

# Run all policy tests
php artisan test tests/Unit/Policies/
```

### Run by Test Name

```bash
# Run a specific test method
php artisan test --filter=captain_grading_mode_uses_group_question_answers

# Run all grading tests
php artisan test --filter=grading

# Run all tests with "dual" in the name
php artisan test --filter=dual
```

### Stop on First Failure

```bash
# Stop immediately when a test fails (useful for debugging)
php artisan test --stop-on-failure
```

---

## Key Test Examples

### Testing Dual Grading System

```bash
# The most critical feature - test it thoroughly
php artisan test tests/Feature/DualGradingSystemTest.php

# Test the entry service (grading logic)
php artisan test tests/Unit/Services/EntryServiceTest.php
```

### Testing Leaderboard System

```bash
# Test rankings, ties, and statistics
php artisan test tests/Unit/Services/LeaderboardServiceTest.php
```

### Testing Event Management

```bash
# Test event playability, search, and filtering
php artisan test tests/Unit/Services/EventServiceTest.php
```

### Testing Models

```bash
php artisan test tests/Unit/Models/UserTest.php
php artisan test tests/Unit/Models/GroupTest.php
php artisan test tests/Unit/Models/GroupQuestionTest.php
```

### Testing Authorization

```bash
# Test all authorization rules
php artisan test tests/Unit/Policies/

# Test specific policy
php artisan test tests/Unit/Policies/EntryPolicyTest.php
php artisan test tests/Unit/Policies/EventPolicyTest.php
```

### Testing Authentication

```bash
php artisan test tests/Feature/AuthenticationTest.php
php artisan test tests/Feature/ProfileTest.php
```

---

## Test Database Configuration

### Option 1: Use Main Database (Default)

Tests will use your configured database. The `RefreshDatabase` trait ensures each test starts with a clean slate by running in a transaction that's rolled back after each test.

**Pros:** Uses real database engine
**Cons:** Slower than in-memory

### Option 2: SQLite In-Memory (Faster) ⚡

Uncomment these lines in `phpunit.xml`:

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

**Pros:** Much faster test execution
**Cons:** Requires SQLite to be installed

---

## Understanding Test Output

### Successful Run

```
PASS  Tests\Unit\Models\UserTest
✓ it has fillable attributes                0.03s
✓ is admin returns true for admin users     0.02s
✓ it can be captain of groups                0.04s

PASS  Tests\Unit\Services\EntryServiceTest
✓ captain grading mode uses group_question_answers  0.06s
✓ admin grading mode uses event answers             0.05s

Tests:  240 passed (510 assertions)
Duration: 20.29s
```

### Failed Test

```
FAILED  Tests\Unit\Services\EntryServiceTest > captain_grading_mode_uses_group_question_answers

Failed asserting that false is true.

at tests/Unit/Services/EntryServiceTest.php:45
   41:     $this->actingAs($captain);
   42:
   43:     $result = $this->entryService->gradeEntry($entry);
   44:
   45:     $this->assertTrue($result->is_correct);
```

The error shows:
- Which test failed
- What the assertion was
- Where in the code it failed
- Context from surrounding lines

---

## Common Issues and Solutions

### Issue: "Database not found"

**Solution:** Run migrations first:
```bash
php artisan migrate
```

### Issue: "Factory not found"

**Solution:** Ensure all factories exist in `database/factories/` and are properly named.

For example, `EntryFactory.php` not `SubmissionFactory.php`.

### Issue: "Route not defined"

**Solution:** Check that route names match in your routes files (`web.php`, `admin.php`, etc.)

### Issue: Tests running slowly

**Solutions:**
1. Use SQLite in-memory database (see configuration above)
2. Run specific test files instead of entire suite
3. Use `--stop-on-failure` to stop after first error

### Issue: "RefreshDatabase not working"

**Solution:** Make sure your test class uses the trait:

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class MyTest extends TestCase
{
    use RefreshDatabase;

    // ... tests
}
```

---

## Writing New Tests

When adding new features, add corresponding tests:

### 1. For Model Methods

Add to relevant file in `tests/Unit/Models/`

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class MyModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_new_method()
    {
        $model = MyModel::factory()->create();

        $result = $model->newMethod();

        $this->assertEquals('expected', $result);
    }
}
```

### 2. For Service Methods

Add to relevant file in `tests/Unit/Services/`

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class MyServiceTest extends TestCase
{
    use RefreshDatabase;

    protected MyService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MyService();
    }

    /** @test */
    public function it_performs_business_logic()
    {
        $data = ['key' => 'value'];

        $result = $this->service->process($data);

        $this->assertNotNull($result);
        $this->assertEquals('expected', $result->key);
    }
}
```

### 3. For Controller Actions (Feature Tests)

Add to relevant file in `tests/Feature/`

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class MyFeatureTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_perform_action()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('my.action'), [
            'data' => 'value',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('table', ['data' => 'value']);
    }
}
```

### 4. For Authorization (Policy Tests)

Add to relevant file in `tests/Unit/Policies/`

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class MyPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected MyPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new MyPolicy();
    }

    /** @test */
    public function only_authorized_users_can_access()
    {
        $user = User::factory()->create();
        $resource = Resource::factory()->create();

        $this->assertFalse($this->policy->view($user, $resource));
    }

    /** @test */
    public function admin_can_access()
    {
        $admin = User::factory()->admin()->create();
        $resource = Resource::factory()->create();

        $this->assertTrue($this->policy->view($admin, $resource));
    }
}
```

---

## Using Factories in Tests

Factories make it easy to create test data:

```php
// Create a basic user
$user = User::factory()->create();

// Create an admin user
$admin = User::factory()->admin()->create();

// Create a guest user
$guest = User::factory()->guest()->create();

// Create with specific attributes
$user = User::factory()->create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
]);

// Create multiple instances
$users = User::factory()->count(5)->create();

// Create without saving to database (for testing validation)
$user = User::factory()->make();
```

---

## Assertions Cheat Sheet

### Common Assertions

```php
// Boolean assertions
$this->assertTrue($condition);
$this->assertFalse($condition);

// Equality
$this->assertEquals($expected, $actual);
$this->assertNotEquals($expected, $actual);

// Null checks
$this->assertNull($variable);
$this->assertNotNull($variable);

// Array/Collection
$this->assertCount(5, $array);
$this->assertContains($value, $array);
$this->assertEmpty($array);
$this->assertNotEmpty($array);

// String
$this->assertStringContainsString('needle', 'haystack');
$this->assertStringStartsWith('prefix', $string);

// Database
$this->assertDatabaseHas('users', ['email' => 'test@example.com']);
$this->assertDatabaseMissing('users', ['email' => 'deleted@example.com']);

// HTTP Response
$response->assertStatus(200);
$response->assertRedirect();
$response->assertRedirect(route('home'));
$response->assertSessionHas('key');
$response->assertSessionHasErrors('field');
```

### Testing Exceptions

```php
// Expect an exception to be thrown
$this->expectException(ValidationException::class);
$this->expectExceptionMessage('The given data was invalid');

// Code that should throw exception
$this->service->validateData([]);
```

---

## Test Organization

```
tests/
├── TestCase.php                    # Base test class
├── Unit/                           # 194 tests - Business logic
│   ├── Models/                    # 65 tests
│   │   ├── UserTest.php           # 15 tests
│   │   ├── GroupTest.php          # 15 tests
│   │   ├── EventTest.php          # 8 tests
│   │   ├── EntryTest.php          # 9 tests
│   │   └── GroupQuestionTest.php  # 18 tests
│   ├── Services/                  # 56 tests
│   │   ├── EntryServiceTest.php   # 13 tests ⭐
│   │   ├── EventServiceTest.php   # 22 tests
│   │   └── LeaderboardServiceTest.php # 21 tests
│   └── Policies/                  # 73 tests
│       ├── GroupPolicyTest.php    # 19 tests
│       ├── EntryPolicyTest.php    # 20 tests
│       ├── EventPolicyTest.php    # 21 tests
│       └── EventQuestionPolicyTest.php # 13 tests
└── Feature/                        # 46 tests - Full stack
    ├── DualGradingSystemTest.php  # 7 tests ⭐
    ├── AuthenticationTest.php     # 12 tests
    ├── ProfileTest.php            # 5 tests
    ├── Auth/                      # 16 tests (Breeze)
    └── ExampleTest.php            # 1 test
```

⭐ = Most critical tests

---

## Best Practices

### 1. ✅ Always use RefreshDatabase trait

Ensures test isolation - each test starts with a clean database.

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class MyTest extends TestCase
{
    use RefreshDatabase;
}
```

### 2. ✅ Use factories for test data

Keeps tests maintainable and readable.

```php
// Good
$user = User::factory()->create();

// Avoid
$user = new User();
$user->name = 'Test User';
$user->email = 'test@example.com';
$user->save();
```

### 3. ✅ Test both success and failure cases

Comprehensive coverage requires testing both paths.

```php
/** @test */
public function it_accepts_valid_data()
{
    $result = $service->process(['valid' => 'data']);
    $this->assertTrue($result->success);
}

/** @test */
public function it_rejects_invalid_data()
{
    $this->expectException(ValidationException::class);
    $service->process(['invalid' => 'data']);
}
```

### 4. ✅ Use descriptive test names

Test names should describe what is being tested.

```php
// Good
/** @test */
public function captain_can_add_custom_questions()

// Avoid
/** @test */
public function test_questions()
```

### 5. ✅ Keep tests focused

One logical assertion per test when possible.

```php
// Good - focused test
/** @test */
public function user_can_view_own_entry()
{
    $user = User::factory()->create();
    $entry = Entry::factory()->create(['user_id' => $user->id]);

    $this->assertTrue($user->can('view', $entry));
}

// Avoid - testing too many things
/** @test */
public function user_permissions()
{
    // Tests view, edit, delete all in one test
}
```

### 6. ✅ Test edge cases

Don't just test the happy path.

```php
// Test edge cases
- Null values
- Empty arrays
- Past dates
- Expired invitations
- Maximum limits
- Tie scenarios
```

### 7. ✅ Arrange, Act, Assert pattern

Structure your tests clearly.

```php
/** @test */
public function it_calculates_score_correctly()
{
    // Arrange - Set up test data
    $user = User::factory()->create();
    $entry = Entry::factory()->create();

    // Act - Perform the action
    $result = $this->service->calculateScore($entry);

    // Assert - Verify the result
    $this->assertEquals(85, $result->percentage);
}
```

---

## Continuous Integration

### GitHub Actions Example

Create `.github/workflows/tests.yml`:

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v3

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          extensions: pdo, pdo_sqlite

      - name: Install Dependencies
        run: composer install --prefer-dist --no-progress

      - name: Run Tests
        run: php artisan test
        env:
          DB_CONNECTION: sqlite
          DB_DATABASE: ":memory:"
```

---

## Code Coverage

To generate code coverage report (requires Xdebug):

```bash
# Simple coverage summary
php artisan test --coverage

# Minimum coverage threshold (fails if below 80%)
php artisan test --coverage --min=80

# HTML coverage report
php artisan test --coverage-html coverage/

# Then open coverage/index.html in browser
```

---

## Debugging Failed Tests

### Use `dd()` or `dump()`

```php
/** @test */
public function it_does_something()
{
    $result = $service->process();

    dd($result); // Dump and die - stops execution
    dump($result); // Dump but continue

    $this->assertEquals('expected', $result);
}
```

### Check Database State

```php
/** @test */
public function it_saves_data()
{
    $service->saveData(['key' => 'value']);

    // Check what's actually in the database
    $this->assertDatabaseHas('table', ['key' => 'value']);

    // Or get the actual record
    $record = DB::table('table')->first();
    dd($record); // See what was actually saved
}
```

### Use `--stop-on-failure`

```bash
# Stop at first failure to debug
php artisan test --stop-on-failure
```

---

## Next Steps

1. ✅ **Run the full test suite:** `php artisan test`
2. ✅ **Review test coverage:** Check `tests/test_suite.md`
3. ✅ **Add tests for new features** as you develop
4. ✅ **Run tests before every commit**
5. ✅ **Set up CI/CD** to run tests automatically

---

## Resources

- **Test Suite Documentation:** `tests/test_suite.md`
- **Laravel Testing Docs:** https://laravel.com/docs/10.x/testing
- **PHPUnit Docs:** https://phpunit.de/documentation.html
- **Test Factories:** `database/factories/`

---

## Summary

**You have 240 comprehensive tests covering:**

✅ All critical business logic
✅ Dual grading system
✅ Authorization and permissions
✅ Complete user flows
✅ Edge cases and error handling

**Status:** Production Ready 🚀

**Test Duration:** ~20 seconds for full suite
**Pass Rate:** 100%
**Last Updated:** December 2, 2025
