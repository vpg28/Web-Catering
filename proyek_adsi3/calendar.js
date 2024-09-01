document.addEventListener("DOMContentLoaded", function() {
    renderCalendar(currentMonth, currentYear);

    document.getElementById('prev-button').addEventListener('click', prevMonth);
    document.getElementById('next-button').addEventListener('click', nextMonth);

    updateNavigationButtons();
});

function openModal() {
    document.getElementById('menuModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('menuModal').style.display = 'none';
}

let currentDate = new Date();
let currentMonth = currentDate.getMonth();
let currentYear = currentDate.getFullYear();
let currentDay = currentDate.getDate();

function renderCalendar(month, year) {
    const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    const monthYear = document.getElementById("monthYear");
    monthYear.textContent = `${monthNames[month]} ${year}`;

    const calendarBody = document.getElementById("calendar").getElementsByTagName("tbody")[0];
    calendarBody.innerHTML = "";

    fetchMenus(year, month + 1).then(menus => {
        console.log('Menus for rendering:', menus);
        let date = new Date(year, month, 1);
        let daysInMonth = new Date(year, month + 1, 0).getDate();
        let row = document.createElement("tr");

        for (let i = 0; i < date.getDay(); i++) {
            row.appendChild(document.createElement("td"));
        }

        for (let day = 1; day <= daysInMonth; day++) {
            if (row.children.length === 7) {
                calendarBody.appendChild(row);
                row = document.createElement("tr");
            }
        
            let cell = document.createElement("td");
            cell.textContent = day;

            if (menus[day]) {
                menus[day].forEach(menu => {
                    let menuName = document.createElement("div");
                    menuName.textContent = menu.menu_name;
                    cell.appendChild(menuName);
                });
            }

            let cellDate = new Date(year, month, day);
            if (cellDate < currentDate || (cellDate.getDate() === currentDate.getDate() && cellDate.getMonth() === currentDate.getMonth() && cellDate.getFullYear() === currentDate.getFullYear())) {
                cell.classList.add("expired");
                cell.addEventListener('click', function() {
                    alert("Menu sudah kadaluwarsa");
                });
            } else {
                cell.addEventListener('click', function() {
                    currentDay = day;
                    window.selectedYear = year;
                    window.selectedMonth = month;
                    window.selectedDay = day;
                    displayMenuDetails(year, month + 1, day);
                });
            }
            row.appendChild(cell);
        }
        
        if (row.children.length > 0) {
            while (row.children.length < 7) {
                row.appendChild(document.createElement("td"));
            }
            calendarBody.appendChild(row);
        }
    }).catch(error => {
        console.error('Error fetching menus:', error);
    });

    updateNavigationButtons();
}

function displayMenuDetails(year, month, day) {
    fetchMenuDetails(year, month, day)
    .then(menuData => {
        console.log('Menu data for the day:', menuData); 
        if (!menuData || Object.keys(menuData).length === 0) {
            console.error(`No menu data available for: ${day}`);
            document.querySelector('.menu-details').innerHTML = 'No menu available for this day.';
            document.querySelector('.menu-total-price').textContent = '';
            closeModal();
        } else {
            updateModalContent(day, month, year, menuData[day]);
            openModal();
        }
    })
    .catch(error => {
        console.error('Failed to fetch menu details:', error);
    });
}

function updateModalContent(day, month, year, menuData) {
    const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
    const dayNames = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];
    const date = new Date(year, month - 1, day); 
    const dayOfWeek = dayNames[date.getDay()];
    document.querySelector('.menu-date').textContent = `${dayOfWeek}, ${day} ${monthNames[month - 1]} ${year}`;

    let detailsContent = '';
    let totalPrice = 0;
    if (menuData && menuData.length > 0) {
        menuData.forEach(menu => {
            detailsContent += `${menu.menu_name}: ${menu.description}<br>`;
            totalPrice += parseFloat(menu.price);
        });
    } else {
        detailsContent = 'Tidak ada informasi menu.';
    }

    document.querySelector('.menu-details').innerHTML = detailsContent;
    document.querySelector('.menu-total-price').textContent = `Total Harga: Rp ${totalPrice}`;
}

function fetchMenus(year, month) {
    return fetch(`get_menus.php?year=${year}&month=${month}`)
        .then(response => response.json())
        .catch(error => {
            console.error('Error fetching menus:', error);
            return {};
        });
}

function fetchMenuDetails(year, month, day) {
    return fetch(`get_menus.php?year=${year}&month=${month}&day=${day}`)
        .then(response => response.json())
        .catch(error => {
            console.error('Error fetching menu details:', error);
            return [];
        });
}

function prevMonth() {
    if (currentMonth === 0) {
        currentMonth = 11;
        currentYear -= 1;
    } else {
        currentMonth -= 1;
    }

    renderCalendar(currentMonth, currentYear);
}

function nextMonth() {
    if (currentMonth === 11) {
        currentMonth = 0;
        currentYear += 1;
    } else {
        currentMonth += 1;
    }

    renderCalendar(currentMonth, currentYear);
}

function updateNavigationButtons() {
    let today = new Date();
    let nextMonth = today.getMonth() === 11 ? 0 : today.getMonth() + 1;
    let nextMonthYear = today.getMonth() === 11 ? today.getFullYear() + 1 : today.getFullYear();

    if (currentYear === today.getFullYear() && currentMonth === today.getMonth()) {
        // tampilkan bulan ini
        document.getElementById('prev-button').style.display = 'none';
        document.getElementById('next-button').style.display = 'inline-block';
    } else if (currentYear === nextMonthYear && currentMonth === nextMonth) {
        // tampilkan bulan depan
        document.getElementById('prev-button').style.display = 'inline-block';
        document.getElementById('next-button').style.display = 'none';
    }
}
document.getElementById('chatButton').addEventListener('click', openChatModal);

function openChatModal() {
    document.getElementById('chatModal').style.display = 'block';
}

function closeChatModal() {
    document.getElementById('chatModal').style.display = 'none';
}

document.getElementById('sendChatButton').addEventListener('click', sendMessage);


