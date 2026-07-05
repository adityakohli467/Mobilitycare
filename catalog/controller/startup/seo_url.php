<?php
class ControllerStartupSeoUrl extends Controller {
	public function index() {
		// Add rewrite to url class
		if ($this->config->get('config_seo_url')) {
			$this->url->addRewrite($this);
		}

		// Decode URL
		if (isset($this->request->get['_route_'])) {
			$parts = explode('/', $this->request->get['_route_']);
			
			$had_shop_prefix = false;

			if (isset($parts[0]) && $parts[0] == 'buy') {
    array_shift($parts); // remove 'buy' prefix for products
}
if (isset($parts[0]) && $parts[0] == 'shop') {
    array_shift($parts); // remove 'shop' prefix for categories
    $had_shop_prefix = true;
}
if (isset($parts[0]) && $parts[0] == 'brands') {
    array_shift($parts); // remove 'brands' prefix for manufacturers
}


			// remove any empty arrays from trailing
			if (utf8_strlen(end($parts)) == 0) {
				array_pop($parts);
			}

			$category_count = 0;
			$last_category_keyword = '';

			foreach ($parts as $part) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE keyword = '" . $this->db->escape($part) . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "'");

				if ($query->num_rows) {
					$url = explode('=', $query->row['query']);

					if ($url[0] == 'product_id') {
						$this->request->get['product_id'] = $url[1];
					}

					if ($url[0] == 'category_id') {
						$category_count++;
						$last_category_keyword = $part;

						if (!isset($this->request->get['path'])) {
							$this->request->get['path'] = $url[1];
						} else {
							$this->request->get['path'] .= '_' . $url[1];
						}
					}

					if ($url[0] == 'manufacturer_id') {
						$this->request->get['manufacturer_id'] = $url[1];
					}

					if ($url[0] == 'information_id') {
						$this->request->get['information_id'] = $url[1];
					}

					if ($query->row['query'] && $url[0] != 'information_id' && $url[0] != 'manufacturer_id' && $url[0] != 'category_id' && $url[0] != 'product_id') {
						$this->request->get['route'] = $query->row['query'];
					}
				} else {
					$this->request->get['route'] = 'error/not_found';

					break;
				}
			}

			// (Nested category URLs like /shop/mobility-aids/walking-aids/ are kept as-is;
			//  canonical full-path URLs are produced by the rewrite() method below.)

			if (!isset($this->request->get['route'])) {
				if (isset($this->request->get['product_id'])) {
					$this->request->get['route'] = 'product/product';
				} elseif (isset($this->request->get['path'])) {
					$this->request->get['route'] = 'product/category';
				} elseif (isset($this->request->get['manufacturer_id'])) {
					$this->request->get['route'] = 'product/manufacturer/info';
				} elseif (isset($this->request->get['information_id'])) {
					$this->request->get['route'] = 'information/information';
				}
			}
		}
	}

	public function rewrite($link) {
		$url_info = parse_url(str_replace('&amp;', '&', $link));

		$url = '';

		$data = array();

		parse_str($url_info['query'], $data);

		// Handle common/home -> root URL
		if (isset($data['route']) && $data['route'] == 'common/home') {
			unset($data['route']);

			$query = '';

			if ($data) {
				foreach ($data as $key => $value) {
					$query .= '&' . rawurlencode((string)$key) . '=' . rawurlencode((is_array($value) ? http_build_query($value) : (string)$value));
				}

				if ($query) {
					$query = '?' . str_replace('&', '&amp;', trim($query, '&'));
				}
			}

			return $url_info['scheme'] . '://' . $url_info['host'] . (isset($url_info['port']) ? ':' . $url_info['port'] : '') . '/' . $query;
		}

		foreach ($data as $key => $value) {
			if (isset($data['route'])) {
				if (($data['route'] == 'product/product' && $key == 'product_id') || (($data['route'] == 'product/manufacturer/info' || $data['route'] == 'product/product') && $key == 'manufacturer_id') || ($data['route'] == 'information/information' && $key == 'information_id')) {
    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE `query` = '" . $this->db->escape($key . '=' . (int)$value) . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");

    if ($query->num_rows && $query->row['keyword']) {
        if ($data['route'] == 'product/product') {
            $url .= '/buy/' . $query->row['keyword'] . '/';
        } elseif ($data['route'] == 'product/manufacturer/info') {
            $url .= '/brands/' . $query->row['keyword'] . '/';
        } elseif ($data['route'] == 'information/information') {
            $url .= '/' . $query->row['keyword'] . '/';
        }

        unset($data[$key]);
    }
} elseif ($key == 'path') {
    $categories = explode('_', $value);
    $leaf = (int)end($categories);

    // Build the FULL category path by walking up the parent chain (parent_id).
    // Produces e.g. /shop/mobility-aids/wheelchairs/manual-wheelchairs/
    // Specialty Solutions stays separate from Mobility Aids (root-level, no /shop/ prefix).
    $chain = array();
    $current = $leaf;
    $guard = 0;

    while ($current > 0 && $guard < 10) {
        $kq = $this->db->query("SELECT keyword FROM " . DB_PREFIX . "seo_url WHERE `query` = 'category_id=" . (int)$current . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");

        if ($kq->num_rows && $kq->row['keyword']) {
            array_unshift($chain, $kq->row['keyword']);
        }

        $cq = $this->db->query("SELECT parent_id FROM " . DB_PREFIX . "category WHERE category_id = '" . (int)$current . "'");
        $current = $cq->num_rows ? (int)$cq->row['parent_id'] : 0;
        $guard++;
    }

    if ($chain) {
        $full_path = implode('/', $chain);

        if ($chain[0] === 'specialty-solutions') {
            $url .= '/' . $full_path . '/';
        } else {
            $url .= '/shop/' . $full_path . '/';
        }
    } else {
        $url = '';
    }

    unset($data[$key]);
}

			}
		}

		if ($url) {
			unset($data['route']);

			$query = '';

			if ($data) {
				foreach ($data as $key => $value) {
					$query .= '&' . rawurlencode((string)$key) . '=' . rawurlencode((is_array($value) ? http_build_query($value) : (string)$value));
				}

				if ($query) {
					$query = '?' . str_replace('&', '&amp;', trim($query, '&'));
				}
			}

			return $url_info['scheme'] . '://' . $url_info['host'] . (isset($url_info['port']) ? ':' . $url_info['port'] : '') . str_replace('/index.php', '', $url_info['path']) . $url . $query;
		} else {
			return $link;
		}
	}
}
