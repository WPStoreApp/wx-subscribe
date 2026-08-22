<?php
/**
 * Tests for WX Subscribe.
 */
class Wx_Subscribe_Test extends WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
		delete_option( 'wxs-settings' );
		wxs_add_roles_on_plugin_activation();
	}

	public function test_config_detection() {
		$this->assertFalse( wxs_assert_plugin_config() );
		update_option(
			'wxs-settings',
			array(
				'merchant_id'  => 'mch',
				'merchant_key' => 'key',
				'price'        => '100',
			)
		);
		$this->assertTrue( wxs_assert_plugin_config() );
	}

	public function test_new_order_number_format() {
		$order = wxs_get_new_order();
		$this->assertMatchesRegularExpression( '/^\d{18}$/', $order );
	}

	public function test_admin_and_client_roles() {
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );
		$this->assertTrue( wxs_is_user_admin() );
		$this->assertFalse( wxs_is_user_client() );

		$client_id = self::factory()->user->create( array( 'role' => 'client' ) );
		wp_set_current_user( $client_id );
		$this->assertFalse( wxs_is_user_admin() );
		$this->assertTrue( wxs_is_user_client() );
	}

	public function test_subscribe_shortcode() {
		global $subscribe_required;
		$this->assertTrue( shortcode_exists( 'subscribe' ) );

		wp_set_current_user( 0 );
		$this->assertSame( $subscribe_required, do_shortcode( '[subscribe]secret[/subscribe]' ) );

		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );
		$this->assertSame( 'secret', do_shortcode( '[subscribe]secret[/subscribe]' ) );

		$client_id = self::factory()->user->create( array( 'role' => 'client' ) );
		wp_set_current_user( $client_id );
		$this->assertSame( 'secret', do_shortcode( '[subscribe]secret[/subscribe]' ) );
	}

	public function test_content_filter_hides_required_posts() {
		global $full_article_subscribe_required;
		$post_id = self::factory()->post->create(
			array(
				'post_content' => 'paid article',
				'post_status'  => 'publish',
			)
		);
		update_post_meta( $post_id, '_subscribe_required', '1' );
		$this->go_to( get_permalink( $post_id ) );
		setup_postdata( get_post( $post_id ) );

		wp_set_current_user( 0 );
		$this->assertSame( $full_article_subscribe_required, wxs_my_the_content_filter( 'paid article' ) );

		$client_id = self::factory()->user->create( array( 'role' => 'client' ) );
		wp_set_current_user( $client_id );
		$this->assertSame( 'paid article', wxs_my_the_content_filter( 'paid article' ) );
	}

	public function test_save_subscribe_required_meta() {
		$user_id = self::factory()->user->create( array( 'role' => 'editor' ) );
		wp_set_current_user( $user_id );
		$post_id = self::factory()->post->create( array( 'post_author' => $user_id ) );

		$_POST['wxs_subscribe_nonce'] = wp_create_nonce( 'wxs_subscribe_nonce_' . $post_id );
		$_POST['_subscribe_required'] = '1';
		wxs_saveCustomField( $post_id );
		$this->assertSame( '1', get_post_meta( $post_id, '_subscribe_required', true ) );

		unset( $_POST['_subscribe_required'] );
		$_POST['wxs_subscribe_nonce'] = wp_create_nonce( 'wxs_subscribe_nonce_' . $post_id );
		wxs_saveCustomField( $post_id );
		$this->assertSame( '', get_post_meta( $post_id, '_subscribe_required', true ) );
	}

	public function test_install_creates_orders_table() {
		global $wpdb;
		wxs_install();
		$table = $wpdb->prefix . 'subscribe_order';
		$this->assertSame( $table, $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) );
	}
}
