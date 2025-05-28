<?php
interface MoveStrategy
{
    public function isValidMove(int $fromRow, int $fromCol, int $toRow, int $toCol, BoardInterface $board, PieceInterface $piece): bool;
    public function canCapture(int $fromRow, int $fromCol, int $toRow, int $toCol, BoardInterface $board, PieceInterface $piece): array;
}