# Form Validation Test Cases — MobilityCare

## Overview
All information forms now use AJAX validation via the shared `form-ajax-validate.js` script.
When a user clicks Submit, the form is validated via AJAX — if validation fails the page does NOT reload and errors are shown inline. If validation passes, the form is submitted normally (POST) and the user is redirected to the thank-you page.

---

## Test Environment
- **Desktop Theme:** so-clickboom
- **Mobile Theme:** so-mobile
- **Captcha System:** Named instances via `basic.php`
- **AJAX JS:** `catalog/view/javascript/form-ajax-validate.js`

---

## TC-01: Contact Form — Validation Failure (No Page Reload)
**Route:** `/contact-mobilitycare/`  
**Controller:** `information/contact`  
**Both themes:** so-clickboom, so-mobile

### Steps:
1. Navigate to `/contact-mobilitycare/`
2. Leave all fields blank
3. Click Submit

### Expected:
- Page does NOT reload
- Loader overlay appears briefly then hides
- Inline error messages appear below empty required fields (red text)
- Toast message appears at bottom with the last error
- All previously entered data (if any) remains in fields
- Form is NOT submitted to the server

---

## TC-02: Contact Form — Validation Success + Submission
**Route:** `/contact-mobilitycare/`

### Steps:
1. Navigate to `/contact-mobilitycare/`
2. Fill in all required fields (Name, Email, Phone, Message, Captcha)
3. Click Submit

### Expected:
- Loader overlay appears
- AJAX validation passes (response: `{success: true}`)
- Form auto-submits via POST
- User is redirected to `/thank-you-contact/`
- Session bypass flag prevents double captcha validation

---

## TC-03: Quote Request Form — Standalone Page
**Route:** `/request-quote/`  
**Controller:** `information/quote_request`

### Steps:
1. Navigate to `/request-quote/`
2. Leave fields blank → Submit → verify inline errors
3. Fill all required fields → Submit → verify redirect to `/thank-you-quote/`

### Expected:
- Same AJAX validation behavior as TC-01/TC-02
- `quote_ajax_validated` session flag set on AJAX success

---

## TC-04: Quote Request Modal — so_listing_tabs (Desktop)
**Theme:** so-clickboom  
**Template:** `so_listing_tabs/default/default_items.twig`

### Steps:
1. Navigate to a product listing page (e.g., homepage)
2. Click "Get Your Custom Quote" button on any product card
3. Modal opens with product pre-selected
4. Leave required fields blank → Submit → verify inline errors appear inside modal
5. Fill all fields correctly → Submit → verify redirect to `/thank-you-quote/`

### Expected:
- Modal form action is `{{ action }}` (resolves to `/request-quote/`)
- AJAX validation via `quoteForm` submit handler (existing inline JS)
- Form does NOT redirect to `/request-quote/` page (stays in modal on error)
- On success, form submits and redirects normally

---

## TC-05: Quote Request Modal — so_listing_tabs (Mobile)
**Theme:** so-mobile  
**Template:** `so_listing_tabs/default/default_items.twig`

### Steps:
1. Navigate to product listing on mobile device
2. Tap "Get Your Custom Quote" button
3. Modal opens
4. Test same as TC-04

### Expected:
- Form action now uses `{{ action }}` (was hardcoded `/request-quote/`)
- JavaScript submit handler works correctly (previously had syntax errors)
- Form data is captured and submitted properly

---

## TC-06: Demo Request Form
**Route:** `/organise-a-product-demonstration/`  
**Controller:** `information/demo_request`

### Steps:
1. Navigate to demo request page
2. Test validation failure (blank fields) → inline errors, no reload
3. Test validation success → redirect to `/thank-you-demo/`

---

## TC-07: Trial Request Form
**Route:** `/organise-a-product-trial/`  
**Controller:** `information/trial_request`

### Steps:
1. Navigate to trial request page
2. Test validation failure → inline errors, no reload
3. Test validation success → redirect to `/thank-you-trial/`

---

## TC-08: Funding Support Form
**Route:** `/funding-support/`  
**Controller:** `information/funding_support`

### Steps:
1. Navigate to funding support page  
2. Test validation failure → inline errors, no reload
3. Test validation success → redirect to `/thank-you-funding/`

---

## TC-09: Place Order Form
**Route:** `/place-an-order/`  
**Controller:** `information/place_order`

### Steps:
1. Navigate to place order page
2. Test validation failure → inline errors, no reload
3. Test validation success → redirect to `/thank-you-place-order/`

---

## TC-10: Warranty Claim Form
**Route:** `/warranty-claim/`  
**Controller:** `information/warranty_claim`

### Steps:
1. Navigate to warranty claim page
2. Test validation failure → inline errors, no reload
3. Test file upload with valid files
4. Test validation success → redirect to `/thank-you-warranty-claim/`

