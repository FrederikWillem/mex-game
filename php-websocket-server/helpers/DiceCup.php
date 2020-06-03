<?php
/**
* Taken from a previous Yahtzee game.
*/
class DiceCup {
	private $dices;
	private $holdDices;
	
	public function __construct($numberOfDices, $numbersOnDice){
		$this->holdDices = array();
		//create dices
		$this->dices = array();
		for($i=0;$i<$numberOfDices;$i++){
			array_push($this->dices, new NumberDice($numbersOnDice));
		}
	}
	public function getNumberOfDices(){
		return count($this->dices);
	}
	public function setHoldDices($holdArray){
		$this->holdDices = $holdArray;
	}
	public function rollDices(){
		for($i=0;$i<count($this->dices);$i++){
			// go through all dices
			$dice = $i+1;
			if(!in_array($dice,$this->holdDices)){
				//if not holded, roll!!
				$this->dices[$i]->rollDice();
			}
		}
	}
	public function getValues(){
		$val_arr = array();
		for($i=0;$i<count($this->dices);$i++){
			array_push($val_arr, $this->dices[$i]->getValue());
		}
		return $val_arr;
	}
}
?>