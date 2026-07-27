@csrf

<div>
    <label for="officer_code">Kode Petugas</label><br>
    <input id="officer_code" name="officer_code" value="{{ old('officer_code', $officer->officer_code ?? '') }}" required>
    @error('officer_code') <p style="color: #dc2626;">{{ $message }}</p> @enderror
</div>

<div>
    <label for="name">Nama Petugas</label><br>
    <input id="name" name="name" value="{{ old('name', $officer->name ?? '') }}" required>
    @error('name') <p style="color: #dc2626;">{{ $message }}</p> @enderror
</div>

<div>
    <label for="email">Email Login</label><br>
    <input id="email" name="email" type="email" value="{{ old('email', $officer->user->email ?? '') }}" required>
    @error('email') <p style="color: #dc2626;">{{ $message }}</p> @enderror
</div>

<div>
    <label for="phone">Nomor HP</label><br>
    <input id="phone" name="phone" value="{{ old('phone', $officer->phone ?? '') }}">
    @error('phone') <p style="color: #dc2626;">{{ $message }}</p> @enderror
</div>

<div>
    <label for="address">Alamat</label><br>
    <textarea id="address" name="address">{{ old('address', $officer->address ?? '') }}</textarea>
    @error('address') <p style="color: #dc2626;">{{ $message }}</p> @enderror
</div>

<div>
    <label for="status">Status</label><br>
    <select id="status" name="status" required>
        <option value="active" @selected(old('status', $officer->status ?? 'active') === 'active')>Aktif</option>
        <option value="inactive" @selected(old('status', $officer->status ?? 'active') === 'inactive')>Nonaktif</option>
    </select>
    @error('status') <p style="color: #dc2626;">{{ $message }}</p> @enderror
</div>

<div>
    <label for="password">Password {{ isset($officer) ? 'Baru (opsional)' : '' }}</label><br>
    <input id="password" name="password" type="password" @required(! isset($officer))>
    @error('password') <p style="color: #dc2626;">{{ $message }}</p> @enderror
</div>

<div>
    <label for="password_confirmation">Konfirmasi Password</label><br>
    <input id="password_confirmation" name="password_confirmation" type="password" @required(! isset($officer))>
</div>

<button type="submit">{{ $submitLabel }}</button>
