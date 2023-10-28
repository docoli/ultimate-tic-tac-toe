<?php

declare(strict_types=1);

namespace UltimateTicTacToe\Player;

use Ratchet\ConnectionInterface;

class Player
{
    public const PLAYER_X = 'x';
    public const PLAYER_O = 'o';

    private int $resourceId;
    private string $playerChar;
    private ConnectionInterface $webSocket;

    /**
     * Player constructor.
     *
     * @param int $resourceId
     * @param ConnectionInterface $webSocket
     */
    public function __construct(int $resourceId, ConnectionInterface $webSocket)
    {
        $this->resourceId = $resourceId;
        $this->webSocket = $webSocket;
    }

    /**
     * Returns the character of the player (x/o)
     *
     * @return string
     */
    public function getPlayerChar(): string
    {
        return $this->playerChar;
    }

    /**
     * Sets the character for the player
     *
     * @param string $playerChar
     *
     * @return void
     */
    public function setPlayerChar(string $playerChar): void
    {
        $this->playerChar = $playerChar;
    }

    /**
     * Send message to all players in the room.
     *
     * @param string $type Type of the message (info, error, win, playerChar...).
     * @param mixed $message Message to send. Can also be the field array.
     *
     * @return void
     */
    public function send(string $type, $message): void
    {
        $jsonMsg = json_encode([
            $type => $message
        ]);

        $this->webSocket->send($jsonMsg);
    }

    /**
     * Returns the player object by his player char if correct.
     *
     * @param string $playerChar
     *
     * @return Player|null
     * @throws \Exception
     */
    public function getPlayerByChar(string $playerChar): ?Player
    {
        if ($playerChar !== self::PLAYER_X && $playerChar !== self::PLAYER_O) {
            throw new \Exception('Player char is either x or o');
        }

        if ($this->getPlayerChar() === $playerChar) {
            return $this;
        }

        return null;
    }

}