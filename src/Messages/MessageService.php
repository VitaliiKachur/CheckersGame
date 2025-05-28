<?php

class MessageService
{
    private array $observers = [];
    private string $currentMessage = '';
    private string $currentMessageType = 'info';

    public function addObserver(MessageObserver $observer): void
    {
        $this->observers[] = $observer;
    }

    public function showMessage(string $text, string $type = 'info'): void
    {
        $this->currentMessage = $text;
        $this->currentMessageType = $type;
        
        foreach ($this->observers as $observer) {
            $observer->onMessage($text, $type);
        }
    }

    public function getMessage(): string { return $this->currentMessage; }
    public function getMessageType(): string { return $this->currentMessageType; }
}
