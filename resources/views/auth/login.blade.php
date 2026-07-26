<x-layouts.app>
    <h1>Login Sistem Distribusi MBG</h1>
    <p>Masuk menggunakan akun Admin, Petugas Distribusi, atau Kepala SPPG.</p>

    <form method="POST" action="{{ route('login.store') }}" style="display: grid; gap: 16px; max-width: 420px;">
        @csrf

        <label>
            Email
            <input name="email" type="email" value="{{ old('email') }}" required autofocus style="display: block; width: 100%; padding: 8px;">
            @error('email')
                <small style="color: #dc2626;">{{ $message }}</small>
            @enderror
        </label>

        <label>
            Password
            <input name="password" type="password" required style="display: block; width: 100%; padding: 8px;">
            @error('password')
                <small style="color: #dc2626;">{{ $message }}</small>
            @enderror
        </label>

        <label>
            <input name="remember" type="checkbox" value="1">
            Ingat saya
        </label>

        <button type="submit" style="padding: 10px 14px;">Login</button>
    </form>
</x-layouts.app>
