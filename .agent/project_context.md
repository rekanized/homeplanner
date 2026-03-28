# Project Context: Home Planner

A platform for planning and managing home-related tasks, including economy tracking, shopping lists, and kids points systems.

## Technical Stack
- **Backend**: Laravel 13 (PHP 8.3)
- **Frontend**: Livewire 4.x (Standard Class + View), Alpine.js, Blade
- **Styling**: Strictly Vanilla CSS (No Tailwind, Bootstrap, or build tools).
- **Database**: SQLite (Optimized with WAL mode for concurrent access).
- **Infrastructure**: Fully Dockerized for Azure App Service/Docker Hub deployments.
- **Architecture**: Standard Livewire components for interactivity, styled with semantic CSS classes.

## Constraints & Requirements
- **NO BUILD**: Strictly avoid Node/NPM/Vite in any part of the production workflow. Rely on CDNs and native browser capabilities.
- **NO TAILWIND**: Do not use Tailwind CSS in any form.
- **NO VOLT**: Do not use Livewire Volt (Bolt). All components must be standard Livewire classes.
- **PACKAGE INTEGRITY**: **NEVER** install or use any new packages or dependencies without explicit user permission.
- **Premium Aesthetics**: High-end visual design using modern Vanilla CSS (Gradients, Glassmorphism, Responsive `clamp()` units).

## Core Architectures

### Infrastructure & Deployment
- **Dockerized Foundation**: Containerized via PHP-FPM 8.3-bookworm.
- **Automated Scheduler**: `php artisan schedule:work` runs in the container background (Entrypoint).
- **Dynamic Snapshots**: Economy snapshots are scheduled monthly based on the user-defined `economy_snapshot_day` setting.
- **Layered Build**: Optimized `Dockerfile` ensures lightning-fast rebuilds by caching dependencies separately from application code.

### Database Strategy (Consolidated)
- **Squashed Migrations**: All table structures are maintained in their base `create` migrations (e.g., `create_users_table.php`).
- **No Alter Files**: Avoid creating incremental `alter_table` migrations. When modifying established tables, update the base file and perform a `migrate:fresh`.
- **Data Integrity**: Uses formal `User ID` foreign key relationships instead of legacy string names.

### Release & History System
- **Static Versioning**: `resources/data/versions.json` is the sole source of truth for app history and release notes.
- **Zero-DB Dependency**: Versioning is available immediately upon deployment and is resilient to database resets.
- **Automated Display**: The sidebar and Admin dashboard pull from this JSON file for live version information.

- **Dynamic UI**: Sidebar, Dashboard Tiles, and Charts are conditionally rendered based on active module settings (Economy, Shopping, etc.).

### 👨‍👩‍👧‍👦 Kids Points System
Engage children in household chores through a gamified reward economy. Parents can assign tasks with point values, while children track their progress and redeem points for rewards. Features include monthly performance stats and a transparent spending history.
- **Dual Ledger**: Uses a dedicated `chores` table for points earned and a `redemptions` table for points spent, ensuring high data integrity.
- **Role-Based Interaction**:
    - **Parents**: Assign chores, approve completions, refund redemptions, and manually adjust scores.
    - **Children**: View pending chores, mark their own tasks as done, and redeem points for rewards.
- **Analytics**: Calculates real-time stats for "Monthly Points" and "Total Finished Tasks" to visualize engagement.

### Testing Philosophy
- **Route Verification**: Comprehensive `tests/Feature/RouteAccessTest.php` covers all 13 primary endpoints.
- **Access Control**: Automated tests verify that Admin pages are strictly restricted to authenticated administrators.
- **Regression Safety**: All core modules (Economy, Todo, Shopping) have feature tests for critical business logic.

### UI/UX Design Patterns
- **Responsive Dashboard**: Uses `clamp()` for fluid typography and spacing.
- **Optimized Charts**: Task productivity charts use a **3-month rolling window** to ensure readability on mobile.
- **Clipping Fixes**: Chart labels are nested in relative containers with overflow handling to prevent clipping on small screen widths.

## Architecture Philosophy
- **Standardization**: Using core Laravel/Livewire patterns to ensure long-term stability and ease of manual maintenance.
- **Vanilla Mastery**: Leveraging the full power of native web technologies (CSS Grid, Alpine.js) for maximum performance and simplicity.
- **Directness**: Direct deployment and maintenance without complex build pipelines.
