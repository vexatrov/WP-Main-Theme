# Project instructions

- Keep this repository as the source of truth. Commit and push completed changes to `main` unless the user requests otherwise.
- Build a lightweight classic WordPress theme; no page builders, frontend frameworks, bundled dependencies, or remote fonts by default.
- Share behavior in `themes/wp-main-theme`; keep brand styling and template variations in child themes.
- Use one intentional palette per brand; no automatic dark mode or theme toggle.
- Escape output, sanitize input, translate user-facing strings, and use WordPress APIs.
- Prefer WooCommerce hooks over copied templates. Do not disable WooCommerce scripts/styles without testing all affected flows.
- Never commit credentials, customer data, uploads, WordPress core, or installed plugin code.
- Document setup, changes, validation, and deployment limitations. Do not claim live testing without a WordPress environment.
- See `docs/BRANDS.md` for the three user-provided Instagram references and approved shared-brand architecture.
