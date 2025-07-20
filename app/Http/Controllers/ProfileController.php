<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $user = Auth::user();
        $user->name = $request->nama;
        $user->save();

        switch ($user->role) {
            case 'admin':
                $user->no_hp  = $request->no_hp;
                $user->alamat = $request->alamat;
                $user->save();
                break;
            case 'customer':
                $customer = \App\Models\Customer::where('user_id', $user->id)->first();
                if ($customer) {
                    $customer->nama_customer = $request->nama;
                    $customer->no_hp  = $request->no_hp;
                    $customer->alamat = $request->alamat;
                    $customer->save();
                }
                break;
            case 'mitra':
                $mitra = \App\Models\Mitra::where('user_id', $user->id)->first();
                if ($mitra) {
                    $mitra->nama_mitra = $request->nama;
                    $mitra->no_hp  = $request->no_hp;
                    $mitra->alamat = $request->alamat;
                    $mitra->save();
                }
                break;
            case 'supplier':
                $supplier = \App\Models\Supplier::where('user_id', $user->id)->first();
                if ($supplier) {
                    $supplier->nama_supplier = $request->nama;
                    $supplier->no_hp  = $request->no_hp;
                    $supplier->alamat = $request->alamat;
                    $supplier->save();
                }
                break;
        }

        return back()->with('success', 'Profil berhasil diperbarui!');
    }
}
