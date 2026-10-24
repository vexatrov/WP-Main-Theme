# Architecture

- `themes/wp-main-theme`: classic PHP templates, WordPress hooks, shared CSS and small vanilla JS navigation.
- `themes/brand-starter`: child-theme skeleton. Copy for each real brand once names and assets are supplied. Child `style.css` loads after the shared styles. Standard WordPress child-template overrides support different homepages and layouts.
- CSS custom properties define palette, font, radius, and container width. No light/dark toggle. Product photography remains on a controlled surface.
- WooCommerce owns catalog, stock, cart, checkout, customer accounts, payment integration, and orders. The theme declares support and changes wrappers through hooks. Default WooCommerce styles/scripts remain for compatibility, including block checkout. Refine styles after staging verification.
- Do not put stock synchronization, payment rules, or other business data logic into the theme. Use provider extensions or a small separately versioned plugin when required.
- Three independent installations isolate store data and failures. The same versioned parent theme ships to all three; each has its own child theme and settings. Central stock management is outside the initial scope.
- Parent CSS and navigation JS load throughout the site. Commerce styling loads when WooCommerce is active, including shortcode pages. No speculative dequeuing of core/block/plugin assets.
- No custom product query or demo products yet. Shop archives and product templates use maintained WooCommerce defaults; future merchandising uses real CMS data.

Before brand implementation collect names, Instagram links, logo files, approved palette/fonts, product categories, catalog size, country/currency, languages (including RTL needs), and payment/shipping requirements.
