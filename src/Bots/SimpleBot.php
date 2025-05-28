<?php
require_once 'src/Interfaces/BotInterface.php';
require_once 'src/Interfaces/BoardInterface.php';

class SimpleBot implements BotInterface
{
    public function makeMove(BoardInterface $board, string $color): ?array
    {
        $allPossibleCaptures = $this->findAllCaptures($board, $color);

        if (!empty($allPossibleCaptures)) {
            return $this->selectRandomMove($allPossibleCaptures);
        }

        $allRegularMoves = $this->findAllRegularMoves($board, $color);

        if (!empty($allRegularMoves)) {
            return $this->selectRandomMove($allRegularMoves);
        }

        return null; 
    }

    private function findAllCaptures(BoardInterface $board, string $color): array
    {
        $captures = [];
        $piecesPositions = $this->getPiecesPositionsByColor($board, $color);

        foreach ($piecesPositions as $position) {
            $piece = $board->getPiece($position['row'], $position['col']);
            if ($piece && $piece->canCapture($position['row'], $position['col'], $board)) {
                $possibleCaptures = $piece->getPossibleCaptures($position['row'], $position['col'], $board);
                foreach ($possibleCaptures as $capture) {
                    $captures[] = [
                        'fromRow' => $position['row'],
                        'fromCol' => $position['col'],
                        'toRow' => $capture['row'],
                        'toCol' => $capture['col'],
                        'isCapture' => true,
                        'captured' => $capture['captured'] 
                    ];
                }
            }
        }
        return $captures;
    }

    private function findAllRegularMoves(BoardInterface $board, string $color): array
    {
        $moves = [];
        $piecesPositions = $this->getPiecesPositionsByColor($board, $color);

        foreach ($piecesPositions as $position) {
            $piece = $board->getPiece($position['row'], $position['col']);
            if ($piece) {
                $possibleMoves = $piece->getPossibleMoves($position['row'], $position['col'], $board);
                foreach ($possibleMoves as $move) {
                    if (!($move['isCapture'] ?? false)) { 
                        $moves[] = [
                            'fromRow' => $position['row'],
                            'fromCol' => $position['col'],
                            'toRow' => $move['row'],
                            'toCol' => $move['col']
                        ];
                    }
                }
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

    private function selectRandomMove(array $moves): array
    {
        if (empty($moves)) {
            return [];
        }
        return $moves[array_rand($moves)];
    }
}