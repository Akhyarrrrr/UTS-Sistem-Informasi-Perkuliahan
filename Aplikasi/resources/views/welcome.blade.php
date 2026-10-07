<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="description" content="Sistem Informasi Perkuliahan karya Akhyar: dari rencana studi hingga hasil perkuliahan, dengan basis data relasional dan tiga ruang kerja akademik.">
    <meta name="theme-color" content="#143d2e">
    <title>Perkuliahan · Rencana bertemu hasil</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @include('partials.theme-init')
    @vite(['resources/css/app.css','resources/css/home.css','resources/js/app.js'])
</head>
<body class="home-body">
<a class="skip-link" href="#main">Lewati navigasi</a>
<header class="home-header">
    <a class="identity" href="{{ route('home') }}"><span class="identity-mark">P</span><span>Perkuliahan<small>Sistem informasi akademik</small></span></a>
    <button class="home-menu-button text-button icon-action" type="button" aria-expanded="false" aria-controls="home-navigation" aria-label="Menu" title="Menu"><x-icon name="menu"/><span class="control-label sr-only">Menu</span></button>
    <nav id="home-navigation" aria-label="Navigasi halaman pengenalan">
        <a href="#alur">Alur studi</a><a href="#peran">Ruang kerja</a><a href="#data">Relasi data</a><a href="#bukti">Bukti UTS</a>
    </nav>
    <div class="home-header-actions"><button type="button" class="theme-button icon-action" aria-label="Tema gelap" title="Tema gelap"><x-icon name="moon" class="theme-moon"/><x-icon name="sun" class="theme-sun"/><span class="control-label sr-only">Tema gelap</span></button><a class="button primary icon-action" href="{{ auth()->check()?route('dashboard'):route('login') }}" aria-label="{{ auth()->check()?'Buka ruang kerja':'Masuk sistem' }}" title="{{ auth()->check()?'Buka ruang kerja':'Masuk sistem' }}"><x-icon name="login"/><span class="control-label sr-only">{{ auth()->check()?'Buka ruang kerja':'Masuk sistem' }}</span></a></div>
