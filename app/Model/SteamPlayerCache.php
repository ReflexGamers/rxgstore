<?php
App::uses('AppModel', 'Model');

/**
 * SteamPlayerCache Model
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

    public function clearAll() {
        $this->query('truncate steam_player_cache');
    }

    public function refresh($steamids) {

        $cachedTime = date('Y-m-d H:i:s', time());

        $i = 0;
        $steamPlayers = array();

        //Steam API has 100 player per call limit
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

    public function refreshAll() {

        $steamids = Hash::extract($this->find('all', array('fields' => 'steamid')), '{n}.SteamPlayerCache.steamid');
        $this->refresh($steamids);
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
