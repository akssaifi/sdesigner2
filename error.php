<?php
// error.php
$code = $_GET['code'] ?? '500';
$messages = [
    '400' => 'Bad Request',
    '401' => 'Unauthorized',
    '403' => 'Forbidden',
    '404' => 'Page Not Found',
    '500' => 'Internal Server Error'
];
$message = $messages[$code] ?? 'Unknown Error';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error <?php echo $code; ?> - SDesigner Boutique</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        .error-container {
            text-align: center;
            padding: 3rem;
            max-width: 500px;
        }
        .error-code {
            font-size: 6rem;
            font-weight: bold;
            color: #8B4513;
            margin-bottom: 1rem;
        }
        .error-message {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 2rem;
        }
        .home-link {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: #8B4513;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code"><?php echo $code; ?></div>
        <div class="error-message"><?php echo htmlspecialchars($message); ?></div>
        <p>Sorry, something went wrong. Please try again later.</p>
        <a href="index.php" class="home-link">Return to Homepage</a>
    </div>
</body>
</html>