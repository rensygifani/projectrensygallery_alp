@extends('layouts.app')

@section('content')
    <h3 class="fw-bold mb-3">Checkout</h3>

    <div id="checkout-wrapper">

        {{-- CART ITEMS --}}
        @foreach ($items as $item)
            <div class="card p-3 mb-2 d-flex flex-row gap-3 align-items-center">
                <img src="{{ asset('storage/' . $item->product->image) }}" width="70" class="rounded">

                <div class="flex-grow-1">
                    <div class="fw-bold">{{ $item->product->name }}</div>
                    <div class="small text-muted">
                        {{ $item->qty }} × Rp {{ number_format($item->product->price, 0, ',', '.') }}
                    </div>
                </div>

                <div class="fw-bold">
                    Rp {{ number_format($item->product->price * $item->qty, 0, ',', '.') }}
                </div>
            </div>
        @endforeach

        <h5 class="text-end fw-bold mt-2">
            Subtotal: Rp <span id="subtotal">{{ number_format($total, 0, ',', '.') }}</span>
        </h5>

        <hr>

        {{-- ⭐ SECTION KUPON ⭐ --}}
        <div class="card p-3 mb-3" style="background: #fff5f8; border: 2px dashed #f6a5c0;">
            <h6 class="fw-bold mb-2">🎟️ Punya Kode Kupon?</h6>
            <div class="input-group">
                <input type="text" id="coupon-code" class="form-control" placeholder="Masukkan kode kupon">
                <button id="apply-coupon" class="btn pastel-btn">Gunakan</button>
            </div>
            <div id="coupon-message" class="mt-2"></div>
        </div>

        {{-- INFO DISKON (hidden by default) --}}
        <div id="discount-info" style="display: none;" class="alert alert-success mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span>💰 Diskon Kupon (<strong id="coupon-name"></strong>):</span>
                </div>
                <div class="text-end">
                    <strong class="text-success">- Rp <span id="discount-amount">0</span></strong>
                    <button type="button" id="remove-coupon" class="btn btn-sm btn-outline-danger ms-2">
                        ✕ Hapus
                    </button>
                </div>
            </div>
        </div>

        {{-- Hidden inputs untuk kupon --}}
        <input type="hidden" id="coupon-code-input" value="">
        <input type="hidden" id="applied-discount" value="0">

        <hr>

        {{-- ALAMAT --}}
        <textarea id="address" class="form-control mb-3" placeholder="Alamat lengkap pengiriman" rows="3"></textarea>

        {{-- PROVINSI --}}
        <select id="province" class="form-control mb-2">
            <option value="">Pilih Provinsi</option>
            @foreach ($provinces as $p)
                <option value="{{ $p['id'] }}">{{ $p['name'] }}</option>
            @endforeach
        </select>

        {{-- KOTA & KECAMATAN --}}
        <select id="city" class="form-control mb-2" disabled>
            <option value="">Pilih Kota</option>
        </select>
        <select id="district" class="form-control mb-3" disabled>
            <option value="">Pilih Kecamatan</option>
        </select>

        {{-- KURIR --}}
        <div class="mb-2">
            <label class="fw-semibold">Kurir</label><br>
            <label><input type="radio" name="courier" value="jne"> JNE</label>
            <label class="ms-3"><input type="radio" name="courier" value="jnt"> J&T</label>
            <label class="ms-3"><input type="radio" name="courier" value="sicepat"> SiCepat</label>
        </div>

        <label class="fw-semibold">Berat (gram)</label>
        <input type="number" id="weight" class="form-control mb-2" value="1000" min="1">

        <button id="cekOngkir" class="btn btn-sm pastel-outline w-100">
            Cek Ongkir
        </button>

        {{-- ONGKIR RESULT --}}
        <div id="ongkirResult" class="mt-3"></div>

        <input type="hidden" id="selectedOngkir" value="0">
        <input type="hidden" id="selectedCourier" value="">
        <input type="hidden" id="selectedService" value="">

        <hr>

        {{-- RINGKASAN PEMBAYARAN --}}
        <div class="card p-3 bg-light">
            <h5 class="fw-bold mb-3">Ringkasan Pembayaran</h5>
            
            <div class="d-flex justify-content-between mb-2">
                <span>Subtotal:</span>
                <span>Rp <span id="summary-subtotal">{{ number_format($total, 0, ',', '.') }}</span></span>
            </div>
            
            <div id="summary-discount-row" style="display: none;" class="d-flex justify-content-between mb-2 text-success">
                <span>Diskon Kupon:</span>
                <span>- Rp <span id="summary-discount">0</span></span>
            </div>
            
            <div id="summary-ongkir-row" style="display: none;" class="d-flex justify-content-between mb-2">
                <span>Ongkos Kirim:</span>
                <span>Rp <span id="summary-ongkir">0</span></span>
            </div>
            
            <hr>
            
            <div class="d-flex justify-content-between">
                <h5 class="fw-bold mb-0">Total Pembayaran:</h5>
                <h5 class="fw-bold mb-0 text-pink">Rp <span id="grandTotal">{{ number_format($total, 0, ',', '.') }}</span></h5>
            </div>
        </div>

        <button type="button" id="pay-button" class="btn pastel-btn w-100 mt-3">
            💳 Bayar Sekarang
        </button>
    </div>

    {{-- MIDTRANS --}}
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>

    <script>
        // Elements
        const subtotalEl = document.getElementById('subtotal');
        const grandTotalEl = document.getElementById('grandTotal');
        const selectedOngkirEl = document.getElementById('selectedOngkir');
        const appliedDiscountEl = document.getElementById('applied-discount');
        const couponCodeInput = document.getElementById('coupon-code-input');

        const address = document.getElementById('address');
        const province = document.getElementById('province');
        const city = document.getElementById('city');
        const district = document.getElementById('district');
        const ongkirResult = document.getElementById('ongkirResult');

        // Get subtotal value
        function getSubtotal() {
            return parseInt(subtotalEl.innerText.replace(/\./g, ''));
        }

        // ⭐ APPLY KUPON
        document.getElementById('apply-coupon').addEventListener('click', async function() {
            const code = document.getElementById('coupon-code').value.trim();
            const subtotal = getSubtotal();
            
            if (!code) {
                alert('Masukkan kode kupon');
                return;
            }

            try {
                const response = await fetch('/coupons/validate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        code: code,
                        amount: subtotal
                    })
                });

                const data = await response.json();
                const messageEl = document.getElementById('coupon-message');
                
                if (data.valid) {
                    // Simpan data kupon
                    couponCodeInput.value = code;
                    appliedDiscountEl.value = data.discount;
                    
                    // Tampilkan info diskon
                    document.getElementById('discount-info').style.display = 'block';
                    document.getElementById('coupon-name').textContent = code;
                    document.getElementById('discount-amount').textContent = data.discount.toLocaleString('id-ID');
                    
                    // Update summary
                    document.getElementById('summary-discount-row').style.display = 'flex';
                    document.getElementById('summary-discount').textContent = data.discount.toLocaleString('id-ID');
                    
                    messageEl.innerHTML = '<small class="text-success">✅ Kupon berhasil diterapkan!</small>';
                    
                    // Disable input setelah berhasil
                    document.getElementById('coupon-code').disabled = true;
                    this.disabled = true;
                    
                    // Update total
                    updateTotal();
                } else {
                    messageEl.innerHTML = `<small class="text-danger">❌ ${data.message}</small>`;
                }
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('coupon-message').innerHTML = 
                    '<small class="text-danger">❌ Terjadi kesalahan sistem</small>';
            }
        });

        // ⭐ HAPUS KUPON
        document.getElementById('remove-coupon').addEventListener('click', function() {
            // Reset kupon
            couponCodeInput.value = '';
            appliedDiscountEl.value = '0';
            document.getElementById('coupon-code').value = '';
            document.getElementById('coupon-code').disabled = false;
            document.getElementById('apply-coupon').disabled = false;
            
            // Hide info
            document.getElementById('discount-info').style.display = 'none';
            document.getElementById('summary-discount-row').style.display = 'none';
            document.getElementById('coupon-message').innerHTML = '';
            
            // Update total
            updateTotal();
        });

        // PROVINSI → KOTA
        province.addEventListener('change', async function() {
            if (!this.value) return;
            
            city.disabled = true;
            district.disabled = true;
            
            try {
                const response = await fetch(`/cities/${this.value}`);
                const data = await response.json();
                
                city.innerHTML = '<option value="">Pilih Kota</option>';
                district.innerHTML = '<option value="">Pilih Kecamatan</option>';
                
                data.forEach(c => {
                    city.innerHTML += `<option value="${c.id}">${c.name}</option>`;
                });
                
                city.disabled = false;
            } catch (error) {
                console.error('Error loading cities:', error);
                alert('Gagal memuat data kota');
            }
        });

        // KOTA → KECAMATAN
        city.addEventListener('change', async function() {
            if (!this.value) return;
            
            district.disabled = true;
            
            try {
                const response = await fetch(`/districts/${this.value}`);
                const data = await response.json();
                
                district.innerHTML = '<option value="">Pilih Kecamatan</option>';
                
                data.forEach(d => {
                    district.innerHTML += `<option value="${d.id}">${d.name}</option>`;
                });
                
                district.disabled = false;
            } catch (error) {
                console.error('Error loading districts:', error);
                alert('Gagal memuat data kecamatan');
            }
        });

        // CEK ONGKIR
        document.getElementById('cekOngkir').addEventListener('click', async function() {
            const districtId = district.value;
            const courier = document.querySelector('input[name="courier"]:checked')?.value;
            const weight = document.getElementById('weight').value;

            if (!districtId) {
                alert('Pilih kecamatan terlebih dahulu');
                return;
            }
            
            if (!courier) {
                alert('Pilih kurir terlebih dahulu');
                return;
            }

            try {
                const response = await fetch('/check-ongkir', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: new URLSearchParams({
                        district_id: districtId,
                        courier: courier,
                        weight: weight
                    })
                });

                const data = await response.json();

                if (!Array.isArray(data) || data.length === 0) {
                    ongkirResult.innerHTML = '<div class="alert alert-warning">Ongkir tidak ditemukan untuk kurir ini</div>';
                    return;
                }

                let html = `<div class="fw-bold mb-2">Pilih Layanan Pengiriman</div>`;

                data.forEach((item, i) => {
                    html += `
                    <label class="ongkir-card">
                        <input type="radio" name="ongkir" 
                               value="${item.cost}" 
                               data-service="${item.service}"
                               ${i === 0 ? 'checked' : ''}>
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="fw-semibold">${item.name} - ${item.service}</div>
                                <small class="text-muted">Estimasi ${item.etd}</small>
                            </div>
                            <div class="fw-bold text-pink">
                                Rp ${item.cost.toLocaleString('id-ID')}
                            </div>
                        </div>
                    </label>`;
                });

                ongkirResult.innerHTML = html;

                // Set ongkir pertama
                const firstOngkir = document.querySelector('input[name="ongkir"]:checked');
                selectedOngkirEl.value = firstOngkir.value;
                document.getElementById('selectedService').value = firstOngkir.dataset.service;
                document.getElementById('selectedCourier').value = courier;
                
                updateTotal();

                // Event listener untuk radio ongkir
                document.querySelectorAll('input[name="ongkir"]').forEach(radio => {
                    radio.addEventListener('change', () => {
                        selectedOngkirEl.value = radio.value;
                        document.getElementById('selectedService').value = radio.dataset.service;
                        updateTotal();
                    });
                });
            } catch (error) {
                console.error('Error:', error);
                alert('Gagal mengecek ongkir');
            }
        });

        // ⭐ UPDATE TOTAL (WITH DISCOUNT)
        function updateTotal() {
            const subtotal = getSubtotal();
            const discount = parseInt(appliedDiscountEl.value) || 0;
            const ongkir = parseInt(selectedOngkirEl.value) || 0;
            const total = subtotal - discount + ongkir;

            // Update grand total
            grandTotalEl.innerText = total.toLocaleString('id-ID');
            
            // Update summary
            document.getElementById('summary-subtotal').textContent = subtotal.toLocaleString('id-ID');
            
            if (ongkir > 0) {
                document.getElementById('summary-ongkir-row').style.display = 'flex';
                document.getElementById('summary-ongkir').textContent = ongkir.toLocaleString('id-ID');
            } else {
                document.getElementById('summary-ongkir-row').style.display = 'none';
            }
        }

        // ⭐ CHECK PAYMENT STATUS (untuk memastikan status terupdate)
        async function checkPaymentStatus(orderCode) {
            try {
                console.log('🔍 Checking payment status for:', orderCode);
                
                const response = await fetch("{{ route('payment.checkStatus') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        order_code: orderCode
                    })
                });

                const data = await response.json();

                if (data.success) {
                    console.log('✅ Status checked:', data.status);
                    
                    if (data.status === 'paid') {
                        // Tampilkan notifikasi sukses
                        alert('🎉 Pembayaran berhasil! Order Anda sedang diproses.');
                    }
                } else {
                    console.warn('⚠️ Check status failed:', data.message);
                }
                
                // Redirect ke orders
                window.location.href = "/orders";
                
            } catch (error) {
                console.error('❌ Error checking status:', error);
                // Tetap redirect meskipun error
                window.location.href = "/orders";
            }
        }

        // ⭐ MIDTRANS PAYMENT
        document.getElementById('pay-button').addEventListener('click', async function() {
            // Validasi
            if (!address.value.trim()) {
                alert('Silakan isi alamat lengkap pengiriman');
                address.focus();
                return;
            }

            if (selectedOngkirEl.value == 0) {
                alert('Silakan cek dan pilih ongkir terlebih dahulu');
                return;
            }

            const courier = document.querySelector('input[name="courier"]:checked');
            if (!courier) {
                alert('Silakan pilih kurir');
                return;
            }

            const payButton = this;
            payButton.disabled = true;
            payButton.innerHTML = '⏳ Memproses...';

            try {
                const response = await fetch("{{ route('midtrans.token') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        address: address.value,
                        ongkir: selectedOngkirEl.value,
                        courier: courier.value,
                        coupon_code: couponCodeInput.value || null
                    })
                });

                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.message || 'Gagal mendapatkan token pembayaran');
                }

                const data = await response.json();

                if (!data.snap_token) {
                    throw new Error('Token pembayaran tidak ditemukan');
                }

                console.log('🎫 Snap token received, order_code:', data.order_code);

                // ⭐ Open Midtrans Snap dengan callback lengkap
                snap.pay(data.snap_token, {
                    onSuccess: function(result) {
                        console.log('✅ Payment success:', result);
                        // Check status untuk memastikan terupdate
                        checkPaymentStatus(data.order_code);
                    },
                    onPending: function(result) {
                        console.log('⏳ Payment pending:', result);
                        window.location.href = "/orders";
                    },
                    onError: function(result) {
                        console.error('❌ Payment error:', result);
                        alert("Pembayaran gagal. Silakan coba lagi.");
                        window.location.href = "/orders";
                    },
                    onClose: function() {
                        console.log('🚪 Payment popup closed');
                        payButton.disabled = false;
                        payButton.innerHTML = '💳 Bayar Sekarang';
                    }
                });

            } catch (error) {
                console.error('❌ Error:', error);
                alert('Terjadi kesalahan: ' + error.message);
                payButton.disabled = false;
                payButton.innerHTML = '💳 Bayar Sekarang';
            }
        });
    </script>

    <style>
        .ongkir-card {
            border: 2px solid #f3c2d4;
            background: #fff5f8;
            padding: 12px;
            border-radius: 12px;
            display: block;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .ongkir-card input[type="radio"] {
            display: none;
        }

        .ongkir-card:has(input:checked) {
            background: #ffe6ee;
            border-color: #e91e63;
        }

        .ongkir-card:hover {
            background: #ffe6ee;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(246, 165, 192, 0.3);
        }

        .text-pink {
            color: #e91e63;
        }

        .pastel-btn {
            background: #f6a5c0;
            color: #fff;
            border: none;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .pastel-btn:hover:not(:disabled) {
            background: #e91e63;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(246, 165, 192, 0.4);
        }

        .pastel-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .pastel-outline {
            border: 2px solid #f6a5c0;
            color: #f6a5c0;
            background: white;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .pastel-outline:hover {
            background: #f6a5c0;
            color: white;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #f6a5c0;
            box-shadow: 0 0 0 0.2rem rgba(246, 165, 192, 0.25);
        }
    </style>
@endsection