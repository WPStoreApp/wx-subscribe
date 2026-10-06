<?php
defined('ABSPATH') || exit;
// Keep the established wxs_ callbacks for backwards compatibility.
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

/**
 * 激活函数，创建一个新的角色：订阅会员
 * @return [type] [description]
 */
function wxs_add_roles_on_plugin_activation() {
	add_role('client', esc_html__(
		'Paid subscriber',
		'wx-subscribe'
	),
		array(
			'read' => true,
		)
	);
}
