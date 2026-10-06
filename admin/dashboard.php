<?php
defined('ABSPATH') || exit;
// Keep the established wxs_ callbacks for backwards compatibility.
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
/**
 * dashboard 返回
 *
 * @return string
 *
 */
function wxs_dashboard_widget_function() {
	if (wxs_is_user_admin() || wxs_is_user_client()) {
		echo "<p style='text-align:center;'><strong>您已成为本站的付费包年用户</strong></p>";
	} else {
		echo "<img src='" . esc_url(wxs_get_QRCode()) . "' alt='支付二维码' style='width:100%'><p style='text-align:center'>支付完成后刷新页面</p>";
	}

}
/**
 * 添加dashboard
 *
 * @return [type] [description]
 */
function wxs_add_dashboard_widgets() {

	if (!wxs_is_user_admin()) {
		wp_add_dashboard_widget(
			'wxs-subscrib-widget',
			'订阅会员',
			'wxs_dashboard_widget_function'
		);
	}

}
add_action('wp_dashboard_setup', 'wxs_add_dashboard_widgets');
