<?php
defined('ABSPATH') || exit;
// Keep the established wxs_ callbacks for backwards compatibility.
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

/**
 * 判断是否进行了插件的配置
 * @return boolean
 */
function wxs_assert_plugin_config() {
	$config = wxs_get_payjs_config();

	return '' !== $config['merchant_id'] && '' !== $config['merchant_key'] && $config['price'] > 0;
}

/**
 * Return the sanitized PayJS settings used by all payment handlers.
 *
 * @return array{merchant_id:string,merchant_key:string,price:float}
 */
function wxs_get_payjs_config() {
	$settings = get_option('wxs-settings');
	$settings = is_array($settings) ? $settings : array();
	$price    = isset($settings['price']) && is_scalar($settings['price']) && is_numeric($settings['price']) ? (float) $settings['price'] : 0.0;

	if (! is_finite($price) || $price < 0) {
		$price = 0.0;
	}

	$price = round($price, 2);

	return array(
		'merchant_id'  => isset($settings['merchant_id']) && is_scalar($settings['merchant_id']) ? trim((string) $settings['merchant_id']) : '',
		'merchant_key' => isset($settings['merchant_key']) && is_scalar($settings['merchant_key']) ? trim((string) $settings['merchant_key']) : '',
		'price'        => $price,
	);
}

/**
 * Convert a configured price in yuan to PayJS cents.
 *
 * @param mixed $price Price in yuan.
 * @return int
 */
function wxs_get_total_fee($price) {
	if (! is_scalar($price) || ! is_numeric($price)) {
		return 0;
	}

	$price = (float) $price;
	if (! is_finite($price) || $price <= 0) {
		return 0;
	}

	$total_fee = round($price * 100);
	if ($total_fee < 1 || $total_fee > PHP_INT_MAX) {
		return 0;
	}

	return (int) $total_fee;
}

/**
 * Return the plugin order table name.
 *
 * The table prefix is controlled by WordPress configuration, not request data.
 *
 * @return string
 */
function wxs_get_order_table_name() {
	global $wpdb;

	return $wpdb->prefix . 'subscribe_order';
}

/**
 * Convert an external value to a strictly positive integer.
 *
 * @param mixed $value External value.
 * @return int
 */
function wxs_get_positive_int($value) {
	if (! is_scalar($value)) {
		return 0;
	}

	$value = (string) $value;
	if (! preg_match('/^[1-9][0-9]*$/', $value)) {
		return 0;
	}

	$integer = filter_var($value, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)));

	return false === $integer ? 0 : (int) $integer;
}
/**
 * 判断用户是否是管理员
 * @return boolean true: 用户是管理员, false: 用户不是管理员
 */
function wxs_get_user($user = null) {
	if ($user instanceof WP_User) {
		return $user;
	}

	if (is_scalar($user) && (int) $user > 0) {
		$resolved_user = get_user_by('id', (int) $user);
		if ($resolved_user instanceof WP_User) {
			return $resolved_user;
		}
	}

	$current_user = wp_get_current_user();

	return $current_user instanceof WP_User ? $current_user : null;
}

/**
 * 判断用户是否有管理权限。
 *
 * @param WP_User|int|null $user User to inspect. Defaults to the current user.
 * @return bool
 */
function wxs_is_user_admin($user = null) {
	$user = wxs_get_user($user);

	return $user instanceof WP_User && $user->exists() && $user->has_cap('manage_options');
}

/**
 * Return the role used for new paid subscribers.
 *
 * The prefixed role avoids assigning a pre-existing generic `client` role
 * whose capabilities may have been changed by another plugin.
 *
 * @return string
 */
function wxs_get_client_role_slug() {
	return 'wxs_client';
}

/**
 * Ensure the role used for new paid subscribers exists with read access only.
 *
 * @return bool
 */
function wxs_ensure_client_role() {
	$role_slug = wxs_get_client_role_slug();
	if (! get_role($role_slug)) {
		add_role(
			$role_slug,
			__('Paid subscriber', 'wx-subscribe'),
			array('read' => true)
		);
	}

	$role = get_role($role_slug);
	if (! $role) {
		return false;
	}

	return empty(array_diff(array_keys((array) $role->capabilities), array('read')));
}

/**
 * Grant the prefixed paid subscriber role to a user.
 *
 * @param WP_User $user User to update.
 * @return bool
 */
function wxs_grant_client_role($user) {
	if (! $user instanceof WP_User || ! $user->exists() || ! wxs_ensure_client_role()) {
		return false;
	}

	$user->add_role(wxs_get_client_role_slug());

	return true;
}

/**
 * 判断用户是否是订阅用户
 * @return [type] [description]
 */
function wxs_is_user_client($user = null) {
	$user = wxs_get_user($user);
	if (! $user instanceof WP_User || ! $user->exists()) {
		return false;
	}

	return in_array(wxs_get_client_role_slug(), (array) $user->roles, true) || in_array('client', (array) $user->roles, true);
}
/**
 * 订单号生成
 * @return integer 订单号
 */
function wxs_get_new_order() {
	return gmdate('YmdHis') . str_pad(wp_rand(1, 9999), 4, '0', STR_PAD_LEFT);
}
