<?php

class GameTimer
{
    private const TIMER_TEMPLATE = '
        <div class="game-timer-widget">
            <div class="timer-header">
                <h3>🕐 Час гри</h3>
            </div>
            <div class="timer-display" id="timer-display">
                00:00:00
            </div>
            <div class="timer-status stopped" id="timer-status">
                Зупинено
            </div>
        </div>';

    public function renderTimer(): string
    {
        return self::TIMER_TEMPLATE;
    }

    public function getTimerScript(?int $startTime = null, ?string $gameStatus = null): string
    {
        $startTimeJS = $this->prepareStartTime($startTime);
        $gameStatusJS = json_encode($gameStatus);
        
        return sprintf(
            '<script src="js/game-timer.js"></script>
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    window.gameTimer.init(%s, %s);
                });
            </script>',
            $startTimeJS,
            $gameStatusJS
        );
    }

    private function prepareStartTime(?int $startTime): string
    {
        return is_numeric($startTime) ? ($startTime * 1000) : 'null';
    }
}
