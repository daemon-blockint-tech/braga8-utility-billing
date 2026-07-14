<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
   <title>Braga8 Utility Billing</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
   @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>


<body>
    <main>
        <aside class="sidebar">
            <div class="sidebar-logo">
                <img src="{{ asset('app-logo.svg') }}" class="logo-image" alt="Logo">
            </div>

            <a href="{{ route('dashboard') }}">
                <h1 @class([
                    'sidebar-title',
                    'active-title' => request()->routeIs('dashboard') 
                ])>Dashboard</h1>
            </a>
            <nav class="sidebar-nav custom-scrollbar">
                <div @class(['menu-group', 'active' => request()->routeIs(['tenants.*', 'units.*'])])>
                    <a href="#" class="menu-item">
                        <span><i class="fa-solid fa-shop"></i></span>
                        <span>Penyewa & Unit</span>
                    </a>
                    <div class="submenu">
                        <a href="{{ route('tenants.index') }}" @class(['active' => request()->routeIs('tenants.*')])>Daftar Penyewa</a>
                        <a href="{{ route('units.index') }}" @class(['active' => request()->routeIs('units.*')])>Daftar Unit</a>
                    </div>
                </div>

                <div @class(['menu-group', 'active' => request()->routeIs(['utility-meters.*', 'meter-readings.*'])])>
                    <a href="#" class="menu-item">
                        <span><i class="fa-solid fa-gear"></i></span>
                        <span>Utilitas</span>
                    </a>
                    <div class="submenu">
                        <a href="{{ route('utility-meters.index') }}" @class(['active' => request()->routeIs('utility-meters.*')])>Data Meter</a>
                        <a href="{{ route('meter-readings.index') }}" @class(['active' => request()->routeIs('meter-readings.*')])>Catatan Meter</a>
                    </div>
                </div>

                <div @class(['menu-group', 'active' => request()->routeIs(['tariffs.*', 'invoices.*'])])>
                    <a href="#" class="menu-item">
                        <span><i class="fa-solid fa-money-bill"></i></span>
                        <span>Tarif & Tagihan</span>
                    </a>
                    <div class="submenu">
                        <a href="{{ route('tariffs.index') }}" @class(['active' => request()->routeIs('tariffs.*')])>Pengaturan Tarif</a>
                        <a href="{{ route('invoices.index') }}" @class(['active' => request()->routeIs('invoices.*')])>Tagihan</a>
                    </div>
                </div>

                <div @class(['menu-group', 'active' => request()->routeIs(['payments.*'])])>
                    <a href="{{ route('payments.index') }}" class="menu-item">
                        <span><i class="fa-solid fa-credit-card"></i></span>
                        <span>Pembayaran</span>
                    </a>
                </div>

                <div class="menu-group">
                    <a href="{{ route('reports.index') }}" @class(['menu-item', 'active'=> request()->routeIs('reports.*')])>
                        <span><i class="fa-solid fa-list"></i></span>
                        <span>Laporan Pemakaian</span>
                    </a>
                </div>

                <div class="menu-group">
                    <a href="{{ route('complaints.index') }}" @class(['menu-item', 'active'=> request()->routeIs('complaints.*')])>
                        <span><i class="fa-solid fa-triangle-exclamation"></i></span>
                        <span>Keluhan</span>
                    </a>
                </div>

                <div class="menu-group">
                    <a href="{{ route('users.index') }}" @class(['menu-item', 'active' => request()->routeIs('users.*')])>
                        <span><i class="fa-solid fa-user"></i></span>
                        <span>Manajemen Pengguna</span>
                    </a>
                </div>

                <div class="menu-group">
                    <a href="{{ route('audit_logs.index') }}" @class(['menu-item', 'active' => request()->routeIs('audit_logs.*')])>
                        <span><i class="fa-solid fa-clock-rotate-left"></i></span>
                        <span>Log Audit</span>
                    </a>
                </div>

                <div class="menu-group">
                    <a href="{{ route('reminders.index') }}" @class(['menu-item', 'active' => request()->routeIs('reminders.*')])>
                        <span><i class="fa-solid fa-sliders"></i></span>
                        <span>Siklus Penagihan</span>
                    </a>
                </div>
            </nav>
        </aside>

        <div class="flex-1 flex flex-col">
            <div class="p-8 w-full">
                @yield('content')
            </div>
        </div>
    </main>
    
    <div class="popup" id="detail-profile-popup">
        <div class="popup-overlay"></div>
        <div class="popup-card popup-md">
            <div class="popup-close-wrapper">
                <button class="popup-close" type="button"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="popup-header">Rincian Akun</div>
            <div class="popup-body user-account-info flex flex-col gap-10">
                <div class="flex flex-row gap-4">
                    <div class="profile-container">
                        <div class="profile-icon">
                            <i class="fa-solid fa-user text-2xl text-[#a04d30]"></i>
                        </div>
                    </div>
                    <div class="user-info">
                        <p class="font-bold text-lg text-zinc-800">{{ auth()->user()->name }}</p>
                        <p class="text-zinc-500 text-sm">@ {{ auth()->user()->username ?? 'admin' }}</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="detail-item">
                        <p class="text-xs text-zinc-400 font-medium">Tanggal Bergabung</p>
                        <p class="font-semibold text-zinc-700">{{ auth()->user()->created_at->format('d M Y') }}</p>
                    </div>
                    <div class="detail-item">
                        <p class="text-xs text-zinc-400 font-medium">Peran</p>
                        <p class="font-semibold text-zinc-700 uppercase text-xs tracking-wider">{{ auth()->user()->role ?? 'Staff' }}</p>
                    </div>
                    <div class="detail-item col-span-2">
                        <p class="text-xs text-zinc-400 font-medium">Email</p>
                        <p class="font-semibold text-zinc-700">{{ auth()->user()->email }}</p>
                    </div>
                </div>
                
                <div class="flex flex-col gap-4">
                    <div class="flex gap-2">
                        <button class="green-btn flex-1" id="openEdit" data-popup="edit-profile-popup">
                            <span><i class="fa-solid fa-pen"></i></span> <span>Edit Akun</span>
                        </button>
                        <form method="POST" action="{{ route('logout') }}" class="flex-1">
                            @csrf
                            <button type="submit" class="light-brown-btn w-full">
                                <span><i class="fa-solid fa-arrow-right-from-bracket"></i></span> <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

