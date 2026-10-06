<?php
defined('ABSPATH') || exit;
// Keep the established wxs_ callbacks for backwards compatibility.
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
global $wxs_db_version;
$wxs_db_version = '1.1';
/**
 * 创建数据库
 * @return [type] [description]
 */
function wxs_install() {
	global $wpdb;
	global $wxs_db_version;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	$table_name = $wpdb->prefix . 'subscribe_order';

	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		user_id mediumint(9) NOT NULL,
		title tinytext NOT NULL,
		note text NULL,
		order_no varchar(55) DEFAULT '' NOT NULL,
		status varchar(20) NOT NULL,
		pay_url tinytext NULL,
		paid_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		payjs_no varchar(100) DEFAULT '' NOT NULL,
		total_fee bigint(20) unsigned NOT NULL DEFAULT 0,
		PRIMARY KEY  (id)
	) $charset_collate;";

	dbDelta($sql);
	update_option('wxs_db_version', $wxs_db_version);
}

/**
 * Upgrade the custom order table without requiring plugin reactivation.
 */
function wxs_maybe_upgrade() {
	global $wxs_db_version;
	$current_version = get_option('wxs_db_version', '0.0');

	if (version_compare((string) $current_version, $wxs_db_version, '<')) {
		wxs_install();
	}
}
