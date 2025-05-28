<?php
require_once 'src/Interfaces/PieceInterface.php';
require_once 'src/Pieces/WhitePiece.php';
require_once 'src/Pieces/BlackPiece.php';
require_once 'src/Moves/RegularMoveStrategy.php'; // Буде створено пізніше

class PieceFactory
{
    public static function createPiece(string $color, bool $isKing = false): PieceInterface
    {
        switch ($color) {
            case 'white':
                $piece = new WhitePiece();
                break;
            case 'black':
                $piece = new BlackPiece();
                break;
            default:
                throw new InvalidArgumentException("Невідомий колір шашки: $color");
        }
        if ($isKing) {
            $piece->promote();
        }
        $piece->setMoveStrategy(new RegularMoveStrategy()); 
        return $piece;
    }
}