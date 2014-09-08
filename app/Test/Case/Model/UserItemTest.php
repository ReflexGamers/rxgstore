<?php
App::uses('UserItem', 'Model');

/**
 * UserItem Test Case
 *
 */
class UserItemTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.user_item',
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
		'app.gift_order',
		'app.order_detail',
		'app.feature',
		'app.server_item',
		'app.server'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->UserItem = ClassRegistry::init('UserItem');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->UserItem);

		parent::tearDown();
	}

/**
 * testGetByUser method
 *
 * @return void
 */
	public function testGetByUser() {
		$this->markTestIncomplete('testGetByUser not implemented.');
	}

}
