<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>รายละเอียดครัวเรือน</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-3">
<div class="container">
  <h3>รายละเอียดข้อมูลครัวเรือน</h3>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <table class="table table-bordered">
    <tr><th>ชื่อ-สกุล</th><td>{{ $household->prefix }} {{ $household->first_name }} {{ $household->last_name }}</td></tr>
    <tr><th>เพศ</th><td>{{ $household->gender }}</td></tr>
    <tr><th>อายุ</th><td>{{ $household->age }}</td></tr>
    <tr><th>ที่อยู่</th><td>{{ $household->province }} / {{ $household->district }} / {{ $household->subdistrict }} / {{ $household->village }}</td></tr>
    <tr><th>เลขที่</th><td>{{ $household->house_no }}</td></tr>
    <tr><th>หมู่ที่</th><td>{{ $household->moo_no }}</td></tr>
    <tr><th>เบอร์โทร</th><td>{{ $household->phone }}</td></tr>
    <tr><th>รายได้/เดือน</th><td>{{ number_format($household->income_month,2) }}</td></tr>
    <tr><th>รายจ่าย/เดือน</th><td>{{ number_format($household->expense_month,2) }}</td></tr>
    <tr><th>คะแนนรวม</th><td>{{ $household->total_score }}</td></tr>
    <tr><th>Priority</th><td>{{ $household->priority }}</td></tr>
    <tr><th>ผ่าน</th><td>{{ $household->passed ? 'ใช่' : 'ไม่' }}</td></tr>
  </table>

  <a href="{{ route('households.create') }}" class="btn btn-secondary">บันทึกใหม่</a>
  <a href="{{ url('/admin/households') }}" class="btn btn-primary">ไปหน้ารายการ</a>
</div>
</body>
</html>