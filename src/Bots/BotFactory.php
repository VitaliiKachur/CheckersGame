<?php
require_once 'src/Interfaces/BotInterface.php';
require_once 'src/Bots/SimpleBot.php';

class BotFactory
{
    public static function createBot(string $type): BotInterface
    {
        switch ($type) {
            case 'simple':
                return new SimpleBot();
            default:
                throw new InvalidArgumentException("Невідомий тип бота: $type");
        }
    }
}