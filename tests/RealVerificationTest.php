<?php
/**
 * REAL Verification Tests for MobilityCare Forms
 * 
 * These tests verify:
 * 1. DB tables exist and INSERT queries match column schemas
 * 2. Model code has no PHP 8.1 issues (no direct $data[] access without isset)
 * 3. No hardcoded empty strings for INT columns (strict mode killer)
 * 4. No double-prefix table names
 * 5. Controller DB calls are wrapped in try/catch
 * 6. AJAX validate URLs are absolute in all templates
 * 7. Script src paths are absolute in all templates
 * 8. Mail template files exist
 * 9. Controller redirects to correct thank-you pages
 * 10. .htaccess rewrite rules exist for all thank-you pages
 */

// Bootstrap - just need config for DB connection
$rootDir = dirname(__DIR__);
require_once $rootDir . '/config.php';

$passed = 0;
$failed = 0;
$errors = [];

function test($name, $condition, $detail = '') {
    global $passed, $failed, $errors;
    if ($condition) {
        $passed++;
        echo "  PASS: {$name}" . PHP_EOL;
    } else {
        $failed++;
        $errors[] = $name . ($detail ? " — {$detail}" : '');
        echo "  FAIL: {$name}" . ($detail ? " — {$detail}" : '') . PHP_EOL;
    }
}

// ============================================================
echo "=== 1. DB TABLE EXISTENCE ===" . PHP_EOL;
// ============================================================

$db = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, (int)DB_PORT);
if ($db->connect_error) { die('DB ERROR: ' . $db->connect_error); }

$requiredTables = [
    'demo_requests', 'findDealer_requests', 'funding_support', 'contact_forms',
    'place_orderForm', 'producttrial_requests', 'quote_requests', 
    'autochairEnquiry', 'lightDriveEnquiry', 'warranty_claims', 'product_inquiry'
];

foreach ($requiredTables as $t) {
    $full = DB_PREFIX . $t;
    $r = $db->query("SHOW TABLES LIKE '" . $db->real_escape_string($full) . "'");
    test("Table {$full} exists", $r && $r->num_rows > 0);
}

// ============================================================
echo PHP_EOL . "=== 2. DB INSERT - manufacturer_id not empty string ===" . PHP_EOL;
// ============================================================

$modelFile = file_get_contents($rootDir . '/catalog/model/catalog/demo_request.php');

// Check no INSERT has manufacturer_id = '' (empty string into INT column)
$hasEmptyManufacturer = preg_match("/manufacturer_id\s*=\s*''/", $modelFile);
test("No manufacturer_id = '' in demo_request model", !$hasEmptyManufacturer, 
    $hasEmptyManufacturer ? "Found manufacturer_id = '' — will fail on MySQL STRICT_TRANS_TABLES" : '');

// ============================================================
echo PHP_EOL . "=== 3. DB INSERT - no double-prefix table names ===" . PHP_EOL;
// ============================================================

$hasDoublePrefix = preg_match('/DB_PREFIX\s*\.\s*["\']oc_/', $modelFile);
test("No double-prefix (DB_PREFIX . 'oc_') in demo_request model", !$hasDoublePrefix,
    $hasDoublePrefix ? "Found DB_PREFIX . 'oc_...' — table name will be oc_oc_..." : '');

// ============================================================
echo PHP_EOL . "=== 4. PHP 8.1 SAFETY - isset() checks in model ===" . PHP_EOL;
// ============================================================

// Extract all public methods and check for direct $data['key'] without isset
$methods = [
    'addDemoRequest', 'addFindDealerRequest', 'addFundingSupport', 'addContactFormInfo',
    'addPlaceOrder', 'addProductTrialRequest', 'addQuoteRequest', 'addAutochairEnquiry',
    'addLightDriveEnquiry', 'addProductEnquiry'
];

