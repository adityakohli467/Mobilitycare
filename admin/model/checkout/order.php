<?php
/**
 * Minimal admin-side checkout/order model.
 *
 * The customer confirmation PDF is built by the catalog model
 * catalog/model/extension/module/pdf_invoice.php, which calls
 * $this->model_checkout_order->getOrder()/getOrderProducts()/getOrderOptions()/
 * getOrderVouchers()/getOrderTotals(). The admin application has no checkout/order
 * model, so this shim provides exactly those methods (delegating to the same
 * order tables) so the admin can regenerate the identical PDF from live order
 * totals. It intentionally mirrors the field set the PDF generator consumes.
 */
class ModelCheckoutOrder extends Model {
	public function getOrder($order_id) {
		$order_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order` WHERE order_id = '" . (int)$order_id . "'");

		if (!$order_query->num_rows) {
			return false;
		}

		$order = $order_query->row;

		// Resolve the language code the same way the catalog model does.
		$this->load->model('localisation/language');

		$language_info = $this->model_localisation_language->getLanguage($order['language_id']);

		if ($language_info) {
			$order['language_code'] = $language_info['code'];
		} else {
			$order['language_code'] = $this->config->get('config_language');
		}

		return $order;
	}

	public function getOrderProducts($order_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_product` WHERE order_id = '" . (int)$order_id . "'");

		return $query->rows;
	}

	public function getOrderOptions($order_id, $order_product_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_option` WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$order_product_id . "'");

		return $query->rows;
	}

	public function getOrderVouchers($order_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_voucher` WHERE order_id = '" . (int)$order_id . "'");

		return $query->rows;
	}

	public function getOrderTotals($order_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_total` WHERE order_id = '" . (int)$order_id . "' ORDER BY sort_order ASC");

		return $query->rows;
	}
}
