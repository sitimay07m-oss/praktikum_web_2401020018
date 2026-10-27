<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Inventaris</title>
</head>
<body>
    <h1>Edit Inventaris</h1>

    @if (session('gagal'))
        <p><strong>{{ session('gagal') }}</strong></p>
    @endif

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $pesan)
                <li>{{ $pesan }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="/inventaris/{{ $inventaris['id'] }}">
        @csrf
        @method('PUT')

        <p>
            <label for="kode_barang">Kode Barang (10 karakter)</label><br>
            <input type="text" id="kode_barang" name="kode_barang"
                   value="{{ old('kode_barang', $inventaris['kode_barang']) }}">
        </p>

        <p>
            <label for="nama_barang">Nama Barang</label><br>
            <input type="text" id="nama_barang" name="nama_barang"
                   value="{{ old('nama_barang', $inventaris['nama_barang']) }}">
        </p>

        <p>
            <label for="nomor_aset">Nomor Aset</label><br>
            <input type="text" id="nomor_aset" name="nomor_aset"
                   value="{{ old('nomor_aset', $inventaris['nomor_aset']) }}">
        </p>

        <p>
            <label for="tahun_perolehan">Tahun Perolehan (2000-2026)</label><br>
            <input type="text" id="tahun_perolehan" name="tahun_perolehan"
                   value="{{ old('tahun_perolehan', $inventaris['tahun_perolehan']) }}">
        </p>

        <p>
            <label for="ruang_id">Ruang</label><br>
            <select id="ruang_id" name="ruang_id">
                @foreach ($daftarRuang as $ruang)
                    <option value="{{ $ruang['id'] }}"
                        {{ old('ruang_id', $inventaris['ruang_id']) == $ruang['id'] ? 'selected' : '' }}>
                        {{ $ruang['nama_ruang'] }}
                    </option>
                @endforeach
            </select>
        </p>

        <button type="submit">Update</button>
        <a href="/inventaris">Batal</a>
    </form>
</body>
</html>