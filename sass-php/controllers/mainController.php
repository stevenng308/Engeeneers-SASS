<?php
	require_once(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'Dispatcher.php');
	require_once(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'Slim-2.x/Slim/Slim.php');
	\Slim\Slim::registerAutoloader();

	$myDispatch = new Dispatcher();
	$app = new \Slim\Slim();
	$app->get('/invoke/:action/:options', function ($action, $options) use($myDispatch){
		$options = json_decode($options, true);
		$options['joins'] = array(
			'armor_dimension',
			'skill_tree_dimension',
			'skill_dimension'
		);
		$options['rarity'] = 8;
		$options['piece'] = 'head';
		$options['classType'] = 'blade';
		$options['order'] = array(
				'armor_dimension' => array(
					'max_defense',
					'num_slots'
				)
			);
		$armors = $myDispatch->invokeCall($action, $options);
		// var_dump($armors);
		if($armors){
			$armors = json_decode($armors);
			var_dump($armors);
		}
	});

	$app->run();


	// $dispatch = new Dispatcher();
	// $options = array(
	// 	'rarity'    => 8,
	// 	'classType' => 'blade',
	// 	'piece'     => 'head',
	// 	'order'     => array(
	// 		'armor_dimension' => array(
	// 			'max_defense',
	// 			'num_slots'
	// 		)
	// 	)
	// );
	// $armors = $dispatch->invokeCall('getArmors', $options);
	// // echo $armors->num_rows;
	// if($armors){
	// 	var_dump(json_decode($armors->getResult()), true);
	// }
?>
