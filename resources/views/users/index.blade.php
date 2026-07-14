@extends('layouts.app') 

@section('content') 
<div class="min-h-screen"> 
    <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-4 pb-8"> 
        <div> 
            <h1 class="title-text">Manajemen User</h1> 
            <p class="subtitle-text">Braga8 Utility Billing Management</p> 
        </div> 
        <div class="header-user"> 
            <div class="icon-wrapper" data-popup="notif-popup"> 
                <i class="fa-solid fa-bell"></i> 
                @if(auth()->user()->customNotifications()->whereNull('read_at')->exists()) 
                <span class="notif-dot"></span> 
                @endif 
            </div> 
            <div class="profile-container" data-popup="detail-profile-popup"> 
                <div class="profile-icon"> 
                    <i class="fa-solid fa-user text-2xl text-[#a04d30]"></i> 
                </div> 
            </div> 
        </div> 
    </div> 

    <div class="flex flex-col gap-6"> 
        @if (session('status')) 
        @php 
        $alerts = [ 
            'user-stored'     => [ 'title' => 'Berhasil Registrasi!', 'desc'  => 'Akun baru telah berhasil ditambahkan ke sistem.', 'icon'  => 'fa-circle-check' ], 
            'user-updated'    => [ 'title' => 'Update Berhasil!', 'desc'  => 'Informasi akun telah diperbarui sepenuhnya.', 'icon'  => 'fa-user-check' ], 
            'user-deleted'    => [ 'title' => 'Akun Dihapus!', 'desc'  => 'Data user tersebut telah dibersihkan dari database.', 'icon'  => 'fa-trash-can' ], 
            'profile-updated' => [ 'title' => 'Profil Diperbarui!', 'desc'  => 'Informasi akun kamu sudah berhasil diubah.', 'icon'  => 'fa-id-card' ] 
        ]; 
        $statusKey = session('status'); 
        $current = $alerts[$statusKey] ?? null; 
        @endphp 
        @if ($current) 
        <div id="universal-alert" class="fixed top-6 right-6 z-[9999] flex items-center justify-between p-5 min-w-[380px] text-white border border-white/20 rounded-2xl backdrop-blur-md shadow-[0_10px_40px_rgba(0,0,0,0.5)] transition-all duration-500" style="background-color: rgba(96, 35, 22, 0.6);"> 
            <div class="flex items-center gap-4"> 
                <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-white/10 border border-white/10 shadow-inner"> 
                    <i class="fa-solid {{ $current['icon'] }} text-[#FA8327] text-lg"></i> 
                </div> 
                <div class="flex flex-col gap-0.5"> 
                    <p class="text-sm font-bold tracking-wide">{{ $current['title'] }}</p> 
                    <p class="text-xs text-white/50 font-light">{{ $current['desc'] }}</p> 
                </div> 
            </div> 
            <button type="button" onclick="closeUniversalAlert()" class="p-2 text-white/20 hover:text-[#FA8327] transition-colors"> 
                <i class="fa-solid fa-xmark text-lg"></i> 
            </button> 
        </div> 
        <script> 
            function closeUniversalAlert() { 
                const alert = document.getElementById('universal-alert'); 
                if (alert) { 
                    alert.style.opacity = '0'; 
                    alert.style.transform = 'translateX(30px)'; 
                    setTimeout(() => alert.remove(), 500); 
                } 
            } 
            setTimeout(closeUniversalAlert, 4500); 
        </script> 
        @endif 
        @endif 

        <div class="toolbar"> 
            <form method="GET" action="{{ route('users.index') }}" class="w-full md:w-72"> 
                <input type="hidden" name="role" value="{{ $role }}"> 
                <div class="search-wrapper !m-0"> 
                    <input type="text" name="search" placeholder="Cari {{ $role }}..." value="{{ request('search') }}"> 
                    <span><i class="fa-solid fa-magnifying-glass"></i></span> 
                </div> 
            </form> 
            <button type="button" class="dark-brown-button btn-small w-full md:w-auto" data-popup="create-user-modal"> 
                <span><i class="fa-solid fa-plus text-xs"></i></span> 
                <span>Buat Akun Baru</span> 
            </button> 
        </div> 

        <div class="table-wrapper"> 
            <div class="table-card overflow-hidden"> 
                <div class="table-card-header !p-0 overflow-hidden border-b border-white/20"> 
                    <div class="flex w-full h-full"> 
                        @foreach(['admin' => 'Admin', 'petugas' => 'Petugas', 'tenant' => 'Penyewa'] as $key => $label) 
                        <a href="{{ route('users.index', ['role' => $key, 'search' => request('search')]) }}" class="flex-1 flex items-center justify-center gap-3 py-5 text-[11px] font-bold tracking-widest uppercase transition-all border-r border-white/10 {{ $role === $key ? 'bg-white/10 text-white shadow-[inset_0_2px_10px_rgba(255,255,255,0.1)]' : 'text-zinc-500 hover:bg-white/[0.02] hover:text-zinc-300' }}"> 
                            <i class="fa-solid {{ $key === 'admin' ? 'fa-user-shield' : ($key === 'petugas' ? 'fa-user-tie' : 'fa-users') }} text-[12px]"></i> {{ $label }} 
                        </a> 
                        @endforeach 
                    </div> 
                </div> 
                <table class="table w-full"> 
                    <thead> 
                        <tr> 
                            <th>No</th> 
                            <th>Nama</th> 
                            <th>Username</th> 
                            <th>Email</th> 
                            <th>No. Telepon</th> 
                            <th class="text-center">Aksi</th> 
                        </tr> 
                    </thead> 
                    <tbody> 
                        @forelse($users as $user) 
                        <tr> 
                            <td>{{ $loop->iteration }}</td> 
                            <td class="font-bold text-zinc-100">{{ $user->name }}</td> 
                            <td>{{ '@' . $user->username }}</td> 
                            <td>{{ $user->email }}</td> 
                            <td>{{ $user->phone_number ?? '-' }}</td> 
                            <td class="actions"> 
                                <div class="flex justify-center gap-2"> 
                                    <button type="button" class="light-green-btn-action" data-popup="edit-user-{{ $user->id }}"> 
                                        <i class="fa-solid fa-pen"></i> Ubah 
                                    </button> 
                                    
                                    <button type="button" class="dark-brown-btn-action border-0" data-popup="delete-user-{{ $user->id }}"> 
                                        <i class="fa-solid fa-trash text-xs"></i> <span class="text-xs">Hapus</span> 
                                    </button> 
                                </div> 

                                <div class="popup" id="edit-user-{{ $user->id }}"> 
                                    <div class="popup-overlay"></div> 
                                    <div class="popup-card popup-md"> 
                                        <div class="popup-close-wrapper"> 
                                            <button type="button" class="popup-close" data-close="edit-user-{{ $user->id }}"> 
                                                <i class="fa-solid fa-xmark"></i> 
                                            </button> 
                                        </div> 
                                        <div class="popup-header">Edit Akun User</div> 
                                        <form action="{{ route('users.update', $user->id) }}" method="POST"> 
                                            @csrf 
                                            @method('PUT') 
                                            <div class="popup-body flex flex-col gap-6"> 
                                                <div> 
                                                    <div class="text-field"> 
                                                        <label class="text-field-label text-left">Nama Lengkap</label> 
                                                        <input type="text" name="name" class="text-field-input" value="{{ old('name', $user->name) }}" required> 
                                                    </div> 
                                                    <div class="grid grid-cols-2 gap-4"> 
                                                        <div class="text-field"> 
                                                            <label class="text-field-label text-left">Username</label> 
                                                            <input type="text" name="username" class="text-field-input" value="{{ old('username', $user->username) }}" required> 
                                                        </div> 
                                                        <div class="text-field"> 
                                                            <label class="text-field-label text-left">No. Telepon</label> 
                                                            <input type="text" name="phone_number" class="text-field-input" value="{{ old('phone_number', $user->phone_number) }}"> 
                                                        </div> 
                                                    </div> 
                                                    <div class="text-field"> 
                                                        <label class="text-field-label text-left">Alamat Email</label> 
                                                        <input type="email" name="email" class="text-field-input" value="{{ old('email', $user->email) }}" required> 
                                                    </div> 
                                                    <div class="grid grid-cols-2 gap-4"> 
                                                        <div class="text-field"> 
                                                            <label class="text-field-label text-left">Role Akun</label> 
                                                            <div class="custom-dropdown"> 
                                                                <div class="dropdown-selected"> 
                                                                    <span class="placeholder text-[#131316]"> 
                                                                        {{ ucfirst($user->role) }} 
                                                                    </span> 
                                                                    <i class="fa-solid fa-chevron-down text-[10px]" style="color: #131316;"></i> 
                                                                </div> 
                                                                <div class="dropdown-options"> 
                                                                    <div class="option" data-value="admin">Administrator</div> 
                                                                    <div class="option" data-value="supervisor">Supervisor</div> 
                                                                    <div class="option" data-value="petugas">Petugas</div> 
                                                                    <div class="option" data-value="tenant">Penyewa</div> 
                                                                </div> 
                                                                <input type="hidden" name="role" value="{{ $user->role }}" required> 
                                                                </div> 
                                                            </div> 
                                                        <div class="text-field"> 
                                                            <label class="text-field-label text-left">Password Baru</label> 
                                                            <div class="relative w-full"> 
                                                                <input type="password" name="password" class="text-field-input pr-12 password-input" placeholder="••••••••"> 
                                                                <button type="button" class="toggle-password absolute right-4 top-1/2 -translate-y-1/2 text-zinc-500 hover:text-[#FA8327]"> 
                                                                    <i class="fa-solid fa-eye"></i> 
                                                                </button> 
                                                            </div> 
                                                        </div> 
                                                    </div> 
                                                </div> 
                                                <button type="submit" class="dark-brown-button w-full"> Simpan Perubahan </button> 
                                            </div> 
                                        </form> 
                                    </div> 
                                </div> 

                                <div class="popup" id="delete-user-{{ $user->id }}"> 
                                    <div class="popup-overlay"></div> 
                                    <div class="popup-card popup-md"> 
                                        <div class="popup-close-wrapper"> 
                                            <button type="button" class="popup-close" data-close="delete-user-{{ $user->id }}"> 
                                                <i class="fa-solid fa-xmark"></i> 
                                            </button> 
                                        </div> 
                                        <div class="popup-header">Hapus User <span class="text-[#FA8327]">{{ $user->name }}</span>?</div> 
                                        <div class="popup-body"> 
                                            <div class="btn-delete-wrapper flex gap-4"> 
                                                <button type="button" class="dark-brown-button flex-1 py-2.5" data-close="delete-user-{{ $user->id }}">Batal</button> 
                                                <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="flex-1 m-0 p-0"> 
                                                    @csrf 
                                                    @method('DELETE') 
                                                    <button type="submit" class="light-brown-btn w-full py-2.5">Ya, Hapus</button> 
                                                </form>
                                            </div> 
                                        </div> 
                                    </div> 
                                </div> 
                            </td> 
                        </tr> 
                        @empty 
                        <tr> 
                            <td colspan="6" class="text-center py-20 text-zinc-500 italic"> Data {{ $role }} tidak ditemukan di sistem. </td> 
                        </tr> 
                        @endforelse 
                    </tbody>
                </table> 
            </div> 
        </div> 
    </div> 
