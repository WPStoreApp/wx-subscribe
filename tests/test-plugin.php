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

	public function test_payment_amount_is_converted_to_cents_safely() {
		$this->assertSame( 999, wxs_get_total_fee( '9.99' ) );
		$this->assertSame( 0, wxs_get_total_fee( '0.001' ) );
		$this->assertSame( 0, wxs_get_total_fee( '0' ) );
		$this->assertSame( 0, wxs_get_total_fee( 'not-a-price' ) );
		$this->assertSame( 0, wxs_get_total_fee( array( 100 ) ) );
	}

	public function test_new_order_number_format() {
		$order = wxs_get_new_order();
		$this->assertMatchesRegularExpression( '/^\d{18}$/', $order );
	}

	public function test_external_ids_are_strict_positive_integers() {
		$this->assertSame( 1, wxs_get_positive_int( '1' ) );
		$this->assertSame( 1, wxs_get_positive_int( 1 ) );
		$this->assertSame( 0, wxs_get_positive_int( '1 AND SLEEP(10)' ) );
		$this->assertSame( 0, wxs_get_positive_int( '1e2' ) );
		$this->assertSame( 0, wxs_get_positive_int( array( 1 ) ) );
	}

	public function test_payjs_notifications_require_a_valid_signature_and_key() {
		$payload = array(
			'attach'       => '12',
			'out_trade_no' => '202401010000000001',
			'return_code'  => '1',
		);
		$payload['sign'] = strtoupper( md5( urldecode( http_build_query( $payload ) ) . '&key=test-key' ) );

		$payjs = new Musnow\Payjs\Pay( array( 'MerchantKey' => 'test-key' ) );
		$this->assertTrue( $payjs->Checking( $payload ) );

		$payload['empty_field'] = '';
		$sign_data              = array_filter( $payload, static function ( $value ) {
			return '' !== (string) $value;
		} );
		unset( $sign_data['sign'] );
		ksort( $sign_data );
		$payload['sign'] = strtoupper( md5( urldecode( http_build_query( $sign_data ) ) . '&key=test-key' ) );
		$this->assertTrue( $payjs->Checking( $payload ) );

		$payload['sign'] = str_repeat( '0', 32 );
		$this->assertFalse( $payjs->Checking( $payload ) );

		$without_key = new Musnow\Payjs\Pay( array( 'MerchantKey' => '' ) );
		$this->assertFalse( $without_key->Checking( $payload ) );
		$this->assertFalse( $payjs->Checking( array( 'attach' => array( 12 ), 'sign' => 'invalid' ) ) );
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

	public function test_new_paid_users_do_not_receive_a_generic_role() {
		$legacy_role = get_role( 'client' );
		$legacy_role->add_cap( 'manage_options' );

		$user_id = self::factory()->user->create();
		$user    = get_user_by( 'id', $user_id );

		$this->assertTrue( wxs_grant_client_role( $user ) );
		$this->assertContains( 'wxs_client', $user->roles );
		$this->assertNotContains( 'client', $user->roles );
		$this->assertFalse( $user->has_cap( 'manage_options' ) );

		$legacy_role->remove_cap( 'manage_options' );
	}

	public function test_qr_code_cannot_be_generated_for_another_user() {
		$current_user_id = self::factory()->user->create();
		$other_user_id   = self::factory()->user->create();
		wp_set_current_user( $current_user_id );

		$this->assertSame( '', wxs_get_QRCode( $other_user_id ) );
	}

	public function test_subscribe_shortcode() {
		global $wxs_subscribe_required;
		$this->assertTrue( shortcode_exists( 'subscribe' ) );

		wp_set_current_user( 0 );
		$this->assertSame( $wxs_subscribe_required, do_shortcode( '[subscribe]secret[/subscribe]' ) );

		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );
		$this->assertSame( 'secret', do_shortcode( '[subscribe]secret[/subscribe]' ) );

		$client_id = self::factory()->user->create( array( 'role' => 'client' ) );
		wp_set_current_user( $client_id );
		$this->assertSame( 'secret', do_shortcode( '[subscribe]secret[/subscribe]' ) );
	}

	public function test_content_filter_hides_required_posts() {
		global $wxs_full_article_subscribe_required;
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
		$this->assertSame( $wxs_full_article_subscribe_required, wxs_my_the_content_filter( 'paid article' ) );

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

	public function test_install_records_db_version() {
		wxs_install();
		$this->assertSame( '1.1', get_option( 'wxs_db_version' ) );
	}
}
