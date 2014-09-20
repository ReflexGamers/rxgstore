<?php

/**
 * SteamPlayer Model
 *
 *  @property SteamPlayerCache $SteamPlayerCache
 */
class SteamPlayer extends AppModel {
	public $useDbConfig = 'steam';
	public $hasOne = 'SteamPlayerCache';

	public function getByIds($steamids) {

		$cacheDuration = Configure::read('Store.SteamCacheDuration');
		$cacheExpireTime = date('Y-m-d H:i:s', time() - $cacheDuration);

		$cache = $this->SteamPlayerCache->find('all', array(
			'conditions' => array(
				'cached >' => $cacheExpireTime,
				'AND' => array(
					'steamid' => $steamids
				)
			)
		));

		$countDesired = count($steamids);
		$countFound = count($cache);

		if (!empty($cache) && $countFound == $countDesired) {

			CakeLog::write('steam', "Fetched $countDesired players from the Steam cache.");
			return Hash::extract($cache, '{n}.SteamPlayerCache');

		} else {

			CakeLog::write('steam', "Tried to fetch $countDesired players from the Steam cache but found only $countFound. Steam API used.");
			$steamPlayers = $this->SteamPlayerCache->refresh($steamids);

			//Prune expired records
			$this->SteamPlayerCache->deleteAll(array(
				'cached <' => $cacheExpireTime
			));

			return $steamPlayers;
		}
	}

	public function getBySteamId($steamid) {
		$players = $this->find('all', array(
			'conditions' => array(
				'steamids' => array($steamid)
			)
		))['SteamPlayers'];

		if (empty($players)) return array();

		$player = $players[0];
		$player['name'] = $player['personaname'];

		return $player;
	}

	function schema($field = false) {
        $this->_schema = array(
            'steamid' => array('type' => 'String'),
			'personaname' => array('type' => 'String'),
			'profileurl' => array('type' => 'String'),
			'avatar' => array('type' => 'String'),
            'avatarmedium' => array('type' => 'String'),
            'avatarfull' => array('type' => 'String'),
			'personastate' => array('type' => 'integer'),
            'communityvisibilitystate' => array('type' => 'integer'),
            'profilestate' => array('type' => 'integer'),
            'lastlogoff' => array('type' => 'integer'),
            'commentpermission' => array('type' => 'integer'),
            'realname' => array('type' => 'String'),
            'primaryclanid' => array('type' => 'String'),
            'timecreated' => array('type' => 'integer'),
            'gameid' => array('type' => 'integer'),
            'gameserverip' => array('type' => 'String'),
            'gameextrainfo' => array('type' => 'String'),
            'cityid' => array('type' => 'String'),
            'loccountrycode' => array('type' => 'String'),
            'locstatecode' => array('type' => 'String'),
            'loccityid' => array('type' => 'String'),
        );
        return $this->_schema;
	}
	
}
?>