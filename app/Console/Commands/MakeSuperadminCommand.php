<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class MakeSuperadminCommand extends Command
{
    protected $signature = 'app:make-superadmin';
    protected $description = 'Membuat atau menjadikan akun sebagai superadmin secara interaktif';

    public function handle(): int
    {
        $this->info('Buat / jadikan akun Superadmin untuk Buyer Tracker.');

        $email = $this->ask('Email');

        $validator = Validator::make(compact('email'), [
            'email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return self::FAILURE;
        }

        // withTrashed() supaya akun yang pernah di-soft-delete dengan email sama tetap terdeteksi
        // (constraint unique di database tetap berlaku meski soft-deleted).
        $existing = User::withTrashed()->where('email', $email)->first();

        if ($existing) {
            return $this->handleExistingUser($existing);
        }

        return $this->handleNewUser($email);
    }

    protected function handleNewUser(string $email): int
    {
        $name = $this->ask('Nama');
        [$password, $ok] = $this->askPassword();

        if (! $ok) {
            return self::FAILURE;
        }

        $validator = Validator::make(
            compact('name', 'password'),
            [
                'name' => 'required|string|max:255',
                'password' => 'required|min:6',
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return self::FAILURE;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        // role harus di-assign langsung, bukan lewat create()/fill() — role tidak $fillable
        $user->role = UserRole::Superadmin;
        $user->save();

        $this->info("Akun superadmin baru '{$email}' berhasil dibuat.");

        return self::SUCCESS;
    }

    protected function handleExistingUser(User $existing): int
    {
        $status = $existing->trashed() ? ' (berstatus terhapus/soft-deleted)' : '';
        $this->warn("Akun dengan email '{$existing->email}'{$status} sudah ada — nama: {$existing->name}, role saat ini: {$existing->role->value}.");

        if (! $this->confirm('Jadikan akun ini superadmin & reset passwordnya?', false)) {
            $this->info('Dibatalkan, tidak ada perubahan.');
            return self::SUCCESS;
        }

        [$password, $ok] = $this->askPassword();

        if (! $ok) {
            return self::FAILURE;
        }

        $validator = Validator::make(compact('password'), [
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return self::FAILURE;
        }

        if ($existing->trashed()) {
            $existing->restore();
        }

        $existing->password = Hash::make($password);
        $existing->role = UserRole::Superadmin;
        $existing->save();

        $this->info("Akun '{$existing->email}' berhasil dijadikan superadmin dengan password baru.");

        return self::SUCCESS;
    }

    /** @return array{0: ?string, 1: bool} [password, berhasil-atau-tidak] */
    protected function askPassword(): array
    {
        $password = $this->secret('Password baru (minimal 6 karakter)');
        $confirm = $this->secret('Konfirmasi Password');

        if ($password !== $confirm) {
            $this->error('Password dan konfirmasi tidak cocok. Coba lagi.');
            return [null, false];
        }

        return [$password, true];
    }
}