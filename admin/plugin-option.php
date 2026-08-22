<?php
add_action('admin_init', 'wxs_settings_init');
function wxs_settings_init() {
	register_setting('wxs-plugin-option-page', 'wxs-settings');
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
function we_settings_section_callback() {
	echo "在下方配置您的商户信息后，即可使用该商户信息进行微信收款。";
}
function wxs_merchant_id_render() {
	$options = (array) get_option('wxs-settings');
	if (!isset($options['merchant_id'])) {
		$options['merchant_id'] = '';
	}
	?>
	<input type='text' name='wxs-settings[merchant_id]' value='<?php echo $options['merchant_id']; ?>'>
	<span class="description">这里的参数可以在<a href="https://payjs.cn/ref/MDNXMD" target="_blank">payjs.cn</a>的后台中的「会员中心」查看</span>
	<?php
}
function wxs_merchant_key_render() {
	$options = (array) get_option('wxs-settings');
	if (!isset($options['merchant_key'])) {
		$options['merchant_key'] = '';
	}
	?>
	<input type='password' name='wxs-settings[merchant_key]' value='<?php echo $options['merchant_key']; ?>'>
	<span class="description">这里的参数可以在<a href="https://payjs.cn/ref/MDNXMD" target="_blank">payjs.cn</a>的后台中的「会员中心」查看</span>
	<?php
}
function wxs_price_render() {
	$options = (array) get_option('wxs-settings');
	if (!isset($options['price'])) {
		$options['price'] = '';
	}
	?>
	<input type='number' name='wxs-settings[price]' value='<?php echo $options['price']; ?>'>
	<span class="description">单位为元，比如输入 <code>99</code>，支付时的价格就是99元/年</span>
	<?php
}

function wxs_plugin_options() {
	if (!current_user_can('manage_options')) {
		wp_die(__('您无权修改本页设置'));
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