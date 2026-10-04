SELECT m.npm,p.nama periode,mk.kode,k.kode kelas,c.nama komponen,c.bobot,n.nilai FROM mahasiswa m JOIN krs r ON r.mahasiswa_id=m.id JOIN periode p ON p.id=r.periode_id JOIN krs_detail d ON d.krs_id=r.id JOIN kelas k ON k.id=d.kelas_id JOIN matakuliah mk ON mk.id=k.matakuliah_id JOIN komponen_nilai c ON c.kelas_id=k.id LEFT JOIN nilai_komponen n ON n.krs_detail_id=d.id AND n.komponen_nilai_id=c.id WHERE m.npm='260820701100010' AND r.status='disetujui' ORDER BY p.tahun_mulai,k.id,c.id;

SELECT m.npm,p.nama periode,SUM(mk.sks) sks FROM krs r JOIN mahasiswa m ON m.id=r.mahasiswa_id JOIN periode p ON p.id=r.periode_id JOIN krs_detail d ON d.krs_id=r.id JOIN kelas k ON k.id=d.kelas_id JOIN matakuliah mk ON mk.id=k.matakuliah_id WHERE m.npm='260820701100010' GROUP BY m.npm,p.nama ORDER BY p.nama;

SELECT (SELECT COUNT(*) FROM krs_detail d LEFT JOIN krs r ON r.id=d.krs_id WHERE r.id IS NULL)+(SELECT COUNT(*) FROM nilai_komponen n LEFT JOIN krs_detail d ON d.id=n.krs_detail_id WHERE d.id IS NULL)+(SELECT COUNT(*) FROM presensi a LEFT JOIN pertemuan p ON p.id=a.pertemuan_id WHERE p.id IS NULL) total_yatim;

SELECT (SELECT COUNT(*) FROM krs_detail d JOIN krs r ON r.id=d.krs_id JOIN kelas k ON k.id=d.kelas_id WHERE r.periode_id<>k.periode_id)+(SELECT COUNT(*) FROM nilai_komponen n JOIN krs_detail d ON d.id=n.krs_detail_id JOIN komponen_nilai c ON c.id=n.komponen_nilai_id WHERE d.kelas_id<>c.kelas_id)+(SELECT COUNT(*) FROM presensi a JOIN krs_detail d ON d.id=a.krs_detail_id JOIN pertemuan p ON p.id=a.pertemuan_id WHERE d.kelas_id<>p.kelas_id) tidak_sesuai;

-- Demonstrasi UPDATE/DELETE yang aman dalam transaksi
START TRANSACTION;
INSERT INTO ruang(kode,nama,kapasitas) VALUES('SQL-TEST','Ruang uji SQL',10);
UPDATE ruang SET kapasitas=12 WHERE kode='SQL-TEST';
SELECT kode,kapasitas FROM ruang WHERE kode='SQL-TEST';
DELETE FROM ruang WHERE kode='SQL-TEST';
ROLLBACK;
