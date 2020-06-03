<?php
/**
* Taken from a previous Yahtzee game.
*/
abstract class Game {
	/**
	* Array with the players in the game.
	* @var array of the chosen player interfaces.
	*/
	protected $players;
	
	/**
	* The number of the current turn of the game.
	* @var integer 
	*/
	protected $turn;
	
	/**
	* The maximum amount of turns in the game.
	* @var integer
	*/
	const NUMBER_OF_TURNS = 0;
	
	function __construct() {
		$this->players = array();
		$this->turn = 0;
	}
	
	/**
	* Adds a player to the game
	*/
	public function addPlayer($player) {
		if (!in_array($player, $this->players)) {
			array_push($this->players, $player);
			return true;
		} else {
			return false;
		}
	}
	
	/**
	* Adds a player to the game at the first place
	*/
	public function addPlayerAtBegin($player) {
		if (!in_array($player, $this->players)) {
			array_unshift($this->players, $player);
			return true;
		} else {
			return false;
		}
	}
	
	/**
	* Adds a player to the game at a random place
	*/
	public function addPlayerAtRandom($player) {
		if (!in_array($player,$this->players)) {
			$count = count($this->players);
			$rand = rand(0, $count);
			array_splice($this->players, $rand, 0, $player);
			return true;
		} else {
			return false;
		}
	}
	
	/**
	* Removes a player from the game
	*/
	public function removePlayer($player) {
		if (in_array($player, $this->players)) {
			$this->players = array_values(array_diff($this->players, array($player)));
		}
	}
	
	/**
	* returns all the players
	*/
	public function getPlayers() {
		return $this->players;
	}
	
	/**
	* returns the player from the given index
	*/
	public function getPlayer($index) {
		return $this->player[$index];
	}
	
	/**
	* returns the current turn of the game
	*/
	public function getTurn() {
		return $this->turn;
	}
	
	/**
	* returns the maximum amount of turn in the game
	*/
	public function getNumberOfTurns() {
		return self::NUMBER_OF_TURNS;
	}
	
	/**
	* Adds a turn
	*/
	public function addOneTurn() {
		if ($this->turn < self::NUMBER_OF_TURNS) {
			$this->turn++;
			return true;
		} else {
			return false;
		}
	}
}
?>