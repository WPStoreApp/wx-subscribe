<?php
defined('ABSPATH') || exit;
// Keep the established wxs_ callbacks for backwards compatibility.
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

function wxs_plugin_orders() {
	if (! current_user_can('manage_options')) {
		wp_die(esc_html__('You are not allowed to view orders.', 'wx-subscribe'));
	}

	global $wpdb;
	$table_name = esc_sql(wxs_get_order_table_name());
	// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- The table identifier is escaped above and cannot use a value placeholder.
	$data       = $wpdb->get_results('SELECT id, title, order_no, status, time, note FROM `' . $table_name . '` ORDER BY id DESC');

	echo '<div class="wrap"><h2>' . esc_html__('Orders', 'wx-subscribe') . '</h2>';
	echo '<table class="widefat">';
	echo '<thead><tr>';
	echo '<th class="row-title">' . esc_html__('ID', 'wx-subscribe') . '</th>';
	echo '<th>' . esc_html__('Order title', 'wx-subscribe') . '</th>';
	echo '<th>' . esc_html__('Order number', 'wx-subscribe') . '</th>';
	echo '<th>' . esc_html__('Payment status', 'wx-subscribe') . '</th>';
	echo '<th>' . esc_html__('Created', 'wx-subscribe') . '</th>';
	echo '<th>' . esc_html__('Note', 'wx-subscribe') . '</th>';
	echo '<th>' . esc_html__('Actions', 'wx-subscribe') . '</th>';
	echo '</tr></thead><tbody>';

foreach ((array) $data as $order) {
	$order_id = (int) $order->id;
	$action   = '';
	if ('UNPAY' === $order->status) {
		$url = add_query_arg(
			array(
				'action'   => 'wxs_cancel_order',
				'order_id' => $order_id,
			),
			admin_url('admin-post.php')
		);
		$url    = wp_nonce_url($url, 'wxs_cancel_order_' . $order_id);
		$action = sprintf(
			'<a class="button" href="%1$s">%2$s</a>',
			esc_url($url),
			esc_html__('Cancel order', 'wx-subscribe')
		);
	}

	echo '<tr>';
	echo '<td class="row-title">' . esc_html($order_id) . '</td>';
	echo '<td>' . esc_html($order->title) . '</td>';
	echo '<td>' . esc_html($order->order_no) . '</td>';
	echo '<td>' . esc_html($order->status) . '</td>';
	echo '<td>' . esc_html($order->time) . '</td>';
	echo '<td>' . esc_html($order->note) . '</td>';
	echo '<td>' . wp_kses_post($action) . '</td>';
	echo '</tr>';
}

	echo '</tbody></table></div>';
}
