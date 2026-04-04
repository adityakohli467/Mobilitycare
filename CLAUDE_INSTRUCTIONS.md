# Project: MobilityCare OpenCart Site
- Stack: OpenCart, PHP 8, MySQL
- Goal: Fix Core Web Vitals, SEO, broken URLs
- GSC Site: https://www.mobilitycare.net.au/
- Priority issues from GSC:
  - 22 poor mobile URLs
  - 28 poor desktop URLs
  - 0 good desktop URLs

## Tasks for Claude:
1. Identify LCP issues in product/category templates
2. Fix CLS caused by missing image dimensions
3. Optimize INP - reduce render-blocking JS
4. Fix any broken URLs found in GSC
5. Add missing meta tags / structured data
6. Write PHPUnit test cases for every fix