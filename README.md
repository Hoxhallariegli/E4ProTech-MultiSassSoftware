# E4ProTech StarterKit — Laravel 12 + Livewire + Flutter Full-Stack

A production-oriented Laravel StarterKit built around **DDD-inspired architecture, Livewire 3, REST APIs, Reverb realtime events, Flutter BLoC/Cubit, localization, permissions, and generator-driven CRUD scaffolding**.

The central idea is simple:

> Define a module once with `php artisan new:view` and generate the Laravel + Livewire + API + realtime + Flutter ecosystem from the same field definition.

---

## What This StarterKit Is

E4ProTech StarterKit is designed to remove repetitive CRUD work without sacrificing structure.

A generated module can include:

- Laravel Model
- Database Migration
- DTO
- Create / Update / Delete Actions
- List Query
- Livewire CRUD
- Blade views
- Permissions
- Admin navigation
- API Controller
- API Resource
- API routes
- Foreign-key lookup endpoints
- Observer
- Broadcast Event
- Reverb channel integration
- Flutter BLoC/Cubit module
- Flutter data/repository layer
- Flutter UI/pages/widgets
- Image upload integration
- Module localization for English and Albanian
- Optional Firebase notification infrastructure

The generator is intentionally **generator-first**: fixes and architectural rules belong in `new:view`, so newly generated modules stay consistent.

---

# 1. Architecture

## 1.1 Laravel / Web

```text
Livewire / API
      ¦
      ?
   DTO
      ¦
      ?
   Action
      ¦
      ?
    Model
      ¦
      ?
   Database
```

The same domain actions are reused by the web and mobile sides so that business mutations do not diverge between interfaces.

## 1.2 Realtime

```text
Database change
      ¦
      ?
   Observer
      ¦
      ?
Broadcast Event
      ¦
      ?
   Laravel Reverb
      +--------------? Flutter realtime UI
      ¦
      +--------------? Livewire realtime refresh
```

**Reverb is the realtime mechanism.**

## 1.3 Firebase

Firebase is separate from the realtime layer.

```text
Successful DB operation
      ¦
      +--------------? Reverb ? realtime UI
      ¦
      +--------------? Firebase ? push notification
```

Firebase is intended for notifications, not as the primary realtime synchronization layer.

---

# 2. The Main Generator

The core command is:

```bash
php artisan new:view {ModelName}
```

For the complete Laravel + Flutter stack:

```bash
php artisan new:view {ModelName} --api
```

Optional Firebase generation:

```bash
php artisan new:view {ModelName} --api --firebase
```

### Options

| Option | Purpose |
|---|---|
| `--api` | Generate the full API + Flutter + realtime ecosystem |
| `--firebase` | Generate Firebase notification infrastructure |

The current generator treats `--api` as a **full-stack module**, not as an API-only mode.

---

# 3. Supported Field Types

The generator supports:

| Field | Generated behavior |
|---|---|
| `string` | Text input |
| `text` | Large textarea |
| `integer` | Integer input |
| `bigInteger` | Big integer input |
| `decimal` | Decimal input with 2-decimal UI |
| `qty` / `quantity` | Decimal semantics |
| `price` / `amount` / `cost` / `unit_price` | Decimal semantics |
| `boolean` | Checkbox / switch |
| `image` | Image picker + upload |
| `date` | Date picker |
| `datetime` | Date + time picker |
| `foreignId` | Relationship + searchable lookup |
| `enum` | Selection input |

Semantic field names intentionally override the numeric menu selection for known conventions such as `qty`, `price`, `priority`, boolean prefixes, date fields, datetime fields, and image fields.

---

# 4. Example Module

A strong regression module is:

```text
TestModule
```

with:

```text
name
description
qty
price
is_active
due_date
event_at
user_id
priority
image
cover_photo
```

Run:

```bash
php artisan new:view TestModule --api
```

This exercises text, textarea, decimal, boolean, date, datetime, foreign-key, enum, and image generation in one module.

---

# 5. Generated Laravel Structure

A generated full-stack module follows this general structure:

