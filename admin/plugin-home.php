<?php
defined('ABSPATH') || exit;
// Keep the established wxs_ callbacks for backwards compatibility.
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

function wxs_plugin_home() {
	if (!current_user_can('manage_options')) {
		wp_die(esc_html__('You are not allowed to view this page.', 'wx-subscribe'));
	}
	?>
<div class="wrap">

	<h1>微信订阅插件</h1>

	<div id="poststuff">

		<div id="post-body" class="metabox-holder columns-2">

			<!-- main content -->
			<div id="post-body-content">

				<div class="meta-box-sortables ui-sortable">
					<div class="postbox">



						<!-- .inside -->

					</div>
					<div class="postbox">

						<h2><span>关于插件</span></h2>

						<div class="inside">
							本插件是基于 <a href="https://payjs.cn/ref/MDNXMD" target="_blank">payjs.cn</a> 实现的 WordPress 付费订阅插件，实现了如下功能。
							<ol>
								<li>全文付费阅读</li>
								<li>文章内容部分付费阅读</li>
								<li>个人微信支付接入</li>
							</ol>
						</div>
						<!-- .inside -->

					</div>

					<div class="postbox">

						<h2><span>常见问题</span></h2>

						<div class="inside">
<strong>开通需要多久才能审核通过？多久才能发起交易？</strong>
<blockquote>
<p>全人工审核，资料完整度会决定审核进度，正规用户10分钟，存疑用户最长1个月被驳回</p>
<p>开通后即可使用</p>
</blockquote>
<strong>是不是违规二清？</strong>
<blockquote>
<p>不是二清。详情请看<a href="https://payjs.cn/help/chang-jian-wen-ti/qi-ta.html" target="_blank">支付与清算</a></p>
</blockquote>
<strong>交易款如何提现？到账时间？</strong>
<blockquote>
<p>微信每天 08:00 前会自动把前一日交易款自动清算至收款方微信钱包</p>
</blockquote>
<strong>交易收手续费吗？</strong>
<blockquote>
<p>交易按笔收取手续费，手续费为交易款的0.38%由<strong>微信支付收取</strong></p>
</blockquote>
<p><strong>重要：</strong>有没有退款接口？</p>
<blockquote>
<p>没有。特别需要退款接口，请到本公司面签授权协议</p>
</blockquote>
<strong>平台支持哪些支付方式？</strong>
<blockquote>
<p>仅支持扫码、收银台（jssdk）两种方式。不支持所有其它方式。不支持小程序</p>
</blockquote>
<strong>什么是扫码支付？</strong>
<blockquote>
<p>根据商品信息生成付款二维码，用户微信扫一扫进行支付</p>
</blockquote>
<strong>什么是收银台模式：</strong>
<blockquote>
<p>收银台模式是为了解决微信环境中点击按钮支付的需求而产生的一种支付方式。技术本质是公众号支付</p>
<p>收银台流程：用户侧根据支付参数发起请求到收银台API接口，收银台直接跳转至支付界面如下图。支付成功后异步通知至用户服务器</p>
						</div>
						<!-- .inside -->

					</div>

				</div>
				<!-- .meta-box-sortables .ui-sortable -->

			</div>
			<!-- post-body-content -->

			<!-- sidebar -->
			<div id="postbox-container-1" class="postbox-container">

				<div class="meta-box-sortables">

					<div class="postbox">

						<h2><span>支持一下</span></h2>

							<div class="inside">感谢您支持 WX Subscribe。</div>
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

</div> <!-- .wrap -->
	<?php
}
