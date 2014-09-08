<?php
App::uses('Aco', 'Model');

/**
 * Aco Test Case
 *
 */
class AcoTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.aco',
		'app.aro',
		'app.permission'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Aco = ClassRegistry::init('Aco');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Aco);

		parent::tearDown();
	}

}
