<?php
class ModelDealerDealer extends Model {
    public function addDealer($data) {
    // Convert preferred_categories IDs to names for display purposes
    $preferred_category_names = '';
    if (isset($data['preferred_categories']) && !empty($data['preferred_categories'])) {
        $category_names = [];
        foreach ($data['preferred_categories'] as $manufacturer_id) {
            $query = $this->db->query("SELECT name FROM " . DB_PREFIX . "manufacturer WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
            if ($query->num_rows) {
                $category_names[] = $query->row['name'];
            }
        }
        $preferred_category_names = implode(', ', $category_names);
    }
    
    // Automatically geocode the address so the dealer appears on the frontend map/search
    $coords = $this->geocodeDealerAddress(
        isset($data['business_address']) ? $data['business_address'] : '',
        isset($data['suburb']) ? $data['suburb'] : '',
        isset($data['state']) ? $data['state'] : '',
        isset($data['postcode']) ? $data['postcode'] : ''
    );
    $latitude  = $coords ? "'" . (float)$coords['lat'] . "'" : "NULL";
    $longitude = $coords ? "'" . (float)$coords['lng'] . "'" : "NULL";

    $this->db->query("INSERT INTO `" . DB_PREFIX . "dealers` 
        (`status`,`is_approved`,`is_new`,`full_name`,`postcode`, `business_name`, `email`, `phone`, `business_address`, `abn`, `website`, `description`, `heard_about`, `preferred_categories`, `latitude`, `longitude`) 
        VALUES (
            '1', 
             '0', 
              '1',
            '" . $this->db->escape($data['full_name']) . "', 
            '" . $this->db->escape($data['postcode']) . "', 
            '" . $this->db->escape($data['business_name']) . "', 
            '" . $this->db->escape($data['email']) . "', 
            '" . $this->db->escape($data['phone']) . "', 
            '" . $this->db->escape($data['business_address']) . "', 
            '" . $this->db->escape($data['abn']) . "', 
            '" . $this->db->escape($data['website']) . "', 
            '" . $this->db->escape($data['description']) . "', 
            '" . $this->db->escape($data['heard_about']) . "', 
            '" . $this->db->escape($preferred_category_names) . "',
            " . $latitude . ",
            " . $longitude . "
        )");
        
    $dealerId = $this->db->getLastId();

    if (isset($data['preferred_categories']) && !empty($data['preferred_categories'])) {
        foreach ($data['preferred_categories'] as $prefCat) {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "dealer_to_brand` (`dealer_id`,`brand_id`) 
                VALUES ('" . (int)$dealerId . "', '" . (int)$prefCat . "')");
        }
    }

    return $dealerId;     
}

    /**
     * Geocode a dealer address into latitude/longitude using the Google Maps Geocoding API.
     * Returns array('lat' => float, 'lng' => float) on success, or null on failure.
     * Never throws so that saving a dealer is not blocked if geocoding fails.
     */
    private function geocodeDealerAddress($business_address, $suburb, $state, $postcode) {
        $parts = array_filter(array_map('trim', array(
            (string)$business_address,
            (string)$suburb,
            (string)$state,
            (string)$postcode,
            'Australia'
        )));

        if (empty($parts) || !function_exists('curl_init')) {
            return null;
        }

        $apiKey = 'AIzaSyCdIW7xk3UQDxMkhznl2gEyabtnGXHN2ww';
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode(implode(', ', $parts)) . '&key=' . $apiKey;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($response === false || $curl_error) {
            error_log('Dealer geocode cURL error: ' . $curl_error);
            return null;
        }

        $result = json_decode($response, true);

        if (isset($result['status']) && $result['status'] === 'OK' && !empty($result['results'][0]['geometry']['location'])) {
            return array(
                'lat' => (float)$result['results'][0]['geometry']['location']['lat'],
                'lng' => (float)$result['results'][0]['geometry']['location']['lng']
            );
        }

        error_log('Dealer geocode failed: ' . (isset($result['status']) ? $result['status'] : 'UNKNOWN') . (isset($result['error_message']) ? ' - ' . $result['error_message'] : ''));
        return null;
    }

    
  public function getDealersByManufacturer($manufacturer_id, $selectedState = 'all', $postcodes = []) {
        try {
            // Validate manufacturer_id
            if (!is_numeric($manufacturer_id) || (int)$manufacturer_id <= 0) {
                return []; // Return empty array to indicate invalid input
            }

            // Validate selectedState
            $selectedState = trim($selectedState);
            if (empty($selectedState)) {
                $selectedState = 'all'; // Default to 'all' if empty
            }

            // Validate postcodes
            if (!is_array($postcodes)) {
                $postcodes = []; // Ensure postcodes is an array
            }
            // Filter out empty or invalid postcodes
            $postcodes = array_filter(array_map('trim', $postcodes), function($value) {
                return !empty($value);
            });

            // Build SQL query
            $sql = "
                SELECT DISTINCT d.dealer_id, d.full_name, d.phone, d.email, d.business_address, d.suburb, d.state, d.postcode
                FROM " . DB_PREFIX . "dealers d
                INNER JOIN " . DB_PREFIX . "dealer_to_brand dtb ON d.dealer_id = dtb.dealer_id
                WHERE dtb.brand_id = '" . (int)$manufacturer_id . "'
                AND d.is_deleted = 0
                AND d.status = 1
                AND d.is_approved = 1";

            // Add state condition if not 'all'
            if (strtolower($selectedState) !== 'all') {
                $sql .= " AND d.state = '" . $this->db->escape($selectedState) . "'";
            }

            // Add postcode condition if postcodes array is not empty
            if (!empty($postcodes)) {
                $postcodeList = implode("','", array_map([$this->db, 'escape'], $postcodes));
                $sql .= " AND d.postcode IN ('" . $postcodeList . "')";
            }

            // Execute query
            $query = $this->db->query($sql);

            // Check if query was successful
            if ($query === false || !isset($query->rows)) {
                throw new Exception('Database query failed');
            }

            return $query->rows;

        } catch (Exception $e) {
            // Log error for debugging (optional, depending on your setup)
            log_message('error', 'getDealersByManufacturer failed: ' . $e->getMessage());
            return []; // Return empty array to indicate failure
        }
    }

    
   
    public function getProductsByManufacturer($manufacturer_id) {
    $sql = "SELECT p.product_id, pd.name 
            FROM " . DB_PREFIX . "product p 
            LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) 
            WHERE p.manufacturer_id = '" . (int)$manufacturer_id . "' 
            AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' 
            AND p.status = '1' 
            ORDER BY pd.name ASC";

    $query = $this->db->query($sql);

    return $query->rows;
}

  public function getNearbyDealers($postcodes, $manufacturer_id = '') {
      
    $postcodeList = implode("','", array_map([$this->db, 'escape'], $postcodes));
    $sql = "SELECT DISTINCT full_name, email, phone, business_address,state,suburb, postcode FROM " . DB_PREFIX . "dealers d ";
            
    if (!empty($manufacturer_id)) {
        $sql .= " left join " . DB_PREFIX . "dealer_to_brand db on d.dealer_id = db.dealer_id where d.postcode IN ('" . $postcodeList . "') and db.brand_id = ".$manufacturer_id . " AND d.is_deleted = 0 AND d.status = 1 AND d.is_approved = 1";   
    }else{
        $sql .= " WHERE d.postcode IN ('" . $postcodeList . "') AND d.is_deleted = 0 AND d.status = 1 AND d.is_approved = 1";
    }
//   echo $sql; exit;
    $query = $this->db->query($sql);
    return $query->rows;
}

public function getDealersWithinRadius($lat, $lng, $radius = 200, $manufacturerId = null, $productId = null, $selectedState = 'All') {
    try {
        $lat = (float)$lat;
        $lng = (float)$lng;
        $radius = (float)$radius;

        // =============== QUERY 1: All dealers within radius (your original working query) ===============
        $sql = "
            SELECT DISTINCT 
                d.sort_order,
                d.dealer_id, d.full_name, d.phone, d.email, 
                d.business_address, d.suburb, d.state, d.postcode, 
                d.latitude, d.longitude,
                (6371 * ACOS(
                    COS(RADIANS(" . $lat . ")) * COS(RADIANS(d.latitude)) 
                    * COS(RADIANS(d.longitude) - RADIANS(" . $lng . ")) 
                    + SIN(RADIANS(" . $lat . ")) * SIN(RADIANS(d.latitude))
                )) AS distance
            FROM " . DB_PREFIX . "dealers d";

        $joins = [];
        $conditions = [
            "d.latitude IS NOT NULL",
            "d.longitude IS NOT NULL",
            "d.latitude != ''",
            "d.longitude != ''",
            "d.status = 1"
        ];

        if ($manufacturerId !== null && is_numeric($manufacturerId) && (int)$manufacturerId > 0) {
            $joins[] = "INNER JOIN " . DB_PREFIX . "dealer_to_brand dtb ON d.dealer_id = dtb.dealer_id";
            $conditions[] = "dtb.brand_id = " . (int)$manufacturerId;
        }

        if ($productId !== null && is_numeric($productId) && (int)$productId > 0) {
            $conditions[] = "dtb.product_id = " . (int)$productId;
        }

        if (strtolower($selectedState) !== 'all' && !empty(trim($selectedState))) {
            $conditions[] = "d.state = '" . $this->db->escape(trim($selectedState)) . "'";
        }
        
        $conditions[] = "d.is_approved = '1'";
        $conditions[] = "d.status = '1'";
        $conditions[] = "d.is_deleted = 0";

        if (!empty($joins)) {
            $sql .= " " . implode(" ", array_unique($joins));
        }
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " HAVING distance <= " . $radius;
        $sql .= " ORDER BY d.sort_order ASC, distance ASC";

        $query = $this->db->query($sql);
        $dealers = $query->rows ?? [];

        // =============== QUERY 2: Always fetch Dealer ID 567 ===============
        $sql567 = "
            SELECT 
                d.sort_order,
                d.dealer_id, d.full_name, d.phone, d.email, 
                d.business_address, d.suburb, d.state, d.postcode, 
                d.latitude, d.longitude,
                (6371 * ACOS(
                    COS(RADIANS(" . $lat . ")) * COS(RADIANS(d.latitude)) 
                    * COS(RADIANS(d.longitude) - RADIANS(" . $lng . ")) 
                    + SIN(RADIANS(" . $lat . ")) * SIN(RADIANS(d.latitude))
                )) AS distance
            FROM " . DB_PREFIX . "dealers d
            WHERE d.dealer_id = 567 AND d.status = 1 AND d.is_deleted = 0
        ";

        $query567 = $this->db->query($sql567);
        $dealer567 = $query567->row ?? null;  // Only one row

        // =============== MERGE: Add 567 if not already in the list ===============
        if ($dealer567) {
            $alreadyExists = false;
            foreach ($dealers as $dealer) {
                if ($dealer['dealer_id'] == 567) {
                    $alreadyExists = true;
                    break;
                }
            }

            if (!$alreadyExists) {
                $dealers[] = $dealer567;  // Add at the end first
            }
        }

        // =============== FINAL SORT: sort_order first, then distance ===============
        usort($dealers, function($a, $b) {
            if ($a['sort_order'] == $b['sort_order']) {
                return ($a['distance'] < $b['distance']) ? -1 : 1;
            }
            return ($a['sort_order'] < $b['sort_order']) ? -1 : 1;
        });

        return $dealers;

    } catch (Exception $e) {
        error_log('getDealersWithinRadius failed: ' . $e->getMessage());
        return [];
    }
}


}
?>
