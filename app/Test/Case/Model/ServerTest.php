<?php
App::uses('Server', 'Model');

/**
 * Server Test Case
 *
 */
class ServerTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.server',
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
		'app.feature'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Server = ClassRegistry::init('Server');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Server);

		parent::tearDown();
	}

/**
 * testGetList method
 *
 * @return void
 */
	public function testGetList() {
		$this->markTestIncomplete('testGetList not implemented.');
	}

}
