<?php

require_once __DIR__ . "/WebSocketClient.class.php";
require_once __DIR__ . "/WebSocketRequest.class.php";

/**
* Taken from https://github.com/felladrin/php-websocket-server/tree/master/example/server
*/
abstract class WebSocketServer
{
    /**
     * Host to bind to.
     * @var string
     */
    protected $host;

    /**
     * Port number where to bind to.
     * @var integer
     */
    protected $port;

    /**
     * Array of connected clients.
     * @var WebSocketClient[]
     */
    protected $clients = array();

    /**
     * The master socket acting as server.
     * @var resource
     */
    protected $socket;

    /**
     * Array of all connected sockets, includes the master.
     * @var resource[]
     */
    protected $sockets = array();

    /**
     * Whether it's on Debug Mode.
     * @var bool $debugMode
     */
    protected $debugMode = false;

    /**
     * Register folder paths to autoload .php files from. (Relative to the path of the class extending WebSocketServer)
     * @var array $foldersToAutoload
     */
    protected $foldersToAutoload = array('models', 'controllers');

    protected $bufferSize = 4096;

    const FIN = 128;
    const MASK = 128;
    const OPCODE_CONTINUATION = 0;
    const OPCODE_TEXT = 1;
    const PAYLOAD_LENGTH_16 = 126;
    const PAYLOAD_LENGTH_63 = 127;

    /** @var static|null */
    protected static $instance = null;

    /**
     * @return static
     */
    public static function Instance()
    {
        if (is_null(static::$instance))
        {
            static::$instance = new static();
        }

        return static::$instance;
    }

    protected function __construct() { }

    protected function __clone() { }

    protected function __wakeup() { }

    /**
     * Called when a client sends a message to the server.
     *
     * @param WebSocketClient $sender Client that sent the message
     * @param string $message Sent message
     * @return mixed
     */
    abstract protected function onMessageRecieved(WebSocketClient $sender, $message);

    /**
     * Called when a new client connects to the server.
     *
     * @param WebSocketClient $client Client that connected
     */
    abstract protected function onClientConnected(WebSocketClient $client);

    /**
     * Called when a  client disconnects from the server.
     *
     * @param WebSocketClient $client Client that disconnected
     */
    abstract protected function onClientDisconnected(WebSocketClient $client);

    /**
     * Returns array of connected clients
     *
     * @return WebSocketClient[] Array of connected clients
     */
    public function getClients()
    {
        return $this->clients;
    }

    /**
     * Returns the number of connected clients
     *
     * @return integer Number of clients
     */
    public function getClientCount()
    {
        return count($this->clients);
    }

    /**
     * Returns last socket error as an object.
     *
     * The object is a basic stdClass with parameters:
     * - code: the code of the error
     * - message: translated error code as message
     *
     * @param resource $socket Optional socket resource
     * @return stdClass Error as stdClass instance with fields code and message
     */
    public static function getLastError($socket = null)
    {
        $lastErrorCode = socket_last_error($socket);
        $lastErrorMessage = socket_strerror($lastErrorCode);

        $error = new stdClass();
        $error->code = $lastErrorCode;
        $error->message = $lastErrorMessage;

        return $error;
    }

    /**
     * Starts the server by binding to a port
     *
     * @param string $host Socket host to bind to, defaults to localhost
     * @param integer $port Port to bind to, defaults to 8080
     * @param integer $maxConnections Max number of incoming backlog connections
     * @throws Exception If something goes wrong
     */
    public function start($host = 'localhost', $port = 8080, $maxConnections = SOMAXCONN)
    {
        set_time_limit(0);
        ob_implicit_flush();

        $this->host = $host;
        $this->port = $port;

        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        if ($this->socket === false)
        {
            $error = static::getLastError();

            throw new Exception('Creating socket failed: ' . $error->message . ' [' . $error->code . ']');
        }

        $this->sockets[] = $this->socket;

        if (socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1) === false)
        {
            $error = static::getLastError($this->socket);

            throw new Exception('Setting socket option to reuse address to true failed: ' . $error->message . ' [' . $error->code . ']');
        }

        if (socket_bind($this->socket, $this->host, $this->port) === false)
        {
            $error = static::getLastError($this->socket);

            throw new Exception('Binding to port ' . $this->port . ' on host "' . $this->host . '" failed: ' . $error->message . ' [' . $error->code . ']');
        }

