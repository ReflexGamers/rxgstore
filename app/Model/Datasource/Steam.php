<?php
//Sockets are having issues, using curl instead
//App::uses('HttpSocket', 'Network/Http');

class Steam extends DataSource {

	function __construct($config) {
		//$this->connection = new HttpSocket("http://api.steampowered.com");
		$this->apiUrl = "http://api.steampowered.com";
		parent::__construct($config);
	}
	
	public function listSources($data = null) {
		return array('steam_news','steam_players','steam_achievements');
	}

	public function read(Model $model, $queryData = array(), $recursive = null)
	{
		$results = array();

		$modelName = Inflector::camelize($model->useTable);

		switch($model->useTable)
		{
			case "steam_news":
				if (!isset($queryData['conditions']['appid'])) {
					$queryData['conditions']['appid'] = 440;
				}
				
				if (!isset($queryData['conditions']['count'])) {
					$queryData['conditions']['count'] = 10;
				}
				
				if (!isset($queryData['conditions']['maxlength'])) {
					$queryData['conditions']['maxlength'] = 0;
				}
				
				$url = "/ISteamNews/GetNewsForApp/v0001/";
				$url .= "?appid={$queryData['conditions']['appid']}&count={$queryData['conditions']['count']}&maxlength={$queryData['conditions']['maxlength']}&format=json";
				
				//$response = json_decode($this->connection->get($url), true);
				$response = json_decode($this->_getContents($this->apiUrl . $url), true);
				if (empty($response)) {
					$results[$modelName] = array();
					break;
				}

				$results[$modelName] = array();
				foreach($response['appnews']['newsitems']['newsitem'] as $data)
				{
					$results[$modelName][] = $data;
				}
				break;
			case "steam_players":
				if (!isset($queryData['conditions']['steamids'])) {
					$queryData['conditions']['steamids'] = array("76561197960435530");
				}

				$steamids = implode($queryData['conditions']['steamids'], ',');

				$url = "/ISteamUser/GetPlayerSummaries/v0002/";
				$url .= "?key={$this->config['apikey']}&steamids={$steamids}&format=json";

				//$response = json_decode($this->connection->get($url), true);
				$response = json_decode($this->_getContents($this->apiUrl . $url), true);
				if (empty($response)) {
					$results[$modelName] = array();
					break;
				}

				$results[$modelName] = array();
				foreach($response['response']['players'] as $data)
				{
					$results[$modelName][] = $data;
				}
				break;
			case "steam_achievements":
				if (!isset($queryData['conditions']['gameid'])) {
					$queryData['conditions']['gameid'] = 440;
				}

				$url = "/ISteamUserStats/GetGlobalAchievementPercentagesForApp/v0001/";
				$url .= "?gameid={$queryData['conditions']['gameid']}&format=json";

				//$response = json_decode($this->connection->get($url), true);
				$response = json_decode($this->_getContents($this->apiUrl . $url), true);
				if (empty($response)) {
					$results[$modelName] = array();
					break;
				}

				$results[$modelName] = array();
				foreach($response['achievementpercentages']['achievements']['achievement'] as $data)
				{
					$results[$modelName][] = $data;
				}
				break;
		}

		return $results;
	}
	
	public function create(Model $model, $fields = null, $values = null)
	{
		return false;
	}	
	
	public function describe($model) {
		return $model->_schema;
	}

	protected function _getContents($url) {

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);

		$data = curl_exec($ch);

		curl_close($ch);

		return $data;
	}
	
}
?>