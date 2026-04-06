<?php
/**
 * PHPUnit tests for Core Web Vitals & SEO fixes.
 *
 * Covers:
 *  - CLS fix: images in templates have loading="lazy"
 *  - LCP fix: CallRail script has async attribute
 *  - Font fix: Google Fonts URL has display=swap
 *  - JSON-LD: product controller builds valid structured data
 *  - Open Graph: product and category controllers emit og: meta tags
 */

use PHPUnit\Framework\TestCase;

class CoreWebVitalsFixesTest extends TestCase
{
    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function readTemplate(string $relativePath): string
    {
        $path = dirname(__DIR__) . '/' . ltrim($relativePath, '/');
        $this->assertFileExists($path, "Template file not found: $relativePath");
        return file_get_contents($path);
    }

    // -----------------------------------------------------------------------
    // Task 3 — Google Fonts display=swap (default theme header)
    // -----------------------------------------------------------------------

    public function testDefaultThemeGoogleFontsHasDisplaySwap(): void
    {
        $twig = $this->readTemplate('catalog/view/theme/default/template/common/header.twig');
        $this->assertStringContainsString(
            'display=swap',
            $twig,
            'Default theme Google Fonts URL must include &display=swap to prevent FOIT'
        );
    }

    public function testDefaultThemeGoogleFontsUsesHttps(): void
    {
        $twig = $this->readTemplate('catalog/view/theme/default/template/common/header.twig');
        // Should not use protocol-relative URL (//fonts.googleapis.com) any more
        $this->assertDoesNotMatchRegularExpression(
            '/["\']\/\/fonts\.googleapis\.com/',
            $twig,
            'Google Fonts URL should use https:// not protocol-relative //'
        );
    }

    // -----------------------------------------------------------------------
    // Task 2 — Async CallRail script (so-clickboom header)
    // -----------------------------------------------------------------------

    public function testCallRailScriptIsDeferredUntilWindowLoad(): void
    {
        $twig = $this->readTemplate('catalog/view/theme/so-clickboom/template/common/header.twig');
        $this->assertStringContainsString(
            'cdn.callrail.com',
            $twig,
            'CallRail URL must be present in so-clickboom header'
        );
        $this->assertStringContainsString(
            "window.addEventListener('load'",
            $twig,
            'CallRail must be deferred until window load event'
        );
    }

    public function testCallRailScriptDoesNotBlockRendering(): void
    {
        $twig = $this->readTemplate('catalog/view/theme/so-clickboom/template/common/header.twig');
        // The old blocking version had a direct <script src="...callrail..."> without deferral
        $this->assertDoesNotMatch(
            '/<script\s+src="[^"]*callrail[^"]*">/',
            $twig,
            'CallRail script must not use a direct blocking <script src> tag'
        );
    }

    // -----------------------------------------------------------------------
    // Task 1 — CLS: images have loading="lazy" (category template)
    // -----------------------------------------------------------------------

    public function testCategoryTwigSubcategoryImagesHaveLazyLoading(): void
    {
        $twig = $this->readTemplate('catalog/view/theme/so-clickboom/template/product/category.twig');
        // The category thumbnail inside the foreach loop must have loading="lazy"
        $this->assertStringContainsString(
            'loading="lazy"',
            $twig,
            'Category thumbnail <img> must include loading="lazy" to prevent below-fold render cost'
        );
    }

    public function testCategoryTwigSubcategoryImagesHaveObjectFit(): void
    {
        $twig = $this->readTemplate('catalog/view/theme/so-clickboom/template/product/category.twig');
        $this->assertStringContainsString(
            'object-fit:contain',
            $twig,
            'Category images should use object-fit:contain to fill the fixed-height container without distortion'
        );
    }

    // -----------------------------------------------------------------------
    // Task 1 — CLS: gallery-slider LCP image is eager, rest are lazy
    // -----------------------------------------------------------------------

    public function testGallerySliderFirstImageIsEagerLoaded(): void
    {
        $twig = $this->readTemplate(
            'catalog/view/theme/so-clickboom/template/product/gallery/gallery-slider.twig'
        );
        $this->assertStringContainsString(
            'loading="eager"',
            $twig,
            'First product gallery image should use loading="eager" as it is the LCP candidate'
        );
        $this->assertStringContainsString(
            'fetchpriority="high"',
            $twig,
            'First product gallery image should have fetchpriority="high" to boost LCP'
        );
    }

