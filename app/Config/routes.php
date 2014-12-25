<?php
/**
 * Routes configuration
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different URLs to chosen controllers and their actions (functions).
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Config
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
/**
 * Here, we are connecting '/' (base path) to controller called 'Pages',
 * its action called 'display', and we pass a param to select the view file
 * to use (in this case, /app/View/Pages/home.ctp)...
 */
	//Router::connect('/', array('controller' => 'pages', 'action' => 'display', 'home'));

	//Router::connect('/', array('controller' => 'Store', 'action' => 'index'));

	Router::connect('/login', array('controller' => 'Users', 'action' => 'login'));
	Router::connect('/logout', array('controller' => 'Users', 'action' => 'logout'));

	Router::connect('/recent/*', array('controller' => 'Items', 'action' => 'recent'));

	Router::connect('/store/:action', array('controller' => 'Store'));

	Router::connect('/', array('controller' => 'Items', 'action' => 'index'));
	Router::connect('/browse', array('controller' => 'Items', 'action' => 'index'));
	Router::connect('/browse/:server', array(
			'controller' => 'Items', 'action' => 'index'
		), array(
			'pass' => array('server'),
			'server' => '[a-z0-9]+'
	));

	Router::connect('/paypal', array('controller' => 'PaypalOrders', 'action' => 'addfunds'));
	Router::connect('/paypal/:action/*', array('controller' => 'PaypalOrders'));

	Router::connect('/server', array('controller' => 'Items', 'action' => 'server'));
	Router::connect('/server/:server', array(
			'controller' => 'Items', 'action' => 'server'
		), array(
			'pass' => array('server'),
			'server' => '[a-z0-9]+'
	));

	Router::connect('/faq', array('controller' => 'Items', 'action' => 'faq'));
    Router::connect('/whatsnew', array('controller' => 'Items', 'action' => 'whatsnew'));
	Router::connect('/admin', array('controller' => 'Admin', 'action' => 'index'));
	Router::connect('/admin/viewlog/:name', array(
			'controller' => 'Admin', 'action' => 'viewlog'
		), array(
			'pass' => array('name'),
			'server' => '[a-z]+'
	));
	Router::connect('/admin/:action/*', array('controller' => 'Admin'));


	Router::connect('/item/:id', array(
			'controller' => 'Items', 'action' => 'view'
		), array(
			'pass' => array('id'),
			'id' => '[a-z_0-9]+'
	));

	Router::connect('/item/:id/:action/*', array(
			'controller' => 'Items'
		), array(
			'pass' => array('id'),
			'id' => '[a-z_0-9]+'
	));

	Router::connect('/rate/', array('controller' => 'Ratings', 'action' => 'rate'));
	Router::connect('/rate/:id', array(
			'controller' => 'Ratings', 'action' => 'rate'
		), array(
			'pass' => array('id'),
			'id' => '[0-9]+'
	));
	Router::connect('/rate/recent/:id', array(
			'controller' => 'Ratings', 'action' => 'rate'
		), array(
			'pass' => array('id')
	));

	Router::connect('/review/:type/:action', array('controller' => 'Reviews'));
	Router::connect('/review/:type/:action/:id', array(
			'controller' => 'Reviews',
		), array(
			'pass' => array('type', 'id'),
			'type' => '[a-z]+',
			'id' => '[0-9]+'
	));

	Router::connect('/cart', array('controller' => 'Cart', 'action' => 'view'));
    Router::connect('/quickbuy', array('controller' => 'Cart', 'action' => 'quickbuy'));
	Router::connect('/cart/:action', array('controller' => 'Cart'));
	Router::connect('/cart/:action/:id', array(
			'controller' => 'Cart'
		), array(
			'pass' => array('id'),
			'id' => '[0-9]+'
		)
	);

	Router::connect('/buy', array('controller' => 'Orders', 'action' => 'buy'));

	Router::connect('/shout', array('controller' => 'ShoutboxMessages', 'action' => 'add'));
	Router::connect('/shout/check/:time', array(
			'controller' => 'ShoutboxMessages', 'action' => 'view'
		), array(
			'pass' => array('time'),
			'time' => '[0-9]+'
	));
	Router::connect('/shout/delete/:id', array(
			'controller' => 'ShoutboxMessages', 'action' => 'delete'
		), array(
			'pass' => array('id'),
			'time' => '[0-9]+'
	));

	Router::connect('/user/:id', array(
			'controller' => 'Users', 'action' => 'profile'
		), array(
			'pass' => array('id'),
			'id' => '[0-9]+'
	));
	Router::connect('/user/:action/:id/*', array(
			'controller' => 'Users'
		), array(
			'pass' => array('id'),
			'id' => '[0-9]+'
	));

	Router::connect('/receipt/:id', array(
			'controller' => 'orders', 'action' => 'receipt'
		), array(
			'pass' => array('id'),
			'id' => '[0-9]+'
	));

	Router::connect('/search', array('controller' => 'Users', 'action' => 'search'));

	Router::connect('/gift/:action', array('controller' => 'Gifts'));
	Router::connect('/gift/:action/:id', array(
			'controller' => 'Gifts'
		), array(
			'pass' => array('id'),
			'id' => '[0-9]+'
	));

	Router::connect('/reward/:action', array('controller' => 'Rewards'));
	Router::connect('/reward/:action/:id', array(
			'controller' => 'Rewards'
		), array(
			'pass' => array('id'),
			'id' => '[0-9]+'
	));

	Router::connect('/quickauth', array('controller' => 'QuickAuth', 'action' => 'auth'));
	Router::connect('/quickauth/:action/:id', array(
			'controller' => 'QuickAuth', 'action' => 'delete'
		), array(
			'pass' => array('id'),
			'id' => '[0-9]+'
		)
	);
	Router::connect('/quickauth/:action/*', array('controller' => 'QuickAuth', 'action' => 'view'));

	Router::connect('/sort', array('controller' => 'Items', 'action' => 'sort'));
	Router::connect('/stock', array('controller' => 'Shipments', 'action' => 'edit'));
	Router::connect('/stock/activity/*', array('controller' => 'Shipments', 'action' => 'activity'));

	Router::connect('/cache', array('controller' => 'SteamPlayerCache', 'action' => 'view'));
	Router::connect('/cache/:action/:id/*', array(
			'controller' => 'SteamPlayerCache'
		), array(
			'pass' => array('id'),
			'id' => '[0-9]+'
		)
	);
	Router::connect('/cache/:action/*', array('controller' => 'SteamPlayerCache'));

	Router::connect('/permissions', array('controller' => 'Permissions', 'action' => 'view'));
	Router::connect('/permissions/:action', array('controller' => 'Permissions'));
	Router::connect('/permissions/:action/:id', array(
			'controller' => 'Permissions'
		), array(
			'pass' => array('id'),
			'id' => '[0-9]+'
		)
	);

	//Temp
	Router::connect('/convert', array('controller' => 'Admin', 'action' => 'convert'));


	//Router::connectNamed(array('page'));
	//Allow JSON views
	Router::parseExtensions('json');

/**
 * ...and connect the rest of 'Pages' controller's URLs.
 */
	Router::connect('/pages/*', array('controller' => 'pages', 'action' => 'display'));

/**
 * Load all plugin routes. See the CakePlugin documentation on
 * how to customize the loading of plugin routes.
 */
	CakePlugin::routes();

/**
 * Load the CakePHP default routes. Only remove this if you do not want to use
 * the built-in default routes.
 */
	require CAKE . 'Config' . DS . 'routes.php';
