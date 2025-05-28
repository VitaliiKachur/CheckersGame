
<?php
require_once 'src/Interfaces/MoveStrategyInterface.php';
require_once 'src/Interfaces/BoardInterface.php';
require_once 'src/Interfaces/PieceInterface.php'; 

class RegularMoveStrategy implements MoveStrategy
{
    public function isValidMove(int $fromRow, int $fromCol, int $toRow, int $toCol, BoardInterface $board, PieceInterface $piece): bool
    {
        if (!($this->isDiagonal($fromRow, $fromCol, $toRow, $toCol) && $this->isOneStepForward($fromRow, $toRow, $piece->getColor()))) {
            return false;
        }
        return $board->getPiece($toRow, $toCol) === null;
    }

    public function canCapture(int $fromRow, int $fromCol, int $toRow, int $toCol, BoardInterface $board, PieceInterface $piece): array
    {
        if (!($this->isDiagonal($fromRow, $fromCol, $toRow, $toCol) && $this->isTwoStepsAway($fromRow, $toRow, $fromCol, $toCol))) {
            return [];
        }
        if ($board->getPiece($toRow, $toCol) !== null) {
            return [];
        }

        $middleRow = ($fromRow + $toRow) / 2;
        $middleCol = ($fromCol + $toCol) / 2;
        $capturedPiece = $board->getPiece($middleRow, $middleCol);

        if ($capturedPiece && $capturedPiece->getColor() !== $piece->getColor()) {
            return [['row' => $middleRow, 'col' => $middleCol]];
        }
        return [];
    }

    private function isDiagonal(int $r1, int $c1, int $r2, int $c2): bool
    {
        return abs($r1 - $r2) === abs($c1 - $c2);
    }

    private function isOneStepForward(int $fromR, int $toR, string $color): bool
    {
        if ($color === 'white') {
            return $toR === $fromR - 1;
        } else { // black
            return $toR === $fromR + 1;
        }
    }

    private function isTwoStepsAway(int $fromR, int $toR, int $fromC, int $toC): bool
    {
        return abs($fromR - $toR) === 2 && abs($fromC - $toC) === 2;
    }
}