<?php
declare(strict_types=1);

use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use UltimateTicTacToe\Room\Room;
use UltimateTicTacToe\Server\Server;

require dirname(__DIR__) . '/vendor/autoload.php';

const WS_PORT = 1337;

$rooms = [
    1 => new Room(1),
    2 => new Room(2),
    3 => new Room(3),
];

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Server($rooms)
        )
    ),
    WS_PORT
);

$server->run();