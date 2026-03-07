<?php
class ControllerInformationContact extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('information/contact');

        $this->document->setTitle('Contact Us');
        $this->document->addLink($this->url->link('information/contact'), 'canonical');
        $this->load->model('catalog/demo_request');
         $this->load->model('catalog/manufacturer');
        $data['manufacturers'] = $this->model_catalog_manufacturer->getManufacturers();
        
	if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
// 	    ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
             
                
               if($this->validate()){
                     $mailMessageHtml = $this->mailHtml($this->request->post);
                //   echo "<pre>"; print_r($this->request->post); exit;
                 $this->model_catalog_demo_request->addContactFormInfo($this->request->post);

                // Mail config
                $mail = new Mail($this->config->get('config_mail_engine'));
                $mail->parameter = $this->config->get('config_mail_parameter');
                $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
                $mail->smtp_username = $this->config->get('config_mail_smtp_username');
                $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
                $mail->smtp_port = $this->config->get('config_mail_smtp_port');
                $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

                $mail->setTo($this->config->get('config_email'));
              
                $mail->setFrom('enquiries@mobilitycare.net.au');
                $replyTo = (isset($this->request->post['email']) ? $this->request->post['email'] : 'enquiries@mobilitycare.net.au');
                $mail->setReplyTo($replyTo);
                $mail->setSender(html_entity_decode($this->request->post['name'], ENT_QUOTES, 'UTF-8'));
                $mail->setSubject('Enquiry from website');
                $mail->setHtml($mailMessageHtml);
                $mail->send();
                
                 //  Send automatic confirmation email to customer
                if (isset($this->request->post['email'])) {
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
        $customerMail->setSubject('Thank You for Your Enquiry - MobilityCare');
        
        // Load the email template
        $data['customer_name'] = isset($this->request->post['name']) ? htmlspecialchars($this->request->post['name']) : 'Valued Customer';
        
        $customerMessageHtml = $this->load->view('mail/enquiry_confirmation', $data);
        
        $customerMail->setHtml($customerMessageHtml);
        $customerMail->addAttachment(DIR_IMAGE . 'mobilitycare-brochure-assistive-technology-web.pdf');
        $customerMail->send();
    }
                
        $this->session->data['success'] = 'Your enquiry has been successfully submitted.';
        $this->response->redirect($this->url->link('information/contact_success'));
        
                   
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

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
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
		
		
			if (isset($this->error['phone'])) {
			$data['error_phone'] = $this->error['phone'];
		    } else {
			$data['error_phone'] = '';
	     	}


		$data['button_submit'] = $this->language->get('button_submit');

		$data['action'] = '/contact-mobilitycare/';

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

		foreach((array)$this->config->get('config_location') as $location_id) {
			$location_info = $this->model_localisation_location->getLocation($location_id);

			if ($location_info) {
				if ($location_info['image']) {
					$image = $this->model_tool_image->resize($location_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_location_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_location_height'));
				} else {
					$image = false;
				}

				$data['locations'][] = array(
					'location_id' => $location_info['location_id'],
					'name'        => $location_info['name'],
					'address'     => nl2br($location_info['address']),
					'geocode'     => $location_info['geocode'],
					'telephone'   => $location_info['telephone'],
					'fax'         => $location_info['fax'],
					'image'       => $image,
					'open'        => nl2br($location_info['open']),
					'comment'     => $location_info['comment']
				);
			}
		}

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} else {
			$data['name'] = $this->customer->getFirstName();
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

		// Captcha
		if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('contact', (array)$this->config->get('config_captcha_page'))) {
			$data['captcha'] = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha'), $this->error);
		} else {
			$data['captcha'] = '';
		}

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('information/contact', $data));
	}
	
	private function mailHtml($post) {
	    
	    $this->load->model('catalog/manufacturer');
        $manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($post['manufacturer_id']);
        $manufacturer_name = $manufacturer_info ? html_entity_decode($manufacturer_info['name'], ENT_QUOTES, 'UTF-8') : 'Unknown Manufacturer';

	  
        $html = '<h2>New Enquiry from website</h2>';
        $html .= '<p>A new enquiry has been submitted with the following details:</p>';
        $html .= '<ul>';
        $html .= '<li><strong>Full Name:</strong> ' . htmlspecialchars($post['name']) . '</li>';
        $html .= '<li><strong>Email:</strong> ' . htmlspecialchars($post['email']) . '</li>';
        $html .= '<li><strong>Phone:</strong> ' . htmlspecialchars($post['phone']) . '</li>';
        $html .= '<li><strong>Postcode:</strong> ' . htmlspecialchars($post['postcode']) . '</li>';
        $html .= '<li><strong>Contact type:</strong> ' . htmlspecialchars($post['contact_type']) . '</li>';
        if ($post['contact_type'] == 'Healthcare Professional') {
        $html .= '<li><strong>Healthcare Profession:</strong> ' . htmlspecialchars($post['healthcare_profession']) . '</li>';
         }
         $html .= '<li><strong>Brand Name:</strong> ' . htmlspecialchars($manufacturer_name) . '</li>';
        $html .= '<li><strong>Reason of Enquiry :</strong> ' . htmlspecialchars($post['inquiry_reason']) . '</li>';
        $html .= '<li><strong>Enquiry:</strong> ' . nl2br(htmlspecialchars($post['enquiry'])) . '</li>';
        $html .= '</ul>';
        return $html;
    }

	protected function validate() {
		if ((utf8_strlen($this->request->post['name']) < 1) || (utf8_strlen($this->request->post['name']) > 112)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		if (!filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
			$this->error['email'] = $this->language->get('error_email');
		}

		if ((utf8_strlen($this->request->post['enquiry']) < 1) || (utf8_strlen($this->request->post['enquiry']) > 5000)) {
			$this->error['enquiry'] = $this->language->get('error_enquiry');
		}
		
		// validate phone no is from AUS
	$this->load->helper('phone');	
     if (!is_valid_au_phone($this->request->post['phone'])) {
      $this->error['phone'] = 'Please enter a valid Australian phone number';
     }
		

		// Captcha
		if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('contact', (array)$this->config->get('config_captcha_page'))) {
			$captcha = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha') . '/validate');

			if ($captcha) {
				$this->error['captcha'] = $captcha;
			}
		}

		return !$this->error;
	}
	
	private function isMyDebugIp() {
    $allowedIps = ['122.177.241.44'];
        
    

    $ip = $this->request->server['HTTP_CF_CONNECTING_IP']
        ?? $this->request->server['HTTP_X_FORWARDED_FOR']
        ?? $this->request->server['REMOTE_ADDR'];

    // If multiple forwarded IPs, take the first one
    if (strpos($ip, ',') !== false) {
        $ip = trim(explode(',', $ip)[0]);
    }

    return in_array($ip, $allowedIps);
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
}
