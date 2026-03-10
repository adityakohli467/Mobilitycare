<?php

class ControllerExtensionModuleSoAskQuestion extends Controller {
	public function index() {
		$data       = array();
        $this->load->language('extension/module/so_askquestion');

        if (isset($this->request->get['product_id'])) {
            $data['product_id'] = (int)$this->request->get['product_id'];
        } else {
            $data['product_id'] = 0;
        }

        if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
            $data['base'] = $this->config->get('config_ssl');
        } else {
            $data['base'] = $this->config->get('config_url');
        }
        // In the controller that loads the page with modal
$data['find_dealer_submit_url'] = $this->url->link('extension/module/so_askquestion/sendData', '', true);
        $data['status'] = $this->config->get('module_so_askquestion_status');
        $data['show_name'] = $this->config->get('module_so_askquestion_show_name');
        $data['require_name'] = $this->config->get('module_so_askquestion_require_name');
        $data['show_phone'] = $this->config->get('module_so_askquestion_show_phone');
        $data['require_phone'] = $this->config->get('module_so_askquestion_require_phone');
        $data['show_question'] = $this->config->get('module_so_askquestion_show_question');
        $data['require_question'] = $this->config->get('module_so_askquestion_require_question');

        $this->response->setOutput($this->load->view('extension/module/so_askquestion', $data));
	}

    public function sendData() {
    $this->load->language('extension/module/so_askquestion');

    $isAjax = !empty($this->request->post['isAjax']) || (!empty($this->request->server['HTTP_X_REQUESTED_WITH']) && strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

    $errors = [];

    // Validation
    if ($this->config->get('module_so_askquestion_require_name') && $this->config->get('module_so_askquestion_show_name')) {
        if (empty(trim($this->request->post['name'] ?? ''))) {
            $errors[] = $this->language->get('error_name');
        }
    }

    if (empty(trim($this->request->post['email'] ?? '')) || !filter_var(trim($this->request->post['email']), FILTER_VALIDATE_EMAIL)) {
        $errors[] = $this->language->get('error_email');
    }

    if ($this->config->get('module_so_askquestion_require_phone') && $this->config->get('module_so_askquestion_show_phone')) {
        if (empty(trim($this->request->post['phone'] ?? ''))) {
            $errors[] = $this->language->get('error_number');
        }
    }

    if (empty(trim($this->request->post['postcode'] ?? ''))) {
        $errors[] = 'Please enter postcode';
    }

    $this->load->helper('phone');
    if (!is_valid_au_phone($this->request->post['phone'] ?? '')) {
        $errors[] = 'Please enter a valid Australian phone number';
    }

    if ($this->config->get('module_so_askquestion_require_question') && $this->config->get('module_so_askquestion_show_question')) {
        if (empty(trim($this->request->post['message'] ?? ''))) {
            $errors[] = $this->language->get('error_message');
        }
    }

    if (!empty($errors)) {
        if ($isAjax) {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'status' => 0,
                'errors' => array_map(function($e) { return ['error' => $e]; }, $errors)
            ]));
            return;
        }

        $this->session->data['askquestion_errors'] = $errors;
        $this->session->data['askquestion_post']   = $this->request->post;

        $redirect = $this->url->link('product/product', 'product_id=' . (int)($this->request->post['product_id'] ?? 0), true);
        $this->response->redirect($redirect);
        return;
    }

    // No errors → process
    $this->load->model('extension/module/so_askquestion');

    $base = (isset($this->request->server['HTTPS']) && ($this->request->server['HTTPS'] !== 'off' || $this->request->server['HTTPS'] == '1'))
        ? $this->config->get('config_ssl')
        : $this->config->get('config_url');

    $data = [
        'product_id'    => (int)($this->request->post['product_id'] ?? 0),
        'product_link'  => $this->url->link('product/product', 'product_id=' . (int)($this->request->post['product_id'] ?? 0)),
        'shop_url'      => $base,
        'name'          => trim($this->request->post['name'] ?? ''),
        'postcode'      => trim($this->request->post['postcode'] ?? ''),
        'state'         => trim($this->request->post['state'] ?? ''),
        'email'         => trim($this->request->post['email'] ?? ''),
        'phone'         => trim($this->request->post['phone'] ?? ''),
        'message'       => trim($this->request->post['message'] ?? '')
    ];

    $this->model_extension_module_so_askquestion->sendData($data);

    if ($isAjax) {
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode([
            'status'  => 1,
            'success' => 'Your enquiry has been sent successfully!'
        ]));
        return;
    }

    $this->response->redirect($this->url->link('information/form_success/find_dealer', '', true));
}
}