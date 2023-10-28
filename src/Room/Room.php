<?php

declare(strict_types=1);

namespace UltimateTicTacToe\Room;

use Ratchet\ConnectionInterface;
use UltimateTicTacToe\Player\Player;

class Room
{
    private Player $player;
    private int $id;
    private int $playerCount = 0;
    private ?array $players;
    private int $maxPlayerLimit = 2;

    /**
     * Room constructor.
     *
     * @param int $roomId
     */
    public function __construct(int $roomId)
    {
        $this->id = $roomId;
    }

    /**
     * Join room and count player.
     *
     * @param int $resourceId
     * @param ConnectionInterface $webSocket
     *
     * @return Player
     */
    public function join(int $resourceId, ConnectionInterface $webSocket): Player
    {
        $this->playerCount++;
        $this->player = new Player($resourceId, $webSocket);
        $this->players[$resourceId] = $this->player;

        if ($this->playerCount === 1) {
            $this->player->setPlayerChar(Player::PLAYER_O);
        } else {
            $this->player->setPlayerChar(Player::PLAYER_X);
        }

        $this->send('info', 'Player ' . $this->player->getPlayerChar() . ' has joined.');

        return $this->player;
    }

    /**
     * Returns a player resource id.
     *
     * @return Player
     */
    public function getPlayer(): Player
    {
        return $this->player;
    }

    /**
     * Returns the player of a give resource id.
     *
     * @param int $resourceId
     *
     * @return Player
     */
    public function getPlayerById(int $resourceId): Player
    {
        return $this->players[$resourceId];
    }

    /**
     * Returns the room id.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Return the current connected players.
     *
     * @return int
     */
    public function getPlayerCount(): int
    {
        return $this->playerCount;
    }


    /**
     * Check if a player is available
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        if ($this->getPlayerCount() < $this->maxPlayerLimit) {
            return true;
        }

        return false;
    }

    /**
     * Returns array of player objects.
     *
     * @return ?Player[]
     */
    public function getPlayersInRoom(): ?array
    {
        return $this->players;
    }

    /**
     * Searches in the array if the user is already in the room.
     *
     * @param int $resourceId
     *
     * @return bool
     */
    public function findPlayerInRoom(int $resourceId): bool
    {
        if (!empty($this->players[$resourceId])) {
            return true;
        }

        return false;
    }

    /**
     * Return the first player.
     *
     * @return Player
     */
    public function getPlayer1(): Player
    {
        $count = 0;
        foreach ($this->players as $playerId => $player) {

            if ($count > 1) {
                break;
            }

            return $player;
        }
    }

    /**
     * Return the second player.
     *
     * @return Player
     */
    public function getPlayer2(): Player
    {
        $count = 0;
        foreach ($this->players as $playerId => $player) {

            if ($count < 1) {
                $count++;
                continue;
            }

            return $player;
        }
    }

    /**
     * Remove given player from the room.
     *
     * @param int $resourceId
     *
     * @return void
     */
    public function removePlayerFromRoom(int $resourceId): void
    {
        unset($this->players[$resourceId]);
        $this->playerCount--;
    }

    /**
     * Send message to all players in the room.
     *
     * @param string $type Type of the message (info, error, win, playerChar...).
     * @param string $message Message to send.
     *
     * @return void
     */
    public function send(string $type, string $message): void
    {
        foreach ($this->getPlayersInRoom() as $player) {
            $player->send($type, $message);
        }
    }
}