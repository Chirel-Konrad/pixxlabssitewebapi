<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Auth\SocialAuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\BlogCommentController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\PartnerSubscriptionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TestimonialController;
use App\Http\Controllers\WebinarController;
use App\Http\Controllers\WebinarRegistrationController;
use App\Http\Controllers\PilierController;
use App\Http\Controllers\PrivilegeController;
use App\Http\Controllers\EvaFeatureController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;

Route::get('/test', function () {
    return response()->json(['message' => 'API works!']);
});


Route::prefix('v1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/social-login', [AuthController::class, 'socialLogin']);

    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/profile', function (Request $request) {
            return $request->user()->makeHidden(['password']);
        });
    });


     Route::post('/password/email', [AuthController::class, 'sendPasswordResetLink']);
    Route::post('/password/reset', [AuthController::class, 'resetPassword']);

    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->middleware('signed')
        ->name('verification.verify');

    Route::post('/enable-2fa', [AuthController::class, 'enable2FA'])->middleware('auth:api');

    Route::get('auth/{provider}', [SocialAuthController::class, 'redirect']);
    Route::get('auth/{provider}/callback', [SocialAuthController::class, 'callback']);

    // DEBUG ROUTE - TO BE REMOVED
    Route::get('/debug-passport', function () {
        $privateKeyPath = config('passport.private_key');
        $publicKeyPath = config('passport.public_key');

        return response()->json([
            'passport_config' => [
                'private_key' => [
                    'path' => $privateKeyPath,
                    'exists' => file_exists($privateKeyPath),
                    'readable' => is_readable($privateKeyPath),
                    'permissions' => file_exists($privateKeyPath) ? substr(sprintf('%o', fileperms($privateKeyPath)), -4) : null,
                ],
                'public_key' => [
                    'path' => $publicKeyPath,
                    'exists' => file_exists($publicKeyPath),
                    'readable' => is_readable($publicKeyPath),
                    'permissions' => file_exists($publicKeyPath) ? substr(sprintf('%o', fileperms($publicKeyPath)), -4) : null,
                ],
            ],
            'env_vars' => [
                'PASSPORT_PRIVATE_KEY_PATH' => env('PASSPORT_PRIVATE_KEY_PATH'),
                'PASSPORT_PUBLIC_KEY_PATH' => env('PASSPORT_PUBLIC_KEY_PATH'),
            ],
            'storage_path' => storage_path(),
        ]);
    });
});



