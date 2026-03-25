# Project Context: Home Planner

A platform for planning and managing home-related tasks, including economy tracking, shopping lists, and kids points systems.

## Technical Stack
- **Backend**: Laravel (PHP 8.x)
- **Frontend**: Livewire (Standard Class + View), Alpine.js, Blade
- **Styling**: Strictly Vanilla CSS (No Tailwind, Bootstrap, or build tools).
- **Database**: SQLite
- **Architecture**: Standard Livewire components for interactivity, styled with semantic CSS classes.

## Constraints & Requirements
- **NO BUILD**: Strictly avoid Node/NPM/Vite in the workflow. Rely on CDNs and native browser capabilities.
- **NO TAILWIND**: Do not use Tailwind CSS in any form.
- **NO VOLT**: Do not use Livewire Volt (Bolt). All components must be standard Livewire classes.
- **PACKAGE INTEGRITY**: **NEVER** install or use any new packages or dependencies without explicit user permission.
- **Premium Aesthetics**: High-end visual design using modern Vanilla CSS.

## Module-Specific Architectures

### Economy Module
- **Persistence**: Removed all monthly time-boxing in favor of a continuous, persistent ledger.
- **Data Integrity**: Replaced legacy string-based names ("Payer", "Recipient", "Saver") with formal `User ID` foreign key relationships for improved data consistency.
- **Ordering**: Implements a `sort_order` integer system across all tables for user-driven drag-and-drop reordering via Sortable.js.
- **Categories**: Dynamic, user-managed categories with real-time balance aggregation.
- **Safety**: Includes a gitignored `EconomyBackupSeeder` for local data persistence and migration.

### Shopping Module
- **Functionality**: Collaborative household shopping list system with multi-list support (Grocery, IKEA, etc.).
- **Interactivity**: Inline editing, quantity counters, and Sortable.js drag-and-drop for items and lists.
- **Responsiveness**: Uses a sophisticated two-line staggered grid on mobile to maximize readability for item names.
- **Bulk Operations**: Unified selection system for mass-deletion and status toggling.

### Global Architecture
- **Navigation**: Separation of concerns between a high-level `Home` dashboard and specialized managers like `EconomyManager` and `ShoppingManager`.
- **Layout**: Standardized `layouts/app.blade.php` to ensure consistent UI across all Livewire-driven modules.

## Architecture Philosophy
- **Standardization**: Using core Laravel/Livewire patterns to ensure long-term stability and ease of manual maintenance.
- **Vanilla Mastery**: Leveraging the full power of native web technologies (CSS Grid, Alpine.js) for maximum performance and simplicity.
- **Directness**: Direct deployment and maintenance without complex build pipelines.
