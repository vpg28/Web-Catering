// open chat page
document.getElementById('chatButton').addEventListener('click', openChatModal);

function openChatModal() {
    document.getElementById('chatModal').style.display = 'block';
}


// Fetch user, chat history
document.getElementById('open-chat').addEventListener('click', function() {
    const userId = 1; // Ganti dengan user_id yang sesuai
    fetch(`get_user.php?user_id=${userId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('user-name').textContent = data.name;
        });

    fetch(`get_chat_history.php?user_id=${userId}`)
        .then(response => response.json())
        .then(data => {
            const chatHistoryDiv = document.getElementById('chat-history');
            chatHistoryDiv.innerHTML = ''; // Bersihkan isi sebelumnya
            data.forEach(chat => {
                const chatMessage = document.createElement('div');
                chatMessage.textContent = `[${chat.time}] ${chat.isi_pesan} (${chat.status})`;
                chatHistoryDiv.appendChild(chatMessage);
            });
        });

    document.getElementById('chat-modal').style.display = 'block';
});

document.getElementById('close-chat').addEventListener('click', function() {
    document.getElementById('chat-modal').style.display = 'none';
});


// send klik
function sendMessage() {
    const username = document.getElement

    const message = document.getElementById('chatInput').value;
    if (message.trim() === '') return;

    const timestamp = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    displayMessage(document.body.dataset.username, message, timestamp, 'pending');

    document.getElementById('chatInput').value = '';

    fetch('chat.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ message: message })
    }).then(response => response.json())
    .then(data => {
        console.log(data)
        if (data.success) {
            // Simpan timestamp yang dikembalikan dari server
            const serverTimestamp = data.timestamp || timestamp;
            // Perbarui status menjadi "terkirim" setelah 2 detik
            setTimeout(() => {
                updateMessageStatus(message, 'terkirim', serverTimestamp);
            }, 2000);
            // Perbarui status menjadi "dibaca" setelah 5 detik
            setTimeout(() => {
                updateMessageStatus(message, 'dibaca', serverTimestamp);
            }, 5000);
            // Display reply setelah 5 detik
            setTimeout(() => {
                displayMessage('Katering Mamayin', 'Halo, ada yang bisa dibantu?', timestamp, '');
            }, 7000);
        } else {
            console.error('Error sending message:', data.error);
        }
    }).catch(error => console.error('Fetch error:', error));
}

// display message
function displayMessage(username, message, timestamp,status) {
    const chatBody = document.getElementById('chatBody');
    const messageElement = document.createElement('div');
    messageElement.classList.add('chat-message');
    messageElement.innerHTML = `
    <div class="message-header">
        <div class="sender-timestamp">
            <strong>${username}</strong>
            <span class="message-content">${message}</span>
            <span class="timestamp">${timestamp}</span>
            <span class="status">${status}</span>
        </div>
        <br>
    </div>
    `;
    chatBody.appendChild(messageElement);
    chatBody.scrollTop = chatBody.scrollHeight;
}

// update status
function updateMessageStatus(message, status, timestamp) {
    const messages = document.getElementsByClassName('chat-message');
    for (let i = 0; i < messages.length; i++) {
        const messageContent = messages[i].getElementsByClassName('message-content')[0].innerText;
        if (messageContent === message) {
            const statusElement = messages[i].getElementsByClassName('status')[0];
            statusElement.innerText = status;
            const timestampElement = messages[i].getElementsByClassName('timestamp')[0];
            if (timestampElement) {
                timestampElement.innerText = timestamp;
            }
            break;
        }
    }
}

// close klik
document.getElementById('close-chat').addEventListener('click', function() {
    document.getElementById('chat-modal').style.display = 'none';
});

function closeChatModal() {
    document.getElementById('chatModal').style.display = 'none';
}