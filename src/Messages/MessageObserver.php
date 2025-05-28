<?php

interface MessageObserver
{
    public function onMessage(string $message, string $type): void;
}
