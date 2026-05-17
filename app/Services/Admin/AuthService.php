<?php

namespace App\Services\Admin;

use App\Models\Role;
use App\Models\User;

class AuthService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function registerAdmin(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'is_active' => true,
        ]);
        
        $token = $user->createToken('admin-token', ['admin:all'])->plainTextToken;
        $user->token = $token;
        $adminRole = Role::where('slug', 'admin')->first();
        if ($adminRole) {
            $user->roles()->attach($adminRole->id);
        }

        return $user;
    }

    public function loginAdmin(array $credentials)
    {
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !\Illuminate\Support\Facades\Hash::check($credentials['password'], $user->password)) {
            return null;
        }

        if (!$user->roles()->where('slug', 'admin')->exists()) {
            return null;
        }

        $token = $user->createToken('admin-token', ['admin:all'])->plainTextToken;
        $user->token = $token;

        return $user;
    }

    public function logoutAdmin($user)
    {
        $user->tokens()->delete();
    }

    public function profileAdmin($user)
    {
        $userProfile = User::find($user->id)->profile;
        $userProfile['name'] = $user->name;
        $userProfile['email'] = $user->email;
        return $userProfile;
    }

    public function createProfileAdmin($user, array $profileData)
    {
        $profile = $user->profile()->create($profileData);

        return $profile;
    }
}
