<?php
class ControllerDealerDealer extends Controller {
    private $error = array();
    
    public function updateCoordinates() {
    // Allow only admin access if needed
    $this->response->addHeader('Content-Type: text/plain');

    $apiKey = 'AIzaSyCdIW7xk3UQDxMkhznl2gEyabtnGXHN2ww'; // Replace with your key

    // Load DB connection
    $query = $this->db->query("
        SELECT dealer_id, postcode, suburb, state 
        FROM " . DB_PREFIX . "dealers 
        WHERE (latitude IS NULL OR latitude = '' OR longitude IS NULL OR longitude = '') 
        AND postcode != '' 
        AND postcode IS NOT NULL
    ");

    if (!$query->num_rows) {
        echo "All dealers already have latitude/longitude set.\n";
        return;
    }

    echo "🔍 Found " . $query->num_rows . " dealers missing coordinates.\n";

    foreach ($query->rows as $row) {
        $dealer_id = (int)$row['dealer_id'];
        $postcode = trim($row['postcode']);
        $address = urlencode($row['suburb'] . ', ' . $row['state'] . ' ' . $postcode);

        $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$address}&key={$apiKey}";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);

        if (isset($data['status']) && $data['status'] === 'OK') {
            $lat = $data['results'][0]['geometry']['location']['lat'];
            $lng = $data['results'][0]['geometry']['location']['lng'];

            // Update DB
            $this->db->query("
                UPDATE " . DB_PREFIX . "dealers 
                SET latitude = '" . (float)$lat . "', longitude = '" . (float)$lng . "' 
                WHERE dealer_id = " . (int)$dealer_id . "
            ");

            echo "✅ Updated Dealer ID {$dealer_id} — {$postcode} => {$lat}, {$lng}\n";
        } else {
            echo "⚠️ Failed for Dealer ID {$dealer_id} ({$postcode}) — Status: {$data['status']}\n";
        }

        // Pause slightly to respect API rate limits
        sleep(1);
        ob_flush();
        flush();
    }

    echo "🎉 All done!";
}


    public function index() {
//         ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

        // $this->load->language('dealer/dealer');
        $this->document->setTitle('Become a Dealer');
        $this->load->model('catalog/category');
        $this->load->model('dealer/dealer');
        $dealerId = '';
        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validate()) {
             $mailMessageHtml = $this->mailHtml($this->request->post);

                try {
    $mail = new Mail($this->config->get('config_mail_engine'));
    $mail->parameter = $this->config->get('config_mail_parameter');
    $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
    $mail->smtp_username = $this->config->get('config_mail_smtp_username');
    $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
    $mail->smtp_port = $this->config->get('config_mail_smtp_port');
    $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
    $mail->setTo($this->config->get('config_email'));
    // $mail->setTo('kohliaditya@yahoo.com');
    $mail->setFrom('enquiries@mobilitycare.net.au');
    $replyTo = (isset($this->request->post['email']) ? $this->request->post['email'] : 'enquiries@mobilitycare.net.au');
    $mail->setReplyTo($replyTo);
    $mail->setSender(html_entity_decode('MobilityCare', ENT_QUOTES, 'UTF-8'));
    $mail->setSubject('New form submission for interest in becoming a dealer');
    $mail->setHtml($mailMessageHtml);
    $mail->send();
    
     //  Send automatic confirmation email to customer
     if (isset($this->request->post['email']) && filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
        $customerMail = new Mail($this->config->get('config_mail_engine'));
        $customerMail->parameter = $this->config->get('config_mail_parameter');
        $customerMail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
        $customerMail->smtp_username = $this->config->get('config_mail_smtp_username');
        $customerMail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
        $customerMail->smtp_port = $this->config->get('config_mail_smtp_port');
        $customerMail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
        $customerMail->setTo($this->request->post['email']);
        $customerMail->setFrom('enquiries@mobilitycare.net.au');
        $customerMail->setReplyTo('enquiries@mobilitycare.net.au');
        $customerMail->setSender(html_entity_decode('MobilityCare', ENT_QUOTES, 'UTF-8'));
        $customerMail->setSubject('Thank you for showing interest in becoming a dealer. - MobilityCare');
        
        // Load the email template
        $data['customer_name'] = isset($this->request->post['full_name']) ? htmlspecialchars($this->request->post['full_name']) : 'Valued Customer';
        
        $customerMessageHtml = $this->load->view('mail/enquiry_confirmation', $data);
        
        $customerMail->setHtml($customerMessageHtml);
        $customerMail->send();
        
        
        
    }
    
    
    
}               catch (Exception $e) {
    // Log or show the error
    $this->log->write('MAIL ERROR: ' . $e->getMessage());
    echo '<pre>Mail Error: ' . $e->getMessage() . '</pre>';
}
            
            
           $dealerId = $this->model_dealer_dealer->addDealer($this->request->post);
            $this->session->data['success'] = 'Your request has been submitted successfully!';
            $this->response->redirect($this->url->link('information/form_success/becomedealer'));
        }
        $category_id = 0;
        $categories = $this->model_catalog_category->getCategories($category_id);
        $data['categories'] = [];
        if(isset($categories) && !empty($categories)){
         foreach ($categories as $category) {
            $data['categories'][] = [
                'category_id' => $category['category_id'],
                'name'        => $category['name']
            ];
        }   
        }
        
        
        $data['action'] = $this->url->link('dealer/dealer', '', true);
        
        $data['captcha'] = '';
		 $this->load->model('catalog/manufacturer');
	    $sortdata['sort'] = 'sort_order';
	    $sortdata['order'] ='ASC';
		$manufacturers = $this->model_catalog_manufacturer->getManufacturers($sortdata);
		
		$data['manufacturers'] = [];

        foreach ($manufacturers as $manufacturer) {
            $data['manufacturers'][] = [
                'id'   => $manufacturer['manufacturer_id'],
                'name' => $manufacturer['name']
            ];
        }
       
        if (isset($this->session->data['success'])) {
         $data['success'] = $this->session->data['success'];
          unset($this->session->data['success']); 
        } else {
         $data['success'] = '';
        }
        
        $data['error_phone'] = isset($this->error['phone']) ? $this->error['phone'] : '';

        $data['header'] = $this->load->controller('common/header');
        $data['footer'] = $this->load->controller('common/footer');
         
        $this->response->setOutput($this->load->view('dealer/dealer', $data));
    }
    
