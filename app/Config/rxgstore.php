<?php

$config['Store'] = array(
    'AutoStock' => array(
        'OverStockMult' => 1.25, // multiplies suggested value by this
        'MaxStockMult' => 1.5, // multiply suggested stock by this to get max stock (includes overstock)
        'AntiMicroThreshold' => 0.75 // will not stock if current > this fraction of suggested (unless stock < min)
    ),
	'SavedLoginDuration' => 2592000, // 1 month
	'Shoutbox' => array(
		'PostCooldown' => 60, // 1 minute
		'UpdateInterval' => 30 // 30 seconds
	),
	'SteamCache' => array(
		'Duration' => 86400, // 24 hours
		'PrecacheQuickAuthTime' => 604800 // 1 week
	),
	'MaxTimeToConsiderInGame' => 300, // 5 minutes
	'QuickAuth' => array(
		'TokenExpire' => 180, // expires after 3 minutes
		'WindowHeight' => 600,
		'WindowWidth' => 800,
		'PopupFromGames' => array(
			'csgo'
		),
		'SkipBanCheckFromGames' => array(
			'csgo', 'tf2'
		)
	),
	'Shipping' => array(
		'Cost' => 100,
		'FreeThreshold' => 2500,
	),
	'CurrencyMultiplier' => 100, // Cents to CASH
	'CashStackSize' => 100,
	'Paypal' => array(
		'EndPoint' => (getenv('CAKEPHP_DEBUG')) ? 'api.sandbox.paypal.com' : 'api.paypal.com',
		'Options' => array(
			100 => 1.00,
			250 => 1.02,
			500 => 1.05,
			1000 => 1.10,
			2500 => 1.25
		)
	),
	'Forums' => array(
		'MemberGroups' => array(
			28, // Member
			29, // Basic Admin
			30, // Full Admin
			37, // Advisor
			38, // Captain
			39, // Cabinet
			40, // Director
			48  // Founder
		),
		'Divisions' => array(
			'Counter-Strike: Global Offensive' => 'CS:GO',
			'Team Fortress 2' => 'TF2',
			'Minecraft' => 'MC'
		)
	)
);
