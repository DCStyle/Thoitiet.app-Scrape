<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::with('children')->whereNull('parent_id')->orderBy('order')->get();
        return view('admin.menus.index', compact('menus'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'icon' => 'nullable|string',
            'url' => 'nullable|string',
            'parent_id' => 'nullable|exists:menus,id',
            'order' => 'nullable|integer',
        ]);

        // Auto set order to next available order if not provided
        if (!isset($validated['order'])) {
            $validated['order'] = Menu::where('parent_id', $validated['parent_id'])->max('order') + 1;
        }

        Menu::create($validated);

        // Clear menu cache
        Cache::forget('menu_items');

        return redirect()->back()->with('success', 'Thêm menu thành công.');
    }

    public function edit(Menu $menu)
    {
        $menus = Menu::whereNull('parent_id')
            ->where('id', '!=', $menu->id)
            ->orderBy('order')
            ->get();

        return view('admin.menus.edit', compact('menu', 'menus'));
    }

    public function update(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'icon' => 'nullable|string',
            'url' => 'nullable|string',
            'parent_id' => 'nullable|exists:menus,id|different:id',
            'order' => 'nullable|integer',
        ]);

        $menu->update($validated);

        // Clear menu cache
        Cache::forget('menu_items');

        return redirect()->back()->with('success', 'Cập nhật menu thành công.');
    }

    public function destroy(Menu $menu)
    {
        $menu->delete();

        return redirect()->back()->with('success', 'Xoá menu thành công.');
    }

    public function reorder(Request $request)
    {
        $orders = $request->input('orders');

        foreach ($orders as $order) {
            Menu::where('id', $order['id'])->update([
                'order' => $order['order'],
                'parent_id' => $order['parent_id']
            ]);
        }

        // Clear menu cache
        Cache::forget('menu_items');

        return response()->json(['success' => true]);
    }
}
