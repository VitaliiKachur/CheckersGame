<?php

interface BoardInterface
{
    public function getBoardState(): array;
    public function getPiece(int $row, int $col): ?Piece;
    public function setPiece(int $row, int $col, ?Piece $piece): void;
    public function movePiece(int $fromRow, int $fromCol, int $toRow, int $toCol): void;
    public function removePiece(int $row, int $col): void;
    public function hasPiece(int $row, int $col): bool;
}