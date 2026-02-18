<?php

namespace App\Http\Controllers;

use App\Models\Baliho;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $totalBaliho = Baliho::count();
        $totalBerFoto = Baliho::whereNotNull('foto')->count();

        $perJenis = Baliho::selectRaw('jenis_kontruksi, COUNT(*) as total')
            ->groupBy('jenis_kontruksi')
            ->get();

        $perKecamatan = Baliho::with('kecamatan')
            ->selectRaw('kode, COUNT(*) as total')
            ->groupBy('kode')
            ->orderByDesc('total')
            ->get()
            ->map(fn($b) => [
                'nama'  => $b->kecamatan->nama_kecamatan ?? $b->kode,
                'total' => $b->total,
            ]);

        return Inertia::render('Dashboard', [
            'totalBaliho'  => $totalBaliho,
            'totalBerFoto' => $totalBerFoto,
            'perJenis'     => $perJenis,
            'perKecamatan' => $perKecamatan,
        ]);
    }
}