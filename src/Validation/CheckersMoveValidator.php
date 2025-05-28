<?php

class CheckersMoveValidator implements MoveValidatorInterface
{
    public function validateMove(PieceInterface $piece, int $fromRow, int $fromCol, int $toRow, int $toCol, Board $board): MoveValidationResult
    {
        $possibleCaptures = $piece->getPossibleCaptures($fromRow, $fromCol, $board);
        $captureInfo = $this->findCaptureOption($possibleCaptures, $toRow, $toCol);
        
        $playerHasCaptures = $this->playerHasCaptures($piece->getColor(), $board);
        
        if ($playerHasCaptures && !$captureInfo) {
            return new MoveValidationResult(false, null, 'Ви повинні бити, якщо це можливо!');
        }
        
        if ($captureInfo) {
            return new MoveValidationResult(true, $captureInfo);
        }
        
        if (!$playerHasCaptures && $piece->isValidMove($fromRow, $fromCol, $toRow, $toCol, $board)) {
            return new MoveValidationResult(true);
        }
        
        return new MoveValidationResult(false, null, 'Недійсний хід або ви повинні бити.');
    }

    private function findCaptureOption(array $possibleCaptures, int $toRow, int $toCol): ?array
    {
        foreach ($possibleCaptures as $option) {
            if ($option['toRow'] === $toRow && $option['toCol'] === $toCol) {
                return $option;
            }
        }
        return null;
    }

    private function playerHasCaptures(string $color, Board $board): bool
    {
        for ($r = 0; $r < 8; $r++) {
            for ($c = 0; $c < 8; $c++) {
                $piece = $board->getPiece($r, $c);
                if ($piece && $piece->getColor() === $color && $piece->canCapture($r, $c, $board)) {
                    return true;
                }
            }
        }
        return false;
    }
}
