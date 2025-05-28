<?php
session_start();

require_once 'src/Board.php';
require_once 'src/GameManager.php';
require_once 'src/Bots/BotFactory.php';
require_once 'src/Interfaces/BotInterface.php';
require_once 'src/Board.php';
require_once 'src/Validation/CheckersMoveValidator.php';


function renderBoard(array $boardData, ?array $selectedCell, GameManager $gameManager): string
{
    $html = '<div class="board" id="board">';

    $filteredPossibleMoves = getFilteredPossibleMoves($selectedCell, $boardData, $gameManager);

    for ($row = 0; $row < 8; $row++) {
        for ($col = 0; $col < 8; $col++) {
            [$possibleMoveClass, $boxShadowStyle] = getCellMoveStyles($row, $col, $filteredPossibleMoves);

            $cellClass = getCellClass($row, $col);
            $selectedClass = isSelectedCell($selectedCell, $row, $col) ? 'selected' : '';

            $html .= renderCell(
                $row,
                $col,
                $cellClass,
                $selectedClass,
                $possibleMoveClass,
                $boxShadowStyle,
                $boardData[$row][$col] ?? null
            );
        }
    }

    $html .= '</div>';

    return $html;
}

function getCellMoveStyles(int $row, int $col, array $filteredPossibleMoves): array
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

function getCellClass(int $row, int $col): string
{
    return (($row + $col) % 2 === 0) ? 'light' : 'dark';
}

function isSelectedCell(?array $selectedCell, int $row, int $col): bool
{
    return $selectedCell !== null && $selectedCell['row'] === $row && $selectedCell['col'] === $col;
}

function getFilteredPossibleMoves(?array $selectedCell, array $boardData, GameManager $gameManager): array
{
    if (!$selectedCell) {
        return [];
    }

    $selectedPiece = $boardData[$selectedCell['row']][$selectedCell['col']] ?? null;

    if (!$selectedPiece || $selectedPiece->getColor() !== $gameManager->getCurrentPlayer()) {
        return [];
    }

    $possibleMoves = $selectedPiece->getPossibleMoves($selectedCell['row'], $selectedCell['col'], $gameManager->getBoard());

    $playerHasCaptures = $gameManager->playerHasCaptures($gameManager->getCurrentPlayer());

    if ($playerHasCaptures) {
        return array_filter($possibleMoves, fn($move) => $move['isCapture']);
    }

    return $possibleMoves;
}

function renderCell(int $row, int $col, string $cellClass, string $selectedClass, string $possibleMoveClass, string $boxShadowStyle, $piece = null): string
{
    $html = "<form action='index.php' method='post' style='display:inline;'>";
    $html .= "<input type='hidden' name='action' value='move'>";
    $html .= "<input type='hidden' name='row' value='{$row}'>";
    $html .= "<input type='hidden' name='col' value='{$col}'>";
    $html .= "<button type='submit' class='cell {$cellClass} {$selectedClass} {$possibleMoveClass}' data-row='{$row}' data-col='{$col}' style='box-shadow: {$boxShadowStyle};'>";

    if ($piece) {
        $kingClass = $piece->isKing() ? ' king' : '';
        $html .= "<div class='piece {$piece->getColor()}{$kingClass}'></div>";
    }

    $html .= "</button></form>";

    return $html;
}

if (isset($_SESSION['game_state'])) {
    $gameManager = unserialize($_SESSION['game_state']);
    if ($gameManager->getMessage() === 'Ви повинні продовжити бити!' && $gameManager->getSelectedCell() !== null) {
    } else {
        $gameManager->showMessage(''); 
    }
} else {
    $gameManager = GameManager::getInstance();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'reset') {
            $gameMode = $_POST['game_mode'] ?? 'player_vs_player';
            $gameManager->resetGame($gameMode);
        } elseif ($_POST['action'] === 'move') {
            $row = isset($_POST['row']) ? (int) $_POST['row'] : null;
            $col = isset($_POST['col']) ? (int) $_POST['col'] : null;
            $gameManager->handleAction($row, $col);
        }
    }
}

if ($gameManager->getGameMode() === 'player_vs_bot' && 
    $gameManager->getCurrentPlayer() === 'black' && 
    $gameManager->getGameStatus() !== 'Пат' && 
    $gameManager->getGameStatus() !== 'Чорні перемогли') {
    $gameManager->makeBotMove();
}

$_SESSION['game_state'] = serialize($gameManager);

$boardData = $gameManager->getBoardData();
$currentPlayer = $gameManager->getCurrentPlayer();
$selectedCell = $gameManager->getSelectedCell();
$gameStatus = $gameManager->getGameStatus();
$message = $gameManager->getMessage();
$messageType = $gameManager->getMessageType();
$gameMode = $gameManager->getGameMode();
?>