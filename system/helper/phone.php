<?php

function is_valid_au_phone($phone) {
    // Clean the phone number (remove spaces, dashes, parentheses, and leading +)
    $phone = preg_replace('/[\s\-\(\)\+]+/', '', $phone);
    
    // Empty check
    if (empty($phone)) {
        return false;
    }
    
    // Must be digits only after cleaning
    if (!preg_match('/^[0-9]+$/', $phone)) {
        return false;
    }
    
    // Convert international format to local format for validation
    // 61411114916 (11 digits with country code) -> 0411114916 (10 digits local)
    if (preg_match('/^61([2-9][0-9]{8})$/', $phone, $matches)) {
        $phone = '0' . $matches[1];
    }
    
    // Handle 9-digit mobile numbers without leading 0 (e.g., 412345678 -> 0412345678)
    // Only auto-prefix for mobile numbers starting with 4
    if (strlen($phone) === 9 && $phone[0] === '4') {
        $phone = '0' . $phone;
    }
    
    // Now validate as 10-digit Australian number starting with 0
    if (strlen($phone) !== 10) {
        return false;
    }
    
    // Must start with 0
    if ($phone[0] !== '0') {
        return false;
    }
    
    // Validate against known Australian number patterns:
    // Mobile:   04XX XXX XXX
    // NSW/ACT:  02 [4569] then 7 digits  (024x,025x,026x,029x — Sydney 028x,029x)
    // VIC/TAS:  03 [5-9] then 7 digits
    // QLD:      07 [2-57] then 7 digits
    // SA/WA/NT: 08 [1-9] then 7 digits
    $validPatterns = '/^('
        . '04[0-9]{8}'          // Mobile
        . '|02[45689][0-9]{7}'  // NSW/ACT landline
        . '|03[5-9][0-9]{7}'   // VIC/TAS landline
        . '|07[2-57][0-9]{7}'  // QLD landline
        . '|08[1-9][0-9]{7}'   // SA/WA/NT landline
        . ')$/';
    
    if (!preg_match($validPatterns, $phone)) {
        return false;
    }
    
    return true;
}

function format_au_phone_e164($phone) {
    try {
        $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
        $number = $phoneUtil->parse($phone, 'AU');
        return $phoneUtil->format(
            $number,
            \libphonenumber\PhoneNumberFormat::E164
        );
    } catch (Exception $e) {
        return null;
    }
}
