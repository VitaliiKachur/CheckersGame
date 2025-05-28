<?php
require_once 'src/Interfaces/PieceInterface.php';
require_once 'src/Interfaces/MoveStrategyInterface.php'; // Буде створено пізніше

abstract class Piece implements PieceInterface
{
    protected string $color;
    protected bool $isKing = false;
    protected MoveStrategyInterface $moveStrategy; 

    public function __construct(string $color)
    {
        $this->color = $color;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function isKing(): bool
    {
        return $this->isKing;
    }

    public function promote(): void
    {
        $this->isKing = true;
    }

    public function isValidMove(int $fromRow, int $fromCol, int $toRow, int $toCol, BoardInterface $board): bool
    {
        return $this->moveStrategy->isValidMove($fromRow, $fromCol, $toRow, $toCol, $board, $this->color);
    }

    public function canCapture(int $row, int $col, BoardInterface $board): bool
    {
        return !empty($this->moveStrategy->getPossibleCaptures($row, $col, $board, $this->color));
    }

    public function getPossibleMoves(int $row, int $col, BoardInterface $board): array
    {
        return $this->moveStrategy->getPossibleMoves($row, $col, $board, $this->color);
    }

    public function getPossibleCaptures(int $row, int $col, BoardInterface $board): array
    {
        return $this->moveStrategy->getPossibleCaptures($row, $col, $board, $this->color);
    }

    public function setMoveStrategy(MoveStrategyInterface $strategy): void
    {
        $this->moveStrategy = $strategy;
    }

    public function __wakeup()
    {
        if ($this->isKing()) {
            $this->setMoveStrategy(new KingMoveStrategy());
        } else {
            $this->setMoveStrategy(new RegularMoveStrategy());
        }
    }
}