<?php
/**
* Taken from a previous Yathzee game.
*/
Class NumberDice {
	// could make parent class Dice, but for now bit unnecessary 
	private $amountOfNumbers;
	private $value;
	
	public function __construct($amountOfNumbers){
		$this->amountOfNumbers = $amountOfNumbers;
		$this->value = 0;
	}
	public function getValue(){
		return $this->value;
	}
	public function getAmountOfNumbers(){
		return $this->amountOfNumbers;
	}
	public function rollDice(){
		//get random number as if dice is rolled
		$this->value = rand(1,$this->amountOfNumbers);
	}
}
?>