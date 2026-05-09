<?php
class ControllerInformationRaymexLiftEnquiry extends Controller {
    private $error = array();

    public function index() {
        $this->load->language('information/contact');
        $this->load->model('catalog/demo_request');

        $this->document->setTitle('RAYMEX® Lift Enquiry | MobilityCare Australia');
        $this->document->setDescription('Enquire about the RAYMEX® Lift — a portable personal lift and mobility walker with a powered elevating seat. Get pricing, availability and book a demo with MobilityCare Australia.');

        // Handle form submission
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {

            $skip_validation = false;
            if (isset($this->session->data['raymex_ajax_validated']) && $this->session->data['raymex_ajax_validated'] === true) {
                $skip_validation = true;
                unset($this->session->data['raymex_ajax_validated']);
            }

            if ($skip_validation || $this->validate()) {

                $fields = ['contact_type', 'additional_info'];
                foreach ($fields as $field) {
                    if (!isset($this->request->post[$field])) {
                        $this->request->post[$field] = '';
                    }
                }

                $mailMessageHtml = $this->mailHtml($this->request->post);

                // Save in database FIRST so data is never lost even if mail fails
                try {
                    $this->model_catalog_demo_request->addRaymexLiftEnquiry($this->request->post);
                } catch (\Throwable $dbError) {
                    $this->log->write('RAYMEX DB SAVE ERROR: ' . $dbError->getMessage());
                }

                // Queue emails for async sending
                $this->load->helper('mail_queue');
                $replyTo = isset($this->request->post['email']) ? $this->request->post['email'] : 'enquiries@mobilitycare.net.au';

                mail_queue_add($this->db, [
                    'to'       => $this->config->get('config_email'),
                    'from'     => 'enquiries@mobilitycare.net.au',
                    'sender'   => 'MobilityCare',
                    'reply_to' => $replyTo,
                    'subject'  => 'New RAYMEX Lift Enquiry Received',
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

                $this->session->data['success'] = 'Your enquiry has been successfully submitted.';
                $this->response->redirect($this->url->link('information/form_success/raymex'));
            }
        }

        $data['action'] = '/raymex-lift-enquiry';
        $data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';
        $data['error_fullname'] = isset($this->error['fullname']) ? $this->error['fullname'] : '';
        $data['error_email'] = isset($this->error['email']) ? $this->error['email'] : '';
        $data['error_phone'] = isset($this->error['phone']) ? $this->error['phone'] : '';
        $data['error_postcode'] = isset($this->error['postcode']) ? $this->error['postcode'] : '';
        $data['error_contact_type'] = isset($this->error['contact_type']) ? $this->error['contact_type'] : '';

        $data['captcha'] = '';

        $data['header'] = $this->load->controller('common/header');
        $data['footer'] = $this->load->controller('common/footer');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');

        $this->response->setOutput($this->load->view('information/raymexLiftEnquiry', $data));
    }

    private function mailHtml($post) {
        $html = '<html><body>';
        $html .= '<h3>New RAYMEX&reg; Lift Enquiry Received</h3>';
        $html .= '<p><b>Full Name:</b> ' . htmlspecialchars(isset($post['fullname']) ? $post['fullname'] : '') . '</p>';
        $html .= '<p><b>Email:</b> ' . htmlspecialchars(isset($post['email']) ? $post['email'] : '') . '</p>';
        $html .= '<p><b>Phone:</b> ' . htmlspecialchars(isset($post['phone']) ? $post['phone'] : '') . '</p>';
        $html .= '<p><b>Postcode:</b> ' . htmlspecialchars(isset($post['postcode']) ? $post['postcode'] : '') . '</p>';
        $html .= '<p><b>Contact Type:</b> ' . htmlspecialchars(isset($post['contact_type']) ? $post['contact_type'] : '') . '</p>';
        $html .= '<p><b>Additional Info:</b><br>' . nl2br(htmlspecialchars(isset($post['additional_info']) ? $post['additional_info'] : '')) . '</p>';
        $html .= '</body></html>';

        return $html;
    }

    protected function validate() {
        if (empty($this->request->post['fullname']) || utf8_strlen($this->request->post['fullname']) < 2) {
            $this->error['fullname'] = 'Full name must be at least 2 characters.';
        }

        if (empty($this->request->post['email']) || !filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
            $this->error['email'] = 'Please enter a valid email address.';
        }

        if (empty($this->request->post['phone']) || !preg_match('/^\+?[0-9]{9,12}$/', $this->request->post['phone'])) {
            $this->error['phone'] = 'Please enter a valid phone number.';
        }

        $this->load->helper('phone');
        if (!isset($this->error['phone']) && !is_valid_au_phone($this->request->post['phone'])) {
            $this->error['phone'] = 'Please enter a valid Australian phone number';
        }

        if (empty($this->request->post['postcode']) || !preg_match('/^[0-9]{4}$/', $this->request->post['postcode'])) {
            $this->error['postcode'] = 'Please enter a valid 4-digit postcode.';
        }

        if (empty($this->request->post['contact_type'])) {
            $this->error['contact_type'] = 'Please select how you are contacting us.';
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
                $this->session->data['raymex_ajax_validated'] = true;
            }
        } else {
            $json['error'] = 'Invalid request';
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
