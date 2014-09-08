<?php
App::uses('OrderDetail', 'Model');

/**
 * OrderDetail Test Case
 *
 */
class OrderDetailTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.order_detail',
		'app.order',
		'app.user',
		'app.rating',
		'app.item',
		'app.stock',
		'app.gift_detail',
		'app.gift',
		'app.sender',
		'app.recipient',
		'app.gift_order',
		'app.feature',
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
		$this->OrderDetail = ClassRegistry::init('OrderDetail');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->OrderDetail);

		parent::tearDown();
	}

}
