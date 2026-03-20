# Project Context: Home Planner

A platform for planning and managing home-related tasks, possibly including layout design, budgeting, or maintenance scheduling.

## Technical Stack
- **Backend**: Laravel (PHP)
- **Frontend**: Standard CSS (No Build)
- **Database**: SQLite (indicated by previous conversations)
- **Deployment**: Traditional web server (No Node/NPM required on server)

## Constraints & Requirements
- **NO BUILD**: Remove and avoid using TAILWIND, BOOTSTRAP, VITE, NODE, and NPM.
- **Standard CSS**: All styling must be done using standard CSS files linked from `public/css/`.
- **Vanilla JS**: If JavaScript is required, use standard scripts linked from `public/js/`.
- **Premium Aesthetics**: The absence of build tools must not compromise visual quality. Leverage CSS's full power for a premium feel.

## Architecture Philosophy
- **Simplicity**: Minimize abstraction layers and dependencies.
- **Directness**: Use native browser features whenever possible.
- **Maintainability**: Keep styles and scripts organized within the `public/` directory for direct serving.
