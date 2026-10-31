# Complete fictional preview catalog

`dev/spelling-preview.php` performs a guarded one-time correction to «تتو» in fictional titles, text, terms, comments and theme copy. It preserves slugs, links, IDs and SKUs, and runs after taxonomy registration or on preview resume. Future fixtures already use the preferred spelling. It is never packaged. `scripts/check-terminology.mjs` prevents regressions in shared UI, fixtures, translations and docs; only legacy migration identifiers are exempt.

`dev/complete-catalog-preview.php` runs after the other preview fixtures at `init` priority **75** (or immediately when loaded after `init`). It requires the exact demo identity **ابزار تتو / پیش‌نمایش**, the catalog plugin and all ten existing published parent SKUs before it changes anything. It never creates replacement products or deletes records.

Completion flag: `wpmain_complete_catalog_preview = '3'`. A temporary atomic option lock `wpmain_complete_catalog_preview_lock` prevents concurrent preview requests from duplicating fixture work; it is cleared in `finally`, with recovery after fifteen minutes if an interrupted process leaves it behind. Re-running completed content is a no-op. Partial runs reuse attachment/review identifiers and can resume without creating another copy of the same fixture artifacts.

## Coverage

All existing parents `PREVIEW-1` through `PREVIEW-8`, `QA-ENQUIRY` and `QA-OUT` receive:

- Persian short introductions and multi-section product descriptions tailored to machines, cartridges, inks or accessories, with explicit fictional context.
- At least three visible native attributes. Existing attributes stay in place, including PREVIEW-3's native ۳۰ / ۶۰ میلی‌لیتر variation attribute and the machine filtering taxonomy.
- Usage/audience paragraphs, three question/answer pairs, explicitly fictional comparison/alternative/compatible relationships and a public educational guide link.
- Their existing primary image, or an existing appropriate category schematic when no image was assigned, plus two original labeled schematic gallery views. Generated images say `FICTIONAL DEMO` and identify the SKU. They are diagrams, not supplier photographs.
- One approved native review authored by **بازبین نمایشی — دیدگاه ساختگی**, whose body begins **این دیدگاه ساختگی است**. The reviewer is a guest with an `.invalid` address; `verified` is explicitly zero. This represents no purchase or real customer experience. Native ratings/review counts are refreshed, and demo reviews are enabled so the review tab can be examined.

The fixture preserves product names, SKUs, regular/sale prices, stock states, parent/variation types, search aliases, existing gallery images and variation records/images. It uses `WC_Product::save()` so normal search-index update hooks run. PREVIEW-1 stays simple, PREVIEW-4 stays a simple sale product, PREVIEW-3 retains its two native options and prices, QA-ENQUIRY stays unpriced, and QA-OUT stays unavailable. Relationships exist only among these fictional sample products; none is compatibility advice about real equipment.

Four labeled sample guide posts are reused by stable slugs `complete-demo-guide-machines`, `complete-demo-guide-cartridges`, `complete-demo-guide-inks` and `complete-demo-guide-accessories`. Existing guide selections, including `preview-guide-1`, are retained up to the plugin's four-guide limit.

Existing sample category terms (`machines`, `cartridges`, `inks`, `accessories`, `wireless`, `rotary`) and brands whose names contain **برند نمونه** also receive:

- `_wpmain_archive_intro`: short plain text.
- `_wpmain_archive_highlights`: array of three short plain strings.
- `_wpmain_archive_content`: safe rich HTML with headings, a list, native `details` questions and guide/shop links.

No unknown brand/category terms, orders, customers, user accounts or credentials are changed. Only fixture source is versioned. Uploaded images, database rows, reviews and generated mail remain in the ignored disposable site and must never be deployed.

## Read-only validation

Run `node scripts/test-demo-content.mjs` only after the fixture completes and the local preview is ready. Optional `PREVIEW_URL` accepts loopback hosts only. The script first checks the exact fictional home identity, then verifies Store API public totals and the ten expected parent SKUs. It visits every product sequentially, checking short/full descriptions, visible specifications, FAQs, clearly fictional reviews, guide/comparison/gallery markup and the original variable/sale/enquiry/unavailable states. Each GET has a 90-second deadline; an overall fifteen-minute watchdog bounds the run. It performs no writes and creates no orders.

Offline syntax checking is available through `node scripts/test-php.mjs` and `node --check scripts/test-demo-content.mjs`. Syntax/contract checks do not establish that this fixture ran or that all ten pages passed HTTP/browser review; report the actual runtime results separately. Windows Playground host-filesystem setup can be slow, so do not run this checker concurrently with visual review or another shopping suite.

Version 2 preserves child-only assignments for the two pens, so a parent machine archive must include descendants to show them. Existing demo gallery/review identifiers are reused during the upgrade.

Version 3 adds distinct images and stable demo SKUs to the existing 30/60ml variations, retaining their prices and attributes. Category/brand guidance includes choosing advice, an illustrative comparison table, FAQs and relevant category/guide links.
