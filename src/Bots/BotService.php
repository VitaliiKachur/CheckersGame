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
        if ($this->shouldSkipBotMove($gameState)) {
            return;
        }

        $botColor = $gameState->getBotColor();
        $this->showBotThinkingMessage();
        $this->pauseForBotThinking();

        $botMove = $this->getBotMove($botColor);

        if (empty($botMove)) {
            $this->endGameAsStalemate($gameState);
            return;
        }

        $this->executeBotMove($botMove, $gameState, $gameEndDetector, $botColor);
    }

    private function shouldSkipBotMove(GameState $gameState): bool
    {
        return $this->bot === null || $gameState->isGameOver() || !$gameState->isBotTurn();
    }

    private function showBotThinkingMessage(): void
    {
        $this->messageService->showMessage('Бот робить хід...', 'info');
    }

    private function pauseForBotThinking(): void
    {
        session_write_close();
        usleep(800000);
        session_start();
    }

    private function getBotMove(string $botColor): array
    {
        return $this->bot->makeMove($this->board, $botColor);
    }

    private function executeBotMove(array $botMove, GameState $gameState, GameEndDetector $gameEndDetector, string $botColor): void
    {
        $moveCoordinates = $this->extractMoveCoordinates($botMove);

        if (!$this->validateBotMove($moveCoordinates, $botColor)) {
            $this->handleInvalidBotMove($gameState);
            return;
        }

        $this->performBotMove($moveCoordinates, $gameState, $gameEndDetector);
    }

    private function extractMoveCoordinates(array $botMove): array
    {
        return [
            'fromRow' => $botMove['fromRow'],
            'fromCol' => $botMove['fromCol'],
            'toRow' => $botMove['toRow'],
            'toCol' => $botMove['toCol']
        ];
    }

    private function validateBotMove(array $coordinates, string $botColor): bool
    {
        $piece = $this->board->getPiece($coordinates['fromRow'], $coordinates['fromCol']);
        return $this->isValidBotPiece($piece, $botColor);
    }

    private function handleInvalidBotMove(GameState $gameState): void
    {
        $this->messageService->showMessage('Помилка бота: невірна фігура для ходу.', 'error');
        $gameState->switchPlayer();
    }

    private function performBotMove(array $coordinates, GameState $gameState, GameEndDetector $gameEndDetector): void
    {
        $piece = $this->board->getPiece($coordinates['fromRow'], $coordinates['fromCol']);
        $captureInfo = $this->findCaptureOption(
            $piece->getPossibleCaptures($coordinates['fromRow'], $coordinates['fromCol'], $this->board),
            $coordinates['toRow'],
            $coordinates['toCol']
        );

        if ($captureInfo) {
            $this->handleBotCapture($coordinates, $captureInfo['captured'], $gameState, $gameEndDetector);
        } else {
            $this->handleBotRegularMove($coordinates, $gameState, $gameEndDetector);
        }
    }

    private function handleBotCapture(array $coordinates, array $capturedPieces, GameState $gameState, GameEndDetector $gameEndDetector): void
    {
        $this->processBotCapture(
            $coordinates['fromRow'],
            $coordinates['fromCol'],
            $coordinates['toRow'],
            $coordinates['toCol'],
            $capturedPieces,
            $gameState,
            $gameEndDetector
        );
    }

    private function handleBotRegularMove(array $coordinates, GameState $gameState, GameEndDetector $gameEndDetector): void
    {
        $this->board->movePiece(
            $coordinates['fromRow'],
            $coordinates['fromCol'],
            $coordinates['toRow'],
            $coordinates['toCol']
        );

        $this->messageService->showMessage("Бот зробив хід", 'info');
        $this->finalizeBotTurn($gameState, $gameEndDetector);
    }

    private function processBotCapture(int $fromRow, int $fromCol, int $toRow, int $toCol, array $capturedPieces, GameState $gameState, GameEndDetector $gameEndDetector): void
    {
        $this->removeCapturedPieces($capturedPieces);
        $this->executeCapture($fromRow, $fromCol, $toRow, $toCol);
        $this->showCaptureMessage();
        $this->handleContinuousCapture($toRow, $toCol, $gameState, $gameEndDetector);
    }

    private function removeCapturedPieces(array $capturedPieces): void
    {
        foreach ($capturedPieces as $captured) {
            $this->board->removePiece($captured['row'], $captured['col']);
        }
    }

    private function executeCapture(int $fromRow, int $fromCol, int $toRow, int $toCol): void
    {
        $this->board->movePiece($fromRow, $fromCol, $toRow, $toCol);
    }

    private function showCaptureMessage(): void
    {
        $this->messageService->showMessage("Бот взяв фігуру!", 'success');
    }

    private function handleContinuousCapture(int $toRow, int $toCol, GameState $gameState, GameEndDetector $gameEndDetector): void
    {
        if ($this->botCanContinueCapture($toRow, $toCol)) {
            $this->showContinuousCaptureMessage();
            $this->makeBotMove($gameState, $gameEndDetector);
        } else {
            $this->finalizeBotTurn($gameState, $gameEndDetector);
        }
    }

    private function showContinuousCaptureMessage(): void
    {
        $this->messageService->showMessage('Бот продовжує бити!', 'info');
    }

    private function finalizeBotTurn(GameState $gameState, GameEndDetector $gameEndDetector): void
    {
        $gameState->switchPlayer();
        $gameEndDetector->checkGameEnd($gameState);
    }
    private function isValidBotPiece(?PieceInterface $piece, string $botColor): bool
    {
        return $piece !== null && $piece->getColor() === $botColor;
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