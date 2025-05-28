<?php
session_start();

require_once 'src/Board.php'; 

function getCellClass(int $row, int $col): string
{
    return (($row + $col) % 2 === 0) ? 'light' : 'dark';
}

function renderCell(int $row, int $col, string $cellClass, string $selectedClass, string $possibleMoveClass, string $boxShadowStyle, $piece = null): string
{
    $html = "<div class='cell {$cellClass} {$selectedClass} {$possibleMoveClass}' data-row='{$row}' data-col='{$col}' style='box-shadow: {$boxShadowStyle};'>";
    if ($piece) {
        $kingClass = ($piece['isKing'] ?? false) ? ' king' : '';
        $html .= "<div class='piece {$piece['color']}{$kingClass}'></div>";
    }
    $html .= "</div>";
    return $html;
}

function renderBoard(array $boardData): string
{
    $html = '<div class="board" id="board">';
    for ($row = 0; $row < 8; $row++) {
        for ($col = 0; $col < 8; $col++) {
            $cellClass = getCellClass($row, $col);
            $html .= renderCell(
                $row,
                $col,
                $cellClass,
                '',
                '', 
                '', 
                $boardData[$row][$col] ?? null
            );
        }
    }
    $html .= '</div>';
    return $html;
}

$board = new Board(); 

$boardData = $board->getBoardState();

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
    </style>
</head>

<body>
    <div class="game-container">
        <h1>🏁 Шашки 🏁</h1>
        <?php echo renderBoard($boardData); ?>
    </div>
</body>

</html>