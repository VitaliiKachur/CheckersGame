<?php
require_once 'src/Pieces/Piece.php';
require_once 'src/Moves/RegularMoveStrategy.php';

class WhitePiece extends Piece
{
    public function __construct()
    {
        parent::__construct('white');
        $this->setMoveStrategy(new RegularMoveStrategy());
    }

    public function promote(): void
    {
        parent::promote();
        $this->setMoveStrategy(new KingMoveStrategy()); // Буде створено KingMoveStrategy
    }
}