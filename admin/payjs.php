<?php

add_action('init', 'wxs_cancel_order');
function wxs_cancel_order() {
	if (preg_match('/wxs_cancel_order/', $_SERVER["REQUEST_URI"])) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'subscribe_order';
		$id = $_GET['id'];

		$payjsInstance = new Musnow\Payjs\Pay([
			"MerchantID" => $config['merchant_id'],
			"MerchantKey" => $config['merchant_key'],
		]);
		$sql = 'SELECT id,payjs_no FROM `' . $table_name . '` WHERE `id`=' . $id;
		$res = $wpdb->get_results($sql);
		$cancelResult = $payjsInstance->Close([
			"PayjsOrderId" => $res[0]->payjs_no,
		]);
		dd($cancelResult);
		exit;

		if ($cancelResult->return_code == 1) {
			$wpdb->update($table_name, ['status' => 'CLOSE'], ['id' => $id]);
		}
		wp_redirect('/wp-admin/admin.php?page=wxs-order-list');exit;
	}
}

add_action('init', 'wxs_notify_order');
function wxs_notify_order() {

	$config = (array) get_option('wxs-settings');
	if ($_SERVER["REQUEST_URI"] == '/wxs_notify_order') {
		$data = $_POST;

		$payjsInstance = new Musnow\Payjs\Pay([
			"MerchantID" => $config['merchant_id'],
			"MerchantKey" => $config['merchant_key'],
		]);
		$res = $payjsInstance->Checking($data);
		if ($res) {
			$id = $data['attach'];
			global $wpdb;
			$table_name = $wpdb->prefix . 'subscribe_order';
			$wpdb->update($table_name, array(
				'status' => 'SUCCESS',
				'paid_at' => current_time('mysql'),
			), ['id' => $id]);
			$res = $wpdb->get_results("SELECT user_id FROM `" . $table_name . "` where `id`=" . $id)[0];
			$user = new WP_USER($res->user_id);
			$user->add_role('client');
			echo "OK";
			exit;
		} else {
			echo "Auth invalid";
			exit;
		}

	}
}

function wxs_get_QRCode() {
	/**
	 * 在线生成二维码的路径，后续可以改为本地生成
	 * @var string
	 */
	$base_url = "http://mobile.qq.com/qrcode?url=";

	global $wpdb;
	global $current_user;

	wp_get_current_user();

	$config = (array) get_option('wxs-settings');
	$order_no = wxs_get_new_order();
	$table_name = $wpdb->prefix . 'subscribe_order';
	$sql = 'select id,pay_url from `' . $table_name . '` where `user_id`=' . $current_user->ID . " AND `status` = 'UNPAY' AND `time` >= '" . date('Y/m/d H:i:s', strtotime('-2 hour')) . "'";
	$result = $wpdb->get_results($sql);

	if (count($result) != 0) {
		return $base_url . $result[0]->pay_url;
		exit;
	}

	$payjsInstance = new Musnow\Payjs\Pay([
		"MerchantID" => $config['merchant_id'],
		"MerchantKey" => $config['merchant_key'],
		"NotifyURL" => get_site_url() . "/wxs_notify_order",
	]);
	$order_title = get_bloginfo('name', 'raw') . "付费订阅 用户名：" . $current_user->display_name;

	$wpdb->insert(
		$table_name,
		array(
			'time' => current_time('mysql'),
			'title' => $order_title,
			'note' => "用户订阅",
			"order_no" => $order_no,
			"status" => "UNPAY",
			"user_id" => $current_user->ID,
			'paid_at' => '',
		)
	);
	$order_id = $wpdb->insert_id;
	$res = $payjsInstance->qrPay(array(
		"TotalFee" => $config['price'] * 100,
		"outTradeNo" => $order_no,
		"Attach" => $order_id,
		"Body" => $order_title,
	));

	if ($res->return_code == 1) {
		$code_url = $res->code_url;
		$wpdb->update($table_name, [
			"pay_url" => $code_url,
			"payjs_no" => $res->payjs_order_id,
		],
			["id" => $order_id]);
		return $base_url . $code_url;
		exit;
	}
}