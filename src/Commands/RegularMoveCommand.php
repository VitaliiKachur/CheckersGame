<?php

class RegularMoveCommand implements MoveCommandInterface
{
    private Board $board;
    private int $fromRow, $fromCol, $toRow, $toCol;
    private MessageService $messageService;

    public function __construct(Board $board, int $fromRow, int $fromCol, int $toRow, int $toCol, MessageService $messageService)
    {
        $this->board = $board;
        $this->fromRow = $fromRow;
        $this->fromCol = $fromCol;
        $this->toRow = $toRow;
        $this->toCol = $toCol;
        $this->messageService = $messageService;
    }

    public function execute(): void
    {
        $this->board->movePiece($this->fromRow, $this->fromCol, $this->toRow, $this->toCol);
        $this->messageService->showMessage('Хід зроблено.', 'info');
    }
}