<?php
App::uses('AppModel', 'Model');

/**
 * SteamPlayerCache Model
 *
 * This model controls the Steam player caching system which stores the results of Steam API calls for later use.
 * If a player's data was cached more than Store.SteamCache.Duration seconds ago, that player is considered 'expired'.
 * Players that have not expired are considered 'valid'.
 *
 * @property SteamPlayer $SteamPlayer
 * @property User $User
 *
 * Magic Methods (for inspection):
 * @method findBySteamid
 */
class SteamPlayerCache extends AppModel {

    public $useTable = 'steam_player_cache';
    public $primaryKey = 'steamid';

    public $belongsTo = array(
        'SteamPlayer', 'User'
    );

    public $order = 'SteamPlayerCache.cached desc';


    protected $expireTime = null;

    /**
     * Returns a query that can be used to fetch a page of search results for a specific term.
     *
     * Note: This does not return data; it simply returns the query as an array.
     *
     * @param string $term search term
     * @param int $limit optional limit for number of reviews to return
     * @return array query to be passed into paginator
     */
    public function getSearchQueryPage($term = '', $limit = 10) {

        return array(
            'SteamPlayerCache' => array(
                'fields' => array(
                    'user_id'
                ),
                'conditions' => array(
                    'personaname like' => "%$term%"
                ),
                'limit' => $limit
            )
        );
    }

    /**
     * Returns cached player data for all the provided steamids even if they have expired since it is better to display
     * slightly older data that could still be correct than to wait forever on the Steam API.
     *
     * @param array $accounts list of signed 32-bit steamids (user_id)
     * @return array of cached player data
     */
    public function getPlayers($accounts) {

        return $this->find('all', array(
            'conditions' => array(
                'user_id' => $accounts
            )
        ));
    }

    /**
     * Calls the Steam API for the provided list of steamids and saves the results to the cache before returning them.
     * If more than 100 steamids are provided, this will make multiple API calls back-to-back with 100 steamids each.
     *
     * @param array $accounts list of signed 32-bit steamids (user_id) to refresh
     * @param bool $precache whether to mark the players as pre-cached
     * @param int $timeout optional number of seconds for which to limit the request
     * @return array the result that was saved to the cache
     */
    public function refreshPlayers($accounts, $precache = false, $timeout = 30) {

        $cachedTime = $this->formatTimestamp(time());

        $steamids = array();
        foreach ($accounts as $acc) {
            $steamids[] = SteamID::Parse($acc, SteamID::FORMAT_S32)->Format(SteamID::FORMAT_STEAMID64);
        }

        $i = 0;
        $steamPlayers = array();

        // Steam API has 100 player per call limit
        while ($batch = array_slice($steamids, $i++ * 100, 100)) {
            $steamPlayers = array_merge(
                $steamPlayers,
                $this->SteamPlayer->find('all', array(
                    'timeout' => $timeout,
                    'conditions' => array(
                        'steamids' => $batch
                    )
                ))['SteamPlayers']
            );
        }

        $saveToCache = Hash::map($steamPlayers, '{n}', function($player) use ($cachedTime, $precache){

            $steamid = $player['steamid'];
            $user_id = SteamID::Parse($steamid, SteamID::FORMAT_STEAMID64)->Format(SteamID::FORMAT_S32);

            return array(
                'steamid' => $steamid,
                'user_id' => $user_id,
                'personaname' => $player['personaname'],
                'profileurl' => $player['profileurl'],
                'avatar' => $player['avatar'],
                'avatarmedium' => $player['avatarmedium'],
                'avatarfull' => $player['avatarfull'],
                'cached' => $cachedTime,
                'precached' => $precache
            );
        });

        $this->saveMany($saveToCache, array('atomic' => false));
        return $saveToCache;
    }

