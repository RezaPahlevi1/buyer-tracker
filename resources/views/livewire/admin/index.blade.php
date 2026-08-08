<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    public bool $showFormModal = false;
    public ?int $editingId = null;
    public string $name = '';
    public string $email = '';

    public bool $showPasswordModal = false;
    public ?int $resettingId = null;
    public string $resettingName = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function openCreate(): void
    {
        $this->reset(['editingId', 'name', 'email']);
        $this->resetErrorBag();
        $this->showFormModal = true;
    }

    public function openEdit(User $user): void
    {
        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->resetErrorBag();
        $this->showFormModal = true;
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($this->editingId),
            ],
        ], [], ['name' => 'nama', 'email' => 'email']);

        if ($this->editingId) {
            $user = User::findOrFail($this->editingId);
            $user->fill($data);
            $user->save();
        } else {
            $user = new User();
            $user->fill($data);
            $user->role = UserRole::Sales; // akun baru selalu dibuat sebagai Sales
            $user->password = Hash::make(Str::random(32));
            $user->email_verified_at = now();
            $user->save();
        }

        $this->showFormModal = false;
        $this->reset(['editingId', 'name', 'email']);
    }

    public function openResetPassword(User $user): void
    {
        $this->resettingId = $user->id;
        $this->resettingName = $user->name;
        $this->reset(['password', 'password_confirmation']);
        $this->resetErrorBag();
        $this->showPasswordModal = true;
    }

    public function resetPassword(): void
    {
        $this->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [], ['password' => 'kata sandi baru']);

        $user = User::findOrFail($this->resettingId);
        $user->password = Hash::make($this->password);
        $user->save();

        $this->showPasswordModal = false;
        $this->reset(['resettingId', 'resettingName', 'password', 'password_confirmation']);
    }

    public function delete(User $user): void
    {
        // Guard: superadmin tidak boleh hapus akunnya sendiri
        abort_if($user->id === auth()->id(), 403, 'Anda tidak dapat menghapus akun Anda sendiri.');

        $user->delete();
    }

    public function with(): array
    {
        return [
            'users' => User::query()
                ->orderByDesc('role') // superadmin ('superadmin') tampil di atas sales ('sales') secara alfabetis
                ->orderBy('name')
                ->paginate(10),
        ];
    }
}; ?>

<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold text-gray-800">Admin</h1>

        <button wire:click="openCreate" class="px-4 py-2 bg-gray-800 text-white text-sm rounded-md hover:bg-gray-700">
            + Tambah Akun Sales
        </button>
    </div>

    {{-- Mobile: card list --}}
    <div class="space-y-3 sm:hidden">
        @forelse ($users as $user)
            <div wire:key="user-mobile-{{ $user->id }}" class="bg-white rounded-lg shadow p-4 text-sm">
                <div class="font-medium text-gray-800">
                    {{ $user->name }}
                    <span class="text-xs font-normal text-gray-400">({{ $user->role->label() }})</span>
                    @if ($user->id === auth()->id())
                        <span class="text-xs font-normal text-gray-400">— Akun Anda</span>
                    @endif
                </div>
                <div class="text-gray-500 mt-0.5">{{ $user->email }}</div>
                <div class="flex items-center gap-4 mt-3 pt-3 border-t border-gray-100">
                    <button wire:click="openEdit({{ $user->id }})" class="text-blue-600 hover:underline">Edit</button>
                    <button wire:click="openResetPassword({{ $user->id }})" class="text-amber-600 hover:underline">Reset Password</button>
                    @if ($user->id !== auth()->id())
                        <button
                            wire:click="delete({{ $user->id }})"
                            wire:confirm="Yakin ingin menghapus akun ini?"
                            class="text-red-600 hover:underline"
                        >
                            Hapus
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow p-6 text-center text-gray-400 text-sm">
                Belum ada akun.
            </div>
        @endforelse
    </div>

    {{-- Desktop: table --}}
    <div class="hidden sm:block bg-white rounded-lg shadow overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Nama</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Role</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Email</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-500">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($users as $user)
                    <tr wire:key="user-{{ $user->id }}">
                        <td class="px-4 py-3">
                            {{ $user->name }}
                            @if ($user->id === auth()->id())
                                <span class="text-xs text-gray-400">(Anda)</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $user->role->label() }}</td>
                        <td class="px-4 py-3">{{ $user->email }}</td>
                        <td class="px-4 py-3 text-right space-x-3">
                            <button wire:click="openEdit({{ $user->id }})" class="text-blue-600 hover:underline">Edit</button>
                            <button wire:click="openResetPassword({{ $user->id }})" class="text-amber-600 hover:underline">Reset Password</button>
                            @if ($user->id !== auth()->id())
                                <button
                                    wire:click="delete({{ $user->id }})"
                                    wire:confirm="Yakin ingin menghapus akun ini?"
                                    class="text-red-600 hover:underline"
                                >
                                    Hapus
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-gray-400">Belum ada akun.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>

    {{-- Modal: Tambah/Edit --}}
    @if ($showFormModal)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 px-4" wire:click.self="$set('showFormModal', false)">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">
                    {{ $editingId ? 'Edit Akun' : 'Tambah Akun Sales' }}
                </h2>

                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Nama</label>
                        <input type="text" wire:model="name" class="w-full rounded-md border-gray-300 shadow-sm">
                        @error('name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Email</label>
                        <input type="email" wire:model="email" class="w-full rounded-md border-gray-300 shadow-sm">
                        @error('email') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    @unless ($editingId)
                        <p class="text-xs text-gray-400">
                            Akun baru selalu dibuat dengan role Sales dan password sementara. Gunakan "Reset Password" setelah akun dibuat.
                        </p>
                    @endunless

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="$set('showFormModal', false)" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-sm rounded-md hover:bg-gray-700">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal: Reset Password --}}
    @if ($showPasswordModal)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 px-4" wire:click.self="$set('showPasswordModal', false)">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-1">Reset Password</h2>
                <p class="text-sm text-gray-500 mb-4">Untuk akun: {{ $resettingName }}</p>

                <form wire:submit="resetPassword" class="space-y-4">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Password Baru</label>
                        <input type="password" wire:model="password" class="w-full rounded-md border-gray-300 shadow-sm">
                        @error('password') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Konfirmasi Password</label>
                        <input type="password" wire:model="password_confirmation" class="w-full rounded-md border-gray-300 shadow-sm">
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="$set('showPasswordModal', false)" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-sm rounded-md hover:bg-gray-700">
                            Simpan Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>