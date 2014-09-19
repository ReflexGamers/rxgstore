<?php
App::uses('AppController', 'Controller');
App::import('Vendor', 'Parsedown');

/**
 * Items Controller
 *
 * @property Item $Item
 * @property PaginatorComponent $Paginator
 * @property AccountUtilityComponent $AccountUtility
 */
class ItemsController extends AppController {
	public $components = array('Paginator', 'AccountUtility', 'RequestHandler');
	public $helpers = array('Html', 'Form', 'Js', 'Time', 'Session');

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow();
		$this->Auth->deny('add', 'edit', 'sort', 'preview');
	}

	public function faq() {

	}

	public function index($server = null) {

		$user_id = $this->Auth->user('user_id');

		if (!empty($user_id)) {

			$this->loadModel('User');
			$this->loadModel('UserItem');
			$this->loadModel('Gift');

			$this->User->id = $user_id;
			$userItems = $this->UserItem->getByUser($user_id);
			$gifts = $this->User->getPendingGifts($user_id);
			$rewards = $this->User->getPendingRewards($user_id);
			$this->addPlayers(Hash::extract($gifts, '{n}.Gift.sender_id'));

			$this->set(array(
				'userItems' => $userItems,
				'credit' => $this->User->field('credit'),
				'gifts' => $gifts,
				'rewards' => $rewards
			));
		}

		$this->loadModel('Server');

		$serverData = $this->Server->getAll();
		$servers = Hash::combine($serverData, '{n}.short_name', '{n}.name');
		$childServers = array();

		foreach ($serverData as $serv) {
			if (!empty($serv['parent_id'])) {
				$childServers[] = $serv['short_name'];
			}
		}

		$this->set(array(
			'servers' => $servers,
			'childServers' => implode($childServers, ',')
		));

		$this->loadShoutboxData();
		$this->server($server);
		$this->recent(false);
	}

	public function server($server = null) {

		$this->loadModel('User');
		$user_id = $this->Auth->user('user_id');

		if (empty($server)) {

			$preferredServer = $this->Session->read('preferredServer');
			if (empty($preferredServer)) {
				$preferredServer = $this->User->getPreferredServer($user_id);
				if (!empty($preferredServer)) {
					$this->Session->write('preferredServer', $preferredServer);
				}
			}

			if (!empty($preferredServer)) {
				$serverItems = $this->Item->getByServer($preferredServer);
				$server = $preferredServer;
			} else {
				$serverItems = $this->Item->getBuyable();
			}

		} else {

			if ($server == 'all') {
				$this->User->deletePreferredServer($user_id);
			} else  {
				$this->User->setPreferredServer($user_id, $server);
			}

			$this->Session->write('preferredServer', $server);
			$serverItems = $this->Item->getByServer($server);

			//Redirect on unrecognized game
			if (empty($serverItems) && !$this->request->is('ajax')) {
				$this->redirect(array('controller' => 'Items', 'action' => 'index'));
			}
		}

		$this->set('serverItems', $serverItems);

		$ratings = Hash::combine(Hash::map(
			$this->Item->getAllTotalRatings(),
			'{n}', function($arr){
				return array_merge($arr['Rating'], $arr[0]);
			}
		), '{n}.item_id', '{n}');

		$this->set(array(
			'server' => $server,
			'serverItems' => $serverItems,
			'ratings' => $ratings
		));
	}

	public function recent($doRender = true) {

		$this->loadModel('Activity');

		$this->Paginator->settings = array(
			'Activity' => array(
				'limit' => 5,
			)
		);

		$activities = $this->Activity->getRecent($this->Paginator->paginate('Activity'));

		$this->addPlayers(Hash::extract($activities, '{n}.{s}.user_id'));
		$this->addPlayers(Hash::extract($activities, '{n}.{s}.sender_id'));
		$this->addPlayers(Hash::extract($activities, '{n}.{s}.recipient_id'));
		$this->addPlayers(Hash::extract($activities, '{n}.RewardRecipient.{n}'));

		$this->loadItems();
		$this->loadCashData();

		$this->set(array(
			'activities' => $activities,
			'activityPageLocation' => array('controller' => 'Items', 'action' => 'recent')
		));

		if ($doRender) {
			$this->render('/Activity/list');
		}
	}

	public function view($id = null) {

		$itemData = $this->Item->find('first', array(
			'conditions' => array(
				'OR' => array(
					'item_id' => $id,
					'short_name' => $id
				)
			),
			'contain' => array(
				'Feature' => array(
					'fields' => array(
						'description'
					)
				)
			)
		));

		if (empty($itemData)) {
			throw new NotFoundException(__('Invalid item'));
		}

		$item = $itemData['Item'];
		$item_id = $item['item_id'];

		$this->loadModel('Server');

		$servers = Hash::extract($this->Server->find('all', array(
			'joins' => array(
				array(
					'table' => 'server_item',
					'conditions' => array(
						'server_item.server_id = Server.server_id',
						'server_item.item_id' => $item_id
					)
				)
			)
		)), '{n}.Server');

		$parsedown = new Parsedown();

		$item['description'] = $parsedown->text($item['description']);


		$topBuyers = Hash::combine(
			$this->Item->getTopBuyers($item_id),
			'{n}.Order.user_id',
			'{n}.{n}'
		);

		$this->addPlayers(array_keys($topBuyers));

		if ($this->Auth->user()) {

			$this->loadModel('User');
			$user_id = $this->Auth->user('user_id');

			$this->set(array(
				'userCanRate' => $this->User->canRateItem($user_id, $item_id),
				'review' => $this->Item->Rating->getByItemAndUser($item_id, $user_id)
			));
		}

		$this->set(array(
			'item' => $item,
			'stock' => $this->Item->getStock($item_id),
			'servers' => $servers,
			'topBuyers' => $topBuyers,
			'ratings' => $this->Item->getTotalRatings($item_id),
			'features' => $itemData['Feature']
		));

		$this->loadItems();
		$this->loadShoutboxData();

		$this->reviews($item, false);
		$this->activity($item, false);
	}

	public function reviews($item = null, $doRender = true) {

		if ($doRender) {

			$item = Hash::extract($this->Item->findByItemIdOrShortName($item, $item, array('item_id', 'name', 'short_name')), 'Item');

			if (empty($item)) {
				throw new NotFoundException(__('Invalid item'));
			}

			$this->set('item', $item);
		}

		$this->loadModel('Rating');
		$this->Paginator->settings = array(
			'Rating' => array(
				'fields'  => array(
					'Rating.user_id', 'Rating.item_id', 'rating', 'review.review_id', 'review.created', 'review.modified', 'review.content', 'SUM(quantity) as quantity'
				),
				'joins' => array(
					array(
						'table' => 'review',
						'conditions' => array(
							'Rating.rating_id = review.rating_id',
							'Rating.item_id' => $item['item_id']
						)
					),
					array(
						'table' => 'order_detail',
						'conditions' => array(
							'Rating.item_id = order_detail.item_id'
						)
					),
					array(
						'table' => 'order',
						'conditions' => array(
							'order_detail.order_id = order.order_id',
							'Rating.user_id = order.user_id'
						)
					)
				),
				'order' => 'quantity desc',
				'group' => 'order.user_id',
				'limit' => 3
			)
		);

		$reviews = Hash::map(
			$this->Paginator->paginate('Rating'),
			'{n}', function ($arr){
				return array_merge(
					$arr['Rating'],
					$arr['review'],
					$arr[0]
				);
			}
		);

		$this->addPlayers(Hash::extract($reviews, '{n}.user_id'));
		$this->loadItems();

		$this->set(array(
			'reviews' => $reviews,
			'displayType' => 'item',
			'reviewPageLocation' => array('controller' => 'Items', 'action' => 'reviews', 'id' => $item['short_name'])
		));

		if ($doRender) {
			$this->render('/Reviews/list');
		}
	}

	public function activity($item = null, $doRender = true) {

		if ($doRender) {

			$item = Hash::extract($this->Item->findByItemIdOrShortName($item, $item, array('item_id', 'name', 'short_name')), 'Item');

			if (empty($item)) {
				throw new NotFoundException(__('Invalid item'));
			}

			$this->set('item', $item);
		}

		$this->loadModel('Activity');
		$this->Paginator->settings = array(
			'Activity' => array(
				'findType' => 'byItem',
				'item_id' => $item['item_id'],
				'limit' => 5
			)
		);

		$activities = $this->Activity->getRecent($this->Paginator->paginate('Activity'));

		$this->addPlayers(Hash::extract($activities, '{n}.{s}.user_id'));
		$this->addPlayers(Hash::extract($activities, '{n}.{s}.sender_id'));
		$this->addPlayers(Hash::extract($activities, '{n}.{s}.recipient_id'));
		$this->addPlayers(Hash::extract($activities, '{n}.RewardRecipient.{n}'));

		$this->loadItems();

		$this->set(array(
			'activities' => $activities,
			'activityPageLocation' => array('controller' => 'Items', 'action' => 'activity', 'id' => $item['short_name'])
		));

		if ($doRender) {
			$this->render('/Activity/list');
		}
	}


	public function edit($name = null) {

		if (!$this->Access->check('Items', 'update')) {
			$this->redirect(array('controller' => 'items', 'action' => 'view', 'id' => $name));
		}

		$itemData = $this->Item->find('first', array(
			'conditions' => array(
				'short_name' => $name
			),
			'contain' => 'Feature'
		));

		if (!$itemData) {
			throw new NotFoundException(__('Invalid item'));
		}

		$item = $itemData['Item'];
		$item_id = $item['item_id'];

		$this->loadModel('Server');
		$serverData = $this->Server->find('all', array(
			'contain' => array(
				'ServerItem' => array(
					'conditions' => array(
						'ServerItem.item_id' => $item_id
					)
				)
			)
		));

		if ($this->request->is('post', 'put')) {

			$this->loadModel('ServerItem');

			$serverParents = Hash::combine($serverData, '{n}.Server.server_id', '{n}.Server.parent_id');
			$newServers = Hash::extract($this->request->data, 'ServerItem.server_id');
			$oldServers = Hash::extract($serverData, '{n}.ServerItem.{n}.server_id');

			foreach ($newServers as $key => $server) {
				if (!empty($serverParents[$server]) && in_array($serverParents[$server], $newServers)) {
					unset($newServers[$key]);
				}
			}

			$addServers = array_diff($newServers, $oldServers);
			$removeServers = array_diff($oldServers, $newServers);

			$insertServerSuccess = true;
			$deleteServerSuccess = true;

			if (!empty($addServers) && !empty(array_values($addServers)[0])) {
				$addServers = Hash::map($addServers, '{n}', function($val) use ($item_id){
					return array(
						'item_id' => $item_id,
						'server_id' => $val
					);
				});

				$insertServerSuccess = $this->ServerItem->saveMany($addServers, array('atomic' => false));
			}

			if (!empty($removeServers)) {
				$removeServers = Hash::map($removeServers, '{n}', function($val){
					return array('ServerItem.server_id' => $val);
				});

				$deleteServerSuccess = $this->ServerItem->deleteAll(array(
					'ServerItem.item_id' => $item_id,
					'AND' => array(
						'OR' => $removeServers
					)
				), false);
			}


			$this->loadModel('Feature');
			$savedFeatures = !empty($this->request->data['Feature']) ? $this->request->data['Feature'] : array();

			foreach ($savedFeatures as $key => &$feature) {
				if (empty($feature['description'])) {
					unset($savedFeatures[$key]);
				} else {
					$feature['item_id'] = $item_id;
				}
			}

			$removeFeatures = array_diff(
				Hash::extract($this->Feature->findAllByItemId($item_id), '{n}.Feature.feature_id'), // old
				Hash::extract($savedFeatures, '{n}.feature_id') // saved
			);

			$saveFeatureSuccess = true;
			$deleteFeatureSuccess = true;


			if (!empty($savedFeatures)) {
				$saveFeatureSuccess = $this->Feature->saveMany($savedFeatures, array('atomic' => false));
			}

			if (!empty($removeFeatures)) {
				$removeFeatures = Hash::map($removeFeatures, '{n}', function($val){
					return array('Feature.feature_id' => $val);
				});

				$deleteFeatureSuccess = $this->Feature->deleteAll(array(
					'OR' => $removeFeatures
				), false);
			}


			$item = $this->request->data['Item'];
			$stock = $this->request->data['Stock'];

			$itemSaveSuccess = $this->Item->save($item, array(
				'fieldList' => array(
					'buyable', 'price', 'name', 'plural', 'short_name', 'description'
				)
			));

			$stockSaveSuccess = $this->Item->Stock->save($stock, array(
				'fieldList' => array(
					'ideal_quantity', 'maximum'
				)
			));

			if ($itemSaveSuccess && $stockSaveSuccess && $insertServerSuccess && $deleteServerSuccess && $saveFeatureSuccess && $deleteFeatureSuccess) {
				$this->Session->setFlash('The item has been saved.', 'default', array('class' => 'success'));
				$this->redirect(array('action' => 'edit', 'id' => $item['short_name']));
				return;
			} else {
				$this->Session->setFlash('Something went wrong. The item could not be fully saved.', 'default', array('class' => 'error'));
				$done = false;
			}
		}

		if (!isset($done)) {
			//In case saving went wrong, don't overwrite item
			//but do overwrite server selection since it's not in the right format
			$this->set('item', $item);
		}

		$servers = Hash::extract($serverData, '{n}.Server');

		foreach ($servers as &$server) {
			$parent_id = $server['parent_id'];
			if (empty($parent_id)) {
				$server['sort_index'] = $server['server_id'] * 2;
			} else {
				$server['sort_index'] = $parent_id * 2 + 1;
			}
		}

		$servers = Hash::sort($servers, '{n}.sort_index');
		$childServers = array();

		foreach ($servers as $server) {
			if (!empty($server['parent_id'])) {
				$childServers[] = $server['server_id'];
			}
		}

		$servers = Hash::combine($servers, '{n}.server_id', '{n}.name');

		//$servers = Hash::combine($serverData, '{n}.Server.server_id', '{n}.Server.name');
		$selectedServers = Hash::extract($serverData, '{n}.ServerItem.{n}.server_id');

		$maxSold = $this->Item->OrderDetail->find('first', array(
			'fields' => array(
				'MAX(quantity) as max'
			),
			'conditions' => array(
				'item_id' => $item_id
			)
		));

		if (!empty($maxSold)) {
			$this->set('suggested', $maxSold[0]['max']);
		}

		$this->set(array(
			'servers' => $servers,
			'selectedServers' => $selectedServers,
			'childServers' => implode($childServers, ','),
			'features' => $itemData['Feature'],
			'currencyMult' => Configure::read('Store.CurrencyMultiplier')
		));

		$stock = $this->Item->Stock->findByItemId($item_id);
		if (!empty($stock)) {
			$this->set('stock', $stock['Stock']);
		}
	}

	public function preview() {

		/*
		if (isset($this->request->data['description'])) {
			$parsedown = new Parsedown();
			$this->set('content', $parsedown->text($this->request->data['description']));
			$this->set('_serialize', 'content');
		}*/

		//$item = $this->request->data['Item'];
		//echo $this->request->data['description'];

		$this->request->allowMethod('post');

		$parsedown = new Parsedown();
		$this->set('content', $parsedown->text($this->request->data['description']));

		//$this->autoRender = false;
		//return $this->view($item['short_name'], $item);
		$this->render('/Common/empty');
	}

	public function sort() {

		if (!$this->Access->check('Items', 'update')) {
			$this->redirect($this->referer());
			return;
		}

		if (isset($this->request->data['Item'])) {
			$this->Item->saveMany($this->request->data['Item'], array(
				'fields' => array('display_index'),
				'atomic' => false
			));

			$this->Session->setFlash('The item display order you provided has been saved!', 'default', array('class' => 'success'));
		}

		$this->set(array(
			'items' => Hash::extract($this->Item->find('all'), '{n}.Item')
		));
	}
}
