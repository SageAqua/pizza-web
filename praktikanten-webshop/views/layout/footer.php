</main>
<footer>
    <p>&copy; <?= date("Y") ?> Azubi Webshop – erstellt von Eris & Gulzhan</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<div id="cart-toast" class="cart-toast d-none">
    <div class="toast-inner">
        <i class="fa-solid fa-check-circle me-2"></i>
        <span id="toast-product-name"></span>
    </div>
</div>
<script>

    document.querySelectorAll('.add-to-cart-form').forEach(form => {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            const formData = new FormData(form);

            const response = await fetch('../../controllers/cart/add.php', {
                method: 'POST',
                body: formData
            });


            const result = await response.json();

            if (result.success) {
                const toast = document.getElementById('cart-toast');
                const toastName = document.getElementById('toast-product-name');
                toastName.textContent = `${result.quantity} × ${form.dataset.name}`;
                toast.classList.remove('d-none');
                toast.classList.add('show');

                setTimeout(() => {
                    toast.classList.remove('show');
                    toast.classList.add('d-none');
                }, 2500);

                const badge = document.querySelector('#cartLink .badge');
                if (badge) {
                    let newTotal = 0;
                    for (const id in result.cart) {
                        newTotal += parseInt(result.cart[id].quantity);
                    }
                    badge.textContent = newTotal;
                    badge.style.display = newTotal > 0 ? 'inline-block' : 'none';
                }
            }
        });
    });
</script>
<script>
    document.getElementById('loginForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const form = e.target;
        const formData = new FormData(form);
        const response = await fetch('../../controllers/auth/login.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        const messageDiv = document.getElementById('message');

        if (result.success) {
            window.location.href = "../../public/" + result.redirect; // <--- Dynamisch!
        } else {
            messageDiv.classList.remove('d-none');
            messageDiv.textContent = result.message;
        }
    });
</script>
</body>
</html>
