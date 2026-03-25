# HomePlanner Project Context

## Overview
HomePlanner is a premium internal family/household management system designed with professional-grade UI and robust backend architecture.

## Tech Stack
- **Backend:** Laravel 11.x, PHP 8.2+
- **Frontend Stack:** Livewire 3 (Strictly Class-based), Alpine.js, Blade templates.
- **Styling:** Custom Vanilla CSS (`public/css/style.css`). The project actively explicitly avoids Tailwind CSS.
- **Database:** Standard Eloquent ORM.

## Key Modules
1. **Economy Dashboard (`/dashboard`)**: Financial tracking for income, savings, and expenses with dynamic budgets and responsive charting.
2. **Shopping Manager (`/shopping`)**: Smart grocery lists with OpenFoodFacts integration, drag-and-drop sorting via Sortable.js, and bulk actions.
3. **Todo Manager (`/todo`)**: Task tracking that flawlessly mimics the premium UX, layout structure, and Drag-and-Drop functionality of the Shopping layout. Supports completion timestamping.
4. **Admin Features**: Advanced employee leave handling via Administrative override boards.

## Core Development Principles ("The Brain")
- **No Livewire Volt:** All new components must be strictly class-based Livewire (`App\Livewire\Name`).
- **CSS Strategy:** Focus on `style.css`. Utilize CSS variables for strict compliance with the high-quality dark/light themes. Maintain heavy focus on responsive grids and mobile usability, specifically single-row mobile layouts (e.g. `todo-grid-row`).
- **Drag & Drop (SortableJS):** MUST be initialized via Alpine.js `x-init` hooks (`x-init="new Sortable($el, {...})"`) directly on the container. Native Livewire script initialization breaks upon DOM morphs.
- **Testing Requirements:** Always generate tests via `php artisan make:test NameTest`. All core workflows must be covered with Feature tests running fully isolated DOM and Livewire assertions.
