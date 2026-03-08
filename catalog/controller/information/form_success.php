<?php
class ControllerInformationFormSuccess extends Controller {
    public function quote() {
        $this->load->language('information/contact');

        $this->document->setTitle('Thank You - Enquiry Received');

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('information/contact')
        );

        $data['breadcrumbs'][] = array(
            'text' => 'Thank You',
            'href' => $this->url->link('information/contact/success')
        );

        $data['heading_title'] = 'Thank You!';
        $data['message'] = 'Your enquiry has been successfully submitted. We will get back to you within 24–48 hours.';

        $data['button_continue'] = $this->language->get('button_continue');
        $data['continue'] = $this->url->link('common/home');

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');
        unset($this->session->data['success']);


        $this->response->setOutput($this->load->view('information/contact_success', $data));
    }
    
    public function demo() {
        $this->load->language('information/contact');

        $this->document->setTitle('Thank You - Enquiry Received');

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('information/contact')
        );

        $data['breadcrumbs'][] = array(
            'text' => 'Thank You',
            'href' => $this->url->link('information/contact/success')
        );

        $data['heading_title'] = 'Thank You!';
        $data['message'] = 'Your enquiry has been successfully submitted. We will get back to you within 24–48 hours.';

        $data['button_continue'] = $this->language->get('button_continue');
        $data['continue'] = $this->url->link('common/home');

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');
        unset($this->session->data['success']);


        $this->response->setOutput($this->load->view('information/contact_success', $data));
    }
    
    public function find_dealer() {
        $this->load->language('information/contact');

        $this->document->setTitle('Thank You - Enquiry Received');

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('information/contact')
        );

        $data['breadcrumbs'][] = array(
            'text' => 'Thank You',
            'href' => $this->url->link('information/contact/success')
        );

        $data['heading_title'] = 'Thank You!';
        $data['message'] = 'Your enquiry has been successfully submitted. We will get back to you within 24–48 hours.';

        $data['button_continue'] = $this->language->get('button_continue');
        $data['continue'] = $this->url->link('common/home');

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');
        unset($this->session->data['success']);


        $this->response->setOutput($this->load->view('information/contact_success', $data));
    }
    
    public function autochair() {
        $this->load->language('information/contact');

        $this->document->setTitle('Thank You - Enquiry Received');

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('information/contact')
        );

        $data['breadcrumbs'][] = array(
            'text' => 'Thank You',
            'href' => $this->url->link('information/contact/success')
        );

        $data['heading_title'] = 'Thank You!';
        $data['message'] = 'Your enquiry has been successfully submitted. We will get back to you within 24–48 hours.';

        $data['button_continue'] = $this->language->get('button_continue');
        $data['continue'] = $this->url->link('common/home');

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');
        unset($this->session->data['success']);


        $this->response->setOutput($this->load->view('information/contact_success', $data));
    }
    
    public function lightdrive() {
        $this->load->language('information/contact');

        $this->document->setTitle('Thank You - Enquiry Received');

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('information/contact')
        );

        $data['breadcrumbs'][] = array(
            'text' => 'Thank You',
            'href' => $this->url->link('information/contact/success')
        );

        $data['heading_title'] = 'Thank You!';
        $data['message'] = 'Your enquiry has been successfully submitted. We will get back to you within 24–48 hours.';

        $data['button_continue'] = $this->language->get('button_continue');
        $data['continue'] = $this->url->link('common/home');

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');
        unset($this->session->data['success']);


        $this->response->setOutput($this->load->view('information/contact_success', $data));
    }
    
     public function placeOrder() {
        $this->load->language('information/contact');

        $this->document->setTitle('Thank You - Enquiry Received');

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('information/contact')
        );

        $data['breadcrumbs'][] = array(
            'text' => 'Thank You',
            'href' => $this->url->link('information/contact/success')
        );

        $data['heading_title'] = 'Thank You!';
        $data['message'] = 'Your enquiry has been successfully submitted. We will get back to you within 24–48 hours.';

        $data['button_continue'] = $this->language->get('button_continue');
        $data['continue'] = $this->url->link('common/home');

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');
        unset($this->session->data['success']);


        $this->response->setOutput($this->load->view('information/contact_success', $data));
    }
    
    public function product_enq() {
        $this->load->language('information/contact');

        $this->document->setTitle('Thank You - Enquiry Received');

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('information/contact')
        );

        $data['breadcrumbs'][] = array(
            'text' => 'Thank You',
            'href' => $this->url->link('information/contact/success')
        );

        $data['heading_title'] = 'Thank You!';
        $data['message'] = 'Your enquiry has been successfully submitted. We will get back to you within 24–48 hours.';

        $data['button_continue'] = $this->language->get('button_continue');
        $data['continue'] = $this->url->link('common/home');

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');
        unset($this->session->data['success']);


        $this->response->setOutput($this->load->view('information/contact_success', $data));
    }
    
    public function trial_request() {
        $this->load->language('information/contact');

        $this->document->setTitle('Thank You - Enquiry Received');

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('information/contact')
        );

        $data['breadcrumbs'][] = array(
            'text' => 'Thank You',
            'href' => $this->url->link('information/contact/success')
        );

        $data['heading_title'] = 'Thank You!';
        $data['message'] = 'Your enquiry has been successfully submitted. We will get back to you within 24–48 hours.';

        $data['button_continue'] = $this->language->get('button_continue');
        $data['continue'] = $this->url->link('common/home');

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');
        unset($this->session->data['success']);


        $this->response->setOutput($this->load->view('information/contact_success', $data));
    }
    
    public function warranty_claim() {
        $this->load->language('information/contact');

        $this->document->setTitle('Thank You - Warranty Claim Received');

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $data['breadcrumbs'][] = array(
            'text' => 'Warranty Claim',
            'href' => $this->url->link('information/warranty_claim')
        );

        $data['breadcrumbs'][] = array(
            'text' => 'Thank You',
            'href' => $this->url->link('information/form_success/warranty_claim')
        );

        $data['heading_title'] = 'Thank You!';
        $data['message'] = 'Your warranty claim has been successfully submitted. We will review your claim and get back to you within 24–48 hours.';

        $data['button_continue'] = $this->language->get('button_continue');
        $data['continue'] = $this->url->link('common/home');

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');
        unset($this->session->data['success']);


        $this->response->setOutput($this->load->view('information/contact_success', $data));
    }

    public function becomedealer() {
        $this->load->language('information/contact');

        $this->document->setTitle('Thank You - Dealer Registration Received');

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $data['breadcrumbs'][] = array(
            'text' => 'Become a Dealer',
            'href' => $this->url->link('dealer/dealer')
        );

        $data['breadcrumbs'][] = array(
            'text' => 'Thank You',
            'href' => $this->url->link('information/form_success/becomedealer')
        );

        $data['heading_title'] = 'Thank You!';
        $data['message'] = 'Your dealer registration request has been successfully submitted. We will review your application and get back to you within 24–48 hours.';

        $data['button_continue'] = $this->language->get('button_continue');
        $data['continue'] = $this->url->link('common/home');

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');
        unset($this->session->data['success']);


        $this->response->setOutput($this->load->view('information/contact_success', $data));
    }
}