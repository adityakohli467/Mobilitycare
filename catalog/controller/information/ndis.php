<?php
class ControllerInformationNdis extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('information/contact');

		$this->document->setTitle('NDIS Funding for Mobility Equipment | MobilityCare Australia');
		$this->document->setDescription('Learn how NDIS funding can help you access mobility aids, wheelchairs, rollators and assistive technology. MobilityCare is a registered NDIS provider across Australia.');

        $data['title'] = 'National Disability Insurance Scheme';
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
        $data['findDealer_link'] = $this->url->link('dealer/findDealer', '', true);
        $data['warranty_claim'] = $this->url->link('information/warranty_claim', '', true);
        $data['bookDemo'] = $this->url->link('information/demo_request', '', true);
        
         $data['marketing_popup'] = $this->load->controller('common/marketing_popup');
         
        	$data['action'] = '/contact-mobilitycare/';
        $data['captcha'] = '';
		
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
		
		$this->response->setOutput($this->load->view('information/ndis', $data));
	}

	
}
