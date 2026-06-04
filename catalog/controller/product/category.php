<?php
class ControllerProductCategory extends Controller {
    public function index() {
        $this->load->language('product/category');

        $this->load->model('catalog/category');
        $this->load->model('catalog/product');
        $this->load->model('tool/image');

        // Handle filters, sorting, and pagination
        if (isset($this->request->get['filter'])) {
            $filter = $this->request->get['filter'];
        } else {
            $filter = '';
        }

        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'p.sort_order';
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'ASC';
        }

        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        if (isset($this->request->get['limit'])) {
            $limit = (int)$this->request->get['limit'];
        } else {
            $limit = $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit');
        }

        // Breadcrumbs
        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        if (isset($this->request->get['path'])) {
            $parts = explode('_', (string)$this->request->get['path']);
            $category_id = (int)array_pop($parts);
        } else {
            $category_id = 0;
        }

        $category_info = $this->model_catalog_category->getCategory($category_id);

        if ($category_info) {
            // Build breadcrumbs by walking up the parent chain
            $parent_chain = array();
            $temp_id = $category_info['parent_id'];
            while ($temp_id) {
                $temp_info = $this->model_catalog_category->getCategory((int)$temp_id);
                if ($temp_info) {
                    array_unshift($parent_chain, array(
                        'category_id' => (int)$temp_id,
                        'name' => $temp_info['name']
                    ));
                    $temp_id = $temp_info['parent_id'];
                } else {
                    break;
                }
            }

            // Add parent categories as breadcrumbs
            foreach ($parent_chain as $parent) {
                $data['breadcrumbs'][] = array(
                    'text' => $parent['name'],
                    'href' => $this->url->link('product/category', 'path=' . $parent['category_id'])
                );
            }

            // Add current category as last breadcrumb
            $data['breadcrumbs'][] = array(
                'text' => $category_info['name'],
                'href' => $this->url->link('product/category', 'path=' . $category_id)
            );

            $this->document->setTitle($category_info['meta_title']);
            // Fallback meta description: use category name if meta_description is empty
            if (!empty($category_info['meta_description'])) {
                $this->document->setDescription($category_info['meta_description']);
            } else {
                $fallback_desc = 'Shop ' . $category_info['name'] . ' at MobilityCare Australia. Quality mobility aids with expert advice, NDIS funding support & nationwide delivery.';
                $this->document->setDescription($fallback_desc);
            }
            $this->document->setKeywords($category_info['meta_keyword']);

            $data['heading_title'] = $category_info['name'];
            $data['text_compare'] = sprintf($this->language->get('text_compare'), (isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0));

            // Category image
            if ($category_info['image']) {
                $data['thumb'] = $this->model_tool_image->resize(
                    $category_info['image'],
                    $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_width'),
                    $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_height')
                );
            } else {
                $data['thumb'] = '';
            }

            $data['description'] = html_entity_decode($category_info['description'], ENT_QUOTES, 'UTF-8');
            $data['compare'] = $this->url->link('product/compare');

            $url = '';
            if (isset($this->request->get['filter'])) {
                $url .= '&filter=' . $this->request->get['filter'];
            }
            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }
            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }
            if (isset($this->request->get['limit'])) {
                $url .= '&limit=' . $this->request->get['limit'];
            }

            // Fetch subcategories
            $data['categories'] = array();
            $data['products'] = array();    
            
            // echo $category_id; exit;
            $results = $this->model_catalog_category->getCategories($category_id);
            if(isset($results) && !empty($results)){
                
            foreach ($results as $result) {
                // Get subcategory image
                $image = $result['image'] ? $this->model_tool_image->resize(
                    $result['image'],
                    $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_width'),
                    $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_height')
                ) : $this->model_tool_image->resize('placeholder.png', 
                    $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_width'),
                    $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_height')
                );

                // Count total products in subcategory (including sub-subcategories)
                $filter_data = array(
                    'filter_category_id' => $result['category_id'],
                    'filter_sub_category' => true
                );
                $product_total = $this->model_catalog_product->getTotalProducts($filter_data);

                $data['categories'][] = array(
                    'name' => $result['name'] . ($this->config->get('config_product_count') ? ' (' . $product_total . ')' : ''),
                    'thumb' => $image,
                    'href' => $this->url->link('product/category/subCategoryList', 'path=' . $result['category_id'] . $url,true)
                );
            }    
            }
            
            // Only fetch products if the category has no subcategories
            
            if (empty($results)) {
                $filter_data = array(
                    'filter_category_id' => $category_id,
                    'filter_sub_category' => false, // Include sub-subcategory products
                    'filter_filter' => $filter,
                    'sort' => $sort,
                    'order' => $order,
                    'start' => ($page - 1) * $limit,
                    'limit' => $limit
                );

                $product_total = $this->model_catalog_product->getTotalProducts($filter_data);
               $products = $this->model_catalog_product->getProducts($filter_data);
       foreach ($products as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
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
				
// 				ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

				$data['products'][] = array(
					'product_id'  => $result['product_id'],
					'display_add_to_cart'  => $result['display_add_to_cart'],
					'display_custom_quote'  => $result['display_custom_quote'],
					'show_price'  => $result['show_price'],
					'thumb'       => $image,
					'name'        => $result['name'],
					'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
					'price'       => $price,
					'special'     => $special,
					'tax'         => $tax,
					'minimum'     => $result['minimum'] > 0 ? $result['minimum'] : 1,
					'rating'      => $result['rating'],
					'href'        => $this->url->link('product/product', '&product_id=' . $result['product_id'])
				);
				
			
			}
                // Pagination
                $pagination = new Pagination();
                $pagination->total = $product_total;
                $pagination->page = $page;
                $pagination->limit = $limit;
                $pagination->url = $this->url->link('product/category', 'path=' . $this->request->get['path'] . $url . '&page={page}');
                $data['pagination'] = $pagination->render();

                $data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($product_total - $limit)) ? $product_total : ((($page - 1) * $limit) + $limit), $product_total, ceil($product_total / $limit));

                // Canonical and prev/next links
                if ($page == 1) {
                    $this->document->addLink($this->url->link('product/category', 'path=' . $category_info['category_id']), 'canonical');
                } else {
                    $this->document->addLink($this->url->link('product/category', 'path=' . $category_info['category_id'] . '&page=' . $page), 'canonical');
                }

                if ($page > 1) {
                    $this->document->addLink($this->url->link('product/category', 'path=' . $category_info['category_id'] . (($page - 2) ? '&page=' . ($page - 1) : '')), 'prev');
                }

                if ($limit && ceil($product_total / $limit) > $page) {
                    $this->document->addLink($this->url->link('product/category', 'path=' . $category_info['category_id'] . '&page=' . ($page + 1)), 'next');
                }
            }

            // Sorting options
            $data['sorts'] = array();
            $data['sorts'][] = array(
                'text' => $this->language->get('text_default'),
                'value' => 'p.sort_order-ASC',
                'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.sort_order&order=ASC' . $url)
            );
            $data['sorts'][] = array(
                'text' => $this->language->get('text_name_asc'),
                'value' => 'pd.name-ASC',
                'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=pd.name&order=ASC' . $url)
            );
            $data['sorts'][] = array(
                'text' => $this->language->get('text_name_desc'),
                'value' => 'pd.name-DESC',
                'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=pd.name&order=DESC' . $url)
            );
            $data['sorts'][] = array(
                'text' => $this->language->get('text_price_asc'),
                'value' => 'p.price-ASC',
                'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.price&order=ASC' . $url)
            );
            $data['sorts'][] = array(
                'text' => $this->language->get('text_price_desc'),
                'value' => 'p.price-DESC',
                'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.price&order=DESC' . $url)
            );
            if ($this->config->get('config_review_status')) {
                $data['sorts'][] = array(
                    'text' => $this->language->get('text_rating_desc'),
                    'value' => 'rating-DESC',
                    'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=rating&order=DESC' . $url)
                );
                $data['sorts'][] = array(
                    'text' => $this->language->get('text_rating_asc'),
                    'value' => 'rating-ASC',
                    'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=rating&order=ASC' . $url)
                );
            }
            $data['sorts'][] = array(
                'text' => $this->language->get('text_model_asc'),
                'value' => 'p.model-ASC',
                'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.model&order=ASC' . $url)
            );
            $data['sorts'][] = array(
                'text' => $this->language->get('text_model_desc'),
                'value' => 'p.model-DESC',
                'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.model&order=DESC' . $url)
            );

            // Limit options
            $data['limits'] = array();
            $limits = array_unique(array($this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit'), 25, 50, 75, 100));
            sort($limits);
            foreach ($limits as $value) {
                $data['limits'][] = array(
                    'text' => $value,
                    'value' => $value,
                    'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'] . $url . '&limit=' . $value)
                );
            }

            $data['sort'] = $sort;
            $data['order'] = $order;
            $data['limit'] = $limit;
            $data['continue'] = $this->url->link('common/home');
            
            			


            // Open Graph meta tags for category
            $og_title = htmlspecialchars($category_info['meta_title'] ?: $category_info['name'], ENT_QUOTES, 'UTF-8');
            $og_description = htmlspecialchars(strip_tags($category_info['meta_description'] ?: ''), ENT_QUOTES, 'UTF-8');
            $og_url = htmlspecialchars($this->url->link('product/category', 'path=' . $this->request->get['path']), ENT_QUOTES, 'UTF-8');
            $og_image = !empty($data['thumb']) ? htmlspecialchars($data['thumb'], ENT_QUOTES, 'UTF-8') : '';
            $og_tags = '<meta property="og:type" content="website" />' . "\n"
                . '<meta property="og:title" content="' . $og_title . '" />' . "\n"
                . '<meta property="og:description" content="' . $og_description . '" />' . "\n"
                . '<meta property="og:url" content="' . $og_url . '" />' . "\n"
                . ($og_image ? '<meta property="og:image" content="' . $og_image . '" />' . "\n" : '')
                . '<meta property="og:site_name" content="' . htmlspecialchars($this->config->get('config_name'), ENT_QUOTES, 'UTF-8') . '" />' . "\n"
                . '<meta name="twitter:card" content="summary" />' . "\n"
                . '<meta name="twitter:title" content="' . $og_title . '" />' . "\n"
                . '<meta name="twitter:description" content="' . $og_description . '" />' . "\n";
            $this->document->addAnalytic($og_tags);

            // BreadcrumbList JSON-LD for category
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
                $this->document->addAnalytic('<script type="application/ld+json">' . json_encode($breadcrumb_list, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>');
            }

            // Load common controllers
            $data['column_left'] = $this->load->controller('common/column_left');
            $data['column_right'] = $this->load->controller('common/column_right');
            $data['content_top'] = $this->load->controller('common/content_top');
            $data['content_bottom'] = $this->load->controller('common/content_bottom');
            $data['footer'] = $this->load->controller('common/footer');
            $data['header'] = $this->load->controller('common/header');

            $this->response->setOutput($this->load->view('product/category', $data));
        } else {
            // Handle category not found
            $url = '';
            if (isset($this->request->get['path'])) {
                $url .= '&path=' . $this->request->get['path'];
            }
            if (isset($this->request->get['filter'])) {
                $url .= '&filter=' . $this->request->get['filter'];
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
                'text' => $this->language->get('text_error'),
                'href' => $this->url->link('product/category', $url)
            );

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
    
    public function subCategoryList(){
        
        $this->load->model('catalog/category');
		$this->load->model('catalog/product');
		$this->load->model('tool/image');

		$url = '';

        $category_id = $this->request->get['path'];
        
        $category_info = $this->model_catalog_category->getCategory($category_id);
        

        if ($category_info) {
            $this->document->setTitle($category_info['meta_title']);
            // Fallback meta description for subcategory listing
            if (!empty($category_info['meta_description'])) {
                $this->document->setDescription($category_info['meta_description']);
            } else {
                $fallback_desc = 'Browse ' . $category_info['name'] . ' at MobilityCare Australia. Quality mobility aids with expert advice, NDIS funding support & nationwide delivery.';
                $this->document->setDescription($fallback_desc);
            }
            $this->document->setKeywords($category_info['meta_keyword']);
            $data['heading_title'] = $category_info['name'];
        }

        $allSubcats = $this->model_catalog_category->getCategories($category_id); 
      
        
        // if category has no further subcat than check for products
        if(isset($allSubcats) && !empty($allSubcats)){
          foreach ($allSubcats as $result) {
				$filter_data = array(
					'filter_category_id'  => $result['category_id'],
					'filter_sub_category' => true
				);

				$data['categories'][] = array(
					'name' => $result['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : ''),
					'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '_' . $result['category_id'] . $url,true)
				);
			}  
        }else{
           $this->response->redirect($this->url->link('product/category', 'path=' . $category_id));
        }
        
			

			
	// Load common controllers
            $data['column_left'] = $this->load->controller('common/column_left');
            $data['column_right'] = $this->load->controller('common/column_right');
            $data['content_top'] = $this->load->controller('common/content_top');
            $data['content_bottom'] = $this->load->controller('common/content_bottom');
            $data['footer'] = $this->load->controller('common/footer');
            $data['header'] = $this->load->controller('common/header');	
			
	$this->response->setOutput($this->load->view('product/allSubCats', $data));		
        
    }
   
}