    /**
     * Returns a list of steamids for all known players. Known players are either members, have
     * completed purchases in the past 6 months, or have been sent rewards.
     *
     * @return array of signed 32-bit steamids (user_id)
     */
    public function getKnownPlayers() {

        $db = $this->getDataSource();

        $orderQuery = $db->buildStatement(array(
            'fields' => array(
                'user_id'
            ),
            'table' => $db->fullTableName($this->User->Order),
            'alias' => 'Order',
            'conditions' => array(
                'Order.date >' => $this->formatTimestamp(strtotime('-6 months'))
            )
        ), $this->User->Order);

//        $quickauthQuery = $db->buildStatement(array(
//            'fields' => array(
//                'User.user_id'
//            ),
//            'conditions' => array(
//                'last_activity >' => (time() - Configure::read('Store.SteamCache.PrecacheQuickAuthTime'))
//            ),
//            'joins' => array(
//                array(
//                    'table' => $db->fullTableName($this->User->QuickAuth),
//                    'alias' => 'QuickAuth',
//                    'conditions' => array(
//                        'User.user_id = QuickAuth.user_id'
//                    )
//                )
//            ),
//            'table' => $db->fullTableName($this->User),
//            'alias' => 'User',
//        ), $this->User);

        $rewardQuery = $db->buildStatement(array(
            'fields' => array(
                'recipient_id'
            ),
            'table' => $db->fullTableName($this->User->RewardRecipient),
            'alias' => 'RewardRecipient',
        ), $this->User->RewardRecipient);

        $memberQuery = $db->buildStatement(array(
            'fields' => array(
                'foreign_key'
            ),
            'conditions' => array(
                'foreign_key is not null'
            ),
            'table' => $db->fullTableName($this->User->Aro),
            'alias' => 'QuickAuth',
        ), $this->User->Aro);

        $rawQuery = "select distinct user_id from ($orderQuery union all $rewardQuery union all $memberQuery) as t";

        return Hash::extract($db->fetchAll($rawQuery), '{n}.t.user_id');
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
     * Store.SteamCache.Duration seconds ago, so if the time a player was cached is less than this, that player is
     * expired.
     *
     * @return int the time against which to compare players' cached time
     */
    public function getExpireTime() {

        if (empty($this->expireTime)) {
            $this->expireTime = $this->formatTimestamp(time() - Configure::read('Store.SteamCache.Duration'));
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
     * Returns the number of pre-cached players in the cache.
     *
     * @return int the number of pre-cached players in the cache
     */
    public function countPrecachedPlayers() {

        return $this->find('count', array(
            'conditions' => array(
                'precached = 1'
            )
        ));
    }

    /**
     * Returns an integer representing the status of the most recent time the player was cached.
     *
     * 0 - not cached
     * 1 - cached
     * 2 - precached
     *
     * @param int $user_id signed 32-bit steamid of the player to check
     * @return int 0 for not cached, 1 for cached, and 2 for precached
     */
    public function getPlayerStatus($user_id) {

        $cache = Hash::get($this->find('first', array(
            'fields' => 'precached',
            'conditions' => array(
                'user_id' => $user_id
            )
        )), 'SteamPlayerCache');

        if (empty($cache)) {
            return 0;
        } else if ($cache['precached']) {
            return 2;
        } else {
            return 1;
        }
    }

    /**
     * Gets the totals for cached players and puts the data in a format friendly to HighCharts.
     *
     * @return array
     */
    public function getTotalsCached() {

        $valid = $this->countValidPlayers();
        $expired = $this->countExpiredPlayers();

        return array(
            array('Valid', $valid),
            array('Expired', $expired)
        );
    }


/**
 * Validation rules
 *
 * @var array
 */
    public $validate = array(
        'user_id' => array(
            'notEmpty' => array(
                'rule' => array('notEmpty'),
                //'message' => 'Your custom message here',
                //'allowEmpty' => false,
                //'required' => false,
                //'last' => false, // Stop validation after this rule
                //'on' => 'create', // Limit validation to 'create' or 'update' operations
            ),
        ),
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
