<?php
interface MoveStrategyInterface
{
    public function isValidMove(int $fromRow, int $fromCol, int $toRow, int $toCol, BoardInterface $board, string $pieceColor): bool;
    public function getPossibleMoves(int $row, int $col, BoardInterface $board, string $pieceColor): array;
    public function getPossibleCaptures(int $row, int $col, BoardInterface $board, string $pieceColor): array;
}