<?php

declare(strict_types=1);

namespace UltimateTicTacToe\Server;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use UltimateTicTacToe\Game\Game;
use UltimateTicTacToe\Room\Room;

class Server implements MessageComponentInterface
{
    private const JOIN = 'join';
    private const CHECK = 'check';
    private const NEW = 'new';

    private \SplObjectStorage $clients;

    /** @var Room[] */
    private array $rooms;

    /** @var Game[] */
    private array $games;

    /**
     * Server constructor.
     *
     * @param Room[] $rooms
     */
    public function __construct(array $rooms)
    {
        $this->clients = new \SplObjectStorage;
        $this->rooms = $rooms;
    }

    public function onOpen(ConnectionInterface $conn): void
    {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        echo 'New connection! (' . $conn->resourceId . ')' . PHP_EOL;
    }

    public function onMessage(ConnectionInterface $from, $msg): void
    {
        $message = json_decode($msg);
        $method = $message->method;
        $roomId =  (int) $message->roomId;
        $room = $this->rooms[$message->roomId];
        xdebug_break();

        if ($method === self::JOIN) {
            $join = $this->joinRoom($from, $roomId);

            if ($join === true) {
                $from->send(json_encode(['joined', 'Joined room successfully.']));
            }

            $from->send(json_encode(['info', 'No available rooms']));
            return;
        }

        if ($room->isAvailable() === true) {
            $from->send(json_encode(['info' => 'Waiting for other players.']));
            return;
        }

        if (isset($this->games[$roomId]) === false || empty($this->games[$roomId]->getField()) === true) {
            $game = new Game($room->getPlayer1(), $room->getPlayer2(), $room);
            $this->games = [
                $roomId => $game
            ];
        }

        if ($method === self::CHECK && isset($this->games[$roomId]) === true) {
            $this->games[$roomId]->setField($message->fieldNumber, $room->getPlayerById($from->resourceId));
            $playerCharAsResult = $this->games[$roomId]->checkField();
            if ($playerCharAsResult === null) {
                $this->games[$roomId]->sendFieldToAllPlayers();
                return;
            }

            $winner = $this->games[$roomId]->getPlayerByChar($playerCharAsResult);
            $this->games[$roomId]->sendFieldToAllPlayers();
            $this->games[$roomId]->setGameOver(true);
            $this->games[$roomId]->sendWinnerToAllPlayers('Game Over. Player ' . $winner->getPlayerChar() . ' is victorious!');


            if ($this->games[$roomId]->checkFieldIsFull() === true) {
                $room->send('info', 'We have a twice. Good Job! You can start a new game now.');
            }
        }

        if ($method === self::NEW && isset($this->games[$roomId]) === true) {
            $room->send('info', 'Start a new Game.');
            $this->games[$roomId] = new Game($room->getPlayer1(), $room->getPlayer2(), $room);
            $this->games[$roomId]->resetField();
            $this->games[$roomId]->sendFieldToAllPlayers();
        }

    }

    public function onClose(ConnectionInterface $conn): void
    {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        foreach($this->rooms as $room) {
            if ($room->findPlayerInRoom($conn->resourceId) === true) {

                if (isset($this->games[$room->getId()]) === true) {
                    $this->games[$room->getId()]->resetField();
                    $this->games[$room->getId()]->sendFieldToAllPlayers();
                    unset($this->games[$room->getId()]);
                }

                $room->removePlayerFromRoom($conn->resourceId);

            }
        }

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e): void
    {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }

    /**
     * Connect a new user to a room.
     *
     * @param ConnectionInterface $from Where the request comes from.
     * @param int|null $roomId Can be null to join a random empty room.
     *
     * @return bool
     */
    private function joinRoom(ConnectionInterface $from, ?int $roomId = null): bool
    {
        if ($roomId === null) {
            return $this->joinRandomRoom($from);
        }

        return $this->joinFixedRoom($from, $roomId);
    }

    /**
     * Joins the player to a specific room if available.
     *
     * @param ConnectionInterface $from
     * @param int $roomId
     *
     * @return bool
     */
    private function joinFixedRoom(ConnectionInterface $from, int $roomId): bool
    {
        $resourceId = $from->resourceId;
        $room = $this->rooms[$roomId];

        if ($room->findPlayerInRoom($resourceId)) {
            return false;
        }

        echo '[Room][' . $room->getId() . '] Trying to join' . PHP_EOL;

        if ($room->isAvailable()) {
            $player = $room->join($resourceId, $from);
            $player->send('playerChar', $player->getPlayerChar());
            return true;
        }

        echo '[Room][' . $room->getId() . '] Room is full' . PHP_EOL;

        return false;
    }

    /**
     * Joins the player to a random empty room.
     *
     * @param ConnectionInterface $from
     *
     * @return bool
     */
    private function joinRandomRoom(ConnectionInterface $from): bool
    {
        $resourceId = $from->resourceId;

        foreach ($this->rooms as $room) {

            if ($room->findPlayerInRoom($resourceId)) {
                return false;
            }

            echo '[Room][' . $room->getId() . '] Trying to join' . PHP_EOL;
            if ($room->isAvailable()) {
                $player = $room->join($resourceId, $from);
                $from->send($player->getPlayerChar());
                return true;
            }

            echo '[Room][' . $room->getId() . '] Room is full' . PHP_EOL;

        }

        return false;
    }
}