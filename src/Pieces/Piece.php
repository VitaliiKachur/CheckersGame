<?php
require_once 'src/Interfaces/PieceInterface.php';
require_once 'src/Interfaces/MoveStrategyInterface.php';

abstract class Piece implements PieceInterface
{
    protected string $color;
    protected bool $isKing;
    protected ?MoveStrategy $moveStrategy = null; 

    public function __construct(string $color, bool $isKing = false)
    {
        $this->color = $color;
        $this->isKing = $isKing;
        $this->setMoveStrategy();
    }

    public function __wakeup()
    {
        $this->setMoveStrategy();
    }

    public function getColor(): string
    {
        return $this->color;
    }
    public function isKing(): bool
    {
        return $this->isKing;
    }
    public function makeKing(): void
    {
        $this->isKing = true;
        $this->setMoveStrategy();
    }

    private function setMoveStrategy(): void
    {
        $this->moveStrategy = $this->isKing ? new KingMoveStrategy() : new RegularMoveStrategy();
    }

    public function isValidMove(int $fromRow, int $fromCol, int $toRow, int $toCol, BoardInterface $board): bool
    {
        if ($this->moveStrategy === null)
            $this->setMoveStrategy(); 
        return $this->moveStrategy->isValidMove($fromRow, $fromCol, $toRow, $toCol, $board, $this);
    }

    public function canCapture(int $fromRow, int $fromCol, BoardInterface $board): bool
    {
        if ($this->moveStrategy === null)
            $this->setMoveStrategy();
        for ($r = 0; $r < 8; $r++) {
            for ($c = 0; $c < 8; $c++) {
                if (!empty($this->moveStrategy->canCapture($fromRow, $fromCol, $r, $c, $board, $this))) {
                    return true;
                }
            }
        }
        return false;
    }

    public function getPossibleMoves(int $row, int $col, BoardInterface $board): array
    {
        if ($this->moveStrategy === null)
            $this->setMoveStrategy(); 
        $moves = [];
        for ($r = 0; $r < 8; $r++) {
            for ($c = 0; $c < 8; $c++) {
                $capturesForThisPiece = $this->moveStrategy->canCapture($row, $col, $r, $c, $board, $this);
                if (!empty($capturesForThisPiece)) {
                    $moves[] = ['row' => $r, 'col' => $c, 'isCapture' => true, 'captured' => $capturesForThisPiece];
                } elseif (!$board->getPiece($row, $col)->canCapture($row, $col, $board)) { 
                    if ($this->isValidMove($row, $col, $r, $c, $board)) {
                        $moves[] = ['row' => $r, 'col' => $c, 'isCapture' => false];
                    }
                }
            }
        }
        return $moves;
    }

    public function getPossibleCaptures(int $row, int $col, BoardInterface $board): array
    {
        if ($this->moveStrategy === null)
            $this->setMoveStrategy(); 
        $captures = [];
        for ($r = 0; $r < 8; $r++) {
            for ($c = 0; $c < 8; $c++) {
                $captured = $this->moveStrategy->canCapture($row, $col, $r, $c, $board, $this);
                if (!empty($captured)) {
                    $captures[] = ['toRow' => $r, 'toCol' => $c, 'captured' => $captured];
                }
            }
        }
        return $captures;
    }
}