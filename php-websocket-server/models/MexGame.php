<?php
require_once __DIR__.'/../helpers/Game.php';

class MexGame extends Game {
	/**
	* A static property to store an in stance of itself.
	*/
	protected static $instance;
	
	/**
	* Creates an instance of itself the first time it is called and stores it.
	* After that it returns the same instance created at the first time it was called.
	*/
	public static function getInstance() {
		if (is_null(static::$instance)) {
			static::$instance = new static();
		}
		return static::$instance;
	}
	
	/**
	* Constants in the game.
	*/
	const AMOUNT_OF_DICES = 2;
	const NUM_ON_DICE = 6;
	const NUM_OF_ROLLS = 1;
	const NUM_OF_ROLLS_WHEN_MEX = 3;
	const MEX = 21;
	
	/**
	* The dice cup of the game containing the dices.
	* @var DiceCup
	*/
	protected $diceCup;
	
	/**
	* The score the previous player gives to the next player, which he/she claims to have thrown.
	* @var interger
	*/
	protected $passedValue;
	
	/**
	* The amount of times advice the previous player says is thrown higher than his actual passed value.
	* @var integer
	*/
	protected $advice;
	
	/**
	* The index of the player's turn
	* @var integer
	*/
	protected $playersTurn;
	
	/**
	* The amount of rolls done by the playing player.
	* @var integer
	*/
	protected $numberOfRolls;
	
	
	/**
	* Functions speak for themselves.
	*/
	function __construct() {
		parent::__construct();
		
		$this->diceCup = new DiceCup(self::AMOUNT_OF_DICES, self::NUM_ON_DICE);
		$this->passedValue = 0;
		$this->playersTurn = 0;
		$this->numberOfRolls = 0;
	}
	
	public function removePlayer($player) {
		if (in_array($player, $this->players)) {
			$key = array_keys($this->players, $player)[0];
			$this->players = array_values(array_diff($this->players, array($player)));
			if ($key < $this->playersTurn) {
				$this->previousPlayersTurn();
			}
		}
	}
	
	public function nextPlayersTurn() {
		if ($this->playersTurn < (count($this->players) - 1)) {
			$this->playersTurn++;
		} else {
			$this->playersTurn = 0;
		}
	}
	
	public function previousPlayersTurn() {
		if ($this->playersTurn == 0) {
			if (count($this->players) != 0) {
				$this->playersTurn = count($this->players) - 1;
			} else {
				$this->playersTurn = 0;
			}
		} else {
			$this->playersTurn--;
		}
	}
	
	public function getPlayersTurn() {
		return $this->playersTurn;
	}
	
	public function rollDices() {
		if ($this->passedValue == self::MEX) {
			if ($this->numberOfRolls < self::NUM_OF_ROLLS_WHEN_MEX) {
				$this->diceCup->rollDices();
				$this->numberOfRolls++;
				return true;
			} else {
				return false;
			}
		} else {
			if ($this->numberOfRolls < self::NUM_OF_ROLLS) {
				$this->diceCup->rollDices();
				$this->numberOfRolls++;
				return true;
			} else {
				return false;
			}
		}
	}
	
	public function getThrownScore() {
		$values = $this->diceCup->getValues();
		if ($values[0] == $values[1]) {
			return $values[0] * 100;
		} elseif ($values[0] > $values[1]) {
			return $values[0] * 10 + $values[1];
		} else {
			return $values[1] * 10 + $values[0];
		}
	}
	
	public function getMayRollAgain() {
		if ($this->passedValue == self::MEX) {
			if ($this->numberOfRolls < self::NUM_OF_ROLLS_WHEN_MEX) {
				return true;
			} else {
				return false;
			}
		} else {
			if ($this->numberOfRolls < self::NUM_OF_ROLLS) {
				return true;
			} else {
				return false;
			}
		}
	}
	
	public function getHasToRoll() {
		if ($this->numberOfRolls == 0 && $this->passedValue == self::MEX) {
			return true;
		} else {
			return false;
		}
	}
	
	public function resetNumberOfRolls() {
		$this->numberOfRolls = 0;
	}
	
	public function getPassedValue() {
		return $this->passedValue;
	}
	
	public function getAdvice() {
		return $this->advice;
	}
	
	public function setPassedValue($value, $adv=0) {
		if ($this->passedValue != self::MEX) {
			if ($value > $this->passedValue || $value == self::MEX) {
				if ($value > 600) {
					$this->passedValue = self::MEX;
				} else {
					$this->passedValue = $value;
				}
				$this->advice = $adv;
				return true;
			} else {
				return false;
			}
		} else {
			if ($value > 600 || $value == self::MEX) {
				$this->advice = $adv;
				return true;
			} else {
				return false;
			}
		}
	}
	public function resetPassedValue() {
		$this->passedValue = 0;
		$this->advice = 0;
	}
	
	public function checkIfPassedValueTrue() {
		if ($this->passedValue == self::MEX) {
			if ($this->getThrownScore() == self::MEX) {
				return true;
			} else {
				return false;
			}
		} else {
			if ($this->passedValue <= $this->getThrownScore()) {
				return true;
			} else {
				return false;
			}
		}
	}
	
	public function getState() {
		return array(
			'playersturn' => $this->getPlayersTurn(), 
			'passedvalue' => $this->getPassedValue(),
			'advice' 	  => $this->getAdvice(), 
			'hastoroll'   => $this->getHasToRoll()
			);
	}
}
?>