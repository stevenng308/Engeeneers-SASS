<?php
	class Combinations{
		private $slots         = null;
		public $skillThreshold = 3;
		public $resultLimit    = 100;
		function __construct(){
			$this->slots        = new stdClass();
			$this->slots->zero  = 0;
			$this->slots->one   = 0;
			$this->slots->two   = 0;
			$this->slots->three = 0;
			$this->weapon       = 0;
		}

		function gattai($weapon, $armors, $skillIds, $decorations, $charms){
      $this->weapon = $weapon;
      $skills       = json_decode($skillIds);
      $decorations  = json_decode($decorations);
      $charms       = json_decode($charms);
      $headArmors   = json_decode($armors[0]);
      $bodyArmors   = json_decode($armors[1]);
      $gloveArmors  = json_decode($armors[2]);
      $waistArmors  = json_decode($armors[3]);
      $legArmors    = json_decode($armors[4]);
      $foundArmors  = array();
			// echo $headArmors->count . " \n";
			// echo $bodyArmors->count . " \n";
			// echo $gloveArmors->count . " \n";
			// echo $waistArmors->count . " \n";
			// echo $legArmors->count . " \n"; exit;
			// var_dump($skills->data);
      $skill_map    = array_keys(get_object_vars($skills->data));
      $weights      = array('3' => 0, '2' => 0, '1' => 0);
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
						$totals = $this->_totalSkills($body, $totals, 'body');

						foreach($gloveArmors->data as $glove){
							if(array_diff($this->_getArraySkillIdFromArmor($glove), $totals['complete'])){
								$totals = $this->_totalSkills($glove, $totals, 'glove');
								if($this->_checkSkillTotals($skill_map, $totals, 'glove') === true){
									$found = $this->_prepareFoundArmors($totals, $skills->data, $weights, $head, $body, $glove, null, null);
									if(array_sum($found['incomplete']) <= $found['num_slots']['possible']){
										unset($found['num_slots']['possible']);
										$found = $this->_totalDecorations($found, $decorations->data, $skills->data);
										if($found){
											$foundArmors[] = $found;
										}
										if(count($foundArmors) >= $this->resultLimit){
											break 3;
										}
										$totals = $this->_cleanUpTotalsWrapper('body', $totals);
										$totals = $this->_cleanUpTotalsWrapper('glove', $totals);
										break;
									}
								}

								foreach($waistArmors->data as $waist){
									if(array_diff($this->_getArraySkillIdFromArmor($waist), $totals['complete'])){
										$totals = $this->_totalSkills($waist, $totals, 'waist');
										if($this->_checkSkillTotals($skill_map, $totals, 'waist') === true){
											$found = $this->_prepareFoundArmors($totals, $skills->data, $weights, $head, $body, $glove, $waist, null);
											if(array_sum($found['incomplete']) <= $found['num_slots']['possible']){
												unset($found['num_slots']['possible']);
												$found = $this->_totalDecorations($found, $decorations->data, $skills->data);
												if($found){
													$foundArmors[] = $found;
												}
												if(count($foundArmors) >= $this->resultLimit){
													break 4;
												}
												$totals = $this->_cleanUpTotalsWrapper('glove', $totals);
												$totals = $this->_cleanUpTotalsWrapper('waist', $totals);
												break;
											}
										}

										foreach($legArmors->data as $leg){
											if(array_diff($this->_getArraySkillIdFromArmor($leg), $totals['complete'])){
												$totals = $this->_totalSkills($leg, $totals, 'leg');
												if($this->_checkSkillTotals($skill_map, $totals, 'leg') === true){
													$found = $this->_prepareFoundArmors($totals, $skills->data, $weights, $head, $body, $glove, $waist, $leg);
													if(array_sum($found['incomplete']) <= $found['num_slots']['possible']){
														unset($found['num_slots']['possible']);
														$found = $this->_totalDecorations($found, $decorations->data, $skills->data);
														if($found){
															$foundArmors[] = $found;
														}
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
										$totals = $this->_cleanUpTotalsWrapper('waist', $totals);
									}
								} //end waist
								$totals = $this->_cleanUpTotalsWrapper('glove', $totals);
							}
						} //end glove
						$totals = $this->_cleanUpTotalsWrapper('body', $totals);
					}
				} //end body

			} //end head
			// var_dump($foundArmors); exit;
			return $foundArmors;
		}

		private function _checkSkillTotals($skills, $results, $type){
			switch($type){
				case "charm":
							return (count($skills) === count($results['complete'])) ? true : $results;
							break;
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

		private function _getArraySkillIdFromArmor($armor){
			return array_keys(get_object_vars($armor->skill_tree_dimension));
		}

		private function _prepareFoundArmors($totals, $skillData, $weights, $head = null, $body = null, $glove = null, $waist = null, $leg = null){
			$point_values = array_values($totals['complete']);
			$num_slots = array(
				'alt'			 => array(
					'3'        => 0,
					'2'        => 0,
					'1'        => 0
				),
				'possible' => 0,
				'3'        => 0,
				'2'        => 0,
				'1'        => 0
			);
			$incomplete = array();
			foreach($point_values as $value){
				if($totals[$value]['total'] < (int) $skillData->$value->required){
					$incomplete[$value] = $skillData->$value->required - $totals[$value]['total'];
				}
			}
			asort($incomplete, SORT_NUMERIC);
			if(!is_null($head)){
				if($head->armor_dimension->num_slots > 0){
					$num_slots[$head->armor_dimension->num_slots] = $num_slots[$head->armor_dimension->num_slots] + 1;
					$num_slots = $this->_addAltSlots($num_slots, $head->armor_dimension->num_slots);
					$num_slots['possible'] += $head->armor_dimension->num_slots;
					if(isset($weights[$head->armor_dimension->num_slots]) && $weights[$head->armor_dimension->num_slots] > 0){
						$num_slots['possible'] += 1;
						unset($weights[$head->armor_dimension->num_slots]);
					}
				}
				$head = $head->fact->name;
			}
			if(!is_null($body)){
				if($body->armor_dimension->num_slots > 0){
					$num_slots[$body->armor_dimension->num_slots] = $num_slots[$body->armor_dimension->num_slots] + 1;
					$num_slots = $this->_addAltSlots($num_slots, $body->armor_dimension->num_slots);
					$num_slots['possible'] += $body->armor_dimension->num_slots;
					if(isset($weights[$body->armor_dimension->num_slots]) && $weights[$body->armor_dimension->num_slots] > 0){
						$num_slots['possible'] += 1;
						unset($weights[$body->armor_dimension->num_slots]);
					}
				}
				$body = $body->fact->name;
			}
			if(!is_null($glove)){
				if($glove->armor_dimension->num_slots > 0){
					$num_slots[$glove->armor_dimension->num_slots] = $num_slots[$glove->armor_dimension->num_slots] + 1;
					$num_slots = $this->_addAltSlots($num_slots, $glove->armor_dimension->num_slots);
					$num_slots['possible'] += $glove->armor_dimension->num_slots;
					if(isset($weights[$glove->armor_dimension->num_slots]) && $weights[$glove->armor_dimension->num_slots] > 0){
						$num_slots['possible'] += 1;
						unset($weights[$glove->armor_dimension->num_slots]);
					}
				}
				$glove = $glove->fact->name;
			}
			if(!is_null($waist)){
				if($waist->armor_dimension->num_slots > 0){
					$num_slots[$waist->armor_dimension->num_slots] = $num_slots[$waist->armor_dimension->num_slots] + 1;
					$num_slots = $this->_addAltSlots($num_slots, $waist->armor_dimension->num_slots);
					$num_slots['possible'] += $waist->armor_dimension->num_slots;
					if(isset($weights[$waist->armor_dimension->num_slots]) && $weights[$waist->armor_dimension->num_slots] > 0){
						$num_slots['possible'] += 1;
						unset($weights[$waist->armor_dimension->num_slots]);
					}
				}
				$waist = $waist->fact->name;
			}
			if(!is_null($leg)){
				if($leg->armor_dimension->num_slots > 0){
					$num_slots[$leg->armor_dimension->num_slots] = $num_slots[$leg->armor_dimension->num_slots] + 1;
					$num_slots = $this->_addAltSlots($num_slots, $leg->armor_dimension->num_slots);
					$num_slots['possible'] += $leg->armor_dimension->num_slots;
					if(isset($weights[$leg->armor_dimension->num_slots]) && $weights[$leg->armor_dimension->num_slots] > 0){
						$num_slots['possible'] += 1;
						unset($weights[$leg->armor_dimension->num_slots]);
					}
				}
				$leg = $leg->fact->name;
			}
			if($this->weapon > 0){
				$num_slots['possible'] += $this->weapon;
				$num_slots[$this->weapon] += 1;
			}
			$num_slots = $this->_addAltSlots($num_slots, $this->weapon);
			return array($head, $body, $glove, $waist, $leg, 'num_slots' => $num_slots, 'incomplete' => $incomplete);
		}

		private function _addAltSlots($num_slots, $slot_value){
			switch((int) $slot_value){
				case 3:
					$num_slots['alt'][2] += 1; //a 3 slot is equal to 1 2 slot and 1 1 slot
					$num_slots['alt'][1] += 4; //sum from above + 3 1 slots
					break;
				case 2:
					$num_slots['alt'][1] += 2;
					break;
			}
			return $num_slots;
		}

		private function _subAltSlots($num_slots, $slot_value, $isSub = false){
			if(array_sum($num_slots) > 0){
				switch((int) $slot_value){
					case 3:
						$num_slots['alt'][2] -= 1; //sub no longer available if a 3 slot is used
						$num_slots['alt'][1] -= 4;
						break;
					case 2:
						$num_slots['alt'][2] -= 1; //using a sub 2 slot from a 3 slot potentially
						$num_slots['alt'][1] -= 2; //which also means 2 1 slots are going to be gone also
						if($isSub && $num_slots[3] > 0){
							$num_slots[3] -= 1;
						}
						break;
					case 1:
						if($isSub){
							$num_slots['alt'][1] -= 1; //using a sub 1 slot from a 2 slot potentially
							$num_slots['alt'][2] -= 1; //potential 2 slot is gone and only a 1 slot is left
							if($num_slots[3] > 0){
								$num_slots[3] -= 1;
							} else if($num_slots[2] > 0){
								$num_slots[2] -= 1;
							}
						}
						break;
				}
			}
			return $num_slots;
		}

		private function _totalDecorations($found, $decorationsData, $skillData){
			// var_dump($decorationsData);
			// var_dump($found);
			foreach($found['incomplete'] as $skill => $remaining){
				$jewelIdx = 0;
				$jewel = $decorationsData->$skill->$jewelIdx;
				// var_dump($jewel);
				$incomplete_val = $found['incomplete'][$skill];
				while($incomplete_val > 0){
					$isChanged = false;
					$tmp_num_slots = $found['num_slots'];
					if($found['num_slots'][$jewel->num_slots] > 0){
						$found['num_slots'][$jewel->num_slots] -= 1;
						$found['num_slots'] = $this->_subAltSlots($found['num_slots'], $jewel->num_slots, false);
					} else if(!empty($found['num_slots']['alt'][$jewel->num_slots])){
						$found['num_slots'] = $this->_subAltSlots($found['num_slots'], $jewel->num_slots, true);
					} else if(!empty($decorationsData->$skill->{$jewelIdx + 1})){
						$jewel = $decorationsData->$skill->{++$jewelIdx};
					} else {
						if($found['incomplete'][$skill] < 1){
							//unset($found['incomplete'][$skill]);
						}
						break;
					}
					if($found['num_slots'] !== $tmp_num_slots){
						$isChanged = true;
					} else if(array_sum($found['num_slots']) <= 0){
						break; //stop if there are no more slots left. ignore alt slots if they are available
					}
					if($isChanged){
						$incomplete_val -= $jewel->point_value;
						$found['decorations'][] = $jewel->name;
					}
				}
			}
			if(isset($found['decorations'])){
				$found['decorations'] = array_count_values($found['decorations']);
				return $found;
			} else {
				return false;
			}
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
				if(empty($results['point_value'][$type])){
					$results['point_value'][$type] = array();
				}
				$results['point_value'][$type][] = "{$id}.{$skill->point_value}";
				if($results[$id]['total'] >= $this->skillThreshold && !in_array($id, $results['complete'])){
					$results['complete'][] = $id;
				}
			}
			$results = $this->_checkSkillTotals($skills, $results, $type);
			return $results;
		}
	}
