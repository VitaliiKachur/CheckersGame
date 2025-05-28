<?php

class MoveValidationResult
{
    private bool $isValid;
    private ?array $captureInfo;
    private string $errorMessage;

    public function __construct(bool $isValid, ?array $captureInfo = null, string $errorMessage = '')
    {
        $this->isValid = $isValid;
        $this->captureInfo = $captureInfo;
        $this->errorMessage = $errorMessage;
    }

    public function isValid(): bool { return $this->isValid; }
    public function getCaptureInfo(): ?array { return $this->captureInfo; }
    public function getErrorMessage(): string { return $this->errorMessage; }
    public function hasCapture(): bool { return $this->captureInfo !== null; }
}