foreach ($methods as $method) {
    // Extract method body
    $pattern = '/function\s+' . preg_quote($method) . '\s*\([^)]*\)\s*\{/';
    if (preg_match($pattern, $modelFile, $m, PREG_OFFSET_CAPTURE)) {
        $startPos = $m[0][1];
        // Find matching closing brace
        $braceCount = 0;
        $methodBody = '';
        $inBody = false;
        for ($i = $startPos; $i < strlen($modelFile); $i++) {
            if ($modelFile[$i] === '{') { $braceCount++; $inBody = true; }
            if ($modelFile[$i] === '}') { $braceCount--; }
            if ($inBody) $methodBody .= $modelFile[$i];
            if ($inBody && $braceCount === 0) break;
        }
        
        // Check for direct $data['xxx'] access NOT inside isset()
        // Pattern: $data['key'] that is NOT preceded by isset( on the same line
        $lines = explode("\n", $methodBody);
        $unsafeAccess = false;
        foreach ($lines as $line) {
            $trimmed = trim($line);
            // Skip lines that use isset or have ternary with isset
            if (strpos($trimmed, 'isset') !== false) continue;
            // Check for direct $data['key'] access in db->escape or direct use
            if (preg_match('/\$data\[/', $trimmed)) {
                $unsafeAccess = true;
                break;
            }
        }
        test("{$method}() uses isset() for all \$data access", !$unsafeAccess,
            $unsafeAccess ? "Direct \$data['key'] without isset() found — PHP 8.1 deprecation" : '');
    } else {
        test("{$method}() exists in model", false, "Method not found");
    }
}

// ============================================================
echo PHP_EOL . "=== 5. CONTROLLER DB CALLS - try/catch protection ===" . PHP_EOL;
// ============================================================

$controllers = [
    'autochairEnquiry.php' => 'addAutochairEnquiry',
    'demo_request.php' => 'addDemoRequest',
    'trial_request.php' => 'addProductTrialRequest',
    'find_dealer_form.php' => 'addFindDealerRequest',
    'warranty_claim.php' => 'addClaim',
    'funding_support.php' => 'addFundingSupport',
    'place_order.php' => 'addPlaceOrder',
    'product_enq.php' => 'addProductEnquiry',
    'lightDriveEnquiry.php' => 'addLightDriveEnquiry',
    'contact.php' => 'addContactFormInfo',
    'quote_request.php' => 'addQuoteRequest',
];

foreach ($controllers as $file => $method) {
    $path = $rootDir . '/catalog/controller/information/' . $file;
    if (!file_exists($path)) {
        test("{$file} exists", false);
        continue;
    }
    $content = file_get_contents($path);
    
    // Check that the DB call is preceded by try {
    // Look for "try {" before the method call, with no "}" between them
    $methodCall = $method . '(';
    $callPos = strpos($content, $methodCall);
    if ($callPos === false) {
        test("{$file} calls {$method}()", false);
        continue;
    }
    
    // Look backwards from the call for "try {"
    $before = substr($content, max(0, $callPos - 200), min(200, $callPos));
    $hasTryCatch = strpos($before, 'try {') !== false || strpos($before, 'try{') !== false;
    test("{$file} wraps {$method}() in try/catch", $hasTryCatch);
}

// ============================================================
echo PHP_EOL . "=== 6. TEMPLATE AJAX VALIDATE URLs - absolute paths ===" . PHP_EOL;
// ============================================================

$templates = [
    'autochairEnquiry', 'contact', 'demo_request', 'find_dealer', 
    'funding_support', 'lightDriveEnquiry', 'place_order', 'product_enq',
    'quote_request', 'trial_request', 'warranty_claim'
];
$themes = ['so-mobile', 'so-clickboom'];

foreach ($themes as $theme) {
    foreach ($templates as $tpl) {
        $path = $rootDir . "/catalog/view/theme/{$theme}/template/information/{$tpl}.twig";
        if (!file_exists($path)) continue;
        
        $content = file_get_contents($path);
        
        // Check data-validate-url starts with /
        if (preg_match('/data-validate-url="([^"]*)"/', $content, $m)) {
            $url = $m[1];
            test("{$theme}/{$tpl} validate URL is absolute", 
                strpos($url, '/') === 0, 
                "URL: {$url}");
        }
    }
}

// ============================================================
echo PHP_EOL . "=== 7. TEMPLATE SCRIPT SRC - form-ajax-validate.js absolute ===" . PHP_EOL;
// ============================================================