    public function getDealers() {
        $json = [
            'success' => false,
            'message' => 'Unknown error occurred'
        ];

        try {
            // Validate input parameters
            if (!isset($this->request->post['manufacturer_id']) || !is_numeric($this->request->post['manufacturer_id'])) {
                $json['message'] = 'Invalid or missing manufacturer ID';
                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($json));
                return;
            }

            if (!isset($this->request->post['selectedState']) || empty(trim($this->request->post['selectedState']))) {
                $json['message'] = 'Invalid or missing state';
                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($json));
                return;
            }

            // updatedPostcodes is optional but should be a string if provided
            $updatedPostcodes = isset($this->request->post['updatedPostcodes']) ? trim($this->request->post['updatedPostcodes']) : '';
            $postcodeArray = $updatedPostcodes ? array_filter(array_map('trim', explode(',', $updatedPostcodes))) : [];

            $manufacturer_id = (int)$this->request->post['manufacturer_id'];
            $selectedState = trim($this->request->post['selectedState']);

            // Load the model
            $this->load->model('dealer/dealer');

            // Fetch dealers
            $dealers = $this->model_dealer_dealer->getDealersByManufacturer($manufacturer_id, $selectedState, $postcodeArray);

            if ($dealers === false || $dealers === null) {
                throw new Exception('Failed to fetch dealers from database');
            }