```text
app/
+-- Domain/
¦   +-- TestModule/
¦       +-- Actions/
¦       ¦   +-- CreateTestModuleAction.php
¦       ¦   +-- UpdateTestModuleAction.php
¦       ¦   +-- DeleteTestModuleAction.php
¦       +-- DTOs/
¦       ¦   +-- TestModuleDTO.php
¦       +-- Queries/
¦       ¦   +-- TestModuleListQuery.php
¦       +-- Events/
¦
+-- Http/
¦   +-- Controllers/
¦   ¦   +-- Api/
¦   ¦       +-- Mobile/
¦   ¦           +-- TestModuleController.php
¦   +-- Resources/
¦       +-- Mobile/
¦           +-- TestModuleResource.php
¦
+-- Livewire/
¦   +-- Admin/
¦       +-- TestModules/
¦           +-- TestModules.php
¦           +-- Create.php
¦           +-- Edit.php
¦           +-- Row.php
¦           +-- QuickCreate.php
¦
+-- Models/
¦   +-- TestModule.php
¦
+-- Observers/
¦   +-- TestModuleObserver.php
¦
+-- Events/
    +-- TestModuleChanged.php
```

Additional generated resources include:

```text
database/migrations/
resources/views/livewire/admin/test-modules/
routes/admin/test-modules.php
```

---

# 6. API Layer

With `--api`, the generator creates the mobile API layer together with the Flutter module.

Typical endpoints:

```text
GET    /api/mobile/test-modules
POST   /api/mobile/test-modules
GET    /api/mobile/test-modules/{id}
PUT    /api/mobile/test-modules/{id}
PATCH  /api/mobile/test-modules/{id}
DELETE /api/mobile/test-modules/{id}
```

The mobile layer reuses the generated DTOs, Actions and Query logic.

That gives one mutation path:

```text
Flutter
   ¦
   ?
API Controller
   ¦
   ?
Validation
   ¦
   ?
DTO
   ¦
   ?
Action
   ¦
   ?
Model
   ¦
   ?
Database
```

---

# 7. API Success / Failure Contract

The mobile UI should always distinguish:

### Success

```text
Flutter request
     ?
HTTP success
     ?
Database mutation succeeds
     ?
Flutter receives ACK
     ?
UI shows success
```

Example:

```text
TestModule u shtua me sukses.
TestModule u përditësua me sukses.
TestModule u fshi me sukses.
```

### Failure

```text
Flutter request
     ?
Validation / server / network failure
     ?
No success state
     ?
Flutter shows error
```

Example:

```text
Nuk u shtua: validation failed.
Nuk u fshi: server error.
Nuk ka përgjigje nga API.
```

Firebase success notifications should only be triggered after a successful operation.

---

# 8. Realtime with Reverb

The generated Observer listens to model lifecycle changes and the generated broadcast event sends the realtime message.

Expected flow when Flutter creates a record:

```text
Flutter
  ?
POST API
  ?
Laravel
  ?
Create Action
  ?
Database
  ?
Observer
  ?
TestModuleChanged
  ?
Reverb
  +--? Flutter list refresh
  +--? Livewire list refresh
```

The reverse works too:

```text
Livewire
  ?
Action
  ?
Database
  ?
Observer
  ?
Reverb
  +--? Flutter refreshes
```

This is the definition of realtime synchronization for `--api`.

---

# 9. Image Upload Architecture

Image fields use the shared `ImageUploadService`.

The intended storage layout is:

```text
public/
+-- uploads/
    +-- test-modules/
        +-- generated-file.webp
```

The database stores the relative public path:

```text
uploads/test-modules/generated-file.webp
```

The service handles image processing and can return a public path suitable for the web and API resource layer.

The generator should use this service consistently instead of maintaining separate upload implementations for Livewire and API.

---

# 10. Flutter Structure

The generated Flutter module follows the project's feature-oriented structure:

```text
mobile-gateway/
+-- lib/
    +-- modules/
    ¦   +-- dashboard/
    ¦       +-- test_module/
    ¦           +-- data/
    ¦           +-- presentation/
    ¦               +-- cubit/
    ¦               +-- pages/
    ¦               +-- widgets/
    ¦
    +-- core/
        +-- realtime/
        +-- widgets/
        +-- ...
```

The generated mobile side is intended to be a real application module, not a second-class API client.

---

# 11. Localization

The Laravel generator already creates module translation files for `en` and `sq`.

The Flutter generator follows the same principle: generated module labels and UI strings should be ready for localization rather than hard-coded throughout widgets.

The rule is:

```text
One generated field definition
        ¦
        +--? Laravel translation key
        ¦
        +--? Flutter localization key
```

This keeps module generation consistent across web and mobile.

---

# 12. Relationship Support

For a `foreignId`, the wizard asks for:

