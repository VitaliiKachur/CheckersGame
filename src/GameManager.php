<?php

require_once 'src/Board.php';
require_once 'src/Interfaces/PieceInterface.php';
require_once 'src/Moves/KingMoveStrategy.php';
require_once 'src/Bots/BotFactory.php';
require_once 'src/Messages/MessageObserver.php';
require_once 'src/Messages/MessageService.php';
require_once 'src/Validation/MoveValidatorInterface.php';
require_once 'src/Validation/MoveValidationResult.php';
require_once 'src/Validation/CheckersMoveValidator.php';
require_once 'src/Commands/MoveCommandInterface.php';
require_once 'src/Commands/CaptureMoveCommand.php';
require_once 'src/Commands/RegularMoveCommand.php';
require_once 'src/Game/GameState.php';
require_once 'src/Game/GameEndDetector.php';
require_once 'src/Game/PlayerActionHandler.php';
require_once 'src/Bots/BotService.php';
require_once 'game.php';

class GameManager implements MessageObserver
{
    private static ?GameManager $instance = null;
    private Board $board;
    private GameState $gameState;
    private MessageService $messageService;
    private PlayerActionHandler $playerActionHandler;
    private GameEndDetector $gameEndDetector;
    private BotService $botService;
    private MoveValidatorInterface $moveValidator;

    private function __construct()
    {
        $this->initializeServices();
        $this->initializeNewGame('player_vs_player', 'white');
    }

    private function initializeServices(): void
    {
        $this->board = new Board();
        $this->messageService = new MessageService();
        $this->messageService->addObserver($this);
        $this->moveValidator = new CheckersMoveValidator();
        $this->playerActionHandler = new PlayerActionHandler($this->board, $this->messageService, $this->moveValidator);
        $this->gameEndDetector = new GameEndDetector($this->board, $this->messageService);
        $this->botService = new BotService($this->board, $this->messageService);
    }

    public function onMessage(string $message, string $type): void
    {
        // Handle message if needed for logging or other purposes
    }

    private function initializeNewGame(string $gameMode, string $humanPlayerColor = 'white'): void
    {
        $this->board = new Board();
        $this->gameState = new GameState($gameMode, $humanPlayerColor);

        $this->playerActionHandler = new PlayerActionHandler($this->board, $this->messageService, $this->moveValidator);
        $this->gameEndDetector = new GameEndDetector($this->board, $this->messageService);
        $this->botService = new BotService($this->board, $this->messageService);

        if ($gameMode === 'player_vs_bot') {
            $this->botService->initializeBot($gameMode);
            $colorText = $humanPlayerColor === 'white' ? 'білими' : 'чорними';
            $this->messageService->showMessage("Нова гра розпочата. Ви граєте {$colorText} проти бота.", 'info');

            if ($this->gameState->isBotTurn()) {
                $this->botService->makeBotMove($this->gameState, $this->gameEndDetector);
            }
        } else {
            $this->messageService->showMessage('Нова гра розпочата. Гравець проти гравця.', 'info');
        }
    }

    public static function getInstance(): GameManager
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __wakeup()
    {
        if ($this->messageService->getMessage() === 'Ви повинні продовжити бити!' && $this->gameState->getSelectedCell() !== null) {
        } else {
            $this->messageService->showMessage('', 'info');
        }

        if ($this->gameState->getGameMode() === 'player_vs_bot') {
            $this->botService->initializeBot('player_vs_bot');
        }
    }

    public function resetGame(string $gameMode = 'player_vs_player', string $humanPlayerColor = 'white'): void
    {
        $this->initializeNewGame($gameMode, $humanPlayerColor);
    }

    public function handleAction(?int $row, ?int $col): void
    {
        if (!$this->isValidGameState()) {
            return;
        }

        if (!$this->areValidCoordinates($row, $col)) {
            $this->handleInvalidCoordinates();
            return;
        }

        $this->processPlayerAction($row, $col);
        $this->processBotTurnIfNeeded();
    }

    private function isValidGameState(): bool
    {
        if ($this->gameState->isGameOver()) {
            $this->messageService->showMessage('Гра закінчена. Натисніть "Нова гра" для початку.', 'info');
            return false;
        }

        if ($this->isBotTurn()) {
            $this->messageService->showMessage('Зараз хід бота. Будь ласка, зачекайте.', 'info');
            return false;
        }

        return true;
    }

    private function isBotTurn(): bool
    {
        return $this->gameState->getGameMode() === 'player_vs_bot' &&
            !$this->gameState->isHumanTurn();
    }

    private function areValidCoordinates(?int $row, ?int $col): bool
    {
        return $row !== null && $col !== null &&
            $row >= 0 && $row < 8 &&
            $col >= 0 && $col < 8;
    }

    private function handleInvalidCoordinates(): void
    {
        $this->messageService->showMessage('Недійсні координати клітинки.', 'error');
        $this->gameState->clearSelection();
    }

    private function processPlayerAction(int $row, int $col): void
    {
        $selectedCell = $this->gameState->getSelectedCell();

        if ($selectedCell) {
            $this->handleCellClickWithSelection($row, $col, $selectedCell);
        } else {
            $this->handleCellClickWithoutSelection($row, $col);
        }
    }

