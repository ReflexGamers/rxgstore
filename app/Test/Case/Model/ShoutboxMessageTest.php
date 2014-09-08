<?php
App::uses('ShoutboxMessage', 'Model');

/**
 * ShoutboxMessage Test Case
 *
 */
class ShoutboxMessageTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.shoutbox_message',
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
		'app.quick_auth',
		'app.saved_login'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->ShoutboxMessage = ClassRegistry::init('ShoutboxMessage');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->ShoutboxMessage);

		parent::tearDown();
	}

/**
 * testGetRecent method
 *
 * @return void
 */
	public function testGetRecent() {
		$this->markTestIncomplete('testGetRecent not implemented.');
	}

/**
 * testCanUserPost method
 *
 * @return void
 */
	public function testCanUserPost() {
		$this->markTestIncomplete('testCanUserPost not implemented.');
	}

}
