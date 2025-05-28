<?php
require_once 'src/Pieces/Piece.php';
require_once 'src/Moves/RegularMoveStrategy.php';

class BlackPiece extends Piece
{
    public function __construct()
    {
        parent::__construct('black');
        $this->setMoveStrategy(new RegularMoveStrategy());
    }

    public function promote(): void
    {
        parent::promote();
        $this->setMoveStrategy(new KingMoveStrategy()); // Буде створено KingMoveStrategy
    }
}