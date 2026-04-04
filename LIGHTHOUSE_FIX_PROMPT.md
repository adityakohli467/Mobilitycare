# Claude Terminal Prompt — Lighthouse Score Fix for MobilityCare OpenCart 3

Copy everything below the line and paste into Claude terminal.

---

I have an OpenCart 3.x site at **mobilitycare.net.au** (local dev at `http://localhost/Mobilitycare/`).

**Current Lighthouse scores:** Performance 38, Accessibility 82, Best Practices 54, SEO 85.
**Target:** All scores above 90.

The codebase uses **Twig** templates (`.twig`, not `.tpl`). There are three themes:
- `catalog/view/theme/so-clickboom/` — **primary/active theme**
- `catalog/view/theme/default/` — fallback theme
- `catalog/view/theme/so-mobile/` — mobile theme

**Existing work already done** (see `tests/CoreWebVitalsFixesTest.php` and `CLAUDE_INSTRUCTIONS.md`):
- CallRail script already has `async` in so-clickboom header.twig
- Google Fonts already has `display=swap` in default header.twig
- Category subcategory images already have `loading="lazy"` and `object-fit:contain`
- Gallery-slider first image already has `loading="eager"` + `fetchpriority="high"`, rest use lazyload class + data-src
- Product JSON-LD structured data already built in product controller
- Open Graph meta tags already added via `addAnalytic()` in product.php and category.php controllers
- product.twig already outputs `json_ld` and `json_ld_breadcrumb` variables

**Do NOT re-do the above.** Check if each is actually present before skipping. Focus on everything else below.

---

## Tasks (do them in order, commit after each group):

### 1. Fix ALL `<img>` tags missing `width`, `height`, and `loading` attributes
- Scan every `.twig` file under `catalog/view/theme/` (all three themes).
- For each `<img>` tag missing `width` and/or `height`: add explicit `width` and `height` attributes. Use reasonable defaults based on context (e.g., product thumbnails: 250x250, category images: 200x200, logo: match the actual logo dimensions, banners: 1200x400). Add a comment next to any guessed dimensions so I can verify.
- For each `<img>` tag missing `loading`: add `loading="lazy"` for below-fold images. For above-fold hero/banner/logo images, use `loading="eager"`.
- For each `<img>` tag missing `alt`: add a meaningful `alt` attribute. Use the Twig variable for product/category name if available (e.g., `alt="{{ product.name }}"`), or a descriptive static string.
- **Important:** Do not break any existing Twig syntax or JavaScript image swapping logic (lazysizes, owl-carousel, etc.).

### 2. Defer/async all non-critical `<script>` tags
- In ALL header.twig and footer.twig files across all three themes:
  - Add `defer` to every `<script src="...">` that is non-critical (jQuery and core OpenCart JS should keep their load order — use `defer` not `async` for these).
  - Do NOT add defer to inline `<script>` blocks (no `src` attribute).
  - Do NOT add defer/async to scripts that already have it.
  - For third-party analytics/tracking scripts (Google Analytics, GTM, CallRail, Facebook Pixel, etc.), use `async` instead of `defer`.
- Also check `catalog/view/theme/so-clickboom/template/common/` for any additional partial templates that load scripts.

### 3. Add `<link rel="preload">` for hero/banner images
- In the active theme's header.twig (`so-clickboom`), add a `<link rel="preload" as="image">` for the main hero banner / slideshow first image if there's a slideshow or banner variable available.
- Also add `<link rel="preconnect" href="https://fonts.googleapis.com">` and `<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>` if not already present.
- Add `<link rel="preload" as="style">` for the main CSS file if identifiable.

### 4. Add `lang="en"` to the `<html>` tag
- Find every `<html` tag in all header.twig files across all three themes.
- Ensure it has `lang="{{ language }}"` or `lang="en"` if no language variable exists.
- If it already has a lang attribute, skip it.

