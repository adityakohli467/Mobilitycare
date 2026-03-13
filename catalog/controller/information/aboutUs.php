<?php
class ControllerInformationAboutUs extends Controller {
    public function index() {
        $this->load->language('information/aboutUs');

        $this->document->setTitle('About Us | MobilityCare Australia');
        $this->document->setDescription('Learn about MobilityCare, Australia\'s trusted supplier of mobility aids, wheelchairs, rollators, lifting equipment and assistive technology solutions.');

        // Load Breadcrumbs
        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('information/aboutUs')
        );

        // Set main content
        $data['heading_title'] = 'About Us';

        // Load common page components
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        // Output the view file: catalog/view/theme/YOUR_THEME/template/information/aboutUs.twig
        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/information/aboutUs.twig')) {
            $this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/information/aboutUs', $data));
        } else {
            $this->response->setOutput($this->load->view('information/aboutUs', $data));
        }
    }
}
