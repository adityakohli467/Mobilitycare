<?php
class ControllerInformationProductEnq extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('information/contact');
		$this->load->model('catalog/demo_request');
		 $this->load->model('catalog/manufacturer');
        $data['manufacturers'] = $this->model_catalog_manufacturer->getManufacturers();

		$this->document->setTitle('Product Enquiry | MobilityCare Australia');
		$this->document->setDescription('Have a question about our mobility equipment? Submit a product enquiry and our specialist team at MobilityCare will get back to you promptly.');
		if (isset($this->request->get['route'])) {
			$this->document->addLink($this->config->get('config_url'), 'canonical');
		}

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {

            $skip_validation = false;
            if (isset($this->session->data['productenq_ajax_validated']) && $this->session->data['productenq_ajax_validated'] === true) {
                $skip_validation = true;
                unset($this->session->data['productenq_ajax_validated']);
            }

            if ($skip_validation || $this->validate()) {

                // Save to DB
                try {
                    $this->model_catalog_demo_request->addProductEnquiry($this->request->post);
                } catch (\Throwable $dbError) {
                    $this->log->write('PRODUCT ENQUIRY DB SAVE ERROR: ' . $dbError->getMessage());
                }

                // Prepare Email
                $mailMessageHtml = $this->mailHtml($this->request->post);

                // Queue emails for async sending (instant — no SMTP wait)
                $this->load->helper('mail_queue');
                $replyTo = isset($this->request->post['email']) ? $this->request->post['email'] : 'enquiries@mobilitycare.net.au';

                mail_queue_add($this->db, [
                    'to'       => $this->config->get('config_email'),
                    'from'     => 'enquiries@mobilitycare.net.au',
                    'sender'   => 'MobilityCare',
                    'reply_to' => $replyTo,
                    'subject'  => 'Product enquiry',
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
                 $this->response->redirect($this->url->link('information/form_success/product_enq'));

            } 
        }

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('information/contact')
		);

		if (isset($this->error['fullname'])) {
			$data['error_name'] = $this->error['fullname'];
		} else {
			$data['error_name'] = '';
		}
		
		if (isset($this->error['phone'])) {
			$data['error_phone'] = $this->error['phone'];
		} else {
			$data['error_phone'] = '';
		}

		if (isset($this->error['email'])) {
			$data['error_email'] = $this->error['email'];
		} else {
			$data['error_email'] = '';
		}

		if (isset($this->error['enquiry'])) {
			$data['error_enquiry'] = $this->error['enquiry'];
		} else {
			$data['error_enquiry'] = '';
		}

		$data['button_submit'] = $this->language->get('button_submit');

		$data['action'] = '/product_enq';

		$this->load->model('tool/image');

		if ($this->config->get('config_image')) {
			$data['image'] = $this->model_tool_image->resize($this->config->get('config_image'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_location_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_location_height'));
		} else {
			$data['image'] = false;
		}

		$data['store'] = $this->config->get('config_name');
		$data['address'] = nl2br($this->config->get('config_address'));
		$data['geocode'] = $this->config->get('config_geocode');
		$data['geocode_hl'] = $this->config->get('config_language');
		$data['telephone'] = $this->config->get('config_telephone');
		$data['fax'] = $this->config->get('config_fax');
		$data['open'] = nl2br($this->config->get('config_open'));
		$data['comment'] = $this->config->get('config_comment');

		$data['locations'] = array();

		$this->load->model('localisation/location');

	

		if (isset($this->request->post['fullname'])) {
			$data['fullname'] = $this->request->post['fullname'];
		} else {
			$data['fullname'] = $this->customer->getFirstName();
		}

		if (isset($this->request->post['email'])) {
			$data['email'] = $this->request->post['email'];
		} else {
			$data['email'] = $this->customer->getEmail();
		}

		if (isset($this->request->post['enquiry'])) {
			$data['enquiry'] = $this->request->post['enquiry'];
		} else {
			$data['enquiry'] = '';
		}

		$data['captcha'] = '';

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('information/product_enq', $data));
	}
	
	private function mailHtml($post) {
	     $this->load->model('catalog/manufacturer');
        $manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($post['manufacturer_id']);
        $manufacturer_name = $manufacturer_info ? html_entity_decode($manufacturer_info['name'], ENT_QUOTES, 'UTF-8') : 'Unknown Manufacturer';


        $html = '<h2>New Product Enquiry from website</h2>';
        $html .= '<p>A new product enquiry has been submitted with the following details:</p>';
        $html .= '<ul>';
        $html .= '<li><strong>Full Name:</strong> ' . htmlspecialchars($post['fullname']) . '</li>';
        $html .= '<li><strong>Email:</strong> ' . htmlspecialchars($post['email']) . '</li>';
        $html .= '<li><strong>Phone:</strong> ' . htmlspecialchars($post['phone']) . '</li>';
        $html .= '<li><strong>Postcode:</strong> ' . htmlspecialchars($post['postcode']) . '</li>';
         $html .= '<li><strong>Contact type:</strong> ' . htmlspecialchars($post['contact_type']) . '</li>';
        if ($post['contact_type'] == 'Healthcare Professional') {
        $html .= '<li><strong>Healthcare Profession:</strong> ' . htmlspecialchars($post['healthcare_profession']) . '</li>';
         }
        $html .= '<li><strong>Enquiry type:</strong> ' . htmlspecialchars($post['inquiry_reason']) . '</li>';
        $html .= '<li><strong>Brand Name:</strong> ' . htmlspecialchars($manufacturer_name) . '</li>';
        $html .= '<li><strong>Message :</strong> ' . nl2br(htmlspecialchars($post['message'])) . '</li>';
        $html .= '</ul>';
        return $html;
    }

	protected function validate() {
	    
		if ((utf8_strlen($this->request->post['fullname']) < 1) || (utf8_strlen($this->request->post['fullname']) > 112)) {
			$this->error['fullname'] = $this->language->get('error_name');
		}

		if (!filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
			$this->error['email'] = $this->language->get('error_email');
		}
		
			// validate phone no is from AUS
	$this->load->helper('phone');	
     if (!is_valid_au_phone($this->request->post['phone'])) {
      $this->error['phone'] = 'Please enter a valid Australian phone number';
     }

		return !$this->error;
	}

	public function success() {
		$this->load->language('information/contact');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('information/contact')
		);

 		$data['text_message'] = $this->language->get('text_message'); 

		$data['continue'] = $this->url->link('common/home');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('common/success', $data));
	}

	public function validateAjax() {
		$json = [];
		if ($this->request->server['REQUEST_METHOD'] === 'POST') {
			if (!$this->validate()) {
				$json['error'] = $this->error;
			} else {
				$json['success'] = true;
				$this->session->data['productenq_ajax_validated'] = true;
			}
		} else {
			$json['error'] = 'Invalid request';
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
