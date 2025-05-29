window.gameTimer = {
    startTime: null,
    isRunning: false,
    intervalId: null,
    gameStatus: null,

    init(startTime = null, gameStatus = null) {
        this.startTime = startTime;
        this.gameStatus = gameStatus;
        
        if (this.isGameEnded()) {
            this.isRunning = false;
            this.updateStatus('Гра завершена', 'finished');
            this.updateDisplay(); 
        } else if (this.startTime) {
            this.isRunning = true;
            this.start();
        } else {
            this.isRunning = false;
            this.updateStatus('Зупинено', 'stopped');
        }
    },

    isGameEnded() {
        if (!this.gameStatus) return false;
        
        return this.gameStatus.includes('перемогли') || 
               this.gameStatus === 'Пат' ||
               this.gameStatus.toLowerCase().includes('game over') ||
               this.gameStatus.toLowerCase().includes('переміг');
    },

    start() {
        if (this.isGameEnded()) {
            this.stop();
            return;
        }

        this.startTime = this.startTime || Date.now();
        this.isRunning = true;
        this.updateStatus('Запущено', 'running');
        this.startInterval();
    },

    stop() {
        this.isRunning = false;
        this.clearInterval();
        this.updateStatus('Зупинено', 'stopped');
    },

    reset() {
        this.stop();
        this.startTime = null;
        this.gameStatus = null;
        this.updateDisplay('00:00:00');
    },

    updateGameStatus(newGameStatus) {
        this.gameStatus = newGameStatus;
        
        if (this.isGameEnded() && this.isRunning) {
            this.isRunning = false;
            this.clearInterval();
            this.updateStatus('Гра завершена', 'finished');
            this.updateDisplay(); 
        }
    },

    startInterval() {
        this.clearInterval();
        this.intervalId = setInterval(() => {
            if (this.isGameEnded()) {
                this.isRunning = false;
                this.clearInterval();
                this.updateStatus('Гра завершена', 'finished');
                this.updateDisplay(); 
                return;
            }
            this.updateDisplay();
        }, 1000);
        this.updateDisplay();
    },

    clearInterval() {
        if (this.intervalId) {
            clearInterval(this.intervalId);
            this.intervalId = null;
        }
    },

    updateStatus(text, className) {
        const status = this.getElement('timer-status');
        if (status) {
            status.textContent = text;
            status.className = `timer-status ${className}`;
        }
    },

    updateDisplay(forceText = null) {
        const display = this.getElement('timer-display');
        if (!display) return;

        if (forceText) {
            display.textContent = forceText;
            return;
        }

        if (!this.canUpdate()) return;

        display.textContent = this.formatElapsedTime();
    },

    canUpdate() {
        return this.startTime && (this.isRunning || this.isGameEnded());
    },

    formatElapsedTime() {
        const elapsed = this.getElapsedSeconds();
        const h = this.padTime(Math.floor(elapsed / 3600));
        const m = this.padTime(Math.floor((elapsed % 3600) / 60));
        const s = this.padTime(elapsed % 60);
        
        return `${h}:${m}:${s}`;
    },

    getElapsedSeconds() {
        return Math.floor((Date.now() - this.startTime) / 1000);
    },

    padTime(num) {
        return String(num).padStart(2, '0');
    },

    getElement(id) {
        return document.getElementById(id);
    }
};