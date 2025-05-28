
<?php
require_once 'src/Interfaces/MoveStrategyInterface.php';
require_once 'src/Interfaces/BoardInterface.php';
require_once 'src/Interfaces/PieceInterface.php'; // Для type hinting

class RegularMoveStrategy implements MoveStrategyInterface
{
    public function isValidMove(int $fromRow, int $fromCol, int $toRow, int $toCol, BoardInterface $board, string $pieceColor): bool
    {
        // Заглушка: поки що дозволяємо будь-який діагональний хід на 1 клітинку
        $dr = abs($toRow - $fromRow);
        $dc = abs($toCol - $fromCol);

        if ($dr !== 1 || $dc !== 1) {
            return false; // Тільки діагональні ходи на 1 клітинку
        }

        // Перевірка на зайнятість цільової клітинки
        if ($board->getPiece($toRow, $toCol) !== null) {
            return false;
        }

        // Для білих шашок - тільки вперед (зменшення рядка)
        if ($pieceColor === 'white' && $toRow >= $fromRow) {
            return false;
        }
        // Для чорних шашок - тільки вперед (збільшення рядка)
        if ($pieceColor === 'black' && $toRow <= $fromRow) {
            return false;
        }

        return true;
    }

    public function getPossibleMoves(int $row, int $col, BoardInterface $board, string $pieceColor): array
    {
        $moves = [];
        $direction = ($pieceColor === 'white') ? -1 : 1; // -1 для білих (вгору), 1 для чорних (вниз)

        $possibleCoords = [
            ['row' => $row + $direction, 'col' => $col - 1],
            ['row' => $row + $direction, 'col' => $col + 1],
        ];

        foreach ($possibleCoords as $coords) {
            $toRow = $coords['row'];
            $toCol = $coords['col'];

            if ($toRow >= 0 && $toRow < 8 && $toCol >= 0 && $toCol < 8 && $board->getPiece($toRow, $toCol) === null) {
                $moves[] = ['row' => $toRow, 'col' => $toCol, 'isCapture' => false];
            }
        }
        return $moves;
    }

    public function getPossibleCaptures(int $row, int $col, BoardInterface $board, string $pieceColor): array
    {
        $captures = [];
        $directions = [
            ['row' => -1, 'col' => -1], ['row' => -1, 'col' => 1],
            ['row' => 1, 'col' => -1], ['row' => 1, 'col' => 1],
        ];

        foreach ($directions as $dir) {
            $captureRow = $row + $dir['row'];
            $captureCol = $col + $dir['col'];
            $toRow = $row + 2 * $dir['row'];
            $toCol = $col + 2 * $dir['col'];

            if (
                $toRow >= 0 && $toRow < 8 && $toCol >= 0 && $toCol < 8 &&
                $board->getPiece($toRow, $toCol) === null
            ) {
                $capturedPiece = $board->getPiece($captureRow, $captureCol);
                if ($capturedPiece && $capturedPiece->getColor() !== $pieceColor) {
                    $captures[] = [
                        'row' => $toRow,
                        'col' => $toCol,
                        'isCapture' => true,
                        'captured' => [['row' => $captureRow, 'col' => $captureCol]]
                    ];
                }
            }
        }
        return $captures;
    }
}