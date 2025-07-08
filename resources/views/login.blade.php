<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Puskesmas Sehat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #e9f5ec;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            margin-top: 5%;
            max-width: 400px;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0px 0px 10px rgba(0, 100, 0, 0.2);
        }
        .login-header {
            text-align: center;
            margin-bottom: 25px;
        }
        .login-header img {
            width: 80px;
        }
        .login-header h4 {
            margin-top: 10px;
            color: #004804;
        }
        .btn-login {
            background-color: #004503;
            color: white;
        }
        .btn-login:hover {
            background-color: #004804;
        }
    </style>
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="login-container">
            <div class="login-header">
                <img src="{{ asset('images/Logo_Puskesmas.jpg') }}" alt="Logo Puskesmas">
                <h4>Login Puskesmas</h4>
            </div>

            <form method="POST" action="">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label">Email / Username</label>
                    <input type="text" class="form-control" id="email" name="email" required autofocus>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Kata Sandi</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-login">Masuk</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
