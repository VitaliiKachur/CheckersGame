document.addEventListener('DOMContentLoaded', () => {
    const board = document.getElementById('board');
    const initialSelectedRow = window.gameData?.selectedCell?.row || null;
    const initialSelectedCol = window.gameData?.selectedCell?.col || null;

    function markSelectedCell(row, col) {
        if (row === null || col === null) return;
        const cell = board.querySelector(`[data-row="${row}"][data-col="${col}"]`);
        if (cell) {
            cell.classList.add('selected');
        }
    }

    markSelectedCell(initialSelectedRow, initialSelectedCol);

    board.addEventListener('click', (event) => {
        const clickedButton = event.target.closest('.cell');
        if (!clickedButton) return;
    });
});