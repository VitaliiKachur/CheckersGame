<?php
class GameTimerManager
{
    private static ?GameTimer $timer = null;

    public static function getTimer(): GameTimer
    {
        return self::$timer ??= new GameTimer();
    }
}

?>