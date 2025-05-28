<?php
interface PieceInterface
{
    public function getColor(): string;
    public function isKing(): bool;
    public function promote(): void;
    public function isValidMove(int $fromRow, int $fromCol, int $toRow, int $toCol, BoardInterface $board): bool;
    public function canCapture(int $row, int $col, BoardInterface $board): bool;
    public function getPossibleMoves(int $row, int $col, BoardInterface $board): array;
    public function getPossibleCaptures(int $row, int $col, BoardInterface $board): array;
    public function setMoveStrategy(MoveStrategyInterface $strategy): void; 
    public function __wakeup(); 
}