<?php
defined('ABSPATH') || exit;
// Keep the established wxs_ callbacks for backwards compatibility.
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
global $wxs_db_version;
$wxs_db_version = '1.0';
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
		PRIMARY KEY  (id)
	) $charset_collate;";

	dbDelta($sql);
	add_option('wxs_db_version', $wxs_db_version);
}
