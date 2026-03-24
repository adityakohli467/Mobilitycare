<?php
class ControllerExtensionCaptchaBasic extends Controller {
	/**
	 * Generates a captcha widget with a unique token so that multiple
	 * browser tabs / forms don't overwrite each other's captcha values.
	 */
	public function index($error = array()) {
		$this->load->language('extension/captcha/basic');

		if (isset($error['captcha'])) {
			$data['error_captcha'] = $error['captcha'];
		} else {
			$data['error_captcha'] = '';
		}

		$data['route'] = isset($this->request->get['route']) ? $this->request->get['route'] : '';
		
		$captcha_name = $this->getCaptchaName();
		$data['captcha_name'] = $captcha_name;

		$captcha_value = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);

		// Token-based storage: each form instance gets a unique token
		$token = bin2hex(random_bytes(16));
		$this->pruneTokens();
		if (!isset($this->session->data['captcha_tokens'])) {
			$this->session->data['captcha_tokens'] = [];
		}
		$this->session->data['captcha_tokens'][$token] = $captcha_value;
		$data['captcha_token'] = $token;

		// Backward-compatible keys (still used by single-form pages)
		$this->session->data['captcha_' . $captcha_name] = $captcha_value;
		$this->session->data['captcha'] = $captcha_value;

		return $this->load->view('extension/captcha/basic', $data);
	}

	/**
	 * Validates captcha. Checks token-based lookup first, then falls back
	 * to named/generic session keys for backward compatibility.
	 */
	public function validate() {
		$this->load->language('extension/captcha/basic');
		
		$posted = isset($this->request->post['captcha']) ? $this->request->post['captcha'] : '';
		
		if (empty($posted)) {
			return $this->language->get('error_captcha');
		}

		// 1. Token-based check (multi-tab safe)
		$token = isset($this->request->post['captcha_token']) ? $this->request->post['captcha_token'] : '';
		if ($token && !empty($this->session->data['captcha_tokens'][$token])) {
			if ($this->session->data['captcha_tokens'][$token] == $posted) {
				unset($this->session->data['captcha_tokens'][$token]);
				return ''; // valid
			}
			// Token exists but value wrong — don't fall through to legacy keys
			return $this->language->get('error_captcha');
		}
		
		// 2. Legacy: check named captcha key
		$captcha_name = $this->getCaptchaName();
		$named_key = 'captcha_' . $captcha_name;
		
		if (!empty($this->session->data[$named_key]) && $this->session->data[$named_key] == $posted) {
			return ''; // valid
		}
		
		// 3. Check the listing_captcha key (from so_listing_tabs module)
		if (!empty($this->session->data['listing_captcha']) && $this->session->data['listing_captcha'] == $posted) {
			return ''; // valid
		}
		
		// 4. Fallback: check generic session key
		if (!empty($this->session->data['captcha']) && $this->session->data['captcha'] == $posted) {
			return ''; // valid
		}
		
		// 5. Last resort: check the product_product key
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
	 * Returns JSON with image_url and captcha_token for multi-tab safety.
	 */
	public function refresh() {
		$captcha_name = isset($this->request->get['captcha_name']) ? preg_replace('/[^a-zA-Z0-9_]/', '', $this->request->get['captcha_name']) : 'default';
		
		$captcha_value = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);

		// Token-based storage
		$token = bin2hex(random_bytes(16));
		$this->pruneTokens();
		if (!isset($this->session->data['captcha_tokens'])) {
			$this->session->data['captcha_tokens'] = [];
		}
		$this->session->data['captcha_tokens'][$token] = $captcha_value;

		// Backward-compatible keys
		$this->session->data['captcha_' . $captcha_name] = $captcha_value;
		$this->session->data['captcha'] = $captcha_value;
		
		$image_url = 'index.php?route=extension/captcha/basic/captcha&captcha_token=' . $token . '&t=' . time();
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode(['image_url' => $image_url, 'captcha_token' => $token]));
	}

	public function captcha() {
		// Token-based lookup first (multi-tab safe)
		$token = isset($this->request->get['captcha_token']) ? $this->request->get['captcha_token'] : '';
		if ($token && !empty($this->session->data['captcha_tokens'][$token])) {
			$captcha_text = $this->session->data['captcha_tokens'][$token];
		} else {
			// Legacy: named captcha or generic fallback
			$captcha_name = isset($this->request->get['captcha_name']) ? $this->request->get['captcha_name'] : '';
			$named_key = $captcha_name ? 'captcha_' . $captcha_name : '';
			
			if ($named_key && !empty($this->session->data[$named_key])) {
				$captcha_text = $this->session->data[$named_key];
			} elseif (!empty($this->session->data['captcha'])) {
				$captcha_text = $this->session->data['captcha'];
			} else {
				$captcha_text = '????';
			}
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
	 * Prevents session bloat by limiting stored captcha tokens.
	 */
	private function pruneTokens() {
		if (isset($this->session->data['captcha_tokens']) && count($this->session->data['captcha_tokens']) > 20) {
			$this->session->data['captcha_tokens'] = array_slice($this->session->data['captcha_tokens'], -10, null, true);
		}
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
