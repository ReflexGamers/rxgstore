<?php
App::uses('Item', 'Model');

/**
 * Item Test Case
 *
 */
class ItemTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
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
		$this->Item = ClassRegistry::init('Item');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Item);

		parent::tearDown();
	}

/**
 * testGetAllIndexed method
 *
 * @return void
 */
	public function testGetAllIndexed() {
		$this->markTestIncomplete('testGetAllIndexed not implemented.');
	}

/**
 * testGetByServer method
 *
 * @return void
 */
	public function testGetByServer() {
		$this->markTestIncomplete('testGetByServer not implemented.');
	}

/**
 * testGetBuyable method
 *
 * @return void
 */
	public function testGetBuyable() {
		$this->markTestIncomplete('testGetBuyable not implemented.');
	}

/**
 * testGetStock method
 *
 * @return void
 */
	public function testGetStock() {
		$this->markTestIncomplete('testGetStock not implemented.');
	}

/**
 * testGetTopBuyers method
 *
 * @return void
 */
	public function testGetTopBuyers() {
		$this->markTestIncomplete('testGetTopBuyers not implemented.');
	}

/**
 * testGetReviews method
 *
 * @return void
 */
	public function testGetReviews() {
		$this->markTestIncomplete('testGetReviews not implemented.');
	}

/**
 * testGetTotalRatings method
 *
 * @return void
 */
	public function testGetTotalRatings() {
		$this->markTestIncomplete('testGetTotalRatings not implemented.');
	}

/**
 * testGetAllTotalRatings method
 *
 * @return void
 */
	public function testGetAllTotalRatings() {
		$this->markTestIncomplete('testGetAllTotalRatings not implemented.');
	}

}