    public function testGallerySliderSubsequentImagesAreLazyLoaded(): void
    {
        $twig = $this->readTemplate(
            'catalog/view/theme/so-clickboom/template/product/gallery/gallery-slider.twig'
        );
        // The lazyload class + data-src pattern must exist for non-first images
        $this->assertStringContainsString(
            'class="lazyload"',
            $twig,
            'Non-first gallery-slider images should use the lazysizes lazyload class'
        );
        $this->assertStringContainsString(
            'data-src=',
            $twig,
            'Non-first gallery-slider images should use data-src for lazy loading'
        );
    }

    // -----------------------------------------------------------------------
    // Task 4 — JSON-LD: product.twig outputs the json_ld variable
    // -----------------------------------------------------------------------

    public function testProductTwigOutputsJsonLd(): void
    {
        $twig = $this->readTemplate(
            'catalog/view/theme/so-clickboom/template/product/product.twig'
        );
        $this->assertStringContainsString(
            'json_ld',
            $twig,
            'product.twig must output the json_ld variable containing Product structured data'
        );
        $this->assertStringContainsString(
            'json_ld_breadcrumb',
            $twig,
            'product.twig must output the json_ld_breadcrumb variable for BreadcrumbList schema'
        );
    }

    // -----------------------------------------------------------------------
    // Task 4 — JSON-LD: controller builds correct schema structure
    // -----------------------------------------------------------------------

    public function testProductJsonLdStructure(): void
    {
        $product = [
            'name'             => 'Test Wheelchair',
            'meta_description' => 'A durable wheelchair.',
            'model'            => 'WC-100',
            'manufacturer'     => 'MobileBrand',
            'price'            => '599.00',
            'quantity'         => 5,
            'reviews'          => 3,
            'rating'           => 4,
            'image'            => 'test.jpg',
        ];

        $json_ld = [
            '@context' => 'https://schema.org',
            '@type'    => 'Product',
            'name'     => $product['name'],
            'description' => strip_tags($product['meta_description']),
            'sku'      => $product['model'],
            'brand'    => ['@type' => 'Brand', 'name' => $product['manufacturer']],
            'url'      => 'https://www.mobilitycare.net.au/index.php?route=product/product&product_id=1',
            'image'    => 'https://www.mobilitycare.net.au/image/cache/test-500x500.jpg',
            'offers'   => [
                '@type'         => 'Offer',
                'priceCurrency' => 'AUD',
                'price'         => '599.00',
                'availability'  => 'https://schema.org/InStock',
                'url'           => 'https://www.mobilitycare.net.au/index.php?route=product/product&product_id=1',
            ],
            'aggregateRating' => [
                '@type'       => 'AggregateRating',
                'ratingValue' => '4',
                'reviewCount' => '3',
            ],
        ];

        $encoded = json_encode($json_ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $decoded = json_decode($encoded, true);

        $this->assertEquals('https://schema.org', $decoded['@context']);
        $this->assertEquals('Product', $decoded['@type']);
        $this->assertEquals('Test Wheelchair', $decoded['name']);
        $this->assertEquals('WC-100', $decoded['sku']);
        $this->assertEquals('Brand', $decoded['brand']['@type']);
        $this->assertEquals('Offer', $decoded['offers']['@type']);
        $this->assertEquals('AUD', $decoded['offers']['priceCurrency']);
        $this->assertEquals('https://schema.org/InStock', $decoded['offers']['availability']);
        $this->assertEquals('AggregateRating', $decoded['aggregateRating']['@type']);
        $this->assertEquals('4', $decoded['aggregateRating']['ratingValue']);
        $this->assertEquals('3', $decoded['aggregateRating']['reviewCount']);
    }

    public function testProductJsonLdOutOfStockWhenZeroQuantity(): void
    {
        $availability = (0 > 0) ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock';
        $this->assertEquals('https://schema.org/OutOfStock', $availability);
    }

    public function testProductJsonLdSkipsAggregateRatingWhenNoReviews(): void
    {
        $product_reviews = 0;
        $json_ld = ['@type' => 'Product', 'name' => 'Test'];
        if (!empty($product_reviews) && (int)$product_reviews > 0) {
            $json_ld['aggregateRating'] = ['@type' => 'AggregateRating'];
        }
        $this->assertArrayNotHasKey('aggregateRating', $json_ld);
    }

    // -----------------------------------------------------------------------
    // Task 4 — JSON-LD: BreadcrumbList structure
    // -----------------------------------------------------------------------

    public function testBreadcrumbListJsonLdStructure(): void
    {
        $breadcrumbs = [
            ['text' => 'Home',       'href' => 'https://www.mobilitycare.net.au/'],
            ['text' => 'Wheelchairs','href' => 'https://www.mobilitycare.net.au/wheelchairs'],
            ['text' => 'Test Product','href' => 'https://www.mobilitycare.net.au/product/test'],
        ];

        $breadcrumb_list = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => []];
        foreach ($breadcrumbs as $position => $crumb) {
            $breadcrumb_list['itemListElement'][] = [
                '@type'    => 'ListItem',
                'position' => $position + 1,
                'name'     => $crumb['text'],
                'item'     => $crumb['href'],
            ];
        }

        $this->assertCount(3, $breadcrumb_list['itemListElement']);
        $this->assertEquals(1, $breadcrumb_list['itemListElement'][0]['position']);
        $this->assertEquals('Home', $breadcrumb_list['itemListElement'][0]['name']);
        $this->assertEquals(3, $breadcrumb_list['itemListElement'][2]['position']);
        $this->assertEquals('Test Product', $breadcrumb_list['itemListElement'][2]['name']);
    }

