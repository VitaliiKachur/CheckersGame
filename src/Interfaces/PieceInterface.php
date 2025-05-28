<?php
interface PieceInterface
{
    public function getColor(): string;
    public function isKing(): bool;
    public function makeKing(): void;
    public function isValidMove(int $fromRow, int $fromCol, int $toRow, int $toCol, BoardInterface $board): bool;
    public function canCapture(int $fromRow, int $fromCol, BoardInterface $board): bool;
    public function getPossibleMoves(int $row, int $col, BoardInterface $board): array;
    public function getPossibleCaptures(int $row, int $col, BoardInterface $board): array;
}