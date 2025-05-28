<?php
session_start();

require_once 'src/Board.php';
require_once 'src/GameManager.php';

require_once 'src/Board.php';

function renderCell(int $row, int $col, string $cellClass, string $selectedClass, string $possibleMoveClass, string $boxShadowStyle, $piece = null): string
{
    $html = "<form action='index.php' method='post' style='display:inline;'>";
    $html .= "<input type='hidden' name='action' value='move'>"; 
    $html .= "<input type='hidden' name='row' value='{$row}'>";
    $html .= "<input type='hidden' name='col' value='{$col}'>";
    $html .= "<button type='submit' class='cell {$cellClass} {$selectedClass} {$possibleMoveClass}' data-row='{$row}' data-col='{$col}' style='box-shadow: {$boxShadowStyle};'>";

    if ($piece instanceof PieceInterface) { 
        $kingClass = $piece->isKing() ? ' king' : '';
        $html .= "<div class='piece {$piece->getColor()}{$kingClass}'></div>";
    }

    $html .= "</button></form>";

    return $html;
}
function getFilteredPossibleMoves(?array $selectedCell, array $boardData, GameManager $gameManager): array
{
    if (!$selectedCell) {
        return [];
    }

    $selectedPiece = $boardData[$selectedCell['row']][$selectedCell['col']] ?? null;

    if (!$selectedPiece || !($selectedPiece instanceof PieceInterface) || $selectedPiece->getColor() !== $gameManager->getCurrentPlayer()) {
        return [];
    }

    $possibleMoves = $selectedPiece->getPossibleMoves($selectedCell['row'], $selectedCell['col'], $gameManager->getBoard());

    $playerHasCaptures = $gameManager->playerHasCaptures($gameManager->getCurrentPlayer());

    if ($playerHasCaptures) {
        return array_filter($possibleMoves, fn($move) => ($move['isCapture'] ?? false)); 
    }

    return $possibleMoves;
}

function renderBoard(array $boardData, ?array $selectedCell, GameManager $gameManager): string
{
    $html = '<div class="board" id="board">';

    $filteredPossibleMoves = getFilteredPossibleMoves($selectedCell, $boardData, $gameManager);

    for ($row = 0; $row < 8; $row++) {
        for ($col = 0; $col < 8; $col++) {
            // Нова функція для стилів ходів
            $cellClass = getCellClass($row, $col);
            [$possibleMoveClass, $boxShadowStyle] = getCellMoveStyles($row, $col, $filteredPossibleMoves);


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

function isSelectedCell(?array $selectedCell, int $row, int $col): bool
{
    return $selectedCell !== null && $selectedCell['row'] === $row && $selectedCell['col'] === $col;
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
    if (isset($_POST['action']) && $_POST['action'] === 'reset') {
        $gameManager->resetGame('player_vs_player');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'reset') {
            $gameManager->resetGame('player_vs_player'); 
        } elseif ($_POST['action'] === 'move') {
            $row = isset($_POST['row']) ? (int) $_POST['row'] : null;
            $col = isset($_POST['col']) ? (int) $_POST['col'] : null;
            $gameManager->handleAction($row, $col);
        }
    }
}

$boardData = $gameManager->getBoardData();
$currentPlayer = $gameManager->getCurrentPlayer();
$selectedCell = $gameManager->getSelectedCell(); 
$gameStatus = $gameManager->getGameStatus();
$message = $gameManager->getMessage();
$messageType = $gameManager->getMessageType();
$gameMode = $gameManager->getGameMode(); 

?>
<!DOCTYPE html>
<html lang="uk">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Шашки (PHP) - Дошка</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: #f0f0f0;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }

        .game-container {
            background-color: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            text-align: center;
            width: 100%;
            max-width: 600px;
            box-sizing: border-box;
        }

        h1 {
            color: #333;
            margin-bottom: 20px;
            font-size: 2.2em;
        }

        .board {
            display: grid;
            grid-template-columns: repeat(8, 60px);
            grid-template-rows: repeat(8, 60px);
            border: 3px solid #333;
            margin: 0 auto 25px auto;
            width: fit-content;
            height: fit-content;
            background-color: #eee;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .cell {
            width: 60px;
            height: 60px;
            border: none;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: default;
            background-color: #eee;
            transition: background-color 0.2s ease, box-shadow 0.2s ease;
            position: relative;
        }

        .cell.dark {
            background-color: #8B4513;
        }

        .piece {
            width: 55px;
            height: 55px;
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0;
            border: none;
            box-shadow: none;
        }

        .piece.white {
            background-image: url('assets/Figure1_.png');
        }

        .piece.black {
            background-image: url('assets/Figure_2_.png');
        }

        .piece.king::after {
            content: '👑';
            font-size: 2.3em;
            position: absolute;
            margin-bottom: 10px;
        }

        .game-info {
            margin-bottom: 20px;
            font-size: 1.1em;
            color: #555;
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 10px;
        }

        .game-info strong {
            color: #000;
        }

        .controls {
            margin-top: 20px;
            display: flex;
            flex-direction: column;
            gap: 15px;
            align-items: center;
        }

        .btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .btn:hover {
            background-color: #45a049;
            transform: translateY(-2px);
        }

        .btn:active {
            transform: translateY(0);
        }

        .message {
            margin-left: 60px;
            margin-top: 20px;
            padding: 10px 15px;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
            width: 100%;
            max-width: 400px;
            box-sizing: border-box;
        }

        .message.info {
            background-color: #e7f3fe;
            color: #0366d6;
            border: 1px solid #cce5ff;
        }

        .cell.selected {
            background-color: #FFD700;
            border: 2px solid #DAA520;
            box-sizing: border-box;
        }

        .cell.possible-move {
            background-color: #7FFF00;
            border: 2px solid #6B8E23;
            box-sizing: border-box;
        }
    </style>
</head>

<body>
    <div class="game-container">

        <h1>🏁 Шашки 🏁</h1>
        <div class="game-info">
            <div>Поточний гравець: <strong><?php echo ucfirst($currentPlayer); ?></strong></div>
            <div>Статус: <span><?php echo $gameStatus; ?></span></div>
        </div>

        <!--  echo renderBoard($boardData); ?>  -->

        <div class="controls">
            <form action="index.php" method="post">
                <input type="hidden" name="action" value="reset">
                <button type="submit" class="btn">🔄 Перезапустити гру</button>
            </form>
        </div>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <div class="game-container">
            <?php echo renderBoard($boardData, $selectedCell, $gameManager); ?>
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const board = document.getElementById('board');
                    const initialSelectedRow = <?php echo json_encode($selectedCell['row'] ?? null); ?>;
                    const initialSelectedCol = <?php echo json_encode($selectedCell['col'] ?? null); ?>;

                    function markSelectedCell(row, col) {
                        if (row === null || col === null) return;
                        const cell = board.querySelector(`[data-row="<span class="math-inline">\{row\}"\]\[data\-col\="</span>{col}"]`);
                        if (cell) {
                            cell.classList.add('selected');
                        }
                    }
                    markSelectedCell(initialSelectedRow, initialSelectedCol);
                });
            </script>
        </div>
    </div>
</body>

</html>