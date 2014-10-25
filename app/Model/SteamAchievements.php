<?php
class SteamAchievements extends AppModel {
    public $useDbConfig = 'steam';

    function schema() {
        $this->_schema = array(
            'name' => array('type' => 'String'),
            'percent' => array('type' => 'Integer'),
        );
        return $this->_schema;
    }

}
?>