
USE praktikum_web_2401020018;

INSERT INTO program_studi (nama_prodi) VALUES
('Teknik Informatika'),
('Sistem Informasi');

INSERT INTO mahasiswa
(nim, nama, email, usia, program_studi_id)
VALUES
('2401020018', 'Siti Umayah', 'siti.umayah@example.com', 21, 1),
('2401020023', 'Farael Ahmad', 'farael.ahmad@example.com', 20, 1),
('2401020029', 'Anika Putri', 'anika.putri@example.com', 20, 2),
('2401020099', 'Mahasiswa Sementara', 'sementara@example.com', 18, 2);

UPDATE mahasiswa
SET email = 'siti.umayah@kampus.ac.id'
WHERE nim = '2401020018';

DELETE FROM mahasiswa
WHERE nim = '2401020099';

SELECT m.nim, m.nama, m.email, m.usia, p.nama_prodi
FROM mahasiswa AS m
JOIN program_studi AS p
ON p.id = m.program_studi_id
ORDER BY m.nim;