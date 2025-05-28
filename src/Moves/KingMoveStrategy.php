<?php
require_once 'src/Interfaces/MoveStrategyInterface.php';
require_once 'src/Interfaces/BoardInterface.php';
require_once 'src/Interfaces/PieceInterface.php';

class KingMoveStrategy implements MoveStrategyInterface
{
    public function isValidMove(int $fromRow, int $fromCol, int $toRow, int $toCol, BoardInterface $board, string $pieceColor): bool
    {
        // Заглушка: дамки можуть рухатися по діагоналі на будь-яку відстань
        if (abs($fromRow - $toRow) !== abs($fromCol - $toCol)) {
            return false; // Тільки діагональні ходи
        }

        $rowStep = ($toRow > $fromRow) ? 1 : -1;
        $colStep = ($toCol > $fromCol) ? 1 : -1;

        $curRow = $fromRow + $rowStep;
        $curCol = $fromCol + $colStep;

        while ($curRow !== $toRow) {
            if ($board->getPiece($curRow, $curCol) !== null) {
                return false; // Шлях заблокований
            }
            $curRow += $rowStep;
            $curCol += $colStep;
        }

        // Перевірка цільової клітинки
        return $board->getPiece($toRow, $toCol) === null;
    }

    public function getPossibleMoves(int $row, int $col, BoardInterface $board, string $pieceColor): array
    {
        $moves = [];
        $directions = [
            ['row' => -1, 'col' => -1], ['row' => -1, 'col' => 1],
            ['row' => 1, 'col' => -1], ['row' => 1, 'col' => 1],
        ];

        foreach ($directions as $dir) {
            for ($i = 1; $i < 8; $i++) { // Король може рухатись на будь-яку відстань
                $toRow = $row + $i * $dir['row'];
                $toCol = $col + $i * $dir['col'];

                if ($toRow < 0 || $toRow >= 8 || $toCol < 0 || $toCol >= 8) {
                    break; // Вийшли за межі дошки
                }

                if ($board->getPiece($toRow, $toCol) === null) {
                    $moves[] = ['row' => $toRow, 'col' => $toCol, 'isCapture' => false];
                } else {
                    break; // Зустріли фігуру, не можемо пройти далі
                }
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
            $capturedPiecesInDirection = [];
            $foundOpponentPiece = false;
            $opponentRow = -1;
            $opponentCol = -1;

            for ($i = 1; $i < 8; $i++) {
                $checkRow = $row + $i * $dir['row'];
                $checkCol = $col + $i * $dir['col'];

                if ($checkRow < 0 || $checkRow >= 8 || $checkCol < 0 || $checkCol >= 8) {
                    break; // Вийшли за межі дошки
                }

                $checkedPiece = $board->getPiece($checkRow, $checkCol);

                if ($checkedPiece === null) {
                    if ($foundOpponentPiece) { // Після взяття місце вільне
                        $captures[] = [
                            'row' => $checkRow,
                            'col' => $checkCol,
                            'isCapture' => true,
                            'captured' => $capturedPiecesInDirection // Взяті фігури на цьому ходу
                        ];
                    }
                } else {
                    if ($checkedPiece->getColor() === $pieceColor) {
                        break; // Зустріли свою фігуру, не можемо пройти
                    } else {
                        // Знайшли фігуру противника
                        if ($foundOpponentPiece) {
                            break; // Вже знайшли одну фігуру противника, не можна бити дві
                        }
                        $foundOpponentPiece = true;
                        $opponentRow = $checkRow;
                        $opponentCol = $checkCol;
                        $capturedPiecesInDirection[] = ['row' => $opponentRow, 'col' => $opponentCol];
                    }
                }
            }
        }
        return $captures;
    }
}