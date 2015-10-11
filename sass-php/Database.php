<?
	require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config.php');

	class Database{
		private $db = null;
		private $required_tables = array(
			'items'             => '../mh4u_app_db/items_fact_table.csv',
			'items_armors'      => '../mh4u_app_db/items_to_armor_dim.csv',
			'items_decorations' => '../mh4u_app_db/items_to_decorations_dim.csv',
			'items_skill_tree'  => '../mh4u_app_db/items_to_skill_tree_dim.csv',
			'items_weapon'      => '../mh4u_app_db/items_to_weapon_dim.csv',
			'skill_tree_skills' => '../mh4u_app_db/skill_tree_to_skill_dim.csv'
		);
		function __construct(){
			$hostName = Config::$hostName;
			$port = Config::$port;
			$user = Config::$user;
			$pass = Config::$pass;
			$dbName	= Config::$dbName;
			if(!isset($this->db) && is_null($this->db)){
				$this->db = new mysqli($hostName, $user, $pass, $dbName);
				$this->db->set_charset("utf8");
			} else {
				echo 'connection already established'; //LOGGER
			}
		}

		function __destruct(){
			$this->db->close();
		}

		function checkTables(){
			foreach($this->_getRequiredTables() as $tableName => $file){
				if($this->checkTable($tableName) === false){
					if($this->_buildTable($tableName, $file)){
						echo $tableName . " successfully created \r\n"; //LOGGER
					} else {
						echo 'ERROR!! ' . $tableName . " not created \r\n"; //EXCEPTION
						return false;
					}
				} else {
					echo $tableName . " exists \r\n"; //LOGGER
				}
			}
			return true;
		}

		function checkTable($tableName){
			$mysqli = $this->getDbConnection();
			$mysqli->query(sprintf('SHOW TABLES LIKE "%s"', $tableName));
			if($mysqli->affected_rows === 0){
				return false;
			}
			return true;
		}

		function getDbConnection(){
			return $this->db;
		}

		function loadData($fileHandler, $tableName, $headers){
			$this->_loadData($fileHandler, $tableName, $headers);
		}

		private function _buildTable($tableName, $filePath){
			switch($tableName){
				case 'items':
				      $func = '_itemsFieldTypes';
							break;
				case 'items_armors':
				     	$func	= '_itemsArmorsFieldTypes';
							break;
				case 'items_decorations':
							$func	= '_itemsDecorFieldTypes';
							break;
				case 'items_skill_tree':
				 			$func	= '_itemsSkillTreeFieldTypes';
							break;
				case 'items_weapon':
				     	$func	= '_itemsWeaponFieldTypes';
							break;
				case 'skill_tree_skills':
							$func = '_itemsSkillTreeSkillsFieldTypes';
							break;
				default:
							die('specified table does not have func'); //EXCEPTION
			}

			$mysqli                     = $this->getDbConnection();
			if(file_exists($filePath)){
				$dropQuery   = sprintf('DROP TABLE IF EXISTS `sass`.`%s`', $tableName);
				$createQuery = sprintf('CREATE TABLE `sass`.`%s` (', $tableName);
				$file        = fopen($filePath, 'r');
				$row         = fgetcsv($file, 0, ',');
				$headers     = array();
				foreach($row as $field){
					if(substr($field, 0, 1) === '_'){
						$field = substr($field, 1);
					}
					$createQuery .= $field;
					if(method_exists($this, $func)){
						$createQuery .= $this->$func($field);
					} else {
						$createQuery .= ' varchar(255) DEFAULT NULL, ';
					}
					$headers[] = $field;
				}
				$createQuery .= 'PRIMARY KEY (id)';
				//$create_query = substr($create_query, 0, strlen($create_query) - 1);
				$createQuery .= ') ENGINE=InnoDB DEFAULT CHARSET=utf8 DEFAULT COLLATE=utf8_unicode_ci';
				echo $createQuery . "\r\n"; //LOGGER
				$mysqli->query($dropQuery);
				$mysqli->query($createQuery);

				if($this->checkTable($tableName) === false){
					return false;
				}
				$this->_loadData($file, $tableName, $headers);
				fclose($file);
				return true;
			} else {
				die($filePath . ' was not found'); //EXCEPTION
			}
		}

		private function _getRequiredTables(){
			return $this->required_tables;
		}

		private function _itemsFieldTypes($field){
			$fieldTypes = array(
				'id'                  => ' int(11), ',
				'name'                => ' varchar(255) DEFAULT NULL, ',
				'name_de'             => ' varchar(255) DEFAULT NULL, ',
				'name_fr'             => ' varchar(255) DEFAULT NULL, ',
				'name_es'             => ' varchar(255) DEFAULT NULL, ',
				'name_it'             => ' varchar(255) DEFAULT NULL, ',
				'name_jp'             => ' varchar(255) DEFAULT NULL, ',
				'type'                => ' varchar(255) DEFAULT NULL, ',
				'sub_type'            => ' varchar(255) DEFAULT NULL, ',
				'rarity'              => ' int(11) DEFAULT NULL, ',
				'carry_capacity'      => ' int(11) DEFAULT NULL, ',
				'buy'                 => ' int(11) DEFAULT NULL, ',
				'sell'                => ' int(11) DEFAULT NULL, ',
				'description'         => ' text, ',
				'icon_name'           => ' varchar(255) DEFAULT NULL, ',
				'armor_dupe_name_fix' => ' varchar(255) DEFAULT NULL, '
			);
			return $fieldTypes[$field];
		}

		private function _itemsArmorsFieldTypes($field){
			$fieldTypes = array(
				'id'          => ' int(11), ',
				'slot'        => ' varchar(255) DEFAULT NULL, ',
				'defense'     => ' int(11) DEFAULT NULL, ',
				'max_defense' => ' int(11) DEFAULT NULL, ',
				'fire_res'    => ' int(11) DEFAULT NULL, ',
				'thunder_res' => ' int(11) DEFAULT NULL, ',
				'dragon_res'  => ' int(11) DEFAULT NULL, ',
				'water_res'   => ' int(11) DEFAULT NULL, ',
				'ice_res'     => ' int(11) DEFAULT NULL, ',
				'gender'      => ' int(11) DEFAULT NULL, ',
				'hunter_type' => ' varchar(255) DEFAULT NULL, ',
				'num_slots'   => ' int(11) DEFAULT NULL, '
			);
			return $fieldTypes[$field];
		}

		private function _itemsDecorFieldTypes($field){
			$fieldTypes = array(
				'id'        => ' int(11), ',
				'num_slots' => ' int(11) DEFAULT NULL, '
			);
			return $fieldTypes[$field];
		}

		private function _itemsSkillTreeFieldTypes($field){
			$fieldTypes = array(
				'id'            => ' int(11), ',
				'item_id'       => ' int(11) DEFAULT NULL, ',
				'skill_tree_id' => ' int(11) DEFAULT NULL, ',
				'point_value'   => ' int(11) DEFAULT NULL, '
			);
			return $fieldTypes[$field];
		}

		private function _itemsWeaponFieldTypes($field){
			$fieldTypes = array(
				'id'               => ' int(11), ',
				'parent_id'        => ' int(11) DEFAULT NULL, ',
				'wtype'            => ' varchar(255) DEFAULT NULL, ',
				'creation_cost'    => ' int(11) DEFAULT NULL, ',
				'upgrade_cost'     => ' int(11) DEFAULT NULL, ',
				'attack'           => ' int(11) DEFAULT NULL, ',
				'max_attack'       => ' int(11) DEFAULT NULL, ',
				'element'          => ' varchar(255) DEFAULT NULL, ',
				'element_attack'   => ' int(11) DEFAULT NULL, ',
				'element_2'         => ' varchar(255) DEFAULT NULL, ',
				'element_2_attack' => ' int(11) DEFAULT NULL, ',
				'awaken'           => ' varchar(255) DEFAULT NULL, ',
				'awaken_attack'    => ' int(11) DEFAULT NULL, ',
				'defense'          => ' int(11) DEFAULT NULL, ',
				'sharpness'        => ' varchar(255) DEFAULT NULL, ',
				'affinity'         => ' int(11) DEFAULT NULL, ',
				'horn_notes'       => ' varchar(255) DEFAULT NULL, ',
				'shelling_type'    => ' varchar(255) DEFAULT NULL, ',
				'phial'            => ' varchar(255) DEFAULT NULL, ',
				'charges'          => ' varchar(255) DEFAULT NULL, ',
				'coatings'         => ' varchar(255) DEFAULT NULL, ',
				'recoil'           => ' varchar(255) DEFAULT NULL, ',
				'reload_speed'     => ' int(11) DEFAULT NULL, ',
				'rapid_fire'       => ' varchar(255) DEFAULT NULL, ',
				'deviation'        => ' varchar(255) DEFAULT NULL, ',
				'ammo'             => ' varchar(255) DEFAULT NULL, ',
				'special_ammo'     => ' varchar(255) DEFAULT NULL, ',
				'num_slots'        => ' int(11) DEFAULT NULL, ',
				'tree_depth'       => ' int(11) DEFAULT NULL, ',
				'final'            => ' int(11) DEFAULT NULL, '
			);
			return $fieldTypes[$field];
		}

		private function _itemsSkillTreeSkillsFieldTypes($field){
			$fieldTypes = array(
				'id'                         => ' int(11), ',
				'skill_tree_id'                  => ' int(11) DEFAULT NULL, ',
				'required_skill_tree_points' => ' int(11) DEFAULT NULL, ',
				'name'                       => ' varchar(255) DEFAULT NULL, ',
				'name_de'                    => ' varchar(255) DEFAULT NULL, ',
				'name_fr'                    => ' varchar(255) DEFAULT NULL, ',
				'name_es'                    => ' varchar(255) DEFAULT NULL, ',
				'name_it'                    => ' varchar(255) DEFAULT NULL, ',
				'name_jp'                    => ' varchar(255) DEFAULT NULL, ',
				'description'                => ' text, ',
				'description_de'             => ' text, ',
				'description_fr'             => ' text, ',
				'description_es'             => ' text, ',
				'description_it'             => ' text, ',
				'description_jp'             => ' text, '
			);
			return $fieldTypes[$field];
		}

		private function _loadData($fileHandler, $tableName, $headers){
			$mysqli    = $this->getDbConnection();
			$values    = array();
			$inserted  = 0;
			while($row = fgetcsv($fileHandler, 0, ',')){
				$insertQuery = sprintf('INSERT INTO %s (%s) ', $tableName, implode(',', $headers));
				foreach($row as $value){
					$values[] = addslashes($value);
				}
				$insertQuery .= sprintf('VALUES ("%s")', implode('","', $values));
				if($mysqli->query($insertQuery)){
					$inserted++;
					echo $inserted . ' inserted for ' . $tableName . "\r\n"; //LOGGER
				} else {
					echo 'ERROR!! row not inserted ' . $tableName . "\r\n"; //EXCEPTION
					die('insert query -- ' . $insertQuery . "\r\n");
				}
				$insertQuery = '';
				$values      = array();
			}
		}
	}

?>
