<?php
interface BotInterface
{
    public function makeMove(BoardInterface $board, string $botColor): array;
}