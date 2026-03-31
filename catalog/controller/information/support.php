<?php
class ControllerInformationSupport extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('information/contact');

		$this->document->setTitle('Customer Support & Service | MobilityCare Australia');
		$this->document->setDescription('Get help with your mobility equipment. Contact MobilityCare customer support for product enquiries, warranty claims, demonstrations, repairs and after-sales service.');

		$server = $this->config->get('config_ssl') ? $this->config->get('config_ssl') : $this->config->get('config_url');
		$this->document->addLink(rtrim($server, '/') . '/customer-service', 'canonical');
        
        $data['title'] = 'Customer Support';
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
        $data['findDealer_link'] = 'find-a-dealer';
        $data['warranty_claim'] = $this->url->link('information/warranty_claim', '', true);
        $data['product_enq'] = 'product_enq';
        $data['bookDemo'] = 'organise-a-product-demonstration';
        $data['payment_options'] = $this->url->link('information/payment_options', '', true);
		$this->response->setOutput($this->load->view('information/support', $data));
	}

	
}
