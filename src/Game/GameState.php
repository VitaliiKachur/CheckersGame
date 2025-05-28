<?php
class GameState
{
    private string $currentPlayer;
    private ?array $selectedCell;
    private bool $gameOver;
    private string $gameStatus;
    private string $gameMode;

    public function __construct(string $gameMode = 'player_vs_player')
    {
        $this->currentPlayer = 'white';
        $this->selectedCell = null;
        $this->gameOver = false;
        $this->gameStatus = 'В процесі';
        $this->gameMode = $gameMode;
    }

    public function getCurrentPlayer(): string { return $this->currentPlayer; }
    public function getSelectedCell(): ?array { return $this->selectedCell; }
    public function isGameOver(): bool { return $this->gameOver; }
    public function getGameStatus(): string { return $this->gameStatus; }
    public function getGameMode(): string { return $this->gameMode; }

    public function setCurrentPlayer(string $player): void { $this->currentPlayer = $player; }
    public function setSelectedCell(?array $cell): void { $this->selectedCell = $cell; }
    public function setGameOver(bool $gameOver): void { $this->gameOver = $gameOver; }
    public function setGameStatus(string $status): void { $this->gameStatus = $status; }

    public function switchPlayer(): void
    {
        $this->currentPlayer = ($this->currentPlayer === 'white') ? 'black' : 'white';
    }

    public function clearSelection(): void
    {
        $this->selectedCell = null;
    }
}