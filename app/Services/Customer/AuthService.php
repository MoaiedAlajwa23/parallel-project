<?php

namespace App\Services\Customer;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function registerCustomer(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'is_active' => true,
        ]);
        
        $token = $user->createToken('customer-token', ['customer:all'])->plainTextToken;
        $user->token = $token;
        $customerRole = Role::where('slug', 'customer')->first();
        if ($customerRole) {
            $user->roles()->attach($customerRole->id);
        }

        return $user;
    }

    public function loginCustomer(array $credentials)
    {
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return null;
        }

        if (!$user->roles()->where('slug', 'customer')->exists()) {
            return null;
        }

        $token = $user->createToken('customer-token', ['customer:all'])->plainTextToken;
        $user->token = $token;

        return $user;
    }

    public function logoutCustomer($user)
    {
        $user->tokens()->delete();
        return true;
    }

    public function profileCustomer($user)
    {
        $userProfile = User::find($user->id)->profile;
        $userProfile['name'] = $user->name;
        $userProfile['email'] = $user->email;
        return $userProfile;
    }

    public function createProfileCustomer($user, array $profileData)
    {
        $profile = $user->profile()->create($profileData);

        return $profile;
    }
}
