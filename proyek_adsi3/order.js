function completedOrderStatusDetail(button) {
    const orderId = button.getAttribute('data-orderid');
    const menuDate = button.getAttribute('data-menudate');

    fetch('update_item_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ orderId: orderId, menuDate: menuDate }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const buttons = document.querySelectorAll(`.selesai[data-orderid="${orderId}"][data-menudate="${menuDate}"]`);
            buttons.forEach(btn => {
                btn.textContent = 'Selesai';
                btn.disabled = true;
            });
            checkAllCompleted(orderId);
        } else {
            alert('Gagal memperbarui status item: ' + data.message);
        }
    })
    .catch(error => {
        alert('Terjadi kesalahan saat mengirim permintaan.');
    });
}

function checkAllCompleted(orderId) {
    const allButtons = document.querySelectorAll(`.selesai[data-orderid="${orderId}"]`);
    let allCompleted = true;

    allButtons.forEach(btn => {
        if (btn.textContent !== 'Selesai') {
            allCompleted = false;
        }
    });

    if (allCompleted) {
        fetch('update_order_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ orderId: orderId }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const orderStatusElement = document.getElementById('orderStatus');
                if (orderStatusElement) {
                    orderStatusElement.textContent = 'Pesanan Selesai';
                    orderStatusElement.classList.remove('yellow-bg');
                    orderStatusElement.classList.add('green-bg');
                } else {
                    alert('Elemen orderStatus tidak ditemukan.');
                }
            } else {
                alert('Gagal memperbarui status pesanan: ' + data.message);
            }
        })
        .catch(error => {
            alert('Terjadi kesalahan saat mengirim permintaan.');
        });
    }
}