        if (socket_listen($this->socket, $maxConnections) === false)
        {
            $error = static::getLastError($this->socket);

            throw new Exception('Starting to listen on the socket on port ' . $this->port . ' and host "' . $this->host . '" failed: ' . $error->message . ' [' . $error->code . ']');
        }

        $this->log(get_called_class() . " started listening connections on {$this->host}:{$this->port}");

        $this->registerAutoload();

        $this->run();
    }

    /**
     * Runs the server as an infinite loop
     *
     * @throws Exception
     * @return void
     */
    protected function run()
    {
        while (true)
        {
            $changedSockets = $this->sockets;

            $write = $except = $tv = $tvu = null;

            $result = socket_select($changedSockets, $write, $except, $tv, $tvu);

            if ($result === false)
            {
                socket_close($this->socket);

                $error = static::getLastError($this->socket);

                throw new Exception('Checking for changed sockets failed: ' . $error->message . ' [' . $error->code . ']');
            }

            foreach ($changedSockets as $socket)
            {
                if ($socket == $this->socket)
                {
                    $newSocket = socket_accept($this->socket);

                    if ($newSocket !== false)
                    {
                        $this->connectClient($newSocket);
                    }
                    else
                    {
                        $error = static::getLastError($this->socket);

                        trigger_error('Failed to accept incoming client: ' . $error->message . ' [' . $error->code . ']', E_USER_WARNING);
                    }
                }
                else
                {
                    $client = $this->getClientBySocket($socket);

                    if (!isset($client))
                    {
                        trigger_error('Failed to match given socket to client', E_USER_WARNING);

                        socket_close($socket);

                        continue;
                    }

                    $buffer = '';
                    $message = '';

                    $bytes = @socket_recv($socket, $buffer, 4096, 0);

                    if ($bytes === false)
                    {
                        $error = static::getLastError($this->socket);

                        trigger_error('Failed to receive data from client #' . $client->id . ': ' . $error->message . ' [' . $error->code . ']', E_USER_WARNING);

                        $this->disconnectClient($client->socket);

                        continue;
                    }

                    $len = ord($buffer[1]) & 127;

                    $masks = null;
                    $data = null;

                    if ($len === 126)
                    {
                        $masks = substr($buffer, 4, 4);
                        $data = substr($buffer, 8);
                    }
                    else if ($len === 127)
                    {
                        $masks = substr($buffer, 10, 4);
                        $data = substr($buffer, 14);
                    }
                    else
                    {
                        $masks = substr($buffer, 2, 4);
                        $data = substr($buffer, 6);
                    }

                    for ($index = 0; $index < strlen($data); $index++)
                    {
                        $message .= $data[$index] ^ $masks[$index % 4];
                    }

                    if ($bytes == 0)
                    {
                        $this->disconnectClient($socket);
                    }
                    else
                    {
                        if ($client->state == WebSocketClient::STATE_OPEN)
                        {
                            $client->lastRecieveTime = time();

                            $this->debug('Received from socket #' . $client->id . ': ' . $message);

                            $this->onMessageRecieved($client, $message);
                        }
                        else if ($client->state == WebSocketClient::STATE_CONNECTING)
                        {
                            $client->performHandshake($buffer);
                        }
                    }
                }
            }
        }
    }

    /**
     * Connects a client by socket.
     *
     * Creates a new instance of the WebSocketClient class and adds it to the list
     * of clients. Also adds the socket to the list of sockets.
     *
     * @param resource $socket Socket to use
     */
    protected function connectClient($socket)
    {
        $client = new WebSocketClient($this, $socket);

        $this->clients[] = $client;
        $this->sockets[] = $socket;

        $this->debug('Socket #' . $client->id . ' connected.');

        $this->onClientConnected($client);
    }

    /**
     * Disconnects a client by socket.
     *
     * @param resource $clientSocket Socket to use
     */
    public function disconnectClient($clientSocket)
    {
        foreach ($this->sockets as $socketKey => $socket)
        {
            if ($socket === $clientSocket)
            {
                socket_close($clientSocket);

                unset($this->sockets[$socketKey]);
            }
        }

        foreach ($this->clients as $clientKey => $client)
        {
            if ($client->socket === $clientSocket)
            {
                $this->debug('Socket #' . $client->id . ' disconnected.');

                $this->onClientDisconnected($client);

                $this->clients[$clientKey]->state = WebSocketClient::STATE_CLOSED;

                unset($this->clients[$clientKey]);
            }
        }
    }

    /**
     * Returns client by socket reference.
     *
     * @param resource $socket Socket resource
     * @return WebSocketClient The client on the socket or null if not found
     */
    protected function getClientBySocket($socket)
    {
        foreach ($this->clients as $client)
        {
            if ($client->socket == $socket)
            {
                return $client;
            }
        }

        return null;
    }

    /**
     * Sends a message to given socket
     *
     * @param resource $socket Socket to send the message to
     * @param mixed $message Message to send
     * @return bool
     */
    public function send($socket, $message)
    {
        $opcode = static::OPCODE_TEXT;

        if (is_object($message))
        {
            $message = (string)$message;
        }

        $messageLength = strlen($message);

        $frameCount = ceil($messageLength / $this->bufferSize);

        if ($frameCount == 0)
        {
            $frameCount = 1;
        }

        $maxFrame = $frameCount - 1;
        $lastFrameBufferLength = ($messageLength % $this->bufferSize) != 0 ? ($messageLength % $this->bufferSize) : ($messageLength != 0 ? $this->bufferSize : 0);

        for ($i = 0; $i < $frameCount; $i++)
        {
            $fin = $i != $maxFrame ? 0 : static::FIN;
            $opcode = $i != 0 ? static::OPCODE_CONTINUATION : $opcode;

            $bufferLength = $i != $maxFrame ? $this->bufferSize : $lastFrameBufferLength;

            if ($bufferLength <= 125)
            {
                $payloadLength = $bufferLength;
                $payloadLengthExtended = '';
                $payloadLengthExtendedLength = 0;
            }
            else if ($bufferLength <= 65535)
            {
                $payloadLength = static::PAYLOAD_LENGTH_16;
                $payloadLengthExtended = pack('n', $bufferLength);
                $payloadLengthExtendedLength = 2;
            }
            else
            {
                $payloadLength = static::PAYLOAD_LENGTH_63;
                $payloadLengthExtended = pack('xxxxN', $bufferLength);
                $payloadLengthExtendedLength = 8;
            }

            $buffer = pack('n', (($fin | $opcode) << 8) | $payloadLength) . $payloadLengthExtended . substr($message, $i * $this->bufferSize, $bufferLength);

            $left = 2 + $payloadLengthExtendedLength + $bufferLength;

            do
            {
                $sent = @socket_send($socket, $buffer, $left, 0);
                if ($sent === false)
                {
                    return false;
                }

                $left -= $sent;
                if ($sent > 0)
                {
                    $buffer = substr($buffer, $sent);
                }
            }
            while ($left > 0);
        }

        $client = $this->getClientBySocket($socket);

        $clientId = -1;

        if ($client != null)
        {
            $client->lastSendTime = time();
            $clientId = $client->id;
        }

        $this->debug('Sending to socket #' . $clientId . ': ' . $message);

        return true;
    }

    /**
     * Sends a message to all connected sockets.
     *
     * @param mixed $message Message to send
     * @return bool
     */
    public function broadcast($message)
    {
        foreach ($this->clients as $client)
        {
            $this->send($client->socket, $message);
        }
    }

    /**
     * Logs a message to console.
     *
     * @param string $message Message to log
     */
    public function log($message)
    {
        echo '[' . gmdate('Y-m-d H:i:s') . ' GMT] ' . $message . PHP_EOL;
    }

    /**
     * Logs a message to console if running on Debug Mode.
     *
     * @param string $message Message to log
     */
    public function debug($message)
    {
        if ($this->debugMode)
        {
            $this->log($message);
        }
    }

    /**
     * Registers the folders to have their php files autoloaded.
     */
    protected function registerAutoload()
    {
		$fta = $this->foldersToAutoload;
		
        //spl_autoload_register(function($class)
        //{
            foreach ($fta as $folder)
            {
                //if (file_exists(__DIR__ . "/$folder/$class.php"))
                //{
                    /** @noinspection PhpIncludeInspection */
					$directory = __DIR__ . "/$folder";
					$files = array_diff(scandir($directory), array('..', '.'));
					foreach($files as $file){
						require_once __DIR__ . "/$folder/$file";
					}
                //}
            }
        //});
    }
}

?>