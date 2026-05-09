<?php
class ModelCatalogFormRequest extends Model {
   public function getDemoRequests() {
    // First get all records
    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "demo_requests ORDER BY date_added DESC");
    $results = $query->rows;

    $final = [];

    foreach ($results as $row) {
        if ((int)$row['is_manufacturer_or_product'] === 0) {
            // Normal case: join with manufacturer
            $manu_query = $this->db->query("SELECT name FROM " . DB_PREFIX . "manufacturer WHERE manufacturer_id = '" . (int)$row['manufacturer_id'] . "'");
            $row['manufacturer_name'] = $manu_query->num_rows ? $manu_query->row['name'] : '';
        } else {
            // If it's a product, get manufacturer via product table
             $prod_query = $this->db->query("
    SELECT name 
    FROM " . DB_PREFIX . "product_description 
    WHERE product_id = '" . (int)$row['manufacturer_id'] . "' 
      AND language_id = '" . (int)$this->config->get('config_language_id') . "'
");
// its product name
$row['manufacturer_name'] = $prod_query->num_rows ? $prod_query->row['name'] : '';
$row['is_manufacturer'] = 0;
        }

        $final[] = $row;
    }

    return $final;
}

   public function getFindDealerFormRequests() {
    // First get all records
    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "findDealer_requests ORDER BY date_added DESC");
    $results = $query->rows;

    $final = [];

    foreach ($results as $row) {
        if ((int)$row['is_manufacturer_or_product'] === 0) {
            // Normal case: join with manufacturer
            $manu_query = $this->db->query("SELECT name FROM " . DB_PREFIX . "manufacturer WHERE manufacturer_id = '" . (int)$row['manufacturer_id'] . "'");
            $row['manufacturer_name'] = $manu_query->num_rows ? $manu_query->row['name'] : '';
        } else {
            // If it's a product, get manufacturer via product table
             $prod_query = $this->db->query("
    SELECT name 
    FROM " . DB_PREFIX . "product_description 
    WHERE product_id = '" . (int)$row['manufacturer_id'] . "' 
      AND language_id = '" . (int)$this->config->get('config_language_id') . "'
");
// its product name
$row['manufacturer_name'] = $prod_query->num_rows ? $prod_query->row['name'] : '';
$row['is_manufacturer'] = 0;
        }

        $final[] = $row;
    }

    return $final;
}


    
  public function getQuoteRequests($quoteId = '') {
    $sql = "SELECT * FROM " . DB_PREFIX . "quote_requests";
    
    if (!empty($quoteId)) {
        $sql .= " WHERE id = '" . (int)$quoteId . "'";
    }

    $sql .= " ORDER BY date_added DESC";
    $query = $this->db->query($sql);

    $results = !empty($quoteId) ? [$query->row] : $query->rows;
    $final = [];
 
    foreach ($results as $row) {
        if ((int)$row['is_manufacturer_or_product'] == 0) {
            // Direct manufacturer
            $manu_query = $this->db->query("SELECT name FROM " . DB_PREFIX . "manufacturer WHERE manufacturer_id = '" . (int)$row['manufacturer_id'] . "'");
            $row['manufacturer_name'] = $manu_query->num_rows ? $manu_query->row['name'] : '';
            $row['is_manufacturer'] = 1;
        } else {
            // It's actually a product_id, get product name
           $prod_query = $this->db->query("
    SELECT name 
    FROM " . DB_PREFIX . "product_description 
    WHERE product_id = '" . (int)$row['manufacturer_id'] . "' 
      AND language_id = '" . (int)$this->config->get('config_language_id') . "'
");
// its product name
$row['product_name'] = $prod_query->num_rows ? $prod_query->row['name'] : '';
$row['is_manufacturer'] = 0;
        }

        $final[] = $row;
    }

    return !empty($quoteId) ? $final[0] : $final;
}





public function getFundingSupportRequests() {
    $sql = "SELECT qr.*, m.name AS manufacturer_name 
            FROM " . DB_PREFIX . "funding_support qr 
            LEFT JOIN " . DB_PREFIX . "manufacturer m 
            ON qr.manufacturer_id = m.manufacturer_id";

    if (!empty($quoteId)) {
        $sql .= " WHERE qr.id = '" . (int)$quoteId . "'";
    }

    $sql .= " ORDER BY qr.date_added DESC";

    $query = $this->db->query($sql);
    return !empty($quoteId) ? $query->row : $query->rows;
}

public function getPlaceOrderRequests() {
    $sql = "SELECT qr.*, m.name AS manufacturer_name FROM " . DB_PREFIX . "place_orderForm qr  LEFT JOIN " . DB_PREFIX . "manufacturer m 
            ON qr.manufacturer_id = m.manufacturer_id ORDER BY qr.date_added DESC";

    $query = $this->db->query($sql);
    return $query->rows;
}

public function contactUsFormRequests() {
    $sql = "SELECT cf.*, m.name AS manufacturer_name FROM " . DB_PREFIX . "contact_forms cf  LEFT JOIN " . DB_PREFIX . "manufacturer m 
            ON cf.manufacturer_id = m.manufacturer_id ORDER BY cf.date_added DESC";

    $query = $this->db->query($sql);
    return $query->rows;
}


    
    public function getProductInquiry() {
        
   $sql = "SELECT pi.*, m.name AS manufacturer_name FROM " . DB_PREFIX . "product_inquiry pi LEFT JOIN " . DB_PREFIX . "manufacturer m 
     ON pi.manufacturer_id = m.manufacturer_id  ORDER BY pi.date_added DESC";
        $query = $this->db->query($sql);
        return $query->rows;
    
    
      
    }
    
     public function getLightDriveInquiry() {
        
        $sql = "SELECT pi.*  FROM " . DB_PREFIX . "lightDriveEnquiry pi  ORDER BY pi.date_added DESC";
        $query = $this->db->query($sql);
        return $query->rows;
    }

     public function getRaymexLiftInquiry() {
        $sql = "SELECT *  FROM " . DB_PREFIX . "raymex_lift_enquiry ORDER BY date_added DESC";
        $query = $this->db->query($sql);
        return $query->rows;
    }
    
     public function autochairInquiry() {
        
        $sql = "SELECT pi.*  FROM " . DB_PREFIX . "autochairEnquiry pi  ORDER BY pi.date_added DESC";
        $query = $this->db->query($sql);
        return $query->rows;
    }
    
    public function getAutochairEnqRequests($quoteId = '') {
    $sql = "SELECT * FROM " . DB_PREFIX . "autochairEnquiry";
    
    if (!empty($quoteId)) {
        $sql .= " WHERE id = '" . (int)$quoteId . "'";
    }

    $sql .= " ORDER BY date_added DESC";
    $query = $this->db->query($sql);
   

    $results = !empty($quoteId) ? [$query->row] : $query->rows;
    

    return (isset($results) && !empty($results) ? $results[0] : []);
}
    
    
    
    
    public function getWarrantyClaims($claimId='') {
        $sql = "SELECT * FROM " . DB_PREFIX . "warranty_claims ";
        
        if($claimId !=''){
          $sql .='where claim_id = '.$claimId.' ORDER BY date_added DESC'; 
        }else{
          $sql .=' ORDER BY date_added DESC';   
        }
        $query = $this->db->query($sql);
        return $query->rows;
    }
    
     public function getProductTrialRequests($data = array()) {
        $sql = "SELECT * FROM " . DB_PREFIX . "producttrial_requests";

        $sql .= " ORDER BY date_added DESC";

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $query = $this->db->query($sql);
        return $query->rows;
    }
}
