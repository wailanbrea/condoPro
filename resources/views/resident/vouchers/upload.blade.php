@extends('layouts.resident')

@section('content')
<section class="space-y-lg">
    {{-- Header --}}
    <div class="flex items-center gap-sm">
        <span class="material-symbols-outlined text-primary text-3xl">cloud_upload</span>
        <h1 class="font-headline-lg text-headline-lg text-on-surface">{{ __('messages.resident.upload_voucher_title') }}</h1>
    </div>

    {{-- Form --}}
    <form action="{{ route('resident.vouchers.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow-sm border border-outline-variant/30 overflow-hidden">
        @csrf
        <div class="px-lg py-md bg-surface-container-low border-b border-outline-variant/20">
            <h2 class="font-headline-md text-headline-md text-on-surface flex items-center gap-sm">
                <span class="material-symbols-outlined text-primary">receipt_long</span>
                {{ __('messages.payments.voucher') }}
            </h2>
        </div>

        <div class="p-lg space-y-lg">
            {{-- Payment Mode Toggle --}}
            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-sm">{{ app()->getLocale() === 'es' ? 'Modo de pago' : 'Payment mode' }}</label>
                <div class="flex rounded-lg overflow-hidden border border-outline-variant/50">
                    <button type="button" id="modeSingle" onclick="setMode('single')"
                        class="flex-1 flex items-center justify-center gap-sm px-md py-sm font-body-md transition-colors bg-primary text-white">
                        <span class="material-symbols-outlined text-lg">receipt</span>
                        {{ app()->getLocale() === 'es' ? 'Factura individual' : 'Single bill' }}
                    </button>
                    <button type="button" id="modeMultiple" onclick="setMode('multiple')"
                        class="flex-1 flex items-center justify-center gap-sm px-md py-sm font-body-md transition-colors bg-white text-on-surface-variant">
                        <span class="material-symbols-outlined text-lg">checklist</span>
                        {{ app()->getLocale() === 'es' ? 'Varias facturas' : 'Multiple bills' }}
                    </button>
                    <button type="button" id="modeAdvance" onclick="setMode('advance')"
                        class="flex-1 flex items-center justify-center gap-sm px-md py-sm font-body-md transition-colors bg-white text-on-surface-variant">
                        <span class="material-symbols-outlined text-lg">schedule</span>
                        {{ app()->getLocale() === 'es' ? 'Pago por adelantado' : 'Advance payment' }}
                    </button>
                </div>
            </div>

            {{-- Single Bill Select --}}
            <div id="singleSelect">
                <label for="bill_id_single" class="block font-label-caps text-label-caps text-on-surface-variant mb-xs">{{ app()->getLocale() === 'es' ? 'Seleccionar factura' : 'Select bill' }}</label>
                <select id="bill_id_single" name="bill_id_single"
                    class="w-full px-md py-sm border border-outline-variant/50 rounded-lg bg-white text-on-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors"
                    onchange="updateSingleAmount()">
                    <option value="">— {{ app()->getLocale() === 'es' ? 'Seleccione una factura' : 'Select a bill' }} —</option>
                    @foreach($bills as $bill)
                        @php $remaining = max(0, $bill->total - $bill->payments_applied); @endphp
                        <option value="{{ $bill->id }}" data-remaining="{{ $remaining }}"
                            data-total="{{ $bill->total }}" data-paid="{{ $bill->payments_applied }}">
                            {{ \Carbon\Carbon::create($bill->billing_year, $bill->billing_month, 1)->translatedFormat('F Y') }}
                            — RD${{ number_format($remaining, 2) }}
                            @if($bill->status === 'partial') ({{ app()->getLocale() === 'es' ? 'Parcial' : 'Partial' }}) @endif
                        </option>
                    @endforeach
                </select>
                <div id="singleBillDetail" class="hidden mt-sm p-md bg-surface-container-low rounded-lg">
                    <div class="flex flex-col gap-xs">
                        <span class="text-body-sm text-on-surface-variant" id="singleBillLabel"></span>
                        <div class="flex items-baseline gap-xs">
                            <span class="font-mono-data text-2xl text-primary font-bold whitespace-nowrap" id="singleBillAmount"></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Multiple Bill Select --}}
            <div id="multiSelect" class="hidden">
                <div class="flex items-center justify-between mb-xs">
                    <label class="font-label-caps text-label-caps text-on-surface-variant">{{ app()->getLocale() === 'es' ? 'Seleccionar facturas' : 'Select bills' }}</label>
                    <button type="button" onclick="toggleAllBills()" class="text-primary text-body-sm font-semibold hover:underline">
                        <span class="material-symbols-outlined text-sm align-middle">select_all</span>
                        {{ app()->getLocale() === 'es' ? 'Seleccionar todas' : 'Select all' }}
                    </button>
                </div>
                @if($bills->isEmpty())
                    <p class="text-on-surface-variant text-body-md py-md">{{ app()->getLocale() === 'es' ? 'No hay facturas pendientes.' : 'No pending bills.' }}</p>
                @else
                    <div class="space-y-sm max-h-64 overflow-y-auto border border-outline-variant/50 rounded-lg p-sm">
                        @foreach($bills as $bill)
                            @php $remaining = max(0, $bill->total - $bill->payments_applied); @endphp
                            <label class="flex items-start gap-sm p-sm rounded-lg hover:bg-surface-container-low transition-colors cursor-pointer">
                                <input type="checkbox" name="bill_ids[]" value="{{ $bill->id }}"
                                    class="bill-checkbox mt-1 w-5 h-5 rounded border-outline-variant text-primary focus:ring-primary-container"
                                    data-remaining="{{ $remaining }}"
                                    onchange="updateTotalAmount()">
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <span class="font-body-md text-on-surface font-semibold">
                                            {{ \Carbon\Carbon::create($bill->billing_year, $bill->billing_month, 1)->translatedFormat('F Y') }}
                                        </span>
                                        <span class="font-mono-data text-on-surface font-bold">RD${{ number_format($remaining, 2) }}</span>
                                    </div>
                                    <div class="flex items-center gap-sm text-body-sm text-on-surface-variant">
                                        @if($bill->status === 'partial')
                                            <span class="inline-flex items-center px-xs py-0.5 rounded bg-amber-100 text-amber-800 text-xs font-semibold">{{ app()->getLocale() === 'es' ? 'Parcial' : 'Partial' }}</span>
                                            <span>{{ app()->getLocale() === 'es' ? 'Pagado: RD$' : 'Paid: RD$' }}{{ number_format($bill->payments_applied, 2) }}</span>
                                        @else
                                            <span class="inline-flex items-center px-xs py-0.5 rounded bg-red-100 text-red-800 text-xs font-semibold">{{ app()->getLocale() === 'es' ? 'Pendiente' : 'Pending' }}</span>
                                        @endif
                                        <span class="text-xs">{{ app()->getLocale() === 'es' ? 'Total: RD$' : 'Total: RD$' }}{{ number_format($bill->total, 2) }}</span>
                                    </div>
                                    <div class="text-body-xs text-on-surface-variant/70 mt-0.5">
                                        @foreach($bill->billItems as $item)
                                            {{ $item->description }} (RD${{ number_format($item->amount, 2) }})@if(!$loop->last), @endif
                                        @endforeach
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    <div id="totalSelectedInfo" class="hidden mt-sm p-md bg-primary/5 border border-primary/20 rounded-lg">
                        <div class="flex items-center justify-between">
                            <span class="font-label-caps text-label-caps text-on-surface-variant">{{ app()->getLocale() === 'es' ? 'Total seleccionado' : 'Total selected' }}</span>
                            <span id="totalSelected" class="font-mono-data text-headline-md text-primary font-bold">RD$0.00</span>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Advance Payment --}}
            <div id="advanceSelect" class="hidden">
                <div class="bg-primary/5 border border-primary/20 rounded-lg p-md">
                    <div class="flex items-start gap-sm">
                        <span class="material-symbols-outlined text-primary text-2xl flex-shrink-0">schedule</span>
                        <div>
                            <h4 class="font-title-lg text-on-surface">{{ app()->getLocale() === 'es' ? 'Pago por adelantado' : 'Advance Payment' }}</h4>
                            <p class="text-body-sm text-on-surface-variant mt-xs">
                                {{ app()->getLocale() === 'es' ? 'Realiza un pago antes de que se genere la factura. Este monto se aplicará automáticamente a tu próxima factura cuando sea generada.' : 'Make a payment before the bill is generated. This amount will be automatically applied to your next bill when it is generated.' }}
                            </p>
                            @if($nextBillPreview ?? false)
                            <div class="mt-sm p-sm bg-white rounded border border-outline-variant/30">
                                <div class="flex justify-between items-center">
                                    <span class="text-body-sm text-on-surface-variant">{{ app()->getLocale() === 'es' ? 'Próxima factura esperada' : 'Next expected bill' }}</span>
                                    <span class="font-mono-data font-bold text-primary">RD${{ number_format($nextBillPreview['total'], 2) }}</span>
                                </div>
                                <div class="text-xs text-on-surface-variant mt-xs">
                                    {{ $nextBillPreview['billing_date']->format('d M Y') }} · {{ $nextBillPreview['days_until'] }} {{ app()->getLocale() === 'es' ? 'días' : 'days' }}
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Amount --}}
            <div>
                <label for="amount" class="block font-label-caps text-label-caps text-on-surface-variant mb-xs">{{ __('messages.resident.amount') }} <span class="text-error">*</span></label>
                <div class="relative">
                    <span class="absolute left-md top-1/2 -translate-y-1/2 font-mono-data text-on-surface-variant">RD$</span>
                    <input type="number" id="amount" name="amount" step="0.01" min="0" required
                        class="w-full pl-xl pr-md py-sm border border-outline-variant/50 rounded-lg bg-white text-on-surface font-mono-data focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors"
                        placeholder="0.00">
                    <p id="amountHint" class="text-body-sm text-on-surface-variant mt-xs hidden"></p>
                </div>
                @error('amount')
                    <p class="text-error text-body-sm mt-xs">{{ $message }}</p>
                @enderror
            </div>

            {{-- Payment Date & Reference Row --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-lg">
                <div>
                    <label for="payment_date" class="block font-label-caps text-label-caps text-on-surface-variant mb-xs">{{ __('messages.resident.payment_date') }} <span class="text-error">*</span></label>
                    <input type="date" id="payment_date" name="payment_date" required
                        class="w-full px-md py-sm border border-outline-variant/50 rounded-lg bg-white text-on-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors"
                        value="{{ old('payment_date', now()->format('Y-m-d')) }}">
                    @error('payment_date')
                        <p class="text-error text-body-sm mt-xs">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="reference_number" class="block font-label-caps text-label-caps text-on-surface-variant mb-xs">{{ __('messages.resident.reference_number') }}</label>
                    <input type="text" id="reference_number" name="reference_number"
                        class="w-full px-md py-sm border border-outline-variant/50 rounded-lg bg-white text-on-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors"
                        placeholder="{{ __('messages.resident.reference_number') }}">
                    @error('reference_number')
                        <p class="text-error text-body-sm mt-xs">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Bank Account --}}
            <div>
                <label for="bank_account_id" class="block font-label-caps text-label-caps text-on-surface-variant mb-xs">{{ __('messages.resident.bank_account') }}</label>
                <select id="bank_account_id" name="bank_account_id" class="w-full px-md py-sm border border-outline-variant/50 rounded-lg bg-white text-on-surface font-body-md focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-colors">
                    <option value="">— {{ __('messages.resident.bank_account') }} —</option>
                    @foreach ($bankAccounts as $bankAccount)
                        <option value="{{ $bankAccount->id }}">
                            {{ $bankAccount->bank_name }} — {{ $bankAccount->account_number }}
                        </option>
                    @endforeach
                </select>
                @error('bank_account_id')
                    <p class="text-error text-body-sm mt-xs">{{ $message }}</p>
                @enderror
            </div>

            {{-- File Upload --}}
            <div>
                <label class="block font-label-caps text-label-caps text-on-surface-variant mb-xs">{{ __('messages.resident.voucher_file') }} <span class="text-error">*</span></label>
                <div id="dropZone" class="relative border-2 border-dashed border-outline-variant rounded-xl p-xl text-center cursor-pointer transition-all hover:border-primary hover:bg-primary/5 group">
                    <input type="file" id="voucher_path" name="voucher_path" required
                        accept=".jpg,.jpeg,.png,.pdf"
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                    <div class="space-y-sm">
                        <span class="material-symbols-outlined text-5xl text-on-surface-variant group-hover:text-primary transition-colors">cloud_upload</span>
                        <p class="font-body-lg text-on-surface-variant group-hover:text-primary transition-colors">{{ __('messages.resident.drag_drop_voucher') }}</p>
                        <p class="text-body-sm text-on-surface-variant/70">{{ __('messages.resident.accepted_files') }}</p>
                    </div>
                </div>
                <p id="fileName" class="text-body-sm text-primary font-semibold mt-xs hidden"></p>
                @error('voucher_path')
                    <p class="text-error text-body-sm mt-xs">{{ $message }}</p>
                @enderror
            </div>

            {{-- Submit --}}
            <div class="flex justify-end pt-sm">
                <button type="submit"
                    class="flex items-center justify-center gap-sm bg-primary text-on-primary px-xl py-md rounded-lg font-bold shadow-sm hover:bg-primary-container hover:text-on-primary-container transition-colors active:scale-[0.98]">
                    <span class="material-symbols-outlined">cloud_upload</span>
                    {{ __('messages.resident.submit_voucher') }}
                </button>
            </div>
        </div>
    </form>
