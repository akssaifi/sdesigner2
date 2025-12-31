<?php
session_start();
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (strtolower($username) === 's-designer' && $password === 'admin123') {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user'] = 'S-Designer';
        header('Location: admin.php');
        exit;
    } else {
        $error = 'Invalid username or password!';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta-name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SDesigner - Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;700&display=swap');
        
        :root {
            --primary: #8B4513;
            --secondary: #D4A76A;
            --accent: #E6B87D;
            --dark: #2C1810;
            --light: #FAF3E0;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #2C1810 0%, #8B4513 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(44, 24, 16, 0.4);
            overflow: hidden;
            width: 100%;
            max-width: 420px;
            position: relative;
        }
        
        .login-header {
            background: var(--primary);
            padding: 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .login-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent 30%, rgba(212, 167, 106, 0.3) 50%, transparent 70%);
            animation: shine 3s infinite linear;
        }
        
        @keyframes shine {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }
        
        .brand-logo {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 700;
            color: white;
            margin: 0;
            position: relative;
            z-index: 1;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .brand-subtitle {
            color: var(--accent);
            font-size: 0.9rem;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-top: 5px;
            position: relative;
            z-index: 1;
        }
        
        .login-form {
            padding: 40px;
        }
        
        .input-group {
            margin-bottom: 25px;
            position: relative;
        }
        
        .input-group input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #E0E0E0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }
        
        .input-group input:focus {
            outline: none;
            border-color: var(--secondary);
            box-shadow: 0 0 0 3px rgba(212, 167, 106, 0.2);
        }
        
        .input-label {
            display: block;
            margin-bottom: 8px;
            color: var(--dark);
            font-weight: 500;
            font-size: 0.9rem;
        }

        /* Eye toggle icon */
        .eye-toggle {
            position: absolute;
            right: 15px;
            top: 52px;
            cursor: pointer;
            font-size: 1.2rem;
            color: #555;
        }

        .login-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--dark) 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(139, 69, 19, 0.3);
        }
        
        .error-message {
            background: #FEE;
            color: #C00;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #C00;
            animation: shake 0.5s ease;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        .fashion-icon {
            text-align: center;
            margin-bottom: 30px;
            color: var(--primary);
            font-size: 3rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1 class="brand-logo">SDesigner</h1>
            <div class="brand-subtitle">Jalandhar</div>
        </div>
        
        <div class="fashion-icon">
            ✂️ 👗
        </div>
        
        <div class="login-form">
            <?php if ($error): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="input-group">
                    <label class="input-label">Username</label>
                    <input type="text" name="username" placeholder="Enter username" required autocomplete="off">
                </div>
                
                <div class="input-group">
                    <label class="input-label">Password</label>
                    <input type="password" id="passwordField" name="password" placeholder="Enter password" required autocomplete="off">
                    <span class="eye-toggle" onclick="togglePassword()">👁️</span>
                </div>
                
                <button type="submit" class="login-btn">
                    Access Admin Panel
                </button>
            </form>
        </div>
    </div>
    
    <script>
        function togglePassword() {
            let p = document.getElementById("passwordField");
            p.type = (p.type === "password") ? "text" : "password";
        }

        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            input.addEventListener('blur', function() {
                if (!this.value) {
                    this.parentElement.classList.remove('focused');
                }
            });
        });
    </script>
</body>
</html>
