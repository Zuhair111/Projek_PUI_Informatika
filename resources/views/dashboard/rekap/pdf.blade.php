<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
    th { background: #f3f4f6; }
  </style>
</head>
<body>
  <h3>Rekap Presensi</h3>
  <table>
    <thead>
      <tr>
        <th>Nama Karyawan</th>
        <th>Departemen</th>
        <th>Tanggal</th>
        <th>Masuk</th>
        <th>Pulang</th>
        <th>Status</th>
        <th>Hasil Deteksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($rows as $row)
        <tr>
          <td>{{ $row->nama_karyawan }}</td>
          <td>{{ $row->departemen }}</td>
          <td>{{ $row->tanggal }}</td>
          <td>{{ $row->check_in_at ?? '-' }}</td>
          <td>{{ $row->check_out_at ?? '-' }}</td>
          <td>{{ $row->status }}</td>
          <td>{{ $row->hasil_deteksi }}</td>
        </tr>
      @empty
        <tr><td colspan="7">Data tidak tersedia.</td></tr>
      @endforelse
    </tbody>
  </table>
</body>
</html>
