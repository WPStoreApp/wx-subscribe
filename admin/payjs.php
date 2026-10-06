<?php
defined('ABSPATH') || exit;

// Table identifiers cannot use value placeholders; every table name below is escaped with esc_sql().
// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
// Keep the established wxs_ callbacks for backwards compatibility.
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

/**
 * Handle the authenticated order cancellation action from the admin screen.
 */
add_action('admin_post_wxs_cancel_order', 'wxs_cancel_order');

function wxs_cancel_order() {
	if (! current_user_can('manage_options')) {
		wp_die(esc_html__('You are not allowed to cancel orders.', 'wx-subscribe'), '', array('response' => 403));
	}

	$order_id_input = isset($_GET['order_id']) && is_scalar($_GET['order_id']) ? sanitize_text_field(wp_unslash($_GET['order_id'])) : '';
	$order_id       = wxs_get_positive_int($order_id_input);
	if (! $order_id) {
		wp_die(esc_html__('Invalid order ID.', 'wx-subscribe'), '', array('response' => 400));
	}

	check_admin_referer('wxs_cancel_order_' . $order_id);

	$config = wxs_get_payjs_config();
	if ('' === $config['merchant_id'] || '' === $config['merchant_key']) {
		wxs_redirect_from_cancel_order('not_configured');
	}

	global $wpdb;
	$table_name = esc_sql(wxs_get_order_table_name());
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- The plugin stores orders in a custom table.
	$order      = $wpdb->get_row(
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- The table identifier is escaped above and cannot use a value placeholder.
		$wpdb->prepare(
			'SELECT id, payjs_no FROM `' . $table_name . '` WHERE id = %d AND status = %s',
			$order_id,
			'UNPAY'
		)
	);

	if (! $order || '' === (string) $order->payjs_no) {
		wxs_redirect_from_cancel_order('not_found');
	}

	$payjs = new Musnow\Payjs\Pay(
		array(
			'MerchantID'  => $config['merchant_id'],
			'MerchantKey' => $config['merchant_key'],
		)
	);
	$result = $payjs->Close(array('PayjsOrderId' => $order->payjs_no));

	if (! is_object($result) || ! isset($result->return_code) || 1 !== (int) $result->return_code) {
		wxs_redirect_from_cancel_order('failed');
	}

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- The plugin stores orders in a custom table.
	$updated = $wpdb->update(
		$table_name,
		array('status' => 'CLOSE'),
		array('id' => $order_id, 'status' => 'UNPAY'),
		array('%s'),
		array('%d', '%s')
	);

	wxs_redirect_from_cancel_order(false === $updated ? 'failed' : 'success');
}

/**
 * Redirect back to the order list without exposing payment-provider details.
 *
 * @param string|false $result Redirect status.
 */
function wxs_redirect_from_cancel_order($result) {
	$args = array('page' => 'wxs-order-list');
	if (false !== $result && '' !== $result) {
		$args['wxs_cancel'] = $result;
	}

	wp_safe_redirect(add_query_arg($args, admin_url('admin.php')));
	exit;
}

/**
 * Handle a PayJS server-to-server notification.
 *
 * The endpoint is intentionally unauthenticated because PayJS cannot send a
 * WordPress nonce. Its authentication is the PayJS signature, which is only
 * accepted when a non-empty merchant key is configured.
 */
add_action('admin_post_nopriv_wxs_notify_order', 'wxs_notify_order');
add_action('admin_post_wxs_notify_order', 'wxs_notify_order');
add_action('init', 'wxs_maybe_handle_notify_order');

/**
 * Preserve the legacy notification URL for installations that already have a
 * rewrite rule or use the query-string form.
 */
