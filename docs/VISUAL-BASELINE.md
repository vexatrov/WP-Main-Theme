# Shared visual baseline — rc.6

The parent theme supplies a neutral spacing/typography baseline. Palette, glass effects, button shapes and alternative hero/homepage compositions remain child-theme decisions.

## Typography and alignment

| Role | Default size | Alignment |
| --- | --- | --- |
| Hero H1 | Fluid 32–60px | Reading start; right in Persian, left in LTR |
| Page/archive/article H1 | Fluid 30–48px | Reading start |
| Main section H2 | Fluid 22.4–30px | Reading start |
| Subsection H3 | Fluid 18–22px | Reading start |
| Product H1 | Fluid 24–36px, 1.5 line height | Reading start |
| Card titles | Compact, two-line reserved height | Reading start |

Article headings align with the reading column rather than floating above centered body text. Paragraphs inherit reading-start alignment, while authored WordPress alignment classes remain effective. Controls, icons, image canvases and numeric indicators may center their contents; long prose is not centered or justified. RTL no longer forces the same line height onto every heading.

Section padding scales from 32–56px. Gutters, hero gaps, article/card headings and footer paragraphs have consistent spacing. Product images stay full-width and square. Purchase controls retain 44px targets beside prices; title rows reserve two lines so long and short titles align. Blog card titles also reserve two lines, with links anchored at the bottom of each stretched card.

Child themes can override `--heading-hero`, `--heading-page`, `--heading-section`, `--heading-subsection` and `--section-spacing`, alongside existing brand/layout tokens. Native WooCommerce behavior and each template's semantic headings remain intact.

## Editor controls

The companion plugin provides admin-only native media-library image selection, ordered name search, remove controls and keyboard-accessible earlier/later buttons. Existing product guidance fields retain WooCommerce's native product/article searches. See EDITOR-CONTROLS.md. No wishlist is included.
