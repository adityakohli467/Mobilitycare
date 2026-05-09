<?php
class ControllerCatalogFormRequest extends Controller {
    
   public function demo_request() { 
    $this->load->model('catalog/form_request');
    $this->load->model('catalog/manufacturer');

    $data['heading_title'] = 'Demo Requests';
    $data['trial_requests'] = [];

    $results = $this->model_catalog_form_request->getDemoRequests();

    foreach ($results as $result) {

        // Determine manufacturer_id based on the is_manufacturer_or_product flag
       
      

        // ADD NEW FIELDS HERE IN RESPONSE
        $data['demo_requests'][] = [
            'demo_request_id' => $result['demo_request_id'],
            'fullname'        => $result['fullname'],
            'email'           => $result['email'],
            'phone'           => $result['phone'],
            'postcode'        => $result['postcode'],
            'demo_type'       => $result['demo_type'],
            'product_name'    => $result['manufacturer_name'], // manufacturer_name or product name
            'car_make'        => $result['car_make'],
            'car_model'       => $result['car_model'],
            'car_year'        => $result['car_year'],
            'body_type'       => $result['body_type'],
            'additional_info' => !empty($result['additional_info']) ? $result['additional_info'] : 'None',
            'date_added'      => $result['date_added']
        ];
    }

    // Load view
    $data['header'] = $this->load->controller('common/header');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['footer'] = $this->load->controller('common/footer');

    $this->response->setOutput($this->load->view('forms/demo_request', $data));
}


 public function findDealerForm_request() { 
    $this->load->model('catalog/form_request');
    $this->load->model('catalog/manufacturer');

    $data['heading_title'] = 'Dind Dealer Form Request';
    $data['trial_requests'] = [];

    $results = $this->model_catalog_form_request->getFindDealerFormRequests();

    foreach ($results as $result) {

        // Determine manufacturer_id based on the is_manufacturer_or_product flag
       
      

        // ADD NEW FIELDS HERE IN RESPONSE
        $data['findDealerForm_requests'][] = [
            'demo_request_id' => $result['demo_request_id'],
            'fullname'        => $result['fullname'],
            'email'           => $result['email'],
            'phone'           => $result['phone'],
            'postcode'        => $result['postcode'],
            'product_name'    => $result['manufacturer_name'], // manufacturer_name or product name
            'car_make'        => $result['car_make'],
            'car_model'       => $result['car_model'],
            'car_year'        => $result['car_year'],
            'body_type'       => $result['body_type'],
            'additional_info' => !empty($result['additional_info']) ? $result['additional_info'] : 'None',
            'date_added'      => date($this->language->get('datetime_format'), strtotime($result['date_added']))
        ];
    }

    // Load view
    $data['header'] = $this->load->controller('common/header');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['footer'] = $this->load->controller('common/footer');

    $this->response->setOutput($this->load->view('forms/find_dealer_form', $data));
}



     public function quote_request() {
    $this->load->model('catalog/form_request');
    $this->document->setTitle('Quote Requests');

    $data['requests'] = $this->model_catalog_form_request->getQuoteRequests();

    $data['header'] = $this->load->controller('common/header');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['footer'] = $this->load->controller('common/footer');

    $data['actionurl'] = $this->url->link('catalog/form_request/view_quote_request', 'user_token=' . $this->session->data['user_token'], true);

    $this->response->setOutput($this->load->view('forms/quote_request', $data));
}


