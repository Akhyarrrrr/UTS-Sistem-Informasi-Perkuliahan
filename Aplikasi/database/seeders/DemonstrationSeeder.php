<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemonstrationSeeder extends Seeder
{
    public function run(): void
    {
        if(DB::table('matakuliah')->where('kode','PDB308')->exists())return;
        DB::transaction(function(){
            $course=DB::table('matakuliah')->insertGetId(['kode'=>'PDB308','nama'=>'Praktikum Basis Data','prodi_id'=>1,'sks'=>2]);
            $class=DB::table('kelas')->insertGetId(['matakuliah_id'=>$course,'periode_id'=>2,'dosen_id'=>1,'kode'=>'A','kapasitas'=>12,'published_at'=>now(),'first_published_at'=>now()]);
            DB::table('jadwal')->insert(['kelas_id'=>$class,'ruang_id'=>2,'hari'=>5,'mulai'=>'13:00','selesai'=>'14:40','mode'=>'Luring']);
            $krs=DB::table('krs')->insertGetId(['mahasiswa_id'=>11,'periode_id'=>2,'status'=>'disetujui','created_at'=>now(),'updated_at'=>now()]);
            $detail=DB::table('krs_detail')->insertGetId(['krs_id'=>$krs,'kelas_id'=>$class]);
            foreach([['Tugas',20,80],['Kuis',10,90],['UTS',30,85],['UAS',40,95]] as [$label,$weight,$value]){
                $component=DB::table('komponen_nilai')->insertGetId(['kelas_id'=>$class,'nama'=>$label,'bobot'=>$weight]);
                DB::table('nilai_komponen')->insert(['krs_detail_id'=>$detail,'komponen_nilai_id'=>$component,'nilai'=>$value]);
            }
            $meeting=DB::table('pertemuan')->insertGetId(['kelas_id'=>$class,'nomor'=>1,'tanggal'=>today(),'topik'=>'Perancangan tabel dan integritas referensial']);
            DB::table('presensi')->insert(['pertemuan_id'=>$meeting,'krs_detail_id'=>$detail,'status'=>'Hadir']);
            DB::table('activity_log')->insert(['user_id'=>1,'aksi'=>'Siapkan contoh demonstrasi','entitas'=>'sistem','detail'=>json_encode(['kode'=>'PDB308','sumber'=>'Seeder reproduksi hasil demonstrasi browser']),'created_at'=>now()]);
        });
    }
}
