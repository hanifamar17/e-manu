<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<style>
  @page { margin: 2.5cm 2cm 2cm 2cm; }
  body { font-family: "Times New Roman", Times, serif; font-size: 12pt; line-height: 1.4; color: #000; }
  .title { text-align: center; font-weight: bold; font-size: 13pt; margin-bottom: 2px; }
  .nomor { text-align: center; font-size: 11pt; margin-bottom: 14px; }
  p { text-align: justify; margin: 6px 0; }
  table.identitas { margin: 6px 0 6px 0; border-collapse: collapse; }
  table.identitas td { padding: 1px 6px 1px 0; vertical-align: top; }
  .section-title { font-weight: bold; text-transform: uppercase; margin: 14px 0 6px 0; }
  table.barang { width: 100%; border-collapse: collapse; margin: 8px 0; }
  table.barang th, table.barang td { border: 1px solid #000; padding: 5px 6px; font-size: 10.5pt; }
  table.barang th { background: #f0f0f0; text-align: center; }
  .c { text-align: center; }
  .jaminan p { margin: 2px 0 2px 20px; }
  table.ttd { width: 100%; margin-top: 28px; border-collapse: collapse; page-break-inside: avoid; }
  table.ttd td { width: 50%; text-align: center; vertical-align: top; }
</style>
</head>
<body>
  <div class="title">SURAT PEMINJAMAN ASET MA NURUL UMMAH</div>
  <div class="nomor">Nomor: {{ $p->nomor_surat }}</div>

  <p>Saya yang bertanda tangan di bawah ini:</p>
  <table class="identitas">
    <tr><td width="150">Nama</td><td width="8">:</td><td>{{ $p->nama }}</td></tr>
    <tr><td>Jenis Kelamin</td><td>:</td><td>{{ $p->jenis_kelamin }}</td></tr>
    <tr><td>Pekerjaan/Instansi</td><td>:</td><td>{{ $p->pekerjaan }}</td></tr>
    <tr><td>Alamat</td><td>:</td><td>{{ $p->alamat }}</td></tr>
    <tr><td>No. Telepon/WA</td><td>:</td><td>{{ $p->telepon }}</td></tr>
    <tr><td>No. Identitas</td><td>:</td><td>{{ $p->no_identitas }}</td></tr>
  </table>

  <p>Dengan ini mengajukan permohonan untuk meminjam aset milik:</p>
  <table class="identitas">
    <tr><td width="150">Nama Lembaga</td><td width="8">:</td><td>MA Nurul Ummah Yogyakarta</td></tr>
    <tr><td>Alamat Lembaga</td><td>:</td><td>Jalan Raden Ronggo KG/II 982, Prenggan, Kotagede, D.I. Yogyakarta</td></tr>
  </table>

  <div class="section-title">Rincian Barang yang Dipinjam</div>
  <table class="barang">
    <thead>
      <tr>
        <th width="5%">No</th><th width="30%">Nama Barang</th><th width="27%">Merek/Spesifikasi</th>
        <th width="12%">Jumlah</th><th width="26%">Kondisi Saat Dipinjam</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($p->items as $i)
        <tr>
          <td class="c">{{ $i->no }}</td>
          <td>{{ $i->nama_barang }}</td>
          <td>{{ $i->merk }}</td>
          <td class="c">{{ $i->jumlah }}</td>
          <td class="c">{{ $i->kondisi }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <table class="identitas">
    <tr><td width="150">Tujuan Peminjaman</td><td width="8">:</td><td>{{ $p->tujuan }}</td></tr>
    <tr><td>Lokasi Penggunaan</td><td>:</td><td>{{ $p->lokasi_penggunaan }}</td></tr>
    <tr><td>Tanggal Peminjaman</td><td>:</td><td>{{ $p->tgl_pinjam->locale('id')->translatedFormat('j F Y') }}</td></tr>
    <tr><td>Tanggal Pengembalian</td><td>:</td><td>{{ $p->tgl_kembali->locale('id')->translatedFormat('j F Y') }}</td></tr>
  </table>

  <div class="section-title">Jaminan Peminjaman</div>
  <p>Sebagai bentuk pertanggungjawaban, peminjam menyerahkan jaminan berupa:</p>
  <div class="jaminan">
    <p>{{ $p->jaminan_identitas ? '[X]' : '[ ]' }} Identitas Asli berupa {{ $p->jenis_identitas_jaminan ?: 'KTP/SIM/Paspor' }}</p>
    <p>{{ $p->jaminan_surat ? '[X]' : '[ ]' }} Surat Jaminan dari Instansi/Organisasi</p>
  </div>
  <p>Jaminan akan dikembalikan apabila seluruh barang dikembalikan dalam kondisi baik, lengkap, dan tepat waktu.</p>

  <div class="section-title">Pernyataan Tanggung Jawab</div>
  <p>Saya bertanggung jawab penuh atas penggunaan, perawatan, dan pengembalian barang-barang yang saya pinjam.
     Jika terjadi kerusakan atau kehilangan, saya bersedia memperbaiki atau mengganti sesuai ketentuan yang berlaku.</p>
  <p>Demikian surat ini saya buat dengan sebenar-benarnya untuk digunakan sebagaimana mestinya.</p>

  <table class="ttd">
    <tr>
      <td>Petugas Lembaga,</td>
      <td>{{ $p->kota }}, {{ $p->tgl_surat->locale('id')->translatedFormat('j F Y') }}</td>
    </tr>
    <tr>
      <td></td>
      <td>Yang Meminjam,<br><i style="font-size:10pt">(Meterai Rp10.000)</i></td>
    </tr>
    <tr><td style="height:70px"></td><td></td></tr>
    <tr>
      <td><strong>{{ $p->nama_petugas }}</strong></td>
      <td><strong>{{ $p->nama }}</strong></td>
    </tr>
  </table>
</body>
</html>