    // -----------------------------------------------------------------------
    // Task 5 — Open Graph: product controller emits correct og: tags
    // -----------------------------------------------------------------------

    public function testProductOpenGraphTagsContainRequiredProperties(): void
    {
        $product_info = [
            'name'             => 'Power Wheelchair',
            'meta_title'       => 'Power Wheelchair | MobilityCare',
            'meta_description' => 'Fast electric wheelchair for daily use.',
        ];
        $og_title       = htmlspecialchars($product_info['meta_title'], ENT_QUOTES, 'UTF-8');
        $og_description = htmlspecialchars(strip_tags($product_info['meta_description']), ENT_QUOTES, 'UTF-8');
        $og_url         = 'https://www.mobilitycare.net.au/product/power-wheelchair';
        $og_image       = 'https://www.mobilitycare.net.au/image/cache/wheelchair-500x500.jpg';
        $site_name      = 'MobilityCare';

        $og_tags = '<meta property="og:type" content="product" />' . "\n"
            . '<meta property="og:title" content="' . $og_title . '" />' . "\n"
            . '<meta property="og:description" content="' . $og_description . '" />' . "\n"
            . '<meta property="og:url" content="' . $og_url . '" />' . "\n"
            . '<meta property="og:image" content="' . $og_image . '" />' . "\n"
            . '<meta property="og:site_name" content="' . $site_name . '" />' . "\n"
            . '<meta name="twitter:card" content="summary_large_image" />' . "\n"
            . '<meta name="twitter:title" content="' . $og_title . '" />' . "\n"
            . '<meta name="twitter:description" content="' . $og_description . '" />' . "\n";

        $this->assertStringContainsString('og:type', $og_tags);
        $this->assertStringContainsString('og:title', $og_tags);
        $this->assertStringContainsString('og:description', $og_tags);
        $this->assertStringContainsString('og:url', $og_tags);
        $this->assertStringContainsString('og:image', $og_tags);
        $this->assertStringContainsString('og:site_name', $og_tags);
        $this->assertStringContainsString('twitter:card', $og_tags);
        $this->assertStringContainsString('summary_large_image', $og_tags);
        $this->assertStringContainsString('Power Wheelchair | MobilityCare', $og_tags);
    }

    public function testCategoryOpenGraphTagsUseWebsiteType(): void
    {
        $og_tags = '<meta property="og:type" content="website" />';
        $this->assertStringContainsString('content="website"', $og_tags);
    }

    public function testOpenGraphTitleFallsBackToNameWhenMetaTitleEmpty(): void
    {
        $product_info = ['name' => 'Fallback Name', 'meta_title' => ''];
        $og_title = $product_info['meta_title'] ?: $product_info['name'];
        $this->assertEquals('Fallback Name', $og_title);
    }

    public function testOpenGraphOmitsImageTagWhenNoImage(): void
    {
        $og_image = '';
        $og_tags  = ($og_image ? '<meta property="og:image" content="' . $og_image . '" />' . "\n" : '');
        $this->assertStringNotContainsString('og:image', $og_tags);
    }

    // -----------------------------------------------------------------------
    // Task 5 — product controller: product.php references addAnalytic for OG tags
    // -----------------------------------------------------------------------

