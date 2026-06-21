<?php

use App\Http\Controllers\Auth\AdminController;
use App\Http\Controllers\Auth\CustomerController;
use App\Http\Controllers\Manage\ManageController;
use App\Http\Controllers\ProductManage\ProductCategoryController;
use App\Http\Controllers\ProductManage\ProductController;
use App\Http\Controllers\Use\InteractController;
use App\Jobs\PaymentSimulateJob;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;


/********** Admin Auth Routes **********/
Route::prefix('admin/auth')->group(function () {
    Route::post('/register', [AdminController::class, 'register']);
    Route::post('/login', [AdminController::class, 'login']);
    Route::post('/logout', [AdminController::class, 'logout']);
    Route::post('/profile', [AdminController::class, 'profile']);
    Route::post('/create-profile', [AdminController::class, 'createProfile']);
});
/********** Customer Auth Routes **********/
Route::prefix('customer/auth')->group(function () {
    Route::post('/register', [CustomerController::class, 'register']);
    Route::post('/login', [CustomerController::class, 'login']);
    Route::post('/logout', [CustomerController::class, 'logout']);
    Route::post('/profile', [CustomerController::class, 'profile']);
    Route::post('/create-profile', [CustomerController::class, 'createProfile']);
});


Route::prefix('admin/manage')->group(function () {
    Route::post('/create-category', [ProductCategoryController::class, 'createCategory']);
    Route::post('/update-category', [ProductCategoryController::class, 'updateCategory']);
    Route::post('/delete-category', [ProductCategoryController::class, 'deleteCategory']);
    Route::post('/create-product', [ProductController::class, 'createProduct']);
    Route::post('/update-product', [ProductController::class, 'updateProduct']);
    Route::post('/delete-product', [ProductController::class, 'deleteProduct']);


});
Route::prefix('customer/use')->group(function () {
    Route::get('/browse-products', [InteractController::class, 'listProducts']);
    Route::get('/browse-categories', [InteractController::class, 'listCategories']);
    Route::get('/best-sellers', [InteractController::class, 'bestSellers']);
    Route::get('/best-sellers-slow', [InteractController::class, 'bestSellersSlow']);
    Route::post('/add-to-cart', [InteractController::class, 'addToCart']);
    Route::post('/deposit', [InteractController::class, 'deposit']);
    Route::post('/checkout', [InteractController::class, 'checkout']);
    Route::post('/show-product', [InteractController::class, 'showProductById']);
    
    });



// m1234 password linux wsl


Route::get('/generate-jmeter-tokens', function () {
  
    $file = fopen(storage_path('jmeter_tokens.csv'), 'w');

    // 2. كتابة اسم العمود في السطر الأول (Header)
    fputcsv($file, ['token']);

    // 3. جلب 100 مستخدم من قاعدة البيانات (تأكد أنهم عملاء إذا كان لديك صلاحيات)
    $users = \App\Models\User::take(200)->get(); 

    foreach ($users as $user) {
        // (اختياري) مسح التوكنز القديمة لهذا المستخدم لعدم إرهاق الداتا بيز
        

        // 4. إنشاء توكن جديد
        $token = $user->createToken('jmeter-test')->plainTextToken;
        
        // 5. كتابة التوكن في ملف الـ CSV
        fputcsv($file, [$token]);
    }

    fclose($file);
    return "تم بنجاح! تم استخراج توكنز لـ " . $users->count() . " مستخدم، الملف موجود في: " . storage_path('jmeter_tokens.csv');
});

Route::get('/tr', function () {
    $users = \App\Models\User::get();
    foreach ($users as $user) {
        $user->update(['role_id' => 2]); // Assuming 2 is the role_id for customers
    }
});