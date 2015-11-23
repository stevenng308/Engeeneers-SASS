<?php
	require_once(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'Dispatcher.php');
	require_once(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'Slim-2.x/Slim/Slim.php');
	require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Combinations.php');
	\Slim\Slim::registerAutoloader();

	$combine 		= new Combinations();
	$myDispatch = new Dispatcher();
	$app        = new \Slim\Slim();

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
		$options['group'] = array(
			'skill_tree_dimension' => array(
				'skill_tree_id'
			),
			'fact' => array(
				'name'
			)
		);
		$options['order'] = array(
			'skill_tree_dimension' => array(
				'point_value DESC'
			),
			'armor_dimension' => array(
				'max_defense DESC',
				'num_slots DESC'
			)
		);
		$options['query_skills'] = array(
			'Bio Researcher',
			'Mind\'s Eye',
			'Attack Up (S)'
		);
		$armorList = new SplFixedArray(7);
		$skillIds = $myDispatch->invokeCall('getSkillIdByName', $options['query_skills']);
		$options['query_skills'] = json_decode($skillIds);
		for($i = 0; $i < count($armor_slots); $i++){
			$options['piece'] = $armor_slots[$i];
			$armors = $myDispatch->invokeCall($action, $options);
			$armorList[$i] = $armors;
		}
		$options = array(
			'query_skills' => $options['query_skills'],
			'joins' => array(
				'skill_tree_dimension',
				'decoration_dimension'
			)
		);
		$decorations = $myDispatch->invokeCall('getSkillDecorations', $options);
		$options['weapon'] = 2;
		// var_dump($skillIds);
		// var_dump($armors);
		// var_dump($decorations); exit;
		if(!empty($armorList)){
			$combine->gattai($options['weapon'], $armorList, $skillIds, $decorations);
		}
	});

	$app->run();

?>
