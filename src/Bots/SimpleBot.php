<?php
require_once 'src/Interfaces/BotInterface.php';
require_once 'src/Interfaces/BoardInterface.php';

class SimpleBot implements BotInterface
{
    public function makeMove(BoardInterface $board, string $botColor): array
    {
        $captures = $this->findAllCaptures($board, $botColor);
        if (!empty($captures)) {
            return $this->selectRandomMove($captures);
        }

        $moves = $this->findAllRegularMoves($board, $botColor);
        if (!empty($moves)) {
            return $this->selectRandomMove($moves);
        }

        return []; 
    }
    private function findAllCaptures(BoardInterface $board, string $color): array
    {
        $captures = [];

        for ($r = 0; $r < 8; $r++) {
            for ($c = 0; $c < 8; $c++) {
                if ($this->isOwnPieceAt($board, $r, $c, $color)) {
                    $capturesFromCell = $this->getCapturesFromCell($board, $r, $c);
                    $captures = array_merge($captures, $capturesFromCell);
                }
            }
        }

        return $captures;
    }

    private function isOwnPieceAt(BoardInterface $board, int $row, int $col, string $color): bool
    {
        $piece = $board->getPiece($row, $col);
        return $piece !== null && $piece->getColor() === $color;
    }

    private function getCapturesFromCell(BoardInterface $board, int $row, int $col): array
    {
        $piece = $board->getPiece($row, $col);
        $captures = [];

        foreach ($piece->getPossibleCaptures($row, $col, $board) as $capture) {
            $captures[] = [
                'fromRow' => $row,
                'fromCol' => $col,
                'toRow' => $capture['toRow'],
                'toCol' => $capture['toCol'],
                'captured' => $capture['captured'],
            ];
        }

        return $captures;
    }


    private function findAllRegularMoves(BoardInterface $board, string $color): array
    {
        $moves = [];

        $piecesPositions = $this->getPiecesPositionsByColor($board, $color);

        foreach ($piecesPositions as $position) {
            $possibleMoves = $this->getPossibleNonCaptureMovesForPiece($board, $position['row'], $position['col']);
            foreach ($possibleMoves as $move) {
                $moves[] = $move;
            }
        }

        return $moves;
    }

    private function getPiecesPositionsByColor(BoardInterface $board, string $color): array
    {
        $positions = [];
        for ($r = 0; $r < 8; $r++) {
            for ($c = 0; $c < 8; $c++) {
                $piece = $board->getPiece($r, $c);
                if ($piece && $piece->getColor() === $color) {
                    $positions[] = ['row' => $r, 'col' => $c];
                }
            }
        }
        return $positions;
    }

    private function getPossibleNonCaptureMovesForPiece(BoardInterface $board, int $row, int $col): array
    {
        $moves = [];
        $piece = $board->getPiece($row, $col);
        if (!$piece) {
            return $moves;
        }

        foreach ($piece->getPossibleMoves($row, $col, $board) as $move) {
            if (!($move['isCapture'] ?? false)) {
                $moves[] = [
                    'fromRow' => $row,
                    'fromCol' => $col,
                    'toRow' => $move['row'],
                    'toCol' => $move['col']
                ];
            }
        }

        return $moves;
    }


    private function selectRandomMove(array $moves): array
    {
        return $moves[array_rand($moves)];
    }
}