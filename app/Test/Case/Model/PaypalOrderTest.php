<?php
App::uses('PaypalOrder', 'Model');

/**
 * PaypalOrder Test Case
 *
 */
class PaypalOrderTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.paypal_order',
		'app.user',
		'app.rating',
		'app.item',
		'app.stock',
		'app.gift_detail',
		'app.gift',
		'app.sender',
		'app.recipient',
		'app.order',
		'app.activity',
		'app.gift_order',
		'app.order_detail',
		'app.feature',
		'app.server_item',
		'app.server',
		'app.user_item',
		'app.review',
		'app.quick_auth',
		'app.saved_login',
		'app.shoutbox_message'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->PaypalOrder = ClassRegistry::init('PaypalOrder');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->PaypalOrder);

		parent::tearDown();
	}

}
