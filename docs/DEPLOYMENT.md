# Deployment

## Hosting

Use managed WordPress hosting with HTTPS, staging, automated database/file backups, SSH or SFTP, current PHP, and an object cache when supported. Each shop gets its own database, installation, domain, and secrets. Do not install caching plugins by default if hosting already supplies caching.

## Release

1. Back up the live database and files; verify a restore method.
2. Deploy a known Git commit's parent, WP Store Catalog plugin and appropriate child theme to staging first. The package script makes parent/plugin ZIPs plus `release-manifest.json` with SHA-256 hashes. ZIP each actual brand child folder separately, preserving its folder name.
3. Follow VALIDATION.md with the site's actual plugins, payment gateway, catalog, and shipping configuration.
4. Deploy only the intended theme directories and this repository's catalog plugin through the hosting workflow. Do not overwrite uploads, configuration, installed third-party plugins or the production database with staging data.
5. Purge page/CDN caches, check browsing and a real permitted order flow, record deployed commit and date.
6. On regression restore the previous theme release; restore database only when an explicit migration requires it and preserve new orders.

Exclude cart, checkout, account, add-to-cart requests, WooCommerce sessions, and authenticated traffic from full-page/CDN caching. Verify the host's rules, including custom page slugs. Use payment sandbox credentials and disabled/search-blocked indexing on staging; prevent staging email going to real customers. Remove production indexing restrictions at launch.

GitHub is the source repository. No hosting or automated production deployment is configured. No staging URL, credentials, domain or Zarinpal merchant configuration has been supplied, so hosted deployment cannot be completed by this repository alone. Do not substitute the local demo for production staging.

## Read-only staging preflight

After deploying to a real staging host, run `node scripts/check-staging.mjs https://your-staging-host.example`. This checks HTTPS redirects, successful pages/stylesheets, staging noindex, a fresh isolated IRT cart, a first public product's schema currency and bounded sitemap discovery/utility exclusions. It does not submit orders or change settings. See STAGING-CHECKS.md for skips and limits; amount conversion is checked more deeply by the fictional release suite and must be verified against real products. Authentication/firewall-protected staging requires the host's own approved access path; do not embed credentials in URLs or Git.

Before launch, verify actual shipping zones/prices/taxes, stock and media; Zarinpal's IRT/IRR mapping exactly once; sandbox paid/pending/failed/cancel/retry callbacks and duplicate callbacks; transactional email delivery; customer password-reset links; correct carrier tracking; cache bypass with two customer sessions; HTTPS; backups and a demonstrated restore; owner-reviewed shipping/returns/warranty/privacy/terms; and hosted mobile Core Web Vitals. Real agency labels, policies and contact details must come from the store owner. Brand identities remain a separate child-theme release.
