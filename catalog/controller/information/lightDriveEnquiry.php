<?php
class ControllerInformationLightDriveEnquiry extends Controller {
    private $error = array();

    public function index() {
        $this->load->language('information/contact');
        $this->load->model('catalog/demo_request');


        $this->document->setTitle('Light Drive 2 Enquiry | MobilityCare Australia');
        $this->document->setDescription('Enquire about the Benoit Systemes Light Drive 2 wheelchair power assist. Get pricing, availability and book a demo with MobilityCare Australia.');

        // Handle form submission
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {

            $skip_validation = false;
            if (isset($this->session->data['lightdrive_ajax_validated']) && $this->session->data['lightdrive_ajax_validated'] === true) {
                $skip_validation = true;
                unset($this->session->data['lightdrive_ajax_validated']);
            }

            if ($skip_validation || $this->validate()) {

                // Default empty for missing fields
                $fields = ['contact_type', 'healthcare_profession', 'quote_type', 'additional_info'];
                foreach ($fields as $field) {
                    if (!isset($this->request->post[$field])) {
                        $this->request->post[$field] = '';
                    }
                }

                // Send mail
                $mailMessageHtml = $this->mailHtml($this->request->post);

                // Save in database FIRST so data is never lost even if mail fails
                try {
                    $this->model_catalog_demo_request->addLightDriveEnquiry($this->request->post);
                } catch (\Throwable $dbError) {
                    $this->log->write('LIGHTDRIVE DB SAVE ERROR: ' . $dbError->getMessage());
                }

                // Queue emails for async sending (instant — no SMTP wait)
                $this->load->helper('mail_queue');
                $replyTo = isset($this->request->post['email']) ? $this->request->post['email'] : 'enquiries@mobilitycare.net.au';

                mail_queue_add($this->db, [
                    'to'       => $this->config->get('config_email'),
                    'from'     => 'enquiries@mobilitycare.net.au',
                    'sender'   => 'MobilityCare',
                    'reply_to' => $replyTo,
                    'subject'  => 'New Light Drive Enquiry Received',
                    'html'     => $mailMessageHtml,
                    'priority' => 2,
                ]);

                if (isset($this->request->post['email']) && filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
                    $data['customer_name'] = isset($this->request->post['fullname']) ? htmlspecialchars($this->request->post['fullname']) : 'Valued Customer';
                    $customerHtml = $this->load->view('mail/enquiry_confirmation', $data);
                    mail_queue_add($this->db, [
                        'to'       => $this->request->post['email'],
                        'from'     => 'enquiries@mobilitycare.net.au',
                        'sender'   => 'MobilityCare',
                        'reply_to' => 'enquiries@mobilitycare.net.au',
                        'subject'  => 'Thank You for Your Enquiry - MobilityCare',
                        'html'     => $customerHtml,
                    ]);
                }

                // redirect
                 $this->session->data['success'] = 'Your enquiry has been successfully submitted.';
                 $this->response->redirect($this->url->link('information/form_success/lightdrive'));
            } 
        }

        $data['action'] = '/light-drive-2-enquiry';
        $data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';
        $data['error_fullname'] = isset($this->error['fullname']) ? $this->error['fullname'] : '';
        $data['error_email'] = isset($this->error['email']) ? $this->error['email'] : '';
        $data['error_phone'] = isset($this->error['phone']) ? $this->error['phone'] : '';
        
        
        $data['captcha'] = '';

        $data['header'] = $this->load->controller('common/header');
        $data['footer'] = $this->load->controller('common/footer');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');

        $this->response->setOutput($this->load->view('information/lightDriveEnquiry', $data));
    }

   

    private function sendJson($data) {
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));
        return;
    }

    private function mailHtml($post) {

       if(isset($post['is_manufacturer_or_product']) && $post['is_manufacturer_or_product'] == 1){
           $this->load->model('catalog/product');
         $product_info = $this->model_catalog_product->getProduct($post['manufacturer_id']);
        $brandOrProductname = $product_info ? html_entity_decode($product_info['name'], ENT_QUOTES, 'UTF-8') : 'Unknown Product';  
        }else{
             $this->load->model('catalog/manufacturer');
           $manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($post['manufacturer_id']);
        $brandOrProductname = $manufacturer_info ? html_entity_decode($manufacturer_info['name'], ENT_QUOTES, 'UTF-8') : 'Unknown Manufacturer';
   
        }
        
        
    $html = '<html><body>';
$html .= '<h3>New Light Drive Enquiry Request Received</h3>';

$html .= '<p><b>Full Name:</b> ' . htmlspecialchars(isset($post['fullname']) ? $post['fullname'] : '') . '</p>';
$html .= '<p><b>Email:</b> ' . htmlspecialchars(isset($post['email']) ? $post['email'] : '') . '</p>';
$html .= '<p><b>Phone:</b> ' . htmlspecialchars(isset($post['phone']) ? $post['phone'] : '') . '</p>';
$html .= '<p><b>Postcode:</b> ' . htmlspecialchars(isset($post['postcode']) ? $post['postcode'] : '') . '</p>';
$html .= '<p><b>Contact Type:</b> ' . htmlspecialchars(isset($post['contact_type']) ? $post['contact_type'] : '') . '</p>';
$html .= '<p><b>Quote Type:</b> ' . htmlspecialchars(isset($post['quote_type']) ? $post['quote_type'] : '') . '</p>';


$html .= '<p><b>Additional Info:</b><br>' . nl2br(htmlspecialchars(isset($post['additional_info']) ? $post['additional_info'] : '')) . '</p>';

$html .= '</body></html>';


        return $html;
    }

    protected function validate() {
        // Name
        if (empty($this->request->post['fullname']) || utf8_strlen($this->request->post['fullname']) < 2) {
            $this->error['fullname'] = 'Full name must be at least 2 characters.';
        }

        // Email
        if (empty($this->request->post['email']) || !filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
            $this->error['email'] = 'Please enter a valid email address.';
        }

        // Phone - accept 10 digits (national) or 11-12 digits (international with/without +)
        if (empty($this->request->post['phone']) || !preg_match('/^\+?[0-9]{9,12}$/', $this->request->post['phone'])) {
            $this->error['phone'] = 'Please enter a valid phone number.';
        }
        
        	// validate phone no is from AUS
	$this->load->helper('phone');	
     if (!isset($this->error['phone']) && !is_valid_au_phone($this->request->post['phone'])) {
      $this->error['phone'] = 'Please enter a valid Australian phone number';
     }
     

        // Postcode
        if (empty($this->request->post['postcode']) || !preg_match('/^[0-9]{4}$/', $this->request->post['postcode'])) {
            $this->error['postcode'] = 'Please enter a valid 4-digit postcode.';
        }

        // Contact Type
        if (empty($this->request->post['contact_type'])) {
            $this->error['contact_type'] = 'Please select how you are contacting us.';
        }

        // Quote Type
        if (empty($this->request->post['quote_type'])) {
            $this->error['quote_type'] = 'Please select a preferred quote type.';
        }
      


        return !$this->error;
    }

    public function validateAjax() {
        $json = [];
        if ($this->request->server['REQUEST_METHOD'] === 'POST') {
            if (!$this->validate()) {
                $json['error'] = $this->error;
            } else {
                $json['success'] = true;
                $this->session->data['lightdrive_ajax_validated'] = true;
            }
        } else {
            $json['error'] = 'Invalid request';
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

   
}
