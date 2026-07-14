<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email - Braga 8</title>
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

        .status-msg {
            background: rgba(0, 0, 0, 0.2);
            padding: 15px;
            border-radius: 14px;
            color: #69ff91;
            font-size: 13px;
            margin-bottom: 20px;
            border-left: 4px solid #69ff91;
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

            <h1 style="font-size: 30px; margin-bottom: 10px; color: white; font-weight: 600;">Verifikasi Email</h1>
            
            <p style="color: white; opacity: 0.9; font-size: 14px; line-height: 1.6; margin-bottom: 25px;">
                Terima kasih sudah mendaftar! Sebelum mulai, tolong verifikasi email kamu ya dengan klik link yang baru saja kami kirimkan. Belum terima emailnya? Klik tombol di bawah untuk kirim ulang.
            </p>

            @if (session('status') == 'verification-link-sent')
                <div class="status-msg">
                    Link verifikasi baru telah dikirim ke alamat email yang kamu daftarkan.
                </div>
            @endif

            <div style="display: flex; flex-direction: column; gap: 15px;">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="btn-braga-glass" style="margin-top: 0;">
                        Kirim Ulang Email Verifikasi
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}" style="text-align: center;">
                    @csrf
                    <button type="submit" style="background: none; border: none; color: white; opacity: 0.6; font-size: 13px; cursor: pointer; text-decoration: underline;">
                        Keluar (Log Out)
                    </button>
                </form>
            </div>
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