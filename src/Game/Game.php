<?php

declare(strict_types=1);

namespace UltimateTicTacToe\Game;

use UltimateTicTacToe\Player\Player;
use UltimateTicTacToe\Room\Room;

class Game
{

    private array $field = [
        1 => '',
        2 => '',
        3 => '',
        4 => '',
        5 => '',
        6 => '',
        7 => '',
        8 => '',
        9 => '',
    ];

    private ?Player $player1 = null;
    private ?Player $player2 = null;
    private ?Room $room = null;
    private bool $gameOver = false;
    private ?Player $playerChecked = null;

    /**
     * Game constructor.
     *
     * @param Player $player1
     * @param Player $player2
     * @param Room $room
     */
    public function __construct(Player $player1, Player $player2, Room $room)
    {
        $this->player1 = $player1;
        $this->player2 = $player2;
        $this->room = $room;
    }

    /**
     * Sets if the game is over.
     *
     * @param bool $gameOver
     */
    public function setGameOver(bool $gameOver): void
    {
        $this->gameOver = $gameOver;
    }

    /**
     * Sets the char of the player on the specific field number.
     *
     * @param int $field
     * @param Player $player
     */
    public function setField(int $field, Player $player): void
    {
        if ($this->playerChecked !== null && $this->playerChecked === $player) {
            $player->send('error', 'Waiting for opponent.');
            return;
        }

        if ($this->gameOver === true) {
            $player->send('info', 'Game already over.');
            return;
        }

        if (empty($this->field[$field]) === false && $this->field[$field] !== $player->getPlayerChar()) {
            $player->send('error', 'Feld bereits belegt mit: ' . $this->field[$field]);
            return;
        }

        $this->field[$field] = $player->getPlayerChar();
        $this->playerChecked = $player;
    }

    /**
     * Resets the whole field array.
     *
     * @return void
     */
    public function resetField(): void
    {
        foreach ($this->field as $key => $value) {
            $this->field[$key] = '';
        }
    }

    /**
     * Returns the game field for the players;
     *
     * @return array|null
     */
    public function getField(): ?array
    {
        return $this->field;
    }

    /**
     * Returns the room for this game.
     *
     * @return Room
     */
    public function getRoom(): Room
    {
        return $this->room;
    }

    /**
     * Returns the player1 in this game.
     *
     * @return Player
     */
    public function getPlayer1(): Player
    {
        return $this->player1;
    }

    /**
     * Returns the player2 in this game.
     *
     * @return Player
     */
    public function getPlayer2(): Player
    {
        return $this->player2;
    }

    /**
     * Returns the correct player by his player char.
     *
     * @param string $playerChar
     * @return Player
     * @throws \Exception
     */
    public function getPlayerByChar(string $playerChar): Player
    {
        $player1 = $this->getPlayer1()->getPlayerByChar($playerChar);

        if ($player1 !== null) {
            return $player1;
        }

        $player2 = $this->getPlayer2()->getPlayerByChar($playerChar);

        if ($player2 !== null) {
            return $player2;
        }

        throw new \Exception('No players assigned with char ' . $playerChar);
    }

    /**
     * Sends the game field to every player.
     *
     * @return void
     */
    public function sendFieldToAllPlayers(): void
    {
        foreach ($this->room->getPlayersInRoom() as $player) {
            $player->send('field', $this->getField());
        }
    }

    /**
     * Sends the winner to all players.
     * TODO: maybe move into the room context.
     * @return void
     */
    public function sendWinnerToAllPlayers(string $message): void
    {
        foreach ($this->room->getPlayersInRoom() as $player) {
            $player->send('win', $message);
        }
    }

    /**
     * Checks the field for possible win.
     *
     * @return string|null
     */
    public function checkField(): ?string
    {
        // 1 2 3
        // 4 5 6
        // 7 8 9
        // 1 4 7
        // 2 5 8
        // 3 6 9
        // 1 5 9
        // 3 5 7

        $field = $this->getField();

        foreach ($field as $key => $value) {

            if ($value === '') {
                continue;
            }

            if ($value === $field[1] && $value === $field[2] && $value === $field[3]) {
                return $value;
            }

            if ($value === $field[4] && $value === $field[5] && $value === $field[6]) {
                return $value;
            }

            if ($value === $field[7] && $value === $field[8] && $value === $field[9]) {
                return $value;
            }

            if ($value === $field[1] && $value === $field[4] && $value === $field[7]) {
                return $value;
            }

            if ($value === $field[2] && $value === $field[5] && $value === $field[8]) {
                return $value;
            }

            if ($value === $field[3] && $value === $field[6] && $value === $field[9]) {
                return $value;
            }

            if ($value === $field[1] && $value === $field[5] && $value === $field[9]) {
                return $value;
            }

            if ($value === $field[3] && $value === $field[5] && $value === $field[7]) {
                return $value;
            }
        }
        return null;
    }

    /**
     * Checks if the field is full and we have a twice.
     *
     * @return bool
     */
    public function checkFieldIsFull(): bool
    {
        foreach ($this->field as $key => $value) {
            if ($value === '') {
                return false;
            }
        }

        return true;
    }
}