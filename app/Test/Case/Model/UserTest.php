<?php
App::uses('User', 'Model');

/**
 * User Test Case
 *
 */
class UserTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
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
		$this->User = ClassRegistry::init('User');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->User);

		parent::tearDown();
	}

/**
 * testCanRateItem method
 *
 * @return void
 */
	public function testCanRateItem() {
		$this->markTestIncomplete('testCanRateItem not implemented.');
	}

/**
 * testGetTotalSpent method
 *
 * @return void
 */
	public function testGetTotalSpent() {
		$this->markTestIncomplete('testGetTotalSpent not implemented.');
	}

/**
 * testGetTotalBoughtByItem method
 *
 * @return void
 */
	public function testGetTotalBoughtByItem() {
		$this->markTestIncomplete('testGetTotalBoughtByItem not implemented.');
	}

/**
 * testGetReviews method
 *
 * @return void
 */
	public function testGetReviews() {
		$this->markTestIncomplete('testGetReviews not implemented.');
	}

}
