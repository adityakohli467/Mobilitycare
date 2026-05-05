<?php
/**
 * PHP 8.1 Compatibility Tests for Mobilitycare OpenCart
 *
 * Verifies that all PHP 8.0/8.1 breaking changes have been fixed:
 * - each() removed → replaced with array_key_first() / foreach
 * - get_magic_quotes_runtime() removed → calls deleted
 * - set_magic_quotes_runtime() removed → calls deleted
 * - create_function() removed → replaced with closures
 * - {} string access removed → replaced with []
 * - strftime() deprecated → replaced with date()
 */

use PHPUnit\Framework\TestCase;

class PHP81CompatibilityTest extends TestCase
{
    private string $baseDir;

    protected function setUp(): void
    {
        $this->baseDir = dirname(__DIR__);
    }

    private function readFile(string $relativePath): string
    {
        $path = $this->baseDir . '/' . ltrim($relativePath, '/');
        $this->assertFileExists($path, "File not found: $relativePath");
        return file_get_contents($path);
    }

    // ===================================================================
    // Cache_Lite — each() removed (15 Lite.php files)
    // ===================================================================

    private function getCacheLiteModules(): array
    {
        return [
            'categories',
            'category_slider',
            'deals',
            'extra_slider',
            'filter_shop_by',
            'home_slider',
            'html_content',
            'instagram_gallery',
            'latest_blog',
            'listing_tabs',
            'megamenu',
            'newletter_custom_popup',
            'popular_tags',
            'searchpro',
            'super_category',
        ];
    }

    /**
     * @dataProvider cacheLiteModuleProvider
     */
    public function testCacheLiteLitePhpDoesNotUseEach(string $module): void
    {
        $content = $this->readFile("system/library/so/{$module}/Cache_Lite/Lite.php");
        $this->assertDoesNotMatchRegularExpression(
            '/\beach\s*\(/',
            $content,
            "Cache_Lite/Lite.php in '{$module}' still uses removed each() function"
        );
    }

    /**
     * @dataProvider cacheLiteModuleProvider
     */
    public function testCacheLiteLitePhpUsesArrayKeyFirst(string $module): void
    {
        $content = $this->readFile("system/library/so/{$module}/Cache_Lite/Lite.php");
        $this->assertStringContainsString(
            'array_key_first',
            $content,
            "Cache_Lite/Lite.php in '{$module}' should use array_key_first() instead of each()"
        );
    }

    /**
     * @dataProvider cacheLiteModuleProvider
     */
    public function testCacheLiteLitePhpDoesNotUseGetMagicQuotesRuntime(string $module): void
    {
        $content = $this->readFile("system/library/so/{$module}/Cache_Lite/Lite.php");
        $this->assertDoesNotMatchRegularExpression(
            '/get_magic_quotes_runtime\s*\(/',
            $content,
            "Cache_Lite/Lite.php in '{$module}' still uses removed get_magic_quotes_runtime()"
        );
    }

    /**
     * @dataProvider cacheLiteModuleProvider
     */
    public function testCacheLiteLitePhpDoesNotUseSetMagicQuotesRuntime(string $module): void
    {
        $content = $this->readFile("system/library/so/{$module}/Cache_Lite/Lite.php");
        $this->assertDoesNotMatchRegularExpression(
            '/set_magic_quotes_runtime\s*\(/',
            $content,
            "Cache_Lite/Lite.php in '{$module}' still uses removed set_magic_quotes_runtime()"
        );
    }

    // ===================================================================
    // Cache_Lite — each() removed (15 Function.php files)
    // ===================================================================

    /**
     * @dataProvider cacheLiteModuleProvider
     */
    public function testCacheLiteFunctionPhpDoesNotUseEach(string $module): void
    {
        $content = $this->readFile("system/library/so/{$module}/Cache_Lite/Lite/Function.php");
        $this->assertDoesNotMatchRegularExpression(
            '/\beach\s*\(/',
            $content,
            "Cache_Lite/Lite/Function.php in '{$module}' still uses removed each() function"
        );
    }

    /**
     * @dataProvider cacheLiteModuleProvider
     */
    public function testCacheLiteFunctionPhpUsesForeach(string $module): void
    {
        $content = $this->readFile("system/library/so/{$module}/Cache_Lite/Lite/Function.php");
        $this->assertStringContainsString(
            'foreach ($options as $name => $value)',
            $content,
            "Cache_Lite/Lite/Function.php in '{$module}' should use foreach instead of each()"
        );
    }

    public static function cacheLiteModuleProvider(): array
    {
        return [
            'categories'              => ['categories'],
            'category_slider'         => ['category_slider'],
            'deals'                   => ['deals'],
            'extra_slider'            => ['extra_slider'],
            'filter_shop_by'          => ['filter_shop_by'],
            'home_slider'             => ['home_slider'],
            'html_content'            => ['html_content'],
            'instagram_gallery'       => ['instagram_gallery'],
            'latest_blog'             => ['latest_blog'],
            'listing_tabs'            => ['listing_tabs'],
            'megamenu'                => ['megamenu'],
            'newletter_custom_popup'  => ['newletter_custom_popup'],
            'popular_tags'            => ['popular_tags'],
            'searchpro'               => ['searchpro'],
            'super_category'          => ['super_category'],
        ];
    }

