<?php
class ControllerExtensionPaymentPinPayments extends Controller {
//     public function index() {
//         $this->load->language('extension/payment/pinpayments');

//         $data['button_confirm'] = $this->language->get('button_confirm');
// $this->response->setOutput($this->load->view('extension/payment/pinpayments', $data));
//         // return $this->load->view('extension/payment/pinpayments', $data);
//     }

   public function index() {
    $this->load->language('extension/payment/pinpayments');

    $data['button_confirm'] = $this->language->get('button_confirm');
    $data['text_card_number'] = $this->language->get('text_card_number');
    $data['text_expiry_month'] = $this->language->get('text_expiry_month');
    $data['text_expiry_year'] = $this->language->get('text_expiry_year');
    $data['text_cvc'] = $this->language->get('text_cvc');
    $data['confirm'] = $this->url->link('extension/payment/pinpayments/confirm', '', true);

    // Return the view HTML (no JSON output here)
    return $this->load->view('extension/payment/pinpayments', $data);
}


    public function confirm() {
    $this->load->model('checkout/order');

    if ($this->request->server['REQUEST_METHOD'] !== 'POST') {
        $this->outputJson(['error' => 'Invalid request']);
        return;
    }

    // Basic validation of required fields
    $required = ['card_number', 'expiry_month', 'expiry_year', 'cvc'];
    foreach ($required as $field) {
        if (empty($this->request->post[$field])) {
            $this->outputJson(['error' => 'Missing ' . $field]);
            return;
        }
    }

    if (empty($this->session->data['order_id'])) {
        $this->outputJson(['error' => 'Order not found in session']);
        return;
    }

    $order_id = (int)$this->session->data['order_id'];
    $order_info = $this->model_checkout_order->getOrder($order_id);

    if (!$order_info) {
        $this->outputJson(['error' => 'Order not found']);
        return;
    }

    // Status that marks an order as paid / processed.
    $paid_status_id = (int)($this->config->get('payment_pinpayments_order_status_id') ?: $this->config->get('config_order_status_id'));

    // -----------------------------------------------------------------
    // Idempotency guard #1 - fast path.
    // A freshly created order has order_status_id = 0. As soon as a charge
    // succeeds we set a status (see below). So if a status already exists,
    // this order has already been paid - never charge the same order twice.
    // This is what stops repeat clicks / page refreshes from re-charging.
    // -----------------------------------------------------------------
    if ((int)$order_info['order_status_id'] > 0) {
        if (isset($this->log)) {
            $this->log->write('PinPayments: duplicate charge blocked for order #' . $order_id . ' (already processed, status ' . (int)$order_info['order_status_id'] . ')');
        }
        $this->outputJson(['success' => $this->url->link('checkout/success')]);
        return;
    }

    // Build charge amount (in cents) from the order total.
    $amount = (int) round($order_info['total'] * 100);
    if ($amount <= 0) {
        $this->outputJson(['error' => 'Invalid order total']);
        return;
    }

    $currency = (isset($this->config) ? $this->config->get('config_currency') : 'AUD');

    $secret_key = $this->config->get('payment_pinpayments_secret_key');
    if (empty($secret_key)) {
        $this->outputJson(['error' => 'Payment gateway not configured']);
        return;
    }

    // -----------------------------------------------------------------
    // Idempotency guard #2 - concurrency lock.
    // The duplicate charges happened because several charge requests for the
    // same order arrived almost simultaneously (stacked click handlers /
    // rapid clicks), so each one passed guard #1 before any of them had
    // finished. A MySQL advisory lock serialises them: the first request
    // charges and sets the paid status; the others then wait, re-check, see
    // the order is already paid and stop WITHOUT charging again.
    // -----------------------------------------------------------------
    $lock_prefix = defined('DB_DATABASE') ? DB_DATABASE : 'oc';
    $lock_name   = substr($lock_prefix . '_pinpay_' . $order_id, 0, 60);
    $lock_name_e = $this->db->escape($lock_name);

    $lock   = $this->db->query("SELECT GET_LOCK('" . $lock_name_e . "', 15) AS acquired");
    $got_lock = (!empty($lock->row['acquired']) && $lock->row['acquired'] == 1);

    if (!$got_lock) {
        // Another attempt for this order is still in progress.
        $this->outputJson(['error' => 'A payment for this order is already being processed. Please wait a moment and do not resubmit.']);
        return;
    }

    $json = [];

    try {
        // Re-check INSIDE the lock: a parallel request may have just paid it.
        $order_info = $this->model_checkout_order->getOrder($order_id);

        if (!$order_info || (int)$order_info['order_status_id'] > 0) {
            if (isset($this->log)) {
                $this->log->write('PinPayments: duplicate charge blocked inside lock for order #' . $order_id);
            }
            $json = ['success' => $this->url->link('checkout/success')];
        } else {
            $charge_data = [
                'amount'      => $amount,
                'currency'    => $currency,
                'description' => 'Order #' . $order_id,
                'email'       => $order_info['email'],
                'ip_address'  => isset($this->request->server['REMOTE_ADDR']) ? $this->request->server['REMOTE_ADDR'] : '',
                // Card fields (Charges API accepts card[...] directly)
                'card[number]'          => $this->request->post['card_number'],
                'card[expiry_month]'    => $this->request->post['expiry_month'],
                'card[expiry_year]'     => $this->request->post['expiry_year'],
                'card[cvc]'             => $this->request->post['cvc'],
                'card[name]'            => trim($order_info['payment_firstname'] . ' ' . $order_info['payment_lastname']),
                'card[address_line1]'   => $order_info['payment_address_1'],
                'card[address_city]'    => $order_info['payment_city'],
                'card[address_state]'   => $order_info['payment_zone'],
                'card[address_postcode]'=> $order_info['payment_postcode'],
                'card[address_country]' => $order_info['payment_country']
            ];

            // Live endpoint. Use the test host for testing:
            // $endpoint = 'https://test-api.pinpayments.com/1/charges';
            $endpoint = 'https://api.pinpayments.com/1/charges';

            $ch = curl_init($endpoint);
            curl_setopt($ch, CURLOPT_USERPWD, $secret_key . ':'); // HTTP Basic auth with secret key
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($charge_data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);

            $response    = curl_exec($ch);
            $curl_errno  = curl_errno($ch);
            $curl_error  = curl_error($ch);
            curl_close($ch);

            // Safe logging only - never log card numbers / CVC.
            if (isset($this->log)) {
                $this->log->write('PinPayments charge attempt - order #' . $order_id . ', amount ' . $amount . ' ' . $currency);
            }

            if ($curl_errno) {
                // Connection-level error (host/network)
                $json = ['error' => 'Connection error: ' . $curl_error];
            } else {
                $result = json_decode($response, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    if (isset($this->log)) {
                        $this->log->write('PinPayments invalid json response for order #' . $order_id);
                    }
                    $json = ['error' => 'Invalid response from payment gateway'];
                } elseif (!empty($result['error'])) {
                    // Top-level gateway error
                    $json = ['error' => isset($result['error_description']) ? $result['error_description'] : 'Payment gateway error'];
                } elseif (!empty($result['response']['success']) && $result['response']['success'] === true) {
                    // SUCCESS - mark the order paid INSIDE the lock so any
                    // queued duplicate requests are blocked by the re-check.
                    $this->model_checkout_order->addOrderHistory($order_id, $paid_status_id);
                    if (isset($this->log)) {
                        $charge_token = isset($result['response']['token']) ? $result['response']['token'] : 'n/a';
                        $this->log->write('PinPayments charge SUCCESS - order #' . $order_id . ', charge ' . $charge_token);
                    }
                    $json = ['success' => $this->url->link('checkout/success')];
                } elseif (!empty($result['response']['redirect_url'])) {
                    // 3D Secure redirect
                    $json = [
                        'redirect' => $result['response']['redirect_url'],
                        'message'  => isset($result['response']['status_message']) ? $result['response']['status_message'] : 'Redirecting for authentication'
                    ];
                } else {
                    // Declined / other failure - no status set, retry allowed.
                    $error_message = $result['response']['error_message'] ?? $result['error_description'] ?? 'Payment failed';
                    $json = ['error' => $error_message];
                }
            }
        }
    } catch (Exception $e) {
        if (isset($this->log)) {
            $this->log->write('PinPayments exception for order #' . $order_id . ': ' . $e->getMessage());
        }
        $json = ['error' => 'Payment processing error. Please try again.'];
    }

    // Always release the lock, on every path.
    $this->db->query("SELECT RELEASE_LOCK('" . $lock_name_e . "')");

    $this->outputJson($json);
}

    private function outputJson($json) {
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }


}
