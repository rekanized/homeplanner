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
- **Data Structure**: Uses JSON casting for `payer` fields on expenses to support multi-payer cost splitting.
- **Ordering**: Implements a `sort_order` integer system across all tables for user-driven drag-and-drop reordering via Sortable.js.
- **Categories**: Dynamic, user-managed categories with real-time balance aggregation.
- **Safety**: Includes a gitignored `EconomyBackupSeeder` for local data persistence and migration.

## Architecture Philosophy
- **Standardization**: Using core Laravel/Livewire patterns to ensure long-term stability and ease of manual maintenance.
- **Vanilla Mastery**: Leveraging the full power of native web technologies (CSS Grid, Alpine.js) for maximum performance and simplicity.
- **Directness**: Direct deployment and maintenance without complex build pipelines.
