<?php
class Lobby {
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
	* Constants of the lobby.
	*/
	const DATETIME_FORMAT = 'H:i';
	
	/**
	* Gets all users in the websocket, who have set their name.
	*/
    public function getUserList() {
        $clients = MexServer::Instance()->getClients();
        $userList = array();

        foreach ($clients as $client) {
			if ($client->get('name')) {
				$userList[] = array(
					'id' => $client->id,
					'name' => $client->get('name'),
				);
			}
        }

        return $userList;
    }
}
?>