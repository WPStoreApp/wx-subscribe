<?php
defined('ABSPATH') || exit;
// Keep the established wxs_ callbacks for backwards compatibility.
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

/**
 * 激活函数，创建一个新的角色：订阅会员
 * @return [type] [description]
 */
function wxs_add_roles_on_plugin_activation() {
	// Keep the legacy role for existing integrations, but never assign it to
	// new users because another plugin may have added extra capabilities to it.
	if (! get_role('client')) {
		add_role(
			'client',
			__('Paid subscriber', 'wx-subscribe'),
			array('read' => true)
		);
	}

	wxs_ensure_client_role();
}