<div class="popup" id="edit-profile-popup">
    <div class="popup-overlay"></div>
    <div class="popup-card popup-md">
        <div class="popup-close-wrapper">
            <button class="popup-close" type="button"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="popup-header">Edit & Perbarui Akun</div>
        <div class="popup-body">
            <form method="post" action="{{ route('profile.update') }}" class="flex flex-col gap-5">
                @csrf
                @method('patch')

                <div class="flex flex-col gap-4">
                    <div> 
                        <div class="text-field"> 
                            <label class="text-field-label">Nama</label> 
                            <input type="text" name="name" class="text-field-input" value="{{ old('name', auth()->user()->name) }}" required> 
                            <x-input-error class="mt-1 text-xs" :messages="$errors->get('name')" /> 
                        </div> 

                        <div class="text-field"> 
                            <label class="text-field-label">Email</label> 
                            <input type="email" name="email" class="text-field-input" value="{{ old('email', auth()->user()->email) }}" required> 
                            <x-input-error class="mt-1 text-xs" :messages="$errors->get('email')" /> 
                        </div> 

                        <div class="text-field"> 
                            <label class="text-field-label mb-1.5 block">Password Saat Ini</label> 
                            
                            <div class="relative flex items-center w-full">
                                <input type="password" name="current_password" class="text-field-input password-input w-full pr-12" placeholder="••••••••"> 
                                
                                <button type="button" class="toggle-password absolute right-4 text-zinc-400 hover:text-zinc-600"> 
                                    <i class="fa-solid fa-eye text-sm"></i> 
                                </button> 
                            </div>
                            <x-input-error :messages="$errors->get('current_password') ?? $errors->updatePassword->get('current_password')" class="mt-1 text-xs" /> 
                        </div> 

                        <div class="text-field"> 
                            <label class="text-field-label mb-1.5 block">Password Baru</label> 
                            
                            <div class="relative flex items-center w-full">
                                <input type="password" name="password" class="text-field-input password-input w-full pr-12" placeholder="Minimal 8 karakter"> 
                                
                                <button type="button" class="toggle-password absolute right-4 text-zinc-400 hover:text-zinc-600"> 
                                    <i class="fa-solid fa-eye text-sm"></i> 
                                </button> 
                            </div>
                            <x-input-error :messages="$errors->get('password') ?? $errors->updatePassword->get('password')" class="mt-1 text-xs" /> 
                        </div> 

                        <div class="text-field"> 
                            <label class="text-field-label mb-1.5 block">Konfirmasi Password Baru</label> 
                            
                            <div class="relative flex items-center w-full">
                                <input type="password" name="password_confirmation" class="text-field-input password-input w-full pr-12" placeholder="Ulangi password baru"> 
                                
                                <button type="button" class="toggle-password absolute right-4 text-zinc-400 hover:text-zinc-600"> 
                                    <i class="fa-solid fa-eye text-sm"></i> 
                                </button> 
                            </div>
                            <x-input-error :messages="$errors->get('password_confirmation') ?? $errors->updatePassword->get('password_confirmation')" class="mt-1 text-xs" /> 
                        </div> 
                    </div>

                    <button type="submit" class="green-btn w-full py-2.5 mt-2">
                        Simpan Perubahan Akun
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

    <div class="popup" id="delete-account-popup">
        <div class="popup-overlay"></div>
        <div class="popup-card popup-md">
            <div class="popup-close-wrapper">
                <button class="popup-close" type="button"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="popup-header text-rose-600">Hapus Akun Permanen</div>
            <div class="popup-body">
                <p class="text-xs text-zinc-500 mb-6 leading-relaxed">
                    Tindakan ini tidak bisa dibatalkan. Semua data riwayat pengelolaan unit Braga8 Anda akan dihapus permanen. SIlakan konfirmasi dengan memasukkan password akun Anda.
                </p>
                
                <form method="post" action="{{ url('/profile') }}" class="flex flex-col gap-6">
                    @csrf
                    @method('delete')
                    
                    <div class="text-field">
                        <label class="text-field-label">Kata Sandi Anda</label>
                        <input type="password" name="password" class="text-field-input" placeholder="Masukkan password untuk konfirmasi" required>
                        <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-1 text-xs" />
                    </div>
                    
                    <div class="flex gap-2 justify-end">
                        <button type="button" class="popup-close px-4 py-2.5 rounded-xl border border-zinc-200 text-zinc-600 hover:bg-zinc-50 text-xs font-medium transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition-colors shadow-sm">
                            Ya, Hapus Permanen
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="popup" id="notif-popup">
        <div class="popup-overlay"></div>
        <div class="popup-card popup-md">
            <div class="popup-close-wrapper">
                <button class="popup-close"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="popup-header">Pemberitahuan</div>
            <div class="popup-body flex flex-col gap-2">
                @if(auth()->user()->customNotifications->isNotEmpty())
                <div class="flex justify-end mb-3">
                    <form action="{{ route('notifications.destroyAll') }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="text-xs text-zinc-500 hover:text-rose-400 transition-colors flex items-center gap-1.5">
                            <i class="fa-solid fa-trash-can text-xs"></i>
                            Hapus Semua
                        </button>
                    </form>
                </div>
                @endif

                <div class="notification-wrapper">
                    @forelse(auth()->user()->customNotifications as $notif)
                        <div class="notification {{ $notif->read_at ? 'is-read' : 'is-unread' }}" id="notif-{{ $notif->id }}"> 
                            <div class="notif-box"> 
                                <div class="flex justify-between items-start mb-2"> 
                                    <div class="flex items-center gap-2"> 
                                        <i class="notif-icon fa-solid {{ $notif->read_at ? 'fa-envelope-open text-zinc-500' : 'fa-envelope text-[#FA8327]' }} text-xs"></i> 
                                        <h4 class="font-bold text-white text-sm">{{ $notif->title }}</h4> 
                                    </div> 
                                    <div class="notif-actions flex gap-3"> 
                                        @if(!$notif->read_at) 
                                        <form action="{{ route('notifications.read', $notif->id) }}" method="POST" class="read-notif-form"> 
                                            @csrf 
                                            <button type="submit" class="text-zinc-500 hover:text-zinc-300 transition-colors hover:scale-110" title="Tandai Baca"> 
                                                <i class="fa-solid fa-check text-xs"></i> 
                                            </button> 
                                        </form> 
                                        @endif 
                                        
                                        <form action="{{ route('notifications.destroy', $notif->id) }}" method="POST" class="delete-notif-form"> 
                                            @csrf 
                                            @method('DELETE') 
                                            <button type="submit" class="text-zinc-500 hover:text-zinc-300 transition-colors hover:scale-110"> 
                                                <i class="fa-solid fa-trash-can text-xs"></i> 
                                            </button> 
                                        </form> 
                                    </div> 
                                </div> 
                                <p class="notif-message">{{ $notif->message }}</p> 
                                <div class="mt-3 text-[10px] text-zinc-500 text-right italic"> 
                                    {{ $notif->created_at->diffForHumans() }} 
                                </div> 
                            </div> 
                        </div>
                    @empty
                        <div class="py-16 text-center">
                            <div class="text-zinc-700 mb-3">
                                <i class="fa-solid fa-bell-slash text-4xl"></i>
                            </div>
                            <p class="text-zinc-500 text-sm italic">Belum ada pemberitahuan baru.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @stack('scripts')
</body>
</html>

