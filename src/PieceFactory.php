<?php
require_once 'src/Interfaces/PieceInterface.php';
require_once 'src/Pieces/WhitePiece.php';
require_once 'src/Pieces/BlackPiece.php';
require_once 'src/Moves/RegularMoveStrategy.php'; 

class PieceFactory
{
    public static function createPiece(string $color, bool $isKing = false): Piece
    {
        if ($color === 'white') {
            return new WhitePiece($isKing);
        } elseif ($color === 'black') {
            return new BlackPiece($isKing);
        }
        throw new InvalidArgumentException("Недійсний колір фігури: $color");
    }
}