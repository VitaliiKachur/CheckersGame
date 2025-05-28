<?php

require_once 'src/Board.php';
require_once 'src/Interfaces/PieceInterface.php';
require_once 'src/Moves/KingMoveStrategy.php';
require_once 'src/Bots/BotFactory.php';

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
    private ?BotInterface $bot = null;

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
        $this->gameMode = $gameMode;
        $this->bot = null; // Reset bot on new game
        if ($this->gameMode === 'player_vs_bot') {
            $this->bot = BotFactory::createBot('simple'); // Create a simple bot
        }
        $this->showMessage('Нова гра розпочата. ' . ($this->gameMode === 'player_vs_bot' ? 'Ви граєте проти бота.' : 'Гравець проти гравця.'), 'info');
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
            $hasPlayerCaptures = $this->playerHasCaptures($this->currentPlayer);
            $pieceCanCapture = $piece->canCapture($row, $col, $this->board);

            if ($hasPlayerCaptures && !$pieceCanCapture) {
                $this->showMessage('Ви повинні бити, якщо це можливо! Виберіть фігуру, яка може бити.', 'error');
                return;
            }
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
        $piece = $this->board->getPiece($fromRow, $fromCol);
        if (!$piece)
            return;

        $captureInfo = $this->getCaptureInfo($piece, $fromRow, $fromCol, $toRow, $toCol);
        $hasMandatoryCapture = $this->playerHasCaptures($this->currentPlayer);

        if ($this->isInvalidDueToMandatoryCapture($hasMandatoryCapture, $captureInfo))
            return;

        if ($captureInfo) {
            $this->processCapture($fromRow, $fromCol, $toRow, $toCol, $captureInfo['captured']);
            return;
        }

        if ($this->canPerformRegularMove($piece, $fromRow, $fromCol, $toRow, $toCol, $hasMandatoryCapture)) {
            $this->processRegularMove($fromRow, $fromCol, $toRow, $toCol);
            return;
        }

        $this->showMessage('Недійсний хід або ви повинні бити.', 'error');
        $this->checkGameEnd();
    }

    private function switchPlayer(): void
    {
        $this->currentPlayer = ($this->currentPlayer === 'white') ? 'black' : 'white';
        $this->showMessage($this->currentPlayer === 'white' ? 'Хід білих.' : 'Хід чорних.', 'info');
    }

    public function playerHasCaptures(string $color): bool
    {
        for ($r = 0; $r < 8; $r++) {
            for ($c = 0; $c < 8; $c++) {
                $piece = $this->board->getPiece($r, $c);
                if ($piece && $piece->getColor() === $color && $piece->canCapture($r, $c, $this->board)) {
                    return true;
                }
            }
        }
        return false;
    }
    private function isInvalidDueToMandatoryCapture(bool $hasMandatoryCapture, ?array $captureInfo): bool
    {
        if ($hasMandatoryCapture && !$captureInfo) {
            $this->showMessage('Ви повинні бити, якщо це можливо!', 'error');
            return true;
        }
        return false;
    }
    private function getCaptureInfo(PieceInterface $piece, int $fromRow, int $fromCol, int $toRow, int $toCol): ?array
    {
        $possibleCaptures = $piece->getPossibleCaptures($fromRow, $fromCol, $this->board);
        return $this->findCaptureOption($possibleCaptures, $toRow, $toCol);
    }
    private function findCaptureOption(array $possibleCaptures, int $toRow, int $toCol): ?array
    {
        foreach ($possibleCaptures as $option) {
            if ($option['row'] === $toRow && $option['col'] === $toCol) {
                return $option;
            }
        }
        return null;
    }
    private function canPerformRegularMove(PieceInterface $piece, int $fromRow, int $fromCol, int $toRow, int $toCol, bool $hasMandatoryCapture): bool
    {
        return !$hasMandatoryCapture && $piece->isValidMove($fromRow, $fromCol, $toRow, $toCol, $this->board);
    }


    private function processCapture(int $fromRow, int $fromCol, int $toRow, int $toCol, array $capturedPieces): void
    {
        foreach ($capturedPieces as $captured) {
            $this->board->removePiece($captured['row'], $captured['col']);
        }

        $this->board->movePiece($fromRow, $fromCol, $toRow, $toCol);
        $this->showMessage('Взято!', 'success');

        $movedPiece = $this->board->getPiece($toRow, $toCol);
        if ($movedPiece && $movedPiece->canCapture($toRow, $toCol, $this->board)) {
            $this->selectedCell = ['row' => $toRow, 'col' => $toCol];
            $this->showMessage('Ви повинні продовжити бити!', 'info');
            return;
        }

        $this->switchPlayer();
        $this->checkGameEnd();
    }

    private function processRegularMove(int $fromRow, int $fromCol, int $toRow, int $toCol): void
    {
        $this->board->movePiece($fromRow, $fromCol, $toRow, $toCol);
        $this->showMessage('Хід зроблено.', 'info');
        $this->switchPlayer();
        $this->checkGameEnd();
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
    private function checkGameEnd(): void
    {
        if ($this->isVictory('white'))
            return;
        if ($this->isVictory('black'))
            return;
        if ($this->isStalemate($this->currentPlayer))
            return;
    }

    private function isVictory(string $color): bool
    {
        $remaining = $this->board->countPieces($color);
        if ($remaining === 0) {
            $this->gameOver = true;
            $winner = $color === 'white' ? 'Чорні' : 'Білі';
            $this->gameStatus = "$winner перемогли";
            $this->showMessage("{$winner} перемогли!", 'success');
            return true;
        }
        return false;
    }

    private function isStalemate(string $player): bool
    {
        if (!$this->playerHasValidMoves($player)) {
            $this->gameOver = true;
            $this->gameStatus = 'Пат';
            $this->showMessage("Пат! Гра закінчилася нічиєю, у " . ($player === 'white' ? 'білих' : 'чорних') . " немає дійсних ходів.", 'info');
            return true;
        }
        return false;
    }

    public function playerHasValidMoves(string $color): bool
    {
        $hasCaptures = $this->playerHasCaptures($color);

        for ($r = 0; $r < 8; $r++) {
            for ($c = 0; $c < 8; $c++) {
                $piece = $this->board->getPiece($r, $c);

                if ($this->isPlayerPiece($piece, $color)) {
                    if ($hasCaptures && $this->canPieceCapture($piece, $r, $c)) {
                        return true;
                    }

                    if (!$hasCaptures && $this->canPieceMove($piece, $r, $c)) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    private function isPlayerPiece(?PieceInterface $piece, string $color): bool
    {
        return $piece !== null && $piece->getColor() === $color;
    }

    private function canPieceCapture(PieceInterface $piece, int $row, int $col): bool
    {
        return !empty($piece->getPossibleCaptures($row, $col, $this->board));
    }

    private function canPieceMove(PieceInterface $piece, int $row, int $col): bool
    {
        return !empty($piece->getPossibleMoves($row, $col, $this->board));
    }


    public function __wakeup()
    {
        // ... існуюча логіка ...
        // Re-initialize bot if game mode is player_vs_bot and bot is null (e.g., if it wasn't serialized properly)
        if ($this->gameMode === 'player_vs_bot' && $this->bot === null) {
            $this->bot = BotFactory::createBot('simple');
        }
    }

    public function handleAction(?int $row, ?int $col): void
    {
        if ($this->isGameOver()) return;
        if ($this->isBotTurn()) return; // Перевірка, чи це хід бота
        if ($this->isInvalidCell($row, $col)) return;

        if ($this->selectedCell) {
            $this->handleMoveAttempt($row, $col);
        } else {
            $this->selectPiece($row, $col);
        }

        if ($this->shouldBotMove()) { // Після ходу гравця перевіряємо, чи хід бота
            $this->makeBotMove();
        }
    }

    private function isBotTurn(): bool
    {
        if ($this->gameMode === 'player_vs_bot' && $this->currentPlayer === 'black') {
            $this->showMessage('Зараз хід бота. Будь ласка, зачекайте.', 'info');
            return true;
        }
        return false;
    }

    private function shouldBotMove(): bool
    {
        return $this->gameMode === 'player_vs_bot' && !$this->gameOver && $this->currentPlayer === 'black';
    }

    private function makeBotMove(): void
    {
        if ($this->bot === null || $this->gameOver || $this->currentPlayer !== 'black') {
            return;
        }

        $this->showMessage('Бот робить хід...', 'info');

        // Для імітації "думок" бота та уникнення блокування сесії
        session_write_close();
        usleep(800000); // 0.8 секунди
        session_start();

        $botMove = $this->bot->makeMove($this->board, 'black');

        if (empty($botMove)) {
            $this->endGameAsStalemate();
            return;
        }

        [$fromRow, $fromCol, $toRow, $toCol] = [
            $botMove['fromRow'],
            $botMove['fromCol'],
            $botMove['toRow'],
            $botMove['toCol']
        ];

        $piece = $this->board->getPiece($fromRow, $fromCol);
        if (!$this->isValidBotPiece($piece)) {
            $this->showMessage('Помилка бота: невірна фігура для ходу.', 'error');
            $this->switchPlayer(); // Перемикаємо гравця, щоб гра не зависла
            return;
        }

        $captureInfo = $this->findCaptureOption($piece->getPossibleCaptures($fromRow, $fromCol, $this->board), $toRow, $toCol);

        if ($captureInfo) {
            $this->processBotCapture($fromRow, $fromCol, $toRow, $toCol, $captureInfo['captured']);
            return;
        }

        $this->board->movePiece($fromRow, $fromCol, $toRow, $toCol);
        $this->showMessage("Бот зробив хід на {$toRow},{$toCol}.", 'info');
        $this->switchPlayer();
        $this->checkGameEnd();
    }

    private function isValidBotPiece(?PieceInterface $piece): bool
    {
        return $piece !== null && $piece->getColor() === 'black';
    }

    private function processBotCapture(int $fromRow, int $fromCol, int $toRow, int $toCol, array $capturedPieces): void
    {
        foreach ($capturedPieces as $captured) {
            $this->board->removePiece($captured['row'], $captured['col']);
        }

        $this->board->movePiece($fromRow, $fromCol, $toRow, $toCol);
        $this->showMessage("Бот взяв фігуру на {$toRow},{$toCol}!", 'success');

        if ($this->botCanContinueCapture($toRow, $toCol)) {
            $this->showMessage('Бот продовжує бити!', 'info');
            $this->makeBotMove(); // Бот продовжує бити
            return;
        }

        $this->switchPlayer();
        $this->checkGameEnd();
    }

    private function botCanContinueCapture(int $row, int $col): bool
    {
        $movedPiece = $this->board->getPiece($row, $col);
        return $movedPiece && $movedPiece->canCapture($row, $col, $this->board);
    }

    private function endGameAsStalemate(): void
    {
        $this->showMessage('Бот не може зробити хід. Пат!', 'info');
        $this->gameOver = true;
        $this->gameStatus = 'Пат';
    }

    // ... інші гетери ...
}