</section>

@push('scripts')
<script>
    var currentMode = 'single';

    function setMode(mode) {
        currentMode = mode;
        var singleEl = document.getElementById('singleSelect');
        var multiEl = document.getElementById('multiSelect');
        var advanceEl = document.getElementById('advanceSelect');
        var singleBtn = document.getElementById('modeSingle');
        var multiBtn = document.getElementById('modeMultiple');
        var advanceBtn = document.getElementById('modeAdvance');

        // Hide all panels
        [singleEl, multiEl, advanceEl].forEach(function(el) { if(el) el.classList.add('hidden'); });
        // Reset all buttons
        [singleBtn, multiBtn, advanceBtn].forEach(function(btn) { 
            if(btn) { btn.classList.remove('bg-primary', 'text-white'); btn.classList.add('bg-white', 'text-on-surface-variant'); }
        });

        if (mode === 'single') {
            singleEl.classList.remove('hidden');
            singleBtn.classList.add('bg-primary', 'text-white');
            singleBtn.classList.remove('bg-white', 'text-on-surface-variant');
            document.getElementById('amount').value = '';
            document.getElementById('amountHint').classList.add('hidden');
            updateSingleAmount();
        } else if (mode === 'multiple') {
            multiEl.classList.remove('hidden');
            multiBtn.classList.add('bg-primary', 'text-white');
            multiBtn.classList.remove('bg-white', 'text-on-surface-variant');
            document.getElementById('amount').value = '';
            updateTotalAmount();
        } else if (mode === 'advance') {
            advanceEl.classList.remove('hidden');
            advanceBtn.classList.add('bg-primary', 'text-white');
            advanceBtn.classList.remove('bg-white', 'text-on-surface-variant');
            document.getElementById('amount').value = '';
            document.getElementById('amountHint').classList.add('hidden');
        }
    }

    function updateSingleAmount() {
        if (currentMode !== 'single') return;
        var select = document.getElementById('bill_id_single');
        var option = select.options[select.selectedIndex];
        var amountField = document.getElementById('amount');
        var hintEl = document.getElementById('amountHint');
        var detailEl = document.getElementById('singleBillDetail');
        var labelEl = document.getElementById('singleBillLabel');
        var amountEl = document.getElementById('singleBillAmount');

        if (option && option.value) {
            var remaining = parseFloat(option.dataset.remaining) || 0;
            amountField.value = remaining.toFixed(2);
            detailEl.classList.remove('hidden');
            labelEl.textContent = option.textContent.trim();
            amountEl.textContent = 'RD$' + remaining.toFixed(2);
            hintEl.textContent = '{{ app()->getLocale() === "es" ? "Monto correspondiente a la factura seleccionada" : "Amount for the selected bill" }}';
            hintEl.classList.remove('hidden');
        } else {
            amountField.value = '';
            detailEl.classList.add('hidden');
            hintEl.classList.add('hidden');
        }
    }

    function updateTotalAmount() {
        if (currentMode !== 'multiple') return;
        var checkboxes = document.querySelectorAll('.bill-checkbox:checked');
        var total = 0;
        checkboxes.forEach(function(cb) {
            total += parseFloat(cb.dataset.remaining) || 0;
        });

        var amountField = document.getElementById('amount');
        var totalInfo = document.getElementById('totalSelectedInfo');
        var totalDisplay = document.getElementById('totalSelected');
        var hintEl = document.getElementById('amountHint');

        if (checkboxes.length > 0) {
            totalInfo.classList.remove('hidden');
            totalDisplay.textContent = 'RD$' + total.toFixed(2);
            amountField.value = total.toFixed(2);
            hintEl.textContent = '{{ app()->getLocale() === "es" ? "Monto total de las facturas seleccionadas" : "Total amount for selected bills" }}';
            hintEl.classList.remove('hidden');
        } else {
            totalInfo.classList.add('hidden');
            amountField.value = '';
            hintEl.classList.add('hidden');
        }
    }

    function toggleAllBills() {
        var checkboxes = document.querySelectorAll('.bill-checkbox');
        var allChecked = Array.from(checkboxes).every(function(cb) { return cb.checked; });
        checkboxes.forEach(function(cb) { cb.checked = !allChecked; });
        updateTotalAmount();
    }

    var voucherInput = document.getElementById('voucher_path');
    var fileNameEl = document.getElementById('fileName');
    var dropZone = document.getElementById('dropZone');
    var uploadForm = voucherInput.closest('form');
    var maxFileSize = 5 * 1024 * 1024;
    var allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];

    voucherInput.addEventListener('change', function () {
        if (this.files.length > 0) {
            var file = this.files[0];
            if (file.size > maxFileSize) { alert('El archivo no debe superar los 5MB.'); this.value = ''; return; }
            if (!allowedTypes.includes(file.type)) { alert('Solo se aceptan archivos JPG, PNG o PDF.'); this.value = ''; return; }
            fileNameEl.textContent = file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)';
            fileNameEl.classList.remove('hidden');
            dropZone.classList.add('border-primary', 'bg-primary/5');
        }
    });

    dropZone.addEventListener('dragover', function (e) { e.preventDefault(); this.classList.add('border-primary', 'bg-primary/5'); });
    dropZone.addEventListener('dragleave', function () { if (!voucherInput.files.length) { this.classList.remove('border-primary', 'bg-primary/5'); } });
    dropZone.addEventListener('drop', function (e) { e.preventDefault(); this.classList.remove('border-primary', 'bg-primary/5'); });

    uploadForm.addEventListener('submit', function(e) {
        var amountField = document.getElementById('amount');
        var amount = parseFloat(amountField.value);
        if (isNaN(amount) || amount <= 0) { e.preventDefault(); alert('{{ app()->getLocale() === "es" ? "Ingrese un monto válido mayor a 0." : "Enter a valid amount greater than 0." }}'); amountField.focus(); return; }
        if (!voucherInput.files.length) { e.preventDefault(); alert('{{ app()->getLocale() === "es" ? "Seleccione un archivo de voucher." : "Select a voucher file." }}'); return; }

        // In single mode, add hidden input for bill_id
        if (currentMode === 'single') {
            var selectEl = document.getElementById('bill_id_single');
            if (selectEl.value) {
                var hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'bill_ids[]';
                hiddenInput.value = selectEl.value;
                this.appendChild(hiddenInput);
            }
        }
    });
</script>
@endpush
@endsection