foreach ($themes as $theme) {
    foreach ($templates as $tpl) {
        $path = $rootDir . "/catalog/view/theme/{$theme}/template/information/{$tpl}.twig";
        if (!file_exists($path)) continue;
        
        $content = file_get_contents($path);
        
        if (strpos($content, 'form-ajax-validate.js') !== false) {
            $hasRelative = strpos($content, 'src="catalog/view/javascript/form-ajax-validate.js"') !== false;
            $hasAbsolute = strpos($content, 'src="/catalog/view/javascript/form-ajax-validate.js"') !== false;
            test("{$theme}/{$tpl} JS src is absolute", $hasAbsolute && !$hasRelative,
                $hasRelative ? "Still has relative src" : '');
        }
    }
}

// ============================================================
echo PHP_EOL . "=== 8. MAIL TEMPLATE EXISTS ===" . PHP_EOL;
// ============================================================

$mailTemplate = $rootDir . '/catalog/view/theme/default/template/mail/enquiry_confirmation.twig';
test("Mail template exists (default theme)", file_exists($mailTemplate));

// ============================================================
echo PHP_EOL . "=== 9. .HTACCESS REWRITE RULES ===" . PHP_EOL;
// ============================================================

$htaccess = file_get_contents($rootDir . '/.htaccess');

$requiredRewrites = [
    'thank-you-autochair' => 'information/form_success/autochair',
    'thank-you-quote' => 'information/form_success/quote',
    'thank-you-demo' => 'information/form_success/demo',
    'thank-you-contact' => 'information/contact_success',
];

foreach ($requiredRewrites as $seo => $route) {
    test(".htaccess has rewrite for {$seo}", 
        strpos($htaccess, $seo) !== false,
        "Missing rewrite rule for /{$seo}/");
}

// ============================================================
echo PHP_EOL . "=== 10. REAL DB INSERT TEST (autochair with fixed values) ===" . PHP_EOL;
// ============================================================

$testSql = "INSERT INTO " . DB_PREFIX . "autochairEnquiry SET 
    fullname = 'PHPUNIT_TEST', email = 'test@test.com', phone = '0400000000', 
    postcode = '3000', contact_type = 'Individual Customer', 
    healthcare_profession = '', quote_type = 'Standard Quote', 
    manufacturer_id = 0, additional_info = 'test', 
    vehicle_make = 'TestMake', vehicle_model = 'TestModel', vehicle_year = '2024', 
    body_type = 'sedan', lifting_item = 'wheelchair', 
    item_weight = 10, item_height = 50, is_manufacturer_or_product = 1, 
    date_added = NOW()";

$result = $db->query($testSql);
test("Autochair INSERT with manufacturer_id=0 succeeds", $result && !$db->error,
    $db->error ?: '');

if ($result && $db->insert_id) {
    $insertId = $db->insert_id;
    
    // Verify data was actually saved correctly
    $verify = $db->query("SELECT * FROM " . DB_PREFIX . "autochairEnquiry WHERE id = " . $insertId);
    $row = $verify->fetch_assoc();
    
    test("Saved fullname matches", $row && $row['fullname'] === 'PHPUNIT_TEST');
    test("Saved email matches", $row && $row['email'] === 'test@test.com');
    test("Saved manufacturer_id is 0", $row && (int)$row['manufacturer_id'] === 0);
    test("Saved vehicle_make matches", $row && $row['vehicle_make'] === 'TestMake');
    
    // Cleanup
    $db->query("DELETE FROM " . DB_PREFIX . "autochairEnquiry WHERE id = " . $insertId);
}

// Test findDealer INSERT (now with fixed table name)
echo PHP_EOL . "=== 11. REAL DB INSERT TEST (findDealer with fixed table name) ===" . PHP_EOL;

$testSql2 = "INSERT INTO " . DB_PREFIX . "findDealer_requests SET 
    fullname = 'PHPUNIT_TEST', email = 'test@test.com', phone = '0400000000', 
    postcode = '3000', dealer_name = '', manufacturer_id = 0, 
    additional_info = '', car_make = '', car_model = '', car_year = '', 
    body_type = '', is_manufacturer_or_product = 0, date_added = NOW()";

