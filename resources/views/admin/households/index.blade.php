<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Admin - รายการครัวเรือน</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-3">
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>รายการครัวเรือน</h3>
        <div>
            <a href="{{ route('admin.households.export', request()->query()) }}" class="btn btn-success">Export CSV</a>
        </div>
    </div>

    <form class="row g-2 mb-3">
        <div class="col-auto">
            <input name="province" value="{{ request('province') }}" class="form-control" placeholder="จังหวัด/อำเภอ/ตำบล">
        </div>
        <div class="col-auto">
            <select name="priority" class="form-select">
                <option value="">-- Priority --</option>
                <option value="A" {{ request('priority')=='A' ? 'selected':'' }}>A</option>
                <option value="B" {{ request('priority')=='B' ? 'selected':'' }}>B</option>
                <option value="C" {{ request('priority')=='C' ? 'selected':'' }}>C</option>
                <option value="D" {{ request('priority')=='D' ? 'selected':'' }}>D</option>
            </select>
        </div>
        <div class="col-auto">
            <button class="btn btn-primary">Filter</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-sm table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>ชื่อ-สกุล</th>
                    <th>เพศ</th>
                    <th>อายุ</th>
                    <th>อำเภอ</th>
                    <th>ตำบล</th>
                    <th>หมู่ที่</th>
                    <th>หมู่บ้าน</th>
                    <th>บ้านเลขที่</th>
                    <th>รายได้</th>
                    <th>รายจ่าย</th>
                    <th>หนี้สิน</th>
                    <th>ผ่าน</th>
                    <th>Priority</th>
                    <th>วันที่บันทึก</th>
                </tr>
            </thead>
            <tbody>
            @foreach($households as $h)
                <tr>
                    <td>{{ $h->id }}</td>
                    <td>{{ $h->prefix }}{{ $h->first_name }} {{ $h->last_name }}</td>
                    <td>{{ \App\Models\Household::genderLabel($h->gender) }}</td>
                    <td>{{ $h->age }}</td>
                    <td>{{ $h->district }}</td>
                    <td>{{ $h->subdistrict }}</td>
                    <td>{{ $h->moo_no }}</td>
                    <td>{{ $h->village }}</td>
                    <td>{{ $h->village_no }}</td>
                    <td>{{ number_format($h->income_month,2) }}</td>
                    <td>{{ number_format($h->expense_month,2) }}</td>
                    <td>{{ number_format($h->debt,2) }}</td>
                    <td>{{ $h->passed ? 'ใช่' : 'ไม่ใช่' }}</td>
                    <td>{{ $h->priority }}</td>
                    <td>{{ $h->created_at ? $h->created_at->format('Y-m-d') : '' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $households->withQueryString()->links() }}
    </div>
</div>
</body>
</html>