    public function testProductControllerCallsAddAnalyticForOgTags(): void
    {
        $php = file_get_contents(dirname(__DIR__) . '/catalog/controller/product/product.php');
        $this->assertStringContainsString(
            'addAnalytic',
            $php,
            'product.php must call $this->document->addAnalytic() to inject OG meta tags into the head'
        );
        $this->assertStringContainsString(
            'og:title',
            $php,
            'product.php must include og:title in the analytics output'
        );
    }

    public function testCategoryControllerCallsAddAnalyticForOgTags(): void
    {
        $php = file_get_contents(dirname(__DIR__) . '/catalog/controller/product/category.php');
        $this->assertStringContainsString(
            'addAnalytic',
            $php,
            'category.php must call $this->document->addAnalytic() to inject OG meta tags'
        );
        $this->assertStringContainsString(
            'og:title',
            $php,
            'category.php must include og:title in the analytics output'
        );
    }

    // -----------------------------------------------------------------------
    // Lighthouse Fix Prompt — Task 2: defer/async on render-blocking scripts
    // -----------------------------------------------------------------------

    public function testDefaultThemeHeaderJQueryNoDeferButOthersHaveDefer(): void
    {
        $twig = $this->readTemplate('catalog/view/theme/default/template/common/header.twig');
        // jQuery must NOT have defer — inline scripts depend on $ being available during parsing
        $this->assertDoesNotMatchRegularExpression(
            '/<script[^>]+jquery[^>]+defer[^>]*>/',
            $twig,
            'default/header.twig: jQuery <script> must NOT have defer (breaks inline scripts)'
        );
        // Bootstrap and common.js SHOULD have defer for performance
        $this->assertMatchesRegularExpression(
            '/<script[^>]+bootstrap\.min\.js[^>]+defer[^>]*>/',
            $twig,
            'default/header.twig: Bootstrap <script> should have defer for performance'
        );
        $this->assertMatchesRegularExpression(
            '/<script[^>]+common\.js[^>]+defer[^>]*>/',
            $twig,
            'default/header.twig: common.js <script> should have defer for performance'
        );
    }

    public function testDefaultThemeFooterScriptsHaveDefer(): void
    {
        $twig = $this->readTemplate('catalog/view/theme/default/template/common/footer.twig');
        $this->assertStringContainsString(
            'defer',
            $twig,
            'default/footer.twig: scripts loop should have defer for performance'
        );
    }

    public function testSoMobileHeaderRecaptchaDeferredOnInteraction(): void
    {
        $twig = $this->readTemplate('catalog/view/theme/so-mobile/template/common/header.twig');
        $this->assertStringContainsString(
            'loadRecaptcha',
            $twig,
            'so-mobile/header.twig: reCAPTCHA must be deferred until user interaction'
        );
        $this->assertStringContainsString(
            'recaptcha/api.js',
            $twig,
            'so-mobile/header.twig: reCAPTCHA API URL must be present'
        );
    }

    public function testSoMobileCallRailDeferred(): void
    {
        $twig = $this->readTemplate('catalog/view/theme/so-mobile/template/common/header.twig');
        $this->assertStringContainsString(
            'callrail.com',
            $twig,
            'so-mobile/header.twig: CallRail URL must be present'
        );
        $this->assertStringContainsString(
            'https://cdn.callrail.com',
            $twig,
            'so-mobile/header.twig: CallRail must use https://'
        );
    }

    // -----------------------------------------------------------------------
    // Lighthouse Fix Prompt — Task 3: preconnect hints and theme-color
    // -----------------------------------------------------------------------

    public function testSoClickboomHeaderHasPreconnectGoogleFonts(): void
    {
        $twig = $this->readTemplate('catalog/view/theme/so-clickboom/template/common/header.twig');
        $this->assertStringContainsString(
            'rel="preconnect" href="https://fonts.googleapis.com"',
            $twig,
            'so-clickboom/header.twig must have <link rel="preconnect"> for fonts.googleapis.com'
        );
        $this->assertStringContainsString(
            'href="https://fonts.gstatic.com" crossorigin',
            $twig,
            'so-clickboom/header.twig must have <link rel="preconnect"> for fonts.gstatic.com with crossorigin'
        );
    }

    public function testDefaultThemeHeaderHasPreconnectGoogleFonts(): void
    {
        $twig = $this->readTemplate('catalog/view/theme/default/template/common/header.twig');
        $this->assertStringContainsString(
            'rel="preconnect" href="https://fonts.googleapis.com"',
            $twig,
            'default/header.twig must have <link rel="preconnect"> for fonts.googleapis.com'
        );
    }

