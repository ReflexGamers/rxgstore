<?php
App::uses('Rating', 'Model');

/**
 * Rating Test Case
 *
 */
class RatingTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.rating',
		'app.item',
		'app.stock',
		'app.gift_detail',
		'app.gift',
		'app.sender',
		'app.recipient',
		'app.order',
		'app.user',
		'app.paypal_order',
		'app.activity',
		'app.quick_auth',
		'app.saved_login',
		'app.shoutbox_message',
		'app.user_item',
		'app.gift_order',
		'app.order_detail',
		'app.feature',
		'app.server_item',
		'app.server',
		'app.review'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Rating = ClassRegistry::init('Rating');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Rating);

		parent::tearDown();
	}

/**
 * testGetByItemAndUser method
 *
 * @return void
 */
	public function testGetByItemAndUser() {
		$this->markTestIncomplete('testGetByItemAndUser not implemented.');
	}

}
