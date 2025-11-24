<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>ฟอร์มสำรวจครัวเรือน</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-3">
<div class="container">
    <h2>แบบฟอร์มสำรวจครัวเรือน (Sections A–E)</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            กรุณาตรวจสอบข้อผิดพลาด
            <ul>
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('households.store') }}">
        @csrf

        <h4>Section A: ข้อมูลพื้นฐาน</h4>
        <div class="row mb-2">
            <div class="col-md-2">
                <label>คำนำหน้า</label>
                <input type="text" name="prefix" class="form-control" value="{{ old('prefix') }}">
            </div>
            <div class="col-md-4">
                <label>ชื่อ</label>
                <input type="text" name="first_name" class="form-control" required value="{{ old('first_name') }}">
            </div>
            <div class="col-md-4">
                <label>นามสกุล</label>
                <input type="text" name="last_name" class="form-control" required value="{{ old('last_name') }}">
            </div>
            <div class="col-md-2">
                <label>เพศ</label>
                <select name="gender" class="form-control">
                    <option value="">-- เลือก --</option>
                    <option value="male" {{ old('gender')=='male' ? 'selected':'' }}>ชาย</option>
                    <option value="female" {{ old('gender')=='female' ? 'selected':'' }}>หญิง</option>
                    <option value="other" {{ old('gender')=='other' ? 'selected':'' }}>อื่นๆ</option>
                </select>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-2"><label>อายุ</label><input type="number" name="age" class="form-control" value="{{ old('age') }}"></div>
            <div class="col-md-4"><label>การศึกษา</label><input type="text" name="education" class="form-control" value="{{ old('education') }}"></div>
            <div class="col-md-6"><label>สุขภาพ</label><input type="text" name="health" class="form-control" value="{{ old('health') }}"></div>
        </div>

        <div class="mb-3">
            <label>ที่อยู่ (กรอกจังหวัด/อำเภอ/ตำบล/หมู่บ้าน/เลขที่/หมู่ที่)</label>
            <div class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="province" class="form-control" placeholder="จังหวัด" value="{{ old('province') }}">
                </div>
                <div class="col-md-3">
                    <input type="text" name="district" class="form-control" placeholder="อำเภอ" value="{{ old('district') }}">
                </div>
                <div class="col-md-3">
                    <input type="text" name="subdistrict" class="form-control" placeholder="ตำบล" value="{{ old('subdistrict') }}">
                </div>
                <div class="col-md-3">
                    <input type="text" name="village" class="form-control" placeholder="หมู่บ้าน" value="{{ old('village') }}">
                </div>
            </div>
            <div class="row g-2 mt-2">
                <div class="col-md-3">
                    <input type="text" name="moo_no" class="form-control" placeholder="หมู่ที่" value="{{ old('moo_no') }}">
                </div>
                <div class="col-md-3">
                    <input type="text" name="village_no" class="form-control" placeholder="เลขที่" value="{{ old('village_no') }}">
                </div>
                <div class="col-md-3">
                    <input type="text" name="phone" class="form-control" placeholder="เบอร์โทร" value="{{ old('phone') }}">
                </div>
            </div>
        </div>

        <!-- rest of the form (unchanged) -->
        <div class="row mb-2">
            <div class="col-md-3"><label>จำนวนสมาชิกในครัวเรือน</label><input type="number" name="household_members" class="form-control" value="{{ old('household_members') }}"></div>
            <div class="col-md-4"><label>อาชีพหลัก</label><input type="text" name="main_occupation" class="form-control" value="{{ old('main_occupation') }}"></div>
            <div class="col-md-5"><label>อาชีพเสริม</label><input type="text" name="extra_occupation" class="form-control" value="{{ old('extra_occupation') }}"></div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4"><label>รายได้/เดือน (บาท)</label><input type="number" step="0.01" name="income_month" class="form-control" value="{{ old('income_month') }}"></div>
            <div class="col-md-4"><label>รายจ่าย/เดือน (บาท)</label><input type="number" step="0.01" name="expense_month" class="form-control" value="{{ old('expense_month') }}"></div>
            <div class="col-md-4"><label>หนี้สินคงค้าง (รายละเอียด)</label><input type="text" name="debt" class="form-control" value="{{ old('debt') }}"></div>
        </div>

        <h4>Section B: ความพร้อมด้านกายภาพ</h4>

        <div class="form-check mb-2">
            <input type="hidden" name="has_mushroom_area" value="0">
            <input class="form-check-input" type="checkbox" name="has_mushroom_area" value="1" id="mushroom_area" {{ old('has_mushroom_area') ? 'checked' : '' }}>
            <label class="form-check-label" for="mushroom_area">มีพื้นที่เพาะเห็ด</label>
        </div>

        <div class="row mb-2">
            <div class="col-md-3"><label>ขนาด (ตร.ม.)</label><input type="number" step="0.1" name="mushroom_area_size" class="form-control" value="{{ old('mushroom_area_size') }}"></div>
            <div class="col-md-3"><label>แหล่งน้ำ</label><select class="form-control" name="water_source">
                <option value="">-- เลือก --</option>
                <option value="tap" {{ old('water_source')=='tap' ? 'selected':'' }}>ประปา</option>
                <option value="well" {{ old('water_source')=='well' ? 'selected':'' }}>บ่อน้ำ</option>
                <option value="other" {{ old('water_source')=='other' ? 'selected':'' }}>อื่นๆ</option>
            </select></div>

            <div class="col-md-3">
                <label>ไฟฟ้า</label>
                <div class="form-check">
                    <input type="hidden" name="has_electricity" value="0">
                    <input class="form-check-input" type="checkbox" name="has_electricity" value="1" id="has_electricity" {{ old('has_electricity') ? 'checked' : '' }}>
                    <label class="form-check-label" for="has_electricity">มีไฟฟ้า</label>
                </div>
            </div>

            <div class="col-md-3"><label>ระยะทางจากตลาด (กม.)</label><input type="number" step="0.1" name="market_distance_km" class="form-control" value="{{ old('market_distance_km') }}"></div>
        </div>

        <h4>Section C: ประสบการณ์และทักษะ</h4>
        <div class="form-check">
            <input type="hidden" name="ever_farmed" value="0">
            <input class="form-check-input" type="checkbox" name="ever_farmed" value="1" id="ever_farmed" {{ old('ever_farmed') ? 'checked' : '' }}>
            <label class="form-check-label" for="ever_farmed">เคยทำเกษตรกรรม</label>
        </div>

        <div class="form-check">
            <input type="hidden" name="ever_mushroom" value="0">
            <input class="form-check-input" type="checkbox" name="ever_mushroom" value="1" id="ever_mushroom" {{ old('ever_mushroom') ? 'checked' : '' }}>
            <label class="form-check-label" for="ever_mushroom">เคยเพาะเห็ด</label>
        </div>

        <div class="mb-2">
            <label>การใช้สมาร์ทโฟน</label>
            <select class="form-control" name="smartphone_usage">
                <option value="not_use" {{ old('smartphone_usage')=='not_use' ? 'selected':'' }}>ไม่ได้</option>
                <option value="use_some" {{ old('smartphone_usage')=='use_some' ? 'selected':'' }}>ใช้ได้บ้าง</option>
                <option value="use_well" {{ old('smartphone_usage')=='use_well' ? 'selected':'' }}>ใช้เป็น</option>
            </select>
        </div>

        <div class="form-check mb-2">
            <input type="hidden" name="social_media" value="0">
            <input class="form-check-input" type="checkbox" name="social_media" value="1" id="social_media" {{ old('social_media') ? 'checked' : '' }}>
            <label class="form-check-label" for="social_media">การใช้ Social Media</label>
        </div>

        <h4>Section D: ความสนใจและแรงจูงใจ</h4>
        <div class="mb-2">
            <label>ความสนใจเข้าร่วมโครงการ</label>
            <select class="form-control" name="interest_level">
                <option value="">-- เลือก --</option>
                <option value="high" {{ old('interest_level')=='high' ? 'selected':'' }}>มาก</option>
                <option value="medium" {{ old('interest_level')=='medium' ? 'selected':'' }}>ปานกลาง</option>
                <option value="low" {{ old('interest_level')=='low' ? 'selected':'' }}>น้อย</option>
            </select>
        </div>

        <div class="mb-2">
            <label>เหตุผลที่ต้องการเข้าร่วม</label>
            <textarea class="form-control" name="interest_reason">{{ old('interest_reason') }}</textarea>
        </div>

        <div class="row mb-2">
            <div class="col-md-6"><label>เวลาที่สามารถอุทิศให้ (ชม./สัปดาห์)</label><input type="number" step="0.1" name="available_hours_per_week" class="form-control" value="{{ old('available_hours_per_week') }}"></div>
            <div class="col-md-6"><label>ความพร้อมในการลงทุนเริ่มต้น (บาท)</label><input type="number" step="0.01" name="initial_investment" class="form-control" value="{{ old('initial_investment') }}"></div>
        </div>

        <h4>Section E: การรวมกลุ่ม</h4>
        <div class="form-check mb-2">
            <input type="hidden" name="group_member" value="0">
            <input class="form-check-input" type="checkbox" name="group_member" value="1" id="group_member" {{ old('group_member') ? 'checked' : '' }}>
            <label class="form-check-label" for="group_member">เป็นสมาชิกกลุ่ม/วิสาหกิจชุมชน</label>
        </div>

        <div class="mb-2">
            <label>ความพร้อมเข้าร่วมกลุ่ม</label>
            <select class="form-control" name="group_readiness">
                <option value="">-- เลือก --</option>
                <option value="ready" {{ old('group_readiness')=='ready'? 'selected':'' }}>พร้อม</option>
                <option value="consider" {{ old('group_readiness')=='consider'? 'selected':'' }}>พิจารณา</option>
                <option value="not_interested" {{ old('group_readiness')=='not_interested'? 'selected':'' }}>ไม่สนใจ</option>
            </select>
        </div>

        <button class="btn btn-primary mt-3" type="submit">บันทึกข้อมูล</button>
    </form>
</div>
</body>
</html>