</header>
<main id="main" tabindex="-1">
    <section class="home-hero" aria-labelledby="hero-title">
        <div class="hero-copy">
            <p class="home-kicker">UTS Manajemen dan Pemodelan Data / Kelas A</p>
            <h1 id="hero-title">Rencana<br>bertemu <em>hasil.</em></h1>
            <p class="hero-lead">Satu alur untuk memilih kelas, mencatat perkuliahan, dan melihat hasil studi. Rencana, proses, dan hasil terhubung dalam satu catatan akademik.</p>
            <div class="hero-actions"><a class="button primary icon-action" href="#alur" aria-label="Ikuti alur perkuliahan" title="Ikuti alur perkuliahan"><x-icon name="relations"/><span class="control-label sr-only">Ikuti alur perkuliahan</span></a><a class="home-text-link" href="#jalankan">Jalankan di komputer saya</a></div>
            <div class="hero-credit"><span>Dirancang dan disusun oleh</span><strong>Akhyar</strong><span>260820701100010 · Universitas Syiah Kuala</span></div>
        </div>
        <div class="hero-study" data-reveal>
            <div class="study-caption"><span>Rekam studi / PDB308</span><span class="home-simulation">Data simulasi</span></div>
            <div class="study-record"><span class="record-code">PDB308 / A</span><h2>Praktikum Basis Data</h2><p>Raka Wijaya · 2026/2027 Ganjil</p></div>
            <div class="study-orbit" role="img" aria-label="Nilai akhir 88,50 dari 100, grade A. KRS disetujui dan presensi Hadir.">
                <svg viewBox="0 0 240 240" aria-hidden="true"><circle class="orbit-track" cx="120" cy="120" r="84"/><circle class="orbit-score" cx="120" cy="120" r="84" stroke-dasharray="467.09 527.79"/><circle class="orbit-core" cx="120" cy="120" r="62"/><path class="orbit-axis" d="M4 120h22m188 0h22M120 4v22m0 188v22"/></svg>
                <div class="orbit-grade"><span>Grade terbit</span><strong>A</strong><span>88,50 / 100</span></div>
                <span class="orbit-status orbit-krs"><x-icon name="check"/>KRS disetujui</span><span class="orbit-status orbit-attendance"><x-icon name="calendar"/>Hadir</span>
            </div>
            <div class="component-bars" aria-label="Nilai komponen dan bobot">
                @foreach([['Tugas',80,20],['Kuis',90,10],['UTS',85,30],['UAS',95,40]] as $component)
                <div><span>{{ $component[0] }} <small>{{ $component[2] }}%</small></span><span class="score-track"><span style="width:{{ $component[1] }}%"></span></span><strong>{{ $component[1] }}</strong></div>
                @endforeach
            </div>
            <div class="record-result"><span>Hasil penilaian terbit</span><strong>88,50<small>A</small></strong><p>2 SKS · IPS 4,00</p></div>
            <a class="record-source" href="#peran">Lihat hasil pada aplikasi</a>
        </div>
        <div class="hero-baseline"><span>Rencana → Perkuliahan → Hasil</span><a href="#sistem">Mengapa catatan ini saling terhubung?</a></div>
    </section>

    <section id="sistem" class="home-section system-story" aria-labelledby="system-title">
        <div><p class="section-index">01 / Dasar rancangan</p><h2 id="system-title">Satu mahasiswa.<br>Banyak hubungan.</h2></div>
        <div class="system-visual" data-reveal><div class="network-scroll" tabindex="0" role="region" aria-label="Peta hubungan akademik. Gunakan tombol panah untuk menggeser diagram pada layar kecil.">
            <svg class="academic-network" viewBox="0 0 720 350" role="img" aria-labelledby="network-title network-desc">
                <title id="network-title">Peta hubungan catatan akademik</title><desc id="network-desc">Mahasiswa memiliki KRS; KRS memiliki detail yang merujuk kelas. Kelas terhubung dengan dosen, mata kuliah, dan periode. Nilai komponen merujuk detail KRS.</desc>
                <g class="network-lines"><path d="M150 170h50m130 0h50m130 0h50M265 130V60h60M630 130V60M455 210v55M630 210v55M395 60h130v110h35"/></g>
                <g class="network-entity"><rect x="20" y="130" width="130" height="80"/><rect x="200" y="130" width="130" height="80"/><rect x="380" y="130" width="130" height="80"/><rect x="560" y="130" width="140" height="80"/></g>
                <g class="network-secondary"><rect x="325" y="20" width="140" height="80"/><rect x="560" y="20" width="140" height="80"/><rect x="380" y="265" width="140" height="70"/><rect x="560" y="265" width="140" height="70"/></g>
                <g class="network-labels"><text x="85" y="165">Mahasiswa</text><text x="265" y="165">KRS</text><text x="445" y="165">Detail KRS</text><text x="630" y="165">Kelas</text><text x="395" y="55">Periode</text><text x="630" y="55">Dosen</text><text x="450" y="295">Nilai komponen</text><text x="630" y="295">Mata kuliah</text></g>
                <g class="network-keys"><text x="85" y="190">NPM unik</text><text x="265" y="190">rencana studi</text><text x="445" y="190">pengambilan</text><text x="630" y="190">penawaran</text><text x="395" y="80">tahun + semester</text><text x="630" y="80">pengampu</text><text x="450" y="318">nilai per peserta</text><text x="630" y="318">katalog + SKS</text></g>
                <g class="network-cardinality"><text x="175" y="160">1:N</text><text x="355" y="160">1:N</text><text x="535" y="160">N:1</text></g>
            </svg></div>
            <p class="network-hint">Geser peta untuk melihat seluruh hubungan.</p>
            <dl class="network-legend"><div><dt><x-icon name="database"/>PK / FK</dt><dd>Penghubung catatan</dd></div><div><dt><x-icon name="relations"/>JOIN</dt><dd>Rekonstruksi data</dd></div><div><dt><x-icon name="chart"/>Σ bobot × nilai</dt><dd>Hasil penilaian</dd></div></dl>
        </div>

    </section>

    <section id="alur" class="home-section journey-section" aria-labelledby="journey-title">
        <div class="journey-intro"><p class="section-index">02 / Alur demonstrasi</p><h2 id="journey-title">Dari pilihan kelas<br>sampai kartu hasil.</h2><p>Ikuti satu pengambilan mata kuliah. Perubahan status menentukan tindakan berikutnya dan siapa yang dapat melakukannya.</p><div class="journey-visual" aria-hidden="true"><span class="journey-node">Mahasiswa</span><span class="journey-line"></span><span class="journey-node">Admin</span><span class="journey-line"></span><span class="journey-node">Dosen</span><span class="journey-line"></span><span class="journey-node">Hasil studi</span></div></div>
        <ol class="journey-steps">
            @foreach([
                ['Mahasiswa','Pilih kelas, simpan rencana.','Pilih kelas sesuai prodi, periode, SKS, dan jadwal. Draf belum memesan kursi.','Draf KRS'],
                ['Mahasiswa','Ajukan untuk ditinjau.','Kapasitas diperiksa, kursi dipesan, dan pilihan dikunci sampai keputusan admin.','Diajukan'],
                ['Admin','Tinjau dan beri keputusan.','Setujui, atau kembalikan dengan alasan. Pengembalian melepaskan kursi untuk revisi.','Disetujui / dikembalikan'],
                ['Dosen','Catat pertemuan dan presensi.','Catat Hadir, Izin, Sakit, atau Alpa. Belum dicatat tetap menjadi status terpisah.','Catatan perkuliahan'],
                ['Dosen','Nilai setiap komponen.','Kosong berarti belum dinilai; nol adalah nilai sah. Bobot komponen menentukan hasil.','Draf penilaian'],
                ['Dosen → Mahasiswa','Terbitkan, lalu periksa hasil.','Terbitkan setelah seluruh nilai lengkap. Koreksi memerlukan alasan dan tercatat di riwayat.','KHS tersedia'],
            ] as $step)
            <li class="journey-step" data-journey-step><span class="step-number"><x-icon :name="['book','send','check','calendar','chart','student'][$loop->index]"/><small>{{ str_pad((string)$loop->iteration,2,'0',STR_PAD_LEFT) }}</small></span><div><span class="step-role">{{ $step[0] }}</span><h3>{{ $step[1] }}</h3><p>{{ $step[2] }}</p><span class="step-state">{{ $step[3] }}</span></div></li>
            @endforeach
        </ol>
    </section>

    <section id="peran" class="home-section role-section" aria-labelledby="role-title">
        <div class="section-heading"><div><p class="section-index">03 / Ruang kerja</p><h2 id="role-title">Tugas berbeda.<br>Catatan yang terhubung.</h2></div><p>Tampilan dari aplikasi lokal. Setiap akun hanya memperoleh tindakan dan catatan yang sesuai dengan perannya.</p></div>
        <div class="role-tabs" aria-label="Pilih ruang kerja" data-role-tabs>
            <button type="button" data-role="mahasiswa" aria-controls="role-mahasiswa" aria-pressed="true" aria-label="Mahasiswa" title="Mahasiswa" class="icon-action"><x-icon name="student"/><span class="control-label sr-only">Mahasiswa</span></button><button type="button" data-role="dosen" aria-controls="role-dosen" aria-pressed="false" aria-label="Dosen" title="Dosen" class="icon-action"><x-icon name="teacher"/><span class="control-label sr-only">Dosen</span></button><button type="button" data-role="admin" aria-controls="role-admin" aria-pressed="false" aria-label="Admin" title="Admin" class="icon-action"><x-icon name="users"/><span class="control-label sr-only">Admin</span></button>
        </div>
        @foreach([
            'mahasiswa'=>['Rencana dan hasil, dalam satu ruang.','Susun KRS, lihat jadwal, periksa presensi, dan pantau hasil yang sudah diterbitkan. Nilai yang belum lengkap tetap terlihat sebagai proses yang belum selesai.',['KRS dengan jumlah SKS pilihan','KHS, IPS sementara, dan IPK','Jadwal serta presensi sendiri'],'mahasiswa.png','KHS mahasiswa simulasi pada aplikasi Perkuliahan'],
            'dosen'=>['Fokus pada kelas yang diampu.','Catat pertemuan, kelola presensi, dan isi komponen nilai peserta yang telah disetujui. Pemeriksaan kelengkapan berlangsung sebelum hasil diterbitkan.',['Presensi per pertemuan','Komponen nilai dan bobot kelas','Publikasi serta koreksi beralasan'],'dosen.png','Penilaian kelas dosen pada aplikasi Perkuliahan'],
            'admin'=>['Kelola hubungan, tinjau keputusan.','Rawat katalog akademik, tawarkan kelas, atur jadwal, dan tinjau pengajuan KRS. Jejak perubahan membantu memeriksa keputusan yang telah dilakukan.',['CRUD seluruh master akademik','Persetujuan dan pengembalian KRS','Rekap serta riwayat perubahan'],'admin.png','Dashboard admin pada aplikasi Perkuliahan'],
        ] as $key=>$role)
        <div class="role-panel" id="role-{{ $key }}" data-role-panel="{{ $key }}">
            <div class="role-copy"><p class="role-name">Ruang kerja {{ ucfirst($key) }}</p><h3>{{ $role[0] }}</h3><p>{{ $role[1] }}</p><ul>@foreach($role[2] as $feature)<li>{{ $feature }}</li>@endforeach</ul><a class="home-text-link" href="{{ auth()->check()?route('dashboard'):route('login') }}">{{ auth()->check()?'Buka ruang kerja saya':'Masuk dengan akun lokal' }}</a></div>
            <figure class="product-preview"><img src="{{ asset('images/showcase/'.$role[3]) }}" alt="{{ $role[4] }}" width="1440" height="1000" loading="lazy"><figcaption>Dokumentasi aplikasi lokal · data simulasi</figcaption></figure>
        </div>
        @endforeach
        <noscript><p>Semua ruang kerja ditampilkan karena JavaScript tidak aktif.</p></noscript>
    </section>

    <section id="data" class="home-section data-section" aria-labelledby="data-title">
        <div class="section-heading"><div><p class="section-index">04 / Model relasional</p><h2 id="data-title">Hubungan disimpan.<br>Hasil dihitung.</h2></div><p>Visual ini merangkum relasi utama. ERD lengkap, kamus data, dan dekomposisi sampai 3NF tersedia pada laporan UTS.</p></div>
        <div class="relation-map" data-reveal>
            <div class="relation-entity"><x-icon name="student"/><span>Identitas</span><h3>Mahasiswa</h3><p>NPM unik<br>Referensi program studi</p></div><span class="relation-connector">1 : N</span>
            <div class="relation-entity"><x-icon name="book"/><span>Rencana</span><h3>KRS</h3><p>Mahasiswa + periode<br>Status dan keputusan</p></div><span class="relation-connector">1 : N</span>
            <div class="relation-entity"><x-icon name="relations"/><span>Pengambilan</span><h3>Detail KRS</h3><p>Referensi KRS<br>Referensi kelas</p></div><span class="relation-connector">N : 1</span>
            <div class="relation-entity"><x-icon name="calendar"/><span>Penawaran</span><h3>Kelas</h3><p>Mata kuliah + periode<br>Pengampu dan kapasitas</p></div>
        </div>
        <div class="data-notes"><article><span>Integritas</span><h3>Referensi yang dapat diperiksa.</h3><p>PK dan FK menghubungkan entitas. UNIQUE menjaga identitas; CHECK membatasi domain nilai. Trigger memeriksa kesesuaian kelas dan periode pada relasi transaksi.</p></article><article><span>Normalisasi</span><h3>Setiap informasi punya tempat.</h3><p>Katalog, penawaran, peserta, komponen nilai, jadwal, dan presensi dipisahkan menurut ketergantungannya. Nama master dibaca melalui JOIN, bukan disalin ke catatan transaksi.</p></article><article><span>Perhitungan</span><h3>Nilai dapat ditelusuri kembali.</h3><p>Nilai akhir memakai bobot komponen. IPS memakai SKS yang nilainya terbit. IPK memakai pengambilan terbaru yang sudah terbit untuk setiap mata kuliah.</p></article></div>
    </section>

    <section id="bukti" class="home-section evidence-section" aria-labelledby="evidence-title">
        <div><p class="section-index">05 / Pengerjaan UTS</p><h2 id="evidence-title">Rancangan bertemu<br>bukti implementasi.</h2><p>Enam bagian laporan mengikuti soal UTS. Fokus utamanya adalah basis data, relasi, SQL, dan normalisasi.</p><p class="evidence-note">Data serta aturan akademik merupakan simulasi. Sistem ini dibuat untuk pembelajaran dan demonstrasi UTS.</p></div>
        <dl class="evidence-list">
            <div><dt>01 / Komputer dan web server</dt><dd>Spesifikasi aktual, konfigurasi localhost, serta diagram browser–Laravel–MySQL.</dd></div>
            <div><dt>02 / Pemrograman web</dt><dd>Struktur proyek, autentikasi, validasi, dan potongan CRUD dari implementasi.</dd></div>
            <div><dt>03 / Database server</dt><dd>Tipe data, kamus tabel, kunci, serta aturan integritas pada MySQL.</dd></div>
            <div><dt>04 / Relasional, SQL, normalisasi</dt><dd>ERD, ketergantungan fungsional, UNF–3NF, query, dan hasil eksekusi.</dd></div>
            <div><dt>05 / Implementasi lokal</dt><dd>Instalasi, bukti localhost, kendala nyata, dan pemeriksaan reproduksi.</dd></div>
            <div><dt>06 / Hasil aplikasi</dt><dd>Demonstrasi tiga peran, screenshot, KRS, presensi, nilai, dan KHS.</dd></div>
        </dl>
    </section>

    <section id="jalankan" class="home-section run-section" aria-labelledby="run-title">
        <div><p class="section-index">06 / Akses aplikasi</p><h2 id="run-title">Buka sistem.<br>Telusuri catatannya.</h2><p>Masuk melalui web menggunakan akun pribadi sesuai peran. Untuk menjalankan salinan di komputer, ikuti instalasi lokal dalam paket aplikasi dan README.</p><a class="button primary icon-action" href="{{ auth()->check()?route('dashboard'):route('login') }}" aria-label="{{ auth()->check()?'Kembali ke ruang kerja':'Buka halaman masuk' }}" title="{{ auth()->check()?'Kembali ke ruang kerja':'Buka halaman masuk' }}"><x-icon name="login"/><span class="control-label sr-only">{{ auth()->check()?'Kembali ke ruang kerja':'Buka halaman masuk' }}</span></a></div>
        <div class="run-guide"><ol><li><strong>Periksa kebutuhan</strong><code>powershell -ExecutionPolicy Bypass -File check.ps1</code></li><li><strong>Pasang dan jalankan</strong><code>powershell -ExecutionPolicy Bypass -File install.ps1</code><code>powershell -ExecutionPolicy Bypass -File start.ps1</code></li><li><strong>Masuk melalui localhost</strong><p>Akses <code>http://localhost:8088</code>. Akun dan kata sandi tersedia pada <code>Runtime/akses-demo.txt</code> yang dibuat saat instalasi.</p></li></ol><p class="small">Instalasi pertama memerlukan internet untuk dependensi. Setelah build, font dan aset dilayani secara lokal.</p></div>
    </section>
</main>
<footer class="home-footer"><a class="identity" href="{{ route('home') }}"><span class="identity-mark">P</span><span>Perkuliahan<small>Rencana bertemu hasil.</small></span></a><p>Akhyar · 260820701100010<br>Magister Kecerdasan Artifisial · USK · 2026</p><a href="#main">Kembali ke atas</a></footer>
</body>
</html>