### 5. Fix HTTP → HTTPS in config files
- In `config.php` (root): change both `HTTP_SERVER` and `HTTPS_SERVER` from `http://localhost/Mobilitycare/` to `https://localhost/Mobilitycare/`.
- In `admin/config.php`: do the same for all `http://` references → `https://`.
- **Also** grep the entire codebase for any hardcoded `http://` URLs pointing to `mobilitycare.net.au` or `healthychoicesca` and change them to `https://`.
- Do NOT change `http://localhost` references in database config or non-URL contexts.

### 6. Audit JS files for common errors
- Scan all `.js` files under `catalog/view/javascript/` for:
  - Use of `var` where `let`/`const` would be safer (just report, don't auto-fix unless asked)
  - Missing semicolons
  - `console.log` / `console.error` statements left in production code (remove them or wrap in a debug check)
  - References to undefined variables or obvious syntax errors
  - jQuery `.size()` (deprecated — should be `.length`)
  - `document.write()` calls (render-blocking — report them)
- **Only fix** `console.log` removal and `document.write` issues. **Report** everything else in the summary.

### 7. Add meta description template support
- In `catalog/view/theme/so-clickboom/template/product/product.twig`:
  - Verify `{{ description }}` or meta_description is output in a `<meta name="description">` tag. If it's handled by the header.twig via controller, verify the controller passes it.
- In `catalog/view/theme/so-clickboom/template/product/category.twig`:
  - Same check — ensure meta description is set.
- In the **product controller** (`catalog/controller/product/product.php`): verify `$this->document->setDescription()` is called with the product's meta_description.
- In the **category controller** (`catalog/controller/product/category.php`): same check.
- If any are missing, add them.

### 8. Additional Lighthouse improvements
- Add `<meta name="theme-color" content="#...">` using the site's primary brand color to all header.twig files.
- Add `<meta name="viewport" content="width=device-width, initial-scale=1">` if missing from any header.twig.
- Ensure all `<a>` tags with `target="_blank"` also have `rel="noopener noreferrer"` (security + performance).
- Ensure all form `<input>` elements have associated `<label>` elements or `aria-label` attributes.
- Check that heading hierarchy is correct (no skipping from `<h1>` to `<h3>` without `<h2>`).
- Ensure sufficient color contrast is maintained (just flag any hardcoded low-contrast colors like light gray on white).

### 9. Write PHPUnit tests for new fixes
- Update `tests/CoreWebVitalsFixesTest.php` with new test methods covering:
  - All header.twig files have `lang=` on the `<html>` tag
  - Config files use `https://`
  - All script tags in headers have `defer` or `async`
  - `<link rel="preconnect">` exists for Google Fonts
  - Viewport meta tag exists
  - No `console.log` in production JS files
- Run `php vendor/bin/phpunit tests/CoreWebVitalsFixesTest.php` and ensure all tests pass (existing + new).

### 10. Summary
After completing all tasks, output a **structured summary table** in Markdown:

| # | Task | Files Changed | What Changed |
|---|------|--------------|--------------|
| 1 | Image attributes | file1.twig, file2.twig | Added width/height/loading/alt to N images |
| 2 | Script defer | header.twig, footer.twig | Added defer to N scripts |
| ... | ... | ... | ... |

Also list:
- Any **issues found but NOT auto-fixed** (for my manual review)
- Any **files that couldn't be modified** (permissions, unclear logic, etc.)
- **Estimated Lighthouse score improvement** per category based on changes made

---

**Rules:**
- Read each file before modifying it. Understand the context.
- Do not break existing functionality. If unsure, ask.
- Do not remove any existing code unless it's clearly dead/unused.
- Preserve Twig template syntax exactly.
- Make minimal, targeted changes — don't refactor unrelated code.
- Use the existing `tests/CoreWebVitalsFixesTest.php` as reference for testing patterns.
- Commit messages must include the Jira issue ID from the branch name (e.g., `fix: SP-1234: ...`).
