# PropOff Documentation

Welcome to the PropOff documentation. This folder contains comprehensive documentation about the application's architecture, features, and development guidelines.

## Documentation Structure

### Core Documentation
- **[requirements.md](requirements.md)** - Functional and non-functional requirements
- **[design.md](design.md)** - Technical architecture and design patterns
- **[specs/PropOffUserFlow.md](specs/PropOffUserFlow.md)** - User flow specification (Play Hub, guests, smart routing)

### Feature Documentation
The `features/` folder contains detailed documentation for each major feature:

- **[captain-system.md](features/captain-system.md)** - Captain roles, invitations, and permissions
- **[question-system.md](features/question-system.md)** - 3-tier question architecture (Templates → Event → Group)
- **[grading-system.md](features/grading-system.md)** - Dual grading model (Captain vs Admin)
- **[groups.md](features/groups.md)** - Group management and member roles
- **[events.md](features/events.md)** - Event creation, status management, and types
- **[authentication.md](features/authentication.md)** - Hybrid auth system (Password + Passwordless)
- **[america-says.md](features/america-says.md)** - America Says game type and live display

### Technical Documentation
The `technical/` folder contains implementation details:

- **[database-relationships.md](technical/database-relationships.md)** - Key model relationships and DB schema
- **[frontend-patterns.md](technical/frontend-patterns.md)** - Vue patterns and component structure
- **[scoring-calculations.md](technical/scoring-calculations.md)** - Scoring logic and answer comparison

### Testing Documentation
The `testing/` folder contains all testing-related documentation:

- **[README.md](testing/README.md)** - Testing overview and philosophy
- **[running-tests.md](testing/running-tests.md)** - How to execute tests
- **[writing-tests.md](testing/writing-tests.md)** - How to write new tests
- **[test-coverage.md](testing/test-coverage.md)** - Test inventory

## When to Update Documentation

### Update Feature Docs When:
- Adding new features or capabilities
- Changing how existing features work
- Modifying business logic or rules
- Adding new user workflows

### Update Technical Docs When:
- Refactoring database schema
- Changing calculation formulas
- Adding new services or patterns
- Modifying API contracts

## Documentation Guidelines

### Writing Style
- **Clear and concise** - Get to the point quickly
- **Use examples** - Show code snippets and real scenarios
- **Explain why** - Don't just document what, explain why decisions were made
- **Keep it current** - Update docs when code changes

### What to Include
- **Business context** - Why does this feature exist?
- **Technical overview** - How does it work?
- **Code locations** - Where to find the implementation
- **Trade-offs** - What alternatives were considered?
- **Gotchas** - Known issues or edge cases

### What to Avoid
- Restating what code does (code should be self-documenting)
- Overly verbose explanations
- Documenting framework basics (link to Laravel/Vue docs instead)
- Duplicate information across multiple files

## For AI Assistants

When working on this codebase:

1. **Start here** - Read the relevant feature docs to understand business logic
2. **Check technical docs** - Understand implementation patterns
3. **Update as you go** - When making changes, update affected docs
4. **Ask questions** - If docs are unclear or missing, ask the user

The most valuable documentation explains:
- **Business rules** that aren't obvious from code
- **Relationships** between different parts of the system
- **Non-obvious decisions** and why they were made
- **Workflows** that span multiple files/services
