<?php
App::uses('SteamPlayerCache', 'Model');

/**
 * SteamPlayerCache Test Case
 *
 */
class SteamPlayerCacheTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.steam_player_cache'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->SteamPlayerCache = ClassRegistry::init('SteamPlayerCache');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->SteamPlayerCache);

		parent::tearDown();
	}

}