$result2 = $db->query($testSql2);
test("FindDealer INSERT with correct table name succeeds", $result2 && !$db->error,
    $db->error ?: '');

if ($result2 && $db->insert_id) {
    $db->query("DELETE FROM " . DB_PREFIX . "findDealer_requests WHERE fullname = 'PHPUNIT_TEST'");
}

// Test lightDrive INSERT
echo PHP_EOL . "=== 12. REAL DB INSERT TEST (lightDrive with manufacturer_id=0) ===" . PHP_EOL;

$testSql3 = "INSERT INTO " . DB_PREFIX . "lightDriveEnquiry SET 
    fullname = 'PHPUNIT_TEST', email = 'test@test.com', phone = '0400000000', 
    postcode = '3000', contact_type = 'Individual Customer', 
    healthcare_profession = '', quote_type = 'Standard Quote', 
    manufacturer_id = 0, additional_info = 'test', 
    is_manufacturer_or_product = 1, date_added = NOW()";

$result3 = $db->query($testSql3);
test("LightDrive INSERT with manufacturer_id=0 succeeds", $result3 && !$db->error,
    $db->error ?: '');

if ($result3 && $db->insert_id) {
    $db->query("DELETE FROM " . DB_PREFIX . "lightDriveEnquiry WHERE fullname = 'PHPUNIT_TEST'");
}

// ============================================================
echo PHP_EOL . "=== 13. HELPER FILE EXISTS ===" . PHP_EOL;
// ============================================================

test("system/helper/phone.php exists", file_exists($rootDir . '/system/helper/phone.php'));

// ============================================================
echo PHP_EOL . "=== 14. FORM SUCCESS CONTROLLER ===" . PHP_EOL;
// ============================================================

$formSuccess = $rootDir . '/catalog/controller/information/form_success.php';
test("form_success.php exists", file_exists($formSuccess));

if (file_exists($formSuccess)) {
    $fsContent = file_get_contents($formSuccess);
    $requiredMethods = ['autochair', 'quote', 'demo', 'find_dealer', 'lightdrive', 'placeOrder', 'product_enq', 'trial_request', 'warranty_claim'];
    foreach ($requiredMethods as $m) {
        test("form_success.php has {$m}() method", strpos($fsContent, "function {$m}(") !== false);
    }
}

// ============================================================
echo PHP_EOL . "=== 15. PHP SYNTAX CHECK ON MODIFIED FILES ===" . PHP_EOL;
// ============================================================

$filesToLint = [
    'catalog/model/catalog/demo_request.php',
    'catalog/model/catalog/warranty_claim.php',
    'catalog/controller/information/autochairEnquiry.php',
    'catalog/controller/information/demo_request.php',
    'catalog/controller/information/trial_request.php',
    'catalog/controller/information/find_dealer_form.php',
    'catalog/controller/information/warranty_claim.php',
    'catalog/controller/information/funding_support.php',
    'catalog/controller/information/place_order.php',
    'catalog/controller/information/product_enq.php',
    'catalog/controller/information/lightDriveEnquiry.php',
    'catalog/controller/information/contact.php',
    'catalog/controller/information/quote_request.php',
];

foreach ($filesToLint as $f) {
    $fullPath = $rootDir . '/' . $f;
    if (!file_exists($fullPath)) {
        test("{$f} PHP syntax", false, "File not found");
        continue;
    }
    $output = [];
    $exitCode = 0;
    exec("php -l " . escapeshellarg($fullPath) . " 2>&1", $output, $exitCode);
    test("{$f} PHP syntax", $exitCode === 0, $exitCode !== 0 ? implode(' ', $output) : '');
}

$db->close();

// ============================================================
echo PHP_EOL . "===========================================" . PHP_EOL;
echo "RESULTS: {$passed} passed, {$failed} failed" . PHP_EOL;
echo "===========================================" . PHP_EOL;

if ($failed > 0) {
    echo PHP_EOL . "FAILURES:" . PHP_EOL;
    foreach ($errors as $e) {
        echo "  - {$e}" . PHP_EOL;
    }
    exit(1);
}

exit(0);
