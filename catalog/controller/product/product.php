<?php
class ControllerProductProduct extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('product/product');
        $this->load->model('tool/image');
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);
		
		$this->load->model('catalog/category');
$this->load->model('catalog/product');


    
    


// Detect product
if (isset($this->request->get['product_id'])) {
    $product_id = (int)$this->request->get['product_id'];
    $product_info = $this->model_catalog_product->getProduct($product_id);
    
    $data['current_url'] = $this->url->link('product/product', 'product_id=' . $product_id);
$data['price_no_currency'] = preg_replace('/[^0-9.]/', '', $product_info['price']);



    if ($product_info) {
        // Find category path for this product (primary category)
        $category_id = 0;
        $category_info = [];

        $categories = $this->model_catalog_product->getCategoriesInfo($product_id);
       
        if ($categories) {
            $category_id = end($categories)['category_id']; // use last assigned category
            $category_info = $this->model_catalog_category->getCategory($category_id);
        }

        // Build category hierarchy
        if ($category_info) {
            $path = $this->getFullCategoryPath($category_id);
            
            if ($path) {
                $parts = explode('_', $path);
                foreach ($parts as $path_id) {
                    $cat = $this->model_catalog_category->getCategory($path_id);
                    if ($cat) {
                        $data['breadcrumbs'][] = array(
                            'text' => $cat['name'],
                            'href' => $this->url->link('product/category', 'path=' . $path_id)
                        );
                    }
                }
            }
        }

        // Finally, add product name
        $data['breadcrumbs'][] = array(
            'text' => $product_info['name'],
            'href' => $this->url->link('product/product', 'product_id=' . $product_id)
        );
    }
}

	

		
		
	    	$this->load->model('catalog/demo_request');
            $quoteProducts = $this->model_catalog_demo_request->getProductsByCategory(101);
           
            // Captcha disabled
	    	$data['captcha'] = '';
		    
            $data['quoteProducts']  = $quoteProducts;
           
            
            

// 		if (isset($this->request->get['path'])) {
// 			$path = '';

// 			$parts = explode('_', (string)$this->request->get['path']);

// 			$category_id = (int)array_pop($parts);

// 			foreach ($parts as $path_id) {
// 				if (!$path) {
// 					$path = $path_id;
// 				} else {
// 					$path .= '_' . $path_id;
// 				}

// 				$category_info = $this->model_catalog_category->getCategory($path_id);

// 				if ($category_info) {
// 					$data['breadcrumbs'][] = array(
// 						'text' => $category_info['name'],
// 						'href' => $this->url->link('product/category', 'path=' . $path)
// 					);
// 				}
// 			}

// 			// Set the last category breadcrumb
// 			$category_info = $this->model_catalog_category->getCategory($category_id);

// 			if ($category_info) {
// 				$url = '';

// 				if (isset($this->request->get['sort'])) {
// 					$url .= '&sort=' . $this->request->get['sort'];
// 				}

// 				if (isset($this->request->get['order'])) {
// 					$url .= '&order=' . $this->request->get['order'];
// 				}

// 				if (isset($this->request->get['page'])) {
// 					$url .= '&page=' . $this->request->get['page'];
// 				}

// 				if (isset($this->request->get['limit'])) {
// 					$url .= '&limit=' . $this->request->get['limit'];
// 				}

