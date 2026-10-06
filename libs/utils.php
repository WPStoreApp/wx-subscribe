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

	return array(
		'merchant_id'  => isset($settings['merchant_id']) && is_scalar($settings['merchant_id']) ? trim((string) $settings['merchant_id']) : '',
		'merchant_key' => isset($settings['merchant_key']) && is_scalar($settings['merchant_key']) ? trim((string) $settings['merchant_key']) : '',
		'price'        => isset($settings['price']) && is_scalar($settings['price']) && is_numeric($settings['price']) && is_finite((float) $settings['price']) ? (float) $settings['price'] : 0.0,
	);
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
function wxs_is_user_admin() {
	global $current_user;
	wp_get_current_user();
	/**
	 * 判断用户是否有管理员角色
	 */
	if (in_array("administrator", $current_user->roles)) {
		return true;
	} else {
		return false;
	}
}
/**
 * 判断用户是否是订阅用户
 * @return [type] [description]
 */
function wxs_is_user_client() {
	global $current_user;
	wp_get_current_user();
	/**
	 * 判断用户是否有订阅用户角色
	 */
	if (in_array("client", $current_user->roles)) {
		return true;
	} else {
		return false;
	}
}
/**
 * 订单号生成
 * @return integer 订单号
 */
function wxs_get_new_order() {
	return gmdate('YmdHis') . str_pad(wp_rand(1, 9999), 4, '0', STR_PAD_LEFT);
}
