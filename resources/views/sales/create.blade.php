<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="section-kicker">Caja</p>
                <h2 class="page-title mt-2">Nueva venta</h2>
                <p class="page-subtitle mt-3">Registra productos por unidad o peso. El stock se descuenta automaticamente al confirmar.</p>
            </div>
            <a href="{{ route('sales.index') }}" class="btn-secondary">Volver al registro</a>
        </div>
    </x-slot>

    @php
        $oldItems = old('items', [['product_id' => '', 'quantity' => '1', 'pricing_mode' => 'standard']]);
    @endphp

    <div class="space-y-6">
        @if (! $currentRegister)
            <div class="notice-error">
                Debes abrir caja antes de registrar ventas. Puedes hacerlo desde <a href="{{ route('cash-registers.index') }}" class="action-link">apertura y cierre de caja</a>.
            </div>
        @endif

        @if ($errors->any())
            <div class="notice-error">
                <p class="font-semibold">Hay datos por corregir:</p>
                <ul class="mt-2 space-y-1 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('sales.store') }}" class="form-card space-y-6" id="sale-form">
            @csrf

            <section class="grid gap-4 lg:grid-cols-4">
                <div class="metric-card !shadow-none lg:col-span-1">
                    <p class="metric-label">Caja actual</p>
                    <p class="metric-value text-2xl">{{ $currentRegister ? 'Abierta' : 'Cerrada' }}</p>
                    <p class="metric-meta">{{ $currentRegister ? 'Fondo inicial S/ ' . number_format((float) $currentRegister->opening_amount, 2) : 'Sin apertura activa' }}</p>
                </div>

                <div class="lg:col-span-3 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <label class="field-label">Cliente</label>
                        <input type="text" name="customer_name" value="{{ old('customer_name') }}" class="field-input" placeholder="Opcional o requerido en credito">
                    </div>

                    <div>
                        <label class="field-label">Celular</label>
                        <input type="text" name="customer_phone" value="{{ old('customer_phone') }}" class="field-input" placeholder="Numero del cliente">
                    </div>

                    <div>
                        <label class="field-label">Metodo de pago</label>
                        <select name="payment_method" class="field-input" id="payment-method">
                            <option value="cash" @selected(old('payment_method', 'cash') === 'cash')>Efectivo</option>
                            <option value="qr" @selected(old('payment_method') === 'qr')>QR</option>
                            <option value="mixed" @selected(old('payment_method') === 'mixed')>Mixto</option>
                            <option value="credit" @selected(old('payment_method') === 'credit')>Credito</option>
                        </select>
                    </div>

                    <div>
                        <label class="field-label">Descuento final</label>
                        <input type="number" step="0.01" min="0" name="discount_amount" value="{{ old('discount_amount', 0) }}" class="field-input" id="discount-amount">
                    </div>
                </div>
            </section>

            <div class="overflow-x-auto">
                <table class="table-shell">
                    <thead class="table-head">
                        <tr>
                            <th class="table-head-cell">Producto</th>
                            <th class="table-head-cell">Modo</th>
                            <th class="table-head-cell">Cantidad</th>
                            <th class="table-head-cell">Precio</th>
                            <th class="table-head-cell">Subtotal</th>
                            <th class="table-head-cell">Accion</th>
                        </tr>
                    </thead>
                    <tbody id="items-body">
                        @foreach ($oldItems as $index => $item)
                            <tr class="table-row sale-item-row">
                                <td class="table-cell">
                                    <select name="items[{{ $index }}][product_id]" class="field-input product-select" required>
                                        <option value="">Seleccione producto</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}"
                                                data-price="{{ $product->price }}"
                                                data-package-price="{{ $product->package_price }}"
                                                data-package-name="{{ $product->package_name }}"
                                                data-package-enabled="{{ $product->supportsPackageSale() ? 1 : 0 }}"
                                                data-units-per-package="{{ $product->units_per_package }}"
                                                data-sale-type="{{ $product->sale_type }}"
                                                data-weight-unit="{{ $product->weight_unit }}"
                                                data-stock="{{ $product->stock }}"
                                                @selected((string) ($item['product_id'] ?? '') === (string) $product->id)
                                            >
                                                {{ $product->name }} - S/ {{ number_format((float) $product->price, 2) }} - Stock {{ $product->stockBreakdownLabel() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="table-cell">
                                    <select name="items[{{ $index }}][pricing_mode]" class="field-input pricing-mode">
                                        <option value="standard" @selected(($item['pricing_mode'] ?? 'standard') === 'standard')>Normal</option>
                                        <option value="package" @selected(($item['pricing_mode'] ?? '') === 'package')>Paquete</option>
                                    </select>
                                </td>
                                <td class="table-cell">
                                    <input type="number" min="0.001" step="0.001" name="items[{{ $index }}][quantity]" value="{{ $item['quantity'] ?? '' }}" class="field-input quantity-input" required>
                                    <p class="field-help quantity-help">Si es por unidad ingresa solo enteros.</p>
                                </td>
                                <td class="table-cell">
                                    <p class="font-semibold text-slate-900 unit-price">S/ 0.00</p>
                                </td>
                                <td class="table-cell">
                                    <p class="font-semibold text-slate-900 line-total">S/ 0.00</p>
                                </td>
                                <td class="table-cell">
                                    <button type="button" class="action-link-danger remove-row">Quitar</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <button type="button" id="add-item" class="btn-secondary">Agregar item</button>

            <section class="grid gap-4 lg:grid-cols-2">
                <div>
                    <label class="field-label">Nota (opcional)</label>
                    <input type="text" name="notes" value="{{ old('notes') }}" class="field-input" placeholder="Observacion de caja">
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    <div>
                        <label class="field-label">Monto en efectivo</label>
                        <input type="number" step="0.01" min="0" name="cash_amount" value="{{ old('cash_amount', 0) }}" class="field-input" id="cash-amount">
                    </div>

                    <div>
                        <label class="field-label">Monto QR</label>
                        <input type="number" step="0.01" min="0" name="qr_amount" value="{{ old('qr_amount', 0) }}" class="field-input" id="qr-amount">
                    </div>

                    <div>
                        <label class="field-label">Efectivo recibido</label>
                        <input type="number" step="0.01" min="0" name="cash_received" value="{{ old('cash_received', 0) }}" class="field-input" id="cash-received">
                    </div>
                </div>
            </section>

            <div class="shell-panel p-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="metric-label">Total venta</p>
                    <p class="metric-value !mt-1" id="sale-total">S/ 0.00</p>
                    <p class="mt-2 text-sm text-slate-500">Cambio sugerido: <span class="font-semibold text-slate-900" id="change-total">S/ 0.00</span></p>
                </div>

                <div class="module-actions">
                    <a href="{{ route('sales.index') }}" class="btn-secondary">Cancelar</a>
                    <button class="btn-primary" @disabled(! $currentRegister)>Registrar venta</button>
                </div>
            </div>
        </form>
    </div>

    <template id="sale-item-template">
        <tr class="table-row sale-item-row">
            <td class="table-cell">
                <select class="field-input product-select" required>
                    <option value="">Seleccione producto</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}"
                            data-price="{{ $product->price }}"
                            data-package-price="{{ $product->package_price }}"
                            data-package-name="{{ $product->package_name }}"
                            data-package-enabled="{{ $product->supportsPackageSale() ? 1 : 0 }}"
                            data-units-per-package="{{ $product->units_per_package }}"
                            data-sale-type="{{ $product->sale_type }}"
                            data-weight-unit="{{ $product->weight_unit }}"
                            data-stock="{{ $product->stock }}"
                        >
                            {{ $product->name }} - S/ {{ number_format((float) $product->price, 2) }} - Stock {{ $product->stockBreakdownLabel() }}
                        </option>
                    @endforeach
                </select>
            </td>
            <td class="table-cell">
                <select class="field-input pricing-mode">
                    <option value="standard">Normal</option>
                    <option value="package">Paquete</option>
                </select>
            </td>
            <td class="table-cell">
                <input type="number" min="0.001" step="0.001" class="field-input quantity-input" required>
                <p class="field-help quantity-help">Si es por unidad ingresa solo enteros.</p>
            </td>
            <td class="table-cell">
                <p class="font-semibold text-slate-900 unit-price">S/ 0.00</p>
            </td>
            <td class="table-cell">
                <p class="font-semibold text-slate-900 line-total">S/ 0.00</p>
            </td>
            <td class="table-cell">
                <button type="button" class="action-link-danger remove-row">Quitar</button>
            </td>
        </tr>
    </template>

    <script>
        (function () {
            const body = document.getElementById('items-body');
            const addBtn = document.getElementById('add-item');
            const template = document.getElementById('sale-item-template');
            const totalEl = document.getElementById('sale-total');
            const changeEl = document.getElementById('change-total');
            const paymentMethodEl = document.getElementById('payment-method');
            const discountEl = document.getElementById('discount-amount');
            const cashAmountEl = document.getElementById('cash-amount');
            const qrAmountEl = document.getElementById('qr-amount');
            const cashReceivedEl = document.getElementById('cash-received');

            const formatMoney = (value) => `S/ ${Number(value).toFixed(2)}`;

            function updateRowNames() {
                [...body.querySelectorAll('.sale-item-row')].forEach((row, index) => {
                    const product = row.querySelector('.product-select');
                    const pricingMode = row.querySelector('.pricing-mode');
                    const quantity = row.querySelector('.quantity-input');

                    product.name = `items[${index}][product_id]`;
                    pricingMode.name = `items[${index}][pricing_mode]`;
                    quantity.name = `items[${index}][quantity]`;
                });
            }

            function updateRowTotals(row) {
                const productSelect = row.querySelector('.product-select');
                const pricingModeSelect = row.querySelector('.pricing-mode');
                const quantityInput = row.querySelector('.quantity-input');
                const unitPriceEl = row.querySelector('.unit-price');
                const lineTotalEl = row.querySelector('.line-total');
                const quantityHelp = row.querySelector('.quantity-help');

                const selectedOption = productSelect.options[productSelect.selectedIndex];
                const saleType = selectedOption?.dataset?.saleType || 'unit';
                const unit = selectedOption?.dataset?.weightUnit || '';
                const packageEnabled = Number(selectedOption?.dataset?.packageEnabled || 0) === 1;
                const packagePrice = Number(selectedOption?.dataset?.packagePrice || 0);
                const packageName = selectedOption?.dataset?.packageName || 'paquete';
                const pricingMode = pricingModeSelect.value === 'package' && packageEnabled ? 'package' : 'standard';
                pricingModeSelect.value = pricingMode;
                pricingModeSelect.disabled = !packageEnabled;
                const unitPrice = pricingMode === 'package' ? packagePrice : Number(selectedOption?.dataset?.price || 0);
                const quantity = Number(quantityInput.value || 0);

                if (pricingMode === 'package') {
                    quantityInput.step = '1';
                    quantityInput.min = '1';
                    quantityHelp.textContent = `Venta por ${packageName}: solo enteros.`;
                } else if (saleType === 'unit') {
                    quantityInput.step = '1';
                    quantityInput.min = '1';
                    quantityHelp.textContent = 'Producto por unidad: solo enteros.';
                } else {
                    quantityInput.step = '0.001';
                    quantityInput.min = '0.001';
                    quantityHelp.textContent = `Producto por peso (${unit || 'kg'}): admite decimales.`;
                }

                const lineTotal = unitPrice * quantity;
                unitPriceEl.textContent = formatMoney(unitPrice);
                lineTotalEl.textContent = formatMoney(lineTotal);
            }

            function updateSaleTotal() {
                let total = 0;
                body.querySelectorAll('.sale-item-row').forEach((row) => {
                    const productSelect = row.querySelector('.product-select');
                    const pricingModeSelect = row.querySelector('.pricing-mode');
                    const quantityInput = row.querySelector('.quantity-input');
                    const selectedOption = productSelect.options[productSelect.selectedIndex];
                    const packageEnabled = Number(selectedOption?.dataset?.packageEnabled || 0) === 1;
                    const pricingMode = pricingModeSelect.value === 'package' && packageEnabled ? 'package' : 'standard';
                    const unitPrice = pricingMode === 'package'
                        ? Number(selectedOption?.dataset?.packagePrice || 0)
                        : Number(selectedOption?.dataset?.price || 0);
                    const quantity = Number(quantityInput.value || 0);
                    total += unitPrice * quantity;
                });

                const discount = Number(discountEl.value || 0);
                const netTotal = Math.max(total - discount, 0);
                totalEl.textContent = formatMoney(netTotal);
                updatePaymentFields(netTotal);
            }

            function updatePaymentFields(total) {
                const method = paymentMethodEl.value;

                if (method === 'cash') {
                    cashAmountEl.value = total.toFixed(2);
                    qrAmountEl.value = '0.00';
                } else if (method === 'qr') {
                    cashAmountEl.value = '0.00';
                    qrAmountEl.value = total.toFixed(2);
                    cashReceivedEl.value = '0.00';
                } else if (method === 'credit') {
                    cashAmountEl.value = '0.00';
                    qrAmountEl.value = '0.00';
                    cashReceivedEl.value = '0.00';
                }

                const cashAmount = Number(cashAmountEl.value || 0);
                const cashReceived = Number(cashReceivedEl.value || 0);
                const change = Math.max(cashReceived - cashAmount, 0);
                changeEl.textContent = formatMoney(change);
            }

            function refreshRow(row) {
                updateRowTotals(row);
                updateSaleTotal();
            }

            function bindRow(row) {
                row.querySelector('.product-select').addEventListener('change', () => refreshRow(row));
                row.querySelector('.pricing-mode').addEventListener('change', () => refreshRow(row));
                row.querySelector('.quantity-input').addEventListener('input', () => refreshRow(row));
                row.querySelector('.remove-row').addEventListener('click', () => {
                    if (body.querySelectorAll('.sale-item-row').length === 1) {
                        return;
                    }
                    row.remove();
                    updateRowNames();
                    updateSaleTotal();
                });
                refreshRow(row);
            }

            addBtn.addEventListener('click', () => {
                const fragment = template.content.cloneNode(true);
                const row = fragment.querySelector('.sale-item-row');
                body.appendChild(row);
                updateRowNames();
                bindRow(row);
            });

            [paymentMethodEl, discountEl, cashAmountEl, qrAmountEl, cashReceivedEl].forEach((element) => {
                element.addEventListener('input', () => updateSaleTotal());
                element.addEventListener('change', () => updateSaleTotal());
            });

            body.querySelectorAll('.sale-item-row').forEach(bindRow);
            updateRowNames();
            updateSaleTotal();
        })();
    </script>
</x-app-layout>
