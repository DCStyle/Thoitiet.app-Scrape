<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FooterColumn;
use App\Models\FooterColumnItem;
use App\Models\FooterSetting;
use Illuminate\Http\Request;

class FooterController extends Controller
{
    public function index()
    {
        $columns = FooterColumn::with('items')->orderBy('order')->get();
        $settings = FooterSetting::all();
        return view('admin.footer.index', compact('columns', 'settings'));
    }

    public function storeColumn(Request $request)
    {
        $request->validate(['title' => 'required|string|max:255']);
        FooterColumn::create(['title' => $request->title]);
        return redirect()->back()->with('success', 'Cột đã được thêm!');
    }

    public function storeColumnItem(Request $request, FooterColumn $column)
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'url' => 'required|string',
        ]);

        $column->items()->create([
            'label' => $request->label,
            'url' => $request->url,
            'order' => $column->items()->count(),
        ]);

        return redirect()->back()->with('success', 'Liên kết đã được thêm!');
    }

    public function updateSetting(Request $request)
    {
        $request->validate(['key' => 'required', 'value' => 'required']);
        FooterSetting::updateOrCreate(['key' => $request->key], ['value' => $request->value]);
        return redirect()->back()->with('success', 'Footer setting updated successfully!');
    }

    public function updateColumnOrder(Request $request)
    {
        $order = $request->get('order');
        foreach ($order as $index => $columnId) {
            FooterColumn::where('id', $columnId)->update(['order' => $index]);
        }
        return response()->json(['success' => true]);
    }

    public function updateItemOrder(Request $request)
    {
        $order = $request->get('order');
        foreach ($order as $index => $itemId) {
            FooterColumnItem::where('id', $itemId)->update(['order' => $index]);
        }
        return response()->json(['success' => true]);
    }

    public function updateItemParent(Request $request)
    {
        $itemId = $request->get('item_id');
        $newParentId = $request->get('new_parent_id');

        FooterColumnItem::where('id', $itemId)->update(['footer_column_id' => $newParentId]);
        return response()->json(['success' => true]);
    }

    public function updateFooter(Request $request)
    {
        try {
            $type = $request->input('type');
            $id = $request->input('id');

            switch ($type) {
                case 'column':
                    $column = FooterColumn::findOrFail($id);
                    $column->update(['title' => $request->input('title')]);
                    break;

                case 'item':
                    $item = FooterColumnItem::findOrFail($id);
                    $item->update([
                        'label' => $request->input('label'),
                        'url' => $request->input('url')
                    ]);
                    break;

                case 'setting':
                    $setting = FooterSetting::findOrFail($id);
                    $setting->update([
                        'key' => $request->input('key'),
                        'value' => $request->input('value')
                    ]);
                    break;

                default:
                    return response()->json(['message' => 'Invalid type'], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteFooter(Request $request)
    {
        $id = $request->get('id');
        $type = $request->get('type');

        switch ($type) {
            case 'column':
                FooterColumn::where('id', $id)->delete();
                break;

            case 'item':
                FooterColumnItem::where('id', $id)->delete();
                break;

            case 'setting':
                FooterSetting::where('id', $id)->delete();
                break;
            default:
                return response()->json(['message' => 'Invalid type'], 400);
        }

        return response()->json(['success' => true]);
    }
}
