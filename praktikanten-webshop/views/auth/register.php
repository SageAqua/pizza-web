<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="auth-form-container mt-5">
                <h2>Registrierung</h2>
                <form id="registerForm" action="../../controllers/auth/register.php" method="POST">
                    <div class="mb-3">
                        <input type="text" class="form-control" id="firstname" name="firstname" placeholder="Vorname" required>
                    </div>
                    <div class="mb-3">
                        <input type="text" class="form-control" id="lastname" name="lastname" placeholder="Nachname" required>
                    </div>
                    <div class="mb-3">
                        <input type="email" class="form-control" id="email" name="email" placeholder="E-Mail Adresse" required>
                    </div>
                    <div class="mb-3">
                        <input type="text" class="form-control" id="address" name="address" placeholder="Adresse" required>
                    </div>
                    <div class="mb-3">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Passwort" required>
                    </div>
                    <div class="mb-3">
                        <input type="password" class="form-control" id="password_confirm" name="password_confirm" placeholder="Passwort bestätigen" required>
                    </div>
                    <button type="submit" class="btn btn-orange w-100">Registrieren</button>
                    <p class="text-center mt-3">Bereits ein Konto? <a href="../../public/index.php?page=login">Einloggen</a></p>
                    <div id="message" class="alert alert-danger d-none mt-3" role="alert"></div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('registerForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);

        const response = await fetch('../../controllers/auth/register.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        const messageDiv = document.getElementById('message');

        if (result.success) {
            alert("Registrierung erfolgreich!");
            window.location.href = "../../public/index.php?page=login&success=1";
        } else {
            messageDiv.classList.remove('d-none');
            messageDiv.classList.add('alert-danger');
            messageDiv.innerText = result.message;
        }
    });
</script>
