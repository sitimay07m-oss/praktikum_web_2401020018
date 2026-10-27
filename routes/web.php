<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

// ===== PERTEMUAN 1 =====

Route::get('/', function () {
    return view('welcome');
});

// ===== PERTEMUAN 2 =====

Route::get('/latihan-php', function () {
    $nama = 'Siti Umayah';
    $nilai = [80, 75, 90, 65, 88];

    $hitungRataRata = function (array $data): float {
        $total = 0;

        foreach ($data as $angka) {
            $total += $angka;
        }

        return $total / count($data);
    };

    $rataRata = $hitungRataRata($nilai);
    $status = $rataRata >= 75 ? 'Lulus' : 'Perlu Perbaikan';

    return view('latihan-php', compact(
        'nama', 'nilai', 'rataRata', 'status'
    ));
});

// ===== PERTEMUAN 3 =====

Route::get('/form-mahasiswa', function () {
    return view('form-mahasiswa');
});

Route::post('/form-mahasiswa', function (Request $request) {
    $dataBersih = [
        'nama' => strip_tags(trim((string) $request->input('nama'))),
        'email' => filter_var(
            (string) $request->input('email'),
            FILTER_SANITIZE_EMAIL
        ),
        'usia' => trim((string) $request->input('usia')),
        'nim' => trim((string) $request->input('nim')),
    ];

    $validator = Validator::make($dataBersih, [
        'nama' => ['required', 'min:3', 'max:50'],
        'email' => ['required', 'email'],
        'usia' => ['required', 'integer', 'min:17', 'max:60'],
        'nim' => ['required', 'digits_between:8,12'],
    ], [
        'nama.required' => 'Nama wajib diisi.',
        'nama.min' => 'Nama minimal 3 karakter.',
        'email.required' => 'Email wajib diisi.',
        'email.email' => 'Format email tidak valid.',
        'usia.required' => 'Usia wajib diisi.',
        'usia.integer' => 'Usia harus berupa angka.',
        'usia.min' => 'Usia minimal 17 tahun.',
        'usia.max' => 'Usia maksimal 60 tahun.',
        'nim.required' => 'NIM wajib diisi.',
        'nim.digits_between' => 'NIM harus berupa angka 8-12 digit.',
    ]);

    if ($validator->fails()) {
        return redirect('/form-mahasiswa')
            ->withErrors($validator)
            ->withInput();
    }

    $data = $validator->validated();
    $data['usia'] = (int) $data['usia'];

    return view('hasil-form', ['data' => $data]);
});

// ===== PERTEMUAN 5, 6, DAN 7 =====

