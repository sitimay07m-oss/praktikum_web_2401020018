CREATE DATABASE IF NOT EXISTS inventaris_lab_2401020018;
USE inventaris_lab_2401020018;

DROP TABLE IF EXISTS inventaris;
DROP TABLE IF EXISTS ruang;

CREATE TABLE ruang (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_ruang VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE inventaris (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode_barang CHAR(10) NOT NULL UNIQUE,
    nama_barang VARCHAR(100) NOT NULL,
    nomor_aset VARCHAR(30) NOT NULL UNIQUE,
    tahun_perolehan SMALLINT UNSIGNED NOT NULL,
    ruang_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT chk_tahun_perolehan CHECK (tahun_perolehan BETWEEN 2000 AND 2026),
    CONSTRAINT fk_inventaris_ruang
        FOREIGN KEY (ruang_id)
        REFERENCES ruang(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

INSERT INTO ruang (nama_ruang) VALUES
('Lab Pemrograman'),
('Lab Jaringan');

INSERT INTO inventaris (kode_barang, nama_barang, nomor_aset, tahun_perolehan, ruang_id, created_at, updated_at) VALUES
('IV00000001', 'Komputer Praktikum', 'AST-LAB-0001', 2023, 1, NOW(), NOW()),
('IV00000002', 'Proyektor',          'AST-LAB-0002', 2022, 1, NOW(), NOW()),
('IV00000003', 'Router',             'AST-LAB-0003', 2024, 2, NOW(), NOW());