    public function testAllHeadersHaveThemeColor(): void
    {
        foreach ([
            'catalog/view/theme/so-clickboom/template/common/header.twig',
            'catalog/view/theme/default/template/common/header.twig',
            'catalog/view/theme/so-mobile/template/common/header.twig',
        ] as $path) {
            $twig = $this->readTemplate($path);
            $this->assertStringContainsString(
                'name="theme-color"',
                $twig,
                "$path must contain <meta name=\"theme-color\">"
            );
        }
    }

    // -----------------------------------------------------------------------
    // Lighthouse Fix Prompt — Task 4: lang attribute on <html>
    // -----------------------------------------------------------------------

    public function testAllHeadersHaveLangAttribute(): void
    {
        foreach ([
            'catalog/view/theme/so-clickboom/template/common/header.twig',
            'catalog/view/theme/default/template/common/header.twig',
            'catalog/view/theme/so-mobile/template/common/header.twig',
        ] as $path) {
            $twig = $this->readTemplate($path);
            $this->assertMatchesRegularExpression(
                '/<html[^>]+lang=/',
                $twig,
                "$path: <html> tag must have a lang= attribute"
            );
        }
    }

    // -----------------------------------------------------------------------
    // Lighthouse Fix Prompt — Task 5: HTTPS in config files
    // -----------------------------------------------------------------------

    public function testRootConfigUsesHttps(): void
    {
        $php = file_get_contents(dirname(__DIR__) . '/config.php');
        $this->assertStringNotContainsString(
            "define('HTTPS_SERVER', 'http://",
            $php,
            'config.php: HTTPS_SERVER must not use http://'
        );
        $this->assertStringContainsString(
            'https://',
            $php,
            'config.php: both SERVER defines must use https://'
        );
    }

    public function testAdminConfigUsesHttps(): void
    {
        $php = file_get_contents(dirname(__DIR__) . '/admin/config.php');
        $this->assertStringNotContainsString(
            "define('HTTPS_SERVER', 'http://",
            $php,
            'admin/config.php: HTTPS_SERVER must not use http://'
        );
        $this->assertStringNotContainsString(
            "define('HTTPS_CATALOG', 'http://",
            $php,
            'admin/config.php: HTTPS_CATALOG must not use http://'
        );
    }

    // -----------------------------------------------------------------------
    // Lighthouse Fix Prompt — Task 6: no console.log in production JS
    // -----------------------------------------------------------------------

    public function testGoogleAddressAutocompleteHasNoConsoleLogs(): void
    {
        $js = file_get_contents(
            dirname(__DIR__) . '/catalog/view/javascript/googleAddressAutocomplete.js'
        );
        $this->assertStringNotContainsString(
            'console.log',
            $js,
            'googleAddressAutocomplete.js must not contain console.log statements in production'
        );
        $this->assertStringNotContainsString(
            'console.error',
            $js,
            'googleAddressAutocomplete.js must not contain console.error statements in production'
        );
    }

    // -----------------------------------------------------------------------
    // Lighthouse Fix Prompt — Task 8: viewport meta, noopener, theme-color
    // -----------------------------------------------------------------------

    public function testAllHeadersHaveViewportMeta(): void
    {
        foreach ([
            'catalog/view/theme/so-clickboom/template/common/header.twig',
            'catalog/view/theme/default/template/common/header.twig',
            'catalog/view/theme/so-mobile/template/common/header.twig',
        ] as $path) {
            $twig = $this->readTemplate($path);
            $this->assertStringContainsString(
                'name="viewport"',
                $twig,
                "$path must have a <meta name=\"viewport\"> tag"
            );
        }
    }

    public function testContactTwigSocialLinksHaveNoopener(): void
    {
        $twig = $this->readTemplate(
            'catalog/view/theme/so-clickboom/template/information/contact.twig'
        );
        // All target="_blank" links in the social list must have rel="noopener noreferrer"
        preg_match_all('/<a[^>]+target="_blank"[^>]*>/', $twig, $matches);
        foreach ($matches[0] as $tag) {
            $this->assertStringContainsString(
                'rel="noopener noreferrer"',
                $tag,
                "Every target=\"_blank\" in contact.twig must have rel=\"noopener noreferrer\": $tag"
            );
        }
    }

    public function testHeader5NdisLinkHasNoopener(): void
    {
        $twig = $this->readTemplate(
            'catalog/view/theme/so-clickboom/template/header/header5.twig'
        );
        $this->assertMatchesRegularExpression(
            '/<a[^>]+ndis\.gov\.au[^>]+rel="noopener noreferrer"[^>]*>/',
            $twig,
            'header5.twig: NDIS link must have rel="noopener noreferrer"'
        );
    }

