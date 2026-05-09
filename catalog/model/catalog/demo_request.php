<?php
// all forms infop are saved from this file

class ModelCatalogDemoRequest extends Model {
    
    
    public function addDemoRequest($data) {
$is_manufacturer_or_product = isset($data['is_manufacturer_or_product']) ? (int)$data['is_manufacturer_or_product'] : 0;
$fullname         = isset($data['fullname']) ? $this->db->escape($data['fullname']) : '';
$email            = isset($data['email']) ? $this->db->escape($data['email']) : '';
$phone            = isset($data['phone']) ? $this->db->escape($data['phone']) : '';
$postcode         = isset($data['postcode']) ? $this->db->escape($data['postcode']) : '';
$demo_type        = isset($data['demo_type']) ? $this->db->escape($data['demo_type']) : '';
$dealer_name      = isset($data['dealer_name']) ? $this->db->escape($data['dealer_name']) : '';
$manufacturer_id  = isset($data['manufacturer_id']) ? (int)$data['manufacturer_id'] : 0;
$additional_info  = isset($data['additional_info']) ? $this->db->escape($data['additional_info']) : '';
$car_make  = isset($data['car_make']) ? $this->db->escape($data['car_make']) : '';
$car_model = isset($data['car_model']) ? $this->db->escape($data['car_model']) : '';
$car_year  = isset($data['car_year']) ? $this->db->escape($data['car_year']) : '';
$body_type = isset($data['body_type']) ? $this->db->escape($data['body_type']) : '';


$this->db->query("INSERT INTO " . DB_PREFIX . "demo_requests SET 
    fullname = '" . $fullname . "', 
    email = '" . $email . "', 
    phone = '" . $phone . "', 
    postcode = '" . $postcode . "', 
    demo_type = '" . $demo_type . "', 
    dealer_name = '" . $dealer_name . "', 
    manufacturer_id = '" . $manufacturer_id . "', 
    additional_info = '" . $additional_info . "', 
    car_make = '" . $car_make . "',
    car_model = '" . $car_model . "',
    car_year = '" . $car_year . "',
    body_type = '" . $body_type . "',
    is_manufacturer_or_product = '" . $is_manufacturer_or_product . "', 
    date_added = NOW()");

    }
    
    public function addFindDealerRequest($data) {
        
        
$is_manufacturer_or_product = isset($data['is_manufacturer_or_product']) ? (int)$data['is_manufacturer_or_product'] : 0;
$fullname         = isset($data['fullname']) ? $this->db->escape($data['fullname']) : '';
$email            = isset($data['email']) ? $this->db->escape($data['email']) : '';
$phone            = isset($data['phone']) ? $this->db->escape($data['phone']) : '';
$postcode         = isset($data['postcode']) ? $this->db->escape($data['postcode']) : '';
$dealer_name      = isset($data['dealer_name']) ? $this->db->escape($data['dealer_name']) : '';
$manufacturer_id  = isset($data['manufacturer_id']) ? (int)$data['manufacturer_id'] : 0;
$additional_info  = isset($data['additional_info']) ? $this->db->escape($data['additional_info']) : '';
$car_make  = isset($data['car_make']) ? $this->db->escape($data['car_make']) : '';
$car_model = isset($data['car_model']) ? $this->db->escape($data['car_model']) : '';
$car_year  = isset($data['car_year']) ? $this->db->escape($data['car_year']) : '';
$body_type = isset($data['body_type']) ? $this->db->escape($data['body_type']) : '';


$this->db->query("INSERT INTO " . DB_PREFIX . "findDealer_requests SET 
    fullname = '" . $fullname . "', 
    email = '" . $email . "', 
    phone = '" . $phone . "', 
    postcode = '" . $postcode . "', 
    dealer_name = '" . $dealer_name . "', 
    manufacturer_id = '" . $manufacturer_id . "', 
    additional_info = '" . $additional_info . "', 
    car_make = '" . $car_make . "',
    car_model = '" . $car_model . "',
    car_year = '" . $car_year . "',
    body_type = '" . $body_type . "',
    is_manufacturer_or_product = '" . $is_manufacturer_or_product . "', 
    date_added = NOW()");

    }
    
    
    
    public function addFundingSupport($data) {
        $fullname = isset($data['fullname']) ? $this->db->escape($data['fullname']) : '';
        $email = isset($data['email']) ? $this->db->escape($data['email']) : '';
        $phone = isset($data['phone']) ? $this->db->escape($data['phone']) : '';
        $postcode = isset($data['postcode']) ? $this->db->escape($data['postcode']) : '';
        $manufacturer_id = isset($data['manufacturer_id']) ? (int)$data['manufacturer_id'] : 0;
        $message = isset($data['message']) ? $this->db->escape($data['message']) : '';

        $this->db->query("INSERT INTO " . DB_PREFIX . "funding_support SET 
            fullname = '{$fullname}', 
            email = '{$email}', 
            phone = '{$phone}', 
            postcode = '{$postcode}', 
            manufacturer_id = '{$manufacturer_id}', 
            message = '{$message}', 
            date_added = NOW()");
    }
    
      public function addContactFormInfo($data) {
        $fullname = isset($data['name']) ? $this->db->escape($data['name']) : '';
        $email = isset($data['email']) ? $this->db->escape($data['email']) : '';
        $phone = isset($data['phone']) ? $this->db->escape($data['phone']) : '';
        $postcode = isset($data['postcode']) ? $this->db->escape($data['postcode']) : '';
        $manufacturer_id = isset($data['manufacturer_id']) ? (int)$data['manufacturer_id'] : 0;
        $contact_type = isset($data['contact_type']) ? $this->db->escape($data['contact_type']) : '';
        $healthcare_profession = isset($data['healthcare_profession']) ? $this->db->escape($data['healthcare_profession']) : '';
        $inquiry_reason = isset($data['inquiry_reason']) ? $this->db->escape($data['inquiry_reason']) : '';
        $message = isset($data['enquiry']) ? $this->db->escape($data['enquiry']) : '';

       $this->db->query("
        INSERT INTO " . DB_PREFIX . "contact_forms SET 
        fullname = '{$fullname}', 
        email = '{$email}', 
        phone = '{$phone}', 
        postcode = '{$postcode}', 
        manufacturer_id = '{$manufacturer_id}', 
        contact_type = '{$contact_type}', 
        healthcare_profession = '{$healthcare_profession}', 
        inquiry_reason = '{$inquiry_reason}', 
        message = '{$message}', 
        date_added = NOW()
");

    }
    
     public function addPlaceOrder($data) {
        $fullname = isset($data['fullname']) ? $this->db->escape($data['fullname']) : '';
        $email = isset($data['email']) ? $this->db->escape($data['email']) : '';
        $phone = isset($data['phone']) ? $this->db->escape($data['phone']) : '';
        $postcode = isset($data['postcode']) ? $this->db->escape($data['postcode']) : '';
        $business_name = isset($data['business_name']) ? $this->db->escape($data['business_name']) : '';
        $contact_type = isset($data['contact_type']) ? $this->db->escape($data['contact_type']) : '';
        $healthcare_profession = isset($data['healthcare_profession']) ? $this->db->escape($data['healthcare_profession']) : '';
        $manufacturer_id = isset($data['manufacturer_id']) ? (int)$data['manufacturer_id'] : 0;
        $message = isset($data['message']) ? $this->db->escape($data['message']) : '';

        $this->db->query("INSERT INTO " . DB_PREFIX . "place_orderForm SET 
            fullname = '{$fullname}', 
            email = '{$email}', 
            phone = '{$phone}', 
            postcode = '{$postcode}', 
            business_name = '{$business_name}', 
            contact_type = '{$contact_type}', 
            healthcare_profession = '{$healthcare_profession}', 
            manufacturer_id = '{$manufacturer_id}', 
            message = '{$message}', 
            date_added = NOW()");
    }
    
    public function addProductTrialRequest($data) {
        // product_id = manufactureer id
        $fullname = isset($data['fullname']) ? $this->db->escape($data['fullname']) : '';
        $email = isset($data['email']) ? $this->db->escape($data['email']) : '';
        $phone = isset($data['phone']) ? $this->db->escape($data['phone']) : '';
        $organisation = isset($data['organisation']) ? $this->db->escape($data['organisation']) : '';
        $address = isset($data['address']) ? $this->db->escape($data['address']) : '';
        $postcode = isset($data['postcode']) ? $this->db->escape($data['postcode']) : '';
        $profession = isset($data['profession']) ? $this->db->escape($data['profession']) : '';
        $profession_other = isset($data['profession_other']) ? $this->db->escape($data['profession_other']) : '';
        $client_fullname = isset($data['client_fullname']) ? $this->db->escape($data['client_fullname']) : '';
        $client_phone = isset($data['client_phone']) ? $this->db->escape($data['client_phone']) : '';
        $manufacturer_id = isset($data['manufacturer_id']) ? (int)$data['manufacturer_id'] : 0;
        $notes = isset($data['notes']) ? $this->db->escape($data['notes']) : '';

        $this->db->query("INSERT INTO " . DB_PREFIX . "producttrial_requests SET 
            fullname = '{$fullname}', 
            email = '{$email}', 
            phone = '{$phone}', 
            organisation = '{$organisation}', 
            address = '{$address}', 
            postcode = '{$postcode}',
            profession = '{$profession}', 
            profession_other = '{$profession_other}', 
            client_fullname = '{$client_fullname}', 
            client_phone = '{$client_phone}', 
            manufacturer_id = '{$manufacturer_id}', 
            notes = '{$notes}', 
            date_added = NOW()");
    }
    
   public function addQuoteRequest($data) {
    // Safe access using isset checks for future-proofing
    $fullname = isset($data['fullname']) ? $this->db->escape($data['fullname']) : '';
    $email = isset($data['email']) ? $this->db->escape($data['email']) : '';
    $phone = isset($data['phone']) ? $this->db->escape($data['phone']) : '';
    $postcode = isset($data['postcode']) ? $this->db->escape($data['postcode']) : '';
    $contact_type = isset($data['contact_type']) ? $this->db->escape($data['contact_type']) : '';
    $healthcare_profession = isset($data['healthcare_profession']) ? $this->db->escape($data['healthcare_profession']) : '';
    $quote_type = isset($data['quote_type']) ? $this->db->escape($data['quote_type']) : '';
    $manufacturer_id = isset($data['manufacturer_id']) ? (int)$data['manufacturer_id'] : 0;
    $additional_info = isset($data['additional_info']) ? $this->db->escape($data['additional_info']) : '';

    $vehicle_make = isset($data['vehicle_make']) ? $this->db->escape($data['vehicle_make']) : '';
    $vehicle_model = isset($data['vehicle_model']) ? $this->db->escape($data['vehicle_model']) : '';
    $vehicle_year = isset($data['vehicle_year']) ? $this->db->escape($data['vehicle_year']) : '';
    $body_type = isset($data['body_type']) ? $this->db->escape($data['body_type']) : '';
    $lifting_item = isset($data['lifting_item']) ? $this->db->escape($data['lifting_item']) : '';
    $item_weight = isset($data['item_weight']) ? (float)$data['item_weight'] : 0;
    $item_height = isset($data['item_height']) ? (float)$data['item_height'] : 0;
    $is_manufacturer_or_product = isset($data['is_manufacturer_or_product']) ? (int)$data['is_manufacturer_or_product'] : 0;

    $this->db->query("INSERT INTO " . DB_PREFIX . "quote_requests SET 
        fullname = '{$fullname}', 
        email = '{$email}', 
        phone = '{$phone}', 
        postcode = '{$postcode}',
        contact_type = '{$contact_type}', 
        healthcare_profession = '{$healthcare_profession}',
        quote_type = '{$quote_type}', 
        manufacturer_id = '{$manufacturer_id}', 
        additional_info = '{$additional_info}', 
        vehicle_make = '{$vehicle_make}',
        vehicle_model = '{$vehicle_model}',
        vehicle_year = '{$vehicle_year}',
        body_type = '{$body_type}',
        lifting_item = '{$lifting_item}',
        item_weight = '{$item_weight}',
        item_height = '{$item_height}',
        is_manufacturer_or_product = '{$is_manufacturer_or_product}',
        date_added = NOW()");
}
   
    public function addAutochairEnquiry($data) {
    // Safe access using isset checks for future-proofing
    $fullname = isset($data['fullname']) ? $this->db->escape($data['fullname']) : '';
    $email = isset($data['email']) ? $this->db->escape($data['email']) : '';
    $phone = isset($data['phone']) ? $this->db->escape($data['phone']) : '';
    $postcode = isset($data['postcode']) ? $this->db->escape($data['postcode']) : '';
    $contact_type = isset($data['contact_type']) ? $this->db->escape($data['contact_type']) : '';
    $healthcare_profession = isset($data['healthcare_profession']) ? $this->db->escape($data['healthcare_profession']) : '';
    $quote_type = isset($data['quote_type']) ? $this->db->escape($data['quote_type']) : '';
    $manufacturer_id = isset($data['manufacturer_id']) ? (int)$data['manufacturer_id'] : 0;
    $additional_info = isset($data['additional_info']) ? $this->db->escape($data['additional_info']) : '';

    $vehicle_make = isset($data['vehicle_make']) ? $this->db->escape($data['vehicle_make']) : '';
    $vehicle_model = isset($data['vehicle_model']) ? $this->db->escape($data['vehicle_model']) : '';
    $vehicle_year = isset($data['vehicle_year']) ? $this->db->escape($data['vehicle_year']) : '';
    $body_type = isset($data['body_type']) ? $this->db->escape($data['body_type']) : '';
    $lifting_item = isset($data['lifting_item']) ? $this->db->escape($data['lifting_item']) : '';
    $item_weight = isset($data['item_weight']) ? (float)$data['item_weight'] : 0;
    $item_height = isset($data['item_height']) ? (float)$data['item_height'] : 0;
    $is_manufacturer_or_product = isset($data['is_manufacturer_or_product']) ? (int)$data['is_manufacturer_or_product'] : 0;

    $this->db->query("INSERT INTO " . DB_PREFIX . "autochairEnquiry SET 
        fullname = '{$fullname}', 
        email = '{$email}', 
        phone = '{$phone}', 
        postcode = '{$postcode}',
        contact_type = '{$contact_type}', 
        healthcare_profession = '{$healthcare_profession}',
        quote_type = '{$quote_type}', 
        manufacturer_id = 0, 
        additional_info = '{$additional_info}', 
        vehicle_make = '{$vehicle_make}',
        vehicle_model = '{$vehicle_model}',
        vehicle_year = '{$vehicle_year}',
        body_type = '{$body_type}',
        lifting_item = '{$lifting_item}',
        item_weight = '{$item_weight}',
        item_height = '{$item_height}',
        is_manufacturer_or_product = 1,
        date_added = NOW()");
}

  public function addLightDriveEnquiry($data) {
    // Safe access using isset checks for future-proofing
    $fullname = isset($data['fullname']) ? $this->db->escape($data['fullname']) : '';
    $email = isset($data['email']) ? $this->db->escape($data['email']) : '';
    $phone = isset($data['phone']) ? $this->db->escape($data['phone']) : '';
    $postcode = isset($data['postcode']) ? $this->db->escape($data['postcode']) : '';
    $contact_type = isset($data['contact_type']) ? $this->db->escape($data['contact_type']) : '';
    $healthcare_profession = isset($data['healthcare_profession']) ? $this->db->escape($data['healthcare_profession']) : '';
    $quote_type = isset($data['quote_type']) ? $this->db->escape($data['quote_type']) : '';
    $additional_info = isset($data['additional_info']) ? $this->db->escape($data['additional_info']) : '';

    

    $this->db->query("INSERT INTO " . DB_PREFIX . "lightDriveEnquiry SET 
        fullname = '{$fullname}', 
        email = '{$email}', 
        phone = '{$phone}', 
        postcode = '{$postcode}',
        contact_type = '{$contact_type}', 
        healthcare_profession = '{$healthcare_profession}',
        quote_type = '{$quote_type}', 
        manufacturer_id = 0, 
        additional_info = '{$additional_info}', 
        is_manufacturer_or_product = 1,
        date_added = NOW()");
}

  public function addRaymexLiftEnquiry($data) {
    $fullname = isset($data['fullname']) ? $this->db->escape($data['fullname']) : '';
    $email = isset($data['email']) ? $this->db->escape($data['email']) : '';
    $phone = isset($data['phone']) ? $this->db->escape($data['phone']) : '';
    $postcode = isset($data['postcode']) ? $this->db->escape($data['postcode']) : '';
    $contact_type = isset($data['contact_type']) ? $this->db->escape($data['contact_type']) : '';
    $additional_info = isset($data['additional_info']) ? $this->db->escape($data['additional_info']) : '';

    $this->db->query("INSERT INTO " . DB_PREFIX . "raymex_lift_enquiry SET 
        fullname = '{$fullname}', 
        email = '{$email}', 
        phone = '{$phone}', 
        postcode = '{$postcode}',
        contact_type = '{$contact_type}', 
        additional_info = '{$additional_info}', 
        date_added = NOW()");
}


    public function getProductsByCategory($category_id) {
       $query = $this->db->query("
    SELECT pd.product_id, pd.name 
    FROM " . DB_PREFIX . "product_to_category p2c 
    LEFT JOIN " . DB_PREFIX . "product_description pd 
    ON p2c.product_id = pd.product_id 
    WHERE p2c.category_id = '" . (int)$category_id . "'
");
        return $query->rows;
    }
    
    public function addProductEnquiry($data) {
        $fullname = isset($data['fullname']) ? $this->db->escape($data['fullname']) : '';
        $email = isset($data['email']) ? $this->db->escape($data['email']) : '';
        $phone = isset($data['phone']) ? $this->db->escape($data['phone']) : '';
        $postcode = isset($data['postcode']) ? $this->db->escape($data['postcode']) : '';
        $contact_type = isset($data['contact_type']) ? $this->db->escape($data['contact_type']) : '';
        $healthcare_profession = isset($data['healthcare_profession']) ? $this->db->escape($data['healthcare_profession']) : '';
        $inquiry_reason = isset($data['inquiry_reason']) ? $this->db->escape($data['inquiry_reason']) : '';
        $manufacturer_id = isset($data['manufacturer_id']) ? (int)$data['manufacturer_id'] : 0;
        $message = isset($data['message']) ? $this->db->escape($data['message']) : '';

        $this->db->query("INSERT INTO " . DB_PREFIX . "product_inquiry SET 
            fullname = '{$fullname}', 
            email = '{$email}', 
            phone = '{$phone}', 
            postcode = '{$postcode}', 
            contact_type = '{$contact_type}', 
            healthcare_profession = '{$healthcare_profession}', 
            inquiry_reason = '{$inquiry_reason}', 
            manufacturer_id = '{$manufacturer_id}', 
            message = '{$message}', 
            date_added = NOW()");
            
         
    }
}
