<?php
class ControllerCommonMarketingPopup extends Controller {
  public function index() {
       $this->load->model('catalog/manufacturer');
      $data['manufacturers'] = $this->model_catalog_manufacturer->getManufacturers();
      
      $data['captcha'] = '';
      
    return $this->load->view('common/marketing_popup', $data);
  }
}
