<?php
class PlayerActionHandler
{
    private Board $board;
    private MessageService $messageService;
    private MoveValidatorInterface $moveValidator;

    public function __construct(Board $board, MessageService $messageService, MoveValidatorInterface $moveValidator)
    {
        $this->board = $board;
        $this->messageService = $messageService;
        $this->moveValidator = $moveValidator;
    }

    public function selectPiece(int $row, int $col, GameState $gameState): void
    {
        $piece = $this->board->getPiece($row, $col);
        if ($piece && $piece->getColor() === $gameState->getCurrentPlayer()) {
            $hasPlayerCaptures = $this->playerHasCaptures($gameState->getCurrentPlayer());
            $pieceCanCapture = $piece->canCapture($row, $col, $this->board);

            if ($hasPlayerCaptures && !$pieceCanCapture) {
                $this->messageService->showMessage('Ви повинні бити, якщо це можливо! Виберіть фігуру, яка може бити.', 'error');
                return;
            }
            
            $gameState->setSelectedCell(['row' => $row, 'col' => $col]);
            $this->messageService->showMessage('Фігуру вибрано. Зробіть хід.', 'info');
        } else {
            $this->messageService->showMessage('Виберіть свою фігуру.', 'error');
        }
    }

    public function attemptMove(int $fromRow, int $fromCol, int $toRow, int $toCol, GameState $gameState): MoveValidationResult
    {
        $piece = $this->board->getPiece($fromRow, $fromCol);
        if (!$piece) {
            $this->messageService->showMessage('Вибрана фігура не знайдена.', 'error');
            return new MoveValidationResult(false);
        }

        return $this->moveValidator->validateMove($piece, $fromRow, $fromCol, $toRow, $toCol, $this->board);
    }

    private function playerHasCaptures(string $color): bool
    {
        for ($r = 0; $r < 8; $r++) {
            for ($c = 0; $c < 8; $c++) {
                $piece = $this->board->getPiece($r, $c);
                if ($piece && $piece->getColor() === $color && $piece->canCapture($r, $c, $this->board)) {
                    return true;
                }
            }
        }
        return false;
    }
}
