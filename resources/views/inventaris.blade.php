<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Inventaris Laboratorium</title>
</head>
<body>
    <h1>Data Inventaris Laboratorium</h1>

    @if (session('berhasil'))
        <p><strong>{{ session('berhasil') }}</strong></p>
    @endif

    @if (session('gagal'))
        <p><strong>{{ session('gagal') }}</strong></p>
    @endif

    <h2>Tambah Inventaris</h2>

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $pesan)
                <li>{{ $pesan }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="/inventaris">
        @csrf

        <p>
            <label for="kode_barang">Kode Barang (10 karakter)</label><br>
            <input type="text" id="kode_barang" name="kode_barang"
                   value="{{ old('kode_barang') }}">
        </p>

        <p>
            <label for="nama_barang">Nama Barang</label><br>
            <input type="text" id="nama_barang" name="nama_barang"
                   value="{{ old('nama_barang') }}">
        </p>

        <p>
            <label for="nomor_aset">Nomor Aset</label><br>
            <input type="text" id="nomor_aset" name="nomor_aset"
                   value="{{ old('nomor_aset') }}">
        </p>

        <p>
            <label for="tahun_perolehan">Tahun Perolehan (2000-2026)</label><br>
            <input type="text" id="tahun_perolehan" name="tahun_perolehan"
                   value="{{ old('tahun_perolehan') }}">
        </p>

        <p>
            <label for="ruang_id">Ruang</label><br>
            <select id="ruang_id" name="ruang_id">
                <option value="">-- Pilih Ruang --</option>
                @foreach ($daftarRuang as $ruang)
                    <option value="{{ $ruang['id'] }}"
                        {{ old('ruang_id') == $ruang['id'] ? 'selected' : '' }}>
                        {{ $ruang['nama_ruang'] }}
                    </option>
                @endforeach
            </select>
        </p>

        <button type="submit">Simpan</button>
    </form>

    <h2>Cari dan Filter</h2>

    <form method="GET" action="/inventaris">
        <input type="text" name="q" value="{{ $q }}"
               placeholder="Kode / nama / nomor aset">

        <select name="ruang_id">
            <option value="">Semua Ruang</option>
            @foreach ($daftarRuang as $ruang)
                <option value="{{ $ruang['id'] }}"
                    {{ (string) $ruangId === (string) $ruang['id'] ? 'selected' : '' }}>
                    {{ $ruang['nama_ruang'] }}
                </option>
            @endforeach
        </select>

        <button type="submit">Cari</button>
        <a href="/inventaris">Reset</a>
    </form>

    <h2>Daftar Inventaris</h2>

    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode</th>
                <th>Nama Barang</th>
                <th>Nomor Aset</th>
                <th>Tahun</th>
                <th>Status Usia</th>
                <th>Ruang</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($daftarInventaris as $index => $barang)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $barang['kode_barang'] }}</td>
                    <td>{{ $barang['nama_barang'] }}</td>
                    <td>{{ $barang['nomor_aset'] }}</td>
                    <td>{{ $barang['tahun_perolehan'] }}</td>
                    <td>
                        @if (statusUsiaAset($barang['tahun_perolehan']) === 'Baru')
                            Baru
                        @else
                            Lama
                        @endif
                    </td>
                    <td>{{ $barang['nama_ruang'] }}</td>
                    <td>
                        <a href="/inventaris/{{ $barang['id'] }}/edit">Edit</a>

                        <form method="POST" action="/inventaris/{{ $barang['id'] }}"
                              style="display:inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">Data inventaris tidak ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>