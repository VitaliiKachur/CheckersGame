<?php
class CaptureMoveCommand implements MoveCommandInterface
{
    private Board $board;
    private int $fromRow, $fromCol, $toRow, $toCol;
    private array $capturedPieces;
    private MessageService $messageService;

    public function __construct(Board $board, int $fromRow, int $fromCol, int $toRow, int $toCol, array $capturedPieces, MessageService $messageService)
    {
        $this->board = $board;
        $this->fromRow = $fromRow;
        $this->fromCol = $fromCol;
        $this->toRow = $toRow;
        $this->toCol = $toCol;
        $this->capturedPieces = $capturedPieces;
        $this->messageService = $messageService;
    }

    public function execute(): void
    {
        foreach ($this->capturedPieces as $captured) {
            $this->board->removePiece($captured['row'], $captured['col']);
        }
        
        $this->board->movePiece($this->fromRow, $this->fromCol, $this->toRow, $this->toCol);
        $this->messageService->showMessage('Взято!', 'success');
    }
}
