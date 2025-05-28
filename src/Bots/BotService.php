<?php

class BotService
{
    private ?BotInterface $bot = null;
    private Board $board;
    private MessageService $messageService;

    public function __construct(Board $board, MessageService $messageService)
    {
        $this->board = $board;
        $this->messageService = $messageService;
    }

    public function initializeBot(string $gameMode): void
    {
        if ($gameMode === 'player_vs_bot') {
            $this->bot = BotFactory::createBot('simple');
        } else {
            $this->bot = null;
        }
    }

    public function makeBotMove(GameState $gameState, GameEndDetector $gameEndDetector): void
    {
        if ($this->bot === null || $gameState->isGameOver() || $gameState->getCurrentPlayer() !== 'black') {
            return;
        }

        $this->messageService->showMessage('Бот робить хід...', 'info');

        session_write_close();
        usleep(800000);
        session_start();

        $botMove = $this->bot->makeMove($this->board, 'black');

        if (empty($botMove)) {
            $this->endGameAsStalemate($gameState);
            return;
        }

        $this->executeBotMove($botMove, $gameState, $gameEndDetector);
    }

    private function executeBotMove(array $botMove, GameState $gameState, GameEndDetector $gameEndDetector): void
    {
        [$fromRow, $fromCol, $toRow, $toCol] = [
            $botMove['fromRow'], $botMove['fromCol'], 
            $botMove['toRow'], $botMove['toCol']
        ];

        $piece = $this->board->getPiece($fromRow, $fromCol);
        if (!$this->isValidBotPiece($piece)) {
            $this->messageService->showMessage('Помилка бота: невірна фігура для ходу.', 'error');
            $gameState->switchPlayer();
            return;
        }

        $captureInfo = $this->findCaptureOption($piece->getPossibleCaptures($fromRow, $fromCol, $this->board), $toRow, $toCol);

        if ($captureInfo) {
            $this->processBotCapture($fromRow, $fromCol, $toRow, $toCol, $captureInfo['captured'], $gameState, $gameEndDetector);
        } else {
            $this->board->movePiece($fromRow, $fromCol, $toRow, $toCol);
            $this->messageService->showMessage("Бот зробив хід на {$toRow},{$toCol}.", 'info');
            $gameState->switchPlayer();
            $gameEndDetector->checkGameEnd($gameState);
        }
    }

    private function processBotCapture(int $fromRow, int $fromCol, int $toRow, int $toCol, array $capturedPieces, GameState $gameState, GameEndDetector $gameEndDetector): void
    {
        foreach ($capturedPieces as $captured) {
            $this->board->removePiece($captured['row'], $captured['col']);
        }

        $this->board->movePiece($fromRow, $fromCol, $toRow, $toCol);
        $this->messageService->showMessage("Бот взяв фігуру на {$toRow},{$toCol}!", 'success');

        if ($this->botCanContinueCapture($toRow, $toCol)) {
            $this->messageService->showMessage('Бот продовжує бити!', 'info');
            $this->makeBotMove($gameState, $gameEndDetector);
        } else {
            $gameState->switchPlayer();
            $gameEndDetector->checkGameEnd($gameState);
        }
    }

    private function isValidBotPiece(?PieceInterface $piece): bool
    {
        return $piece !== null && $piece->getColor() === 'black';
    }

    private function findCaptureOption(array $possibleCaptures, int $toRow, int $toCol): ?array
    {
        foreach ($possibleCaptures as $option) {
            if ($option['toRow'] === $toRow && $option['toCol'] === $toCol) {
                return $option;
            }
        }
        return null;
    }

    private function botCanContinueCapture(int $row, int $col): bool
    {
        $movedPiece = $this->board->getPiece($row, $col);
        return $movedPiece && $movedPiece->canCapture($row, $col, $this->board);
    }

    private function endGameAsStalemate(GameState $gameState): void
    {
        $this->messageService->showMessage('Бот не може зробити хід. Пат!', 'info');
        $gameState->setGameOver(true);
        $gameState->setGameStatus('Пат');
    }
}