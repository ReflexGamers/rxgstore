<?php
App::uses('GiftDetail', 'Model');

/**
 * GiftDetail Test Case
 *
 */
class GiftDetailTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.gift_detail',
		'app.gift',
		'app.sender',
		'app.recipient',
		'app.order',
		'app.user',
		'app.rating',
		'app.item',
		'app.stock',
		'app.feature',
		'app.order_detail',
		'app.server_item',
		'app.server',
		'app.user_item',
		'app.review',
		'app.activity',
		'app.paypal_order',
		'app.quick_auth',
		'app.saved_login',
		'app.shoutbox_message',
		'app.gift_order'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->GiftDetail = ClassRegistry::init('GiftDetail');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->GiftDetail);

		parent::tearDown();
	}

}
