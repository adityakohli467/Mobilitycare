<?php
class ModelDealerDealer extends Model {
    public function getDealers($data = array()) {
        $sql = "SELECT * FROM " . DB_PREFIX . "dealers WHERE 1";

        if (!empty($data['filter_name'])) {
            $sql .= " AND full_name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
        }

        if (!empty($data['filter_email'])) {
            $sql .= " AND email LIKE '%" . $this->db->escape($data['filter_email']) . "%'";
        }
        $sql .=" AND is_deleted =0 ORDER BY date_added DESC";

        $query = $this->db->query($sql);
        return $query->rows;
    }
    
    public function getDealer($dealer_id) {
    $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "dealers` WHERE dealer_id = '" . (int)$dealer_id . "'");
    
    return $query->row;
}

 public function getDealersList() {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "dealers where is_deleted =0 ORDER BY date_added DESC");
        return $query->rows;
    }

   public function deleteDealer($dealer_id) {
    // Soft delete: set status = 0 (inactive)
    $this->db->query("UPDATE " . DB_PREFIX . "dealers  SET is_deleted = 1 WHERE dealer_id = '" . (int)$dealer_id . "'");
                      
}

public function approveDealer($dealer_id) {
    // Soft delete: set status = 0 (inactive)
$this->db->query("UPDATE " . DB_PREFIX . "dealers SET is_approved = 1, is_new = 0, status = 1 WHERE dealer_id = " . (int)$dealer_id);
                       
}

    

public function addDealer($data) {
//     ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

        $this->db->query("INSERT INTO `" . DB_PREFIX . "dealers` 
            (`full_name`, `business_name`, `email`, `phone`, `business_address`, `abn`, `website`, `description`,`postcode`,`state`,`suburb`,`is_new`) 
            VALUES (
                '" . $this->db->escape($data['full_name']) . "', 
                '" . $this->db->escape($data['business_name']) . "', 
                '" . $this->db->escape($data['email']) . "', 
                '" . $this->db->escape($data['phone']) . "', 
                '" . $this->db->escape($data['business_address']) . "', 
                '" . $this->db->escape($data['abn']) . "', 
                '" . $this->db->escape($data['website']) . "', 
                '" . $this->db->escape($data['description']) . "',
                '" . $this->db->escape($data['postcode']) . "', 
                '" . $this->db->escape($data['state']) . "', 
                '" . $this->db->escape($data['suburb']) . "',
                '1'
                 
            )");
            
            $dealer_id = $this->db->getLastId();
            // echo $dealer_id;
            // echo "<pre>"; print_r($data); exit;
            
            // Insert new brand-product mappings
    if (isset($data['dealer_products']) && !empty($data['dealer_products'])) {
        foreach ($data['dealer_products'] as $entry) {
            $brand_id = (int)$entry['brand_id'];
            $show_demo = isset($entry['show_demo']) ? 1 : 0;

            if (!empty($entry['product_id'])) {
                foreach ($entry['product_id'] as $product_id) {
                    $this->db->query("INSERT INTO `" . DB_PREFIX . "dealer_to_brand` 
                        (dealer_id, brand_id, product_id, show_demo) VALUES (
                        '" . (int)$dealer_id . "',
                        '" . $brand_id . "',
                        '" . (int)$product_id . "',
                        '" . $show_demo . "'
                    )");
                }
            }
        }
    }
    }
    
public function editDealer($dealer_id, $data) {
    // Update dealer details
    $this->db->query("UPDATE `" . DB_PREFIX . "dealers` SET 
        full_name = '" . $this->db->escape($data['full_name']) . "',
        business_name = '" . $this->db->escape($data['business_name']) . "',
        email = '" . $this->db->escape($data['email']) . "',
        state = '" . $this->db->escape($data['state']) . "',
        suburb = '" . $this->db->escape($data['suburb']) . "',
        postcode = '" . $this->db->escape($data['postcode']) . "',
        phone = '" . $this->db->escape($data['phone']) . "',
        business_address = '" . $this->db->escape($data['business_address']) . "',
        abn = '" . $this->db->escape($data['abn']) . "',
        website = '" . $this->db->escape($data['website']) . "',
        description = '" . $this->db->escape($data['description']) . "',
        is_new = '0'
        WHERE dealer_id = '" . (int)$dealer_id . "'
    ");

    // Remove old brand-product mappings
    $this->db->query("DELETE FROM `" . DB_PREFIX . "dealer_to_brand` WHERE dealer_id = '" . (int)$dealer_id . "'");

    // Insert new brand-product mappings
    if (isset($data['dealer_products']) && !empty($data['dealer_products'])) {
        foreach ($data['dealer_products'] as $entry) {
            $brand_id = (int)$entry['brand_id'];
            $show_demo = isset($entry['show_demo']) ? 1 : 0;

            if (!empty($entry['product_id'])) {
                foreach ($entry['product_id'] as $product_id) {
                    $this->db->query("INSERT INTO `" . DB_PREFIX . "dealer_to_brand` 
                        (dealer_id, brand_id, product_id, show_demo) VALUES (
                        '" . (int)$dealer_id . "',
                        '" . $brand_id . "',
                        '" . (int)$product_id . "',
                        '" . $show_demo . "'
                    )");
                }
            }
        }
    }
    
      // Insert new brand-product mappings
        if (isset($data['brands']) && !empty($data['brands'])) {
            foreach ($data['brands'] as $brand_id => $brand_data) {
                if (!empty($brand_data['product_id'])) {
                    foreach ($brand_data['product_id'] as $product_id) {
                        $show_demo = isset($brand_data['show_demo']) ? 1 : 0;
                        $this->db->query("INSERT INTO `" . DB_PREFIX . "dealer_to_brand` 
                            (dealer_id, brand_id, product_id, show_demo) VALUES 
                            ('" . (int)$dealer_id . "', '" . (int)$brand_id . "', '" . (int)$product_id . "', '" . (int)$show_demo . "')
                        ");
                    }
                }
            }
        }
}

public function toggleDealerStatus($dealer_id, $status) {
    $this->db->query("UPDATE " . DB_PREFIX . "dealers SET status = '" . (int)$status . "' WHERE dealer_id = '" . (int)$dealer_id . "'");
}

public function getDealerBrandsWithProducts($dealer_id) {
        $query = $this->db->query("SELECT brand_id, product_id, show_demo 
                                   FROM `" . DB_PREFIX . "dealer_to_brand`
                                   WHERE dealer_id = '" . (int)$dealer_id . "'");

        $brand_products = [];
        foreach ($query->rows as $row) {
            $brand_products[$row['brand_id']]['brand_id'] = $row['brand_id'];
            $brand_products[$row['brand_id']]['product_id'][] = $row['product_id'];
            $brand_products[$row['brand_id']]['show_demo'] = $row['show_demo'];
        }

        return array_values($brand_products); // Returns array structured for form
    }


}
