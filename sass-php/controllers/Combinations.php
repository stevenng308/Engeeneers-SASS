<?php
	class Combinations{
		private $slots  = null;
		function __construct(){
			$this->slots        = new stdClass();
			$this->slots->zero  = 0;
			$this->slots->one   = 0;
			$this->slots->two   = 0;
			$this->slots->three = 0;
		}

		function gattai($armors, $skillIds){
			$skills 		 = json_decode($skillIds);
			$headArmors  = json_decode($armors[0]);
			$bodyArmors  = json_decode($armors[1]);
			$gloveArmors = json_decode($armors[2]);
			$waistArmors = json_decode($armors[3]);
			$legArmors   = json_decode($armors[4]);
			$foundArmors  = array();
			// echo $headArmors->count . " \n";
			// echo $bodyArmors->count . " \n";
			// echo $gloveArmors->count . " \n";
			// echo $waistArmors->count . " \n";
			// echo $legArmors->count . " \n"; exit;
			foreach($headArmors->data as $head){
				// if($head->fact->name === "Kaiser Crown X"){
					foreach($bodyArmors->data as $body){
						// if($body->fact->name === "Kaiser Mail X"){
							foreach($gloveArmors->data as $glove){
								// if($glove->fact->name === "Kaiser Vambraces X"){
									foreach($waistArmors->data as $waist){
										// if($waist->fact->name === "Kaiser Tassets X"){
											foreach($legArmors->data as $leg){
												// if($leg->fact->name === "Kaiser Greaves X"){
													$armors = array(
														$head,
														$body,
														$glove,
														$waist,
														$leg
													);
													// $foo = 107;
													// var_dump($skills->data);
													// var_dump($leg->skill_tree_dimension->$foo); exit;
													$result = $this->_totalSkills($armors, $skills->data);
													if(!empty($result)){
														$foundArmors[] = $result;
													};
												// }
											}
										// }
									}
								// }
							}
						// }
					}
				// }
			}
			echo count($foundArmors); exit;
		}

		private function _checkForFalse($val){
			return ($val === false);
		}

		private function _checkRequiredTotals($required, $total, $badSkill = false){
			if($badSkill){
				return ($total <= $required);
			} else {
				return ($total >= $required);
			}
		}

		private function _checkSkillTotals($skills){
			$checks = array();
			foreach($skills as $skill){
				if(isset($skill->totals)){
					$total = array_reduce($skill->totals, array($this, '_sumTotals'));
					if(!is_array($skill->required)){
						$checks[] = $this->_checkRequiredTotals($skill->required, $total);
					}// else { //this check may not matter for now as it is extra skills that comes with the set
					// 	$len = count($skill->required);
					// 	for($i = $len-1; $i >= 0; $i--){
					// 		$checks[] = $this->_checkRequiredTotals($skill->required, $total, ($skill->required[$i] < 0));
					// 	}
					// 	foreach($skill->required as $key => $req){
					//
					// 	}
					// }
				}
			}
			$hasFalse = (empty($checks) || count($checks) < count($skills)) ? false : array_filter($checks, array($this, '_checkForFalse'));
			return $hasFalse;
		}

		private function _sumTotals($carry, $item){
			$carry += $item;
    	return $carry;
		}

		private function _getNumOfSlots($type = null){
			return (is_null($type)) ? $this->slots : $this->slots->$type;
		}

		private function _totalSkills($armors, $skills){
			$results     = array();
			foreach($armors as $armor){
				$skillDim = get_object_vars($armor->skill_tree_dimension);
				// foreach($skillDim as $dim){ //testing method to add extra skills that come with the armor that use did not specify
				// 	$id = $dim->skill_tree_id;
				// 	if(!isset($skill->$id)){
				// 		$skills->$id = new stdClass();
 				// 	  $skills->$id->id = $id;
				// 		if(count($dim->skills) > 1){
				// 			$skills->$id->name     = array();
				// 			$skills->$id->required = array();
				// 			$skills->$id->totals   = array();
				// 			foreach($dim->skills as $dimSkill){
				// 				$skills->$id->name[]     = $dimSkill->name;
				// 				$skills->$id->required[] = $dimSkill->required_skill_tree_points;
				// 			}
				// 		} else {
				// 			foreach($dim->skills as $dimSkill){
				// 				$skills->$id->name     = $dimSkill->name;
				// 				$skills->$id->required = $dimSkill->required_skill_tree_points;
				// 			}
				// 		}
				// 	} else {
				// 		$skills->$id->totals = array();
				// 	}
				// }
				foreach($skills as $skill){
					$id = $skill->id;
					if(isset($skillDim[$id])){
					 	$skills->$id->totals[] = (int) $skillDim[$id]->point_value;
				 	}
				}
			}
			$hasFalse = $this->_checkSkillTotals($skills);
			if(empty($hasFalse)){
				$results = $armors;
			}
			return $results;
		}
	}