// READ + SEARCH + FILTER
Route::get('/mahasiswa', function (Request $request) {
    try {
        $pdo = DB::connection()->getPdo();

        $q = trim((string) $request->query('q', ''));
        $programStudiId = $request->query('program_studi_id', '');

        $sql = "
            SELECT m.id, m.nim, m.nama, m.email, m.usia,
                   m.program_studi_id, p.nama_prodi
            FROM mahasiswa AS m
            JOIN program_studi AS p
                ON p.id = m.program_studi_id
            WHERE 1 = 1
        ";

        $params = [];

        if ($q !== '') {
            $sql .= "
                AND (
                    m.nim LIKE :q_nim
                    OR m.nama LIKE :q_nama
                    OR m.email LIKE :q_email
                )
            ";

            $kataKunci = '%' . $q . '%';

            $params['q_nim'] = $kataKunci;
            $params['q_nama'] = $kataKunci;
            $params['q_email'] = $kataKunci;
        }

        if (
            $programStudiId !== ''
            && ctype_digit((string) $programStudiId)
        ) {
            $sql .= " AND m.program_studi_id = :prodi_id";
            $params['prodi_id'] = (int) $programStudiId;
        } else {
            $programStudiId = '';
        }

        $sql .= " ORDER BY m.nim";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $daftarMahasiswa = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $stmtProdi = $pdo->prepare("
            SELECT id, nama_prodi
            FROM program_studi
            ORDER BY nama_prodi
        ");

        $stmtProdi->execute();
        $daftarProgramStudi = $stmtProdi->fetchAll(\PDO::FETCH_ASSOC);

        return view('mahasiswa', compact(
            'daftarMahasiswa',
            'daftarProgramStudi',
            'q',
            'programStudiId'
        ));

    } catch (\Throwable $e) {
        report($e);

        return response(
            'Data tidak dapat dibaca. Periksa koneksi dan query.',
            500
        );
    }
})->name('mahasiswa.index');


// CREATE
Route::post('/mahasiswa', function (Request $request) {
    $data = $request->validate([
        'nim' => ['required', 'string', 'max:10'],
        'nama' => ['required', 'string', 'max:100'],
        'email' => ['required', 'email', 'max:100'],
        'usia' => ['required', 'integer', 'min:15', 'max:100'],
        'program_studi_id' => [
            'required', 'integer', 'exists:program_studi,id',
        ],
    ]);

    try {
        $pdo = DB::connection()->getPdo();

        $cek = $pdo->prepare("
            SELECT COUNT(*)
            FROM mahasiswa
            WHERE nim = :nim OR email = :email
        ");

        $cek->execute([
            'nim' => $data['nim'],
            'email' => $data['email'],
        ]);

        if ((int) $cek->fetchColumn() > 0) {
            return back()->withInput()->with(
                'gagal',
                'NIM atau email sudah digunakan.'
            );
        }

        $simpan = $pdo->prepare("
            INSERT INTO mahasiswa
                (nim, nama, email, usia, program_studi_id)
            VALUES
                (:nim, :nama, :email, :usia, :program_studi_id)
        ");

        $simpan->execute([
            'nim' => $data['nim'],
            'nama' => $data['nama'],
            'email' => $data['email'],
            'usia' => $data['usia'],
            'program_studi_id' => $data['program_studi_id'],
        ]);

        return redirect()
            ->route('mahasiswa.index')
            ->with('berhasil', 'Data mahasiswa berhasil ditambahkan.');

    } catch (\Throwable $e) {
        report($e);

        return back()->withInput()->with(
            'gagal',
            'Data gagal disimpan. Periksa koneksi dan data.'
        );
    }
})->name('mahasiswa.store');


// FORM UPDATE: mengambil data mahasiswa berdasarkan ID
Route::get('/mahasiswa/{id}/edit', function ($id) {
    try {
        $pdo = DB::connection()->getPdo();

        $stmt = $pdo->prepare("
            SELECT id, nim, nama, email, usia, program_studi_id
            FROM mahasiswa
            WHERE id = :id
        ");

        $stmt->execute(['id' => $id]);
        $mahasiswa = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$mahasiswa) {
            return redirect()
                ->route('mahasiswa.index')
                ->with('gagal', 'Data mahasiswa tidak ditemukan.');
        }

        $stmtProdi = $pdo->prepare("
            SELECT id, nama_prodi
            FROM program_studi
            ORDER BY nama_prodi
        ");

        $stmtProdi->execute();
        $daftarProgramStudi = $stmtProdi->fetchAll(\PDO::FETCH_ASSOC);

        return view('mahasiswa-edit', compact(
            'mahasiswa', 'daftarProgramStudi'
        ));

    } catch (\Throwable $e) {
        report($e);

        return redirect()
            ->route('mahasiswa.index')
            ->with('gagal', 'Data edit gagal dibaca.');
    }
})->whereNumber('id')->name('mahasiswa.edit');


// UPDATE
Route::put('/mahasiswa/{id}', function (Request $request, $id) {
    $data = $request->validate([
        'nim' => ['required', 'string', 'max:10'],
        'nama' => ['required', 'string', 'max:100'],
        'email' => ['required', 'email', 'max:100'],
        'usia' => ['required', 'integer', 'min:15', 'max:100'],
        'program_studi_id' => [
            'required', 'integer', 'exists:program_studi,id',
        ],
    ]);

    try {
        $pdo = DB::connection()->getPdo();

        $cekData = $pdo->prepare("
            SELECT id
            FROM mahasiswa
            WHERE id = :id
        ");
        $cekData->execute(['id' => $id]);

        if (!$cekData->fetchColumn()) {
            return redirect()
                ->route('mahasiswa.index')
                ->with('gagal', 'Data mahasiswa tidak ditemukan.');
        }

        $cekDuplikat = $pdo->prepare("
            SELECT COUNT(*)
            FROM mahasiswa
            WHERE (nim = :nim OR email = :email)
              AND id <> :id
        ");

        $cekDuplikat->execute([
            'nim' => $data['nim'],
            'email' => $data['email'],
            'id' => $id,
        ]);

        if ((int) $cekDuplikat->fetchColumn() > 0) {
            return back()->withInput()->with(
                'gagal',
                'NIM atau email sudah digunakan mahasiswa lain.'
            );
        }

        $update = $pdo->prepare("
            UPDATE mahasiswa
            SET nim = :nim,
                nama = :nama,
                email = :email,
                usia = :usia,
                program_studi_id = :program_studi_id,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");

        $update->execute([
            'nim' => $data['nim'],
            'nama' => $data['nama'],
            'email' => $data['email'],
            'usia' => $data['usia'],
            'program_studi_id' => $data['program_studi_id'],
            'id' => $id,
        ]);

        return redirect()
            ->route('mahasiswa.index')
            ->with('berhasil', 'Data mahasiswa berhasil diperbarui.');

    } catch (\Throwable $e) {
        report($e);

        return back()->withInput()->with(
            'gagal',
            'Data gagal diperbarui. Periksa koneksi dan data.'
        );
    }
})->whereNumber('id')->name('mahasiswa.update');


// DELETE
Route::delete('/mahasiswa/{id}', function ($id) {
    try {
        $pdo = DB::connection()->getPdo();

        $hapus = $pdo->prepare("
            DELETE FROM mahasiswa
            WHERE id = :id
        ");

        $hapus->execute(['id' => $id]);

        if ($hapus->rowCount() === 0) {
            return redirect()
                ->route('mahasiswa.index')
                ->with('gagal', 'Data tidak ditemukan atau sudah dihapus.');
        }

        return redirect()
            ->route('mahasiswa.index')
            ->with('berhasil', 'Data mahasiswa berhasil dihapus.');

    } catch (\Throwable $e) {
        report($e);

        return redirect()
            ->route('mahasiswa.index')
            ->with(
                'gagal',
                'Data gagal dihapus. Periksa relasi dan koneksi database.'
            );
    }
})->whereNumber('id')->name('mahasiswa.destroy');


// READ: mencari mahasiswa berdasarkan NIM (fitur lama)
Route::get('/mahasiswa/{nim}', function ($nim) {
    try {
        $pdo = DB::connection()->getPdo();

        $stmt = $pdo->prepare("
            SELECT m.id, m.nim, m.nama, m.email, m.usia,
                   m.program_studi_id, p.nama_prodi
            FROM mahasiswa AS m
            JOIN program_studi AS p
                ON p.id = m.program_studi_id
            WHERE m.nim = :nim
        ");

        $stmt->execute(['nim' => $nim]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$data) {
            return response('Data mahasiswa tidak ditemukan.', 404);
        }

        $stmtProdi = $pdo->prepare("
            SELECT id, nama_prodi
            FROM program_studi
            ORDER BY nama_prodi
        ");

        $stmtProdi->execute();
        $daftarProgramStudi = $stmtProdi->fetchAll(\PDO::FETCH_ASSOC);

        return view('mahasiswa', [
            'daftarMahasiswa' => [$data],
            'daftarProgramStudi' => $daftarProgramStudi,
            'q' => '',
            'programStudiId' => '',
        ]);

    } catch (\Throwable $e) {
        report($e);

        return response(
            'Data tidak dapat dibaca. Periksa koneksi dan query.',
            500
        );
    }
})->where('nim', '[0-9]+');

// =====================================================================
// UTS TIPE B: SISTEM INVENTARIS LABORATORIUM (MODUL 1-7)
// =====================================================================

// Fungsi sederhana (Modul 2): status usia aset
if (!function_exists('statusUsiaAset')) {
    function statusUsiaAset($tahun): string
    {
        if ((int) $tahun >= 2023) {
            return 'Baru';
        }

        return 'Lama';
    }
}

// Sanitasi input inventaris: trim + strip_tags
if (!function_exists('bersihkanInventaris')) {
    function bersihkanInventaris(Request $request): array
    {
        return [
            'kode_barang' => strip_tags(trim((string) $request->input('kode_barang'))),
            'nama_barang' => strip_tags(trim((string) $request->input('nama_barang'))),
            'nomor_aset' => strip_tags(trim((string) $request->input('nomor_aset'))),
            'tahun_perolehan' => trim((string) $request->input('tahun_perolehan')),
            'ruang_id' => trim((string) $request->input('ruang_id')),
        ];
    }
}

// Aturan validasi server-side
if (!function_exists('aturanInventaris')) {
    function aturanInventaris(): array
    {
        return [
            'kode_barang' => ['required', 'regex:/^[A-Za-z0-9]{10}$/'],
            'nama_barang' => ['required', 'min:3', 'max:100'],
            'nomor_aset' => ['required', 'min:5', 'max:30'],
            'tahun_perolehan' => ['required', 'integer', 'min:2000', 'max:2026'],
            'ruang_id' => ['required', 'integer', 'exists:ruang,id'],
        ];
    }
}

// Pesan kesalahan dalam bahasa Indonesia
if (!function_exists('pesanInventaris')) {
    function pesanInventaris(): array
    {
        return [
            'kode_barang.required' => 'Kode barang wajib diisi.',
            'kode_barang.regex' => 'Kode barang harus tepat 10 karakter alfanumerik.',
            'nama_barang.required' => 'Nama barang wajib diisi.',
            'nama_barang.min' => 'Nama barang minimal 3 karakter.',
            'nama_barang.max' => 'Nama barang maksimal 100 karakter.',
            'nomor_aset.required' => 'Nomor aset wajib diisi.',
            'nomor_aset.min' => 'Nomor aset minimal 5 karakter.',
            'nomor_aset.max' => 'Nomor aset maksimal 30 karakter.',
            'tahun_perolehan.required' => 'Tahun perolehan wajib diisi.',
            'tahun_perolehan.integer' => 'Tahun perolehan harus berupa angka.',
            'tahun_perolehan.min' => 'Tahun perolehan minimal 2000.',
            'tahun_perolehan.max' => 'Tahun perolehan maksimal 2026.',
            'ruang_id.required' => 'Ruang wajib dipilih.',
            'ruang_id.integer' => 'Ruang tidak valid.',
            'ruang_id.exists' => 'Ruang tidak tersedia.',
        ];
    }
}

// READ + SEARCH + FILTER
Route::get('/inventaris', function (Request $request) {
    try {
        $pdo = DB::connection()->getPdo();

        $q = strip_tags(trim((string) $request->query('q', '')));
        $ruangId = $request->query('ruang_id', '');

        $sql = "
            SELECT i.id, i.kode_barang, i.nama_barang, i.nomor_aset,
                   i.tahun_perolehan, i.ruang_id, r.nama_ruang
            FROM inventaris AS i
            JOIN ruang AS r
                ON r.id = i.ruang_id
            WHERE 1 = 1
        ";

        $params = [];

        if ($q !== '') {
            $sql .= "
                AND (
                    i.kode_barang LIKE :q_kode
                    OR i.nama_barang LIKE :q_nama
                    OR i.nomor_aset LIKE :q_aset
                )
            ";

            $kataKunci = '%' . $q . '%';

            $params['q_kode'] = $kataKunci;
            $params['q_nama'] = $kataKunci;
            $params['q_aset'] = $kataKunci;
        }

        if ($ruangId !== '' && ctype_digit((string) $ruangId)) {
            $sql .= " AND i.ruang_id = :ruang_id";
            $params['ruang_id'] = (int) $ruangId;
        } else {
            $ruangId = '';
        }

        $sql .= " ORDER BY i.id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $daftarInventaris = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $stmtRuang = $pdo->prepare("
            SELECT id, nama_ruang
            FROM ruang
            ORDER BY nama_ruang
        ");
        $stmtRuang->execute();
        $daftarRuang = $stmtRuang->fetchAll(\PDO::FETCH_ASSOC);

        return view('inventaris', compact(
            'daftarInventaris',
            'daftarRuang',
            'q',
            'ruangId'
        ));

    } catch (\Throwable $e) {
        report($e);

        return response(
            'Data tidak dapat dibaca. Periksa koneksi dan query.',
            500
        );
    }
})->name('inventaris.index');


// CREATE
Route::post('/inventaris', function (Request $request) {
    $dataBersih = bersihkanInventaris($request);

    $validator = Validator::make($dataBersih, aturanInventaris(), pesanInventaris());

    if ($validator->fails()) {
        return redirect()
            ->route('inventaris.index')
            ->withErrors($validator)
            ->withInput($dataBersih);
    }

    $data = $validator->validated();

    try {
        $pdo = DB::connection()->getPdo();

        $cek = $pdo->prepare("
            SELECT COUNT(*)
            FROM inventaris
            WHERE kode_barang = :kode_barang OR nomor_aset = :nomor_aset
        ");

        $cek->execute([
            'kode_barang' => $data['kode_barang'],
            'nomor_aset' => $data['nomor_aset'],
        ]);

        if ((int) $cek->fetchColumn() > 0) {
            return redirect()
                ->route('inventaris.index')
                ->withInput($dataBersih)
                ->with('gagal', 'Kode barang atau nomor aset sudah digunakan.');
        }

        $simpan = $pdo->prepare("
            INSERT INTO inventaris
                (kode_barang, nama_barang, nomor_aset, tahun_perolehan,
                 ruang_id, created_at, updated_at)
            VALUES
                (:kode_barang, :nama_barang, :nomor_aset, :tahun_perolehan,
                 :ruang_id, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
        ");

        $simpan->execute([
            'kode_barang' => $data['kode_barang'],
            'nama_barang' => $data['nama_barang'],
            'nomor_aset' => $data['nomor_aset'],
            'tahun_perolehan' => (int) $data['tahun_perolehan'],
            'ruang_id' => (int) $data['ruang_id'],
        ]);

        return redirect()
            ->route('inventaris.index')
            ->with('berhasil', 'Data inventaris berhasil ditambahkan.');

    } catch (\Throwable $e) {
        report($e);

        return redirect()
            ->route('inventaris.index')
            ->withInput($dataBersih)
            ->with('gagal', 'Data gagal disimpan. Periksa koneksi dan data.');
    }
})->name('inventaris.store');


// FORM UPDATE: mengambil data inventaris berdasarkan ID
Route::get('/inventaris/{id}/edit', function ($id) {
    try {
        $pdo = DB::connection()->getPdo();

        $stmt = $pdo->prepare("
            SELECT id, kode_barang, nama_barang, nomor_aset,
                   tahun_perolehan, ruang_id
            FROM inventaris
            WHERE id = :id
        ");

        $stmt->execute(['id' => $id]);
        $inventaris = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$inventaris) {
            return redirect()
                ->route('inventaris.index')
                ->with('gagal', 'Data inventaris tidak ditemukan.');
        }

        $stmtRuang = $pdo->prepare("
            SELECT id, nama_ruang
            FROM ruang
            ORDER BY nama_ruang
        ");
        $stmtRuang->execute();
        $daftarRuang = $stmtRuang->fetchAll(\PDO::FETCH_ASSOC);

        return view('inventaris-edit', compact('inventaris', 'daftarRuang'));

    } catch (\Throwable $e) {
        report($e);

        return redirect()
            ->route('inventaris.index')
            ->with('gagal', 'Data edit gagal dibaca.');
    }
})->whereNumber('id')->name('inventaris.edit');


// UPDATE
Route::put('/inventaris/{id}', function (Request $request, $id) {
    $dataBersih = bersihkanInventaris($request);

    $validator = Validator::make($dataBersih, aturanInventaris(), pesanInventaris());

    if ($validator->fails()) {
        return redirect()
            ->route('inventaris.edit', $id)
            ->withErrors($validator)
            ->withInput($dataBersih);
    }

    $data = $validator->validated();

    try {
        $pdo = DB::connection()->getPdo();

        $cekData = $pdo->prepare("
            SELECT id
            FROM inventaris
            WHERE id = :id
        ");
        $cekData->execute(['id' => $id]);

        if (!$cekData->fetchColumn()) {
            return redirect()
                ->route('inventaris.index')
                ->with('gagal', 'Data inventaris tidak ditemukan.');
        }

        $cekDuplikat = $pdo->prepare("
            SELECT COUNT(*)
            FROM inventaris
            WHERE (kode_barang = :kode_barang OR nomor_aset = :nomor_aset)
              AND id <> :id
        ");

        $cekDuplikat->execute([
            'kode_barang' => $data['kode_barang'],
            'nomor_aset' => $data['nomor_aset'],
            'id' => $id,
        ]);

        if ((int) $cekDuplikat->fetchColumn() > 0) {
            return redirect()
                ->route('inventaris.edit', $id)
                ->withInput($dataBersih)
                ->with('gagal', 'Kode barang atau nomor aset sudah digunakan barang lain.');
        }

        $update = $pdo->prepare("
            UPDATE inventaris
            SET kode_barang = :kode_barang,
                nama_barang = :nama_barang,
                nomor_aset = :nomor_aset,
                tahun_perolehan = :tahun_perolehan,
                ruang_id = :ruang_id,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");

        $update->execute([
            'kode_barang' => $data['kode_barang'],
            'nama_barang' => $data['nama_barang'],
            'nomor_aset' => $data['nomor_aset'],
            'tahun_perolehan' => (int) $data['tahun_perolehan'],
            'ruang_id' => (int) $data['ruang_id'],
            'id' => $id,
        ]);

        return redirect()
            ->route('inventaris.index')
            ->with('berhasil', 'Data inventaris berhasil diperbarui.');

    } catch (\Throwable $e) {
        report($e);

        return redirect()
            ->route('inventaris.edit', $id)
            ->withInput($dataBersih)
            ->with('gagal', 'Data gagal diperbarui. Periksa koneksi dan data.');
    }
})->whereNumber('id')->name('inventaris.update');


// DELETE
Route::delete('/inventaris/{id}', function ($id) {
    try {
        $pdo = DB::connection()->getPdo();

        $hapus = $pdo->prepare("
            DELETE FROM inventaris
            WHERE id = :id
        ");

        $hapus->execute(['id' => $id]);

        if ($hapus->rowCount() === 0) {
            return redirect()
                ->route('inventaris.index')
                ->with('gagal', 'Data tidak ditemukan atau sudah dihapus.');
        }

        return redirect()
            ->route('inventaris.index')
            ->with('berhasil', 'Data inventaris berhasil dihapus.');

    } catch (\Throwable $e) {
        report($e);

        return redirect()
            ->route('inventaris.index')
            ->with('gagal', 'Data gagal dihapus. Periksa relasi dan koneksi database.');
    }
})->whereNumber('id')->name('inventaris.destroy');