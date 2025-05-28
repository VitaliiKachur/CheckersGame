<?php
require_once 'src/Interfaces/MoveStrategyInterface.php';
require_once 'src/Interfaces/BoardInterface.php';
require_once 'src/Interfaces/PieceInterface.php';

class KingMoveStrategy implements MoveStrategy
{
    public function isValidMove(int $fromRow, int $fromCol, int $toRow, int $toCol, BoardInterface $board, PieceInterface $piece): bool
    {
        return $this->isDiagonalMove($fromRow, $fromCol, $toRow, $toCol)
            && $this->isTargetEmpty($toRow, $toCol, $board)
            && $this->isPathClear($fromRow, $fromCol, $toRow, $toCol, $board);
    }

    public function canCapture(int $fromRow, int $fromCol, int $toRow, int $toCol, BoardInterface $board, PieceInterface $piece): array
    {
        if (!$this->isDiagonalMove($fromRow, $fromCol, $toRow, $toCol) || !$this->isTargetEmpty($toRow, $toCol, $board)) {
            return [];
        }

        return $this->findCapturableEnemy($fromRow, $fromCol, $toRow, $toCol, $board, $piece);
    }

    private function isDiagonalMove(int $r1, int $c1, int $r2, int $c2): bool
    {
        return abs($r1 - $r2) === abs($c1 - $c2);
    }

    private function isTargetEmpty(int $row, int $col, BoardInterface $board): bool
    {
        return $board->getPiece($row, $col) === null;
    }

    private function isPathClear(int $fromR, int $fromC, int $toR, int $toC, BoardInterface $board): bool
    {
        [$rowStep, $colStep] = $this->getStep($fromR, $fromC, $toR, $toC);

        for ($r = $fromR + $rowStep, $c = $fromC + $colStep; $r !== $toR && $c !== $toC; $r += $rowStep, $c += $colStep) {
            if (!$this->isInBounds($r, $c) || $board->getPiece($r, $c) !== null) {
                return false;
            }
        }

        return true;
    }

    private function findCapturableEnemy(
        int $fromR,
        int $fromC,
        int $toR,
        int $toC,
        BoardInterface $board,
        PieceInterface $piece
    ): array {
        [$rowStep, $colStep] = $this->getStep($fromR, $fromC, $toR, $toC);

        $piecesOnPath = $this->getPiecesOnPath($fromR, $fromC, $toR, $toC, $rowStep, $colStep, $board);

        return $this->analyzeCapturedPieces($piecesOnPath, $piece);
    }

    private function getPiecesOnPath(
        int $fromR,
        int $fromC,
        int $toR,
        int $toC,
        int $rowStep,
        int $colStep,
        BoardInterface $board
    ): array {
        $pieces = [];

        for (
            $r = $fromR + $rowStep, $c = $fromC + $colStep;
            $r !== $toR && $c !== $toC;
            $r += $rowStep, $c += $colStep
        ) {
            if (!$this->isInBounds($r, $c)) {
                return [];
            }

            $current = $board->getPiece($r, $c);
            if ($current !== null) {
                $pieces[] = ['row' => $r, 'col' => $c, 'piece' => $current];
            }
        }

        return $pieces;
    }

    private function analyzeCapturedPieces(array $piecesOnPath, PieceInterface $piece): array
    {
        $enemyFound = false;
        $captured = [];

        foreach ($piecesOnPath as $pos) {
            $currentPiece = $pos['piece'];
            if ($currentPiece->getColor() === $piece->getColor()) {
                return [];
            }
            if ($enemyFound) {
                return [];
            }
            $captured[] = ['row' => $pos['row'], 'col' => $pos['col']];
            $enemyFound = true;
        }

        return count($captured) === 1 ? $captured : [];
    }


    private function getStep(int $fromR, int $fromC, int $toR, int $toC): array
    {
        $rowStep = $toR > $fromR ? 1 : -1;
        $colStep = $toC > $fromC ? 1 : -1;
        return [$rowStep, $colStep];
    }

    private function isInBounds(int $row, int $col): bool
    {
        return $row >= 0 && $row < 8 && $col >= 0 && $col < 8;
    }
}