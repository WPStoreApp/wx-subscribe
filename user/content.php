<?php
defined('ABSPATH') || exit;
// Keep the established wxs_ callbacks for backwards compatibility.
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
function wxs_my_the_content_filter($content) {
	if (get_post_meta(get_the_ID(), '_subscribe_required')) {
		if (wxs_is_user_admin() || wxs_is_user_client()) {
			return $content;
		} else {
		global $wxs_full_article_subscribe_required;
			return $wxs_full_article_subscribe_required;
		}

	} else {
		return $content;
	}

}

add_filter('the_content', 'wxs_my_the_content_filter');
