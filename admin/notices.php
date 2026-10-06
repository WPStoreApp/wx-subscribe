<?php
defined('ABSPATH') || exit;
// Keep the established wxs_ callbacks for backwards compatibility.
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
/**
 * 初始化显示
 */
add_action('admin_notices', 'wxs_admin_notices');

function wxs_hide_admin_notices() {
	/**
	 * 移除系统提醒
	 */
	remove_action('admin_notices', 'wxs_admin_notices');
}
/**
 * 如果用户没有设置，就提醒他设置
 * @return  string
 */
function wxs_admin_notices() {
	global $wxs_merchant_need_set;

	if (wxs_assert_plugin_config()) {
		// 用户已经设置，不做任何输出
	} else {
		/**
		 * @todo 判断用户是否已经进行了配置，如果没有配置就加入配置
		 */
		echo wp_kses_post($wxs_merchant_need_set);
	}

}