    private function handleCellClickWithSelection(int $row, int $col, array $selectedCell): void
    {
        if ($this->isClickOnSelectedPiece($row, $col, $selectedCell)) {
            $this->clearSelection();
            return;
        }

        if ($this->isClickOnOwnPiece($row, $col)) {
            $this->handleOwnPieceClick($row, $col, $selectedCell);
            return;
        }

        $this->handleMoveAttempt($row, $col);
    }

    private function handleCellClickWithoutSelection(int $row, int $col): void
    {
        $this->playerActionHandler->selectPiece($row, $col, $this->gameState);
    }

    private function isClickOnSelectedPiece(int $row, int $col, array $selectedCell): bool
    {
        return $selectedCell['row'] === $row && $selectedCell['col'] === $col;
    }

    private function isClickOnOwnPiece(int $row, int $col): bool
    {
        $clickedPiece = $this->board->getPiece($row, $col);

        if ($this->gameState->getGameMode() === 'player_vs_player') {
            return $clickedPiece && $clickedPiece->getColor() === $this->gameState->getCurrentPlayer();
        }

        return $clickedPiece && $clickedPiece->getColor() === $this->gameState->getHumanPlayerColor();
    }

    private function handleOwnPieceClick(int $row, int $col, array $selectedCell): void
    {
        if ($this->canChangeSelection($selectedCell)) {
            $this->playerActionHandler->selectPiece($row, $col, $this->gameState);
        } else {
            $this->messageService->showMessage('Ви повинні продовжити бити цією шашкою!', 'error');
        }
    }

    private function canChangeSelection(array $selectedCell): bool
    {
        return !$this->isInMandatoryCapture($selectedCell['row'], $selectedCell['col']);
    }

    private function isInMandatoryCapture(int $row, int $col): bool
    {
        $piece = $this->board->getPiece($row, $col);
        if (!$piece) {
            return false;
        }

        $currentMessage = $this->messageService->getMessage();
        return $currentMessage === 'Ви повинні продовжити бити!' &&
            $piece->canCapture($row, $col, $this->board);
    }

    private function processBotTurnIfNeeded(): void
    {
        if ($this->shouldBotMove()) {
            $this->botService->makeBotMove($this->gameState, $this->gameEndDetector);
        }
    }

    public function cancelSelection(): void
    {
        $this->clearSelection();
    }

    public function clearSelection(): void
    {
        $this->gameState->clearSelection();
        $this->messageService->showMessage('', 'info');
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

    private function handleMoveAttempt(int $row, int $col): void
    {
        $selectedCell = $this->gameState->getSelectedCell();
        $validationResult = $this->playerActionHandler->attemptMove(
            $selectedCell['row'],
            $selectedCell['col'],
            $row,
            $col,
            $this->gameState
        );

        if (!$validationResult->isValid()) {
            $this->messageService->showMessage($validationResult->getErrorMessage(), 'error');
            return;
        }

        if ($validationResult->hasCapture()) {
            $this->processCapture($selectedCell['row'], $selectedCell['col'], $row, $col, $validationResult->getCaptureInfo()['captured']);
        } else {
            $this->processRegularMove($selectedCell['row'], $selectedCell['col'], $row, $col);
        }
    }

    private function processCapture(int $fromRow, int $fromCol, int $toRow, int $toCol, array $capturedPieces): void
    {
        $command = new CaptureMoveCommand($this->board, $fromRow, $fromCol, $toRow, $toCol, $capturedPieces, $this->messageService);
        $command->execute();

        $movedPiece = $this->board->getPiece($toRow, $toCol);
        if ($movedPiece && $movedPiece->canCapture($toRow, $toCol, $this->board)) {
            $this->gameState->setSelectedCell(['row' => $toRow, 'col' => $toCol]);
            $this->messageService->showMessage('Ви повинні продовжити бити!', 'info');
            return;
        }

        $this->gameState->switchPlayer();
        $this->gameState->clearSelection();
        $this->gameEndDetector->checkGameEnd($this->gameState);
    }

    private function processRegularMove(int $fromRow, int $fromCol, int $toRow, int $toCol): void
    {
        $command = new RegularMoveCommand($this->board, $fromRow, $fromCol, $toRow, $toCol, $this->messageService);
        $command->execute();

        $this->gameState->switchPlayer();
        $this->gameState->clearSelection();
        $this->gameEndDetector->checkGameEnd($this->gameState);
    }

    private function shouldBotMove(): bool
    {
        return $this->gameState->getGameMode() === 'player_vs_bot' &&
            !$this->gameState->isGameOver() &&
            $this->gameState->isBotTurn();
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
        return $this->gameState->getCurrentPlayer();
    }

    public function getSelectedCell(): ?array
    {
        return $this->gameState->getSelectedCell();
    }

    public function getGameStatus(): string
    {
        return $this->gameState->getGameStatus();
    }

    public function getMessage(): string
    {
        return $this->messageService->getMessage();
    }

    public function getMessageType(): string
    {
        return $this->messageService->getMessageType();
    }

    public function getGameMode(): string
    {
        return $this->gameState->getGameMode();
    }

    public function getHumanPlayerColor(): string
    {
        return $this->gameState->getHumanPlayerColor();
    }

    public function showMessage(string $text, string $type = 'info'): void
    {
        $this->messageService->showMessage($text, $type);
    }
    public function getGameState(): GameState
    {
        return $this->gameState;
    }

    public function makeBotMove(): void
    {
        if ($this->shouldBotMove()) {
            $this->botService->makeBotMove($this->gameState, $this->gameEndDetector);
        }
    }
}