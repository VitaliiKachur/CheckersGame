<?php
session_start();

require_once 'src/Board.php';
require_once 'src/GameManager.php';

require_once 'src/Bots/BotFactory.php';
require_once 'src/Interfaces/BotInterface.php';
require_once 'src/Board.php';
require_once 'src/Validation/CheckersMoveValidator.php';

class GameRenderer
{
    /**
     * Головний метод рендерингу дошки
     */
    public static function renderBoard(array $boardData, ?array $selectedCell, GameManager $gameManager): string
    {
        $html = '<div class="board" id="board">';
        $filteredPossibleMoves = self::getFilteredPossibleMoves($selectedCell, $boardData, $gameManager);

        for ($row = 0; $row < 8; $row++) {
            for ($col = 0; $col < 8; $col++) {
                $html .= self::renderSingleCell($row, $col, $selectedCell, $filteredPossibleMoves, $boardData);
            }
        }

        $html .= '</div>';
        return $html;
    }

    /**
     * Рендеринг однієї клітинки дошки
     */
    private static function renderSingleCell(int $row, int $col, ?array $selectedCell, array $filteredPossibleMoves, array $boardData): string
    {
        [$possibleMoveClass, $boxShadowStyle] = self::getCellMoveStyles($row, $col, $filteredPossibleMoves);

        $cellClass = self::getCellClass($row, $col);
        $selectedClass = self::isSelectedCell($selectedCell, $row, $col) ? 'selected' : '';
        $piece = $boardData[$row][$col] ?? null;

        return self::renderCell($row, $col, $cellClass, $selectedClass, $possibleMoveClass, $boxShadowStyle, $piece);
    }

    /**
     * Отримання стилів для можливих ходів
     */
    private static function getCellMoveStyles(int $row, int $col, array $filteredPossibleMoves): array
    {
        $possibleMoveClass = '';
        $boxShadowStyle = '';

        foreach ($filteredPossibleMoves as $move) {
            if ($move['row'] === $row && $move['col'] === $col) {
                $possibleMoveClass = 'possible-move';
                if (!empty($move['isCapture'])) {
                    $boxShadowStyle = 'inset 0 0 20px rgba(255, 0, 0, 0.7)';
                }
                break;
            }
        }

        return [$possibleMoveClass, $boxShadowStyle];
    }

    /**
     * Визначення класу клітинки (світла/темна)
     */
    private static function getCellClass(int $row, int $col): string
    {
        return (($row + $col) % 2 === 0) ? 'light' : 'dark';
    }

    /**
     * Перевірка чи є клітинка вибраною
     */
    private static function isSelectedCell(?array $selectedCell, int $row, int $col): bool
    {
        return $selectedCell !== null && $selectedCell['row'] === $row && $selectedCell['col'] === $col;
    }

    /**
     * Отримання відфільтрованих можливих ходів
     */
    private static function getFilteredPossibleMoves(?array $selectedCell, array $boardData, GameManager $gameManager): array
    {
        if (!$selectedCell) {
            return [];
        }

        $selectedPiece = $boardData[$selectedCell['row']][$selectedCell['col']] ?? null;
        if (!$selectedPiece) {
            return [];
        }

        if (!self::canPlayerMovePiece($selectedPiece, $gameManager)) {
            return [];
        }

        $possibleMoves = $selectedPiece->getPossibleMoves($selectedCell['row'], $selectedCell['col'], $gameManager->getBoard());

        return self::filterMovesByCaptures($possibleMoves, $selectedPiece->getColor(), $gameManager);
    }

    /**
     * Перевірка чи може гравець рухати фігуру
     */
    private static function canPlayerMovePiece($selectedPiece, GameManager $gameManager): bool
    {
        if ($gameManager->getGameMode() === 'player_vs_bot') {
            return $selectedPiece->getColor() === $gameManager->getHumanPlayerColor();
        }

        return $selectedPiece->getColor() === $gameManager->getCurrentPlayer();
    }

    /**
     * Фільтрація ходів за захопленнями
     */
    private static function filterMovesByCaptures(array $possibleMoves, string $pieceColor, GameManager $gameManager): array
    {
        $playerHasCaptures = $gameManager->playerHasCaptures($pieceColor);

        if ($playerHasCaptures) {
            return array_filter($possibleMoves, fn($move) => $move['isCapture']);
        }

        return $possibleMoves;
    }

    /**
     * Рендеринг HTML для окремої клітинки
     */
    private static function renderCell(int $row, int $col, string $cellClass, string $selectedClass, string $possibleMoveClass, string $boxShadowStyle, $piece = null): string
    {
        $html = self::createCellForm($row, $col);
        $html .= self::createCellButton($row, $col, $cellClass, $selectedClass, $possibleMoveClass, $boxShadowStyle);

        if ($piece) {
            $html .= self::renderPiece($piece);
        }

        $html .= "</button></form>";
        return $html;
    }

    /**
     * Створення форми для клітинки
     */
    private static function createCellForm(int $row, int $col): string
    {
        $html = "<form action='index.php' method='post' style='display:inline;'>";
        $html .= "<input type='hidden' name='action' value='move'>";
        $html .= "<input type='hidden' name='row' value='{$row}'>";
        $html .= "<input type='hidden' name='col' value='{$col}'>";

        return $html;
    }

    /**
     * Створення кнопки клітинки
     */
    private static function createCellButton(int $row, int $col, string $cellClass, string $selectedClass, string $possibleMoveClass, string $boxShadowStyle): string
    {
        return "<button type='submit' class='cell {$cellClass} {$selectedClass} {$possibleMoveClass}' data-row='{$row}' data-col='{$col}' style='box-shadow: {$boxShadowStyle};'>";
    }

    /**
     * Рендеринг фігури
     */
    private static function renderPiece($piece): string
    {
        $kingClass = $piece->isKing() ? ' king' : '';
        return "<div class='piece {$piece->getColor()}{$kingClass}'></div>";
    }
}

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
        if (
            $this->gameManager->getMessage() === 'Ви повинні продовжити бити!' &&
            $this->gameManager->getSelectedCell() !== null
        ) {
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

        $_SESSION['game_start_time'] = time();
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

// Допоміжна функція для рендерингу дошки (для зворотної сумісності)
function renderBoard(array $boardData, ?array $selectedCell, GameManager $gameManager): string
{
    return GameRenderer::renderBoard($boardData, $selectedCell, $gameManager);
}
?>