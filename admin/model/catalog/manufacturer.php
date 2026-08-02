<?php
class ModelCatalogManufacturer extends Model {
	private function ensureDescriptionTable() {
		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "manufacturer_description` (
			`manufacturer_id` int(11) NOT NULL,
			`language_id` int(11) NOT NULL,
			`description` text NOT NULL,
			`meta_title` varchar(255) NOT NULL,
			`meta_description` varchar(255) NOT NULL,
			`meta_keyword` varchar(255) NOT NULL,
			PRIMARY KEY (`manufacturer_id`,`language_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
	}

	public function addManufacturer($data) {
		$this->ensureDescriptionTable();

		$this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer SET name = '" . $this->db->escape($data['name']) . "', sort_order = '" . (int)$data['sort_order'] . "'");

		$manufacturer_id = $this->db->getLastId();

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "manufacturer SET image = '" . $this->db->escape($data['image']) . "' WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
		}

		if (isset($data['manufacturer_store'])) {
			foreach ($data['manufacturer_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer_to_store SET manufacturer_id = '" . (int)$manufacturer_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		if (isset($data['manufacturer_description'])) {
			foreach ($data['manufacturer_description'] as $language_id => $value) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "manufacturer_description` SET manufacturer_id = '" . (int)$manufacturer_id . "', language_id = '" . (int)$language_id . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
			}
		}

		// SEO URL
		if (isset($data['manufacturer_seo_url'])) {
			foreach ($data['manufacturer_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'manufacturer_id=" . (int)$manufacturer_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}
		
		$this->cache->delete('manufacturer');

		return $manufacturer_id;
	}

	public function editManufacturer($manufacturer_id, $data) {
		$this->ensureDescriptionTable();

		$this->db->query("UPDATE " . DB_PREFIX . "manufacturer SET name = '" . $this->db->escape($data['name']) . "', sort_order = '" . (int)$data['sort_order'] . "' WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "manufacturer SET image = '" . $this->db->escape($data['image']) . "' WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "manufacturer_to_store WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");

		if (isset($data['manufacturer_store'])) {
			foreach ($data['manufacturer_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer_to_store SET manufacturer_id = '" . (int)$manufacturer_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "manufacturer_description` WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");

		if (isset($data['manufacturer_description'])) {
			foreach ($data['manufacturer_description'] as $language_id => $value) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "manufacturer_description` SET manufacturer_id = '" . (int)$manufacturer_id . "', language_id = '" . (int)$language_id . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE query = 'manufacturer_id=" . (int)$manufacturer_id . "'");

		if (isset($data['manufacturer_seo_url'])) {
			foreach ($data['manufacturer_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'manufacturer_id=" . (int)$manufacturer_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}

		$this->cache->delete('manufacturer');
	}

	public function deleteManufacturer($manufacturer_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "manufacturer` WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "manufacturer_to_store` WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "manufacturer_description` WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE query = 'manufacturer_id=" . (int)$manufacturer_id . "'");

		$this->cache->delete('manufacturer');
	}

	public function getManufacturer($manufacturer_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "manufacturer WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");

		return $query->row;
	}
   	public function getProductsByManufacturer($manufacturer_id) {
	
        $query = $this->db->query("
            SELECT p.product_id, pd.name 
            FROM " . DB_PREFIX . "product p left join oc_product_description pd on p.product_id = pd.product_id
            WHERE p.manufacturer_id = '" . (int)$manufacturer_id . "' AND p.status = '1'
            ORDER BY pd.name ASC
        ");

        return $query->rows;
    }
    
	public function getManufacturers($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "manufacturer";

		if (!empty($data['filter_name'])) {
			$sql .= " WHERE name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		$sort_data = array(
			'name',
			'sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY name";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

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

	public function getManufacturerStores($manufacturer_id) {
		$manufacturer_store_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "manufacturer_to_store WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");

		foreach ($query->rows as $result) {
			$manufacturer_store_data[] = $result['store_id'];
		}

		return $manufacturer_store_data;
	}
	
	public function getManufacturerSeoUrls($manufacturer_id) {
		$manufacturer_seo_url_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE query = 'manufacturer_id=" . (int)$manufacturer_id . "'");

		foreach ($query->rows as $result) {
			$manufacturer_seo_url_data[$result['store_id']][$result['language_id']] = $result['keyword'];
		}

		return $manufacturer_seo_url_data;
	}

	public function getManufacturerDescriptions($manufacturer_id) {
		$this->ensureDescriptionTable();

		$manufacturer_description_data = array();

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "manufacturer_description` WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");

		foreach ($query->rows as $result) {
			$manufacturer_description_data[$result['language_id']] = array(
				'description'       => $result['description'],
				'meta_title'        => $result['meta_title'],
				'meta_description'  => $result['meta_description'],
				'meta_keyword'      => $result['meta_keyword']
			);
		}

		return $manufacturer_description_data;
	}
	
	public function getTotalManufacturers() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "manufacturer");

		return $query->row['total'];
	}
}
