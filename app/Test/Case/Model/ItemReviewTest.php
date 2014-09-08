<?php
App::uses('Review', 'Model');

/**
 * Review Test Case
 *
 */
class ReviewTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.review',
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
		'app.server'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Review = ClassRegistry::init('Review');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Review);

		parent::tearDown();
	}

/**
 * testGetItemByReviewId method
 *
 * @return void
 */
	public function testGetItemByReviewId() {
		$this->markTestIncomplete('testGetItemByReviewId not implemented.');
	}

}
