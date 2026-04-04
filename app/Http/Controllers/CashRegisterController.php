<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
            'opening_amount' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        CashRegister::create([
            'opened_by' => $request->user()->id,
            'opened_at' => now(),
            'opening_amount' => $data['opening_amount'],
            'status' => CashRegister::STATUS_OPEN,
            'notes' => $data['notes'] ?? null,
        ]);

        return back()->with('status', 'Caja abierta correctamente.');
    }

    public function close(Request $request, CashRegister $cashRegister): RedirectResponse
    {
        if ($cashRegister->status !== CashRegister::STATUS_OPEN) {
            return back()->with('error', 'La caja seleccionada ya esta cerrada.');
        }

        $data = $request->validate([
            'counted_cash' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $expectedCash = $cashRegister->expectedCash();
        $countedCash = round((float) $data['counted_cash'], 2);

        $cashRegister->update([
            'closed_by' => $request->user()->id,
            'closed_at' => now(),
            'counted_cash' => $countedCash,
            'difference_amount' => round($countedCash - $expectedCash, 2),
            'status' => CashRegister::STATUS_CLOSED,
            'notes' => $data['notes'] ?? $cashRegister->notes,
        ]);

        return back()->with('status', 'Caja cerrada correctamente.');
    }
}