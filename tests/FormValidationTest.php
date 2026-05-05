<?php
/**
 * Form Validation & Site Integration Tests for Mobilitycare
 *
 * Tests all forms across the website (desktop so-clickboom and mobile so-mobile themes):
 * - Contact form
 * - Demo request form
 * - Trial request form
 * - Warranty claim form
 * - Quote request form
 * - Find dealer form
 * - AutoChair enquiry form
 * - LightDrive enquiry form
 * - Funding support form
 * - Product enquiry/quote form
 * - Login & Registration forms
 * - Search form
 * - Newsletter signup
 * - Checkout cart
 *
 * Also validates that mobile theme (so-mobile) forms match desktop functionality.
 */

use PHPUnit\Framework\TestCase;

class FormValidationTest extends TestCase
{
    private string $baseDir;
    private string $siteUrl;

    protected function setUp(): void
    {
        $this->baseDir = dirname(__DIR__);
        $this->siteUrl = getenv('SITE_URL') ?: 'https://www.mobilitycare.net.au';
    }

    private function readFile(string $relativePath): string
    {
        $path = $this->baseDir . '/' . ltrim($relativePath, '/');
        $this->assertFileExists($path, "File not found: $relativePath");
        return file_get_contents($path);
    }

    /**
     * Helper: HTTP request to a URL (for live integration tests)
     */
    private function httpGet(string $url): array
    {
        $context = stream_context_create([
            'http' => [
                'timeout' => 15,
                'follow_location' => true,
                'header' => "User-Agent: MobilitycareTestSuite/1.0\r\n",
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);

        $response = @file_get_contents($url, false, $context);
        $statusCode = 0;

        if (isset($http_response_header[0])) {
            preg_match('/HTTP\/\d\.?\d?\s+(\d{3})/', $http_response_header[0], $m);
            $statusCode = (int)($m[1] ?? 0);
        }

        return [
            'status' => $statusCode,
            'body'   => $response ?: '',
        ];
    }

    // ===================================================================
    // SECTION 1: Controller Validation Logic (Unit Tests)
    // ===================================================================

    // -------------------------------------------------------------------
    // Contact Form Controller
    // -------------------------------------------------------------------

    public function testContactControllerExists(): void
    {
        $this->assertFileExists(
            $this->baseDir . '/catalog/controller/information/contact.php',
            'Contact form controller must exist'
        );
    }

    public function testContactControllerValidatesNameLength(): void
    {
        $content = $this->readFile('catalog/controller/information/contact.php');
        $this->assertMatchesRegularExpression(
            '/utf8_strlen.*name.*[<>].*[0-9]/s',
            $content,
            'Contact controller must validate name length'
        );
    }

    public function testContactControllerValidatesEmail(): void
    {
        $content = $this->readFile('catalog/controller/information/contact.php');
        $this->assertMatchesRegularExpression(
            '/filter_var.*FILTER_VALIDATE_EMAIL|utf8_strlen.*email/s',
            $content,
            'Contact controller must validate email format'
        );
    }

    public function testContactControllerValidatesEnquiryLength(): void
    {
        $content = $this->readFile('catalog/controller/information/contact.php');
        $this->assertMatchesRegularExpression(
            '/utf8_strlen.*enquiry.*[<>].*[0-9]/s',
            $content,
            'Contact controller must validate enquiry length'
        );
    }

    // -------------------------------------------------------------------
    // Demo Request Form Controller
    // -------------------------------------------------------------------

    public function testDemoRequestControllerExists(): void
    {
        $this->assertFileExists(
            $this->baseDir . '/catalog/controller/information/demo_request.php',
            'Demo request form controller must exist'
        );
    }

    public function testDemoRequestValidatesRequiredFields(): void
    {
        $content = $this->readFile('catalog/controller/information/demo_request.php');
        $this->assertStringContainsString('fullname', $content, 'Must validate fullname');
        $this->assertStringContainsString('email', $content, 'Must validate email');
        $this->assertStringContainsString('phone', $content, 'Must validate phone');
    }

    // -------------------------------------------------------------------
    // Trial Request Form Controller
    // -------------------------------------------------------------------

    public function testTrialRequestControllerExists(): void
    {
        $this->assertFileExists(
            $this->baseDir . '/catalog/controller/information/trial_request.php',
            'Trial request form controller must exist'
        );
    }

    public function testTrialRequestValidatesRequiredFields(): void
    {
        $content = $this->readFile('catalog/controller/information/trial_request.php');
        $this->assertStringContainsString('fullname', $content, 'Must validate fullname');
        $this->assertStringContainsString('email', $content, 'Must validate email');
        $this->assertStringContainsString('phone', $content, 'Must validate phone');
        $this->assertStringContainsString('organisation', $content, 'Must validate organisation');
        $this->assertStringContainsString('client_fullname', $content, 'Must validate client_fullname');
    }

    // -------------------------------------------------------------------
    // Warranty Claim Form Controller
    // -------------------------------------------------------------------

    public function testWarrantyClaimControllerExists(): void
    {
        $this->assertFileExists(
            $this->baseDir . '/catalog/controller/information/warranty_claim.php',
            'Warranty claim form controller must exist'
        );
    }

    public function testWarrantyClaimValidatesRequiredFields(): void
    {
        $content = $this->readFile('catalog/controller/information/warranty_claim.php');
        $this->assertStringContainsString('full_name', $content, 'Must validate full_name');
        $this->assertStringContainsString('email', $content, 'Must validate email');
        $this->assertStringContainsString('phone_number', $content, 'Must validate phone_number');
    }

    // -------------------------------------------------------------------
    // Quote Request Form Controller
    // -------------------------------------------------------------------

    public function testQuoteRequestControllerExists(): void
    {
        $this->assertFileExists(
            $this->baseDir . '/catalog/controller/information/quote_request.php',
            'Quote request form controller must exist'
        );
    }

    public function testQuoteRequestValidatesRequiredFields(): void
    {
        $content = $this->readFile('catalog/controller/information/quote_request.php');
        $this->assertStringContainsString('fullname', $content, 'Must validate fullname');
        $this->assertStringContainsString('email', $content, 'Must validate email');
        $this->assertStringContainsString('phone', $content, 'Must validate phone');
    }

    // -------------------------------------------------------------------
    // Find Dealer Form Controller
    // -------------------------------------------------------------------

    public function testFindDealerControllerExists(): void
    {
        $this->assertFileExists(
            $this->baseDir . '/catalog/controller/information/find_dealer_form.php',
            'Find dealer form controller must exist'
        );
    }

    public function testFindDealerValidatesRequiredFields(): void
    {
        $content = $this->readFile('catalog/controller/information/find_dealer_form.php');
        $this->assertStringContainsString('fullname', $content, 'Must validate fullname');
        $this->assertStringContainsString('email', $content, 'Must validate email');
        $this->assertStringContainsString('phone', $content, 'Must validate phone');
    }

    // -------------------------------------------------------------------
    // AutoChair Enquiry Form
    // -------------------------------------------------------------------

    public function testAutoChairEnquiryTemplateExists(): void
    {
        $this->assertFileExists(
            $this->baseDir . '/catalog/view/theme/so-clickboom/template/information/autochairEnquiry.twig',
            'AutoChair enquiry template must exist'
        );
    }

    public function testAutoChairEnquiryHasFormTag(): void
    {
        $content = $this->readFile('catalog/view/theme/so-clickboom/template/information/autochairEnquiry.twig');
        $this->assertStringContainsString('<form', $content, 'AutoChair template must contain a form');
        $this->assertStringContainsString('{{ action }}', $content, 'AutoChair form must have an action');
    }

    /**
     * @dataProvider autochairTemplateProvider
     */
    public function testAutoChairAjaxValidateUrlIsAbsolute(string $theme): void
    {
        $content = $this->readFile("catalog/view/theme/{$theme}/template/information/autochairEnquiry.twig");
        $this->assertStringContainsString(
            'data-validate-url="/index.php?route=information/autochairEnquiry/validateAjax"',
            $content,
            "AutoChair form in {$theme} must use absolute AJAX validate URL (leading /)"
        );
    }

    /**
     * @dataProvider autochairTemplateProvider
     */
    public function testAutoChairContactTypeHasRequired(string $theme): void
    {
        $content = $this->readFile("catalog/view/theme/{$theme}/template/information/autochairEnquiry.twig");
        // Find the contact_type select and check it has required attribute
        $this->assertMatchesRegularExpression(
            '/name="contact_type"[^>]*required/',
            $content,
            "AutoChair contact_type select in {$theme} must have required attribute"
        );
    }

    /**
     * @dataProvider autochairTemplateProvider
     */
    public function testAutoChairFormIncludesAjaxValidateScript(string $theme): void
    {
        $content = $this->readFile("catalog/view/theme/{$theme}/template/information/autochairEnquiry.twig");
        $this->assertStringContainsString(
            'form-ajax-validate.js',
            $content,
            "AutoChair template in {$theme} must include the shared AJAX validation script"
        );
    }

    public function testAutoChairControllerRedirectsToThankYouPage(): void
    {
        $content = $this->readFile('catalog/controller/information/autochairEnquiry.php');
        $this->assertStringContainsString(
            "redirect('/thank-you-autochair')",
            $content,
            'AutoChair controller must redirect to /thank-you-autochair on success'
        );
    }

    public function testAutoChairControllerSavesToDatabase(): void
    {
        $content = $this->readFile('catalog/controller/information/autochairEnquiry.php');
        $this->assertStringContainsString(
            'addAutochairEnquiry',
            $content,
            'AutoChair controller must save enquiry to database'
        );
    }

    public function testAutoChairControllerSendsMail(): void
    {
        $content = $this->readFile('catalog/controller/information/autochairEnquiry.php');
        $this->assertStringContainsString(
            '$mail->send()',
            $content,
            'AutoChair controller must send admin notification email'
        );
        $this->assertStringContainsString(
            '$customerMail->send()',
            $content,
            'AutoChair controller must send customer confirmation email'
        );
    }

    public function testAutoChairControllerHasValidateAjaxMethod(): void
    {
        $content = $this->readFile('catalog/controller/information/autochairEnquiry.php');
        $this->assertStringContainsString(
            'public function validateAjax()',
            $content,
            'AutoChair controller must have validateAjax method for AJAX validation'
        );
    }

    public function testAutoChairThankYouControllerMethodExists(): void
    {
        $content = $this->readFile('catalog/controller/information/form_success.php');
        $this->assertStringContainsString(
            'public function autochair()',
            $content,
            'form_success controller must have autochair() method'
        );
    }

    public function testAutoChairModelMethodExists(): void
    {
        $content = $this->readFile('catalog/model/catalog/demo_request.php');
        $this->assertStringContainsString(
            'function addAutochairEnquiry',
            $content,
            'demo_request model must have addAutochairEnquiry method'
        );
    }

    public function testAutoChairAdminPageExists(): void
    {
        $content = $this->readFile('admin/controller/catalog/form_request.php');
        $this->assertStringContainsString(
            'AutochairEnq',
            $content,
            'Admin form_request controller must have AutochairEnq method'
        );
    }

    public static function autochairTemplateProvider(): array
    {
        return [
            'desktop (so-clickboom)' => ['so-clickboom'],
            'mobile (so-mobile)'     => ['so-mobile'],
        ];
    }

    /**
     * @group integration
     */
    public function testAutoChairFormPageReturns200(): void
    {
        if (!getenv('SITE_URL')) {
            $this->markTestSkipped('Set SITE_URL env to run live integration tests');
        }
        $response = $this->httpGet($this->siteUrl . '/autochair-smart-lifter-enquiry/');
        $this->assertGreaterThanOrEqual(200, $response['status'], 'Autochair enquiry page should return 200');
        $this->assertLessThan(400, $response['status'], 'Autochair enquiry page returned error: ' . $response['status']);
    }

    /**
     * @group integration
     */
    public function testAutoChairThankYouPageReturns200(): void
    {
        if (!getenv('SITE_URL')) {
            $this->markTestSkipped('Set SITE_URL env to run live integration tests');
        }
        $response = $this->httpGet($this->siteUrl . '/thank-you-autochair');
        $this->assertGreaterThanOrEqual(200, $response['status'], 'Autochair thank-you page should return 200');
        $this->assertLessThan(400, $response['status'], 'Autochair thank-you page returned error: ' . $response['status']);
    }

    /**
     * @group integration
     * @group mobile
     */
    public function testAutoChairMobilePageReturns200(): void
    {
        if (!getenv('SITE_URL')) {
            $this->markTestSkipped('Set SITE_URL env to run live integration tests');
        }
        $context = stream_context_create([
            'http' => [
                'timeout' => 15,
                'header' => "User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.0 Mobile/15E148 Safari/604.1\r\n",
            ],
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
        ]);
        $response = @file_get_contents($this->siteUrl . '/autochair-smart-lifter-enquiry/', false, $context);
        $statusCode = 0;
        if (isset($http_response_header[0])) {
            preg_match('/HTTP\/\d\.?\d?\s+(\d{3})/', $http_response_header[0], $m);
            $statusCode = (int)($m[1] ?? 0);
        }
        $this->assertGreaterThanOrEqual(200, $statusCode, 'Mobile autochair page should return 200');
        $this->assertLessThan(400, $statusCode, 'Mobile autochair page returned error: ' . $statusCode);
    }

    // ===================================================================
    // SECTION: All Forms — Mail try/catch, Fallback, AJAX URL Tests
    // ===================================================================

    /**
     * Verify all form controllers wrap admin mail in try/catch.
     * @dataProvider allFormControllerProvider
     */
    public function testControllerHasMailTryCatch(string $controller, string $label): void
    {
        $content = $this->readFile($controller);
        $this->assertMatchesRegularExpression(
            '/try\s*\{[^}]*\$mail->send\(\)/',
            $content,
            "{$label} controller must wrap admin \$mail->send() in try/catch"
        );
    }

    /**
     * Verify all form controllers have a fallback retry on admin mail failure.
     * @dataProvider allFormControllerProvider
     */
    public function testControllerHasMailFallbackRetry(string $controller, string $label): void
    {
        $content = $this->readFile($controller);
        $this->assertStringContainsString(
            'FALLBACK MAIL ERROR',
            $content,
            "{$label} controller must have a fallback retry when admin mail fails"
        );
    }

    /**
     * Verify all form controllers that have customer mail wrap it in try/catch.
     * @dataProvider formControllersWithCustomerMailProvider
     */
    public function testControllerHasCustomerMailTryCatch(string $controller, string $label): void
    {
        $content = $this->readFile($controller);
        $this->assertMatchesRegularExpression(
            '/try\s*\{[^}]*\$customerMail->send\(\)/s',
            $content,
            "{$label} controller must wrap customer \$customerMail->send() in try/catch"
        );
    }

    /**
     * Verify all form controllers save to DB before sending mail.
     * @dataProvider allFormControllerProvider
     */
    public function testControllerSavesDbBeforeMail(string $controller, string $label): void
    {
        $content = $this->readFile($controller);
        $dbPos = strpos($content, '$this->model_');
        $mailPos = strpos($content, '$mail->send()');
        $this->assertNotFalse($dbPos, "{$label} controller must have a model (DB) call");
        $this->assertNotFalse($mailPos, "{$label} controller must send mail");
        $this->assertLessThan($mailPos, $dbPos, "{$label} controller must save to DB before sending mail");
    }

    /**
     * Verify all form controllers redirect after successful submission.
     * @dataProvider allFormControllerProvider
     */
    public function testControllerRedirectsOnSuccess(string $controller, string $label): void
    {
        $content = $this->readFile($controller);
        $this->assertMatchesRegularExpression(
            '/redirect\(/',
            $content,
            "{$label} controller must redirect after successful submission"
        );
    }

    /**
     * Verify all mobile templates use absolute AJAX validate URLs.
     * @dataProvider mobileFormTemplateProvider
     */
    public function testMobileTemplateHasAbsoluteAjaxUrl(string $template, string $label): void
    {
        $content = $this->readFile("catalog/view/theme/so-mobile/template/information/{$template}");
        $this->assertMatchesRegularExpression(
            '/data-validate-url="\/index\.php/',
            $content,
            "Mobile {$label} template must use absolute AJAX validate URL (leading /)"
        );
    }

    /**
     * Verify all desktop templates use absolute AJAX validate URLs.
     * @dataProvider desktopFormTemplateWithAjaxProvider
     */
    public function testDesktopTemplateHasAbsoluteAjaxUrl(string $template, string $label): void
    {
        $path = "catalog/view/theme/so-clickboom/template/information/{$template}";
        if (!file_exists($this->baseDir . '/' . $path)) {
            $this->markTestSkipped("Template not found: {$path}");
        }
        $content = $this->readFile($path);
        $this->assertMatchesRegularExpression(
            '/data-validate-url="\/index\.php/',
            $content,
            "Desktop {$label} template must use absolute AJAX validate URL (leading /)"
        );
    }

    /**
     * Verify all mobile templates include the shared AJAX validation script.
     * @dataProvider mobileFormTemplateProvider
     */
    public function testMobileTemplateIncludesAjaxValidateScript(string $template, string $label): void
    {
        $content = $this->readFile("catalog/view/theme/so-mobile/template/information/{$template}");
        $this->assertStringContainsString(
            'form-ajax-validate.js',
            $content,
            "Mobile {$label} template must include form-ajax-validate.js"
        );
    }

    // --- Data Providers ---

    public static function allFormControllerProvider(): array
    {
        return [
            'contact'         => ['catalog/controller/information/contact.php', 'Contact'],
            'demo_request'    => ['catalog/controller/information/demo_request.php', 'Demo Request'],
            'trial_request'   => ['catalog/controller/information/trial_request.php', 'Trial Request'],
            'quote_request'   => ['catalog/controller/information/quote_request.php', 'Quote Request'],
            'find_dealer'     => ['catalog/controller/information/find_dealer_form.php', 'Find Dealer'],
            'warranty_claim'  => ['catalog/controller/information/warranty_claim.php', 'Warranty Claim'],
            'funding_support' => ['catalog/controller/information/funding_support.php', 'Funding Support'],
            'lightdrive'      => ['catalog/controller/information/lightDriveEnquiry.php', 'Light Drive'],
            'autochair'       => ['catalog/controller/information/autochairEnquiry.php', 'Autochair'],
            'place_order'     => ['catalog/controller/information/place_order.php', 'Place Order'],
            'product_enq'     => ['catalog/controller/information/product_enq.php', 'Product Enquiry'],
        ];
    }

    public static function formControllersWithCustomerMailProvider(): array
    {
        return [
            'contact'         => ['catalog/controller/information/contact.php', 'Contact'],
            'demo_request'    => ['catalog/controller/information/demo_request.php', 'Demo Request'],
            'trial_request'   => ['catalog/controller/information/trial_request.php', 'Trial Request'],
            'quote_request'   => ['catalog/controller/information/quote_request.php', 'Quote Request'],
            'find_dealer'     => ['catalog/controller/information/find_dealer_form.php', 'Find Dealer'],
            'funding_support' => ['catalog/controller/information/funding_support.php', 'Funding Support'],
            'lightdrive'      => ['catalog/controller/information/lightDriveEnquiry.php', 'Light Drive'],
            'autochair'       => ['catalog/controller/information/autochairEnquiry.php', 'Autochair'],
            'place_order'     => ['catalog/controller/information/place_order.php', 'Place Order'],
            'product_enq'     => ['catalog/controller/information/product_enq.php', 'Product Enquiry'],
        ];
    }

    public static function mobileFormTemplateProvider(): array
    {
        return [
            'contact'         => ['contact.twig', 'Contact'],
            'demo_request'    => ['demo_request.twig', 'Demo Request'],
            'trial_request'   => ['trial_request.twig', 'Trial Request'],
            'quote_request'   => ['quote_request.twig', 'Quote Request'],
            'find_dealer'     => ['find_dealer.twig', 'Find Dealer'],
            'warranty_claim'  => ['warranty_claim.twig', 'Warranty Claim'],
            'funding_support' => ['funding_support.twig', 'Funding Support'],
            'lightdrive'      => ['lightDriveEnquiry.twig', 'Light Drive'],
            'autochair'       => ['autochairEnquiry.twig', 'Autochair'],
            'place_order'     => ['place_order.twig', 'Place Order'],
            'product_enq'     => ['product_enq.twig', 'Product Enquiry'],
        ];
    }

    public static function desktopFormTemplateWithAjaxProvider(): array
    {
        return [
            'contact'         => ['contact.twig', 'Contact'],
            'demo_request'    => ['demo_request.twig', 'Demo Request'],
            'trial_request'   => ['trial_request.twig', 'Trial Request'],
            'quote_request'   => ['quote_request.twig', 'Quote Request'],
            'find_dealer'     => ['find_dealer.twig', 'Find Dealer'],
            'warranty_claim'  => ['warranty_claim.twig', 'Warranty Claim'],
            'funding_support' => ['funding_support.twig', 'Funding Support'],
            'lightdrive'      => ['lightDriveEnquiry.twig', 'Light Drive'],
            'autochair'       => ['autochairEnquiry.twig', 'Autochair'],
            'place_order'     => ['place_order.twig', 'Place Order'],
            'product_enq'     => ['product_enq.twig', 'Product Enquiry'],
        ];
    }

    // -------------------------------------------------------------------
    // LightDrive Enquiry Form
    // -------------------------------------------------------------------

    public function testLightDriveEnquiryTemplateExists(): void
    {
        $this->assertFileExists(
            $this->baseDir . '/catalog/view/theme/so-clickboom/template/information/lightDriveEnquiry.twig',
            'LightDrive enquiry template must exist'
        );
    }

    public function testLightDriveEnquiryHasFormTag(): void
    {
        $content = $this->readFile('catalog/view/theme/so-clickboom/template/information/lightDriveEnquiry.twig');
        $this->assertStringContainsString('<form', $content, 'LightDrive template must contain a form');
    }

    // -------------------------------------------------------------------
    // Funding Support Form
    // -------------------------------------------------------------------

    public function testFundingSupportTemplateExists(): void
    {
        $this->assertFileExists(
            $this->baseDir . '/catalog/view/theme/so-clickboom/template/information/funding_support.twig',
            'Funding support template must exist'
        );
    }

    public function testFundingSupportHasFormTag(): void
    {
        $content = $this->readFile('catalog/view/theme/so-clickboom/template/information/funding_support.twig');
        $this->assertStringContainsString('<form', $content, 'Funding support template must contain a form');
    }

    // -------------------------------------------------------------------
    // Login Form Controller
    // -------------------------------------------------------------------

    public function testLoginControllerExists(): void
    {
        $this->assertFileExists(
            $this->baseDir . '/catalog/controller/account/login.php',
            'Login controller must exist'
        );
    }

    public function testLoginControllerHandsPostRequests(): void
    {
        $content = $this->readFile('catalog/controller/account/login.php');
        $this->assertStringContainsString(
            '$this->request->server[\'REQUEST_METHOD\']',
            $content,
            'Login controller must check for POST method'
        );
    }

    // -------------------------------------------------------------------
    // Registration Form Controller
    // -------------------------------------------------------------------

    public function testRegisterControllerExists(): void
    {
        $this->assertFileExists(
            $this->baseDir . '/catalog/controller/account/register.php',
            'Register controller must exist'
        );
    }

    public function testRegisterControllerValidatesPassword(): void
    {
        $content = $this->readFile('catalog/controller/account/register.php');
        $this->assertStringContainsString('password', $content, 'Must validate password');
        $this->assertStringContainsString('confirm', $content, 'Must validate password confirmation');
    }

    // ===================================================================
    // SECTION 2: Template Structure Tests (Desktop & Mobile)
    // ===================================================================

    // -------------------------------------------------------------------
    // Desktop (so-clickboom) Templates
    // -------------------------------------------------------------------

    /**
     * @dataProvider desktopFormTemplateProvider
     */
    public function testDesktopFormTemplateHasCSRFProtectionOrAction(string $template): void
    {
        $path = "catalog/view/theme/so-clickboom/template/{$template}";
        if (!file_exists($this->baseDir . '/' . $path)) {
            $this->markTestSkipped("Template not found: {$path}");
        }
        $content = $this->readFile($path);
        // Forms should either have an action or use AJAX submission
        $hasAction = strpos($content, '{{ action }}') !== false || strpos($content, 'action=') !== false;
        $hasAjax = strpos($content, '$.ajax') !== false || strpos($content, '$.post') !== false;
        $this->assertTrue(
            $hasAction || $hasAjax,
            "Desktop form in {$template} must have form action or AJAX submission"
        );
    }

    public static function desktopFormTemplateProvider(): array
    {
        return [
            'contact'           => ['information/contact.twig'],
            'demo_request'      => ['information/demo_request.twig'],
            'trial_request'     => ['information/trial_request.twig'],
            'warranty_claim'    => ['information/warranty_claim.twig'],
            'funding_support'   => ['information/funding_support.twig'],
            'autochairEnquiry'  => ['information/autochairEnquiry.twig'],
            'lightDriveEnquiry' => ['information/lightDriveEnquiry.twig'],
            'find_dealer'       => ['information/find_dealer.twig'],
        ];
    }

    // -------------------------------------------------------------------
    // Mobile (so-mobile) Theme Templates
    // -------------------------------------------------------------------

    public function testMobileContactTemplateExists(): void
    {
        $this->assertFileExists(
            $this->baseDir . '/catalog/view/theme/so-mobile/template/information/contact.twig',
            'Mobile contact template must exist for mobile visitors'
        );
    }

    public function testMobileContactFormHasRequiredFields(): void
    {
        $content = $this->readFile('catalog/view/theme/so-mobile/template/information/contact.twig');
        $this->assertStringContainsString('name="name"', $content, 'Mobile contact form must have name field');
        $this->assertStringContainsString('name="email"', $content, 'Mobile contact form must have email field');
    }

    public function testMobileProductTemplateExists(): void
    {
        $this->assertFileExists(
            $this->baseDir . '/catalog/view/theme/so-mobile/template/product/product.twig',
            'Mobile product template must exist'
        );
    }

    public function testMobileProductHasQuoteForm(): void
    {
        $path = $this->baseDir . '/catalog/view/theme/so-mobile/template/product/product.twig';
        if (!file_exists($path)) {
            $this->markTestSkipped('Mobile product template not found');
        }
        $content = file_get_contents($path);
        $hasForm = strpos($content, '<form') !== false;
        $hasAction = strpos($content, '{{ action }}') !== false;
        $this->assertTrue($hasForm, 'Mobile product page must have a form (quote/enquiry)');
    }

    public function testMobileHeaderExists(): void
    {
        $this->assertFileExists(
            $this->baseDir . '/catalog/view/theme/so-mobile/template/common/header.twig',
            'Mobile header template must exist'
        );
    }

    public function testMobileFooterExists(): void
    {
        $this->assertFileExists(
            $this->baseDir . '/catalog/view/theme/so-mobile/template/common/footer.twig',
            'Mobile footer template must exist'
        );
    }

    public function testMobileHomeTemplateExists(): void
    {
        $this->assertFileExists(
            $this->baseDir . '/catalog/view/theme/so-mobile/template/common/home.twig',
            'Mobile home template must exist'
        );
    }

    public function testMobileSearchTemplateExists(): void
    {
        // Search is in the header sub-template (header2.twig) included by header.twig
        $headerDir = $this->baseDir . '/catalog/view/theme/so-mobile/template/header/';
        $searchFound = false;
        if (is_dir($headerDir)) {
            foreach (glob($headerDir . '*.twig') as $file) {
                if (stripos(file_get_contents($file), 'search') !== false) {
                    $searchFound = true;
                    break;
                }
            }
        }
        // Also check modules
        if (!$searchFound) {
            $searchproDir = $this->baseDir . '/catalog/view/theme/so-mobile/template/extension/module/so_searchpro/';
            if (is_dir($searchproDir)) {
                foreach (glob($searchproDir . '*.twig') as $file) {
                    if (stripos(file_get_contents($file), 'search') !== false) {
                        $searchFound = true;
                        break;
                    }
                }
            }
        }
        // Fallback: check default theme
        if (!$searchFound) {
            $defaultSearch = $this->baseDir . '/catalog/view/theme/default/template/common/search.twig';
            $searchFound = file_exists($defaultSearch);
        }
        $this->assertTrue($searchFound, 'Search template must exist in mobile theme, searchpro module, or default theme');
    }

    public function testMobileLoginTemplateExists(): void
    {
        // Mobile may use default theme login or have its own
        $mobilePath = $this->baseDir . '/catalog/view/theme/so-mobile/template/account/login.twig';
        $defaultPath = $this->baseDir . '/catalog/view/theme/default/template/account/login.twig';
        $this->assertTrue(
            file_exists($mobilePath) || file_exists($defaultPath),
            'Login template must exist in either mobile or default theme'
        );
    }

    // -------------------------------------------------------------------
    // Mobile Theme: Ensure parity with desktop forms
    // -------------------------------------------------------------------

    public function testMobileContactHasFormAction(): void
    {
        $content = $this->readFile('catalog/view/theme/so-mobile/template/information/contact.twig');
        $hasAction = strpos($content, '{{ action }}') !== false || strpos($content, 'action=') !== false;
        $hasAjax = strpos($content, '$.ajax') !== false || strpos($content, '$.post') !== false;
        $this->assertTrue(
            $hasAction || $hasAjax,
            'Mobile contact form must have action or AJAX submission'
        );
    }

    // ===================================================================
    // SECTION 3: Support Form Controller
    // ===================================================================

    public function testSupportTemplateExists(): void
    {
        $this->assertFileExists(
            $this->baseDir . '/catalog/view/theme/so-clickboom/template/information/support.twig',
            'Support form template must exist'
        );
    }

    // ===================================================================
    // SECTION 4: Search Form
    // ===================================================================

    public function testSearchFormExistsInDesktopHeader(): void
    {
        // Search is in the header sub-template (header5.twig) included by header.twig
        $headerDir = $this->baseDir . '/catalog/view/theme/so-clickboom/template/header/';
        $searchFound = false;
        if (is_dir($headerDir)) {
            foreach (glob($headerDir . '*.twig') as $file) {
                if (stripos(file_get_contents($file), 'search') !== false) {
                    $searchFound = true;
                    break;
                }
            }
        }
        $this->assertTrue($searchFound, 'Desktop header sub-templates must include search functionality');
    }

    // ===================================================================
    // SECTION 5: Checkout & Cart Forms
    // ===================================================================

    public function testCheckoutTemplateExists(): void
    {
        $desktopPath = $this->baseDir . '/catalog/view/theme/so-clickboom/template/checkout/checkout.twig';
        $defaultPath = $this->baseDir . '/catalog/view/theme/default/template/checkout/checkout.twig';
        $this->assertTrue(
            file_exists($desktopPath) || file_exists($defaultPath),
            'Checkout template must exist in theme'
        );
    }

    public function testCartControllerExists(): void
    {
        $this->assertFileExists(
            $this->baseDir . '/catalog/controller/checkout/cart.php',
            'Cart controller must exist'
        );
    }

    // ===================================================================
    // SECTION 6: Newsletter Signup
    // ===================================================================

    public function testNewsletterPopupTemplateExists(): void
    {
        $path = $this->baseDir . '/catalog/view/theme/so-clickboom/template/extension/module/so_newletter_custom_popup/default_layout_popup.twig';
        if (!file_exists($path)) {
            $this->markTestSkipped('Newsletter popup template not found');
        }
        $content = file_get_contents($path);
        $this->assertStringContainsString('email', $content, 'Newsletter popup must have email field');
    }

    // ===================================================================
    // SECTION 7: Live HTTP Integration Tests (run with SITE_URL env var)
    // ===================================================================

    /**
     * @dataProvider livePageProvider
     * @group integration
     */
    public function testLivePageReturns200(string $path, string $description): void
    {
        if (!getenv('SITE_URL')) {
            $this->markTestSkipped('Set SITE_URL env to run live integration tests');
        }

        $response = $this->httpGet($this->siteUrl . $path);
        $this->assertGreaterThanOrEqual(200, $response['status'], "Page {$path} ({$description}) should return 200");
        $this->assertLessThan(400, $response['status'], "Page {$path} ({$description}) returned error: {$response['status']}");
    }

    /**
     * @dataProvider livePageProvider
     * @group integration
     */
    public function testLivePageHasDoctype(string $path, string $description): void
    {
        if (!getenv('SITE_URL')) {
            $this->markTestSkipped('Set SITE_URL env to run live integration tests');
        }

        $response = $this->httpGet($this->siteUrl . $path);
        if ($response['status'] >= 200 && $response['status'] < 400) {
            $this->assertMatchesRegularExpression(
                '/^\s*<!DOCTYPE\s+html/i',
                $response['body'],
                "Page {$path} ({$description}) must start with <!DOCTYPE html>"
            );
        }
    }

    /**
     * @dataProvider liveFormPageProvider
     * @group integration
     */
    public function testLiveFormPageContainsForm(string $path, string $description): void
    {
        if (!getenv('SITE_URL')) {
            $this->markTestSkipped('Set SITE_URL env to run live integration tests');
        }

        $response = $this->httpGet($this->siteUrl . $path);
        if ($response['status'] >= 200 && $response['status'] < 400) {
            $this->assertStringContainsString(
                '<form',
                $response['body'],
                "Page {$path} ({$description}) must contain a <form> element"
            );
        }
    }

    /**
     * @dataProvider liveFormPageProvider
     * @group integration
     */
    public function testLiveFormPageHasNoPhpErrors(string $path, string $description): void
    {
        if (!getenv('SITE_URL')) {
            $this->markTestSkipped('Set SITE_URL env to run live integration tests');
        }

        $response = $this->httpGet($this->siteUrl . $path);
        if ($response['status'] >= 200 && $response['status'] < 400) {
            $this->assertDoesNotMatchRegularExpression(
                '/Fatal error|Warning:|Parse error|Deprecated:|Uncaught Error/i',
                $response['body'],
                "Page {$path} ({$description}) must not contain PHP errors"
            );
        }
    }

    public static function livePageProvider(): array
    {
        return [
            'homepage'        => ['/home', 'Homepage'],
            'contact'         => ['/contact-mobilitycare', 'Contact page'],
            'demo_request'    => ['/organise-a-product-demonstration/', 'Demo request'],
            'trial_request'   => ['/organise-a-product-trial/', 'Trial request'],
            'warranty_claim'  => ['/warranty-claim/', 'Warranty claim'],
            'quote_request'   => ['/request-quote/', 'Quote request'],
            'find_dealer'     => ['/request-local-dealer/', 'Find dealer'],
            'login'           => ['/index.php?route=account/login', 'Login page'],
            'register'        => ['/index.php?route=account/register', 'Register page'],
            'search'          => ['/index.php?route=product/search&search=wheelchair', 'Search page'],
        ];
    }

    public static function liveFormPageProvider(): array
    {
        return [
            'contact'         => ['/contact-mobilitycare', 'Contact form'],
            'demo_request'    => ['/organise-a-product-demonstration/', 'Demo request form'],
            'trial_request'   => ['/organise-a-product-trial/', 'Trial request form'],
            'warranty_claim'  => ['/warranty-claim/', 'Warranty claim form'],
            'quote_request'   => ['/request-quote/', 'Quote request form'],
            'find_dealer'     => ['/request-local-dealer/', 'Find dealer form'],
            'login'           => ['/index.php?route=account/login', 'Login form'],
            'register'        => ['/index.php?route=account/register', 'Registration form'],
        ];
    }

    // ===================================================================
    // SECTION 8: Mobile-specific live tests
    // ===================================================================

    /**
     * @dataProvider mobilePageProvider
     * @group integration
     * @group mobile
     */
    public function testMobilePageReturns200(string $path, string $description): void
    {
        if (!getenv('SITE_URL')) {
            $this->markTestSkipped('Set SITE_URL env to run live integration tests');
        }

        // Simulate mobile user agent
        $context = stream_context_create([
            'http' => [
                'timeout' => 15,
                'header' => "User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.0 Mobile/15E148 Safari/604.1\r\n",
            ],
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
        ]);

        $response = @file_get_contents($this->siteUrl . $path, false, $context);
        $statusCode = 0;
        if (isset($http_response_header[0])) {
            preg_match('/HTTP\/\d\.?\d?\s+(\d{3})/', $http_response_header[0], $m);
            $statusCode = (int)($m[1] ?? 0);
        }

        $this->assertGreaterThanOrEqual(200, $statusCode, "Mobile: {$path} ({$description}) should return 200");
        $this->assertLessThan(400, $statusCode, "Mobile: {$path} ({$description}) returned error: {$statusCode}");
    }

    /**
     * @dataProvider mobilePageProvider
     * @group integration
     * @group mobile
     */
    public function testMobilePageHasNoPhpErrors(string $path, string $description): void
    {
        if (!getenv('SITE_URL')) {
            $this->markTestSkipped('Set SITE_URL env to run live integration tests');
        }

        $context = stream_context_create([
            'http' => [
                'timeout' => 15,
                'header' => "User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.0 Mobile/15E148 Safari/604.1\r\n",
            ],
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
        ]);

        $response = @file_get_contents($this->siteUrl . $path, false, $context);
        if ($response) {
            $this->assertDoesNotMatchRegularExpression(
                '/Fatal error|Warning:|Parse error|Deprecated:|Uncaught Error|each\(\)|create_function|strftime|get_magic_quotes/i',
                $response,
                "Mobile page {$path} ({$description}) must not contain PHP 8.1 errors"
            );
        }
    }

    public static function mobilePageProvider(): array
    {
        return [
            'homepage'        => ['/home', 'Mobile Homepage'],
            'contact'         => ['/contact-mobilitycare', 'Mobile Contact'],
            'demo_request'    => ['/organise-a-product-demonstration/', 'Mobile Demo Request'],
            'trial_request'   => ['/organise-a-product-trial/', 'Mobile Trial Request'],
            'warranty_claim'  => ['/warranty-claim/', 'Mobile Warranty Claim'],
            'quote_request'   => ['/request-quote/', 'Mobile Quote Request'],
            'find_dealer'     => ['/request-local-dealer/', 'Mobile Find Dealer'],
            'login'           => ['/index.php?route=account/login', 'Mobile Login'],
            'register'        => ['/index.php?route=account/register', 'Mobile Register'],
        ];
    }
}
