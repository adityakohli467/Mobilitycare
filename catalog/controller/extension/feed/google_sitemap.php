<?php
class ControllerExtensionFeedGoogleSitemap extends Controller {
	public function index() {
		if ($this->config->get('feed_google_sitemap_status')) {
			$output  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
			$output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

			// Homepage
			$output .= '<url>' . "\n";
			$output .= '  <loc>' . $this->escapeUrl($this->config->get('config_ssl') ? $this->config->get('config_ssl') : $this->config->get('config_url')) . '</loc>' . "\n";
			$output .= '  <lastmod>' . date('Y-m-d\TH:i:sP') . '</lastmod>' . "\n";
			$output .= '  <changefreq>daily</changefreq>' . "\n";
			$output .= '  <priority>1.0</priority>' . "\n";
			$output .= '</url>' . "\n";

			$this->load->model('catalog/product');
			$this->load->model('tool/image');

			$products = $this->model_catalog_product->getProducts();

			foreach ($products as $product) {
				$output .= '<url>' . "\n";
				$output .= '  <loc>' . $this->escapeUrl($this->url->link('product/product', 'product_id=' . $product['product_id'])) . '</loc>' . "\n";
				$output .= '  <lastmod>' . date('Y-m-d\TH:i:sP', strtotime($product['date_modified'])) . '</lastmod>' . "\n";
				$output .= '  <changefreq>weekly</changefreq>' . "\n";
				$output .= '  <priority>1.0</priority>' . "\n";

				if ($product['image']) {
					$output .= '  <image:image>' . "\n";
					$output .= '    <image:loc>' . $this->escapeUrl($this->model_tool_image->resize($product['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height'))) . '</image:loc>' . "\n";
					$output .= '    <image:caption>' . $this->escapeXml($product['name']) . '</image:caption>' . "\n";
					$output .= '    <image:title>' . $this->escapeXml($product['name']) . '</image:title>' . "\n";
					$output .= '  </image:image>' . "\n";
				}

				$output .= '</url>' . "\n";
			}

			$this->load->model('catalog/category');

			$output .= $this->getCategories(0);

			$this->load->model('catalog/manufacturer');

			$manufacturers = $this->model_catalog_manufacturer->getManufacturers();

			foreach ($manufacturers as $manufacturer) {
				$output .= '<url>' . "\n";
				$output .= '  <loc>' . $this->escapeUrl($this->url->link('product/manufacturer/info', 'manufacturer_id=' . $manufacturer['manufacturer_id'])) . '</loc>' . "\n";
				$output .= '  <changefreq>weekly</changefreq>' . "\n";
				$output .= '  <priority>0.7</priority>' . "\n";
				$output .= '</url>' . "\n";
			}

			$this->load->model('catalog/information');

			$informations = $this->model_catalog_information->getInformations();

			foreach ($informations as $information) {
				$output .= '<url>' . "\n";
				$output .= '  <loc>' . $this->escapeUrl($this->url->link('information/information', 'information_id=' . $information['information_id'])) . '</loc>' . "\n";
				$output .= '  <changefreq>weekly</changefreq>' . "\n";
				$output .= '  <priority>0.5</priority>' . "\n";
				$output .= '</url>' . "\n";
			}

			$server = $this->config->get('config_ssl') ? $this->config->get('config_ssl') : $this->config->get('config_url');

			$customUrls = array(
				'organise-a-product-trial',
				'organise-a-product-demonstration',
				'am-i-eligible-for-funding-support',
				'funding-support',
				'ndis',
				'place-an-order',
				'request-quote',
				'warranty-claim',
				'contact-mobilitycare',
				'product_enq',
				'find-a-dealer',
				'become-a-dealer',
				'customer-service',
				'light-drive-2-enquiry',
				'autochair-smart-lifter-enquiry',
				'about-us',
				'blog',
				'faq',
				'brands',
				'register',
				'request-local-dealer'
			);

			foreach ($customUrls as $url) {
				$output .= '<url>' . "\n";
				$output .= '  <loc>' . $this->escapeUrl($server . $url) . '</loc>' . "\n";
				$output .= '  <changefreq>monthly</changefreq>' . "\n";
				$output .= '  <priority>0.8</priority>' . "\n";
				$output .= '</url>' . "\n";
			}

			$output .= '</urlset>';

			$this->response->addHeader('Content-Type: application/xml');
			$this->response->setOutput($output);
		}
	}

	protected function getCategories($parent_id) {
		$output = '';

		$results = $this->model_catalog_category->getCategories($parent_id);

		foreach ($results as $result) {
			$output .= '<url>' . "\n";
			$output .= '  <loc>' . trim($this->escapeUrl($this->url->link('product/category', 'path=' . $result['category_id']))) . '</loc>' . "\n";
			$output .= '  <changefreq>weekly</changefreq>' . "\n";
			$output .= '  <priority>0.7</priority>' . "\n";
			$output .= '</url>' . "\n";

			$output .= $this->getCategories($result['category_id']);
		}

		return $output;
	}

	private function escapeXml($string) {
		return htmlspecialchars($string, ENT_XML1 | ENT_QUOTES, 'UTF-8');
	}

	private function escapeUrl($url) {
		// Trim whitespace and ensure ampersands in URLs are XML-safe
		$url = trim($url);
		return str_replace('&', '&amp;', str_replace('&amp;', '&', $url));
	}
}