    // ===================================================================
    // Alipay Cross — each() removed
    // ===================================================================

    public function testAlipayCreateLinkstringDoesNotUseEach(): void
    {
        $content = $this->readFile('catalog/model/extension/payment/alipay_cross.php');
        $this->assertDoesNotMatchRegularExpression(
            '/\beach\s*\(/',
            $content,
            'alipay_cross.php still uses removed each() function'
        );
    }

    public function testAlipayCreateLinkstringUsesForeach(): void
    {
        $content = $this->readFile('catalog/model/extension/payment/alipay_cross.php');
        $this->assertStringContainsString(
            'foreach ($para as $key => $val)',
            $content,
            'alipay_cross.php should use foreach instead of each()'
        );
    }

    public function testAlipayCreateLinkstringUsesStrlenNotCount(): void
    {
        $content = $this->readFile('catalog/model/extension/payment/alipay_cross.php');
        // The original had count($arg)-2 which was wrong; it should use strlen
        $this->assertStringContainsString(
            'strlen($arg)',
            $content,
            'alipay_cross.php should use strlen() for string length, not count()'
        );
    }

    // ===================================================================
    // FraudLabsPro — create_function() removed
    // ===================================================================

    public function testFraudLabsProDoesNotUseCreateFunction(): void
    {
        $content = $this->readFile('admin/controller/extension/fraud/fraudlabspro.php');
        $this->assertDoesNotMatchRegularExpression(
            '/create_function\s*\(/',
            $content,
            'fraudlabspro.php still uses removed create_function()'
        );
    }

    public function testFraudLabsProUsesAnonymousFunction(): void
    {
        $content = $this->readFile('admin/controller/extension/fraud/fraudlabspro.php');
        $this->assertStringContainsString(
            'function($matches)',
            $content,
            'fraudlabspro.php should use an anonymous closure instead of create_function()'
        );
    }

    // ===================================================================
    // Google_Utils — curly brace string access {} removed
    // ===================================================================

    public function testGoogleUtilsDoesNotUseCurlyBraceAccess(): void
    {
        $content = $this->readFile('system/library/so_social/src/service/Google_Utils.php');
        $this->assertDoesNotMatchRegularExpression(
            '/\$str\{/',
            $content,
            'Google_Utils.php still uses removed curly brace string access $str{}'
        );
    }

    public function testGoogleUtilsUsesBracketAccess(): void
    {
        $content = $this->readFile('system/library/so_social/src/service/Google_Utils.php');
        $this->assertStringContainsString(
            '$str[$ret]',
            $content,
            'Google_Utils.php should use bracket access $str[$ret] instead of $str{$ret}'
        );
    }

    // ===================================================================
    // Payment Extensions — strftime() deprecated
    // ===================================================================

    public function testSagepayServerCreditCardDoesNotUseStrftime(): void
    {
        $content = $this->readFile('catalog/controller/extension/credit_card/sagepay_server.php');
        $this->assertDoesNotMatchRegularExpression(
            '/strftime\s*\(/',
            $content,
            'sagepay_server credit card controller still uses deprecated strftime()'
        );
    }

    public function testSagepayServerCreditCardUsesDate(): void
    {
        $content = $this->readFile('catalog/controller/extension/credit_card/sagepay_server.php');
        $this->assertStringContainsString(
            "date('YmdHis')",
            $content,
            'sagepay_server credit card should use date() instead of strftime()'
        );
    }

    public function testSagepayDirectCreditCardDoesNotUseStrftime(): void
    {
        $content = $this->readFile('catalog/controller/extension/credit_card/sagepay_direct.php');
        $this->assertDoesNotMatchRegularExpression(
            '/strftime\s*\(/',
            $content,
            'sagepay_direct credit card controller still uses deprecated strftime()'
        );
    }

    public function testSagepayDirectCreditCardUsesDateForMonthAndYear(): void
    {
        $content = $this->readFile('catalog/controller/extension/credit_card/sagepay_direct.php');
        $this->assertStringContainsString(
            "date('F'",
            $content,
            'sagepay_direct should use date(\'F\') for month names instead of strftime(\'%B\')'
        );
        $this->assertStringContainsString(
            "date('Y'",
            $content,
            'sagepay_direct should use date(\'Y\') for year instead of strftime(\'%Y\')'
        );
    }

    public function testCardconnectDoesNotUseStrftime(): void
    {
        $content = $this->readFile('catalog/model/extension/payment/cardconnect.php');
        $this->assertDoesNotMatchRegularExpression(
            '/strftime\s*\(/',
            $content,
            'cardconnect.php still uses deprecated strftime()'
        );
    }

