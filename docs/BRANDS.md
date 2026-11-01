# Brand references

User-provided Instagram references, recorded 2026-10-24:

| Brand reference | Store type | Instagram |
| --- | --- | --- |
| tattoomato_ | B2C, primarily body tattoo supplies (not PMU-focused) | https://www.instagram.com/tattoomato_/ |
| tattooishop | B2C, primarily body tattoo supplies (not PMU-focused) | https://www.instagram.com/tattooishop/ |
| collection_eyelash.shop | Lash extension and makeup tools | https://www.instagram.com/collection_eyelash.shop/ |

The initial implementation could not fetch Instagram. A later TattooMato browser audit on 2026-11-01 reviewed the confirmed @tattoomato_ account, including its red/black TM logo, bio, sample captions/replies and feed visuals. Durable evidence, limitations, source URLs, highlight identifiers and proposed web colors live in the private [TattooMato project repository](https://github.com/vexatrov/TattooMato), under `docs/branding/INSTAGRAM-AUDIT-2026-11-01.md` and `docs/branding/WEB-DIRECTION.md`. Proposed colors are not approved designs or measured official logo values. The similarly named @tatoomato_ is the wrong account. The other two references have not been reviewed in that audit; review their actual assets independently.

TattooMato pins this shared repository as a submodule at `vendor/wp-main-theme`. Keep shared behavior here and brand-specific source/research in its project repository. Push shared changes before updating the brand repository's pinned commit. Git pushes do not deploy either store.

Agreed direction: three independent shops, one shared custom parent theme, distinct brand child themes, one fixed palette per store, no Elementor, minimal plugins. Brand references remain here for future work; do not rely on conversation memory.

The earlier PMU site is a functionality reference only. Both tattoo stores primarily sell body tattoo equipment to consumers. Do not carry over PMU positioning or wholesale-first messaging. Current preview uses clearly identified sample content, not any brand's actual inventory.

Shared requirements: desktop/laptop/phone responsiveness and speed, compact sticky header, aligned product/blog cards, Home/Shop/About/Contact pages, and a short factual footer introduction. Brand effects (glass, button/input shapes and hero variations) belong in child themes. Prices are in tomans (تومان/IRT); the user's USD reference was explicitly corrected.
