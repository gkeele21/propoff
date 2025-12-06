# Template Question Management & Variable Replacement Improvements

**Version:** 1.0
**Date:** December 5, 2024
**Status:** Proposed

---

## Table of Contents

1. [Executive Summary](#executive-summary)
2. [Feature 1: Dynamic Category Search](#feature-1-dynamic-category-search)
3. [Feature 2: Consolidated Variable Replacement](#feature-2-consolidated-variable-replacement)
4. [Database Changes](#database-changes)
5. [Code Changes](#code-changes)
6. [Testing Requirements](#testing-requirements)
7. [Migration Path](#migration-path)
8. [User Experience Flow](#user-experience-flow)

---

## Executive Summary

This specification outlines two major improvements to the template question management system:

1. **Dynamic Category Search**: Replace the rigid event-category matching with a flexible, multi-category tagging system
2. **Consolidated Variable Replacement**: Replace question-by-question variable entry with a single consolidated input screen

### Goals
- Increase flexibility in template organization and discovery
- Reduce data entry time by eliminating redundant variable inputs
- Remove unnecessary category field from events
- Enable questions to belong to multiple categories

### Impact
- **User Experience**: Significantly faster question import process, more flexible template discovery
- **Data Model**: Event category field removed, QuestionTemplate category becomes comma-separated list
- **Backend**: New filtering logic, consolidated variable collection and replacement
- **Frontend**: New UI for category search and consolidated variable entry

---

## Feature 1: Dynamic Category Search

### Current Behavior

```
Event Model:
  - category: "sports" (string, max 100)

QuestionTemplate Model:
  - category: "sports" (string, max 100)

Flow:
  1. Admin creates event with category "sports"
  2. Admin navigates to "Manage Questions" for event
  3. System shows ALL templates where category = "sports"
  4. Admin can only see templates matching event's category
```

**Problems:**
- Events are locked to one category forever
- Templates can only belong to one category
- No flexibility to search across categories
- Category must be set at event creation

---

### New Behavior

```
Event Model:
  - category: REMOVED

QuestionTemplate Model:
  - category: "football,nfl,sports" (comma-separated list)

Flow:
  1. Admin creates event (no category)
  2. Admin navigates to "Manage Questions" for event
  3. UI shows text input field with "Find" button
  4. Admin types "football" and clicks "Find"
  5. System shows ALL templates where category list contains "football"
  6. Admin can select/import desired templates
  7. Admin types "nfl" and clicks "Find"
  8. System shows ALL templates where category list contains "nfl"
  9. Templates already imported are hidden
  10. Can repeat with different search terms
```

**Benefits:**
- Events not locked to single category
- Questions can have multiple tags/categories
- Dynamic searching based on current needs
- More flexible template organization

---

### User Interface Changes

#### Location: `Create.vue` - Template Search Section

**BEFORE:**
```vue
<div class="available-templates-section">
  <h3>Available Templates (Category: {{ event.category }})</h3>
  <p v-if="availableTemplates.length === 0">
    No templates found for this category.
  </p>
  <template-list :templates="availableTemplates" />
</div>
```

**AFTER:**
```vue
<div class="template-search-section">
  <h3>Find Template Questions</h3>

  <!-- Category Search Input -->
  <div class="search-controls">
    <TextInput
      v-model="categorySearch"
      placeholder="Enter category (e.g., football, nfl, sports)"
      @keyup.enter="findTemplatesByCategory"
    />
    <PrimaryButton @click="findTemplatesByCategory">
      Find
    </PrimaryButton>
  </div>

  <!-- Search Results -->
  <div v-if="searchPerformed" class="search-results">
    <p v-if="filteredTemplates.length === 0" class="text-gray-500">
      No templates found for category "{{ lastSearchTerm }}".
    </p>
    <p v-else class="text-gray-700 mb-2">
      Found {{ filteredTemplates.length }} templates for "{{ lastSearchTerm }}"
    </p>

    <template-list
      :templates="filteredTemplates"
      :selected="selectedTemplates"
      :already-imported="alreadyImportedTemplateIds"
      @select="toggleTemplateSelection"
    />
  </div>
</div>
```

**Key UI Elements:**
1. **Text input** for category search (supports any text)
2. **"Find" button** to trigger search
3. **Search results area** showing matching templates
4. **Result count** and search term display
5. **No results message** if no matches
6. **Already imported indicator** to hide previously imported templates

---

### Backend API Changes

#### New Endpoint: Search Templates by Category

**Route:** `GET /api/events/{event}/templates/search`

**Query Parameters:**
- `category` (string, required): Category to search for

**Response:**
```json
{
  "templates": [
    {
      "id": 1,
      "title": "Super Bowl Winner",
      "question_text": "Who will win Super Bowl {year}?",
      "question_type": "multiple_choice",
      "category": "football,nfl,sports",
      "variables": ["year"],
      "default_points": 10,
      "display_order": 1,
      "already_imported": false
    },
    {
      "id": 2,
      "title": "MVP Prediction",
      "question_text": "Who will be MVP of {team}?",
      "question_type": "text",
      "category": "football,nfl",
      "variables": ["team"],
      "default_points": 15,
      "display_order": 2,
      "already_imported": true
    }
  ],
  "total": 2,
  "already_imported_count": 1
}
```

**Logic:**
```php
public function searchTemplates(Request $request, Event $event)
{
    $searchCategory = $request->input('category');

    if (empty($searchCategory)) {
        return response()->json(['templates' => [], 'total' => 0]);
    }

    // Get all templates where category contains the search term
    $templates = QuestionTemplate::where('category', 'LIKE', "%{$searchCategory}%")
        ->orderBy('display_order')
        ->get();

    // Get IDs of templates already imported to this event
    $importedTemplateIds = EventQuestion::where('event_id', $event->id)
        ->whereNotNull('template_id')
        ->pluck('template_id')
        ->toArray();

    // Mark templates as already imported
    $templates->each(function($template) use ($importedTemplateIds) {
        $template->already_imported = in_array($template->id, $importedTemplateIds);
    });

    // Optionally filter out already imported ones
    $filteredTemplates = $templates->filter(function($template) {
        return !$template->already_imported;
    })->values();

    return response()->json([
        'templates' => $filteredTemplates,
        'total' => $filteredTemplates->count(),
        'already_imported_count' => count($importedTemplateIds)
    ]);
}
```

---

### Category Storage Format

**QuestionTemplate.category field:**
- Type: `string(255)` (increased from 100 to accommodate multiple categories)
- Format: Comma-separated list
- Examples:
  - `"football"`
  - `"football,nfl"`
  - `"football,nfl,sports,superbowl"`
  - `"basketball,nba"`

**Parsing Logic:**
```php
// In QuestionTemplate model
public function hasCategory(string $search): bool
{
    $categories = array_map('trim', explode(',', $this->category));
    return in_array(strtolower($search), array_map('strtolower', $categories));
}

public function getCategoriesAttribute(): array
{
    return array_map('trim', explode(',', $this->category));
}
```

**Search Query:**
```php
// Simple LIKE search (case-insensitive)
QuestionTemplate::where('category', 'LIKE', "%{$search}%")

// Or more precise with explode check
QuestionTemplate::get()->filter(function($template) use ($search) {
    return $template->hasCategory($search);
});
```

---

### Template Creation UI Enhancement

**QuestionTemplates/Create.vue** - Category Input

**BEFORE:**
```vue
<TextInput
  v-model="form.category"
  label="Category"
  placeholder="e.g., sports"
/>
```

**AFTER:**
```vue
<div class="category-input">
  <label>Categories</label>
  <p class="text-sm text-gray-600 mb-2">
    Enter multiple categories separated by commas (e.g., football,nfl,sports)
  </p>
  <TextInput
    v-model="form.category"
    placeholder="e.g., football,nfl,sports"
  />

  <!-- Optional: Show tags preview -->
  <div v-if="categoryTags.length > 0" class="mt-2 flex gap-2 flex-wrap">
    <span
      v-for="tag in categoryTags"
      :key="tag"
      class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-sm"
    >
      {{ tag }}
    </span>
  </div>
</div>
```

```javascript
const categoryTags = computed(() => {
  if (!form.category) return [];
  return form.category.split(',').map(c => c.trim()).filter(c => c);
});
```

---

## Feature 2: Consolidated Variable Replacement

### Current Behavior

```
Admin selects 20 templates, each with variable {AFC Team}

Current Flow:
  1. Click "Import"
  2. Modal shows Template 1 with variable input for {AFC Team}
  3. Enter "KC Chiefs"
  4. Click "Next" or "Add"
  5. Modal shows Template 2 with variable input for {AFC Team}
  6. Enter "KC Chiefs" AGAIN
  7. Repeat 18 more times...
```

**Problem:** Massive data entry redundancy

---

### New Behavior

```
Admin selects 40 templates:
  - 20 have {AFC Team}
  - 18 have {NFC Team}
  - 35 have {Year}
  - 10 have {Stadium}

New Flow:
  1. Click "Import"
  2. Modal shows ALL DISTINCT variables:
     - {AFC Team}: _______
     - {NFC Team}: _______
     - {Year}: _______
     - {Stadium}: _______
  3. Fill out ONCE:
     - AFC Team: "KC Chiefs"
     - NFC Team: "San Francisco 49ers"
     - Year: "2025"
     - Stadium: "Allegiant Stadium"
  4. Click "Import All"
  5. System replaces ALL variables across ALL 40 questions
```

**Benefits:**
- Enter each variable value ONCE
- See all variables in one place
- Faster import process
- Less error-prone

---

### User Interface Changes

#### New Consolidated Variable Modal

**Location:** `Create.vue` - Replace existing variable modals

```vue
<template>
  <!-- Consolidated Variable Input Modal -->
  <Modal :show="showConsolidatedVariableModal" @close="closeConsolidatedVariableModal">
    <div class="p-6">
      <h2 class="text-xl font-semibold mb-4">
        Fill in Variables for {{ selectedTemplates.length }} Question{{ selectedTemplates.length > 1 ? 's' : '' }}
      </h2>

      <p class="text-gray-600 mb-6">
        The following variables were found across your selected templates.
        Fill them out once, and they'll be applied to all questions.
      </p>

      <!-- List of all distinct variables -->
      <div class="space-y-4 max-h-96 overflow-y-auto">
        <div
          v-for="variable in distinctVariables"
          :key="variable"
          class="variable-input-group"
        >
          <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ variable }}
          </label>

          <!-- Show which templates use this variable -->
          <p class="text-xs text-gray-500 mb-2">
            Used in {{ getTemplateCountForVariable(variable) }} question(s)
          </p>

          <TextInput
            v-model="consolidatedVariables[variable]"
            :placeholder="`Enter value for ${variable}`"
            class="w-full"
          />
        </div>
      </div>

      <!-- Preview Section -->
      <div v-if="previewQuestion" class="mt-6 p-4 bg-gray-50 rounded">
        <h3 class="text-sm font-semibold mb-2">Preview (first question):</h3>
        <p class="text-gray-700">{{ previewQuestionText }}</p>
      </div>

      <!-- Actions -->
      <div class="mt-6 flex justify-end gap-3">
        <SecondaryButton @click="closeConsolidatedVariableModal">
          Cancel
        </SecondaryButton>
        <PrimaryButton
          @click="submitConsolidatedImport"
          :disabled="!allVariablesFilled"
        >
          Import {{ selectedTemplates.length }} Question{{ selectedTemplates.length > 1 ? 's' : '' }}
        </PrimaryButton>
      </div>
    </div>
  </Modal>
</template>
```

**Script Logic:**
```javascript
// Computed: Get all distinct variables across selected templates
const distinctVariables = computed(() => {
  const variableSet = new Set();

  selectedTemplates.value.forEach(template => {
    if (template.variables && Array.isArray(template.variables)) {
      template.variables.forEach(v => variableSet.add(v));
    }
  });

  return Array.from(variableSet).sort();
});

// Reactive: Store consolidated variable values
const consolidatedVariables = ref({});

// Computed: Check if all variables are filled
const allVariablesFilled = computed(() => {
  return distinctVariables.value.every(variable => {
    return consolidatedVariables.value[variable] &&
           consolidatedVariables.value[variable].trim() !== '';
  });
});

// Computed: Preview with variables replaced
const previewQuestionText = computed(() => {
  if (!previewQuestion.value) return '';

  let text = previewQuestion.value.question_text;

  Object.keys(consolidatedVariables.value).forEach(variable => {
    const value = consolidatedVariables.value[variable];
    if (value) {
      text = text.replace(new RegExp(`{${variable}}`, 'g'), value);
    }
  });

  return text;
});

// Helper: Count how many templates use a variable
const getTemplateCountForVariable = (variable) => {
  return selectedTemplates.value.filter(template => {
    return template.variables && template.variables.includes(variable);
  }).length;
};

// Method: Submit import with consolidated variables
const submitConsolidatedImport = async () => {
  const payload = {
    templates: selectedTemplates.value.map(template => ({
      template_id: template.id,
      // Only include variables that this template actually uses
      variable_values: template.variables.reduce((acc, variable) => {
        acc[variable] = consolidatedVariables.value[variable];
        return acc;
      }, {})
    }))
  };

  await router.post(
    route('admin.events.event-questions.bulk-create-from-templates', event.id),
    payload,
    {
      onSuccess: () => {
        closeConsolidatedVariableModal();
        resetSelection();
      }
    }
  );
};
```

---

### Backend Changes

#### Updated Bulk Import Endpoint

**Controller:** `EventQuestionController::bulkCreateFromTemplates`

**BEFORE:**
```php
// Expected format (per-template variable values)
{
  "templates": [
    {
      "template_id": 1,
      "variable_values": {
        "team1": "Lakers",
        "team2": "Celtics"
      }
    },
    {
      "template_id": 2,
      "variable_values": {
        "team1": "Lakers",  // DUPLICATE ENTRY
        "team2": "Celtics"  // DUPLICATE ENTRY
      }
    }
  ]
}
```

**AFTER (No Change in Format):**
```php
// Same format, but frontend sends it with consolidated data
{
  "templates": [
    {
      "template_id": 1,
      "variable_values": {
        "team1": "Lakers",
        "team2": "Celtics"
      }
    },
    {
      "template_id": 2,
      "variable_values": {
        "team1": "Lakers",
        "team2": "Celtics"
      }
    }
  ]
}
```

**Note:** The backend endpoint format stays the same! The change is purely on the frontend where we collect variables once and duplicate the values across templates that need them.

**Current Implementation (stays mostly the same):**

File: `app/Http/Controllers/Admin/EventQuestionController.php`

```php
public function bulkCreateFromTemplates(Request $request, Event $event)
{
    $validated = $request->validate([
        'templates' => 'required|array',
        'templates.*.template_id' => 'required|exists:question_templates,id',
        'templates.*.variable_values' => 'nullable|array',
    ]);

    $nextOrder = $event->eventQuestions()->max('display_order') + 1;

    foreach ($validated['templates'] as $templateData) {
        $template = QuestionTemplate::findOrFail($templateData['template_id']);
        $variableValues = $templateData['variable_values'] ?? [];

        // Substitute variables in question text
        $questionText = $this->substituteVariables(
            $template->question_text,
            $variableValues
        );

        // Substitute variables in options
        $options = null;
        if ($template->default_options) {
            $options = collect($template->default_options)->map(function ($option) use ($variableValues) {
                if (is_array($option) && isset($option['label'])) {
                    $option['label'] = $this->substituteVariables($option['label'], $variableValues);
                    return $option;
                } else {
                    return $this->substituteVariables($option, $variableValues);
                }
            })->toArray();
        }

        // Create event question
        $eventQuestion = EventQuestion::create([
            'event_id' => $event->id,
            'template_id' => $template->id,
            'question_text' => $questionText,
            'question_type' => $template->question_type,
            'options' => $options,
            'points' => $template->default_points,
            'display_order' => $nextOrder++,
        ]);

        // Create group questions for all groups in this event
        foreach ($event->groups as $group) {
            GroupQuestion::create([
                'group_id' => $group->id,
                'event_question_id' => $eventQuestion->id,
                'question_text' => $questionText,
                'question_type' => $template->question_type,
                'options' => $options,
                'points' => $template->default_points,
                'display_order' => $eventQuestion->display_order,
                'is_active' => true,
                'is_custom' => false,
            ]);
        }
    }

    return redirect()->route('admin.events.event-questions.index', $event)
        ->with('success', 'Questions imported successfully.');
}

private function substituteVariables(string $text, array $variables): string
{
    foreach ($variables as $name => $value) {
        $text = str_replace("{" . $name . "}", $value, $text);
    }
    return $text;
}
```

---

## Database Changes

### Migration 1: Remove Category from Events

**File:** `database/migrations/YYYY_MM_DD_HHMMSS_remove_category_from_events.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('category', 100)->nullable();
        });
    }
};
```

---

### Migration 2: Expand Category Field in Question Templates

**File:** `database/migrations/YYYY_MM_DD_HHMMSS_expand_category_in_question_templates.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('question_templates', function (Blueprint $table) {
            // Change from string(100) to string(255) to support comma-separated lists
            $table->string('category', 255)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('question_templates', function (Blueprint $table) {
            // Revert back to original size
            $table->string('category', 100)->change();
        });
    }
};
```

**Note:** This migration is safe even if existing categories are already comma-separated, as long as they're under 255 characters.

---

## Code Changes

### 1. Model Changes

#### Event Model
**File:** `app/Models/Event.php`

**REMOVE:**
```php
// Remove from $fillable
'category',

// Remove method
public function availableTemplates()
{
    return QuestionTemplate::where('category', $this->category)
        ->orderBy('display_order')
        ->get();
}
```

**NO OTHER CHANGES NEEDED** - Event model simplified!

---

#### QuestionTemplate Model
**File:** `app/Models/QuestionTemplate.php`

**ADD:**
```php
/**
 * Get categories as array
 */
public function getCategoriesAttribute(): array
{
    return array_map('trim', explode(',', $this->category));
}

/**
 * Check if template has a specific category
 */
public function hasCategory(string $search): bool
{
    $categories = $this->categories;
    return in_array(strtolower(trim($search)), array_map('strtolower', $categories));
}

/**
 * Scope: Filter by category
 */
public function scopeWithCategory($query, string $category)
{
    return $query->where('category', 'LIKE', "%{$category}%");
}
```

---

### 2. Controller Changes

#### EventQuestionController
**File:** `app/Http/Controllers/Admin/EventQuestionController.php`

**MODIFY `create` method:**

**BEFORE:**
```php
public function create(Event $event)
{
    $this->authorize('create', EventQuestion::class);

    $availableTemplates = $event->availableTemplates();

    return Inertia::render('Admin/EventQuestions/Create', [
        'event' => $event,
        'availableTemplates' => $availableTemplates,
        'currentQuestions' => $event->eventQuestions,
        'nextOrder' => $event->eventQuestions()->max('display_order') + 1,
    ]);
}
```

**AFTER:**
```php
public function create(Event $event)
{
    $this->authorize('create', EventQuestion::class);

    return Inertia::render('Admin/EventQuestions/Create', [
        'event' => $event,
        'currentQuestions' => $event->eventQuestions,
        'nextOrder' => $event->eventQuestions()->max('display_order') + 1,
    ]);
}
```

**ADD new method:**
```php
/**
 * Search for templates by category
 */
public function searchTemplates(Request $request, Event $event)
{
    $this->authorize('create', EventQuestion::class);

    $validated = $request->validate([
        'category' => 'required|string|max:100',
    ]);

    $searchCategory = trim($validated['category']);

    // Find templates where category contains the search term
    $templates = QuestionTemplate::where('category', 'LIKE', "%{$searchCategory}%")
        ->orderBy('display_order')
        ->get();

    // Get IDs of templates already imported to this event
    $importedTemplateIds = EventQuestion::where('event_id', $event->id)
        ->whereNotNull('template_id')
        ->pluck('template_id')
        ->toArray();

    // Filter out already imported templates
    $filteredTemplates = $templates->filter(function($template) use ($importedTemplateIds) {
        return !in_array($template->id, $importedTemplateIds);
    })->values();

    return response()->json([
        'templates' => $filteredTemplates,
        'total' => $filteredTemplates->count(),
        'search_term' => $searchCategory,
    ]);
}
```

**MODIFY `bulkCreateFromTemplates` (no changes needed, just documentation):**

The method already handles the correct format. Frontend will send the same structure, just with consolidated variable values duplicated across templates.

---

### 3. Route Changes

#### Add Template Search Route
**File:** `routes/web.php`

**ADD** (within the admin event-questions group):
```php
Route::get('/events/{event}/event-questions/search-templates', [EventQuestionController::class, 'searchTemplates'])
    ->name('admin.events.event-questions.search-templates');
```

**Full context:**
```php
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // ... other routes ...

    Route::get('/events/{event}/event-questions', [EventQuestionController::class, 'index'])
        ->name('events.event-questions.index');
    Route::get('/events/{event}/event-questions/create', [EventQuestionController::class, 'create'])
        ->name('events.event-questions.create');
    Route::get('/events/{event}/event-questions/search-templates', [EventQuestionController::class, 'searchTemplates'])
        ->name('admin.events.event-questions.search-templates');  // NEW
    Route::post('/events/{event}/event-questions', [EventQuestionController::class, 'store'])
        ->name('events.event-questions.store');
    // ... rest of routes ...
});
```

---

### 4. Frontend Changes

#### EventQuestions/Create.vue
**File:** `resources/js/Pages/Admin/EventQuestions/Create.vue`

**Major Changes:**

1. **Remove `availableTemplates` prop dependency**
2. **Add category search state and methods**
3. **Replace individual variable modals with consolidated modal**
4. **Add template filtering to hide already imported**

**Key State Variables:**
```javascript
// Category search
const categorySearch = ref('');
const lastSearchTerm = ref('');
const searchPerformed = ref(false);
const filteredTemplates = ref([]);

// Template selection
const selectedTemplates = ref([]);

// Consolidated variables
const showConsolidatedVariableModal = ref(false);
const consolidatedVariables = ref({});

// Already imported tracking
const alreadyImportedTemplateIds = computed(() => {
  return props.currentQuestions
    .filter(q => q.template_id)
    .map(q => q.template_id);
});
```

**Key Methods:**
```javascript
// Search for templates by category
const findTemplatesByCategory = async () => {
  if (!categorySearch.value.trim()) {
    return;
  }

  try {
    const response = await axios.get(
      route('admin.events.event-questions.search-templates', props.event.id),
      {
        params: { category: categorySearch.value.trim() }
      }
    );

    filteredTemplates.value = response.data.templates;
    lastSearchTerm.value = response.data.search_term;
    searchPerformed.value = true;
  } catch (error) {
    console.error('Error searching templates:', error);
  }
};

// Toggle template selection
const toggleTemplateSelection = (template) => {
  const index = selectedTemplates.value.findIndex(t => t.id === template.id);
  if (index > -1) {
    selectedTemplates.value.splice(index, 1);
  } else {
    selectedTemplates.value.push(template);
  }
};

// Open consolidated variable modal
const openConsolidatedVariableModal = () => {
  // Initialize consolidated variables
  consolidatedVariables.value = {};
  distinctVariables.value.forEach(variable => {
    consolidatedVariables.value[variable] = '';
  });

  showConsolidatedVariableModal.value = true;
};

// Computed: distinct variables across all selected templates
const distinctVariables = computed(() => {
  const variableSet = new Set();

  selectedTemplates.value.forEach(template => {
    if (template.variables && Array.isArray(template.variables)) {
      template.variables.forEach(v => variableSet.add(v));
    }
  });

  return Array.from(variableSet).sort();
});

// Import selected templates with consolidated variables
const bulkCreateSelected = () => {
  if (selectedTemplates.value.length === 0) {
    return;
  }

  // Check if any selected templates have variables
  const hasVariables = selectedTemplates.value.some(t =>
    t.variables && t.variables.length > 0
  );

  if (hasVariables) {
    openConsolidatedVariableModal();
  } else {
    // No variables, import directly
    submitBulkImport([]);
  }
};

// Submit bulk import
const submitBulkImport = async () => {
  const payload = {
    templates: selectedTemplates.value.map(template => ({
      template_id: template.id,
      variable_values: template.variables
        ? template.variables.reduce((acc, variable) => {
            acc[variable] = consolidatedVariables.value[variable] || '';
            return acc;
          }, {})
        : {}
    }))
  };

  await router.post(
    route('admin.events.event-questions.bulk-create-from-templates', props.event.id),
    payload,
    {
      onSuccess: () => {
        showConsolidatedVariableModal.value = false;
        selectedTemplates.value = [];
        categorySearch.value = '';
        searchPerformed.value = false;
        filteredTemplates.value = [];
      }
    }
  );
};
```

---

#### QuestionTemplates/Create.vue & Edit.vue
**Files:**
- `resources/js/Pages/Admin/QuestionTemplates/Create.vue`
- `resources/js/Pages/Admin/QuestionTemplates/Edit.vue`

**Update category input with helper text:**

```vue
<div class="mb-4">
  <label for="category" class="block text-sm font-medium text-gray-700 mb-1">
    Categories
  </label>
  <p class="text-sm text-gray-600 mb-2">
    Enter one or more categories separated by commas (e.g., football,nfl,sports)
  </p>
  <TextInput
    id="category"
    v-model="form.category"
    type="text"
    class="mt-1 block w-full"
    placeholder="e.g., football,nfl,sports"
    required
  />
  <InputError :message="form.errors.category" class="mt-2" />

  <!-- Category tags preview -->
  <div v-if="categoryTags.length > 0" class="mt-2 flex gap-2 flex-wrap">
    <span
      v-for="tag in categoryTags"
      :key="tag"
      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
    >
      {{ tag }}
    </span>
  </div>
</div>
```

```javascript
const categoryTags = computed(() => {
  if (!form.category) return [];
  return form.category
    .split(',')
    .map(c => c.trim())
    .filter(c => c.length > 0);
});
```

---

### 5. Event Form Changes

#### Events/Create.vue & Edit.vue
**Files:**
- `resources/js/Pages/Admin/Events/Create.vue`
- `resources/js/Pages/Admin/Events/Edit.vue`

**REMOVE category field entirely:**

```vue
<!-- DELETE THIS SECTION -->
<div class="mb-4">
  <label for="category" class="block text-sm font-medium text-gray-700">
    Category
  </label>
  <TextInput
    id="category"
    v-model="form.category"
    type="text"
    class="mt-1 block w-full"
    required
  />
  <InputError :message="form.errors.category" class="mt-2" />
</div>
```

**And remove from form data:**
```javascript
const form = useForm({
  name: props.event?.name || '',
  description: props.event?.description || '',
  // category: props.event?.category || '',  // DELETE THIS LINE
  event_date: props.event?.event_date || '',
  lock_date: props.event?.lock_date || '',
  status: props.event?.status || 'draft',
});
```

---

## Testing Requirements

### Unit Tests

#### 1. QuestionTemplate Model Tests
**File:** `tests/Unit/Models/QuestionTemplateTest.php` (NEW)

```php
<?php

namespace Tests\Unit\Models;

use App\Models\QuestionTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionTemplateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_parse_single_category()
    {
        $template = QuestionTemplate::factory()->create([
            'category' => 'football'
        ]);

        $categories = $template->categories;

        $this->assertCount(1, $categories);
        $this->assertEquals(['football'], $categories);
    }

    /** @test */
    public function it_can_parse_multiple_categories()
    {
        $template = QuestionTemplate::factory()->create([
            'category' => 'football,nfl,sports'
        ]);

        $categories = $template->categories;

        $this->assertCount(3, $categories);
        $this->assertEquals(['football', 'nfl', 'sports'], $categories);
    }

    /** @test */
    public function it_trims_whitespace_from_categories()
    {
        $template = QuestionTemplate::factory()->create([
            'category' => 'football, nfl , sports'
        ]);

        $categories = $template->categories;

        $this->assertEquals(['football', 'nfl', 'sports'], $categories);
    }

    /** @test */
    public function has_category_returns_true_for_matching_category()
    {
        $template = QuestionTemplate::factory()->create([
            'category' => 'football,nfl,sports'
        ]);

        $this->assertTrue($template->hasCategory('football'));
        $this->assertTrue($template->hasCategory('nfl'));
        $this->assertTrue($template->hasCategory('sports'));
    }

    /** @test */
    public function has_category_returns_false_for_non_matching_category()
    {
        $template = QuestionTemplate::factory()->create([
            'category' => 'football,nfl'
        ]);

        $this->assertFalse($template->hasCategory('basketball'));
        $this->assertFalse($template->hasCategory('mlb'));
    }

    /** @test */
    public function has_category_is_case_insensitive()
    {
        $template = QuestionTemplate::factory()->create([
            'category' => 'football,NFL,Sports'
        ]);

        $this->assertTrue($template->hasCategory('FOOTBALL'));
        $this->assertTrue($template->hasCategory('nfl'));
        $this->assertTrue($template->hasCategory('sports'));
    }

    /** @test */
    public function with_category_scope_finds_matching_templates()
    {
        QuestionTemplate::factory()->create([
            'category' => 'football,nfl'
        ]);
        QuestionTemplate::factory()->create([
            'category' => 'basketball,nba'
        ]);
        QuestionTemplate::factory()->create([
            'category' => 'football,college'
        ]);

        $results = QuestionTemplate::withCategory('football')->get();

        $this->assertCount(2, $results);
    }
}
```

---

#### 2. Event Model Tests (Update)
**File:** `tests/Unit/Models/EventTest.php`

**REMOVE these tests:**
```php
/** @test */
public function available_templates_returns_templates_matching_event_category()
{
    // DELETE THIS TEST
}

/** @test */
public function available_templates_are_ordered_by_display_order()
{
    // DELETE THIS TEST
}
```

**ADD test to verify category field removed:**
```php
/** @test */
public function it_does_not_have_category_field()
{
    $event = Event::factory()->create();

    $this->assertArrayNotHasKey('category', $event->toArray());
}
```

---

#### 3. Variable Substitution Tests (Update)
**File:** `tests/Unit/Controllers/EventQuestionControllerTest.php` (NEW)

```php
<?php

namespace Tests\Unit\Controllers;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EventQuestionControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function substitute_variables_replaces_single_variable()
    {
        $controller = new \App\Http\Controllers\Admin\EventQuestionController();

        $text = "Who will win {team}?";
        $variables = ['team' => 'Lakers'];

        $result = $this->invokePrivateMethod($controller, 'substituteVariables', [$text, $variables]);

        $this->assertEquals("Who will win Lakers?", $result);
    }

    /** @test */
    public function substitute_variables_replaces_multiple_variables()
    {
        $controller = new \App\Http\Controllers\Admin\EventQuestionController();

        $text = "Will {team1} beat {team2} in {year}?";
        $variables = [
            'team1' => 'Lakers',
            'team2' => 'Celtics',
            'year' => '2025'
        ];

        $result = $this->invokePrivateMethod($controller, 'substituteVariables', [$text, $variables]);

        $this->assertEquals("Will Lakers beat Celtics in 2025?", $result);
    }

    /** @test */
    public function substitute_variables_replaces_same_variable_multiple_times()
    {
        $controller = new \App\Http\Controllers\Admin\EventQuestionController();

        $text = "{team} will play {team} in the championship";
        $variables = ['team' => 'Lakers'];

        $result = $this->invokePrivateMethod($controller, 'substituteVariables', [$text, $variables]);

        $this->assertEquals("Lakers will play Lakers in the championship", $result);
    }

    /**
     * Helper to invoke private methods for testing
     */
    private function invokePrivateMethod($object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }
}
```

---

### Feature Tests

#### 1. Template Search Feature Test
**File:** `tests/Feature/TemplateSearchTest.php` (NEW)

```php
<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventQuestion;
use App\Models\QuestionTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TemplateSearchTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_search_templates_by_category()
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();

        QuestionTemplate::factory()->create([
            'category' => 'football,nfl',
            'title' => 'Football Question'
        ]);
        QuestionTemplate::factory()->create([
            'category' => 'basketball,nba',
            'title' => 'Basketball Question'
        ]);

        $response = $this->actingAs($admin)->getJson(
            route('admin.events.event-questions.search-templates', $event),
            ['category' => 'football']
        );

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'templates');
        $response->assertJsonPath('templates.0.title', 'Football Question');
    }

    /** @test */
    public function search_excludes_already_imported_templates()
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();

        $template1 = QuestionTemplate::factory()->create([
            'category' => 'football'
        ]);
        $template2 = QuestionTemplate::factory()->create([
            'category' => 'football'
        ]);

        // Import template1 to the event
        EventQuestion::factory()->create([
            'event_id' => $event->id,
            'template_id' => $template1->id
        ]);

        $response = $this->actingAs($admin)->getJson(
            route('admin.events.event-questions.search-templates', $event),
            ['category' => 'football']
        );

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'templates');
        $response->assertJsonPath('templates.0.id', $template2->id);
    }

    /** @test */
    public function search_returns_empty_for_non_matching_category()
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();

        QuestionTemplate::factory()->create(['category' => 'football']);

        $response = $this->actingAs($admin)->getJson(
            route('admin.events.event-questions.search-templates', $event),
            ['category' => 'baseball']
        );

        $response->assertStatus(200);
        $response->assertJsonCount(0, 'templates');
    }

    /** @test */
    public function non_admin_cannot_search_templates()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create();

        $response = $this->actingAs($user)->getJson(
            route('admin.events.event-questions.search-templates', $event),
            ['category' => 'football']
        );

        $response->assertStatus(403);
    }

    /** @test */
    public function search_requires_category_parameter()
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();

        $response = $this->actingAs($admin)->getJson(
            route('admin.events.event-questions.search-templates', $event)
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('category');
    }
}
```

---

#### 2. Bulk Import with Consolidated Variables Test
**File:** `tests/Feature/ConsolidatedVariableImportTest.php` (NEW)

```php
<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventQuestion;
use App\Models\Group;
use App\Models\GroupQuestion;
use App\Models\QuestionTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsolidatedVariableImportTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_import_multiple_templates_with_shared_variables()
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();
        $group = Group::factory()->create(['event_id' => $event->id]);

        $template1 = QuestionTemplate::factory()->create([
            'question_text' => 'Who will win {team1} vs {team2}?',
            'variables' => ['team1', 'team2'],
            'question_type' => 'text'
        ]);

        $template2 = QuestionTemplate::factory()->create([
            'question_text' => 'Will {team1} score over 100 points?',
            'variables' => ['team1'],
            'question_type' => 'yes_no'
        ]);

        $response = $this->actingAs($admin)->post(
            route('admin.events.event-questions.bulk-create-from-templates', $event),
            [
                'templates' => [
                    [
                        'template_id' => $template1->id,
                        'variable_values' => [
                            'team1' => 'Lakers',
                            'team2' => 'Celtics'
                        ]
                    ],
                    [
                        'template_id' => $template2->id,
                        'variable_values' => [
                            'team1' => 'Lakers'
                        ]
                    ]
                ]
            ]
        );

        $response->assertRedirect(route('admin.events.event-questions.index', $event));

        $this->assertDatabaseHas('event_questions', [
            'event_id' => $event->id,
            'question_text' => 'Who will win Lakers vs Celtics?'
        ]);

        $this->assertDatabaseHas('event_questions', [
            'event_id' => $event->id,
            'question_text' => 'Will Lakers score over 100 points?'
        ]);

        // Verify group questions were created
        $this->assertEquals(2, GroupQuestion::where('group_id', $group->id)->count());
    }

    /** @test */
    public function variables_are_replaced_in_options()
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();

        $template = QuestionTemplate::factory()->create([
            'question_text' => 'Who will win?',
            'variables' => ['team1', 'team2'],
            'question_type' => 'multiple_choice',
            'default_options' => [
                ['label' => '{team1}', 'points' => 0],
                ['label' => '{team2}', 'points' => 0],
                ['label' => 'Draw', 'points' => 0]
            ]
        ]);

        $this->actingAs($admin)->post(
            route('admin.events.event-questions.bulk-create-from-templates', $event),
            [
                'templates' => [
                    [
                        'template_id' => $template->id,
                        'variable_values' => [
                            'team1' => 'Lakers',
                            'team2' => 'Celtics'
                        ]
                    ]
                ]
            ]
        );

        $question = EventQuestion::where('event_id', $event->id)->first();

        $this->assertEquals('Lakers', $question->options[0]['label']);
        $this->assertEquals('Celtics', $question->options[1]['label']);
        $this->assertEquals('Draw', $question->options[2]['label']);
    }

    /** @test */
    public function can_import_templates_without_variables()
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();

        $template = QuestionTemplate::factory()->create([
            'question_text' => 'Will it rain tomorrow?',
            'variables' => null,
            'question_type' => 'yes_no'
        ]);

        $response = $this->actingAs($admin)->post(
            route('admin.events.event-questions.bulk-create-from-templates', $event),
            [
                'templates' => [
                    [
                        'template_id' => $template->id,
                        'variable_values' => []
                    ]
                ]
            ]
        );

        $response->assertRedirect();

        $this->assertDatabaseHas('event_questions', [
            'event_id' => $event->id,
            'question_text' => 'Will it rain tomorrow?'
        ]);
    }
}
```

---

#### 3. Update Existing EventQuestionController Tests
**File:** `tests/Feature/EventQuestionControllerTest.php` (if exists, update)

**Tests to ADD/UPDATE:**

```php
/** @test */
public function create_page_does_not_include_available_templates()
{
    $admin = User::factory()->admin()->create();
    $event = Event::factory()->create();

    $response = $this->actingAs($admin)->get(
        route('admin.events.event-questions.create', $event)
    );

    $response->assertStatus(200);
    // Should NOT have availableTemplates prop
    $response->assertDontSee('availableTemplates');
}
```

---

### Integration Tests

#### Event Creation Without Category
**File:** `tests/Feature/EventManagementTest.php` (update existing or create new)

```php
/** @test */
public function can_create_event_without_category()
{
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post(route('admin.events.store'), [
        'name' => 'Super Bowl 2025',
        'description' => 'Championship game',
        'event_date' => now()->addMonth()->toDateString(),
        'lock_date' => now()->addWeek()->toDateString(),
        'status' => 'draft',
        // Note: No category field
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('events', [
        'name' => 'Super Bowl 2025'
    ]);

    $event = Event::where('name', 'Super Bowl 2025')->first();
    $this->assertNull($event->category ?? null);
}

/** @test */
public function category_field_is_not_in_event_fillable()
{
    $event = new Event();

    $this->assertNotContains('category', $event->getFillable());
}
```

---

### Validation Tests

#### Category Field Validation in Templates
**File:** `tests/Feature/QuestionTemplateValidationTest.php` (NEW)

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionTemplateValidationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function category_field_can_be_comma_separated()
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.question-templates.store'), [
            'title' => 'Test Template',
            'question_text' => 'Test question?',
            'question_type' => 'yes_no',
            'category' => 'football,nfl,sports',
            'default_points' => 10,
            'display_order' => 1
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('question_templates', [
            'category' => 'football,nfl,sports'
        ]);
    }

    /** @test */
    public function category_field_can_exceed_100_characters()
    {
        $admin = User::factory()->admin()->create();

        $longCategory = str_repeat('category,', 30); // ~240 characters

        $response = $this->actingAs($admin)->post(route('admin.question-templates.store'), [
            'title' => 'Test Template',
            'question_text' => 'Test question?',
            'question_type' => 'yes_no',
            'category' => $longCategory,
            'default_points' => 10,
            'display_order' => 1
        ]);

        $response->assertRedirect(); // Should not fail
    }
}
```

---

## Migration Path

### Step-by-Step Migration Plan

#### Phase 1: Database Preparation
1. **Backup current database**
2. **Run migration to expand QuestionTemplate.category to 255 chars**
   ```bash
   php artisan make:migration expand_category_in_question_templates
   php artisan migrate
   ```

#### Phase 2: Data Migration (Optional)
If you want to preserve existing event categories as template search terms:

```php
// Optional migration to tag existing templates with event categories
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // This is optional - only if you want to preserve event-category relationships
        // by adding event categories as additional tags to templates

        $events = DB::table('events')->select('id', 'category')->get();

        foreach ($events as $event) {
            // Find templates that match this event's category
            $templates = DB::table('question_templates')
                ->where('category', $event->category)
                ->get();

            // Optionally add event name as additional category tag
            foreach ($templates as $template) {
                // Example: if event is "Super Bowl 2025" and category is "football"
                // Could add "superbowl" as additional tag
                // This is just an example - adjust based on your needs
            }
        }
    }
};
```

#### Phase 3: Remove Event Category
1. **Remove category from Event model**
2. **Run migration to drop events.category column**
   ```bash
   php artisan make:migration remove_category_from_events
   php artisan migrate
   ```

#### Phase 4: Update Frontend
1. **Update EventQuestions/Create.vue** with new search UI
2. **Remove category field from Events/Create.vue and Edit.vue**
3. **Update QuestionTemplates/Create.vue** with multi-category input

#### Phase 5: Update Backend
1. **Remove Event::availableTemplates() method**
2. **Add EventQuestionController::searchTemplates() method**
3. **Add new route for template search**
4. **Add QuestionTemplate category helper methods**

#### Phase 6: Testing
1. **Run all unit tests**
2. **Run all feature tests**
3. **Manual QA testing**

---

### Rollback Plan

If issues arise, rollback in reverse order:

1. **Restore Event category field**
   ```bash
   php artisan migrate:rollback --step=1
   ```

2. **Restore old frontend components from git**
   ```bash
   git checkout HEAD -- resources/js/Pages/Admin/EventQuestions/Create.vue
   git checkout HEAD -- resources/js/Pages/Admin/Events/
   ```

3. **Restore Event::availableTemplates() method**

4. **Restore database from backup** (if major issues)

---

## User Experience Flow

### Before (Current)

```
1. Admin creates Event
   └─ Must set category: "football"

2. Admin navigates to Manage Questions
   └─ Sees ONLY templates with category="football"
   └─ Cannot see templates from other categories

3. Admin selects 20 templates (all have {AFC Team} variable)
   └─ Clicks "Bulk Add"

4. Modal appears for Template 1
   └─ Enter {AFC Team}: "KC Chiefs"
   └─ Click "Next"

5. Modal appears for Template 2
   └─ Enter {AFC Team}: "KC Chiefs" AGAIN
   └─ Click "Next"

6. Repeat 18 more times...
   └─ Total time: ~5-10 minutes of repetitive data entry

7. Questions imported
```

### After (New)

```
1. Admin creates Event
   └─ No category required

2. Admin navigates to Manage Questions
   └─ Sees search box

3. Admin types "football" and clicks "Find"
   └─ Sees all templates tagged with "football"
   └─ Selects desired templates
   └─ Clicks "Import Selected"

4. Admin types "nfl" and clicks "Find"
   └─ Sees all templates tagged with "nfl"
   └─ Templates already imported are hidden
   └─ Selects more templates
   └─ Clicks "Import Selected"

5. Selected 40 templates total:
   - 20 use {AFC Team}
   - 18 use {NFC Team}
   - 35 use {Year}
   - 10 use {Stadium}

6. Consolidated Variable Modal appears
   └─ Shows ALL 4 distinct variables in one screen:
       • AFC Team: "KC Chiefs"
       • NFC Team: "San Francisco 49ers"
       • Year: "2025"
       • Stadium: "Allegiant Stadium"
   └─ Click "Import 40 Questions"

7. All 40 questions imported instantly
   └─ Variables replaced across all questions
   └─ Total time: ~2 minutes
```

---

## Summary

### Key Benefits

1. **Flexibility**: Events not locked to single category
2. **Speed**: Variable entry time reduced by 90%+
3. **Organization**: Questions can have multiple category tags
4. **Discovery**: Dynamic search enables better template discovery
5. **Simplicity**: Cleaner data model (no unused event category)

### Breaking Changes

1. **Database**: Events table drops `category` column
2. **API**: `Event::availableTemplates()` method removed
3. **Frontend**: EventQuestions/Create.vue major refactor

### Migration Effort

- **Database migrations**: 2 migrations (low risk)
- **Backend changes**: ~200 lines of code (medium complexity)
- **Frontend changes**: ~500 lines of code (medium complexity)
- **Testing**: ~15 new tests + updates to existing tests
- **Estimated time**: 2-3 days for development + testing

---

## Appendix: File Reference

### Files to Modify

**Models:**
- `app/Models/Event.php` - Remove category field and availableTemplates()
- `app/Models/QuestionTemplate.php` - Add category helpers

**Controllers:**
- `app/Http/Controllers/Admin/EventQuestionController.php` - Add searchTemplates()

**Migrations:**
- `database/migrations/YYYY_MM_DD_remove_category_from_events.php` (new)
- `database/migrations/YYYY_MM_DD_expand_category_in_question_templates.php` (new)

**Routes:**
- `routes/web.php` - Add template search route

**Frontend:**
- `resources/js/Pages/Admin/EventQuestions/Create.vue` - Major refactor
- `resources/js/Pages/Admin/Events/Create.vue` - Remove category field
- `resources/js/Pages/Admin/Events/Edit.vue` - Remove category field
- `resources/js/Pages/Admin/QuestionTemplates/Create.vue` - Update category input
- `resources/js/Pages/Admin/QuestionTemplates/Edit.vue` - Update category input

**Tests:**
- `tests/Unit/Models/EventTest.php` - Update tests
- `tests/Unit/Models/QuestionTemplateTest.php` - Add new tests
- `tests/Feature/TemplateSearchTest.php` - Add new tests
- `tests/Feature/ConsolidatedVariableImportTest.php` - Add new tests
- `tests/Feature/EventManagementTest.php` - Update tests

---

**End of Specification**
