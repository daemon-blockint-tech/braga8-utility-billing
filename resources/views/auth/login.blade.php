<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Braga 8</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #141315; 
        }

        .auth-container {
            display: flex;
            width: 100%;
            max-width: 1100px;
            height: 700px;
            gap: 24px;
            padding: 20px;
            box-sizing: border-box;
        }

        .auth-left {
            flex: 1;
            background: linear-gradient(145deg, #E05A00 0%, #8C2A00 100%);
            border-radius: 40px;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }

        .auth-right {
            flex: 1.8;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .visual-top {
            flex: 1.6;
            background-color: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 40px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            position: relative;
            overflow: hidden;
        }

        .visual-top img {
            width: 100%;
            height: auto;
            object-fit: contain;
            margin-right: -15%;
            transform: rotate(-5deg);
        }

        .visual-bottom {
            flex: 1;
            background-color: #1a1a1c;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 40px;
            position: relative;
            overflow: hidden;
        }

        .visual-bottom img {
            width: 100%;
            height: 100%;
            object-fit: cover; 
            opacity: 0.4;
        }

        .auth-left .text-field-input {
            background: #E5D0C9;
            border: none;
            color: #131316;
            margin-top: 6px;
            width: 100%;
            padding: 12px 14px;
            border-radius: 14px;
        }

        .auth-left .text-field-label {
            color: white;
            font-size: 13px;
            display: block;
            margin-top: 15px;
        }

        .remember-wrapper {
            display: flex;
            align-items: center;
            cursor: pointer;
            user-select: none;
            color: white;
            font-size: 13px;
            gap: 10px;
            transition: 0.3s;
        }

        .remember-wrapper input {
            display: none;
        }

        .checkmark {
            width: 18px;
            height: 18px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 6px;
            position: relative;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .remember-wrapper input:checked + .checkmark {
            background: #E05A00;
            border-color: #FF7A21;
            box-shadow: 0 0 10px rgba(224, 90, 0, 0.5);
        }

        .checkmark:after {
            content: "";
            position: absolute;
            display: none;
            left: 6px;
            top: 2px;
            width: 5px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        .remember-wrapper input:checked + .checkmark:after {
            display: block;
        }

        @media (max-width: 900px) {
            .auth-right { display: none; }
        }
    </style>
</head>
<body>

    <div class="auth-container">
        <div class="auth-left">
            <div style="margin-bottom: 20px;">
                <img src="{{ asset('logo.svg') }}" alt="Braga 8" style="height: 80px;">
            </div>

            <h1 style="font-size: 34px; margin-bottom: 10px; color: white; font-weight: 500;">Selamat Datang</h1>
            
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="text-field">
                    <label class="text-field-label">Alamat Email</label>
                    <input type="email" name="email" class="text-field-input" placeholder="masukkan email" :value="old('email')" required autofocus>
                    <x-input-error :messages="$errors->get('email')" class="mt-2 text-white text-xs" />
                </div>

                <div class="text-field">
                    <label class="text-field-label">Kata Sandi</label>
                    
                    <div class="password-wrapper relative flex items-center w-full">
                        <input type="password" name="password" class="text-field-input password-input w-full" placeholder="••••••••" required>
                        
                        <button type="button" class="toggle-password absolute right-4 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600 flex items-center justify-center">
                            <i class="fa-solid fa-eye text-sm"></i>
                        </button>
                    </div>
                    
                    <x-input-error :messages="$errors->get('password')" class="mt-2 text-white text-xs" />
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
                    <label class="remember-wrapper">
                        <input type="checkbox" name="remember">
                        <span class="checkmark"></span> Ingat saya
                    </label>
                    
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" style="color: white; font-size: 13px; opacity: 0.7; text-decoration: underline;">
                            Lupa Kata Sandi?
                        </a>
                    @endif
                </div>

                <button type="submit" class="btn-braga-glass" style="margin-top: 25px;">
                    Masuk
                </button>

            </form>
        </div>

        <div class="auth-right">
            <div class="visual-top">
                <img src="{{ asset('mockup-img.png') }}" alt="Mockup">
            </div>
            
            <div class="visual-bottom">
                <img src="{{ asset('texture-2.png') }}" alt="Texture">
            </div>
        </div>
    </div>

    @stack('scripts')
</body>
</html>