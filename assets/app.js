
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';


// Message flash
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        document.querySelectorAll('.flash-message').forEach(function(element) {
            element.style.display = 'none';
        });
    }, 3000);
});


// Pagination
function paginateTable() {
    const rowsPerPage = 8; 
    const tbody = document.querySelector('#userTable tbody');
    const rows = Array.from(tbody.rows);
    const totalRows = rows.length;
    const totalPages = Math.ceil(totalRows / rowsPerPage);
    const paginationContainer = document.createElement('div');
    paginationContainer.className = 'pagination-container flex justify-center mt-4';

    for (let i = 0; i < totalPages; i++) {
        const pageNumber = i + 1;
        const button = document.createElement('button');
        button.textContent = pageNumber;
        button.className = 'px-4 rounded-full py-2 hover:bg-custom-1 scale-75 hover:text-custom-4'; 
        button.addEventListener('click', () => {
            goToPage(pageNumber);
        });
        paginationContainer.appendChild(button);
    }

    function goToPage(page) {
        const start = (page - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        rows.forEach((row, index) => {
            row.style.display = (index >= start && index < end) ? '' : 'none';
        });
        updatePaginationButtons(page);
    }
    function updatePaginationButtons(activePage) {
        const buttons = paginationContainer.querySelectorAll('button');
        buttons.forEach(button => {
            button.classList.toggle('bg-custom-2', button.textContent == activePage);
            button.classList.toggle('text-custom-4', button.textContent == activePage);
        });
    }
    const existingPaginationContainer = document.querySelector('.pagination-container');
    if (existingPaginationContainer) {
        existingPaginationContainer.remove();
    }
    document.querySelector('.table-container').appendChild(paginationContainer);
    goToPage(1);
}

document.addEventListener('DOMContentLoaded', () => {
    paginateTable();
});

// PopUp
document.getElementById('delete-button').addEventListener('click', function () {
    document.getElementById('confirmationModal').classList.remove('hidden');
});

document.getElementById('cancel-button').addEventListener('click', function () {
    document.getElementById('confirmationModal').classList.add('hidden');
});