<?
	require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Dispatcher.php');

	$dispatch = new Dispatcher();
	$options = array(
		'rarity'    => 8,
		'classType' => 'blade',
		'slot'      => 'head',
		'order'     => array(
			'armor_dimension' => array(
				'max_defense',
				'num_slots'
			)
		)
	);
	$dispatch->invokeCall('getArmors', $options);
?>