### Special Notes:
- This form has `enctype="multipart/form-data"` for file uploads
- Toast + Loader divs were newly added (weren't present before)
- Controller POST pattern was refactored from combined `if(POST && validate())` to separate nested checks

---

## TC-11: Product Enquiry Form
**Route:** `/product_enq/`  
**Controller:** `information/product_enq`

### Steps:
1. Navigate to product enquiry page
2. Test validation failure → inline errors, no reload
3. Test validation success → redirect to `/thank-you-product-enquiry/`

---

## TC-12: Find Dealer Form
**Route:** `/request-local-dealer/`  
**Controller:** `information/find_dealer_form`

### Steps:
1. Navigate to find dealer page
2. Test validation failure → inline errors, no reload
3. Test validation success → redirect to `/thank-you-find-dealer/`

### Special Notes:
- Controller POST pattern was refactored from combined `if(POST && validate())` to separate nested checks with bypass flag

---

## TC-13: Autochair Smart Lifter Enquiry
**Route:** `/autochair-smart-lifter-enquiry/`  
**Controller:** `information/autochairEnquiry`

### Steps:
1. Navigate to autochair enquiry page
2. Test validation failure → inline errors, no reload
3. Test validation success → redirect to `/thank-you-autochair/`

---

## TC-14: Light Drive Enquiry
**Route:** `/light-drive-2-enquiry/`  
**Controller:** `information/lightDriveEnquiry`

### Steps:
1. Navigate to light drive enquiry page
2. Test validation failure → inline errors, no reload
3. Test validation success → redirect to `/thank-you-lightdrive/`

---

## TC-15: Homepage Banner "Get Your Custom Quote" Button
**Theme:** so-clickboom  
**Template:** `so_home_slider/default.twig`

### Steps:
1. Navigate to homepage
2. Find the banner slider with "Get Your Custom Quote" button
3. Click the button

### Expected:
- Quote modal opens (from so_listing_tabs) instead of redirecting to `/request-quote/`
- Product ID 65 is pre-selected in the product dropdown
- Product name for ID 65 is displayed in the "Product of Interest" field
- Form can be submitted from modal (see TC-04)

---

## TC-16: Captcha Named Instance Isolation
**All forms**

### Steps:
1. Open Contact form in Tab 1
2. Open Quote Request form in Tab 2
3. Submit Contact form with wrong captcha
4. Submit Quote Request with correct captcha

### Expected:
- Each form uses a separate captcha session key (`captcha_information/contact` vs `captcha_information/quote_request`)
- Submitting one form does not invalidate the other form's captcha
- Triple-fallback validation in `basic.php` checks named key first, then `listing_captcha`, then generic `captcha`

---

## TC-17: AJAX Validation → POST Bypass Flow
**All forms**

### Steps:
1. Fill in a form completely with correct captcha
2. Submit (triggers AJAX validation)
3. AJAX response returns `{success: true}`
4. JS sets `form.data('ajax-validated', true)` and re-triggers submit
5. Form POST hits the controller's `index()` method

### Expected:
- Controller checks session bypass flag (e.g., `contact_ajax_validated`)
- Bypass flag is `true` → skips `$this->validate()` (avoids double captcha consumption)
- Form processes normally → sends email → saves to DB → redirects to thank-you

---

## TC-18: Loader Overlay Behavior
**All forms**

### Steps:
1. Fill form and click Submit
2. Observe loader overlay

### Expected:
- `#loaderOverlay` div becomes visible (`display: flex`)
- On AJAX error: loader hides
- On AJAX success + form re-submit: loader stays visible until page navigates to thank-you
- Prevent double-click (loader blocks interaction)

---

## TC-19: Cross-Theme Consistency
**Both themes: so-clickboom, so-mobile**

### Steps:
1. Test each form on desktop (so-clickboom theme)
2. Test each form on mobile (so-mobile theme)

### Expected:
- Both themes have identical AJAX validation behavior
- Both themes include `form-ajax-validate.js`
- Both themes show loader overlay
- Both themes show toast messages
- Both themes show inline validation errors

---

## TC-20: Network Error Handling
**All forms**

### Steps:
1. Open a form page
2. Disconnect network / block AJAX request
3. Click Submit

### Expected:
- AJAX `error` callback fires
- Loader overlay hides
- Alert displays: "A network error occurred. Please try again."

---

## Session Bypass Flag Reference

| Controller | Session Key |
|---|---|
| contact.php | `contact_ajax_validated` |
| demo_request.php | `demo_ajax_validated` |
| quote_request.php | `quote_ajax_validated` |
| trial_request.php | `trial_ajax_validated` |
| funding_support.php | `funding_ajax_validated` |
| place_order.php | `placeorder_ajax_validated` |
| warranty_claim.php | `warranty_ajax_validated` |
| product_enq.php | `productenq_ajax_validated` |
| find_dealer_form.php | `finddealer_ajax_validated` |
| autochairEnquiry.php | `autochair_ajax_validated` |
| lightDriveEnquiry.php | `lightdrive_ajax_validated` |

---

## ValidateAjax Route Reference

| Controller | AJAX Validate URL |
|---|---|
| contact | `index.php?route=information/contact/validateAjax` |
| demo_request | `index.php?route=information/demo_request/validateAjax` |
| quote_request | `index.php?route=information/quote_request/validateAjax` |
| trial_request | `index.php?route=information/trial_request/validateAjax` |
| funding_support | `index.php?route=information/funding_support/validateAjax` |
| place_order | `index.php?route=information/place_order/validateAjax` |
| warranty_claim | `index.php?route=information/warranty_claim/validateAjax` |
| product_enq | `index.php?route=information/product_enq/validateAjax` |
| find_dealer_form | `index.php?route=information/find_dealer_form/validateAjax` |
| autochairEnquiry | `index.php?route=information/autochairEnquiry/validateAjax` |
| lightDriveEnquiry | `index.php?route=information/lightDriveEnquiry/validateAjax` |
