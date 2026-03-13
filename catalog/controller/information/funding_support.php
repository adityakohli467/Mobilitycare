<?php
class ControllerInformationFundingSupport extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('information/contact');

		$this->document->setTitle('Funding Support Eligibility Check | MobilityCare Australia');
		$this->document->setDescription('Check your eligibility for NDIS, DVA, My Aged Care and other funding support for mobility aids and assistive equipment from MobilityCare.');
        $this->load->model('catalog/demo_request');
        // all Manufacturers list
        $this->load->model('catalog/manufacturer');
        $data['manufacturers'] = $this->model_catalog_manufacturer->getManufacturers();
        
      
        
	  if ($this->request->server['REQUEST_METHOD'] == 'POST') {

            $skip_validation = false;
            if (isset($this->session->data['funding_ajax_validated']) && $this->session->data['funding_ajax_validated'] === true) {
                $skip_validation = true;
                unset($this->session->data['funding_ajax_validated']);
            }

            if ($skip_validation || $this->validate()) {

                // Save to DB
                $this->model_catalog_demo_request->addFundingSupport($this->request->post);

                // Prepare Email
                $mailMessageHtml = $this->mailHtml($this->request->post);

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
                $mail->setSender(html_entity_decode('MobilityCare', ENT_QUOTES, 'UTF-8'));
                
                $mail->setSubject('Funding Support');
                $mail->setHtml($mailMessageHtml);
                $mail->send();
                
                // auto reply to customer
                
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

                 // redirect
                 $this->session->data['success'] = 'Your enquiry has been successfully submitted.';
                 $this->response->redirect($this->url->link('information/contact_success'));

            } 
        }

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

	
		if (isset($this->error['fullname'])) {
			$data['error_name'] = $this->error['fullname'];
		} else {
			$data['error_name'] = '';
		}
		
		if (isset($this->error['manufacturer_id'])) {
			$data['error_manufacturer_id'] = $this->error['manufacturer_id'];
		} else {
			$data['error_manufacturer_id'] = '';
		}


		if (isset($this->error['email'])) {
			$data['error_email'] = $this->error['email'];
		} else {
			$data['error_email'] = '';
		}
		
		

		if (isset($this->error['phone'])) {
			$data['error_phone'] = $this->error['phone'];
		} else {
			$data['error_phone'] = '';
		}

		$data['button_submit'] = 'Submit your request';

		$data['action'] = '/funding-support/';


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

		if (isset($this->request->post['phone'])) {
			$data['phone'] = $this->request->post['phone'];
		} else {
			$data['phone'] = '';
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

		$this->response->setOutput($this->load->view('information/funding_support', $data));
	}
	
	private function mailHtml($post) {
	     $this->load->model('catalog/manufacturer');
        $manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($post['manufacturer_id']);
        $manufacturer_name = $manufacturer_info ? html_entity_decode($manufacturer_info['name'], ENT_QUOTES, 'UTF-8') : 'Unknown Manufacturer';

        $html = '<h2>New Funding Support Request Received</h2>';
        $html .= '<p>A new  request has been submitted with the following details:</p>';
        $html .= '<ul>';
        $html .= '<li><strong>Full Name:</strong> ' . htmlspecialchars($post['fullname']) . '</li>';
        $html .= '<li><strong>Email:</strong> ' . htmlspecialchars($post['email']) . '</li>';
        $html .= '<li><strong>Phone:</strong> ' . htmlspecialchars($post['phone']) . '</li>';
        $html .= '<li><strong>Postcode:</strong> ' . htmlspecialchars($post['postcode']) . '</li>';
        $html .= '<li><strong>Brand Name:</strong> ' . htmlspecialchars($manufacturer_name) . '</li>';
        $html .= '<li><strong>Message:</strong> ' . nl2br(htmlspecialchars($post['message'])) . '</li>';
        $html .= '</ul>';
        return $html;
    }

	protected function validate() {
		if ((utf8_strlen($this->request->post['fullname']) < 1) || (utf8_strlen($this->request->post['fullname']) > 112)) {
			$this->error['fullname'] = 'Name is required';
		}

		if (!filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
			$this->error['email'] = 'Email is required';
		}

		// Phone - accept 10 digits (national) or 11-12 digits (international)
		if ((utf8_strlen($this->request->post['phone']) < 10) || (utf8_strlen($this->request->post['phone']) > 12)) {
			$this->error['phone'] = 'Please enter a valid phone number';
		}
		
			// validate phone no is from AUS
	$this->load->helper('phone');	
     if (!isset($this->error['phone']) && !is_valid_au_phone($this->request->post['phone'])) {
      $this->error['phone'] = 'Please enter a valid Australian phone number';
     }
		
		if ($this->request->post['manufacturer_id'] =='') {
			$this->error['manufacturer_id'] = 'Please choose the product of interest';
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
				$this->session->data['funding_ajax_validated'] = true;
			}
		} else {
			$json['error'] = 'Invalid request';
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
