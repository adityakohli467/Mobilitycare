<?php
class ControllerDealerDealer extends Controller {
    private $error = array();

    public function index() {
       
        $this->load->language('dealer/dealer');
        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('dealer/dealer');

        $this->getList();
    }

    protected function getList() {
        if (isset($this->request->get['filter_name'])) {
            $filter_name = $this->request->get['filter_name'];
        } else {
            $filter_name = '';
        }

        if (isset($this->request->get['filter_email'])) {
            $filter_email = $this->request->get['filter_email'];
        } else {
            $filter_email = '';
        }

        $url = '';

        if (isset($this->request->get['filter_name'])) {
            $url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_email'])) {
            $url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('dealer/dealer', 'user_token=' . $this->session->data['user_token'] . $url, true)
        );

        $data['add'] = $this->url->link('dealer/dealer/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
         $data['approve'] = $this->url->link('dealer/dealer/approve', 'user_token=' . $this->session->data['user_token'] . $url, true);
        $data['edit'] = $this->url->link('dealer/dealer/edit', 'user_token=' . $this->session->data['user_token'] . $url, true);
        $data['update'] = $this->url->link('dealer/dealer/update', 'user_token=' . $this->session->data['user_token'] . $url, true);
        $data['delete'] = $this->url->link('dealer/dealer/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

        $filter_data = array(
            'filter_name'  => $filter_name,
            'filter_email' => $filter_email
        );

        $data['dealers'] = array();



        $results = $this->model_dealer_dealer->getDealers($filter_data);

        foreach ($results as $result) {
            $data['dealers'][] = array(
                'dealer_id' => $result['dealer_id'],
                'is_new' => $result['is_new'],
                'is_approved' => $result['is_approved'],
                'status' => $result['status'],
                'postcode' => $result['postcode'],
                'state' => $result['state'],
                'name'      => $result['full_name'],
                'email'     => $result['email'],
                'phone'     => $result['phone'],
               'approve'      => $this->url->link('dealer/dealer/approve', 'user_token=' . $this->session->data['user_token'] . '&dealer_id=' . $result['dealer_id'] . $url, true),
                'edit'      => $this->url->link('dealer/dealer/edit', 'user_token=' . $this->session->data['user_token'] . '&dealer_id=' . $result['dealer_id'] . $url, true)
            );
        }

        $data['filter_name'] = $filter_name;
        $data['filter_email'] = $filter_email;
        $data['user_token'] = $this->session->data['user_token'];

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('dealer/dealer_list', $data));
    }
    
    public function delete() {
        $this->load->model('dealer/dealer');
        $json = [];

        if (isset($this->request->post['dealer_id'])) {
            $dealer_id = (int)$this->request->post['dealer_id'];
           
            $this->model_dealer_dealer->deleteDealer($dealer_id);
            $json['success'] = 'Dealer deleted successfully!';
        } else {
            $json['error'] = 'Missing dealer_id';
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    public function approve() {
        ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

        $this->load->model('dealer/dealer');
        $json = [];
if (isset($this->request->get['dealer_id'])) {
        $dealer_id = $this->request->get['dealer_id'];
    } else {
        $dealer_id = 0;
    }
        if (isset($dealer_id)) {
            
           
            $this->model_dealer_dealer->approveDealer($dealer_id);
            $json['success'] = 'Dealer approved successfully!';
        } else {
            $json['error'] = 'Missing dealer_id';
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    
    
    public function edit() {
   
    $this->document->setTitle('Edit Dealer');

    $this->load->model('dealer/dealer');
   
    if (isset($this->request->get['dealer_id'])) {
        $dealer_id = $this->request->get['dealer_id'];
    } else {
        $dealer_id = 0;
    }

    if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
        // echo $dealer_id;
        // echo "<pre>"; print_r($this->request->post); exit;
        $this->model_dealer_dealer->editDealer($dealer_id, $this->request->post);
        
        $this->session->data['success'] = 'Dealer updated successfully!';
        $this->response->redirect($this->url->link('dealer/dealer', 'user_token=' . $this->session->data['user_token'], true));
    }

    $data['dealer'] = $this->model_dealer_dealer->getDealer($dealer_id);
    $data['dealer_brands'] = $this->model_dealer_dealer->getDealerBrandsWithProducts($dealer_id);
     $this->load->model('catalog/product');
    $data['products'] = $this->model_catalog_product->getProducts();
    
    // Fetch manufacturers for preferred categories
    $this->load->model('catalog/manufacturer');
    $data['manufacturers'] = $this->model_catalog_manufacturer->getManufacturers();

    $data['update'] = $this->url->link('dealer/dealer/edit', 'user_token=' . $this->session->data['user_token'] . '&dealer_id=' . $dealer_id, true);
    $data['user_token'] = $this->session->data['user_token'];
    $data['header'] = $this->load->controller('common/header');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['footer'] = $this->load->controller('common/footer');
     $data['cancel'] = $this->url->link('dealer/dealer', 'user_token=' . $this->session->data['user_token'], true);

// echo "<pre>"; print_r($data['manufacturers']); exit;
    $this->response->setOutput($this->load->view('dealer/edit_dealer', $data));
}
    
    public function add() {
   
    $this->document->setTitle('Add Dealer');

    $this->load->model('dealer/dealer');
   
     $data['cancel'] = $this->url->link('dealer/dealer', 'user_token=' . $this->session->data['user_token'], true);

    if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
        $this->model_dealer_dealer->addDealer($this->request->post);
        
        $this->session->data['success'] = 'Dealer updated successfully!';
        $this->response->redirect($this->url->link('dealer/dealer', 'user_token=' . $this->session->data['user_token'], true));
    }

   
    $this->load->model('catalog/product');
    $data['products'] = $this->model_catalog_product->getProducts();
    
    // Fetch manufacturers for preferred categories
    $this->load->model('catalog/manufacturer');
    $data['manufacturers'] = $this->model_catalog_manufacturer->getManufacturers();

    $data['submitdealer'] = $this->url->link('dealer/dealer/add', 'user_token=' . $this->session->data['user_token'] , true);

    $data['header'] = $this->load->controller('common/header');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['footer'] = $this->load->controller('common/footer');
    $data['user_token'] = $this->session->data['user_token'];
// echo "<pre>"; print_r($data['manufacturers']); exit;
    $this->response->setOutput($this->load->view('dealer/add_dealer', $data));
}
    
  public function toggleStatus() {
        $this->load->model('dealer/dealer');
        $json = [];

        if (isset($this->request->post['dealer_id']) && isset($this->request->post['status'])) {
            $dealer_id = (int)$this->request->post['dealer_id'];
            $status = (int)$this->request->post['status'] ? 1 : 0;

            $this->model_dealer_dealer->toggleDealerStatus($dealer_id, $status);
            $json['success'] = true;
            $json['message'] = 'Dealer status updated successfully!';
        } else {
            $json['success'] = false;
            $json['message'] = 'Missing dealer_id or status';
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

  public function fetchProducts() {
    $this->load->model('catalog/manufacturer');

    $manufacturer_id = $this->request->post['manufacturer_id'];
   
    $products = [];
    $results = $this->model_catalog_manufacturer->getProductsByManufacturer($manufacturer_id);
    
    foreach ($results as $product) {
        $products[] = [
            'product_id' => $product['product_id'],
            'name'       => $product['name']
        ];
    }

    echo json_encode($products);
}


}