       public function view_quote_request() {
    $quoteId = isset($this->request->get['id']) ? (int)$this->request->get['id'] : 0;

    $this->document->setTitle('View Quote Request');
    $this->load->model('catalog/form_request');

    $data['quoteData'] = $this->model_catalog_form_request->getQuoteRequests($quoteId);
    $data['cancel'] = $this->url->link('catalog/form_request/quote_request', 'user_token=' . $this->session->data['user_token'], true);


    $data['header'] = $this->load->controller('common/header');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['footer'] = $this->load->controller('common/footer');

    $this->response->setOutput($this->load->view('forms/view_quote_request', $data));
}


    
    public function product_inquiry() {
        $this->load->model('catalog/form_request');
        $this->document->setTitle('Product Inquiry');
        $data['requests'] = $this->model_catalog_form_request->getProductInquiry();
        // echo "<pre>"; print_r($data['requests']); exit;
        $data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->load->view('forms/product_inquiry', $data));
    }
    
      public function LightDriveEnq() {
          
          ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
        $this->load->model('catalog/form_request');
        $this->document->setTitle('Light Drive Inquiry');
        $data['requests'] = $this->model_catalog_form_request->getLightDriveInquiry();
        // echo "<pre>"; print_r($data['requests']); exit;
        $data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->load->view('forms/lightDriveInquiry', $data));
    }

      public function RaymexLiftEnq() {
        $this->load->model('catalog/form_request');
        $this->document->setTitle('RAYMEX Lift Enquiries');
        $data['requests'] = $this->model_catalog_form_request->getRaymexLiftInquiry();
        $data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->load->view('forms/raymexLiftInquiry', $data));
    }
    
      public function AutochairEnq() {
       
        $this->load->model('catalog/form_request');
        $this->document->setTitle('Autochair Inquiry');
        $data['requests'] = $this->model_catalog_form_request->autochairInquiry();
        
        $data['actionurl'] = $this->url->link('catalog/form_request/view_autochair_enq', 'user_token=' . $this->session->data['user_token'], true);

        // echo "<pre>"; print_r($data['requests']); exit;
        $data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->load->view('forms/autochairInquiry', $data));
    }
    
    
      public function view_autochair_enq() {
    $quoteId = isset($this->request->get['id']) ? (int)$this->request->get['id'] : 0;

    $this->document->setTitle('View Autochair Enquiry');
    $this->load->model('catalog/form_request');

    $data['quoteData'] = $this->model_catalog_form_request->getAutochairEnqRequests($quoteId);
    
    $data['cancel'] = $this->url->link('catalog/form_request/AutochairEnq', 'user_token=' . $this->session->data['user_token'], true);


    $data['header'] = $this->load->controller('common/header');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['footer'] = $this->load->controller('common/footer');

    $this->response->setOutput($this->load->view('forms/view_autochair_enq', $data));
}

    
    
    public function warranty_claims() {
        $this->load->model('catalog/form_request');
        $this->document->setTitle('Qarranty Claims');
        $data['requests'] = $this->model_catalog_form_request->getWarrantyClaims();
        $data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$data['actionurl'] = $this->url->link('catalog/form_request/view_warranty_claims', 'user_token=' . $this->session->data['user_token']  , true);
        $this->response->setOutput($this->load->view('forms/warranty_claims', $data));
    }
    
     function view_warranty_claims(){
       $claimId =  $this->request->get['id'];
       $this->document->setTitle('Qarranty Claims');
       $this->load->model('catalog/form_request');
        $data['requests'] = $this->model_catalog_form_request->getWarrantyClaims($claimId);
        // echo "<pre>"; print_r($data['requests']); exit;
        $data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		if(isset($data['requests'][0]['uploaded_files']) && $data['requests'][0]['uploaded_files'] !=''){
		 $decoded_files = json_decode($data['requests'][0]['uploaded_files'], true);
         $data['uploaded_files'] = is_array($decoded_files) ? $decoded_files : [];    
		}else{
		  $data['uploaded_files'] = array(); 
		}
	  
	   $data['base_urlnew'] = rtrim(HTTPS_SERVER, 'admin/');
 
	    $data['cancel'] = $this->url->link('catalog/form_request/warranty_claims', 'user_token=' . $this->session->data['user_token'], true);

        $this->response->setOutput($this->load->view('forms/view_warranty_claims', $data));
    }
    
    function productTrialRequests() { 
        $data['trial_requests'] = array();
         $this->load->model('catalog/form_request');
           $this->load->model('catalog/manufacturer');
         
        $results = $this->model_catalog_form_request->getProductTrialRequests();
        $data['heading_title'] = 'Product Trial Requests';
        foreach ($results as $result) {
          
      $manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($result['manufacturer_id']);
      $manufacturer_name = $manufacturer_info ? html_entity_decode($manufacturer_info['name'], ENT_QUOTES, 'UTF-8') : 'Unknown manufacturer';


            $data['trial_requests'][] = array(
                'demo_request_id' => $result['demo_request_id'],
                'fullname' => $result['fullname'],
                'email' => $result['email'],
                'phone' => $result['phone'],
                'organisation' => $result['organisation'],
                'address' => $result['address'],
                'postcode' => $result['postcode'],
                'profession' => $result['profession'] . ($result['profession_other'] ? ' (' . $result['profession_other'] . ')' : ''),
                'client_fullname' => $result['client_fullname'] ?: 'Not provided',
                'client_phone' => $result['client_phone'] ?: 'Not provided',
                'product_name' => $manufacturer_name,
                'notes' => $result['notes'] ?: 'None',
                'date_added' => date($this->language->get('datetime_format'), strtotime($result['date_added']))
            );
        }
        
        $data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->load->view('forms/product_trial_request_list', $data));
    }
    
    function fundingSupportRequests() { 
        $data['trial_requests'] = array();
         $this->load->model('catalog/form_request');
           $this->load->model('catalog/manufacturer');
         
        $results = $this->model_catalog_form_request->getFundingSupportRequests();
        $data['heading_title'] = 'Funding Support Requests';
        foreach ($results as $result) {
          
      $manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($result['manufacturer_id']);
      $manufacturer_name = $manufacturer_info ? html_entity_decode($manufacturer_info['name'], ENT_QUOTES, 'UTF-8') : 'Unknown manufacturer';


            $data['trial_requests'][] = array(
               
                'fullname' => $result['fullname'],
                'email' => $result['email'],
                'phone' => $result['phone'],
                'postcode' => $result['postcode'],
                'product_name' => $manufacturer_name,
                'message' => $result['message'] ?: 'None',
                'date_added' => date($this->language->get('datetime_format'), strtotime($result['date_added']))
            );
        }
        
        $data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->load->view('forms/funding_support_request_list', $data));
    }
    
    function placeorderRequests() { 
        $data['requests'] = array();
         $this->load->model('catalog/form_request');
           $this->load->model('catalog/manufacturer');
         
        $results = $this->model_catalog_form_request->getPlaceOrderRequests();
        $data['heading_title'] = 'Place Order Requests';
        foreach ($results as $result) {
          
      $manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($result['manufacturer_id']);
      $manufacturer_name = $manufacturer_info ? html_entity_decode($manufacturer_info['name'], ENT_QUOTES, 'UTF-8') : 'Unknown manufacturer';


            $data['requests'][] = array(
                
                'fullname' => $result['fullname'],
                'email' => $result['email'],
                'phone' => $result['phone'],
                'postcode' => $result['postcode'],
                'contact_type' => $result['contact_type'],
                'healthcare_profession' => $result['healthcare_profession'],
                'business_name' => $result['business_name'],
                 'product_name' => $manufacturer_name,
                'message' => $result['message'] ?: 'None',
                'date_added' => date($this->language->get('datetime_format'), strtotime($result['date_added']))
            );
        }
        
        $data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->load->view('forms/placeorder_request_list', $data));
    }
    
    
    function contactUsFormRequests() { 
        $data['requests'] = array();
         $this->load->model('catalog/form_request');
           $this->load->model('catalog/manufacturer');
         
        $results = $this->model_catalog_form_request->contactUsFormRequests();
        $data['heading_title'] = 'Contact Us Form Requests';
        foreach ($results as $result) {
          
      $manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($result['manufacturer_id']);
      $manufacturer_name = $manufacturer_info ? html_entity_decode($manufacturer_info['name'], ENT_QUOTES, 'UTF-8') : 'Unknown manufacturer';


            $data['forms'][] = array(
                'id' => $result['id'],
                'fullname' => $result['fullname'],
                'email' => $result['email'],
                'phone' => $result['phone'],
                'postcode' => $result['postcode'],
                'contact_type' => $result['contact_type'],
                'inquiry_reason' => $result['inquiry_reason'],
                'message' => nl2br($result['message']),
                'date_added' => date('Y-m-d H:i', strtotime($result['date_added']))
            );
        
        }
        
        $data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->load->view('forms/contact_forms', $data));
    }
    
}
