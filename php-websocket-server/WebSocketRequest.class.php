<?php

/**
* Taken with minor adjustments from https://github.com/felladrin/php-websocket-server/tree/master/example/server
*/
class WebSocketRequest
{
    /** @var stdClass[] $controllers */
    private static $controllers = array();//'UserController','MessageController');

    /** @var array $parameters */
    private static $parameters = array();

    /** @var WebSocketClient $sender */
    public static $sender;

    /**
     * Returns the parameter an specifc parameter value. If the paramter does not exist, returns the default value.
     *
     * @param string $name Name of the parameter to be retrieved.
     * @param mixed $defaultValue Value to be returned in case the parameter does not exist.
     *
     * @return mixed|null
     */
    public static function getParameter($name, $defaultValue = null)
    {
        if (array_key_exists($name, static::$parameters))
        {
            return static::$parameters[$name];
        }
        else
        {
            return $defaultValue;
        }
    }

    /**
     * Sends a WebSocketRequest to all connected sockets.
     *
     * @param $controller	--> removed
     * @param $action
     * @param array $parameters
     */
    public static function broadcast($action, array $parameters = array())
    {
        $message = static::encode($action, $parameters);
        WebSocketServer::Instance()->broadcast($message);
    }

    /**
     * Sends a WebSocketRequest to all connected sockets, except to the sender.
     *
     * @param $controller	--> removed
     * @param $action
     * @param array $parameters
     */
    public static function broadcastExcludingSender($action, array $parameters = array())
    {
        $message = static::encode($action, $parameters);
        $server = WebSocketServer::Instance();
        $sender = static::$sender;

        foreach ($server->getClients() as $client)
        {
            if ($client != $sender)
            {
                $server->send($client->socket, $message);
            }
        }
    }

    /**
     * Sends a WebSocketRequest back to the sender.
     *
     * @param $controller	--> removed
     * @param $action
     * @param array $parameters
     */
    public static function reply($action, array $parameters = array())
    {
        $message = static::encode($action, $parameters);
        $sender = static::$sender;

        WebSocketServer::Instance()->send($sender->socket, $message);
    }

    /**
     * Encodes a WebSocketRequest in JSON format.
     *
     * @param $controller	--> removed
     * @param $action
     * @param array $parameters
     *
     * @return string
     */
    private static function encode($action, array $parameters = array())
    {
        return json_encode(array(
            'action' => $action,
            'parameters' => $parameters
        ));
    }

    /**
     * Decodes a JSON WebSocketRequest and calls runs the spectific controller action.
     *
     * @param WebSocketClient $sender
     * @param $message
     */
    public static function decode(WebSocketClient $sender, $message)
    {
        $request = json_decode($message, true);

        if (is_null($request) || empty($request['controller']) || empty($request['action']))
        {
            return;
        }

        $controllerName = str_replace(' ', '', ucwords(str_replace('-', ' ', $request['controller']))) . 'Controller';
        $actionName = 'action' . str_replace(' ', '', ucwords(str_replace('-', ' ', $request['action'])));

        if (!isset(static::$controllers[$controllerName]))
        {
            if (!class_exists($controllerName))
            {
                return;
            }

            static::$controllers[$controllerName] = new $controllerName();
        }

        $controller = static::$controllers[$controllerName];
        static::$sender = $sender;

        if (!empty($request['parameters']) && is_array($request['parameters']))
        {
            static::$parameters = $request['parameters'];
        }

        if (is_callable(array($controller, $actionName)))
        {
            $controller->$actionName();
        }
    }
}

?>