<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="auth-form-container">
                <h2>Login</h2>
                <form id="loginForm" action="../../controllers/auth/login.php" method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">E-Mail Adresse</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="z. B. max@example.com" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Passwort</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="********" required>
                    </div>
                    <div id="message" class="alert alert-danger d-none" role="alert"></div>
                    <div class="mb-3">
                        <button type="submit" class="btn btn-orange w-100">Login</button>
                    </div>
                    <div class="text-center mt-2">
                        <p class="mb-0">Noch keinen Account? <a href="?page=register">Registrieren</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>



