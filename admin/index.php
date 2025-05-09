<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Full-page background with image */
        body {
            background: url('../images/tastyph.png') no-repeat center center fixed;
            background-size: cover;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, sans-serif;
            position: relative;
        }

        /* Optional: Dark overlay for better contrast */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            /* Dark overlay */
            z-index: 0;
        }


        .login-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            padding: 50px;
            /* Increased padding for a larger card */
            width: 500px;
            /* Larger card width */
            z-index: 1;
            /* Ensure the card appears above the overlay */
            position: relative;
        }

        .login-card h2 {
            font-weight: bold;
            color: #6a11cb;
        }

        .btn-primary {
            background-color: #6a11cb;
            border-color: #6a11cb;
        }

        .btn-primary:hover {
            background-color: violet;
            border-color: #2575fc;
        }
    </style>
</head>

<body>
    <div class="login-card">
        <h2 class="text-center mb-4">Admin Login</h2>
        <form action="admin_login.php" method="post">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>