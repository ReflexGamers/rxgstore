<?php
App::uses('GiftOrder', 'Model');

/**
 * GiftOrder Test Case
 *
 */
class GiftOrderTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.gift_order',
		'app.gift',
		'app.sender',
		'app.recipient',
		'app.order',
		'app.user',
		'app.rating',
		'app.item',
		'app.stock',
		'app.gift_detail',
		'app.feature',
		'app.order_detail',
		'app.server_item',
		'app.server',
		'app.user_item',
		'app.review',
		'app.activity',
		'app.paypal_order',
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
		$this->GiftOrder = ClassRegistry::init('GiftOrder');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->GiftOrder);

		parent::tearDown();
	}

}
