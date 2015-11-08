<?php
	class Combinations{
		private $slots         = null;
		public $skillThreshold = 5;
		public $resultLimit    = 500;
		function __construct(){
			$this->slots        = new stdClass();
			$this->slots->zero  = 0;
			$this->slots->one   = 0;
			$this->slots->two   = 0;
			$this->slots->three = 0;
		}

		function gattai($armors, $skillIds, $decorations){
			$skills 		 = json_decode($skillIds);
			$decorations = json_decode($decorations);
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
			var_dump($skills->data);
			$skill_map = array_keys(get_object_vars($skills->data));
			$foundArmors = array();
			$weights = array('3' => 0, '2' => 0, '1' => 0);
			foreach($skill_map as $skill){
				foreach($decorations->data->$skill->slot_weights as $slot => $weight){
					$weights[$slot] = 1;
				}
				unset($decorations->data->$skill->slot_weights);
			}

			foreach($headArmors->data as $head){
				// var_dump($head->skill_tree_dimension); exit;
				$totals = array();
				$totals = $this->_totalSkills($head, $totals, 'head');

				foreach($bodyArmors->data as $body){
					// var_dump(array_diff($this->_getArraySkillIdFromArmor($body), $totals['complete'])); exit;
					if(array_diff($this->_getArraySkillIdFromArmor($body), $totals['complete'])){
						if(isset($totals['point_value']['body'])){
							$totals = $this->_cleanUpTotalsWrapper('body', $totals);
						}
						$totals = $this->_totalSkills($body, $totals, 'body');

						foreach($gloveArmors->data as $glove){
							if(array_diff($this->_getArraySkillIdFromArmor($glove), $totals['complete'])){
								$totals = $this->_totalSkills($glove, $totals, 'glove');
								if($this->_checkSkillTotals($skill_map, $totals, 'glove') === true){
									$found = $this->_prepareFoundArmors($totals, $skills->data, $weights, $head, $body, $glove, null, null);
									if(count($foundArmors) >= $this->resultLimit){
										break 3;
									}
									$totals = $this->_cleanUpTotalsWrapper('body', $totals);
									$totals = $this->_cleanUpTotalsWrapper('glove', $totals);
									break;
								} else {
									$totals = $this->_cleanUpTotalsWrapper('glove', $totals);
								}

								foreach($waistArmors->data as $waist){
									if(array_diff($this->_getArraySkillIdFromArmor($waist), $totals['complete'])){
										$totals = $this->_totalSkills($waist, $totals, 'waist');
										if($this->_checkSkillTotals($skill_map, $totals, 'waist') === true){
											$found = $this->_prepareFoundArmors($totals, $skills->data, $weights, $head, $body, $glove, $waist, null);
											if(count($foundArmors) >= $this->resultLimit){
												break 4;
											}
											$totals = $this->_cleanUpTotalsWrapper('glove', $totals);
											$totals = $this->_cleanUpTotalsWrapper('waist', $totals);
											break;
										} else {
											$totals = $this->_cleanUpTotalsWrapper('waist', $totals);
										}

										foreach($legArmors->data as $leg){
											if(array_diff($this->_getArraySkillIdFromArmor($leg), $totals['complete'])){
												$totals = $this->_totalSkills($leg, $totals, 'leg');
												if($this->_checkSkillTotals($skill_map, $totals, 'leg') === true){
													$found = $this->_prepareFoundArmors($totals, $skills->data, $weights, $head, $body, $glove, $waist, $leg);
													if(array_sum($found['incomplete']) <= $found['num_slots']['possible']){
														unset($found['num_slots']['possible']);
														$this->_totalDecorations($found, $decorations->data, $skills->data);
														if(count($foundArmors) >= $this->resultLimit){
															break 5;
														}
														$totals = $this->_cleanUpTotalsWrapper('waist', $totals);
														$totals = $this->_cleanUpTotalsWrapper('leg', $totals);
														break;
													}
													$totals = $this->_cleanUpTotalsWrapper('leg', $totals);
												} else {
													$totals = $this->_cleanUpTotalsWrapper('leg', $totals);
												}
											}
										} //end legs

									}
								} //end waist

							}
						} //end glove

					}
				} //end body

			} //end head
			var_dump($foundArmors); exit;
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

		private function _checkSkillTotals($skills, $results, $type){
			switch($type){
				case "glove":
							return (count($skills) === count($results['complete'])) ? true : $results;
							break;
				case "waist":
						 return (count($skills) === count($results['complete'])) ? true : $results;
						 break;
				case "leg":
						 return (count($skills) === count($results['complete'])) ? true : $results;
						 break;
				default:
							return $results;
							break;
			}
		}
		private function _cleanUpTotalsWrapper($type, $totals){
			foreach($totals['point_value'][$type] as $key => $skillValue){
				list($id, $value) = explode('.', $skillValue);
				$totals = $this->_cleanUpTotals($totals, $id, $value);
				unset($totals['point_value'][$type][$key]);
			}
			return $totals;
		}

		private function _cleanUpTotals($totals, $id, $value){
			$totals[$id]['total'] -= $value;
			foreach($totals as $key => $value){
				if(is_numeric($key) && $value['total'] < $this->skillThreshold){
					$key = array_search($key, $totals['complete']);
					unset($totals['complete'][$key]);
				}
			}
			return $totals;
		}

		private function _filterCompleteSkills($results, $armor){
			$skillDim = get_object_vars($armor->skill_tree_dimension);
			foreach($skillDim as $id => $skill){
				if(!in_array($is, $results['complete'])){
					return true;
				}
			}
			return false;
		}

		private function _getArraySkillIdFromArmor($armor){
			return array_keys(get_object_vars($armor->skill_tree_dimension));
		}

		private function _getNumOfSlots($type = null){
			return (is_null($type)) ? $this->slots : $this->slots->$type;
		}

		private function _prepareFoundArmors($totals, $skillData, $weights, $head = null, $body = null, $glove = null, $waist = null, $leg = null){
			$point_values = array_values($totals['complete']);
			$num_slots = array(
				'possible' => 0,
				'3'        => 0,
				'2'        => 0,
				'1'        => 0
			);
			$incomplete = array();
			foreach($point_values as $value){
				if($totals[$value]['total'] < $skillData->$value->required){
					$incomplete[$value] = $skillData->$value->required - $totals[$value]['total'];
				}
			}
			if(!is_null($head)){
				if($head->armor_dimension->num_slots > 0){
					$num_slots[$head->armor_dimension->num_slots] = $num_slots[$head->armor_dimension->num_slots] + 1;
					$num_slots['possible'] += $head->armor_dimension->num_slots;
					if(isset($weights[$head->armor_dimension->num_slots]) && $weights[$head->armor_dimension->num_slots] > 0){
						$num_slots['possible'] += 1;
						if(isset($weights[$head->armor_dimension->num_slots])){
							unset($weights[$head->armor_dimension->num_slots]);
						}
					}
				}
				$head = $head->fact->name;
			}
			if(!is_null($body)){
				if($body->armor_dimension->num_slots > 0){
					$num_slots[$body->armor_dimension->num_slots] = $num_slots[$body->armor_dimension->num_slots] + 1;
					$num_slots['possible'] += $body->armor_dimension->num_slots;
					if(isset($weights[$body->armor_dimension->num_slots]) && $weights[$body->armor_dimension->num_slots] > 0){
						$num_slots['possible'] += 1;
						if(isset($weights[$body->armor_dimension->num_slots])){
							unset($weights[$body->armor_dimension->num_slots]);
						}
					}
				}
				$body = $body->fact->name;
			}
			if(!is_null($glove)){
				if($glove->armor_dimension->num_slots > 0){
					$num_slots[$glove->armor_dimension->num_slots] = $num_slots[$glove->armor_dimension->num_slots] + 1;
					$num_slots['possible'] += $glove->armor_dimension->num_slots;
					if(isset($weights[$glove->armor_dimension->num_slots]) && $weights[$glove->armor_dimension->num_slots] > 0){
						$num_slots['possible'] += 1;
						if(isset($weights[$glove->armor_dimension->num_slots])){
							unset($weights[$glove->armor_dimension->num_slots]);
						}
					}
				}
				$glove = $glove->fact->name;
			}
			if(!is_null($waist)){
				if($waist->armor_dimension->num_slots > 0){
					$num_slots[$waist->armor_dimension->num_slots] = $num_slots[$waist->armor_dimension->num_slots] + 1;
					$num_slots['possible'] += $waist->armor_dimension->num_slots;
					if(isset($weights[$waist->armor_dimension->num_slots]) && $weights[$waist->armor_dimension->num_slots] > 0){
						$num_slots['possible'] += 1;
						if(isset($weights[$waist->armor_dimension->num_slots])){
							unset($weights[$waist->armor_dimension->num_slots]);
						}
					}
				}
				$waist = $waist->fact->name;
			}
			if(!is_null($leg)){
				if($leg->armor_dimension->num_slots > 0){
					$num_slots[$leg->armor_dimension->num_slots] = $num_slots[$leg->armor_dimension->num_slots] + 1;
					$num_slots['possible'] += $leg->armor_dimension->num_slots;
					if(isset($weights[$leg->armor_dimension->num_slots]) && $weights[$leg->armor_dimension->num_slots] > 0){
						$num_slots['possible'] += 1;
						if(isset($weights[$leg->armor_dimension->num_slots])){
							unset($weights[$leg->armor_dimension->num_slots]);
						}
					}
				}
				$leg = $leg->fact->name;
			}
			return array($head, $body, $glove, $waist, $leg, 'num_slots' => $num_slots, 'incomplete' => $incomplete);
		}

		private function _sumTotals($carry, $item){
			$carry += $item;
    	return $carry;
		}

		private function _totalDecorations($found, $decorationsData, $skillData){
			var_dump($decorationsData);
			var_dump($found);
			foreach($found['incomplete'] as $skill => $remaining){
				foreach($decorationsData->$skill as $jewel){
					if($found['num_slots'][$jewel->num_slots] > 0){
						$found['num_slots'][$jewel->num_slots] -= 1;
						$found['incomplete'][$skill] -= $jewel->point_value;
						$found['decorations'][] = $jewel->name;
					} else {
						
					}
				}
			}

			var_dump($found); exit;
		}

		private function _totalSkills($armor, $skills, $type){
			$isValid = false;
			$results = $skills;
			if(!isset($results['complete'])){
				$results['complete'] = array();
			}
			if(!isset($results['point_value'])){
				$results['point_value'] = array();
			}
			$skillDim = get_object_vars($armor->skill_tree_dimension);
			foreach($skillDim as $id => $skill){
				if(empty($results[$id])){
					$results[$id] = array('total' => 0);
				}
				$results[$id]['total'] += $skill->point_value;
				$results['point_value'][$type][] = "{$id}.{$skill->point_value}";
				if($results[$id]['total'] >= $this->skillThreshold && !in_array($id, $results['complete'])){
					$results['complete'][] = $id;
				}
			}

			$results = $this->_checkSkillTotals($skills, $results, $type);
			return $results;
		}
	}
