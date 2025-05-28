<?php

class GameEndDetector
{
    private Board $board;
    private MessageService $messageService;

    public function __construct(Board $board, MessageService $messageService)
    {
        $this->board = $board;
        $this->messageService = $messageService;
    }

    public function checkGameEnd(GameState $gameState): void
    {
        if ($this->isVictory('white', $gameState)) return;
        if ($this->isVictory('black', $gameState)) return;
        if ($this->isStalemate($gameState->getCurrentPlayer(), $gameState)) return;
    }

    private function isVictory(string $color, GameState $gameState): bool
    {
        $remaining = $this->board->countPieces($color);
        if ($remaining === 0) {
            $gameState->setGameOver(true);
            $winner = $color === 'white' ? 'Чорні' : 'Білі';
            $gameState->setGameStatus("$winner перемогли");
            $this->messageService->showMessage("{$winner} перемогли!", 'success');
            return true;
        }
        return false;
    }

    private function isStalemate(string $player, GameState $gameState): bool
    {
        if (!$this->playerHasValidMoves($player)) {
            $gameState->setGameOver(true);
            $gameState->setGameStatus('Пат');
            $this->messageService->showMessage("Пат! Гра закінчилася нічиєю, у " . ($player === 'white' ? 'білих' : 'чорних') . " немає дійсних ходів.", 'info');
            return true;
        }
        return false;
    }

    public function playerHasValidMoves(string $color): bool
    {
        $hasCaptures = $this->playerHasCaptures($color);

        for ($r = 0; $r < 8; $r++) {
            for ($c = 0; $c < 8; $c++) {
                $piece = $this->board->getPiece($r, $c);

                if ($this->isPlayerPiece($piece, $color)) {
                    if ($hasCaptures && $this->canPieceCapture($piece, $r, $c)) {
                        return true;
                    }

                    if (!$hasCaptures && $this->canPieceMove($piece, $r, $c)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    private function playerHasCaptures(string $color): bool
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

    private function isPlayerPiece(?PieceInterface $piece, string $color): bool
    {
        return $piece !== null && $piece->getColor() === $color;
    }

    private function canPieceCapture(PieceInterface $piece, int $row, int $col): bool
    {
        return !empty($piece->getPossibleCaptures($row, $col, $this->board));
    }

    private function canPieceMove(PieceInterface $piece, int $row, int $col): bool
    {
        return !empty($piece->getPossibleMoves($row, $col, $this->board));
    }
}
