<?php
require_once 'game.php';
?>
<!DOCTYPE html>
<html lang="uk">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Шашки (PHP)</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<style>
    .piece.white {
        background-image: url('assets/Figure1_.png');
    }

    .piece.black {
        background-image: url('assets/Figure_2_.png');
    }
</style>

<body>
    <div class="game-container">
        <h1>🏁 Шашки 🏁</h1>

        <div class="game-info">
            <div>Поточний гравець: <strong><?php echo ucfirst($currentPlayer); ?></strong></div>
            <div>Статус: <span><?php echo $gameStatus; ?></span></div>
            <?php if ($gameMode === 'player_vs_bot'): ?>
                <div>Ваш колір: <strong><?php echo ucfirst($humanPlayerColor); ?></strong></div>
            <?php endif; ?>
        </div>

        <div class="game-mode-selection">
            <form action="index.php" method="post" style="display:inline-block;">
                <label for="game_mode">Режим гри:</label>
                <select name="game_mode" id="game_mode" onchange="togglePlayerColorSelection()">
                    <option value="player_vs_player" <?php echo ($gameMode === 'player_vs_player') ? 'selected' : ''; ?>>
                        Гравець проти гравця</option>
                    <option value="player_vs_bot" <?php echo ($gameMode === 'player_vs_bot') ? 'selected' : ''; ?>>Гравець
                        проти бота</option>
                </select>
                
                <div id="player_color_selection" style="display: <?php echo ($gameMode === 'player_vs_bot') ? 'inline-block' : 'none'; ?>; margin-left: 15px;">
                    <label for="player_color">Ваш колір:</label>
                    <select name="player_color" id="player_color">
                        <option value="white" <?php echo ($humanPlayerColor === 'white') ? 'selected' : ''; ?>>Білі</option>
                        <option value="black" <?php echo ($humanPlayerColor === 'black') ? 'selected' : ''; ?>>Чорні</option>
                    </select>
                </div>
                
                <input type="hidden" name="action" value="reset">
                <button type="submit">Почати нову гру</button>
            </form>
        </div>

        <?php echo renderBoard($boardData, $selectedCell, $gameManager); ?>

        <div class="controls">
            <form action="index.php" method="post">
                <input type="hidden" name="action" value="reset">
                <input type="hidden" name="game_mode" value="<?php echo htmlspecialchars($gameMode); ?>">
                <input type="hidden" name="player_color" value="<?php echo htmlspecialchars($humanPlayerColor); ?>">
                <button type="submit" class="btn">🔄 Перезапустити поточну гру</button>
            </form>
        </div>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        window.gameData = {
            selectedCell: {
                row: <?php echo json_encode($selectedCell['row'] ?? null); ?>,
                col: <?php echo json_encode($selectedCell['col'] ?? null); ?>
            }
        };

        function togglePlayerColorSelection() {
            const gameModeSelect = document.getElementById('game_mode');
            const playerColorDiv = document.getElementById('player_color_selection');
            
            if (gameModeSelect.value === 'player_vs_bot') {
                playerColorDiv.style.display = 'inline-block';
            } else {
                playerColorDiv.style.display = 'none';
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            togglePlayerColorSelection();
        });
    </script>
    <script src="js/game.js"></script>
</body>

</html>