</div> 

<div class="popup" id="create-user-modal"> 
    <div class="popup-overlay"></div> 
    <div class="popup-card popup-md"> 
        <div class="popup-close-wrapper"> 
            <button type="button" class="popup-close" data-close="create-user-modal"> <i class="fa-solid fa-xmark"></i> </button> 
        </div> 
        <div class="popup-header">Daftarkan User Baru</div> 
        <form action="{{ route('users.store') }}" method="POST"> 
            @csrf 
            <div class="popup-body flex flex-col gap-6"> 
                <div> 
                    <div class="text-field"> 
                        <label class="text-field-label text-left">Nama Lengkap</label> 
                        <input type="text" name="name" class="text-field-input" placeholder="Masukkan nama lengkap.." required> 
                    </div> 
                    <div class="grid grid-cols-2 gap-4"> 
                        <div class="text-field"> 
                            <label class="text-field-label text-left">Username</label> 
                            <input type="text" name="username" class="text-field-input" placeholder="Username unik.." required> 
                        </div> 
                        <div class="text-field"> 
                            <label class="text-field-label text-left">No. Telepon</label> 
                            <input type="text" name="phone_number" class="text-field-input" placeholder="08xxxxxxxx"> 
                        </div> 
                    </div> 
                    <div class="text-field"> 
                        <label class="text-field-label text-left">Alamat Email</label> 
                        <input type="email" name="email" class="text-field-input" placeholder="email@jasa.com" required> 
                    </div> 
                    <div class="grid grid-cols-2 gap-4"> 
                        <div class="text-field"> 
                            <label class="text-field-label text-left">Role Akun</label> 
                            <div class="custom-dropdown"> 
                                <div class="dropdown-selected"> 
                                    <span class="placeholder">Pilih Role</span> 
                                    <i class="fa-solid fa-chevron-down text-[10px]"></i> 
                                </div> 
                                <div class="dropdown-options"> 
                                    <div class="option" data-value="admin">Administrator</div> 
                                    <div class="option" data-value="supervisor">Supervisor</div> 
                                    <div class="option" data-value="petugas">Petugas</div> 
                                    <div class="option" data-value="tenant">Penyewa</div> 
                                </div> 
                                <input type="hidden" name="role" required> 
                            </div> 
                        </div> 
                        <div class="text-field"> 
                            <label class="text-field-label text-left">Password</label> 
                            <div class="relative w-full"> 
                                <input type="password" name="password" class="text-field-input pr-12 password-input" placeholder="••••••••" required> 
                                <button type="button" class="toggle-password text-zinc-500 hover:text-[#FA8327]"> 
                                    <i class="fa-solid fa-eye"></i> 
                                </button> 
                            </div> 
                        </div> 
                    </div> 
                </div> 
                <button type="submit" class="dark-brown-button w-full"> Simpan Akun Baru </button> 
            </div> 
        </form> 
    </div> 
</div> 
@endsection