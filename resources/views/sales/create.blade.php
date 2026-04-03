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
        $oldItems = old('items', [['product_id' => '', 'quantity' => '1']]);
    @endphp

    <div class="space-y-6">
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

            <div class="overflow-x-auto">
                <table class="table-shell">
                    <thead class="table-head">
                        <tr>
                            <th class="table-head-cell">Producto</th>
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
                                                data-sale-type="{{ $product->sale_type }}"
                                                data-weight-unit="{{ $product->weight_unit }}"
                                                data-stock="{{ $product->stock }}"
                                                @selected((string) ($item['product_id'] ?? '') === (string) $product->id)
                                            >
                                                {{ $product->name }} - S/ {{ number_format((float) $product->price, 2) }} - Stock {{ $product->stock }} {{ $product->stockUnitLabel() }}
                                            </option>
                                        @endforeach
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

            <div>
                <label class="field-label">Nota (opcional)</label>
                <input type="text" name="notes" value="{{ old('notes') }}" class="field-input" placeholder="Observacion de caja">
            </div>

            <div class="shell-panel p-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="metric-label">Total venta</p>
                    <p class="metric-value !mt-1" id="sale-total">S/ 0.00</p>
                </div>

                <div class="module-actions">
                    <a href="{{ route('sales.index') }}" class="btn-secondary">Cancelar</a>
                    <button class="btn-primary">Registrar venta</button>
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
                            data-sale-type="{{ $product->sale_type }}"
                            data-weight-unit="{{ $product->weight_unit }}"
                            data-stock="{{ $product->stock }}"
                        >
                            {{ $product->name }} - S/ {{ number_format((float) $product->price, 2) }} - Stock {{ $product->stock }} {{ $product->stockUnitLabel() }}
                        </option>
                    @endforeach
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

            const formatMoney = (value) => `S/ ${Number(value).toFixed(2)}`;

            function updateRowNames() {
                [...body.querySelectorAll('.sale-item-row')].forEach((row, index) => {
                    const product = row.querySelector('.product-select');
                    const quantity = row.querySelector('.quantity-input');

                    product.name = `items[${index}][product_id]`;
                    quantity.name = `items[${index}][quantity]`;
                });
            }

            function updateRowTotals(row) {
                const productSelect = row.querySelector('.product-select');
                const quantityInput = row.querySelector('.quantity-input');
                const unitPriceEl = row.querySelector('.unit-price');
                const lineTotalEl = row.querySelector('.line-total');
                const quantityHelp = row.querySelector('.quantity-help');

                const selectedOption = productSelect.options[productSelect.selectedIndex];
                const unitPrice = Number(selectedOption?.dataset?.price || 0);
                const saleType = selectedOption?.dataset?.saleType || 'unit';
                const unit = selectedOption?.dataset?.weightUnit || '';
                const quantity = Number(quantityInput.value || 0);

                if (saleType === 'unit') {
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
                    const quantityInput = row.querySelector('.quantity-input');
                    const selectedOption = productSelect.options[productSelect.selectedIndex];
                    const unitPrice = Number(selectedOption?.dataset?.price || 0);
                    const quantity = Number(quantityInput.value || 0);
                    total += unitPrice * quantity;
                });
                totalEl.textContent = formatMoney(total);
            }

            function refreshRow(row) {
                updateRowTotals(row);
                updateSaleTotal();
            }

            function bindRow(row) {
                row.querySelector('.product-select').addEventListener('change', () => refreshRow(row));
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

            body.querySelectorAll('.sale-item-row').forEach(bindRow);
            updateRowNames();
            updateSaleTotal();
        })();
    </script>
</x-app-layout>
