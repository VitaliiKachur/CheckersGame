<?php
class GameController
{
    private GameManager $gameManager;

    public function __construct()
    {
        $this->initializeGameManager();
    }

    /**
     * Ініціалізація менеджера гри
     */
    private function initializeGameManager(): void
    {
        if (isset($_SESSION['game_state'])) {
            $this->gameManager = unserialize($_SESSION['game_state']);
            $this->handleContinuousCapture();
        } else {
            $this->gameManager = GameManager::getInstance();
        }
    }

    /**
     * Обробка продовження захоплення
     */
    private function handleContinuousCapture(): void
    {
        if ($this->gameManager->getMessage() === 'Ви повинні продовжити бити!' && 
            $this->gameManager->getSelectedCell() !== null) {
            // Зберігаємо повідомлення про продовження захоплення
        } else {
            $this->gameManager->showMessage('');
        }
    }

    /**
     * Обробка POST запитів
     */
    public function handleRequest(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $this->processAction($_POST['action']);
        }

        $this->handleBotMove();
        $this->saveGameState();
    }

    /**
     * Обробка дій користувача
     */
    private function processAction(string $action): void
    {
        switch ($action) {
            case 'reset':
                $this->handleResetAction();
                break;
            case 'move':
                $this->handleMoveAction();
                break;
        }
    }

    /**
     * Обробка скидання гри
     */
    private function handleResetAction(): void
    {
        $gameMode = $_POST['game_mode'] ?? 'player_vs_player';
        $humanPlayerColor = $_POST['player_color'] ?? 'white';
        $this->gameManager->resetGame($gameMode, $humanPlayerColor);
    }

    /**
     * Обробка ходу
     */
    private function handleMoveAction(): void
    {
        $row = isset($_POST['row']) ? (int) $_POST['row'] : null;
        $col = isset($_POST['col']) ? (int) $_POST['col'] : null;
        $this->gameManager->handleAction($row, $col);
    }

    /**
     * Обробка ходу бота
     */
    private function handleBotMove(): void
    {
        if ($this->shouldMakeBotMove()) {
            $this->gameManager->makeBotMove();
        }
    }

    /**
     * Перевірка чи потрібно зробити хід боту
     */
    private function shouldMakeBotMove(): bool
    {
        return $this->gameManager->getGameMode() === 'player_vs_bot' &&
               !$this->gameManager->getGameState()->isHumanTurn() &&
               $this->gameManager->getGameStatus() !== 'Пат' &&
               !strpos($this->gameManager->getGameStatus(), 'перемогли');
    }

    /**
     * Збереження стану гри
     */
    private function saveGameState(): void
    {
        $_SESSION['game_state'] = serialize($this->gameManager);
    }

    /**
     * Отримання даних для відображення
     */
    public function getDisplayData(): array
    {
        return [
            'boardData' => $this->gameManager->getBoardData(),
            'currentPlayer' => $this->gameManager->getCurrentPlayer(),
            'selectedCell' => $this->gameManager->getSelectedCell(),
            'gameStatus' => $this->gameManager->getGameStatus(),
            'message' => $this->gameManager->getMessage(),
            'messageType' => $this->gameManager->getMessageType(),
            'gameMode' => $this->gameManager->getGameMode(),
            'humanPlayerColor' => $this->gameManager->getHumanPlayerColor(),
            'gameManager' => $this->gameManager
        ];
    }
}

$gameController = new GameController();
$gameController->handleRequest();
$displayData = $gameController->getDisplayData();

extract($displayData);

function renderBoard(array $boardData, ?array $selectedCell, GameManager $gameManager): string
{
    return GameRenderer::renderBoard($boardData, $selectedCell, $gameManager);
}
?>