<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Braga 8</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            margin: 0; padding: 0;
            display: flex; justify-content: center; align-items: center;
            min-height: 100vh; background-color: #141315;
        }
        .auth-container {
            display: flex; width: 100%; max-width: 1100px; height: 700px;
            gap: 24px; padding: 20px; box-sizing: border-box;
        }
        .auth-left {
            flex: 1;
            background: linear-gradient(145deg, #E05A00 0%, #8C2A00 100%);
            border-radius: 40px; padding: 50px;
            display: flex; flex-direction: column; justify-content: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative; overflow: hidden;
        }
        .auth-right { flex: 1.8; display: flex; flex-direction: column; gap: 20px; }
        
        .visual-top {
            flex: 1.6; background-color: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 40px;
            display: flex; align-items: center; justify-content: flex-end;
            position: relative; overflow: hidden;
        }
        .visual-top img {
            width: 100%; height: auto; object-fit: contain;
            margin-right: -15%; transform: rotate(-5deg);
        }
        .visual-bottom {
            flex: 1; background-color: #1a1a1c;
            border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 40px;
            position: relative; overflow: hidden;
        }
        .visual-bottom img {
            width: 100%; height: 100%; object-fit: cover; opacity: 0.4;
        }

        /* Form Styling */
        .auth-left .text-field-input {
            background: #E5D0C9; border: none; color: #131316;
            margin-top: 6px; width: 100%; padding: 12px 14px; border-radius: 14px;
        }
        .auth-left .text-field-label {
            color: white; font-size: 13px; display: block; margin-top: 15px;
        }

        @media (max-width: 900px) { .auth-right { display: none; } }
    </style>
</head>
<body>

    <div class="auth-container">
        <div class="auth-left">
            <div style="margin-bottom: 20px;">
                <img src="{{ asset('logo.svg') }}" alt="Braga 8" style="height: 80px;">
            </div>

            <h1 style="font-size: 30px; margin-bottom: 10px; color: white; font-weight: 600;">Lupa Password?</h1>
            
            <p style="color: white; opacity: 0.8; font-size: 14px; line-height: 1.6; margin-bottom: 25px;">
                {{ __('Jangan panik. Masukkan alamat email kamu dan kami akan kirimkan link reset password biar kamu bisa bikin password baru.') }}
            </p>

            <x-auth-session-status class="mb-4" :status="session('status')" style="color: #993d00; font-size: 14px; font-weight: bold;" />

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <div class="text-field">
                    <label class="text-field-label">Alamat Email</label>
                    <input type="email" name="email" class="text-field-input" placeholder="nama@gmail.com" :value="old('email')" required autofocus>
                    <x-input-error :messages="$errors->get('email')" class="mt-2" style="color: #ffcfcf; font-size: 12px;" />
                </div>

                <button type="submit" class="btn-braga-glass">
                    {{ __('Kirim Link Reset') }}
                </button>

                <div style="margin-top: 30px; text-align: center;">
                    <a href="{{ route('login') }}" style="color: white; font-size: 14px; text-decoration: none; opacity: 0.7;">
                         Kembali ke Login
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

</body>
</html>