function wxs_maybe_handle_notify_order() {
	$request_uri = isset($_SERVER['REQUEST_URI']) && is_scalar($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '';
	$path        = wp_parse_url($request_uri, PHP_URL_PATH);
	$notify_path = wp_parse_url(home_url('/wxs_notify_order'), PHP_URL_PATH);
	$is_legacy   = untrailingslashit((string) $notify_path) === untrailingslashit((string) $path);

	if ($is_legacy) {
		wxs_notify_order();
	}
}

function wxs_notify_order() {
	$request_method = isset($_SERVER['REQUEST_METHOD']) && is_scalar($_SERVER['REQUEST_METHOD']) ? strtoupper(sanitize_text_field(wp_unslash($_SERVER['REQUEST_METHOD']))) : '';
	if ('POST' !== $request_method) {
		wxs_notify_response('Method not allowed.', 405);
	}

	$config = wxs_get_payjs_config();
	if ('' === $config['merchant_id'] || '' === $config['merchant_key']) {
		wxs_notify_response('Payment notifications are not configured.', 503);
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended -- PayJS authenticates this server-to-server webhook with its signature.
	$data = wp_unslash($_POST);
	if (! is_array($data)) {
		wxs_notify_response('Invalid notification.', 400);
	}

	$payjs = new Musnow\Payjs\Pay(
		array(
			'MerchantID'  => $config['merchant_id'],
			'MerchantKey' => $config['merchant_key'],
		)
	);
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- PayJS authenticates this server-to-server webhook with its signature.
	if (! $payjs->Checking($data)) {
		wxs_notify_response('Auth invalid.', 403);
	}

	if (! isset($data['return_code']) || ! is_scalar($data['return_code']) || '1' !== (string) $data['return_code']) {
		wxs_notify_response('Payment was not successful.', 400);
	}

	$order_id = wxs_get_positive_int(isset($data['attach']) ? $data['attach'] : 0);
	if (! $order_id) {
		wxs_notify_response('Invalid order.', 400);
	}

	global $wpdb;
	$table_name = esc_sql(wxs_get_order_table_name());
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- The plugin stores orders in a custom table.
	$order      = $wpdb->get_row(
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- The table identifier is escaped above and cannot use a value placeholder.
		$wpdb->prepare(
			'SELECT id, user_id, order_no, status FROM `' . $table_name . '` WHERE id = %d',
			$order_id
		)
	);

	if (! $order) {
		wxs_notify_response('Order not found.', 404);
	}

	if (! isset($data['out_trade_no']) || ! is_scalar($data['out_trade_no']) || '' === (string) $data['out_trade_no'] || ! hash_equals((string) $order->order_no, (string) $data['out_trade_no'])) {
		wxs_notify_response('Order mismatch.', 400);
	}

	if ('SUCCESS' === $order->status) {
		wxs_notify_response('OK', 200);
	}

	if ('UNPAY' !== $order->status) {
		wxs_notify_response('Order is not payable.', 409);
	}

	$user = get_user_by('id', (int) $order->user_id);
	if (! $user) {
		wxs_notify_response('User not found.', 404);
	}

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- The plugin stores orders in a custom table.
	$updated = $wpdb->update(
		$table_name,
		array(
			'status'  => 'SUCCESS',
			'paid_at' => current_time('mysql'),
		),
		array('id' => $order_id, 'status' => 'UNPAY'),
		array('%s', '%s'),
		array('%d', '%s')
	);

	if (false === $updated) {
		wxs_notify_response('Could not update order.', 500);
	}

	$user->add_role('client');
	wxs_notify_response('OK', 200);
}

/**
 * Send a small webhook response and stop processing.
 *
 * @param string $message Response body.
 * @param int    $status HTTP status code.
 */
function wxs_notify_response($message, $status) {
	status_header($status);
	header('Content-Type: text/plain; charset=utf-8');
	echo esc_html($message);
	exit;
}

function wxs_get_QRCode() {
	if (! is_user_logged_in()) {
		return '';
	}

	$config = wxs_get_payjs_config();
	if ('' === $config['merchant_id'] || '' === $config['merchant_key'] || $config['price'] <= 0) {
		return '';
	}

	$current_user = wp_get_current_user();
	$user_id      = wxs_get_positive_int($current_user->ID);
	if (! $user_id) {
		return '';
	}

	global $wpdb;
	$table_name = esc_sql(wxs_get_order_table_name());
	$cutoff     = get_date_from_gmt(gmdate('Y-m-d H:i:s', time() - (2 * HOUR_IN_SECONDS)));
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- The plugin stores orders in a custom table.
	$order      = $wpdb->get_row(
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- The table identifier is escaped above and cannot use a value placeholder.
		$wpdb->prepare(
			'SELECT id, pay_url FROM `' . $table_name . '` WHERE user_id = %d AND status = %s AND time >= %s ORDER BY id DESC LIMIT 1',
			$user_id,
			'UNPAY',
			$cutoff
		)
	);

	if ($order && '' !== (string) $order->pay_url) {
		return wxs_get_qr_url($order->pay_url);
	}

	$order_no    = wxs_get_new_order();
	$order_title = get_bloginfo('name', 'raw') . '付费订阅 用户名：' . $current_user->display_name;
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- The plugin stores orders in a custom table.
	$inserted    = $wpdb->insert(
		$table_name,
		array(
			'time'     => current_time('mysql'),
			'title'    => $order_title,
			'note'     => '用户订阅',
			'order_no' => $order_no,
			'status'   => 'UNPAY',
			'user_id'  => $user_id,
			'paid_at'  => '',
		),
		array('%s', '%s', '%s', '%s', '%s', '%d', '%s')
	);

	if (false === $inserted) {
		return '';
	}

	$order_id = (int) $wpdb->insert_id;
	$payjs    = new Musnow\Payjs\Pay(
		array(
			'MerchantID'  => $config['merchant_id'],
			'MerchantKey' => $config['merchant_key'],
			'NotifyURL'   => add_query_arg('action', 'wxs_notify_order', admin_url('admin-post.php')),
		)
	);
	$result = $payjs->qrPay(
		array(
			'TotalFee'   => (int) round($config['price'] * 100),
			'outTradeNo' => $order_no,
			'Attach'     => $order_id,
			'Body'       => $order_title,
		)
	);

	if (! is_object($result) || ! isset($result->return_code) || 1 !== (int) $result->return_code || empty($result->code_url) || empty($result->payjs_order_id)) {
		return '';
	}

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- The plugin stores orders in a custom table.
	$updated = $wpdb->update(
		$table_name,
		array(
			'pay_url'  => (string) $result->code_url,
			'payjs_no' => (string) $result->payjs_order_id,
		),
		array('id' => $order_id),
		array('%s', '%s'),
		array('%d')
	);

	return false === $updated ? '' : wxs_get_qr_url($result->code_url);
}

/**
 * Build the external QR image URL using URL encoding for the payment URL.
 *
 * @param string $payment_url PayJS payment URL.
 * @return string
 */
function wxs_get_qr_url($payment_url) {
	if (! is_scalar($payment_url) || '' === (string) $payment_url) {
		return '';
	}

	return esc_url_raw(add_query_arg('url', (string) $payment_url, 'https://mobile.qq.com/qrcode'));
}

// phpcs:enable WordPress.DB.PreparedSQL.NotPrepared
