<?php
App::uses('QuickAuth', 'Model');

/**
 * QuickAuth Test Case
 *
 */
class QuickAuthTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.quick_auth',
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
		'app.paypal_order',
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
		$this->QuickAuth = ClassRegistry::init('QuickAuth');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->QuickAuth);

		parent::tearDown();
	}

}
