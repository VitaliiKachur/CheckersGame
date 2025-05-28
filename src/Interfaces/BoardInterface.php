<?php

interface BoardInterface
{
    public function getPiece(int $row, int $col): ?PieceInterface;
    public function setPiece(int $row, int $col, ?PieceInterface $piece): void;
    public function movePiece(int $fromRow, int $fromCol, int $toRow, int $toCol): void;
    public function removePiece(int $row, int $col): void;
    public function getBoardState(): array;
    public function countPieces(string $color): int;
}