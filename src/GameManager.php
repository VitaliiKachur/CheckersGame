<?php

require_once 'src/Board.php';
require_once 'src/Interfaces/PieceInterface.php'; 
require_once 'src/Moves/KingMoveStrategy.php';

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
    private function isGameOver(): bool
    {
        if ($this->gameOver) {
            $this->showMessage('Гра закінчена. Натисніть "Нова гра" для початку.', 'info');
            return true;
        }
        return false;
    }
    public function handleAction(?int $row, ?int $col): void
    {

        if ($this->isGameOver())
            return;

        if ($this->isInvalidCell($row, $col))
            return;

        if ($this->selectedCell) {
            $this->handleMoveAttempt($row, $col);
        } else {
            $this->selectPiece($row, $col);
        }
   
    }
    private function isBotTurn(): bool
    {
        return false;
    }
    private function shouldBotMove(): bool
    {
        return false;
    }
    private function isInvalidCell(?int $row, ?int $col): bool
    {
        if ($row === null || $col === null || $row < 0 || $row >= 8 || $col < 0 || $col >= 8) {
            $this->showMessage('Недійсні координати клітинки.', 'error');
            $this->clearSelection();
            return true;
        }
        return false;
    }
    private function handleMoveAttempt(int $row, int $col): void
    {
        $this->attemptMove($this->selectedCell['row'], $this->selectedCell['col'], $row, $col);


        if ($this->selectedCell && $this->message !== 'Ви повинні продовжити бити!') {
            $this->clearSelection();
        }
    }

    private function selectPiece(int $row, int $col): void
    {
        $piece = $this->board->getPiece($row, $col);
        if ($piece && $piece->getColor() === $this->currentPlayer) {


            $this->selectedCell = ['row' => $row, 'col' => $col];
            $this->showMessage('Фігуру вибрано. Зробіть хід.', 'info');
        } else {
            $this->showMessage('Виберіть свою фігуру.', 'error');
        }
    }

    private function clearSelection(): void
    {
        $this->selectedCell = null;
    }


    public function getSelectedCell(): ?array
    {
        return $this->selectedCell;
    }


    private function attemptMove(int $fromRow, int $fromCol, int $toRow, int $toCol): void
    {
        $this->showMessage('Спроба ходу: ' . $fromRow . ',' . $fromCol . ' до ' . $toRow . ',' . $toCol, 'info');
        $piece = $this->board->getPiece($fromRow, $fromCol);

        if (!$piece) {
            $this->showMessage('На вибраній клітинці немає фігури.', 'error');
            return;
        }
        if ($piece->isValidMove($fromRow, $fromCol, $toRow, $toCol, $this->board)) {
            $this->board->movePiece($fromRow, $fromCol, $toRow, $toCol);
            $this->showMessage('Хід зроблено.', 'info');
            $this->switchPlayer();
        } else {
            $this->showMessage('Недійсний хід.', 'error');
        }
    }

    private function switchPlayer(): void
    {
        $this->currentPlayer = ($this->currentPlayer === 'white') ? 'black' : 'white';
        $this->showMessage($this->currentPlayer === 'white' ? 'Хід білих.' : 'Хід чорних.', 'info');
    }


    private function checkGameEnd(): void
    { /* ... */
    }
    private function isVictory(string $color): bool
    {
        return false;
    }
    private function isStalemate(string $player): bool
    {
        return false;
    }
    public function playerHasValidMoves(string $color): bool
    {
        return true;
    } 
    private function isPlayerPiece(?PieceInterface $piece, string $color): bool
    {
        return true;
    }
    private function canPieceCapture(PieceInterface $piece, int $row, int $col): bool
    {
        return false;
    }
    private function canPieceMove(PieceInterface $piece, int $row, int $col): bool
    {
        return true;
    } 
    public function playerHasCaptures(string $color): bool
    {
        return false;
    } 
    public function getBoardData(): array
    {
        return $this->board->getBoardState();
    }
    public function getBoard(): Board
    {
        return $this->board;
    }
    public function getCurrentPlayer(): string
    {
        return $this->currentPlayer;
    }
    public function getGameStatus(): string
    {
        return $this->gameStatus;
    }
    public function getMessage(): string
    {
        return $this->message;
    }
    public function getMessageType(): string
    {
        return $this->messageType;
    }
    public function showMessage(string $text, string $type = 'info'): void
    {
        $this->message = $text;
        $this->messageType = $type;
    }
    public function getGameMode(): string
    {
        return $this->gameMode;
    }
}