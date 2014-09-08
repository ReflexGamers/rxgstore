<?php
App::uses('ServerItem', 'Model');

/**
 * ServerItem Test Case
 *
 */
class ServerItemTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.server_item',
		'app.item',
		'app.stock',
		'app.gift_detail',
		'app.gift',
		'app.sender',
		'app.recipient',
		'app.order',
		'app.user',
		'app.rating',
		'app.review',
		'app.activity',
		'app.paypal_order',
		'app.quick_auth',
		'app.saved_login',
		'app.shoutbox_message',
		'app.user_item',
		'app.gift_order',
		'app.order_detail',
		'app.feature',
		'app.server'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->ServerItem = ClassRegistry::init('ServerItem');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->ServerItem);

		parent::tearDown();
	}

}
