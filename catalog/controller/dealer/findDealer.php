<?php
class ControllerDealerFindDealer extends Controller {
    private $error = array();

    public function index() {
//         ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

        // $this->load->language('dealer/dealer');
        $this->document->setTitle('Find a Dealer');
        $this->load->model('catalog/category');
        $this->load->model('dealer/dealer');
        $this->load->model('catalog/manufacturer');
        
        
        $category_id = 0;
        $categories = $this->model_catalog_category->getCategories($category_id);
        $data['categories'] = [];
        if(isset($categories) && !empty($categories)){
         foreach ($categories as $category) {
            $data['categories'][] = [
                'category_id' => $category['category_id'],
                'name'        => $category['name']
            ];
        }   
        }
        
        
        $data['action'] = $this->url->link('dealer/dealer', '', true);
       
        
        
        $data['captcha'] = '';
		
		$manufacturers = $this->model_catalog_manufacturer->getManufacturers();
		
		$data['manufacturers'] = [];

        foreach ($manufacturers as $manufacturer) {
            $data['manufacturers'][] = [
                'id'   => $manufacturer['manufacturer_id'],
                'name' => $manufacturer['name']
            ];
        }
       

        $data['header'] = $this->load->controller('common/header');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('dealer/findADealer', $data));
    }
}

?>