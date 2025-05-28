<?php

require_once 'src/Board.php'; 

class GameManager
{
    private static ?GameManager $instance = null;
    private Board $board;
    private string $currentPlayer;
    private ?array $selectedCell;
    private bool $gameOver;
    private string $gameStatus;
    private string $message;
    private string $messageType;
    private string $gameMode;

    private function __construct()
    {
        $this->initializeNewGame('player_vs_player');
    }

    private function initializeNewGame(string $gameMode): void
    {
        $this->board = new Board();
        $this->currentPlayer = 'white';
        $this->selectedCell = null;
        $this->gameOver = false;
        $this->gameStatus = 'В процесі';
        $this->message = 'Нова гра розпочата.';
        $this->messageType = 'info';
        $this->gameMode = $gameMode;
    }

    public function __wakeup()
    {
        if ($this->message !== 'Ви повинні продовжити бити!') {
            $this->message = '';
            $this->messageType = 'info';
        }
    }

    public static function getInstance(): GameManager
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function resetGame(string $gameMode = 'player_vs_player'): void
    {
        $this->initializeNewGame($gameMode);
    }

    public function handleAction(?int $row, ?int $col): void
    {
        $this->showMessage("Натиснуто клітинку {$row},{$col}", 'info');
    }

    public function getBoardData(): array { return $this->board->getBoardState(); }
    public function getBoard(): Board { return $this->board; } 
    public function getCurrentPlayer(): string { return $this->currentPlayer; }
    public function getSelectedCell(): ?array { return $this->selectedCell; }
    public function getGameStatus(): string { return $this->gameStatus; }
    public function getMessage(): string { return $this->message; }
    public function getMessageType(): string { return $this->messageType; }
    public function showMessage(string $text, string $type = 'info'): void { $this->message = $text; $this->messageType = $type; }
    public function getGameMode(): string { return $this->gameMode; }
}