<?php
defined('ABSPATH') || exit;
// Keep the established wxs_ callbacks for backwards compatibility.
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

add_action('post_submitbox_misc_actions', 'wxs_createCustomField');
add_action('save_post', 'wxs_saveCustomField');

/** 创建一个checkBox */
function wxs_createCustomField() {
	$post_id = get_the_ID();

	if (get_post_type($post_id) != 'post') {
		return;
	}
	/**
	 * 提取现有的值
	 * @var boolean
	 */
	$value = get_post_meta($post_id, '_subscribe_required', true);
	/**
	 * 添加 nonce 安全处理
	 */
	wp_nonce_field('wxs_subscribe_nonce_' . $post_id, 'wxs_subscribe_nonce');
	?>
    <div class="misc-pub-section misc-pub-section-last">
        <label><input type="checkbox" value="1" <?php checked($value, true, true);?> name="_subscribe_required" />本文订阅后可读</label>
    </div>
    <?php
}
/**
 * 保存配置信息
 * @param  int $post_id 文章的ID
 */
function wxs_saveCustomField($post_id) {
	/**
	 * 自动保存不处理
	 */
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}

	/**
	 * nonce 信息不正确不处理
	 */
	if (
		!isset($_POST['wxs_subscribe_nonce']) ||
		!is_scalar($_POST['wxs_subscribe_nonce']) ||
		!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wxs_subscribe_nonce'])), 'wxs_subscribe_nonce_' . $post_id)
	) {
		return;
	}

	/**
	 * 用户无权编辑文件不处理
	 */
	if (!current_user_can('edit_post', $post_id)) {
		return;
	}
	/**
	 * 存在此项目就更新
	 */
	if (isset($_POST['_subscribe_required'])) {
		update_post_meta($post_id, '_subscribe_required', '1');
	} else {
		/**
		 * 不存在就删除
		 */
		delete_post_meta($post_id, '_subscribe_required');
	}
}
