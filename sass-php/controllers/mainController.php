<?php
	require_once(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'Dispatcher.php');
	require_once(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'Slim-2.x/Slim/Slim.php');
	require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Combinations.php');
	\Slim\Slim::registerAutoloader();

	$combine 		= new Combinations();
	$myDispatch = new Dispatcher();
	$app        = new \Slim\Slim();

	$app->post('/invoke/:action(/)(/:options)', function ($action, $options = '') use($myDispatch, $combine){
		$params = $_POST['options']; //sanitize this
		if(!is_array($params)){
			try{
				$params = json_decode($params, true);
				if(is_null($params)){
					echo $myDispatch->_getAPIResponse(201, 'POST options formatted improperly.');
					return;
				}
			} catch(Exception $e){
				echo $myDispatch->_getAPIResponse(201, 'POST options formatted improperly.');
				return;
			}
		} else {
			echo $myDispatch->_getAPIResponse(201, 'POST options formatted improperly.');
			return;
		}
		$armor_slots = array(
			'head',
			'body',
			'arms',
			'waist',
			'legs'
		);
		// $options = json_decode($options, true);
		$options = array();
		$options['joins'] = array(
			'armor_dimension',
			'skill_tree_dimension',
			'skill_dimension'
		);
		$hunter_types = array(
			'blade',
			'gunner'
		);
    $options['classType']    = (is_string($params['hunterType']) && in_array($params['hunterType'], $hunter_types)) ? $params['hunterType'] : 'blade';
    $options['rarity']       = (is_numeric($params['armorRarity']) && $params['armorRarity'] >= 7) ? $params['armorRarity'] : 8;
    $options['query_skills'] = (is_array($params['skills'])) ? $params['skills'] : array();
		if(empty($options['query_skills'])){
			echo $myDispatch->_getAPIResponse(201, 'No skills POSTed.');
			return;
		} else {
			foreach($options['query_skills'] as $skill){
				if(!is_numeric($skill)){
					echo $myDispatch->_getAPIResponse(201, 'Invalid skill POSTed.');
					return;
				}
			}
		}
    $options['group']        = array(
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
		// $options['query_skills'] = array(
		// 	'Bio Researcher',
		// 	'Mind\'s Eye',
		// 	'Attack Up (S)'
		// );
		$armorList = new SplFixedArray(7);
		$skillIds = $myDispatch->invokeCall('getSkillIdById', $options['query_skills']);
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
		$options['weapon'] = (is_numeric($params['weapon']) && $params['weapon'] >= 0 && $params['weapon'] <= 3) ? $params['weapon'] : 2;
		// var_dump($skillIds);
		// var_dump($armors);
		// var_dump($decorations); exit;
		$results = false;
		if(!empty($armorList)){
			$arrayMsg = array(
				"Missing weapon slot param",
				"Missing skill param",
				"Missing decoration param",
				"Missing charm param"
			);
      $isWeapon     = false;
      $isSkill      = false;
      $isDecoration = false;
      $isCharm      = true;
			if(isset($options['weapon'])){
				$isWeapon = true;
				unset($arrayMsg[0]);
			}
			if(isset($skillIds)){
				$isSkill = true;
				unset($arrayMsg[1]);
			}
			if(isset($decorations)){
				$isDecoration = true;
				unset($arrayMsg[2]);
			}
			if(isset($skillList)){
				$isCharm = true;
				unset($arrayMsg[3]);
			}
			if($isWeapon === true && $isSkill === true && $isDecoration === true && $isCharm === true){
				$results = $combine->gattai($options['weapon'], $armorList, $skillIds, $decorations, '');
			} else {
				echo $myDispatch->_getAPIResponse(301, implode(', ', $arrayMsg));
				return;
			}
		}
		if($results){
			echo $myDispatch->_getAPIResponse(100, $results);
		} else {
			echo $myDispatch->_getAPIResponse(101, 'No results found');
		}
	});

	//use for testing and debugging
	$app->get('/invoke/:action/:options', function ($action, $options = '') use($myDispatch, $combine){
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
		// charms section
		$options['charms'] = array(
			array(
				'slots' => 2,
				31 => 6,
				18 => 2
			),
			array(
				'slots' => 3,
				31 => 4,
				6 => 2
			),
			array(
				'slots' => 3,
				32 => 4,
				64 => 2
			)
		);
		$options['valid_charms'] = new stdClass();
		$options['valid_charms']->data = new stdClass();
		foreach($options['query_skills']->data as $id => $skill){
			foreach($options['charms'] as $charmId => $charm){
				if(array_key_exists($id, $charm)){
					$charmObj = new stdClass();
					$charmObj->skill_tree_dimension = new stdClass();
					foreach($charm as $key => $value){
						if(is_numeric($key)){
							$charmObj->skill_tree_dimension->$key = new stdClass();
							$charmObj->skill_tree_dimension->$key->skill_tree_id = $key;
							$charmObj->skill_tree_dimension->$key->point_value = $value;
						}
					}
					$options['valid_charms']->data->$charmId = $charmObj;
					unset($options['charms'][$charmId]);
				}
			}
		}
		$charmList = json_encode($options['valid_charms']);
		var_dump($charmList); die;
		// charms section end
		// var_dump($skillIds);
		// var_dump($armors);
		// var_dump($decorations); exit;
		$results = false;
		if(!empty($armorList)){
			$results = $combine->gattai($options['weapon'], $armorList, $skillIds, $decorations, $charmList);
		}
		if($results){
			echo json_encode(str_replace("'", "\'", $results));
		} else {
			echo false;
		}
	});

	$app->run();

?>