// 				$data['breadcrumbs'][] = array(
// 					'text' => $category_info['name'],
// 					'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'] . $url)
// 				);
// 			}
// 		}

		$this->load->model('catalog/manufacturer');

		if (isset($this->request->get['manufacturer_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_brand'),
				'href' => $this->url->link('product/manufacturer')
			);

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($this->request->get['manufacturer_id']);

			if ($manufacturer_info) {
				$data['breadcrumbs'][] = array(
					'text' => $manufacturer_info['name'],
					'href' => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . $url)
				);
			}
		}

		if (isset($this->request->get['search']) || isset($this->request->get['tag'])) {
			$url = '';

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . $this->request->get['search'];
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . $this->request->get['tag'];
			}

			if (isset($this->request->get['description'])) {
				$url .= '&description=' . $this->request->get['description'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['sub_category'])) {
				$url .= '&sub_category=' . $this->request->get['sub_category'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_search'),
				'href' => $this->url->link('product/search', $url)
			);
		}

		if (isset($this->request->get['product_id'])) {
			$product_id = (int)$this->request->get['product_id'];
		} else {
			$product_id = 0;
		}

		

		$product_info = $this->model_catalog_product->getProduct($product_id);
		$data['product_specs'] = $this->model_catalog_product->getProductSpecs($product_id);
		
		 $product_messages = $this->db->query("SELECT message FROM " . DB_PREFIX . "product_messages WHERE product_id = '" . (int)$product_id . "'");
         $data['dynamic_messages'] = array_column($product_messages->rows, 'message');
       
       
// 		echo "<pre>"; print_r($data['product_specs']); exit;
 // Use SEO-friendly URLs directly to avoid 301 redirect which converts POST to GET
 $data['action'] = '/request-quote/';
	$data['demo_form_action'] = '/organise-a-product-demonstration/';	
     	 // Get product features
            $product_features = $this->model_catalog_product->getProductFeatures($product_id);
            $data['product_features'] = array();

            foreach ($product_features as $feature) {
                // Build proper image URL - handle both full URLs (legacy) and relative paths (new)
                if (!empty($feature['image']) && strpos($feature['image'], 'http') === 0) {
                    $feature_image = $feature['image'];
                } elseif (!empty($feature['image'])) {
                    $feature_image = $this->model_tool_image->resize($feature['image'], 600, 600);
                } else {
                    $feature_image = '';
                }

                $data['product_features'][] = array(
                    'title' => $feature['title'],
                    'image' => $feature_image,
                    'video' => $feature['video'],
                    'text' => html_entity_decode($feature['text'], ENT_QUOTES, 'UTF-8'),
                    'link' => isset($feature['link']) ? $feature['link'] : ''
                );
            }
		$data['product']['right_for_you'] = html_entity_decode($product_info['right_for_you'], ENT_QUOTES, 'UTF-8');
        $data['product']['why_love'] = html_entity_decode($product_info['why_love'], ENT_QUOTES, 'UTF-8');
        $data['product']['everyday_life'] = html_entity_decode($product_info['everyday_life'], ENT_QUOTES, 'UTF-8');
        $data['product']['what_different'] = html_entity_decode($product_info['what_different'], ENT_QUOTES, 'UTF-8');
        $data['product']['desc_video'] = html_entity_decode($product_info['desc_video'], ENT_QUOTES, 'UTF-8');
       
        
            $data['marketing_popup'] = $this->load->controller('common/marketing_popup');
        
         
         

        