Route::prefix('v1')->group(function () {
    // Blogs
    Route::prefix('blogs')->group(function () {
        Route::get('/', [BlogController::class, 'index']);
        Route::get('{blog}', [BlogController::class, 'show']);
        Route::get('slug/{blog:slug}', [BlogController::class, 'show']);
        Route::post('/', [BlogController::class, 'store'])->middleware(['auth:api', 'admin']);
        Route::put('{blog}', [BlogController::class, 'update'])->middleware(['auth:api', 'admin']);
        Route::put('slug/{blog:slug}', [BlogController::class, 'update']);
        Route::delete('{blog}', [BlogController::class, 'destroy'])->middleware(['auth:api', 'admin']);
        Route::delete('slug/{blog:slug}', [BlogController::class, 'destroy']);
    });

    // Blog Comments
    Route::prefix('blog-comments')->group(function () {
        Route::get('/', [BlogCommentController::class, 'index']);
        Route::get('{blogComment}', [BlogCommentController::class, 'show']);
        Route::post('/', [BlogCommentController::class, 'store']);
        Route::put('{blogComment}', [BlogCommentController::class, 'update']);
        Route::delete('{blogComment}', [BlogCommentController::class, 'destroy']);
    });

    // Contacts
    Route::prefix('contacts')->group(function () {
        Route::get('/', [ContactController::class, 'index']);
        Route::get('{contact}', [ContactController::class, 'show']);
        Route::get('slug/{contact:slug}', [ContactController::class, 'show']);
        Route::post('/', [ContactController::class, 'store']);
        Route::put('{contact}', [ContactController::class, 'update']);
        Route::put('slug/{contact:slug}', [ContactController::class, 'update']);
        Route::delete('{contact}', [ContactController::class, 'destroy']);
        Route::delete('slug/{contact:slug}', [ContactController::class, 'destroy']);
    });

    // FAQs
    Route::prefix('faqs')->group(function () {
        Route::get('/', [FaqController::class, 'index']);
        Route::get('{faq}', [FaqController::class, 'show']);
        Route::get('slug/{faq:slug}', [FaqController::class, 'show']);
        Route::post('/', [FaqController::class, 'store'])->middleware(['auth:api', 'admin']);
        Route::put('{faq}', [FaqController::class, 'update'])->middleware(['auth:api', 'admin']);
        Route::put('slug/{faq:slug}', [FaqController::class, 'update']);
        Route::delete('{faq}', [FaqController::class, 'destroy'])->middleware(['auth:api', 'admin']);
        Route::delete('slug/{faq:slug}', [FaqController::class, 'destroy']);
    });

    // Newsletters
    Route::prefix('newsletters')->group(function () {
        Route::get('/', [NewsletterController::class, 'index']);
        Route::get('{newsletter}', [NewsletterController::class, 'show']);
        Route::get('slug/{newsletter:slug}', [NewsletterController::class, 'show']);
        Route::post('/', [NewsletterController::class, 'store']);
        Route::put('{newsletter}', [NewsletterController::class, 'update']);
        Route::put('slug/{newsletter:slug}', [NewsletterController::class, 'update']);
        Route::delete('{newsletter}', [NewsletterController::class, 'destroy']);
        Route::delete('slug/{newsletter:slug}', [NewsletterController::class, 'destroy']);
    });

    // Products
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::get('{product}', [ProductController::class, 'show']);
        Route::get('slug/{product:slug}', [ProductController::class, 'show']);
        Route::post('/', [ProductController::class, 'store'])->middleware(['auth:api', 'admin']);
        Route::put('{product}', [ProductController::class, 'update'])->middleware(['auth:api', 'admin']);
        Route::put('slug/{product:slug}', [ProductController::class, 'update']);
        Route::delete('{product}', [ProductController::class, 'destroy'])->middleware(['auth:api', 'admin']);
        Route::delete('slug/{product:slug}', [ProductController::class, 'destroy']);
    });

    // Testimonials
    Route::prefix('testimonials')->group(function () {
        Route::get('/', [TestimonialController::class, 'index']);
        Route::get('{testimonial}', [TestimonialController::class, 'show']);
        Route::get('slug/{testimonial:slug}', [TestimonialController::class, 'show']);
        Route::post('/', [TestimonialController::class, 'store'])->middleware(['auth:api', 'admin']);
        Route::put('{testimonial}', [TestimonialController::class, 'update'])->middleware(['auth:api', 'admin']);
        Route::put('slug/{testimonial:slug}', [TestimonialController::class, 'update']);
        Route::delete('{testimonial}', [TestimonialController::class, 'destroy'])->middleware(['auth:api', 'admin']);
        Route::delete('slug/{testimonial:slug}', [TestimonialController::class, 'destroy']);
    });

    // Webinars
    Route::prefix('webinars')->group(function () {
        Route::get('/', [WebinarController::class, 'index']);
        Route::get('{webinar}', [WebinarController::class, 'show']);
        Route::get('slug/{webinar:slug}', [WebinarController::class, 'show']);
        Route::post('/', [WebinarController::class, 'store'])->middleware(['auth:api', 'admin']);
        Route::put('{webinar}', [WebinarController::class, 'update'])->middleware(['auth:api', 'admin']);
        Route::put('slug/{webinar:slug}', [WebinarController::class, 'update']);
        Route::delete('{webinar}', [WebinarController::class, 'destroy'])->middleware(['auth:api', 'admin']);
        Route::delete('slug/{webinar:slug}', [WebinarController::class, 'destroy']);
    });

    // Webinar Registrations
    Route::prefix('webinar-registrations')->group(function () {
        Route::get('/', [WebinarRegistrationController::class, 'index']);
        Route::get('{webinarRegistration}', [WebinarRegistrationController::class, 'show']);
        Route::get('slug/{webinarRegistration:slug}', [WebinarRegistrationController::class, 'show']);
        Route::post('/', [WebinarRegistrationController::class, 'store']);
        Route::put('{webinarRegistration}', [WebinarRegistrationController::class, 'update']);
        Route::delete('{webinarRegistration}', [WebinarRegistrationController::class, 'destroy']);
    });

    // Piliers
    Route::prefix('piliers')->group(function () {
        Route::get('/', [PilierController::class, 'index']);
        Route::get('{pilier}', [PilierController::class, 'show']);
        Route::get('slug/{pilier:slug}', [PilierController::class, 'show']);
        Route::post('/', [PilierController::class, 'store'])->middleware(['auth:api', 'admin']);
        Route::put('{pilier}', [PilierController::class, 'update'])->middleware(['auth:api', 'admin']);
        Route::put('slug/{pilier:slug}', [PilierController::class, 'update']);
        Route::delete('{pilier}', [PilierController::class, 'destroy'])->middleware(['auth:api', 'admin']);
        Route::delete('slug/{pilier:slug}', [PilierController::class, 'destroy']);
    });

    // Privileges
    Route::prefix('privileges')->group(function () {
        Route::get('/', [PrivilegeController::class, 'index']);
        Route::get('{privilege}', [PrivilegeController::class, 'show']);
        Route::get('slug/{privilege:slug}', [PrivilegeController::class, 'show']);
        Route::post('/', [PrivilegeController::class, 'store'])->middleware(['auth:api', 'admin']);
        Route::put('{privilege}', [PrivilegeController::class, 'update'])->middleware(['auth:api', 'admin']);
        Route::put('slug/{privilege:slug}', [PrivilegeController::class, 'update']);
        Route::delete('{privilege}', [PrivilegeController::class, 'destroy'])->middleware(['auth:api', 'admin']);
        Route::delete('slug/{privilege:slug}', [PrivilegeController::class, 'destroy']);
    });

    // Eva Features
    Route::prefix('eva-features')->group(function () {
        Route::get('/', [EvaFeatureController::class, 'index']);
        Route::get('{evaFeature}', [EvaFeatureController::class, 'show']);
        Route::get('slug/{evaFeature:slug}', [EvaFeatureController::class, 'show']);
        Route::post('/', [EvaFeatureController::class, 'store'])->middleware(['auth:api', 'admin']);
        Route::put('{evaFeature}', [EvaFeatureController::class, 'update'])->middleware(['auth:api', 'admin']);
        Route::put('slug/{evaFeature:slug}', [EvaFeatureController::class, 'update']);
        Route::delete('{evaFeature}', [EvaFeatureController::class, 'destroy'])->middleware(['auth:api', 'admin']);
        Route::delete('slug/{evaFeature:slug}', [EvaFeatureController::class, 'destroy']);
    });

    // Offers
    Route::prefix('offers')->group(function () {
        Route::get('/', [OfferController::class, 'index']);
        Route::get('{offer}', [OfferController::class, 'show']);
        Route::get('slug/{offer:slug}', [OfferController::class, 'show']);
        Route::post('/', [OfferController::class, 'store'])->middleware(['auth:api', 'admin']);
        Route::put('{offer}', [OfferController::class, 'update'])->middleware(['auth:api', 'admin']);
        Route::put('slug/{offer:slug}', [OfferController::class, 'update']);
        Route::delete('{offer}', [OfferController::class, 'destroy'])->middleware(['auth:api', 'admin']);
        Route::delete('slug/{offer:slug}', [OfferController::class, 'destroy']);
    });

    // Users
    Route::prefix('users')->group(function () {
        Route::get('{id}', [UserController::class, 'show']);
        Route::post('/', [UserController::class, 'store']);
        Route::put('{id}', [UserController::class, 'update']);
        Route::delete('{id}', [UserController::class, 'destroy']);
    });
});
