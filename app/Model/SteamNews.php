<?php
class SteamNews extends AppModel {
	public $useDbConfig = 'steam';
	
	function schema() {
        $this->_schema = array(
            'id' => array('type' => 'integer'),
			'title' => array('type' => 'string'),
			'url' => array('type' => 'string'),
			'author' => array('type' => 'string'),
			'contents' => array('type' => 'text'),
			'feedlabel' => array('type' => 'string'),
			'date' => array('type' => 'date'),
			'feedname' => array('type' => 'string'),
        );
        return $this->_schema;
	}
}
?>