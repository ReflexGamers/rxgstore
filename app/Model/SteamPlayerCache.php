<?php
App::uses('AppModel', 'Model');

/**
 * SteamPlayerCache Model
 *
 * This model controls the Steam player caching system which stores the results of Steam API calls for later use.
 * If a player's data was cached more than Store.SteamCacheDuration seconds ago, that player is considered 'expired'.
 * Players that have not expired are considered 'valid'.
 *
 * @property SteamPlayer $SteamPlayer
 *
 * Magic Methods (for inspection):
 * @method findBySteamid
 */
class SteamPlayerCache extends AppModel {

    public $useTable = 'steam_player_cache';
    public $primaryKey = 'steamid';

    public $belongsTo = 'SteamPlayer';

    public $order = 'SteamPlayerCache.cached desc';


    protected $expireTime = null;

    /**
     * Returns cached player data for all the provided steamids that have not expired.
     *
     * @param array $steamids list of 64-bit steamids
     * @return array of cached player data
     */
    public function getValidPlayers($steamids) {

        return $this->find('all', array(
            'conditions' => array(
                'cached >' => $this->getExpireTime(),
                'AND' => array(
                    'steamid' => $steamids
                )
            )
        ));
    }

    /**
     * Calls the Steam API for the provided list of steamids and saves the results to the cache before returning them.
     * If more than 100 steamids are provided, this will make multiple API calls back-to-back with 100 steamids each.
     *
     * @param array $steamids list of 64-bit steamids to refresh
     * @return array the result of the Steam API call
     */
    public function refreshPlayers($steamids) {

        $cachedTime = $this->formatTimestamp(time());

        $i = 0;
        $steamPlayers = array();

        // Steam API has 100 player per call limit
        while ($batch = array_slice($steamids, $i++ * 100, 100)) {
            $steamPlayers = array_merge(
                $steamPlayers,
                $this->SteamPlayer->find('all', array(
                    'conditions' => array(
                        'steamids' => $batch
                    )
                ))['SteamPlayers']
            );
        }

        $saveToCache = Hash::map($steamPlayers, '{n}', function($player) use ($cachedTime){
            return array(
                'steamid' => $player['steamid'],
                'personaname' => $player['personaname'],
                'profileurl' => $player['profileurl'],
                'avatar' => $player['avatar'],
                'avatarmedium' => $player['avatarmedium'],
                'avatarfull' => $player['avatarfull'],
                'cached' => $cachedTime
            );
        });

        $this->saveMany($saveToCache, array('atomic' => false));
        return $steamPlayers;
    }

    /**
     * Truncates the cache table.
     */
    public function clearAll() {
        $this->query('truncate steam_player_cache');
    }

    /**
     * Refreshes all steamids in the cache. Use sparingly.
     */
    public function refreshAll() {

        $steamids = Hash::extract($this->find('all', array('fields' => 'steamid')), '{n}.SteamPlayerCache.steamid');
        $this->refreshPlayers($steamids);
    }

    /**
     * Returns the time to compare cached players against for determining which are expired. This time is basically
     * Store.SteamCacheDuration seconds ago, so if the time a player was cached is less than this, that player is
     * expired.
     *
     * @return int the time against which to compare players' cached time
     */
    public function getExpireTime() {

        if (empty($this->expireTime)) {
            $this->expireTime = $this->formatTimestamp(time() - Configure::read('Store.SteamCacheDuration'));
        }

        return $this->expireTime;
    }

    /**
     * Refreshes all expired players in the cache.
     */
    public function refreshExpiredPlayers() {

        $steamids = Hash::extract($this->find('all', array(
            'fields' => 'steamid',
            'conditions' => array(
                'cached <' => $this->getExpireTime()
            )
        )), '{n}.SteamPlayerCache.steamid');

        $this->refreshPlayers($steamids);
    }

    /**
     * Deletes all expired players from the cache.
     */
    public function pruneExpiredPlayers() {

        $this->deleteAll(array(
            'cached <' => $this->getExpireTime()
        ), false);
    }

    /**
     * Returns the number of valid players in the cache. Valid players are not expired.
     *
     * @return int the number of valid players in the cache
     */
    public function countValidPlayers() {

        return $this->find('count', array(
            'conditions' => array(
                'cached >=' => $this->getExpireTime()
            )
        ));
    }

    /**
     * Returns the number of expired players in the cache.
     *
     * @return int the number of expired players in the cache
     */
    public function countExpiredPlayers() {

        return $this->find('count', array(
            'conditions' => array(
                'cached <' => $this->getExpireTime()
            )
        ));
    }

    /**
     * Formats the provided time (or current time by default) into a timestamp that MySQL understands.
     *
     * @param int $time optional time (defaults to current time)
     * @return string the formatted date
     */
    private function formatTimestamp($time) {
        return date('Y-m-d H:i:s', !empty($time) ? $time : time());
    }


/**
 * Validation rules
 *
 * @var array
 */
    public $validate = array(
        'personaname' => array(
            'notEmpty' => array(
                'rule' => array('notEmpty'),
                //'message' => 'Your custom message here',
                //'allowEmpty' => false,
                //'required' => false,
                //'last' => false, // Stop validation after this rule
                //'on' => 'create', // Limit validation to 'create' or 'update' operations
            ),
        ),
        'profileurl' => array(
            'notEmpty' => array(
                'rule' => array('notEmpty'),
                //'message' => 'Your custom message here',
                //'allowEmpty' => false,
                //'required' => false,
                //'last' => false, // Stop validation after this rule
                //'on' => 'create', // Limit validation to 'create' or 'update' operations
            ),
        ),
        'avatar' => array(
            'notEmpty' => array(
                'rule' => array('notEmpty'),
                //'message' => 'Your custom message here',
                //'allowEmpty' => false,
                //'required' => false,
                //'last' => false, // Stop validation after this rule
                //'on' => 'create', // Limit validation to 'create' or 'update' operations
            ),
        ),
        'avatarmedium' => array(
            'notEmpty' => array(
                'rule' => array('notEmpty'),
                //'message' => 'Your custom message here',
                //'allowEmpty' => false,
                //'required' => false,
                //'last' => false, // Stop validation after this rule
                //'on' => 'create', // Limit validation to 'create' or 'update' operations
            ),
        ),
        'avatarfull' => array(
            'notEmpty' => array(
                'rule' => array('notEmpty'),
                //'message' => 'Your custom message here',
                //'allowEmpty' => false,
                //'required' => false,
                //'last' => false, // Stop validation after this rule
                //'on' => 'create', // Limit validation to 'create' or 'update' operations
            ),
        ),
        'cached' => array(
            'notEmpty' => array(
                'rule' => array('notEmpty'),
                //'message' => 'Your custom message here',
                //'allowEmpty' => false,
                //'required' => false,
                //'last' => false, // Stop validation after this rule
                //'on' => 'create', // Limit validation to 'create' or 'update' operations
            ),
        ),
    );
}
