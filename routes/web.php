<?php

use App\Enums\UserRole;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    Volt::route('dashboard', 'dashboard-home')->name('dashboard');

    // Modul data — kedua role bisa akses, kontrol create/update/delete via Policy di dalam component
    Volt::route('pelanggan', 'pelanggan.index')->name('pelanggan.index');
    Volt::route('pelanggan/tambah', 'pelanggan.form')->name('pelanggan.create');
    Volt::route('pelanggan/{buyer}/edit', 'pelanggan.form')->name('pelanggan.edit');
    Volt::route('pelanggan/{buyer}', 'pelanggan.show')->name('pelanggan.show');

    Volt::route('vendor', 'vendor.index')->name('vendor.index');
    Volt::route('vendor/tambah', 'vendor.form')->name('vendor.create');
    Volt::route('vendor/{vendor}/edit', 'vendor.form')->name('vendor.edit');
    Volt::route('vendor/{vendor}', 'vendor.show')->name('vendor.show');

    Volt::route('produk', 'produk.index')->name('produk.index');
    Volt::route('produk/tambah', 'produk.form')->name('produk.create');
    Volt::route('produk/{product}/edit', 'produk.form')->name('produk.edit');
    Volt::route('produk/{product}', 'produk.show')->name('produk.show');

    // Modul stub
    Volt::route('karyawan', 'karyawan.index')->name('karyawan.index');
    Volt::route('coming-soon', 'coming-soon.index')->name('coming-soon.index');

    // Superadmin only — seluruh modul, bukan cuma per-aksi
    Route::middleware(['role:' . UserRole::Superadmin->value])->group(function () {
        Volt::route('settings', 'settings.index')->name('settings.index');
        Volt::route('admin', 'admin.index')->name('admin.index');
    });
});

require __DIR__.'/auth.php';