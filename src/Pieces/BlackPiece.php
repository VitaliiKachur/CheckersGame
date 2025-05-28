<?php
require_once 'src/Pieces/Piece.php';
require_once 'src/Moves/RegularMoveStrategy.php';

class BlackPiece extends Piece
{
    public function __construct(bool $isKing = false)
    {
        parent::__construct('black', $isKing);
    }
}