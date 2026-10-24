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
- Both tattoo shops are primarily body tattoo B2C, not PMU-focused. See `docs/ROADMAP.md` for reference features and `docs/LOCAL-PREVIEW.md` for the review environment.
- Currency is tomans (IRT/تومان), not USD or rials. Currency configuration and conversions belong outside the theme.
- Keep responsive/sticky behavior and aligned card layouts shared; brand effects, hero variations and shape choices belong in child themes. See `docs/THEMING.md`.
- Do not add a development/announcement strip above the header. Research and layout discussion are recorded in `docs/STOREFRONT-PLAN.md` and `docs/PAGE-LAYOUTS.md`; planned features must not be reported as implemented.
- Product cards use full-width square images with no default inner box/padding. Product guidance, comparisons, FAQs, guide links, verified compatible products and same-use alternatives are specified in `docs/PRODUCT-PAGES.md`; keep durable data in `plugins/wp-store-catalog`.
