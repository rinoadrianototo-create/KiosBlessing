<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'store_name' => Setting::get('store_name', 'Kios Blessing'),
            'store_address' => Setting::get('store_address', 'Jl. Kasih Karunia No. 12, Manado'),
            'store_phone' => Setting::get('store_phone', '081234567890'),
            'receipt_footer' => Setting::get('receipt_footer', 'Terima kasih telah berbelanja di Kios Blessing!'),
            'max_cashier_discount_percent' => Setting::get('max_cashier_discount_percent', '10'),
        ];

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'store_name' => 'required|string|max:150',
            'store_address' => 'required|string|max:255',
            'store_phone' => 'required|string|max:30',
            'receipt_footer' => 'nullable|string|max:255',
            'max_cashier_discount_percent' => 'required|numeric|min:0|max:100',
        ]);

        Setting::set('store_name', $request->store_name);
        Setting::set('store_address', $request->store_address);
        Setting::set('store_phone', $request->store_phone);
        Setting::set('receipt_footer', $request->receipt_footer);
        Setting::set('max_cashier_discount_percent', $request->max_cashier_discount_percent);

        return redirect()->route('settings.index')->with('success', 'Pengaturan kios berhasil disimpan.');
    }

    public function backupDatabase()
    {
        $dbPath = database_path('database.sqlite');
        
        if (File::exists($dbPath)) {
            $filename = 'backup_kios_blessing_' . date('Y-m-d_H-i-s') . '.sqlite';
            return response()->download($dbPath, $filename);
        }

        return back()->with('error', 'File database tidak ditemukan untuk dicadangkan.');
    }
}
