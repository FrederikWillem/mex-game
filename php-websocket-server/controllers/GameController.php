<?php

class GameController {
	/**
	* Gets name of the webclient, 
	* adds him/her to the game 
	* and broadcasts the new formation of players to all clients.
	*/
	public function actionJoinGame() {
		$name = WebSocketRequest::$sender->get('name');
		MexGame::getInstance()->addPlayer($name);
		WebSocketRequest::broadcast('players-update', array('players' => MexGame::getInstance()->getPlayers(), 'playersturn' => MexGame::getInstance()->getPlayersTurn()));
	}
	
	/**
	* Gets name of the webclient, 
	* removes him/her from the game 
	* and broadcasts the new fomration of players to all clients.
	*/
	public function actionExitGame() {
		$name = WebSocketRequest::$sender->get('name');
		MexGame::getInstance()->removePlayer($name);
		WebSocketRequest::broadcast('players-update', array('players' => MexGame::getInstance()->getPlayers(), 'playersturn' => MexGame::getInstance()->getPlayersTurn()));
	}
	
	/**
	* Tells every client to cover the dices,
	* reset the passed value to start a new round,
	* rolls the dices,
	* gets the rolled score,
	* gets if player may roll again
	* and replies the score back to the 'roller'.
	*/
	public function actionFirstRollDices() {
		WebSocketRequest::broadcast('cover-dices', array());
		MexGame::getInstance()->resetPassedValue();
		MexGame::getInstance()->rollDices();
		$throw = MexGame::getInstance()->getThrownScore();
		$mayRollAgain = MexGame::getInstance()->getMayRollAgain();
		WebSocketRequest::reply('throw-response', array('score' => $throw, 'mayrollagain' => $mayRollAgain));
	}
	
	/**
	* Rolls the dices,
	* gets the rolled score,
	* gets if player may roll again
	* and replies the score back to the 'roller'.
	*/
	public function actionRollDices() {
		MexGame::getInstance()->rollDices();
		$throw = MexGame::getInstance()->getThrownScore();
		$mayRollAgain = MexGame::getInstance()->getMayRollAgain();
		WebSocketRequest::reply('throw-response', array('score' => $throw, 'mayrollagain' => $mayRollAgain));
	}
	
	/**
	* Gets the passed value,
	* gets the amount of times advice given,
	* checks if passed value is valid.
	* If so, it 
	* 	sets the turn to the next player,
	*	reset the number of rolls done
	*	and broadcasts the new state of the game to all clients.
	* Else, it
	*	replies a pass fail back to the 'passer'.
	*/
	public function actionPassCup() {
		$value = (int) WebSocketRequest::getParameter('value');
		$advice = (int) WebSocketRequest::getParameter('advice');
		if (MexGame::getInstance()->setPassedValue($value, $advice)) {
			MexGame::getInstance()->nextPlayersTurn();
			MexGame::getInstance()->resetNumberOfRolls();
			WebSocketRequest::broadcast('state-update', MexGame::getInstance()->getState());
		} else {
			WebSocketRequest::reply('pass-fail', array());
		}
	}
	
	/**
	* Check if the passed value is equal or higher then the thrown score.
	* If not, it
	* 	sets the turn back to the previous player.
	* Then it resets the number of rolls done,
	* broadcasts the drawn score, with the player that lost, to all clients
	* and broadcasts the new state of the gam to all clients.
	*/
	public function actionDraw() {
		if (!MexGame::getInstance()->checkIfPassedValueTrue()) {
			MexGame::getInstance()->previousPlayersTurn();
		}
		MexGame::getInstance()->resetNumberOfRolls();
		WebSocketRequest::broadcast('draw-response', array('score' => MexGame::getInstance()->getThrownScore(), 'loser' => MexGame::getInstance()->getPlayersTurn()));
		WebSocketRequest::broadcast('state-update', MexGame::getInstance()->getState());
	}
}

?>