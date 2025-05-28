<?php
session_start();

require_once 'src/Board.php';
require_once 'src/GameManager.php';

function getCellClass(int $row, int $col): string
{ /* ... */
}
function renderCell(int $row, int $col, string $cellClass, string $selectedClass, string $possibleMoveClass, string $boxShadowStyle, $piece = null): string
{ /* ... */
}
function renderBoard(array $boardData): string
{ /* ... */
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

$_SESSION['game_state'] = serialize($gameManager);

$boardData = $gameManager->getBoardData();
$currentPlayer = $gameManager->getCurrentPlayer();
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
    </style>
</head>
<body>
    <div class="game-container">
        <h1>🏁 Шашки 🏁</h1>
        <div class="game-info">
            <div>Поточний гравець: <strong><?php echo ucfirst($currentPlayer); ?></strong></div>
            <div>Статус: <span><?php echo $gameStatus; ?></span></div>
        </div>

        <?php echo renderBoard($boardData); ?>

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
    </div>
</body>
</html>