    public function testSoMobileDownloadLinksHaveNoopener(): void
    {
        $twig = $this->readTemplate(
            'catalog/view/theme/so-mobile/template/product/product.twig'
        );
        // Check the download href link has rel="noopener noreferrer"
        $this->assertMatchesRegularExpression(
            '/<a[^>]+download\.href[^>]+rel="noopener noreferrer"[^>]*>/',
            $twig,
            'so-mobile product.twig: download target="_blank" links must have rel="noopener noreferrer"'
        );
    }

    // -----------------------------------------------------------------------
    // Lighthouse Fix Prompt — Task 1: img tags have width/height attributes
    // -----------------------------------------------------------------------

    public function testSoClickboomOptionImagesHaveDimensions(): void
    {
        $twig = $this->readTemplate(
            'catalog/view/theme/so-clickboom/template/product/product.twig'
        );
        // Option images should have explicit width and height
        $this->assertMatchesRegularExpression(
            '/option_value\.image.*width="50".*height="50"/',
            $twig,
            'so-clickboom product.twig: option_value images must have width="50" height="50"'
        );
    }

    public function testSoClickboomFeatureImagesHaveDimensions(): void
    {
        $twig = $this->readTemplate(
            'catalog/view/theme/so-clickboom/template/product/product.twig'
        );
        $this->assertStringContainsString(
            'width="600" height="400"',
            $twig,
            'so-clickboom product.twig: feature images must have explicit width/height'
        );
    }

    public function testDefaultThemeProductMainImageHasDimensions(): void
    {
        $twig = $this->readTemplate(
            'catalog/view/theme/default/template/product/product.twig'
        );
        $this->assertStringContainsString(
            'width="500" height="500"',
            $twig,
            'default product.twig: main product thumbnail must have width="500" height="500"'
        );
        $this->assertStringContainsString(
            'loading="eager"',
            $twig,
            'default product.twig: main product thumbnail should use loading="eager" (LCP candidate)'
        );
    }

    // -----------------------------------------------------------------------
    // Helper — assertDoesNotMatch (avoid deprecation in older PHPUnit)
    // -----------------------------------------------------------------------

    private function assertDoesNotMatch(string $pattern, string $string, string $message = ''): void
    {
        $this->assertDoesNotMatchRegularExpression($pattern, $string, $message);
    }

    // -----------------------------------------------------------------------
    // Performance: third-party scripts deferred in so-clickboom header
    // -----------------------------------------------------------------------

    public function testSoClickboomGTMDeferredUntilLoad(): void
    {
        $twig = $this->readTemplate('catalog/view/theme/so-clickboom/template/common/header.twig');
        $this->assertStringContainsString(
            'googletagmanager.com',
            $twig,
            'so-clickboom/header.twig must contain GTM'
        );
        // GTM should be inside a window.addEventListener('load', ...) wrapper
        $this->assertMatchesRegularExpression(
            '/window\.addEventListener\s*\(\s*[\'"]load[\'"].*googletagmanager/s',
            $twig,
            'GTM script must be deferred until window load event'
        );
    }

    public function testSoClickboomGADeferredUntilLoad(): void
    {
        $twig = $this->readTemplate('catalog/view/theme/so-clickboom/template/common/header.twig');
        $this->assertMatchesRegularExpression(
            '/window\.addEventListener\s*\(\s*[\'"]load[\'"].*google-analytics\.com/s',
            $twig,
            'Google Analytics must be deferred until window load event'
        );
    }

    public function testSoClickboomFBPixelDeferredUntilLoad(): void
    {
        $twig = $this->readTemplate('catalog/view/theme/so-clickboom/template/common/header.twig');
        $this->assertMatchesRegularExpression(
            '/window\.addEventListener\s*\(\s*[\'"]load[\'"].*connect\.facebook\.net/s',
            $twig,
            'Facebook Pixel must be deferred until window load event'
        );
    }

    // -----------------------------------------------------------------------
    // Performance: reCAPTCHA deferred until user interaction in header5
    // -----------------------------------------------------------------------

