<?php
class ControllerInformationPaymentOptions extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('information/contact');
		$this->document->setTitle('Payment Options | ' . $this->config->get('config_name'));
		$data['title'] = 'Payment Options';
        $data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('information/payment_options', $data));
	}


}