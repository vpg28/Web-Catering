document.addEventListener("DOMContentLoaded", function() {
    document.getElementById('buyNowButton').addEventListener('click', function() {
        openSubscriptionModal();
    });
});

function openSubscriptionModal() {
    document.getElementById("subscriptionModal").style.display = "block"; // Ubah dari "none" menjadi "block"
    document.getElementById("menuModal").style.display = "none";
}

function closeSubscriptionModal() {
    document.getElementById("subscriptionModal").style.display = "none"; // Tetap gunakan "none" untuk menutup modal
}
