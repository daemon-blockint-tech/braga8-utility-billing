<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Braga 8</title>
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
            padding: 40px 50px; /* Padding disesuaikan sedikit */
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
            filter: contrast(120%);
        }

        .auth-left .text-field-input {
            background: #E5D0C9;
            border: none;
            color: #131316;
            margin-top: 6px;
        }

        .auth-left .text-field-label {
            color: white;
            font-size: 13px;
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

            <h1 class="title-text" style="font-size: 32px; margin-bottom: 25px; color: white;">Daftar Akun Baru</h1>

            <form method="POST" action="{{ route('register') }}">
                @csrf
                
                <div class="text-field">
                    <label class="text-field-label">Nama Lengkap</label>
                    <input type="text" name="name" class="text-field-input" placeholder="Nama Anda" required autofocus>
                </div>

                <div class="text-field">
                    <label class="text-field-label">Email</label>
                    <input type="email" name="email" class="text-field-input" placeholder="nama@gmail.com" required>
                </div>

                <div class="flex gap-4">
                    <div class="text-field flex-1">
                        <label class="text-field-label">Kata Sandi</label>
                        <div class="password-wrapper">
                            <input type="password" name="password" class="text-field-input password-input" placeholder="••••••••" required>
                            <button type="button" class="toggle-password">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-2 text-white text-xs" />
                    </div>

                    <div class="text-field flex-1">
                        <label class="text-field-label">Konfirmasi Sandi</label>
                        <div class="password-wrapper">
                            <input type="password" name="password_confirmation" class="text-field-input password-input" placeholder="••••••••" required>
                            <button type="button" class="toggle-password">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-braga-glass" style="margin-top: 10px;">
                    Daftar Sekarang
                </button>

                <div style="margin-top: 20px; text-align: center;">
                    <a href="{{ route('login') }}" class="subtitle-text" style="color: white; font-size: 14px; text-decoration: none;">
                        Sudah punya akun? <span style="text-decoration: underline; font-weight: bold;">Masuk</span>
                    </a>
                </div>
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