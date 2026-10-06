<?php
defined('ABSPATH') || exit;
// Keep the established wxs_ callbacks for backwards compatibility.
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

/**
 * @Author: Bestony
 * @Date:   2018-05-01 11:50:48
 * @Last Modified by:   Bestony
 * @Last Modified time: 2018-05-01 11:52:43
 */
function wxs_plugin_helpcenter() {
	if (!current_user_can('manage_options')) {
		wp_die(esc_html__('You are not allowed to view this page.', 'wx-subscribe'));
	}
	?>
	<div class="wrap">

	<h1>微信订阅插件 · 使用帮助中心</h1>
		<div id="poststuff">

		<div id="post-body" class="metabox-holder columns-2">

			<!-- main content -->
			<div id="post-body-content">

				<div class="meta-box-sortables ui-sortable">

					<div class="postbox">

						<h2><span>插件配置说明</span></h2>

						<div class="inside">
							<p>1. 访问 <a href="https://payjs.cn/ref/MDNXMD" target="_blank" rel="noopener noreferrer">Pay.js 官网</a>，进入后台。</p>
							<p>2. 在 Payjs 后台的「会员中心」栏目获取到商户ID和接口通信密钥。</p>
							<p>3. 将配置填写在 WordPress 后台的「插件配置」页面中的对应位置。</p>
							<p>4. 插件完成配置～</p>
						</div>
						<!-- .inside -->

					</div>
					<!-- .postbox -->
					<div class="postbox">

						<h2><span>插件使用说明</span></h2>

						<div class="inside">
							<p>WX Subscribe 目前支持两种不同的内容隐藏模式，分别是「文章全文隐藏」和「文章部分内容隐藏」。</p>
							<h4>文章部分内容隐藏</h4>
							<p>1. 切换为文本模式</p>
							<p>2. 选中要隐藏的内容</p>
							<p>3. 点击「付费阅读」按钮</p>
							<strong>动图展示</strong>
							<h4>文章全文隐藏</h4>
							<p>1. 编写文章内容</p>
							<p>2. 勾选「本文订阅后可读」</p>
							<strong>动图展示</strong>
						</div>
						<!-- .inside -->

					</div>
					<!-- .postbox -->

				</div>
				<!-- .meta-box-sortables .ui-sortable -->

			</div>
			<!-- post-body-content -->

			<!-- sidebar -->
			<div id="postbox-container-1" class="postbox-container">

				<div class="meta-box-sortables">

					<div class="postbox">

						<h2><span>如何联系到我？</span></h2>

						<div class="inside">
							<p>点击下方按钮，既可通过邮件联系到我。如果你希望加入我们的微信交流群，请在邮件中附上您的微信号，我将单独加您，并将您拉到微信群内。</p>
							<p style="font-weight: bold">您有任何问题，都可以直接通过邮件与我取得联系。</p>
							<a target="_blank" rel="noopener noreferrer" href="https://mail.qq.com/cgi-bin/qm_share?t=qm_mailme&amp;email=UjA3ISZ8Jj08KxI0PSo-Mzs_fDE9Pw" style="text-decoration:none;">通过邮件联系作者</a>

						</div>
						<!-- .inside -->

					</div>
					<!-- .postbox -->

				</div>
				<!-- .meta-box-sortables -->

			</div>
			<!-- #postbox-container-1 .postbox-container -->

		</div>
		<!-- #post-body .metabox-holder .columns-2 -->

		<br class="clear">
	</div>
	<!-- #poststuff -->
	</div>
	<?php
}
