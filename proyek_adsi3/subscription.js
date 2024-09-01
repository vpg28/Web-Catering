document.addEventListener("DOMContentLoaded", function() {
    document.getElementById('dailyButton').addEventListener('click', function() {
        handleSubscription('daily');
    });
    document.getElementById('weeklyButton').addEventListener('click', function() {
        handleSubscription('weekly');
    });
    document.getElementById('monthlyButton').addEventListener('click', function() {
        handleSubscription('monthly');
    });
});

function handleSubscription(type) {
    console.log("Subscription type:", type);

    if (window.selectedDay === null || window.selectedMonth === null || window.selectedYear === null) {
        console.error("No date selected");
        alert("Please select a date first.");
        return;
    }

    let today = new Date();
    today.setHours(0, 0, 0, 0);
    let selectedDate = new Date(window.selectedYear, window.selectedMonth, window.selectedDay);
    selectedDate.setHours(0, 0, 0, 0);

    if (selectedDate < today) {
        alert("Selected date is in the past. Please select a valid date.");
        return;
    }

    switch(type) {
        case 'daily':
            addToCart(window.selectedYear, window.selectedMonth + 1, window.selectedDay);
            break;
        case 'weekly':
            handleWeeklySubscription(selectedDate);
            break;
        case 'monthly':
            handleMonthlySubscription(selectedDate, today);
            break;
    }
}

function addToCart(year, month, day) {
    console.log(`Adding to cart: year=${year}, month=${month}, day=${day}`);
    fetch(`add_to_cart.php?year=${year}&month=${month}&day=${day}`)
        .then(response => response.json())
        .then(data => {
            console.log("Response from server:", data);
            if (data.success) {
                window.location.href = 'cart.php';
            } else {
                console.error('Failed to add to cart:', data.message);
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

function handleWeeklySubscription(selectedDate) {
    let startOfWeek = new Date(selectedDate);
    let dayOfWeek = startOfWeek.getDay();
    let adjustedStartDate = new Date(startOfWeek.setDate(startOfWeek.getDate() - dayOfWeek + 2));
    let adjustedEndDate = new Date(adjustedStartDate);
    adjustedEndDate.setDate(adjustedStartDate.getDate() + 6);

    console.log(`Adding weekly package from ${adjustedStartDate.toISOString().split('T')[0]} to ${adjustedEndDate.toISOString().split('T')[0]}`);

    fetch(`add_weekly_to_cart.php?start_date=${adjustedStartDate.toISOString().split('T')[0]}&end_date=${adjustedEndDate.toISOString().split('T')[0]}`)
        .then(response => response.json())
        .then(data => {
            console.log("Response from server:", data);
            if (data.success) {
                window.location.href = 'cart.php';
            } else {
                console.error('Failed to add weekly package to cart:', data.message);
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

function handleMonthlySubscription(selectedDate, today) {
    let selectedYear = selectedDate.getFullYear();
    let selectedMonth = selectedDate.getMonth();

    let currentYear = today.getFullYear();
    let currentMonth = today.getMonth();
    
    if (selectedYear < currentYear || (selectedYear === currentYear && selectedMonth <= currentMonth)) {
        alert("Paket untuk bulan ini atau sebelumnya tidak bisa dipesan. Silakan pilih bulan depan.");
        return;
    }

    // mengambil tanggal awal bulan dan akhir bulan
    let startOfMonth = new Date(selectedYear, selectedMonth, 1);
    let endOfMonth = new Date(selectedYear, selectedMonth + 1, 0); // hari ke 0 bulan depan adalah tanggal terakhir bulan ini
    
    // array nama bulan
    const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    
    console.log(`Selected month: ${monthNames[selectedMonth]} ${selectedYear}`);
    console.log(`Adding monthly package from ${startOfMonth.toISOString().split('T')[0]} to ${endOfMonth.toISOString().split('T')[0]}`);

    fetch(`add_monthly_to_cart.php?start_date=${startOfMonth.toISOString().split('T')[0]}&end_date=${endOfMonth.toISOString().split('T')[0]}`)
        .then(response => response.json())
        .then(data => {
            console.log("Response from server:", data);
            if (data.success) {
                window.location.href = 'cart.php';
            } else {
                console.error('Failed to add monthly package to cart:', data.message);
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}



