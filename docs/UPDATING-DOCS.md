# Updating Documentation

## When to Update Documentation

### Immediate Updates Required

Update documentation **immediately** when:

1. **Adding new features**
   - Create or update relevant feature doc
   - Add to database-relationships.md if new tables
   - Update frontend-patterns.md if new UI patterns

2. **Changing business logic**
   - Update affected feature docs
   - Update scoring-calculations.md if formulas change

3. **Modifying database schema**
   - Update database-relationships.md
   - Update affected feature docs

4. **Changing calculations/formulas**
   - Update scoring-calculations.md
   - Document why change was made

### Optional Updates

Consider updating when:

1. **Refactoring code** (no behavior change)
   - Only update if pattern changes significantly
   - Update code locations if files moved

2. **Bug fixes**
   - Update if fix reveals gap in documentation
   - Add to "Gotchas" section if edge case

3. **Performance optimizations**
   - Update if pattern changes (e.g., caching strategy)
   - Otherwise, can skip

## How to Update Documentation

### Feature Documentation

**Location**: `docs/features/`

**When adding new feature**:
1. Create new file: `docs/features/[feature-name].md`
2. Use existing files as template
3. Include:
   - Overview (what it does, why it exists)
   - Key concepts (business terms)
   - Database relationships (high-level)
   - Code locations (controllers, services, views)
   - Workflows (user journeys)
   - Design decisions (why this approach)
   - Common patterns (code examples)
4. Add link to `docs/README.md`

**When modifying existing feature**:
1. Update affected section(s)
2. Update "Last Updated" if exists
3. Add to "Recent Changes" if major

### Technical Documentation

**Location**: `docs/technical/`

**When modifying database**:
1. Update `database-relationships.md`
   - Add/modify entity relationships
   - Update schema examples
   - Add to common patterns if needed

**When changing calculations**:
1. Update `scoring-calculations.md`
   - Show old and new formula
   - Explain why changed
   - Update examples

**When changing frontend patterns**:
1. Update `frontend-patterns.md`
   - Add new pattern examples
   - Update component structure if changed

## Documentation Templates

### Feature Doc Template

```markdown
# [Feature Name]

## Overview

Brief description of what this feature does and why it exists.

## Key Concepts

- **Term 1**: Definition
- **Term 2**: Definition

## Database Relationships

High-level entity relationships diagram or list.

## Code Locations

### Models
- `app/Models/Thing.php` - Description

### Controllers
- `app/Http/Controllers/ThingController.php` - Description

### Services
- `app/Services/ThingService.php` - Description

### Views
- `resources/js/Pages/Thing/Index.vue` - Description

## Workflows

### User Workflow Name
1. Step 1
2. Step 2
3. Step 3

## Design Decisions

### Why We Chose X Over Y
**Decision**: Brief statement

**Reasoning**:
- Reason 1
- Reason 2

**Trade-off**: What we gave up

## Common Patterns

### Pattern Name
```php
// Code example
```

## Future Enhancements

- Potential improvement 1
- Potential improvement 2
```

### Technical Doc Template

```markdown
# [Technical Topic]

## Overview

What this document covers.

## [Main Section]

### Subsection

Detailed explanation with examples.

### Code Examples

```php
// Example code
```

## Common Patterns

Frequently used code patterns.

## Performance Considerations

Tips for optimization.
```

## AI Assistant Guidelines

When I (Claude) make changes to the codebase, I should:

1. **Identify affected docs**
   - Which features changed?
   - What technical aspects changed?

2. **Update immediately**
   - Don't wait for user to ask
   - Update in same session as code changes

3. **Be concise**
   - Focus on what changed
   - Don't rewrite entire doc unless major overhaul

4. **Ask questions**
   - If business logic unclear, ask user
   - If design decision not obvious, ask why

## Examples

### Example 1: Adding New Feature

**Change**: Adding team competitions

**Updates Required**:
1. Create `docs/features/team-competitions.md`
2. Update `docs/README.md` to link to new doc
3. Update `docs/technical/database-relationships.md` (add Team relationships)
4. Update `docs/requirements.md` if feature adds new requirements

### Example 2: Changing Calculation

**Change**: Modifying scoring formula

**Updates Required**:
1. Update `docs/technical/scoring-calculations.md` (new formula)
2. Update `docs/features/grading-system.md` (impact on grading)
3. Document why change was made
4. Update examples with new results

### Example 3: Database Schema Change

**Change**: Adding `dismissed_notifications` to users table

**Updates Required**:
1. Update `docs/technical/database-relationships.md` (add field to schema)
2. Update `docs/features/authentication.md` (explain dismissible notifications)
3. Update design.md if significant architectural change

### Example 4: UI Pattern Change

**Change**: Adding real-time polling for game state

**Updates Required**:
1. Update `docs/features/america-says.md` (explain polling)
2. Update `docs/technical/frontend-patterns.md` (show pattern example)
3. Update design.md if affects architecture section

## Version Control

### Versioning Strategy

**Not Currently Used**: Docs don't have versions separate from code.

**Alternative**: Git history tracks doc changes alongside code changes.

**Future**: Could add version numbers to major docs:
```markdown
**Version**: 2.0
**Last Updated**: 2025-12-15
```

### Change Log

Add "Recent Changes" section to major docs:

```markdown
## Recent Changes

- v1.2 (2025-12-15): Added dual entry mode
- v1.1 (2025-12-10): Updated scoring formula
- v1.0 (2025-12-01): Initial documentation
```

## Quality Checklist

Before finalizing doc updates:

- [ ] Accurate (reflects current code)
- [ ] Clear (easy to understand)
- [ ] Concise (no unnecessary detail)
- [ ] Complete (covers key aspects)
- [ ] Examples (shows real usage)
- [ ] Explains "why" (not just "what")
- [ ] Code locations accurate
- [ ] Links work (if any)

## Questions to Ask

When documenting, consider:

1. **Why does this feature exist?**
2. **What problem does it solve?**
3. **How does it work at a high level?**
4. **Where is the code located?**
5. **What design decisions were made and why?**
6. **What are common use cases?**
7. **What are the gotchas or edge cases?**
8. **What might change in the future?**

## Getting Help

If you (the user) need to clarify documentation:

1. **Point me to the file**: "Update docs/features/grading-system.md"
2. **Explain the change**: "We now support partial credit"
3. **Clarify business rules**: "Partial credit awards 50% of points"
4. **I'll ask questions**: To ensure I document accurately

## Maintenance

### Quarterly Review

Every 3 months, review docs for:
- Outdated information
- Missing new features
- Broken links
- Stale examples

### When Docs Diverge from Code

If you notice docs are wrong:
1. Note the discrepancy
2. Tell me (Claude) what's actually correct
3. I'll update immediately

**Example**:
> "The docs say captains can't add custom questions, but they actually can."

I'll then update:
- `docs/features/captain-system.md`
- `docs/features/question-system.md`
- Any other affected docs
