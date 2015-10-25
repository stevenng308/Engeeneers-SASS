<?php
	require_once(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'Dispatcher.php');
	require_once(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'Slim-2.x/Slim/Slim.php');
	require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Combinations.php');
	\Slim\Slim::registerAutoloader();

	$combine = new Combinations();
	$myDispatch    = new Dispatcher();
	$app           = new \Slim\Slim();
	$app->get('/invoke/:action/:options', function ($action, $options) use($myDispatch, $combine){
		$armor_slots = array(
			'head',
			'body',
			'arms',
			'waist',
			'legs'
		);
		$options = json_decode($options, true);
		$options['joins'] = array(
			'armor_dimension',
			'skill_tree_dimension',
			'skill_dimension'
		);
		$options['rarity'] = 8;
		$options['classType'] = 'blade';
		$options['order'] = array(
			'armor_dimension' => array(
				'max_defense',
				'num_slots'
			)
		);
		$armorList = new SplFixedArray(7);
		$options['query_skills'] = array(
			'Bio Researcher',
			'Mind\'s Eye',
			'Attack Up (S)'
		);
		$skillIds = $myDispatch->invokeCall('getSkillIdByName', $options['query_skills']);
		$options['query_skills'] = json_decode($skillIds);
		for($i = 0; $i < count($armor_slots); $i++){
			$options['piece'] = $armor_slots[$i];
			$armors = $myDispatch->invokeCall($action, $options);
			$armorList[$i] = $armors;
		}
		// var_dump($skillIds); die();
		// var_dump($armors);
		if(!empty($armorList)){
			$combine->gattai($armorList, $skillIds);
		}
	});

	$app->run();

?>