```text
Constrained table
Display field
```

The display field supports dot notation.

Example:

```text
employee.name
```

The generated Query layer can eager-load the required relationship path, while the UI resolves nested relation labels safely.

This also supports nested create flows such as:

```text
JobCard
  +-- Vehicle
       +-- Model
            +-- Brand
```

The generated Livewire components can propagate newly-created related IDs back to the parent form.

---

# 13. Smart UI

Generated modules include reusable premium UI patterns such as:

- Searchable relationship dropdowns
- Sortable table headers
- Pagination
- Smart filter panels
- Create-on-the-fly related records
- Nested modal creation
- Date and datetime controls
- Decimal inputs
- Image preview/upload components
- Dark mode support

The goal is to keep generated modules visually consistent rather than producing raw framework defaults.

---

# 14. Permissions and Navigation

A generated module registers CRUD permissions such as:

```text
view_test_modules
add_test_modules
edit_test_modules
delete_test_modules
```

The generator also adds the admin navigation entry.

This keeps access control part of scaffolding instead of an afterthought.

---

# 15. Translation-Safe Regeneration

Running the generator again should not blindly destroy existing translation work.

The generator reads existing translation files, merges missing keys, and preserves existing values.

That means a module can evolve without losing manually edited translations.

---

# 16. Remove a Generated Module

Use:

```bash
php artisan remove:view TestModule
```

The cleanup command is intended to remove the generated CRUD ecosystem, including generated application files, routes, navigation and permissions according to the implementation of the remove command.

After removal:

```bash
git status
```

Always review the Git diff before committing destructive cleanup.

> Important: database/schema cleanup should be treated separately from filesystem cleanup unless the project's `remove:view` implementation explicitly handles it.

---

# 17. Git Workflow

Before major generator changes, create a clean baseline:

```bash
git add .
git commit -m "chore: starterkit baseline"
```

Recommended tag:

```text
v1.0.0-starterkit
```

Then experiment on a separate branch:

```bash
git switch -c demo/new-view-test
```

Useful workflow:

```text
StarterKit baseline
       ¦
       ?
new:view regression test
       ¦
       ?
generator changes
       ¦
       +-- success ? commit
       ¦
       +-- failure ? restore / reset
```

Never use the demo generated module as the source of truth. The source of truth is the generator.

---

# 18. Professional Regression Test

The standard regression module is:

```text
TestModule
```

Use the PowerShell sequence documented in `DemoUse.txt`.

After generation, verify:

```text
[ ] Migration generated
[ ] Model generated
[ ] DTO generated
[ ] Create Action generated
[ ] Update Action generated
[ ] Delete Action generated
[ ] List Query generated
[ ] Livewire Index generated
[ ] Livewire Create generated
[ ] Livewire Edit generated
[ ] Livewire QuickCreate generated
[ ] API Controller generated
[ ] API Resource generated
[ ] API routes generated
[ ] Foreign lookup generated
[ ] Observer generated
[ ] Reverb event generated
[ ] Flutter module generated
[ ] Flutter repository generated
[ ] Flutter Cubit/BLoC generated
[ ] Flutter pages/widgets generated
[ ] Localization generated
[ ] Image picker generated
[ ] Enum generated correctly
[ ] priority is ENUM
[ ] qty is decimal(12,2)
[ ] price is decimal(12,2)
[ ] CREATE works
[ ] UPDATE works
[ ] DELETE works
[ ] Livewire realtime refresh works
[ ] Flutter realtime refresh works
```

---

# 19. Deployment Baseline

Typical project setup:

```bash
composer install
npm install
npm run build
php artisan migrate --seed
```

Then configure:

- database
- application environment
- Reverb
- Flutter server URL / API configuration
- notification infrastructure when Firebase is enabled

---

# 20. Philosophy

The purpose of this StarterKit is not to generate the smallest amount of code.

It is to generate a **repeatable, structured and maintainable application ecosystem**.

The command:

```bash
php artisan new:view TestModule --api
```

should be enough to create a consistent path from:

```text
Database
    ?
Laravel Domain
    ?
Livewire
    ?
API
    ?
Reverb
    ?
Flutter
```

with the same naming, validation, permissions, localization and UI conventions across the stack.

---

**E4ProTech StarterKit**

Enterprise-oriented Laravel + Livewire + Flutter scaffolding, generated from one command.
#   E 4 P r o T e c h - M u l t i S a s s S o f t w a r e  
 