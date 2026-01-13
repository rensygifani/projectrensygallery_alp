@extends('layouts.app')

@section('content')
<h3 class="fw-bold mb-3">🚚 Cek Ongkos Kirim</h3>

<div class="card p-4">

    {{-- PROVINSI --}}
    <div class="mb-3">
        <label class="fw-bold">Provinsi</label>
        <select id="province" name="province_id" class="form-control">
            <option value="">-- Pilih Provinsi --</option>
            @foreach($provinces as $province)
                <option value="{{ $province['id'] }}">
                    {{ $province['name'] }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- KOTA --}}
    <div class="mb-3">
        <label class="fw-bold">Kota / Kabupaten</label>
        <select id="city" name="city_id" class="form-control">
            <option value="">-- Pilih Kota --</option>
        </select>
    </div>

    {{-- KECAMATAN --}}
    <div class="mb-3">
        <label class="fw-bold">Kecamatan</label>
        <select id="district" name="district_id" class="form-control">
            <option value="">-- Pilih Kecamatan --</option>
        </select>
    </div>

    {{-- BERAT --}}
    <div class="mb-3">
        <label class="fw-bold">Berat (gram)</label>
        <input type="number" id="weight" class="form-control" value="1000" min="1">
    </div>

    {{-- RADIO BUTTON KURIR (DARI TUTORIAL) --}}
    <div class="mb-3">
        <label class="fw-bold mb-2">Pilih Kurir</label>
        <div class="row">

            <div class="col-md-4">
                <input type="radio" name="courier" value="jne"> JNE
            </div>

            <div class="col-md-4">
                <input type="radio" name="courier" value="pos"> POS Indonesia
            </div>

            <div class="col-md-4">
                <input type="radio" name="courier" value="tiki"> TIKI
            </div>

            <div class="col-md-4">
                <input type="radio" name="courier" value="jnt"> J&T
            </div>

            <div class="col-md-4">
                <input type="radio" name="courier" value="sicepat"> SiCepat
            </div>

            <div class="col-md-4">
                <input type="radio" name="courier" value="anteraja"> AnterAja
            </div>

        </div>
    </div>

    {{-- BUTTON --}}
    <button id="cekOngkir" class="btn pastel-btn w-100">
        Cek Ongkir
    </button>

    {{-- RESULT --}}
    <div id="result" class="mt-4"></div>

</div>

{{-- SCRIPT --}}
<script>
/* ======================
   PROVINSI -> KOTA
====================== */
document.getElementById('province').addEventListener('change', function () {
    let provinceId = this.value;
    let city = document.getElementById('city');
    let district = document.getElementById('district');

    city.innerHTML = '<option value="">-- Pilih Kota --</option>';
    district.innerHTML = '<option value="">-- Pilih Kecamatan --</option>';

    if (!provinceId) return;

    fetch(`/cities/${provinceId}`)
        .then(res => res.json())
        .then(data => {
            data.forEach(item => {
                city.innerHTML += `<option value="${item.id}">${item.name}</option>`;
            });
        });
});

/* ======================
   KOTA -> KECAMATAN
====================== */
document.getElementById('city').addEventListener('change', function () {
    let cityId = this.value;
    let district = document.getElementById('district');

    district.innerHTML = '<option value="">-- Pilih Kecamatan --</option>';

    if (!cityId) return;

    fetch(`/districts/${cityId}`)
        .then(res => res.json())
        .then(data => {
            data.forEach(item => {
                district.innerHTML += `<option value="${item.id}">${item.name}</option>`;
            });
        });
});

/* ======================
   CHECK ONGKIR
====================== */
document.getElementById('cekOngkir').addEventListener('click', function () {

    let district = document.getElementById('district').value;
    let weight   = document.getElementById('weight').value;
    let courier  = document.querySelector('input[name="courier"]:checked');

    if (!district || !weight || !courier) {
        alert('Lengkapi semua data terlebih dahulu');
        return;
    }

    fetch('/check-ongkir', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            district_id: district,
            weight: weight,
            courier: courier.value
        })
    })
    .then(res => res.json())
    .then(data => {
        let html = '<ul class="list-group">';
        data.forEach(item => {
            html += `
                <li class="list-group-item">
                    <strong>${item.service}</strong><br>
                    Rp ${item.cost} (${item.etd})
                </li>
            `;
        });
        html += '</ul>';
        document.getElementById('result').innerHTML = html;
    });
});
</script>
@endsection
