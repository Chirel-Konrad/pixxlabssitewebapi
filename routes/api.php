<?php

use Illuminate\Http\Request;
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

// Blogs
Route::prefix('blogs')->group(function () {
    Route::get('/', [BlogController::class, 'index']);
    Route::get('{blog}', [BlogController::class, 'show']);
    Route::get('slug/{blog:slug}', [BlogController::class, 'show']);
    Route::post('/', [BlogController::class, 'store']);
    Route::put('{blog}', [BlogController::class, 'update']);
    Route::put('slug/{blog:slug}', [BlogController::class, 'update']);
    Route::delete('{blog}', [BlogController::class, 'destroy']);
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
    Route::post('/', [FaqController::class, 'store']);
    Route::put('{faq}', [FaqController::class, 'update']);
    Route::put('slug/{faq:slug}', [FaqController::class, 'update']);
    Route::delete('{faq}', [FaqController::class, 'destroy']);
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
    Route::post('/', [ProductController::class, 'store']);
    Route::put('{product}', [ProductController::class, 'update']);
    Route::put('slug/{product:slug}', [ProductController::class, 'update']);
    Route::delete('{product}', [ProductController::class, 'destroy']);
    Route::delete('slug/{product:slug}', [ProductController::class, 'destroy']);
});

// Testimonials
Route::prefix('testimonials')->group(function () {
    Route::get('/', [TestimonialController::class, 'index']);
    Route::get('{testimonial}', [TestimonialController::class, 'show']);
    Route::get('slug/{testimonial:slug}', [TestimonialController::class, 'show']);
    Route::post('/', [TestimonialController::class, 'store']);
    Route::put('{testimonial}', [TestimonialController::class, 'update']);
    Route::put('slug/{testimonial:slug}', [TestimonialController::class, 'update']);
    Route::delete('{testimonial}', [TestimonialController::class, 'destroy']);
    Route::delete('slug/{testimonial:slug}', [TestimonialController::class, 'destroy']);
});

// Webinars
Route::prefix('webinars')->group(function () {
    Route::get('/', [WebinarController::class, 'index']);
    Route::get('{webinar}', [WebinarController::class, 'show']);
    Route::get('slug/{webinar:slug}', [WebinarController::class, 'show']);
    Route::post('/', [WebinarController::class, 'store']);
    Route::put('{webinar}', [WebinarController::class, 'update']);
    Route::put('slug/{webinar:slug}', [WebinarController::class, 'update']);
    Route::delete('{webinar}', [WebinarController::class, 'destroy']);
    Route::delete('slug/{webinar:slug}', [WebinarController::class, 'destroy']);
});

// Webinar Registrations
Route::prefix('webinar-registrations')->group(function () {
    Route::get('/', [WebinarRegistrationController::class, 'index']);
    Route::get('{webinarRegistration}', [WebinarRegistrationController::class, 'show']);
    Route::get('slug/{webinarRegistration:slug}', [WebinarRegistrationController::class, 'show']);
    Route::post('/', [WebinarRegistrationController::class, 'store']);
    Route::put('{webinarRegistration}', [WebinarRegistrationController::class, 'update']);
    Route::put('slug/{webinarRegistration:slug}', [WebinarRegistrationController::class, 'update']);
    Route::delete('{webinarRegistration}', [WebinarRegistrationController::class, 'destroy']);
    Route::delete('slug/{webinarRegistration:slug}', [WebinarRegistrationController::class, 'destroy']);
});

// Piliers
Route::prefix('piliers')->group(function () {
    Route::get('/', [PilierController::class, 'index']);
    Route::get('{pilier}', [PilierController::class, 'show']);
    Route::get('slug/{pilier:slug}', [PilierController::class, 'show']);
    Route::post('/', [PilierController::class, 'store']);
    Route::put('{pilier}', [PilierController::class, 'update']);
    Route::put('slug/{pilier:slug}', [PilierController::class, 'update']);
    Route::delete('{pilier}', [PilierController::class, 'destroy']);
    Route::delete('slug/{pilier:slug}', [PilierController::class, 'destroy']);
});

// Privileges
Route::prefix('privileges')->group(function () {
    Route::get('/', [PrivilegeController::class, 'index']);
    Route::get('{privilege}', [PrivilegeController::class, 'show']);
    Route::get('slug/{privilege:slug}', [PrivilegeController::class, 'show']);
    Route::post('/', [PrivilegeController::class, 'store']);
    Route::put('{privilege}', [PrivilegeController::class, 'update']);
    Route::put('slug/{privilege:slug}', [PrivilegeController::class, 'update']);
    Route::delete('{privilege}', [PrivilegeController::class, 'destroy']);
    Route::delete('slug/{privilege:slug}', [PrivilegeController::class, 'destroy']);
});

// Eva Features
Route::prefix('eva-features')->group(function () {
    Route::get('/', [EvaFeatureController::class, 'index']);
    Route::get('{evaFeature}', [EvaFeatureController::class, 'show']);
    Route::get('slug/{evaFeature:slug}', [EvaFeatureController::class, 'show']);
    Route::post('/', [EvaFeatureController::class, 'store']);
    Route::put('{evaFeature}', [EvaFeatureController::class, 'update']);
    Route::put('slug/{evaFeature:slug}', [EvaFeatureController::class, 'update']);
    Route::delete('{evaFeature}', [EvaFeatureController::class, 'destroy']);
    Route::delete('slug/{evaFeature:slug}', [EvaFeatureController::class, 'destroy']);
});
// Offers
Route::prefix('offers')->group(function () {
    Route::get('/', [OfferController::class, 'index']);
    Route::get('{offer}', [OfferController::class, 'show']);
    Route::get('slug/{offer:slug}', [OfferController::class, 'show']);
    Route::post('/', [OfferController::class, 'store']);
    Route::put('{offer}', [OfferController::class, 'update']);
    Route::put('slug/{offer:slug}', [OfferController::class, 'update']);
    Route::delete('{offer}', [OfferController::class, 'destroy']);
    Route::delete('slug/{offer:slug}', [OfferController::class, 'destroy']);
});

// Users
Route::prefix('users')->group(function () {
    Route::get('{id}', [UserController::class, 'show']);
    Route::post('/', [UserController::class, 'store']);
    Route::put('{id}', [UserController::class, 'update']);
    Route::delete('{id}', [UserController::class, 'destroy']);
});
