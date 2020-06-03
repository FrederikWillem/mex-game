<?php
/**
* Starts the websocket server.
*/
require_once __DIR__ . "/MexServer.class.php";
require_once __DIR__ . "/array_column.php";

const IP_SERVER = '0.0.0.0';
const PORT = 3000;

try
{
    MexServer::Instance()->start(IP_SERVER, PORT);
}
catch (Exception $e)
{
    echo 'Fatal exception occured: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine() . "\n";
}

?>