<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance | Learning Alliance</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes float {
            0%   { transform: translateY(0px); }
            50%  { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }
        .float {
            animation: float 3s ease-in-out infinite;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50%      { opacity: 0.4; }
        }
        .blink {
            animation: blink 1.5s infinite;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-600 via-indigo-700 to-purple-800 min-h-screen flex items-center justify-center">
    <div class="text-center px-6">
        <!-- Logo with floating animation -->
        <div class="flex justify-center mb-6">
            <img src="{{ asset('index') }}/assets/img/logo-right.png"
                 alt="Learning Alliance Logo"
                 class="w-28 h-28 rounded-full shadow-xl bg-white p-2 float">
        </div>

        <!-- Heading -->
        <h1 class="text-4xl md:text-5xl font-extrabold text-white mb-4">
            🚧 We’ll Be Back Soon
        </h1>

        <!-- Message with blinking effect -->
        <p class="text-lg md:text-xl text-gray-100 mb-6 blink">
            The <span class="font-semibold">Learning Alliance School</span> website is under maintenance.<br>
            Please check back shortly.
        </p>

        <!-- Reload button -->
        <button onclick="location.reload()"
                class="bg-white text-indigo-700 font-semibold px-8 py-3 rounded-full shadow-lg hover:scale-105 hover:bg-gray-200 transition-transform duration-300">
            🔄 Try Again
        </button>

        <!-- Footer -->
        <div class="mt-10 text-gray-300 text-sm">
            © {{ date('Y') }} Learning Alliance. All rights reserved.
        </div>
    </div>
</body>
</html>
