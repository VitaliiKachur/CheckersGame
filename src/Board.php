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
        $this->board = $this->createEmptyBoard();
        $this->placePieces('black', 0, 3);
        $this->placePieces('white', 5, 8);
    }

    private function createEmptyBoard(): array
    {
        return array_fill(0, 8, array_fill(0, 8, null));
    }

    private function placePieces(string $color, int $startRow, int $endRow): void
    {
        for ($row = $startRow; $row < $endRow; $row++) {
            for ($col = 0; $col < 8; $col++) {
                if ($this->isPlayableCell($row, $col)) {
                    $this->board[$row][$col] = PieceFactory::createPiece($color);
                }
            }
        }
    }

    private function isPlayableCell(int $row, int $col): bool
    {
        return ($row + $col) % 2 === 1;
    }


    public function getBoardState(): array
    {
        return $this->board;
    }
    public function getPiece(int $row, int $col): ?Piece
    {
        if (!isset($this->board[$row][$col])) { 
            return null;
        }
        return $this->board[$row][$col];
    }
    public function setPiece(int $row, int $col, ?Piece $piece): void
    {
        $this->board[$row][$col] = $piece;
    }
    public function hasPiece(int $row, int $col): bool
    {
        return isset($this->board[$row][$col]) && $this->board[$row][$col] !== null;
    }
    public function removePiece(int $row, int $col): void
    {
        $this->board[$row][$col] = null;
    }

    public function movePiece(int $fromRow, int $fromCol, int $toRow, int $toCol): void
    {
        $piece = $this->getPiece($fromRow, $fromCol);
        if ($piece) {
            $this->setPiece($toRow, $toCol, $piece);
            $this->removePiece($fromRow, $fromCol);
            $this->checkKingStatus($toRow, $toCol);
        }
    }

    private function checkKingStatus(int $row, int $col): void
    {
        $piece = $this->getPiece($row, $col);
        if ($piece && !$piece->isKing()) {
            if (
                ($piece->getColor() === 'white' && $row === 0) ||
                ($piece->getColor() === 'black' && $row === 7)
            ) {
                $piece->makeKing();
            }
        }
    }

    public function countPieces(string $color): int
    {
        $count = 0;
        foreach ($this->board as $row) {
            foreach ($row as $piece) {
                if ($piece && $piece->getColor() === $color) {
                    $count++;
                }
            }
        }
        return $count;
    }
}