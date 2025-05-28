<?php

require_once 'src/Interfaces/BoardInterface.php';
require_once 'src/Interfaces/PieceInterface.php';
require_once 'src/Pieces/WhitePiece.php';
require_once 'src/Pieces/BlackPiece.php'; 
require_once 'src/PieceFactory.php';

class Board implements BoardInterface
{
    private array $board;

    public function __construct()
    {
        $this->initializeBoard();
    }

    private function initializeBoard(): void
    {
        $this->board = array_fill(0, 8, array_fill(0, 8, null));

        // Розстановка шашок за допомогою PieceFactory
        for ($row = 0; $row < 3; $row++) {
            for ($col = 0; $col < 8; $col++) {
                if (($row + $col) % 2 !== 0) {
                    $this->board[$row][$col] = PieceFactory::createPiece('black');
                }
            }
        }

        for ($row = 5; $row < 8; $row++) {
            for ($col = 0; $col < 8; $col++) {
                if (($row + $col) % 2 !== 0) {
                    $this->board[$row][$col] = PieceFactory::createPiece('white');
                }
            }
        }
    }

    public function getPiece(int $row, int $col): ?PieceInterface
    {
        if ($row < 0 || $row >= 8 || $col < 0 || $col >= 8) {
            return null; // Запобігаємо виходу за межі масиву
        }
        return $this->board[$row][$col];
    }
    public function setPiece(int $row, int $col, ?PieceInterface $piece): void
    {
        $this->board[$row][$col] = $piece;
    }

    public function movePiece(int $fromRow, int $fromCol, int $toRow, int $toCol): void
    {
        $piece = $this->board[$fromRow][$fromCol];
        if ($piece) {
            $this->board[$toRow][$toCol] = $piece;
            $this->board[$fromRow][$fromCol] = null;
        }
    }

    public function removePiece(int $row, int $col): void
    {
        $this->board[$row][$col] = null;
    }

    public function getBoardState(): array
    {
        return $this->board;
    }

    public function countPieces(string $color): int
    {
        $count = 0;
        foreach ($this->board as $row) {
            foreach ($row as $piece) {
                if (is_array($piece) && $piece['color'] === $color) { 
                    $count++;
                } elseif ($piece instanceof PieceInterface && $piece->getColor() === $color) {
                    $count++;
                }
            }
        }
        return $count;
    }
    
}