<?php

/**
 * 判断是否进行了插件的配置
 * @return boolean
 */
function wxs_assert_plugin_config() {
	$settings = (array) get_option('wxs-settings');
	if (($settings['merchant_id'] ?? '') == '' || ($settings['merchant_key'] ?? '') == '' || ($settings['price'] ?? '') == '') {
		return false;
	} else {
		return true;
	}
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
	return date('YmdHis') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
}