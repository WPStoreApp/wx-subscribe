<?php
defined('ABSPATH') || exit;
// Keep the established wxs_ callbacks for backwards compatibility.
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
add_action('admin_init', 'wxs_settings_init');
function wxs_settings_init() {
	register_setting(
		'wxs-plugin-option-page',
		'wxs-settings',
		array(
			'sanitize_callback' => 'wxs_sanitize_settings',
		)
	);
	add_settings_section(
		'wxs-plugin-option-page_section',
		"PayJS 商户配置",
		'we_settings_section_callback',
		'wxs-plugin-option-page'
	);
	add_settings_field(
		'wxs_merchant_id_form',
		"商户号",
		'wxs_merchant_id_render',
		'wxs-plugin-option-page',
		'wxs-plugin-option-page_section'
	);
	add_settings_field(
		'wxs_merchant_key_form',
		"接口通信密钥",
		'wxs_merchant_key_render',
		'wxs-plugin-option-page',
		'wxs-plugin-option-page_section'
	);
	add_settings_field(
		'wxs_subscribe_price_form',
		"订阅价格（年）",
		'wxs_price_render',
		'wxs-plugin-option-page',
		'wxs-plugin-option-page_section'
	);
}

/**
 * Sanitize the PayJS settings before they are persisted.
 *
 * @param mixed $input Submitted settings.
 * @return array<string,string>
 */
function wxs_sanitize_settings($input) {
	$input = is_array($input) ? $input : array();
	$price = isset($input['price']) && is_scalar($input['price']) && is_numeric($input['price']) ? (float) $input['price'] : 0;

	if ($price < 0 || ! is_finite($price)) {
		$price = 0;
	}

	return array(
		'merchant_id'  => isset($input['merchant_id']) && is_scalar($input['merchant_id']) ? sanitize_text_field(wp_unslash($input['merchant_id'])) : '',
		'merchant_key' => isset($input['merchant_key']) && is_scalar($input['merchant_key']) ? sanitize_text_field(wp_unslash($input['merchant_key'])) : '',
		'price'        => (string) $price,
	);
}
function we_settings_section_callback() {
	echo "在下方配置您的商户信息后，即可使用该商户信息进行微信收款。";
}
function wxs_merchant_id_render() {
	$options = (array) get_option('wxs-settings');
	if (!isset($options['merchant_id'])) {
		$options['merchant_id'] = '';
	}
	?>
	<input type='text' name='wxs-settings[merchant_id]' value='<?php echo esc_attr($options['merchant_id']); ?>'>
	<span class="description">这里的参数可以在<a href="https://payjs.cn/ref/MDNXMD" target="_blank">payjs.cn</a>的后台中的「会员中心」查看</span>
	<?php
}
function wxs_merchant_key_render() {
	$options = (array) get_option('wxs-settings');
	if (!isset($options['merchant_key'])) {
		$options['merchant_key'] = '';
	}
	?>
	<input type='password' name='wxs-settings[merchant_key]' value='<?php echo esc_attr($options['merchant_key']); ?>'>
	<span class="description">这里的参数可以在<a href="https://payjs.cn/ref/MDNXMD" target="_blank">payjs.cn</a>的后台中的「会员中心」查看</span>
	<?php
}
function wxs_price_render() {
	$options = (array) get_option('wxs-settings');
	if (!isset($options['price'])) {
		$options['price'] = '';
	}
	?>
	<input type='number' min='0' step='0.01' name='wxs-settings[price]' value='<?php echo esc_attr($options['price']); ?>'>
	<span class="description">单位为元，比如输入 <code>99</code>，支付时的价格就是99元/年</span>
	<?php
}

function wxs_plugin_options() {
	if (!current_user_can('manage_options')) {
		wp_die(esc_html__('You are not allowed to edit these settings.', 'wx-subscribe'));
	}
	/**
	 * 输出页面内容
	 */
	echo '<div class="wrap"><h2>插件配置</h2>';
	echo "<form action='options.php' method='post'>";
	settings_fields('wxs-plugin-option-page');
	do_settings_sections('wxs-plugin-option-page');
	submit_button();
	echo '</form>';
	echo '</div>';

}