            if (empty($dealers)) {
                $json['success'] = false;
                $json['message'] = 'No dealers found for the given manufacturer and state';
                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($json));
                return;
            }

            // Fetch products
            $dealersProducts = $this->model_dealer_dealer->getProductsByManufacturer($manufacturer_id);

            // Prepare successful response
            $json['success'] = true;
            $json['message'] = 'Dealers retrieved successfully';
            $json['dealers'] = $dealers;
            $json['dealersCount'] = count($dealers);
            if ($dealersProducts) {
                $json['dealersProducts'] = $dealersProducts;
            } else {
                $json['dealersProducts'] = []; // Return empty array for consistency
            }

        } catch (Exception $e) {
            // Catch any unexpected errors (e.g., database issues)
            $json['success'] = false;
            $json['message'] = 'Server error: ' . $e->getMessage();
        }

        // Set response headers and output JSON
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function fetchDealerfromPostcode() {
        
    $this->response->addHeader('Content-Type: application/json');
    $json = [];

    // Read JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    // Validate inputs
    if (!empty($input['lat']) && !empty($input['lng'])) {
        
        $lat = (float)$input['lat'];
        $lng = (float)$input['lng'];
        $radius = (int)$input['range'];
        $manufacturerId = !empty($input['manufacturerId']) ? (int)$input['manufacturerId'] : 0;
        $productId = !empty($input['product_id']) ? (int)$input['product_id'] : 0;
        $selectedState = trim($input['selectedState']);

        $this->load->model('dealer/dealer');

        // Fetch dealers using lat/lng + radius
        $dealers = $this->model_dealer_dealer->getDealersWithinRadius($lat,$lng, $radius,$manufacturerId,$productId,$selectedState);

        $json['dealers'] = $dealers;
    } else {
        $json['error'] = 'Latitude and Longitude are required.';
    }

    $this->response->setOutput(json_encode($json));
}

    private function validate() {
        if (empty($this->request->post['full_name'])) {
            $this->error['full_name'] = 'Full Name is required!';
        }
        if (empty($this->request->post['email']) || !filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
            $this->error['email'] = 'Valid Email is required!';
        }
        if (empty($this->request->post['business_name'])) {
            $this->error['business_name'] = 'Business Name is required!';
        }
        if (empty($this->request->post['phone'])) {
            $this->error['phone'] = 'Phone Number is required!';
        }
        
        
        	// validate phone no is from AUS
	$this->load->helper('phone');	
     if (!is_valid_au_phone($this->request->post['phone'])) {
      $this->error['phone'] = 'Please enter a valid Australian phone number';
     }
        if (empty($this->request->post['business_address'])) {
            $this->error['business_address'] = 'Business Address is required!';
        }
        if (empty($this->request->post['abn'])) {
            $this->error['abn'] = 'ABN is required!';
        }
        
        // Captcha
// 		if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('contact', (array)$this->config->get('config_captcha_page'))) {
// 			$captcha = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha') . '/validate');

// 			if ($captcha) {
// 				$this->error['captcha'] = $captcha;
// 			}
// 		}

        return !$this->error;
    }
    
     private function mailHtml($post) {

       
        
        
    $html = '<html><body>';
$html .= '<h3>Request for become a dealer form is submitted </h3>';

$html .= '<p><b>Full Name:</b> ' . htmlspecialchars(isset($post['full_name']) ? $post['full_name'] : '') . '</p>';
$html .= '<p><b>Email:</b> ' . htmlspecialchars(isset($post['email']) ? $post['email'] : '') . '</p>';
$html .= '<p><b>Phone:</b> ' . htmlspecialchars(isset($post['phone']) ? $post['phone'] : '') . '</p>';
$html .= '<p><b>Postcode:</b> ' . htmlspecialchars(isset($post['postcode']) ? $post['postcode'] : '') . '</p>';
$html .= '<p><b>Business name:</b> ' . htmlspecialchars(isset($post['business_name']) ? $post['business_name'] : '') . '</p>';


$html .= '</body></html>';


        return $html;
    }
}
?>