<?php
class ControllerInformationWarrantyClaim extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('information/contact');
      
		$this->document->setTitle('Warranty Claim | MobilityCare Australia');
		$this->document->setDescription('Submit a warranty claim for your MobilityCare mobility equipment. We stand behind all our products with comprehensive warranty support.');
        $this->load->model('catalog/warranty_claim');
        
		if ($this->request->server['REQUEST_METHOD'] == 'POST') {

		    $skip_validation = false;
		    if (isset($this->session->data['warranty_ajax_validated']) && $this->session->data['warranty_ajax_validated'] === true) {
		        $skip_validation = true;
		        unset($this->session->data['warranty_ajax_validated']);
		    }

		    if ($skip_validation || $this->validate()) {
		    
		    if (!empty($this->request->files['upload_files']['name'][0])) {
            $uploaded_files = [];
            $upload_path = DIR_IMAGE . 'catalog/warranty_claims/';

    // Ensure the directory exists
    if (!is_dir($upload_path)) {
        mkdir($upload_path, 0755, true);
    }

    foreach ($this->request->files['upload_files']['name'] as $key => $file_name) {
        $file_tmp = $this->request->files['upload_files']['tmp_name'][$key];
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];

        if (in_array(strtolower($file_ext), $allowed_extensions) && is_uploaded_file($file_tmp)) {
            $new_file_name = uniqid('warranty_', true) . '.' . $file_ext;
            move_uploaded_file($file_tmp, $upload_path . $new_file_name);
            $uploaded_files[] = 'catalog/warranty_claims/' . $new_file_name; // Save relative path
        }
    }

       if (!empty($uploaded_files)) {
        $this->request->post['uploaded_files'] = json_encode($uploaded_files);
        }
       }

         // Save warranty claim details
         try {
             $this->model_catalog_warranty_claim->addClaim($this->request->post);
         } catch (\Throwable $dbError) {
             $this->log->write('WARRANTY CLAIM DB SAVE ERROR: ' . $dbError->getMessage());
         }
         
                try {
                $mail = new Mail($this->config->get('config_mail_engine'));
                $mail->parameter = $this->config->get('config_mail_parameter');
                $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
                $mail->smtp_username = $this->config->get('config_mail_smtp_username');
                $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
                $mail->smtp_port = $this->config->get('config_mail_smtp_port');
                $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

                $mail->setTo($this->config->get('config_email'));
                 $mail->setFrom('enquiries@mobilitycare.net.au');
    $mail->setReplyTo('enquiries@mobilitycare.net.au');
    $mail->setSender(html_entity_decode('MobilityCare', ENT_QUOTES, 'UTF-8'));
                $mail->setSubject('New Warranty claim');
                $mail->setText('New warranty claim form has been submitted. Please login to admin and navigate to "Forms > Warrant Claim" to check the full details.');
                $mail->send();
                } catch (\Throwable $e) {
                    $this->log->write('WARRANTY CLAIM ADMIN MAIL ERROR: ' . $e->getMessage());
                    try {
                        $fallback = new Mail($this->config->get('config_mail_engine'));
                        $fallback->parameter = $this->config->get('config_mail_parameter');
                        $fallback->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
                        $fallback->smtp_username = $this->config->get('config_mail_smtp_username');
                        $fallback->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
                        $fallback->smtp_port = $this->config->get('config_mail_smtp_port');
                        $fallback->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
                        $fallback->setTo($this->config->get('config_email'));
                        $fallback->setFrom('enquiries@mobilitycare.net.au');
                        $fallback->setSender('MobilityCare');
                        $fallback->setSubject('[ALERT] Warranty Claim Received - Email Delivery Issue');
                        $fallback->setText('A warranty claim was saved to the database but the notification email failed. Error: ' . $e->getMessage() . '. Please check admin panel > Forms > Warranty Claim.');
                        $fallback->send();
                    } catch (\Throwable $e2) {
                        $this->log->write('WARRANTY CLAIM FALLBACK MAIL ERROR: ' . $e2->getMessage());
                    }
                }
                
 
         $this->session->data['success'] = 'Your warranty claim has been successfully submitted.';
         $this->response->redirect($this->url->link('information/form_success/warranty_claim'));
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
		
		if (isset($this->error['product_id'])) {
			$data['error_product'] = $this->error['product_id'];
		} else {
			$data['error_product'] = '';
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

		$data['action'] = '/warranty-claim/';
        

		if (isset($this->request->post['full_name'])) {
			$data['name'] = $this->request->post['full_name'];
		} else {
			$data['name'] = $this->customer->getFirstName();
		}

		if (isset($this->request->post['email'])) {
			$data['email'] = $this->request->post['email'];
		} else {
			$data['email'] = $this->customer->getEmail();
		}

		if (isset($this->request->post['phone_number'])) {
			$data['phone'] = $this->request->post['phone_number'];
		} else {
			$data['phone'] = '';
		}

		$data['captcha'] = '';

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('information/warranty_claim', $data));
	}

	protected function validate() {
		if ((utf8_strlen($this->request->post['full_name']) < 2) || (utf8_strlen($this->request->post['full_name']) > 320)) {
			$this->error['name'] = 'Name is required';
		}

		if (!filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
			$this->error['email'] = 'Email is required';
		}

		// Phone - accept 10 digits (national) or 11-12 digits (international)
		if ((utf8_strlen($this->request->post['phone_number']) < 10) || (utf8_strlen($this->request->post['phone_number']) > 12)) {
			$this->error['phone'] = 'Please enter a valid phone number';
		}
		
			// validate phone no is from AUS
	$this->load->helper('phone');	
     if (!isset($this->error['phone']) && !is_valid_au_phone($this->request->post['phone_number'])) {
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
				$this->session->data['warranty_ajax_validated'] = true;
			}
		} else {
			$json['error'] = 'Invalid request';
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
