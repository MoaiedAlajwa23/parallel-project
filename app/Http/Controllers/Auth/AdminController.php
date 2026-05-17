<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateProfileRequest;
use App\Http\Requests\Admin\LoginRequest;
use App\Http\Requests\Admin\RegisterRequest;
use App\Services\Admin\AuthService;
use App\Traits\ResourceTrait;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class AdminController extends BaseController
{
    use ResourceTrait;
    /**
     * Create a new class instance.
     */
    public function __construct(protected AuthService $authService)
    {
        $this->middleware(['auth:sanctum', 'admin'])->except('login', 'register');
    }

    public function register(RegisterRequest $request)
    {
        $user = $this->authService->registerAdmin($request->validated());
        return $this->successResponse($user, 'Admin registered successfully', 201);
    }

    public function login(LoginRequest $request)
    {
        $user = $this->authService->loginAdmin($request->validated());
        if (!$user) {
            return $this->errorResponse('Invalid credentials or not an admin', 401);
        }
        return $this->successResponse($user, 'Admin logged in successfully');
    }

    public function logout(Request $request)
    {
        $this->authService->logoutAdmin($request->user());
        return $this->successResponse(null, 'Admin logged out successfully');
    }
    public function profile(Request $request)
    {
        $userProfile = $this->authService->profileAdmin($request->user());
        if (!$userProfile) {
            return $this->errorResponse('Admin has not created a profile yet', 404);
        }
        return $this->successResponse($userProfile, 'Admin profile retrieved successfully');
    }
    public function createProfile(CreateProfileRequest $request)
    {
        $user = $request->user();
        $profile = $this->authService->createProfileAdmin($user, $request->validated());

        return $this->successResponse($profile, 'Admin profile created successfully', 201);
    }
}
