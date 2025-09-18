<?php
// **FIX**: Start the session at the very beginning of the script
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require 'db_connect.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $error = "البريد الإلكتروني وكلمة المرور مطلوبان.";
    } else {
        $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            // Verify the hashed password
            if (password_verify($password, $user['password'])) {
                // Start session and store user data
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                
                // Redirect to the dashboard
                header("Location: index.php");
                exit();
            } else {
                $error = "البريد الإلكتروني أو كلمة المرور غير صحيحة.";
            }
        } else {
            $error = "البريد الإلكتروني أو كلمة المرور غير صحيحة.";
        }
        $stmt->close();
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - Vet Nour</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
<link rel="icon" type="image/png" href="favicon.png">

    <style>
        body { 
            font-family: 'Cairo', sans-serif;
        }
        
        /* **MODIFIED:** Combined Logo Animation Effects */
        .logo-container {
            position: relative;
            width: 160px;
            height: 160px;
            margin-bottom: 1rem;
            border-radius: 50%;
            padding: 5px; /* Space for the gradient border */
            background: linear-gradient(45deg, #f97316, #3b82f6, #10b981); /* orange-500, blue-500, emerald-500 */
            animation: border-color-change 5s linear infinite;
            overflow: hidden; /* Hide the shimmer overflow */
            transition: transform 0.3s ease-out;
        }

        .logo-container:hover {
            transform: scale(1.1) translateY(-5px);
        }

        .logo-image {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 4px solid white; /* Inner border to separate logo from gradient */
        }

        /* Shimmer/Sweep effect */
        .logo-container::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -60%;
            width: 20%;
            height: 200%;
            background: linear-gradient(
                to right,
                rgba(255, 255, 255, 0) 0%,
                rgba(255, 255, 255, 0.4) 50%,
                rgba(255, 255, 255, 0) 100%
            );
            transform: rotate(25deg);
            animation: shimmer 4s infinite;
            animation-delay: 2s;
        }

        @keyframes border-color-change {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        @keyframes shimmer {
            100% {
                transform: translateX(250px) rotate(25deg);
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex items-center justify-center min-h-screen">
        <div class="relative flex flex-col m-6 space-y-8 bg-white shadow-2xl rounded-2xl md:flex-row md:space-y-0">
            <!-- Left Side -->
            <div class="flex flex-col justify-center p-8 md:p-14">
                <div class="logo-container mx-auto">
                    <img src="logo.png" alt="Vet Nour Logo" class="logo-image">
                </div>
                <span class="font-light text-gray-500 mb-8 text-center">
                    مرحباً بعودتك! يرجى إدخال بياناتك
                </span>

                 <?php if ($error): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg" role="alert">
                        <p><?php echo $error; ?></p>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="post" class="space-y-4">
                    <input type="email" name="email" placeholder="البريد الإلكتروني" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <input type="password" name="password" placeholder="كلمة المرور" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition-colors duration-300 shadow-lg hover:shadow-blue-500/50 transform hover:-translate-y-0.5">
                        تسجيل الدخول
                    </button>
                </form>
                <hr class="my-6 border-gray-300">
                <div class="text-center text-gray-500">
                    ليس لديك حساب؟
                    <a href="register.php" class="font-bold text-blue-600 hover:underline">أنشئ حساباً جديداً</a>
                </div>
            </div>
            <!-- Right Side (Branding & Image) -->
            <div class="relative hidden lg:block">
                <img src="vet.png" 
                     alt="صورة بيطرية" 
                     class="w-[400px] h-full hidden rounded-r-2xl md:block object-cover">
                <!-- Overlay -->
               <div class="absolute hidden md:block inset-0 bg-gradient-to-t from-blue-800/80 to-transparent rounded-r-2xl"></div>
                <div class="absolute hidden md:block bottom-10 right-6 p-6 text-white text-xl">
                    <p>"مرحباً بعودتك يا دكتور!"</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
