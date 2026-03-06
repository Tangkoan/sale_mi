<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Shop Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Nokora:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                        khmer: ['Nokora', 'sans-serif'],
                    },
                    colors: {
                        primary: 'rgb(var(--color-primary) / <alpha-value>)',
                        secondary: 'rgb(var(--color-secondary) / <alpha-value>)',
                        'page-bg': 'rgb(var(--page-bg) / <alpha-value>)',
                        'card-bg': 'rgb(var(--card-bg) / <alpha-value>)',
                        'input-bg': 'rgb(var(--input-bg) / <alpha-value>)',
                        'input-border': 'rgb(var(--input-border) / <alpha-value>)',
                    },
                    // បន្ថែម utility ថ្មីសម្រាប់ Safe Area
                    spacing: {
                        'safe-bottom': 'env(safe-area-inset-bottom)',
                    }
                }
            }
        }
    </script>
    <style>
        :root {
            --color-primary: 37 99 235;
            --color-secondary: 79 70 229;
            --page-bg: 243 244 246;
            --card-bg: 255 255 255;
            --input-bg: 249 250 251;
            --input-border: 229 231 235;
        }

        body { font-family: 'Plus Jakarta Sans', 'Nokora', sans-serif; }
        
        .captcha-box {
            background-image: radial-gradient(rgb(var(--input-border)) 1px, transparent 1px);
            background-size: 10px 10px;
            font-family: 'Courier New', monospace;
            text-decoration: line-through;
            opacity: 0.9;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-up {
            animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
        .animate-blob {
            animation: blob 7s infinite;
        }
        .animation-delay-2000 {
            animation-delay: 2s;
        }
    </style>
</head>

<body class="bg-page-bg min-h-[100dvh] w-full flex flex-col justify-center items-center px-4 py-6 pb-[calc(30px+env(safe-area-inset-bottom))] relative overflow-x-hidden overflow-y-auto">

    <div class="absolute top-0 left-0 w-72 h-72 bg-primary rounded-full mix-blend-multiply filter blur-2xl opacity-30 animate-blob"></div>
    <div class="absolute bottom-0 right-0 w-72 h-72 bg-secondary rounded-full mix-blend-multiply filter blur-2xl opacity-30 animate-blob animation-delay-2000"></div>

    <div class="w-full max-w-sm bg-card-bg/90 backdrop-blur-xl rounded-[2rem] shadow-2xl border border-input-border/50 p-8 relative z-10 animate-fade-up my-auto">
        
        <div class="text-center mb-6">
            <div class="flex justify-center mb-4">
                @if(isset($shop) && $shop->logo)
                    <div class="p-1.5 rounded-2xl bg-card-bg shadow-lg shadow-primary/10">
                        <img src="{{ asset('storage/' . $shop->logo) }}" 
                             class="w-16 h-16 rounded-xl object-cover" 
                             alt="Shop Logo">
                    </div>
                @else
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-white shadow-lg shadow-primary/30">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                @endif
            </div>
            <h2 class="text-xl font-bold text-gray-900 tracking-tight">Welcome Back!</h2>
        </div>

        <form id="loginForm" action="{{ route('login.submit') }}" method="POST" class="space-y-4">
            @csrf
            
            <div class="group">
                <label class="block text-gray-500 text-[11px] font-bold mb-1 ml-3 uppercase tracking-wider">Username</label>
                <div class="relative transition-all duration-300 transform group-focus-within:-translate-y-0.5">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-primary transition-colors" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <input type="text" name="username" id="username"
                        class="w-full pl-11 pr-4 py-3 bg-input-bg border border-input-border rounded-xl focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 focus:bg-card-bg transition-all text-gray-800 font-medium placeholder-gray-400" 
                        placeholder="Enter username">
                </div>
                <p id="error-username" class="text-red-500 text-xs mt-1 ml-2 hidden font-medium flex items-center animate-pulse">
                    <span>Error message</span>
                </p>
            </div>

            <div class="group">
                <label class="block text-gray-500 text-[11px] font-bold mb-1 ml-3 uppercase tracking-wider">Password</label>
                <div class="relative transition-all duration-300 transform group-focus-within:-translate-y-0.5">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400 group-focus-within:text-primary transition-colors" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <input type="password" name="password" id="passwordInput"
                        class="w-full pl-11 pr-12 py-3 bg-input-bg border border-input-border rounded-xl focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 focus:bg-card-bg transition-all text-gray-800 font-medium placeholder-gray-400" 
                        placeholder="••••••••">
                    
                    <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 flex items-center px-4 text-gray-400 hover:text-primary transition-colors cursor-pointer">
                        <svg id="eyeOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg id="eyeClosed" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                    </button>
                </div>
                <p id="error-password" class="text-red-500 text-xs mt-1 ml-2 hidden font-medium flex items-center animate-pulse">
                    <span>Error message</span>
                </p>
            </div>

            <div class="pt-1">
                <div class="flex justify-between items-end mb-2">
                    <label class="block text-gray-500 text-[11px] font-bold ml-3 uppercase tracking-wider">Security Code</label>
                    <button type="button" onclick="window.location.reload()" class="text-primary text-xs font-semibold hover:text-secondary flex items-center px-2 py-1 rounded-lg hover:bg-primary/10 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Change Code
                    </button>
                </div>
                
                <div class="flex gap-3">
                    <div class="captcha-box w-1/3 bg-input-bg rounded-xl flex items-center justify-center text-xl font-bold text-gray-600 select-none border border-input-border shadow-inner">
                        {{ $captchaCode }}
                    </div>
                    <input type="text" name="captcha" id="captcha"
                        class="w-2/3 px-4 py-3 bg-input-bg border border-input-border rounded-xl focus:outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 focus:bg-card-bg transition-all text-center tracking-[0.3em] font-bold text-gray-800 placeholder-gray-300" 
                        placeholder="XXXX">
                </div>
                <p id="error-captcha" class="text-red-500 text-xs mt-1 ml-2 hidden font-medium flex items-center animate-pulse">
                    <span>Error message</span>
                </p>
            </div>

            <button type="submit" id="btnSubmit" class="w-full bg-gradient-to-r from-primary to-secondary hover:opacity-90 text-white font-bold py-3 rounded-xl shadow-lg shadow-primary/30 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all duration-300 transform active:scale-[0.98] mt-4 text-base tracking-wide">
                Sign In
            </button>
        </form>
        
        <div class="mt-8 text-center">
            <p class="text-gray-400 text-[10px] font-semibold tracking-widest opacity-60">SECURE SHOP SYSTEM V1.0</p>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('passwordInput');
            const eyeOpen = document.getElementById('eyeOpen');
            const eyeClosed = document.getElementById('eyeClosed');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeOpen.classList.add('hidden');
                eyeClosed.classList.remove('hidden');
            } else {
                passwordInput.type = 'password';
                eyeOpen.classList.remove('hidden');
                eyeClosed.classList.add('hidden');
            }
        }

        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault(); 
            let formData = new FormData(this);
            let btnSubmit = document.getElementById('btnSubmit');
            
            btnSubmit.innerHTML = '<div class="flex items-center justify-center"><svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Verifying...</div>';
            btnSubmit.disabled = true;
            btnSubmit.classList.add('opacity-80', 'cursor-not-allowed');

            document.querySelectorAll('[id^="error-"]').forEach(el => {
                el.classList.add('hidden');
                let span = el.querySelector('span');
                if(span) span.innerText = ''; else el.innerText = '';
            });

            fetch("{{ route('login.submit') }}", {
                method: "POST",
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    window.location.href = data.redirect_url;
                } else {
                    resetButton();
                    if (data.errors) {
                        if (data.errors.username) showError('error-username', data.errors.username[0]);
                        if (data.errors.password) showError('error-password', data.errors.password[0]);
                        if (data.errors.captcha) showError('error-captcha', data.errors.captcha[0]);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                resetButton();
                alert("Connection error! Please try again.");
            });

            function resetButton() {
                btnSubmit.innerHTML = 'Sign In';
                btnSubmit.disabled = false;
                btnSubmit.classList.remove('opacity-80', 'cursor-not-allowed');
            }
        });

        function showError(elementId, message) {
            let el = document.getElementById(elementId);
            let span = el.querySelector('span');
            if(span) span.innerText = message; else el.innerText = message;
            el.classList.remove('hidden');
        }
    </script>
</body>
</html>