<?php

class LobbyController {
	/**
	* Gets given name
	* and gets a list of the users already in the lobby.
	* If a name is given and it is not already taken,
	*	it sets the name to the web socket client,
	*	broadcasts the new user to the other users,
	*	replies that the user setup was a success allong with a list of the users in the lobby,
	* 	replies the list of players in the game 
	* 	and replies the state of the game.
	* Else 
	*	it replies that the user setup was a failure.
	*/
    public function actionSetup() {
		$name = WebSocketRequest::getParameter('name');
		$userlist = Lobby::getInstance()->getUserList();
		if (!is_null($name) && trim($name) != "" && !in_array($name, array_column($userlist, 'name'))) {
			WebSocketRequest::$sender->set('name', $name);
			WebSocketRequest::broadcastExcludingSender('add-user', array('user' => array('id' => WebSocketRequest::$sender->id,'name' => $name)));
			WebSocketRequest::reply('setup-user', array('userset' => true, 'userlist' => Lobby::getInstance()->getUserList()));
			WebSocketRequest::reply('players-update', array('players' => MexGame::getInstance()->getPlayers(), 'playersturn' => MexGame::getInstance()->getPlayersTurn()));
			WebSocketRequest::reply('state-update', MexGame::getInstance()->getState());
		} else {
			WebSocketRequest::reply('setup-user', array('userset' => false));
		}
    }
	
	/**
	* Gets the name of the sender,
	* gets the text of the message
	* and gets the current time.
	* If the message contains text,
	* 	it broadcasts the message to all the other clients.
	*/
	public function actionSubmitMessage() {
        $author = WebSocketRequest::$sender->get('name');
        $text = WebSocketRequest::getParameter('message');
        $datetime = date(Lobby::DATETIME_FORMAT);
		if (strlen(trim($text)) != 0) {
			WebSocketRequest::broadcast('new-message', array(message => array('author' => $author, 'text' => $text, 'datetime' => $datetime)));
		}
    }
}

?>