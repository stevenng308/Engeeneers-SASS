<?
	require_once(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR .  'Database.php');
	class SASS{
		public $database = null;
		private $joinTables = array(
			'armor_dimension'      => 'INNER JOIN items_armors as armor_dimension ON (fact.id = armor_dimension.id) ',
			'decoration_dimension' => 'INNER JOIN items_decorations as decoration_dimension ON (fact.id = decoration_dimension.id) ',
			'skill_tree_dimension' => 'RIGHT JOIN items_skill_tree as skill_tree_dimension ON (fact.id = skill_tree_dimension.item_id) ',
			'weapon_dimension'     => 'INNER JOIN items_weapon as weapon_dimension ON (fact.id = weapon_dimension.id) ',
			'skill_dimension'      => 'INNER JOIN skill_tree_skills as skill_dimension ON (skill_tree_dimension.skill_tree_id = skill_dimension.skill_tree_id) '
		);

		function __construct(){
			$this->database = new Database();
			if($this->database->getDbConnection()->connect_error){
				die('ERROR!! CONNECTION FAILED! ' . $this->database->getDbConnection()->connect_error  . "\r\n");
			} else {
				// echo 'SUCCESS!! ' . $this->database->getDbConnection()->host_info . "\r\n"; //log
			}
		}

		function getArmors($options){
			$rarity    = (isset($options['rarity']) && !empty($options['rarity'])) ? $options['rarity'] : '';
			$classType = (isset($options['classType']) && !empty($options['classType'])) ?  ucfirst(strtolower($options['classType'])) : '';
			$piece     = (isset($options['piece']) && !empty($options['piece'])) ?  ucfirst(strtolower($options['piece'])) : '';
			$exclude   = (isset($options['exclude']) && !empty($options['exclude'])) ? $options['exclude'] : '';
			$conditionString = '';
			if($rarity !== '' || $classType !== '' || $piece !== '' || $exclude !== ''){
				$conditionString = 'WHERE ';
				$hasPrevious = false;
				if($rarity !== ''){
					$conditionString .= sprintf('fact.rarity >= %s ', $rarity);
				}
				if($classType !== ''){
					if($rarity !== ''){
						$hasPrevious = true;
					}
					$conditionString .= ($hasPrevious) ? ' AND ' : '';
					$conditionString .= sprintf('(armor_dimension.hunter_type = "%s" OR armor_dimension.hunter_type = "Both") ', $classType);
					$hasPrevious = false;
				}
				if($piece !== ''){
					if($classType !== ''){
						$hasPrevious = true;
					}
					$conditionString .= ($hasPrevious) ? ' AND ' : '';
					$conditionString .= sprintf('armor_dimension.slot = "%s" ', $piece);
					$hasPrevious = false;
				}
				if($exclude !== ''){
					if($piece !== ''){
						$hasPrevious = true;
					}
					$excludedTable =  ucfirst(strtolower($exclude['table']));
					$excludedField =  ucfirst(strtolower($exclude['field']));
					$conditionString .= ($hasPrevious) ? ' AND ' : '';
					if(is_array($exclude)){
						foreach($exclude['value'] as $key => $value){
							$exclude['value'][$key] = ucfirst(strtolower($value));
						}
						$conditionString .= sprintf('%s.%s NOT IN (%s) ', $excludedTable, $excludedField, implode(',', $exclude['value']));
					} else {
						$conditionString .= sprintf('%s.%s != %s ', $excludedTable, $excludedField,  ucfirst(strtolower($exclude['value'])));
					}
				}
			}

			if(isset($options['order']) && !empty($options['order'])){
				$order = array();
				foreach($options['order'] as $table => $fields){
					foreach($fields as $field){
						$order[] = $table . '.' . $field;
					}
				}
				// var_dump($order);
				$conditionString .= ' ORDER BY ' . implode(', ', $order);
			}

			if(isset($options['group']) && !empty($options['group'])){
				$group = array();
				foreach($options['group'] as $field){
					$group[] = $field;
				}
				$conditionString .= ' GROUP BY ' . implode(', ', $group);
			}
			$select = sprintf('SELECT fact.id as `fact|id`, fact.name%1$s as `fact|name`, fact.sub_type as `fact|slot`,
			 armor_dimension.id as `armor_dimension|id`, armor_dimension.slot as `armor_dimension|slot`, armor_dimension.defense as `armor_dimension|defense`, armor_dimension.max_defense as `armor_dimension|max_defense`, armor_dimension.fire_res as `armor_dimension|fire_res`, armor_dimension.thunder_res as `armor_dimension|thunder_res`, armor_dimension.dragon_res as `armor_dimension|dragon_res`, armor_dimension.water_res as `armor_dimension|water_res`, armor_dimension.ice_res as `armor_dimension|ice_res`, armor_dimension.gender as `armor_dimension|gender`, armor_dimension.hunter_type as `armor_dimension|hunter_type`, armor_dimension.num_slots as `armor_dimension|num_slots`,
			 skill_tree_dimension.skill_tree_id as `skill_tree_dimension|skill_tree_id`, skill_tree_dimension.point_value as `skill_tree_dimension|point_value`, skill_dimension.name%1$s as `skill_dimension|name`, skill_dimension.required_skill_tree_points as `skill_dimension|required_skill_tree_points`
			 FROM items as fact ', (Config::$locale === 'eng') ? '' : '_' . Config::$locale);

			$selectString = $this->_buildJoins($select, $options['joins']);
			echo $selectString . $conditionString;
			$armors = $this->database->getDbConnection()->query($selectString . $conditionString);
			return $this->_prepareArmors($armors);
		}

		function _prepareArmors($results){
			$obj = new stdClass();
			$obj->data = new stdClass();
			$obj->count = 0;
			if($results){
				$num_rows = $results->num_rows;
				for($i = 0; $i < $num_rows; $i++){
					$result = $results->fetch_assoc();
					$data = new stdClass();
					$hash = $this->_hashKey($result['fact|id'] . $result['fact|id']);
					$skillId = 0;
					$skillName = '';
					if(isset($obj->data->$hash)){
						if(isset($result['skill_tree_dimension|skill_tree_id'])){
							$value = $result['skill_tree_dimension|skill_tree_id'];
							if(!isset($obj->data->$hash->skill_tree_dimension->$value)){
								$obj->data->$hash->skill_tree_dimension->$value = new stdClass();
								$obj->data->$hash->skill_tree_dimension->$value->skill_tree_id = $value;
								$obj->data->$hash->skill_tree_dimension->$value->point_value = $result['skill_tree_dimension|point_value'];
							}

							if(isset($result['skill_dimension|name'])){
								$value_hash = $this->_hashKey($result['skill_dimension|name']);
								if(!isset($obj->data->$hash->skill_tree_dimension->$value->$value_hash)){
									$obj->data->$hash->skill_tree_dimension->$value->$value_hash = new stdClass();
									$obj->data->$hash->skill_tree_dimension->$value->$value_hash->name = $result['skill_dimension|name'];
									$obj->data->$hash->skill_tree_dimension->$value->$value_hash->required_skill_tree_points = $result['skill_dimension|required_skill_tree_points'];
								}
							}
						}
					} else {
						foreach($result as $key => $value){
							list($table, $field) = explode('|', $key);
							if(!isset($data->$table) && $table !== 'skill_dimension'){
								$data->$table = new stdClass();
							}
							if($table === 'skill_tree_dimension'){
								if($field === 'skill_tree_id' && !isset($data->$table->$value)){
									$data->$table->$value = new stdClass();
									$data->$table->$value->skill_tree_id = $value;
									$skillId = $value;
								} else if(isset($data->$table->$skillId)){
									$data->$table->$skillId->point_value = $value;
								} else {
									var_dump($result); var_dump($obj); die('Error! unpacking skill points for item');
								}
							} else if($table === 'skill_dimension'){
								$value_hash = $this->_hashKey($value);
								if($field === 'name' && !isset($data->skill_tree_dimension->$skillId->$value_hash)){
									$data->skill_tree_dimension->$skillId->$value_hash = new stdClass();
									$data->skill_tree_dimension->$skillId->$value_hash->name = $value;
									$skillName = $value_hash;
								} else if(isset($data->skill_tree_dimension->$skillId->$skillName)){
									$data->skill_tree_dimension->$skillId->$skillName->required_skill_tree_points = $value;
								} else {
									var_dump($result); var_dump($obj); die('Error! unpacking potential skill for item');
								}
							} else {
								$data->$table->$field = $value;
								// $obj->data->$hash = $data;
							}
						}
						$obj->data->$hash = $data;
						$obj->count++;
					}
				}
			}
			return $obj;
		}

		private function _buildJoins($query, $alias){
			$tables = $this->_getJoinTables();
			// var_dump($tables); die();
			foreach($alias as $join){
				// echo $join;
				$query .= $tables[$join];
			}
			return $query;
		}

		private function _getJoinTables(){
			return $this->joinTables;
		}

		private function _hashKey($value){
			$value = base64_encode($value);
			return str_replace('=', '', $value);
		}

		private function _unhashKey($value){
			$value = base64_decode($value);
			return $value;
		}

		/*
			Scans to find:
			equal => array(table => items, field => id, value => 1),
			in  => array(table => items, field => id, value => array(1, 2, 3, etc.)),
			order  => array(array(table => items, field => id), value => array(table => items, field => name)),
			group  => array(array(table => items, field => id), value => array(table => items, field => name)),
			between  => array(table => items, field => id, value => array(1, 2)),
			limit  => array(value => 100),
			offset => array(value => 100)
		*/
		/*private function _buildOptions($options){
			$statements = array(
				'equal' => sprintf('%s.$s = %s '),
				'in' => sprintf('%s.$s in (%s) '),
				'order' => sprintf('ORDER BY '),
				'group' => sprintf('GROUP BY '),
				'between' => sprintf('%s.%s BETWEEN %s AND %s '),
				'limit' => sprintf('LIMIT %s '),
				'offset' => sprintf('OFFSET %s '),
			);

			$defaultLimit = 1000;
			$defaultOrder = 'id'
			$optionsString = '';
			if($this->_checkAddWhere($options)){
				$optionsString = 'WHERE ';
				foreach($options as $type => $option){
					if(isset($statements[$type])){
						if(count($options > ))
					}
				}
			}
		}

		private function _checkAddWhere($options){
			if(isset($options['equal']) && $options['equal'] !== ''){
				return true;
			}
			if(isset($options['in']) && $options['in'] !== ''){
				return true;
			}
			if(isset($options['between']) && $options['between'] !== ''){
				return true;
			}
			return false;
		}*/
	}
?>