// 	echo "<pre>"; print_r($data['product_specs']); exit;
		//check product page open from cateory page
		if (isset($this->request->get['path'])) {
			$parts = explode('_', (string)$this->request->get['path']);
						
			if(empty($this->model_catalog_product->checkProductCategory($product_id, $parts))) {
				$product_info = array();
			}
		}

		//check product page open from manufacturer page
		if (isset($this->request->get['manufacturer_id']) && !empty($product_info)) {
			if($product_info['manufacturer_id'] !=  $this->request->get['manufacturer_id']) {
				$product_info = array();
			}
		}

		if ($product_info) {
			$url = '';

			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
			}

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['manufacturer_id'])) {
				$url .= '&manufacturer_id=' . $this->request->get['manufacturer_id'];
			}

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . $this->request->get['search'];
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . $this->request->get['tag'];
			}

			if (isset($this->request->get['description'])) {
				$url .= '&description=' . $this->request->get['description'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['sub_category'])) {
				$url .= '&sub_category=' . $this->request->get['sub_category'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

// 			$data['breadcrumbs'][] = array(
// 				'text' => $product_info['name'],
// 				'href' => $this->url->link('product/product', $url . '&product_id=' . $this->request->get['product_id'])
// 			);

			$this->document->setTitle($product_info['meta_title']);
			// Fallback meta description: use product name + truncated description if meta_description is empty
			if (!empty($product_info['meta_description'])) {
				$this->document->setDescription($product_info['meta_description']);
			} else {
				$fallback_desc = $product_info['name'] . ' - Buy online at MobilityCare Australia. ' . trim(substr(strip_tags(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8')), 0, 120));
				$this->document->setDescription($fallback_desc);
			}
			$this->document->setKeywords($product_info['meta_keyword']);
			$this->document->addLink($this->url->link('product/product', 'product_id=' . $this->request->get['product_id']), 'canonical');
			$this->document->addScript('catalog/view/javascript/jquery/magnific/jquery.magnific-popup.min.js');
			$this->document->addStyle('catalog/view/javascript/jquery/magnific/magnific-popup.css');
			$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment.min.js');
			$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment-with-locales.min.js');
			$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js');
			$this->document->addStyle('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css');

			$data['heading_title'] = $product_info['name'];
			$data['short_description'] = $product_info['short_description'];
			$data['display_add_to_cart'] = $product_info['display_add_to_cart'];
			$data['display_custom_quote'] = $product_info['display_custom_quote'];
			$data['display_custom_quote_for_vehicle_modification'] = $product_info['display_custom_quote_for_vehicle_mod'];

			$data['text_minimum'] = sprintf($this->language->get('text_minimum'), $product_info['minimum']);
			$data['text_login'] = sprintf($this->language->get('text_login'), $this->url->link('account/login', '', true), $this->url->link('account/register', '', true));

			$this->load->model('catalog/review');

			$data['tab_review'] = sprintf($this->language->get('tab_review'), $product_info['reviews']);

			$data['product_id'] = (int)$this->request->get['product_id'];
			$data['manufacturer'] = $product_info['manufacturer'];
			$data['manufacturers'] = $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $product_info['manufacturer_id']);
			$data['model'] = $product_info['model'];
			$data['reward'] = $product_info['reward'];
			$data['points'] = $product_info['points'];
			$data['description'] = html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8');
			$data['tooltip_text'] = html_entity_decode($product_info['tooltip_text'], ENT_QUOTES, 'UTF-8');

// 			echo "<pre>"; print_r($data['description']); exit;

			if ($product_info['quantity'] <= 0) {
				$data['stock'] = $product_info['stock_status'];
			} elseif ($this->config->get('config_stock_display')) {
				$data['stock'] = $product_info['quantity'];
			} else {
				$data['stock'] = $this->language->get('text_instock');
			}

	

			if ($product_info['image']) {
				$data['popup'] = $this->model_tool_image->resize($product_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height'));
			} else {
				$data['popup'] = '';
			}

			if ($product_info['image']) {
				$data['thumb'] = $this->model_tool_image->resize($product_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_height'));
			} else {
				$data['thumb'] = '';
			}

			$data['images'] = array();

			$results = $this->model_catalog_product->getProductImages($this->request->get['product_id']);

			foreach ($results as $result) {
				$data['images'][] = array(
					'popup' => $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height')),
					'thumb' => $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_additional_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_additional_height'))
				);
			}

			if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
				$data['price'] = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
			} else {
				$data['price'] = false;
			}

			if (!is_null($product_info['special']) && (float)$product_info['special'] >= 0) {
				$data['special'] = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				$tax_price = (float)$product_info['special'];
			} else {
				$data['special'] = false;
				$tax_price = (float)$product_info['price'];
			}

			if ($this->config->get('config_tax')) {
				$data['tax'] = $this->currency->format($tax_price, $this->session->data['currency']);
			} else {
				$data['tax'] = false;
			}

			$discounts = $this->model_catalog_product->getProductDiscounts($this->request->get['product_id']);

			$data['discounts'] = array();

			foreach ($discounts as $discount) {
				$data['discounts'][] = array(
					'quantity' => $discount['quantity'],
					'price'    => $this->currency->format($this->tax->calculate($discount['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'])
				);
			}

			$data['options'] = array();

			foreach ($this->model_catalog_product->getProductOptions($this->request->get['product_id']) as $option) {
				$product_option_value_data = array();

				foreach ($option['product_option_value'] as $option_value) {
					if (!$option_value['subtract'] || ($option_value['quantity'] > 0)) {
						if ((($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) && (float)$option_value['price']) {
							$price = $this->currency->format($this->tax->calculate($option_value['price'], $product_info['tax_class_id'], $this->config->get('config_tax') ? 'P' : false), $this->session->data['currency']);
						} else {
							$price = false;
						}

						$product_option_value_data[] = array(
							'product_option_value_id' => $option_value['product_option_value_id'],
							'option_value_id'         => $option_value['option_value_id'],
							'name'                    => $option_value['name'],
							'image'                   => $this->model_tool_image->resize($option_value['image'], 50, 50),
							'price'                   => $price,
							'price_prefix'            => $option_value['price_prefix']
						);
					}
				}

				$data['options'][] = array(
					'product_option_id'    => $option['product_option_id'],
					'product_option_value' => $product_option_value_data,
					'option_id'            => $option['option_id'],
					'name'                 => $option['name'],
					'type'                 => $option['type'],
					'value'                => $option['value'],
					'required'             => $option['required']
				);
			}

			if ($product_info['minimum']) {
				$data['minimum'] = $product_info['minimum'];
			} else {
				$data['minimum'] = 1;
			}

			$data['review_status'] = $this->config->get('config_review_status');

			if ($this->config->get('config_review_guest') || $this->customer->isLogged()) {
				$data['review_guest'] = true;
			} else {
				$data['review_guest'] = false;
			}

			if ($this->customer->isLogged()) {
				$data['customer_name'] = $this->customer->getFirstName() . '&nbsp;' . $this->customer->getLastName();
			} else {
				$data['customer_name'] = '';
			}

			$data['reviews'] = sprintf($this->language->get('text_reviews'), (int)$product_info['reviews']);
			$data['rating'] = (int)$product_info['rating'];
			// product reviews
			$data['productReviews'] = $this->model_catalog_product->getProductReviews($product_id);
// 			echo "<pre>"; print_r($data['productReviews']); exit;

			$data['review_captcha'] = '';
			$data['share'] = $this->url->link('product/product', 'product_id=' . (int)$this->request->get['product_id']);

			$data['attribute_groups'] = $this->model_catalog_product->getProductAttributes($this->request->get['product_id']);

			$data['products'] = array();
			
			$data['downloads'] = array();
			
		
$downloads = $this->model_catalog_product->getProductDownloads($product_id);

foreach ($downloads as $download) {
    if (is_file(DIR_DOWNLOAD . $download['filename'])) {
        $slug = $this->slugify($download['name']) . '-' . $download['download_id'];
        $data['downloads'][] = array(
            'name' => $download['name'],
            'href' => rtrim(HTTPS_SERVER, '/') . '/documents/' . $slug,
            'mask' => $download['mask']
        );
    }
}
			
			


			$results = $this->model_catalog_product->getProductRelated($this->request->get['product_id']);

			foreach ($results as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_height'));
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_height'));
				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				if (!is_null($result['special']) && (float)$result['special'] >= 0) {
					$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					$tax_price = (float)$result['special'];
				} else {
					$special = false;
					$tax_price = (float)$result['price'];
				}
	
				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format($tax_price, $this->session->data['currency']);
				} else {
					$tax = false;
				}

				if ($this->config->get('config_review_status')) {
					$rating = (int)$result['rating'];
				} else {
					$rating = false;
				}

				$data['products'][] = array(
					'product_id'  => $result['product_id'],
					'thumb'       => $image,
					'name'        => $result['name'],
					'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
					'price'       => $price,
					'special'     => $special,
					'tax'         => $tax,
					'minimum'     => $result['minimum'] > 0 ? $result['minimum'] : 1,
					'rating'      => $rating,
					'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id'])
				);
			}

			$data['tags'] = array();

			if ($product_info['tag']) {
				$tags = explode(',', $product_info['tag']);

				foreach ($tags as $tag) {
					$data['tags'][] = array(
						'tag'  => trim($tag),
						'href' => $this->url->link('product/search', 'tag=' . trim($tag))
					);
				}
			}
   $data['so_askquestion_modal'] = $this->load->view('extension/module/so_askquestion',$data);
			$data['recurrings'] = $this->model_catalog_product->getProfiles($this->request->get['product_id']);

			$this->model_catalog_product->updateViewed($this->request->get['product_id']);
			
			// Open Graph meta tags
			$og_image = !empty($data['thumb']) ? $data['thumb'] : '';
			$og_title = htmlspecialchars($product_info['meta_title'] ?: $product_info['name'], ENT_QUOTES, 'UTF-8');
			$og_description = htmlspecialchars(strip_tags($product_info['meta_description'] ?: ''), ENT_QUOTES, 'UTF-8');
			$og_url = htmlspecialchars($data['current_url'], ENT_QUOTES, 'UTF-8');
			$og_tags = '<meta property="og:type" content="product" />' . "\n"
				. '<meta property="og:title" content="' . $og_title . '" />' . "\n"
				. '<meta property="og:description" content="' . $og_description . '" />' . "\n"
				. '<meta property="og:url" content="' . $og_url . '" />' . "\n"
				. ($og_image ? '<meta property="og:image" content="' . htmlspecialchars($og_image, ENT_QUOTES, 'UTF-8') . '" />' . "\n" : '')
				. '<meta property="og:site_name" content="' . htmlspecialchars($this->config->get('config_name'), ENT_QUOTES, 'UTF-8') . '" />' . "\n"
				. '<meta name="twitter:card" content="summary_large_image" />' . "\n"
				. '<meta name="twitter:title" content="' . $og_title . '" />' . "\n"
				. '<meta name="twitter:description" content="' . $og_description . '" />' . "\n"
				. ($og_image ? '<meta name="twitter:image" content="' . htmlspecialchars($og_image, ENT_QUOTES, 'UTF-8') . '" />' . "\n" : '');
			$this->document->addAnalytic($og_tags);

			// JSON-LD Product structured data
			$json_ld = array(
				'@context' => 'https://schema.org',
				'@type'    => 'Product',
				'name'     => $product_info['name'],
				'description' => strip_tags(html_entity_decode($product_info['meta_description'] ?: $product_info['description'], ENT_QUOTES, 'UTF-8')),
				'sku'      => $product_info['model'],
				'brand'    => array('@type' => 'Brand', 'name' => $product_info['manufacturer'] ?: $this->config->get('config_name')),
				'url'      => $data['current_url'],
			);
			if ($og_image) {
				$json_ld['image'] = $og_image;
			} else {
				$json_ld['image'] = $this->config->get('config_ssl') . 'image/placeholder.png';
			}
			if (!empty($data['price_no_currency']) && (float)$data['price_no_currency'] > 0) {
				$json_ld['offers'] = array(
					'@type'         => 'Offer',
					'priceCurrency' => $this->session->data['currency'],
					'price'         => $data['price_no_currency'],
					'availability'  => ($product_info['quantity'] > 0) ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
					'url'           => $data['current_url'],
				);
			}
			if (!empty($product_info['reviews']) && (int)$product_info['reviews'] > 0) {
				$json_ld['aggregateRating'] = array(
					'@type'       => 'AggregateRating',
					'ratingValue' => (string)(int)$product_info['rating'],
					'reviewCount' => (string)(int)$product_info['reviews'],
				);
			}
			if (!empty($data['breadcrumbs']) && count($data['breadcrumbs']) > 1) {
				$breadcrumb_list = array('@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => array());
				foreach ($data['breadcrumbs'] as $position => $crumb) {
					$breadcrumb_list['itemListElement'][] = array(
						'@type'    => 'ListItem',
						'position' => $position + 1,
						'name'     => $crumb['text'],
						'item'     => $crumb['href'],
					);
				}
				$data['json_ld_breadcrumb'] = '<script type="application/ld+json">' . json_encode($breadcrumb_list, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
			} else {
				$data['json_ld_breadcrumb'] = '';
			}
			$data['json_ld'] = '<script type="application/ld+json">' . json_encode($json_ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('product/product', $data));
		} else {
			$url = '';

			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
			}

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['manufacturer_id'])) {
				$url .= '&manufacturer_id=' . $this->request->get['manufacturer_id'];
			}

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . $this->request->get['search'];
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . $this->request->get['tag'];
			}

			if (isset($this->request->get['description'])) {
				$url .= '&description=' . $this->request->get['description'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['sub_category'])) {
				$url .= '&sub_category=' . $this->request->get['sub_category'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

// 			$data['breadcrumbs'][] = array(
// 				'text' => $this->language->get('text_error'),
// 				'href' => $this->url->link('product/product', $url . '&product_id=' . $product_id)
// 			);

			$this->document->setTitle($this->language->get('text_error'));

			$data['continue'] = $this->url->link('common/home');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');
			
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}
	
private function getFullCategoryPath($category_id) {
    $this->load->model('catalog/category');
    $category_info = $this->model_catalog_category->getCategory($category_id);

    if (!$category_info) return '';

    if ($category_info['parent_id']) {
        return $this->getFullCategoryPath($category_info['parent_id']) . '_' . $category_id;
    } else {
        return $category_id;
    }
}


	
public function download() {
    $this->load->model('catalog/product');

    if (isset($this->request->get['download_id'])) {
        $download_id = (int)$this->request->get['download_id'];
    } else {
        $download_id = 0;
    }

    $download_info = $this->model_catalog_product->getDownload($download_id);

    if ($download_info) {
        $file = DIR_DOWNLOAD . $download_info['filename'];
        $mask = basename($download_info['mask']);

        if (!headers_sent()) {
            if (file_exists($file)) {
                if (ob_get_level()) {
                    ob_end_clean();
                }

               
                $ext = strtolower(pathinfo($mask, PATHINFO_EXTENSION));

                if ($ext === 'pdf') {
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: inline; filename="' . $mask . '"');
                } else {
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename="' . $mask . '"');
                }

                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: public');
                header('Content-Length: ' . filesize($file));

                readfile($file);

                exit();
            } else {
                exit('Error: Could not find file ' . $file . '!');
            }
        } else {
            exit('Error: Headers already sent out!');
        }
    } else {
        $this->response->redirect($this->url->link('catalog/product', '', true));
    }
}

public function viewDocument() {
    $this->load->model('catalog/product');

    if (isset($this->request->get['download_id'])) {
        $download_id = (int)$this->request->get['download_id'];
    } else {
        $download_id = 0;
    }

    $download_info = $this->model_catalog_product->getDownloadWithName($download_id);

    if ($download_info && is_file(DIR_DOWNLOAD . $download_info['filename'])) {
        $document_name = $download_info['name'] ?: pathinfo(basename($download_info['mask']), PATHINFO_FILENAME);
        $ext = strtolower(pathinfo(basename($download_info['mask']), PATHINFO_EXTENSION));

        $data['document_name'] = $document_name;
        $data['pdf_url'] = $this->url->link('product/product/download', 'download_id=' . $download_id);
        $data['is_pdf'] = ($ext === 'pdf');
        $data['download_url'] = $this->url->link('product/product/download', 'download_id=' . $download_id);
        $data['favicon'] = HTTPS_SERVER . 'image/catalog/favicon.png';
        $data['site_name'] = $this->config->get('config_name');

        $this->document->setTitle($document_name . ' | ' . $this->config->get('config_name'));

        $this->response->setOutput($this->load->view('product/document_viewer', $data));
    } else {
        $this->response->redirect($this->url->link('common/home', '', true));
    }
}

private function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return $text ?: 'document';
}



	public function review() {
		$this->load->language('product/product');

		$this->load->model('catalog/review');

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$data['reviews'] = array();

		$review_total = $this->model_catalog_review->getTotalReviewsByProductId($this->request->get['product_id']);

		$results = $this->model_catalog_review->getReviewsByProductId($this->request->get['product_id'], ($page - 1) * 5, 5);

		foreach ($results as $result) {
			$data['reviews'][] = array(
				'author'     => $result['author'],
				'text'       => nl2br($result['text']),
				'rating'     => (int)$result['rating'],
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added']))
			);
		}

		$pagination = new Pagination();
		$pagination->total = $review_total;
		$pagination->page = $page;
		$pagination->limit = 5;
		$pagination->url = $this->url->link('product/product/review', 'product_id=' . $this->request->get['product_id'] . '&page={page}');

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($review_total) ? (($page - 1) * 5) + 1 : 0, ((($page - 1) * 5) > ($review_total - 5)) ? $review_total : ((($page - 1) * 5) + 5), $review_total, ceil($review_total / 5));

		$this->response->setOutput($this->load->view('product/review', $data));
	}

	public function write() {
		$this->load->language('product/product');

		$json = array();

		if (isset($this->request->get['product_id']) && $this->request->get['product_id']) {
			if ($this->request->server['REQUEST_METHOD'] == 'POST') {
				if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 25)) {
					$json['error'] = $this->language->get('error_name');
				}

				if ((utf8_strlen($this->request->post['text']) < 4) || (utf8_strlen($this->request->post['text']) > 1000)) {
					$json['error'] = $this->language->get('error_text');
				}
			
				if (empty($this->request->post['rating']) || $this->request->post['rating'] < 0 || $this->request->post['rating'] > 5) {
					$json['error'] = $this->language->get('error_rating');
				}

				if (!isset($json['error'])) {
					$this->load->model('catalog/review');

					$this->model_catalog_review->addReview($this->request->get['product_id'], $this->request->post);

					$json['success'] = $this->language->get('text_success');
				}
			}
		} else {
			$json['error'] = $this->language->get('error_product');
		} 

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function getRecurringDescription() {
		$this->load->language('product/product');
		$this->load->model('catalog/product');

		if (isset($this->request->post['product_id'])) {
			$product_id = $this->request->post['product_id'];
		} else {
			$product_id = 0;
		}

		if (isset($this->request->post['recurring_id'])) {
			$recurring_id = $this->request->post['recurring_id'];
		} else {
			$recurring_id = 0;
		}

		if (isset($this->request->post['quantity'])) {
			$quantity = $this->request->post['quantity'];
		} else {
			$quantity = 1;
		}

		$product_info = $this->model_catalog_product->getProduct($product_id);
		
		$recurring_info = $this->model_catalog_product->getProfile($product_id, $recurring_id);

		$json = array();

		if ($product_info && $recurring_info) {
			if (!$json) {
				$frequencies = array(
					'day'        => $this->language->get('text_day'),
					'week'       => $this->language->get('text_week'),
					'semi_month' => $this->language->get('text_semi_month'),
					'month'      => $this->language->get('text_month'),
					'year'       => $this->language->get('text_year'),
				);

				if ($recurring_info['trial_status'] == 1) {
					$price = $this->currency->format($this->tax->calculate($recurring_info['trial_price'] * $quantity, $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					$trial_text = sprintf($this->language->get('text_trial_description'), $price, $recurring_info['trial_cycle'], $frequencies[$recurring_info['trial_frequency']], $recurring_info['trial_duration']) . ' ';
				} else {
					$trial_text = '';
				}

				$price = $this->currency->format($this->tax->calculate($recurring_info['price'] * $quantity, $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);

				if ($recurring_info['duration']) {
					$text = $trial_text . sprintf($this->language->get('text_payment_description'), $price, $recurring_info['cycle'], $frequencies[$recurring_info['frequency']], $recurring_info['duration']);
				} else {
					$text = $trial_text . sprintf($this->language->get('text_payment_cancel'), $price, $recurring_info['cycle'], $frequencies[$recurring_info['frequency']], $recurring_info['duration']);
				}

				$json['success'] = $text;
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
