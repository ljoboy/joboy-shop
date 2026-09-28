<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderStatusController extends Controller
{
    public function index()
    {
        $statuses = OrderStatus::orderBy('position')->get();
        return view('admin.statuses.index', compact('statuses'));
    }

    public function create()
    {
        return view('admin.statuses.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'badge_color' => 'required|string|max:50',
            'whatsapp_template' => 'nullable|string|max:2000',
            'position' => 'nullable|integer',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['position'] = $validated['position'] ?? (OrderStatus::max('position') + 1);

        if ($request->has('is_default')) {
            OrderStatus::query()->update(['is_default' => false]);
            $validated['is_default'] = true;
        }

        OrderStatus::create($validated);

        return redirect()->route('admin.statuses.index')->with('success', 'Statut de commande créé.');
    }

    public function edit(OrderStatus $status)
    {
        return view('admin.statuses.edit', compact('status'));
    }

    public function update(Request $request, OrderStatus $status)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'badge_color' => 'required|string|max:50',
            'whatsapp_template' => 'nullable|string|max:2000',
            'position' => 'nullable|integer',
        ]);

        if ($request->has('is_default')) {
            OrderStatus::where('id', '!=', $status->id)->update(['is_default' => false]);
            $validated['is_default'] = true;
        } else {
            $validated['is_default'] = false;
        }

        $status->update($validated);

        return redirect()->route('admin.statuses.index')->with('success', 'Statut mis à jour.');
    }

    public function destroy(OrderStatus $status)
    {
        if ($status->orders()->count() > 0) {
            return redirect()->back()->with('error', 'Impossible de supprimer un statut associé à des commandes.');
        }
        $status->delete();
        return redirect()->route('admin.statuses.index')->with('success', 'Statut supprimé.');
    }
}