    public function testWebPaymentSoftwareDoesNotUseStrftime(): void
    {
        $content = $this->readFile('catalog/controller/extension/payment/web_payment_software.php');
        $this->assertDoesNotMatchRegularExpression(
            '/strftime\s*\(/',
            $content,
            'web_payment_software.php still uses deprecated strftime()'
        );
    }

    public function testSagepayServerModelDoesNotUseStrftime(): void
    {
        $content = $this->readFile('catalog/model/extension/payment/sagepay_server.php');
        $this->assertDoesNotMatchRegularExpression(
            '/strftime\s*\(/',
            $content,
            'sagepay_server model still uses deprecated strftime()'
        );
    }

    public function testSagepayDirectModelDoesNotUseStrftime(): void
    {
        $content = $this->readFile('catalog/model/extension/payment/sagepay_direct.php');
        $this->assertDoesNotMatchRegularExpression(
            '/strftime\s*\(/',
            $content,
            'sagepay_direct model still uses deprecated strftime()'
        );
    }

    public function testRealexRemoteDoesNotUseStrftime(): void
    {
        $content = $this->readFile('catalog/model/extension/payment/realex_remote.php');
        $this->assertDoesNotMatchRegularExpression(
            '/strftime\s*\(/',
            $content,
            'realex_remote.php still uses deprecated strftime()'
        );
    }

    public function testGlobalpayRemoteDoesNotUseStrftime(): void
    {
        $content = $this->readFile('catalog/model/extension/payment/globalpay_remote.php');
        $this->assertDoesNotMatchRegularExpression(
            '/strftime\s*\(/',
            $content,
            'globalpay_remote.php still uses deprecated strftime()'
        );
    }

    // ===================================================================
    // Verify no remaining FATAL PHP 8.0/8.1 issues in entire codebase
    // ===================================================================

    public function testNoRemainingEachCallsInSoLibrary(): void
    {
        $soDir = $this->baseDir . '/system/library/so/';
        $this->assertDirectoryExists($soDir);

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($soDir, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') continue;
            $content = file_get_contents($file->getPathname());
            // Skip jQuery .each() in embedded JS inside PHP files
            if (preg_match('/\beach\s*\(\s*\$/', $content)) {
                $relativePath = str_replace($this->baseDir . '/', '', $file->getPathname());
                $this->fail("Found remaining each() call in: {$relativePath}");
            }
        }
        $this->assertTrue(true, 'No each() calls found in SO library');
    }

    public function testNoRemainingMagicQuotesInSoLibrary(): void
    {
        $soDir = $this->baseDir . '/system/library/so/';
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($soDir, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') continue;
            $content = file_get_contents($file->getPathname());
            if (strpos($content, 'get_magic_quotes_runtime') !== false || strpos($content, 'set_magic_quotes_runtime') !== false) {
                $relativePath = str_replace($this->baseDir . '/', '', $file->getPathname());
                $this->fail("Found remaining magic_quotes call in: {$relativePath}");
            }
        }
        $this->assertTrue(true, 'No magic_quotes calls found in SO library');
    }

    // ===================================================================
    // Cache_Lite _memoryCacheAdd — functional correctness
    // ===================================================================

    public function testCacheLiteMemoryCacheAddLogicIsCorrect(): void
    {
        // Verify the replacement logic: array_key_first() returns the first key
        // which is the oldest entry (FIFO eviction) - same behavior as each()
        $content = $this->readFile('system/library/so/super_category/Cache_Lite/Lite.php');

        // Must have: $key = array_key_first(...)
        $this->assertStringContainsString(
            '$key = array_key_first($this->_memoryCachingArray)',
            $content,
            'Cache eviction must use array_key_first() to get oldest entry'
        );

        // Must still have: unset(...$key...)
        $this->assertStringContainsString(
            'unset($this->_memoryCachingArray[$key])',
            $content,
            'Cache eviction must still unset the oldest entry'
        );
    }

    // ===================================================================
    // Payment strftime → date equivalence tests
    // ===================================================================

    public function testDateFProducesFullMonthName(): void
    {
        // strftime('%B') ≡ date('F') — full month name
        $timestamp = mktime(0, 0, 0, 3, 1, 2000);
        $this->assertEquals('March', date('F', $timestamp));
    }

    public function testDateYProduces4DigitYear(): void
    {
        // strftime('%Y') ≡ date('Y') — 4-digit year
        $timestamp = mktime(0, 0, 0, 1, 1, 2026);
        $this->assertEquals('2026', date('Y', $timestamp));
    }

    public function testDateYmdHisProducesTimestamp(): void
    {
        // strftime('%Y%m%d%H%M%S') ≡ date('YmdHis')
        $result = date('YmdHis');
        $this->assertMatchesRegularExpression(
            '/^\d{14}$/',
            $result,
            'date(YmdHis) must produce a 14-digit timestamp string'
        );
    }

    public function testDateSmallYProduces2DigitYear(): void
    {
        // strftime('%y') ≡ date('y') — 2-digit year
        $timestamp = mktime(0, 0, 0, 1, 1, 2026);
        $this->assertEquals('26', date('y', $timestamp));
    }
}
