<?php
/*
Plugin Name: 	WX Subscribe
Plugin URI: 	https://www.ixiqin.com/wx-subscribe
Description: 	微信支付订阅插件，用户通过微信支付进行订阅的支付，实现订阅才能查看付费文章的功能。同时，还提供了短代码对部分内容进行隐藏(<code>[subscribe]</code>)
Version: 		1.2.3
Author: 		Bestony
Author URI: 	https://www.ixiqin.com/
License: 		GPL2
License URI:  	https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: 	wx-subscribe

 */
defined('ABSPATH') || exit;
// Keep the established wxs_ callbacks for backwards compatibility.
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

/*  Copyright  2018 Bestony (email : xiqingongzi@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
/**
 * 引入变量的定义
 */
include 'libs/variable.php';
/**
 * 引入自定义函数
 */
include 'libs/utils.php';
/**
 * 引入 PayJS
 */
if (! class_exists('Musnow\\Payjs\\Pay')) {
	include 'libs/payjs.php';
}

/**
 * 引入安装函数：数据库初始化
 */
include 'libs/install.php';
add_action('init', 'wxs_maybe_upgrade', 1);
/**
 * 引入激活函数：角色创建与删除
 */
include 'libs/activation.php';

/**
 * 管理后台提醒设置
 */
include 'admin/notices.php';
/**
 * 后台仪表盘设置
 */
include 'admin/dashboard.php';
/**
 * 文章后台设置
 */
include 'admin/post.php';

/**
 * 插件设置页面
 */
include 'admin/plugin-option.php';
/**
 * 插件订单页面
 */
include 'admin/plugin-order.php';

/**
 * 用户端内容过滤器
 */
include 'user/content.php';
/**
 * 用户端 shortcode && Qtags
 */
include 'admin/shortcode.php';

/**
 * payjs 调用
 */
include 'admin/payjs.php';

/**
 * 用户详情页设置
 */
include 'admin/userprofile.php';
/**
 * 插件首页显示
 */
include 'admin/plugin-home.php';

/**
 * 帮助中心
 */
include 'admin/plugin-helpcenter.php';
/**
 * 菜单部分
 */
add_action('admin_menu', 'wxs_plugin_menu', 10);
function wxs_plugin_menu() {

	/**
	 * 添加后台菜单
	 * @version 1.0.0
	 */
	add_menu_page("微信订阅", "微信订阅", "manage_options", "wxs-plugin-admin", 'wxs_plugin_home', '', 99);

	/**
	 * 创建配置页面，并获取到 Hook
	 * @var hook
	 * @version 1.0.0
	 */
	$hook_suffix = add_submenu_page('wxs-plugin-admin', "微信订阅插件配置", "插件配置", "manage_options", "wxs-options-list", 'wxs_plugin_options');

	/**
	 * 添加页面加载的监听，页面加载完成后，跳转到详情页
	 */
	add_action('load-' . $hook_suffix, 'wxs_hide_admin_notices');
	if (wxs_assert_plugin_config()) {
		add_submenu_page('wxs-plugin-admin', "支付订单管理", "订单管理", "manage_options", 'wxs-order-list', 'wxs_plugin_orders');
		add_submenu_page('wxs-plugin-admin', "帮助中心", "帮助中心", "manage_options", 'wxs-help-center', 'wxs_plugin_helpcenter');
	}

}

/**
 * 初始化数据库
 */
register_activation_hook(__FILE__, 'wxs_install');

/**
 * 添加新的角色
 */
register_activation_hook(__FILE__, 'wxs_add_roles_on_plugin_activation');
/**
 * TODO List
 * @todo  添加 Cron ，一年后自动为用户移除角色
 *
 */
