<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

// ===== Route Pertemuan 1 =====

Route::get('/', function () {
    return view('welcome');
});

// ===== Route Pertemuan 2 =====

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

    if ($rataRata >= 75) {
        $status = 'Lulus';
    } else {
        $status = 'Perlu Perbaikan';
    }

    return view('latihan-php', compact(
        'nama',
        'nilai',
        'rataRata',
        'status'
    ));
});

// ===== Route Pertemuan 3 =====

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

// ===== Route Pertemuan 5 =====

Route::get('/mahasiswa', function () {

    try {
        $pdo = DB::connection()->getPdo();

        $stmt = $pdo->prepare("
            SELECT
                m.nim,
                m.nama,
                m.email,
                m.usia,
                p.nama_prodi
            FROM mahasiswa AS m
            JOIN program_studi AS p
                ON p.id = m.program_studi_id
            ORDER BY m.nim
        ");

        $stmt->execute();

        $mahasiswa = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return view('mahasiswa', compact('mahasiswa'));

    } catch (\Throwable $e) {

        return response(
            'Koneksi atau query database gagal: ' . $e->getMessage(),
            500
        );
    }
});

Route::get('/mahasiswa/{nim}', function ($nim) {

    try {
        $pdo = DB::connection()->getPdo();

        $stmt = $pdo->prepare("
            SELECT
                m.nim,
                m.nama,
                m.email,
                m.usia,
                p.nama_prodi
            FROM mahasiswa AS m
            JOIN program_studi AS p
                ON p.id = m.program_studi_id
            WHERE m.nim = ?
        ");

        $stmt->execute([$nim]);

        $mahasiswa = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$mahasiswa) {
            return response(
                'Data mahasiswa tidak ditemukan.',
                404
            );
        }

        return view('mahasiswa', compact('mahasiswa'));

    } catch (\Throwable $e) {

        return response(
            'Koneksi atau query database gagal: ' . $e->getMessage(),
            500
        );
    }
});