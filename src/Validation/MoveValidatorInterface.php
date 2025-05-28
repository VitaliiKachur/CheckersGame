
<?php
interface MoveValidatorInterface
{
    public function validateMove(PieceInterface $piece, int $fromRow, int $fromCol, int $toRow, int $toCol, Board $board): MoveValidationResult;
}