<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests\Customer\CreateProfileRequest;
use App\Http\Requests\Customer\LoginRequest;
use App\Http\Requests\Customer\RegisterRequest;
use App\Services\Customer\AuthService;
use App\Traits\ResourceTrait;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class CustomerController extends BaseController
{
    use ResourceTrait;
    public function __construct(protected AuthService $authService)
    {
        $this->middleware(['auth:sanctum', 'customer'])->except('login', 'register');
    }

    public function register(RegisterRequest $request)
    {
        $user = $this->authService->registerCustomer($request->validated());
        return $this->successResponse($user, 'Customer registered successfully', 201);
    }

    public function login(LoginRequest $request)
    {
        $user = $this->authService->loginCustomer($request->validated());
        if (!$user) {
            return $this->errorResponse('Invalid credentials or not a customer', 401);
        }
        return $this->successResponse($user, 'Customer logged in successfully');
    }

    public function logout(Request $request)
    {
        
        $finished = $this->authService->logoutCustomer($request->user());
        // if (!$finished) {
        //     return $this->errorResponse('Could not log out customer', 500);
        // }

        return $this->successResponse(null, 'Customer logged out successfully');
    }

    public function profile(Request $request)
    {
        $userProfile = $this->authService->profileCustomer($request->user());
        if (!$userProfile) {
            return $this->errorResponse('Customer has not created a profile yet', 404);
        }
        return $this->successResponse($userProfile, 'Customer profile retrieved successfully');
    }

    public function createProfile(CreateProfileRequest $request)
    {
        $user = $request->user();
        $profile = $this->authService->createProfileCustomer($user, $request->validated());

        return $this->successResponse($profile, 'Customer profile created successfully', 201);
    }
}
