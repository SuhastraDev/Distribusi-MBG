<x-layouts.app>
    <h1>Ubah Password</h1>

    <form method="POST" action="{{ route('password.update') }}" style="display: grid; gap: 16px; max-width: 420px;">
        @csrf
        @method('PUT')

        <label>
            Password lama
            <input name="current_password" type="password" required style="display: block; width: 100%; padding: 8px;">
            @error('current_password')
                <small style="color: #dc2626;">{{ $message }}</small>
            @enderror
        </label>

        <label>
            Password baru
            <input name="password" type="password" required style="display: block; width: 100%; padding: 8px;">
            @error('password')
                <small style="color: #dc2626;">{{ $message }}</small>
            @enderror
        </label>

        <label>
            Konfirmasi password baru
            <input name="password_confirmation" type="password" required style="display: block; width: 100%; padding: 8px;">
        </label>

        <button type="submit" style="padding: 10px 14px;">Simpan Password</button>
    </form>
</x-layouts.app>