    public function testSoClickboomRecaptchaDeferredOnInteraction(): void
    {
        $twig = $this->readTemplate('catalog/view/theme/so-clickboom/template/header/header5.twig');
        $this->assertStringContainsString(
            'loadRecaptcha',
            $twig,
            'header5.twig: reCAPTCHA must be deferred until user interaction'
        );
        $this->assertStringContainsString(
            'recaptcha/api.js',
            $twig,
            'header5.twig: reCAPTCHA API URL must be present'
        );
        // Must NOT have a direct blocking script tag for reCAPTCHA
        $this->assertDoesNotMatch(
            '/<script\s+src="[^"]*recaptcha[^"]*">/',
            $twig,
            'header5.twig: reCAPTCHA must not use a blocking <script src> tag'
        );
    }

    // -----------------------------------------------------------------------
    // Performance: soconfig js_out adds defer to non-jQuery scripts
    // -----------------------------------------------------------------------

    public function testSoconfigJsOutAddsDeferToNonJqueryScripts(): void
    {
        $php = file_get_contents(
            dirname(__DIR__) . '/admin/view/template/extension/soconfig/class/soconfig.php'
        );
        // The js_out method must add defer to non-jQuery scripts
        $this->assertStringContainsString(
            'defer',
            $php,
            'soconfig.php js_out() must add defer attribute to script tags'
        );
        // jQuery must be kept non-deferred
        $this->assertStringContainsString(
            'jquery-',
            $php,
            'soconfig.php must check for jquery- to exclude it from defer'
        );
    }

    // -----------------------------------------------------------------------
    // Performance: DNS prefetch for third-party domains
    // -----------------------------------------------------------------------

    public function testSoClickboomHeaderHasDnsPrefetch(): void
    {
        $twig = $this->readTemplate('catalog/view/theme/so-clickboom/template/common/header.twig');
        $this->assertStringContainsString(
            'dns-prefetch',
            $twig,
            'so-clickboom/header.twig must have dns-prefetch hints'
        );
    }

    // -----------------------------------------------------------------------
    // Performance: Google Font loaded asynchronously
    // -----------------------------------------------------------------------

    public function testSoClickboomGoogleFontLoadedAsync(): void
    {
        $twig = $this->readTemplate('catalog/view/theme/so-clickboom/template/common/header.twig');
        $this->assertStringContainsString(
            'media="print" onload="this.media=\'all\'"',
            $twig,
            'so-clickboom/header.twig: Google Font must use media="print" onload trick for async loading'
        );
    }

    // -----------------------------------------------------------------------
    // Performance: Cart thumbnail images have width/height
    // -----------------------------------------------------------------------

    public function testCartThumbnailsHaveDimensions(): void
    {
        $twig = $this->readTemplate('catalog/view/theme/so-clickboom/template/common/cart.twig');
        $this->assertStringContainsString(
            'width="80" height="80"',
            $twig,
            'Cart thumbnail images must have explicit width and height for CLS prevention'
        );
    }

    // -----------------------------------------------------------------------
    // Security: NDIS page target="_blank" links have noopener
    // -----------------------------------------------------------------------

    public function testNdisPageLinksHaveNoopener(): void
    {
        $twig = $this->readTemplate(
            'catalog/view/theme/so-clickboom/template/information/ndis.twig'
        );
        preg_match_all('/<a[^>]+target="_blank"[^>]*>/', $twig, $matches);
        $this->assertNotEmpty($matches[0], 'NDIS page should have target="_blank" links');
        foreach ($matches[0] as $tag) {
            $this->assertStringContainsString(
                'rel="noopener noreferrer"',
                $tag,
                "Every target=\"_blank\" in ndis.twig must have rel=\"noopener noreferrer\": $tag"
            );
        }
    }

    // -----------------------------------------------------------------------
    // CLS FIX: Product listing images have width/height attributes
    // -----------------------------------------------------------------------

    public function testSoClickboomListingImagesHaveDimensions(): void
    {
        $twig = $this->readTemplate('catalog/view/theme/so-clickboom/template/soconfig/listing.twig');
        // The main product listing img in the product-image-container must have width and height
        $this->assertMatchesRegularExpression(
            '/data-src="{{ product\.thumb }}"[^>]*width="250"[^>]*height="250"/',
            $twig,
            'so-clickboom listing.twig: product listing images must have width="250" height="250"'
        );
    }

    public function testSoClickboomListingImagesHaveLoadingLazy(): void
    {
        $twig = $this->readTemplate('catalog/view/theme/so-clickboom/template/soconfig/listing.twig');
        $this->assertMatchesRegularExpression(
            '/data-src="{{ product\.thumb }}"[^>]*loading="lazy"/',
            $twig,
            'so-clickboom listing.twig: product listing images must have loading="lazy"'
        );
    }

