# Deployment

## Hosting

Use managed WordPress hosting with HTTPS, staging, automated database/file backups, SSH or SFTP, current PHP, and an object cache when supported. Each shop gets its own database, installation, domain, and secrets. Do not install caching plugins by default if hosting already supplies caching.

## Release

1. Back up the live database and files; verify a restore method.
2. Deploy a known Git commit's parent and appropriate child theme to staging first. The package script makes only the parent ZIP; ZIP each actual child folder separately, preserving its folder name.
3. Follow VALIDATION.md with the site's actual plugins, payment gateway, catalog, and shipping configuration.
4. Deploy only the intended theme directories to production through the hosting workflow. Do not overwrite uploads, configuration, plugins, or the production database with staging data.
5. Purge page/CDN caches, check browsing and a real permitted order flow, record deployed commit and date.
6. On regression restore the previous theme release; restore database only when an explicit migration requires it and preserve new orders.

Exclude cart, checkout, account, add-to-cart requests, WooCommerce sessions, and authenticated traffic from full-page/CDN caching. Verify the host's rules, including custom page slugs. Use payment sandbox credentials and disabled/search-blocked indexing on staging; prevent staging email going to real customers. Remove production indexing restrictions at launch.

GitHub is the source repository. No hosting or automated production deployment is configured in this initial version.
