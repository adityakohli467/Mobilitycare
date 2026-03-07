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
    
    // Handle 9-digit numbers without leading 0 (e.g., 411114916 -> 0411114916)
    if (strlen($phone) === 9 && preg_match('/^[2-9]/', $phone)) {
        $phone = '0' . $phone;
    }
    
    // Now validate as 10-digit Australian number starting with 0
    // Australian mobile: 04XX XXX XXX
    // Australian landline: 02/03/07/08 XXXX XXXX
    if (strlen($phone) !== 10) {
        return false;
    }
    
    // Must start with 0
    if ($phone[0] !== '0') {
        return false;
    }
    
    // Valid Australian prefixes:
    // 04 - Mobile
    // 02 - NSW/ACT
    // 03 - VIC/TAS  
    // 07 - QLD
    // 08 - SA/WA/NT
    $validPrefixes = ['02', '03', '04', '07', '08'];
    $prefix = substr($phone, 0, 2);
    
    if (!in_array($prefix, $validPrefixes)) {
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
