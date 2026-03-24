<?php
class ControllerExtensionCaptchaBasic extends Controller {
	/**
	 * Generates a captcha widget. Supports named instances so multiple
	 * captcha widgets on the same page don't overwrite each other.
	 * 
	 * The captcha name is derived from the current route (e.g. 'information/contact'
	 * becomes session key 'captcha_information_contact'). Each form gets its own key.
	 */
	public function index($error = array()) {
		$this->load->language('extension/captcha/basic');

		if (isset($error['captcha'])) {
			$data['error_captcha'] = $error['captcha'];
		} else {
			$data['error_captcha'] = '';
		}

		$data['route'] = isset($this->request->get['route']) ? $this->request->get['route'] : '';
		
		// Derive a unique captcha name from the page route
		$captcha_name = $this->getCaptchaName();
		$data['captcha_name'] = $captcha_name;

		$captcha_value = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
		$this->session->data['captcha_' . $captcha_name] = $captcha_value;
		
		// Also keep backward-compatible session key (used by forms that
		// are the only captcha on their page)
		$this->session->data['captcha'] = $captcha_value;

		return $this->load->view('extension/captcha/basic', $data);
	}

	/**
	 * Validates captcha. Checks the named session key first, falls back to
	 * the generic 'captcha' key for backward compatibility.
	 */
	public function validate() {
		$this->load->language('extension/captcha/basic');
		
		$posted = isset($this->request->post['captcha']) ? $this->request->post['captcha'] : '';
		
		if (empty($posted)) {
			return $this->language->get('error_captcha');
		}
		
		// Check named captcha key first (from the page that loaded the captcha)
		$captcha_name = $this->getCaptchaName();
		$named_key = 'captcha_' . $captcha_name;
		
		if (!empty($this->session->data[$named_key]) && $this->session->data[$named_key] == $posted) {
			return ''; // valid
		}
		
		// Check the listing_captcha key (from so_listing_tabs module)
		if (!empty($this->session->data['listing_captcha']) && $this->session->data['listing_captcha'] == $posted) {
			return ''; // valid
		}
		
		// Fallback: check generic session key
		if (!empty($this->session->data['captcha']) && $this->session->data['captcha'] == $posted) {
			return ''; // valid
		}
		
		// Last resort: check the product_product key (forms rendered on product pages
		// validate via information/quote_request route, so the named key differs)
		if (!empty($this->session->data['captcha_product_product']) && $this->session->data['captcha_product_product'] == $posted) {
			return ''; // valid
		}
		
		return $this->language->get('error_captcha');
	}
	
	public function validateCustom() {
		$this->load->language('extension/captcha/basic');

		if (empty($this->request->post['originalCaptcha']) || ($this->request->post['originalCaptcha'] != $this->request->post['captcha'])) {
			return $this->language->get('error_captcha');
		}
	}

	/**
	 * AJAX endpoint to regenerate captcha value + image URL.
	 * Call: index.php?route=extension/captcha/basic/refresh&captcha_name=product_product
	 * Returns JSON: { "image_url": "index.php?route=extension/captcha/basic/captcha&captcha_name=...&t=..." }
	 */
	public function refresh() {
		$captcha_name = isset($this->request->get['captcha_name']) ? preg_replace('/[^a-zA-Z0-9_]/', '', $this->request->get['captcha_name']) : 'default';
		
		$captcha_value = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
		$this->session->data['captcha_' . $captcha_name] = $captcha_value;
		$this->session->data['captcha'] = $captcha_value;
		
		$image_url = 'index.php?route=extension/captcha/basic/captcha&captcha_name=' . $captcha_name . '&t=' . time();
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode(['image_url' => $image_url]));
	}

	public function captcha() {
		// Support named captcha: check if a specific name was requested
		$captcha_name = isset($this->request->get['captcha_name']) ? $this->request->get['captcha_name'] : '';
		$named_key = $captcha_name ? 'captcha_' . $captcha_name : '';
		
		// Try named key first, fall back to generic
		if ($named_key && !empty($this->session->data[$named_key])) {
			$captcha_text = $this->session->data[$named_key];
		} elseif (!empty($this->session->data['captcha'])) {
			$captcha_text = $this->session->data['captcha'];
		} else {
			$captcha_text = '????';
		}
		
		$image = imagecreatetruecolor(150, 35);

		$width = imagesx($image);
		$height = imagesy($image);

		$black = imagecolorallocate($image, 0, 0, 0);
		$white = imagecolorallocate($image, 255, 255, 255);
		$red = imagecolorallocatealpha($image, 255, 0, 0, 75);
		$green = imagecolorallocatealpha($image, 0, 255, 0, 75);
		$blue = imagecolorallocatealpha($image, 0, 0, 255, 75);

		imagefilledrectangle($image, 0, 0, $width, $height, $white);
		imagefilledellipse($image, ceil(rand(5, 145)), ceil(rand(0, 35)), 30, 30, $red);
		imagefilledellipse($image, ceil(rand(5, 145)), ceil(rand(0, 35)), 30, 30, $green);
		imagefilledellipse($image, ceil(rand(5, 145)), ceil(rand(0, 35)), 30, 30, $blue);
		imagefilledrectangle($image, 0, 0, $width, 0, $black);
		imagefilledrectangle($image, $width - 1, 0, $width - 1, $height - 1, $black);
		imagefilledrectangle($image, 0, 0, 0, $height - 1, $black);
		imagefilledrectangle($image, 0, $height - 1, $width, $height - 1, $black);

		imagestring($image, 10, intval(($width - (strlen($captcha_text) * 9)) / 2), intval(($height - 15) / 2), $captcha_text, $black);

		header('Content-type: image/jpeg');
		header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
		header('Cache-Control: post-check=0, pre-check=0', false);
		header('Pragma: no-cache');
		header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

		imagejpeg($image);

		imagedestroy($image);
		exit();
	}
	
	/**
	 * Derives a safe captcha name from the current page route.
	 * e.g. 'information/contact' → 'information_contact'
	 *      'information/quote_request/validateAjax' → 'information_quote_request'
	 *      'product/product' → 'product_product'
	 */
	private function getCaptchaName() {
		$route = isset($this->request->get['route']) ? $this->request->get['route'] : 'default';
		
		// Strip method suffixes like /validateAjax, /validate, /submit
		// so AJAX validation and normal POST use the same key
		$route = preg_replace('#/(validateAjax|validate|submit|write)$#', '', $route);
		
		// Convert slashes to underscores for a clean session key
		$name = str_replace('/', '_', $route);
		
		// Remove the captcha's own route prefix if called as sub-controller
		// In that case, use the parent page route from the referrer or fallback
		if (strpos($name, 'extension_captcha') === 0) {
			$name = 'default';
		}
		
		return preg_replace('/[^a-zA-Z0-9_]/', '', $name);
	}
}
