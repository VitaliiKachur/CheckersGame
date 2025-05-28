<?php
require_once 'src/Pieces/Piece.php';
require_once 'src/Moves/RegularMoveStrategy.php';

class WhitePiece extends Piece
{
    public function __construct(bool $isKing = false)
    {
        parent::__construct('white', $isKing);
    }
}