<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashRegisterController extends Controller
{
    public function index()
    {
        $currentRegister = CashRegister::query()
            ->with('opener')
            ->where('status', CashRegister::STATUS_OPEN)
            ->latest('opened_at')
            ->first();

        $history = CashRegister::query()
            ->with(['opener', 'closer'])
            ->latest('opened_at')
            ->paginate(12);

        return view('cash-registers.index', compact('currentRegister', 'history'));
    }

    public function open(Request $request): RedirectResponse
    {
        if (CashRegister::current()) {
            return back()->with('error', 'Ya existe una caja abierta. Debes cerrarla antes de abrir una nueva.');
        }

        $data = $request->validate([
            'opening_amount' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // Campos no-fillable (status, totales) se insertan explícitamente con DB::statement
        DB::statement(
            'INSERT INTO cash_registers (opened_by, opened_at, opening_amount, status, notes, cash_sales_total, qr_sales_total, credit_sales_total, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 0, 0, 0, NOW(), NOW())',
            [
                $request->user()->id,
                now(),
                round((float) $data['opening_amount'], 2),
                CashRegister::STATUS_OPEN,
                $data['notes'] ?? null,
            ]
        );

        return back()->with('status', 'Caja abierta correctamente.');
    }

    public function close(Request $request, CashRegister $cashRegister): RedirectResponse
    {
        // Solo el admin puede cerrar cualquier caja; verificar que esté abierta
        if ($cashRegister->status !== CashRegister::STATUS_OPEN) {
            return back()->with('error', 'La caja seleccionada ya esta cerrada.');
        }

        $data = $request->validate([
            'counted_cash' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $expectedCash = $cashRegister->expectedCash();
        $countedCash = round((float) $data['counted_cash'], 2);
        $difference = round($countedCash - $expectedCash, 2);

        // Campos sensibles (status, diferencia, cierre) con bindings parametrizados
        DB::statement(
            'UPDATE cash_registers SET closed_by = ?, closed_at = NOW(), counted_cash = ?, difference_amount = ?, status = ?, notes = ?, updated_at = NOW() WHERE id = ? AND status = ?',
            [
                $request->user()->id,
                $countedCash,
                $difference,
                CashRegister::STATUS_CLOSED,
                $data['notes'] ?? $cashRegister->notes,
                $cashRegister->id,
                CashRegister::STATUS_OPEN, // doble check: solo cierra si está abierta
            ]
        );

        return back()->with('status', 'Caja cerrada correctamente.');
    }
}
