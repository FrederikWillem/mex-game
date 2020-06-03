<?php

/**
* Taken from https://github.com/felladrin/php-websocket-server/tree/master/example/server
*/
class WebSocketClient
{
    /**
     * Auto-incremented id for identifying the next client.
     *
     * @var integer
     */
    private static $nextId = 0;

    /**
     * Reference to server that created the client.
     *
     * @var WebSocketServer
     */
    public $server;

    /**
     * Client id.
     *
     * This starts from one and is incremented for every connecting user.
     *
     * @var integer
     */
    public $id;

    /**
     * Client socket.
     *
     * @var resource
     */
    public $socket;

    /**
     * Client state.
     *
     * One of WebSocketClient::STATE_.. constants.
     *
     * @var integer
     */
    public $state;

    /**
     * The ip of the client.
     *
     * @var string
     */
    public $ip;

    /**
     * The port of the client.
     *
     * @var integer
     */
    public $port;

    /**
     * The time data was last recieved from the client.
     *
     * @var integer
     */
    public $lastRecieveTime = 0;

    /**
     * Last time data was sent to this client.
     *
     * @var integer
     */
    public $lastSendTime = 0;

    /**
     * Any data associated with the user.
     *
     * @var mixed
     */
    public $data = array();

    /**
     * User is connecting, handshake not yet performed.
     */
    const STATE_CONNECTING = 0;

    /**
     * Connection is valid.
     */
    const STATE_OPEN = 1;

    /**
     * Connection has been closed.
     */
    const STATE_CLOSED = 2;

    /**
     * Constructor, sets the server that spawned the client and the socket.
     *
     * @param WebSocketServer $server Parent server
     * @param resource $socket User socket
     */
    public function __construct(WebSocketServer $server, $socket)
    {
        static::$nextId++;

        $this->server = $server;
        $this->id = static::$nextId;
        $this->socket = $socket;
        $this->state = static::STATE_CONNECTING;
        $this->lastRecieveTime = time();

        socket_getpeername($socket, $this->ip, $this->port);
    }

    /**
     * Sets a client variable. If the specified key already exists, the old value will be overwritten.
     *
     * @param string $key Variable key.
     * @param mixed $value Variable value.
     */
    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * Returns a client variable.
     *
     * @param string $key Variable key.
     * @param mixed $defaultValue Default value returned when variable does not exist.
     *
     * @return mixed
     */
    public function get($key, $defaultValue = null)
    {
        return (array_key_exists($key, $this->data)) ? $this->data[$key] : $defaultValue;
    }

    /**
     * Disconnects the client.
     */
    public function disconnect()
    {
        if ($this->state == static::STATE_CLOSED)
        {
            return;
        }

        $this->server->disconnectClient($this->socket);
    }

    /**
     * Does the magic handshake to begin the connection.
     *
     * @param string $buffer Buffer sent by the client
     * @return bool Was the handshake successful
     * @throws Exception If something goes wrong
     */
    public function performHandshake($buffer)
    {
        if ($this->state != static::STATE_CONNECTING)
        {
            throw new Exception('Unable to perform handshake, client is not in connecting state');
        }

        $headers = $this->parseRequestHeader($buffer);
        $key = $headers['Sec-WebSocket-Key'];
		//var_dump($headers);
		
        $hash = base64_encode(sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));

        $headers = array(
            'HTTP/1.1 101 Switching Protocols',
            'Upgrade: websocket',
            'Connection: Upgrade',
            'Sec-WebSocket-Accept: ' . $hash
        );

        $headers = implode("\r\n", $headers) . "\r\n\r\n";

        $left = strlen($headers);

        do
        {
            $sent = @socket_send($this->socket, $headers, $left, 0);

            if ($sent === false)
            {
                $error = $this->server->getLastError();

                throw new Exception('Sending handshake failed: : ' . $error->message . ' [' . $error->code . ']');
            }

            $left -= $sent;

            if ($sent > 0)
            {
                $headers = substr($headers, $sent);
            }
        }
        while ($left > 0);

        $this->state = static::STATE_OPEN;
    }

    /**
     * Parses the request header into resource, headers and security code
     *
     * @param string $request The request
     * @return array Array containing the resource, headers and security code
     */
    private function parseRequestHeader($request)
    {
        $headers = array();

        foreach (explode("\r\n", $request) as $line)
        {
            if (strpos($line, ': ') !== false)
            {
                list($key, $value) = explode(': ', $line);

                $headers[trim($key)] = trim($value);
            }
        }

        return $headers;
    }
}

?>