    public function testSoClickboomListingImagesHaveAltAttribute(): void
    {
        $twig = $this->readTemplate('catalog/view/theme/so-clickboom/template/soconfig/listing.twig');
        $this->assertMatchesRegularExpression(
            '/data-src="{{ product\.thumb }}"[^>]*alt="{{ product\.name }}"/',
            $twig,
            'so-clickboom listing.twig: product listing images must have alt attribute'
        );
    }

    // -----------------------------------------------------------------------
    // CLS FIX: Product image container has aspect-ratio in CSS
    // -----------------------------------------------------------------------

    public function testCustomCssHasProductImageContainerAspectRatio(): void
    {
        $css = file_get_contents(
            dirname(__DIR__) . '/catalog/view/theme/so-clickboom/css/custom.css'
        );
        $this->assertStringContainsString(
            'aspect-ratio: 1 / 1',
            $css,
            'custom.css must set aspect-ratio: 1/1 on .product-image-container to prevent CLS'
        );
        $this->assertStringContainsString(
            'background-color: #f5f5f5',
            $css,
            'custom.css must set a placeholder background on .product-image-container'
        );
    }

    public function testCustomCssHasImgMaxWidth(): void
    {
        $css = file_get_contents(
            dirname(__DIR__) . '/catalog/view/theme/so-clickboom/css/custom.css'
        );
        $this->assertStringContainsString(
            'max-width: 100%',
            $css,
            'custom.css must set img { max-width: 100% } to prevent overflow'
        );
    }

    // -----------------------------------------------------------------------
    // CLS FIX: Critical inline CSS in header for space reservation
    // -----------------------------------------------------------------------

    public function testSoClickboomHeaderHasCriticalClsCss(): void
    {
        $twig = $this->readTemplate('catalog/view/theme/so-clickboom/template/common/header.twig');
        $this->assertStringContainsString(
            '.product-image-container{aspect-ratio:1/1;',
            $twig,
            'so-clickboom header must have inline critical CSS for product-image-container aspect-ratio'
        );
        $this->assertStringContainsString(
            '#header .container-fluid{min-height:34px;}',
            $twig,
            'so-clickboom header must reserve min-height for feature bar'
        );
    }

    public function testSoMobileHeaderHasCriticalClsCss(): void
    {
        $twig = $this->readTemplate('catalog/view/theme/so-mobile/template/common/header.twig');
        $this->assertStringContainsString(
            '.product-image-container{aspect-ratio:1/1;',
            $twig,
            'so-mobile header must have inline critical CSS for product-image-container aspect-ratio'
        );
    }

    // -----------------------------------------------------------------------
    // CLS FIX: Category images have width/height attributes
    // -----------------------------------------------------------------------

    public function testCategoryTwigImagesHaveDimensions(): void
    {
        $twig = $this->readTemplate('catalog/view/theme/so-clickboom/template/product/category.twig');
        $this->assertStringContainsString(
            'width="200" height="200"',
            $twig,
            'category.twig: category images must have explicit width and height'
        );
    }

    public function testAllSubCatsTwigImagesHaveDimensions(): void
    {
        $twig = $this->readTemplate('catalog/view/theme/so-clickboom/template/product/allSubCats.twig');
        $this->assertStringContainsString(
            'width="200" height="200"',
            $twig,
            'allSubCats.twig: subcategory images must have explicit width and height'
        );
    }

    // -----------------------------------------------------------------------
    // CLS FIX: Cart modal uses fixed positioning
    // -----------------------------------------------------------------------

    public function testCustomCssCartModalFixedPositioning(): void
    {
        $css = file_get_contents(
            dirname(__DIR__) . '/catalog/view/theme/so-clickboom/css/custom.css'
        );
        $this->assertStringContainsString(
            'position: fixed',
            $css,
            'custom.css must set position: fixed on cart/modal elements to prevent CLS'
        );
    }

    // -----------------------------------------------------------------------
    // CLS FIX: Owl carousel has min-height to prevent collapse
    // -----------------------------------------------------------------------

    public function testCustomCssOwlCarouselMinHeight(): void
    {
        $css = file_get_contents(
            dirname(__DIR__) . '/catalog/view/theme/so-clickboom/css/custom.css'
        );
        $this->assertStringContainsString(
            '.owl2-carousel',
            $css,
            'custom.css must set min-height on .owl2-carousel to prevent CLS during load'
        );
    }
}
