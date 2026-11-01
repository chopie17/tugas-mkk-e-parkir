<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

/**
 * UserController mengelola akun petugas (Super Admin only).
 *
 * GET    /users          → index
 * POST   /users          → store (buat petugas baru)
 * GET    /users/{user}   → show
 * PUT    /users/{user}   → update
 * DELETE /users/{user}   → destroy
 */
class UserController extends Controller
{
    /**
     * Daftar semua pengguna.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $users = User::latest()->get();

        return response()->json([
            'users' => UserResource::collection($users),
        ]);
    }

    /**
     * Buat akun petugas baru (role default: petugas).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'role'     => ['required', new Enum(UserRole::class)],
        ]);

        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        return response()->json([
            'message' => 'Akun berhasil dibuat.',
            'user'    => new UserResource($user),
        ], 201);
    }

    /**
     * Detail pengguna.
     *
     * @param User $user
     * @return JsonResponse
     */
    public function show(User $user): JsonResponse
    {
        return response()->json(['user' => new UserResource($user)]);
    }

    /**
     * Update data pengguna.
     *
     * @param Request $request
     * @param User    $user
     * @return JsonResponse
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'name'     => 'sometimes|required|string|max:255',
            'username' => ['sometimes','required','string', Rule::unique('users')->ignore($user->id)],
            'email'    => ['sometimes','required','email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8',
            'role'     => ['sometimes','required', new Enum(UserRole::class)],
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return response()->json([
            'message' => 'Data pengguna berhasil diperbarui.',
            'user'    => new UserResource($user->fresh()),
        ]);
    }

    /**
     * Hapus akun pengguna. Tidak bisa hapus akun sendiri.
     *
     * @param Request $request
     * @param User    $user
     * @return JsonResponse
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Tidak dapat menghapus akun sendiri.'], 403);
        }

        $user->tokens()->delete(); // Revoke semua token aktif
        $user->delete();

        return response()->json(['message' => 'Akun berhasil dihapus.']);
    }
}
