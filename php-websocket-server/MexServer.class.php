<?php
require_once __DIR__ . '/WebSocketServer.class.php';

/**
* Extends WebSocketServer, which is (with WebSocketRequest & WebSocketClient) based on felladrin's from github.com.
* (source: https://github.com/felladrin/php-websocket-server/tree/master/example/server)
*/
class MexServer extends WebSocketServer {
    protected $debugMode = true;

    protected $foldersToAutoload = array('helpers', 'models', 'controllers');

	/**
	* Decodes the recieved message. (directs to the given action of the given controller)
	*/
    protected function onMessageRecieved(WebSocketClient $sender, $message) {
        $msg = WebSocketRequest::decode($sender, $message);
    }

	/**
	* Set the sender of the WebSocketRequest to the new client
	* and broadcasts its id to all the other clients connected to the server.
	*/
    protected function onClientConnected(WebSocketClient $newClient) {
        WebSocketRequest::$sender = $newClient;
		WebSocketRequest::broadcastExcludingSender('user-connected', array('id' => $newClient->id));
    }
	
	/**
	* Set the sender of the WebSocketRequest to the left client,
	* sends its id and name to all the other clients,
	* removes, when still participating, the user from the game
	* and broadcasts the new formation of player in the game to all clients.
	*/
    protected function onClientDisconnected(WebSocketClient $leftClient) {
        WebSocketRequest::$sender = $leftClient;
        WebSocketRequest::broadcastExcludingSender('user-disconnected', array('user' => array('id' => $leftClient->id, 'name' => WebSocketRequest::$sender->get('name'))));
		MexGame::getInstance()->removePlayer($leftClient->get('name'));
		WebSocketRequest::broadcast('players-update', array('players' => MexGame::getInstance()->getPlayers()));
    }
}
?>