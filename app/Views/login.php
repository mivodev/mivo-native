<?php
$title = 'MIVO Login';
include ROOT.'/app/Views/layouts/header_public.php';
?>

    <!-- Login Container -->
    <main class="flex-grow flex items-center justify-center flex-col w-full">
    <div class="w-full max-w-full sm:max-w-md z-10 p-4 sm:p-6 animate-fade-in-up">
        
        <div class="text-center mb-6 sm:mb-10">
            <!-- Brand / Logo Area -->
            <div class="flex justify-center mb-6 sm:mb-8">
                <div class="relative group">
                    <!-- <div class="absolute -inset-1 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg blur opacity-25 group-hover:opacity-50 transition duration-1000 group-hover:duration-200"></div> --> <!--SAYA TIDAK SUKA INI -->
                     <img src="/assets/img/logo-m.svg" alt="MIVO Logo" class="relative h-10 sm:h-12 w-auto block dark:hidden transform transition-transform duration-300 group-hover:scale-105">
                     <img src="/assets/img/logo-m-dark.svg" alt="MIVO Logo" class="relative h-10 sm:h-12 w-auto hidden dark:block transform transition-transform duration-300 group-hover:scale-105">
                </div>
            </div>
            
            <p class="text-accents-5 text-sm mb-6 sm:mb-10" data-i18n="login.welcome">Welcome back, please sign in to continue.</p>
        </div>

        <div class="card p-6 sm:p-8 relative overflow-hidden">
            <form action="/login" method="POST" class="space-y-4 relative z-10">
                <div class="space-y-2">
                    <label class="text-xs font-bold text-accents-5 uppercase tracking-wider ml-1" data-i18n="login.username">Username</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                            <i data-lucide="user" class="h-4 w-4 text-accents-6"></i>
                        </div>
                        <input type="text" name="username" class="form-input pl-10" placeholder="mivo" required autocomplete="username" data-i18n-placeholder="login.username">
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-accents-5 uppercase tracking-wider ml-1" data-i18n="login.password">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                            <i data-lucide="key" class="h-4 w-4 text-accents-6"></i>
                        </div>
                        <input type="password" name="password" id="password" class="form-input pl-10 pr-10" placeholder="••••••••" required autocomplete="current-password" data-i18n-placeholder="login.password">
                        <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-3 flex items-center text-accents-5 hover:text-foreground focus:outline-none cursor-pointer z-10">
                            <i id="eye-icon" data-lucide="eye" class="h-4 w-4"></i>
                            <i id="eye-off-icon" data-lucide="eye-off" class="h-4 w-4 hidden"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-full h-10 shadow-lg hover:shadow-primary/20" data-i18n="login.sign_in">
                    Sign In
                </button>
            </form>
        </div>
        
    </div>
    </main>

    <?php include ROOT.'/app/Views/layouts/footer_public.php'; ?>



    <script>


        // Toggle Password Logic
        function togglePassword() {
            const pwd = document.getElementById('password');
            const eye = document.getElementById('eye-icon');
            const eyeOff = document.getElementById('eye-off-icon');
            
            if (pwd.type === 'password') {
                pwd.type = 'text';
                eye.classList.add('hidden');
                eyeOff.classList.remove('hidden');
            } else {
                pwd.type = 'password';
                eye.classList.remove('hidden');
                eyeOff.classList.add('hidden');
            }
        }
    </script>

