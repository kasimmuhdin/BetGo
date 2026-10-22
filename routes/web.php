<?php

use App\Http\Controllers\Admin\ContactSettingsController;
use App\Http\Controllers\Admin\ListingApprovalController;
use App\Http\Controllers\Admin\ListingController as AdminListingController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\UserVerificationController;
use App\Http\Controllers\Agent\DashboardController as AgentDashboardController;
use App\Http\Controllers\Agent\ListingController;
use App\Http\Controllers\Agent\MediaController as AgentMediaController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Home\HomeController;
use App\Http\Controllers\Home\PricingController;
use App\Http\Controllers\Home\PropertyContactController;
use App\Http\Controllers\Messages\ConversationController;
use App\Http\Controllers\Messages\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Owner\DashboardController as OwnerDashboardController;
use App\Http\Controllers\Owner\MediaController;
use App\Http\Controllers\Payment\BillingController;
use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\Payment\StripeWebhookController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleRequestController;
use App\Http\Controllers\SavedListingController;
use App\Models\Property;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\MarkdownPreviewController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Home\FaqController as HomeFaqController;
use App\Http\Controllers\Home\PageController as HomePageController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/property/{property}', [HomeController::class, 'show'])->name('property.show');
Route::post('/ajax-register', [PropertyContactController::class, 'ajaxRegister'])->name('ajax.register');



Route::middleware(['auth',  'role:admin'])->prefix('admin')
    ->name('admin.')->group(function () {
        Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('listings', [AdminListingController::class, 'index'])->name('listings.all');
        Route::get('listings/{listing}', [AdminListingController::class, 'show'])->name('listings.show');
        Route::post('listings/{listing}/unpublish', [AdminListingController::class, 'unpublish'])->name('listings.unpublish');

        // Pending review queue + moderation actions
        Route::get('listings-pending', [ListingApprovalController::class, 'index'])->name('listings.pending');
        Route::post('listings-pending/{listing}/approve', [ListingApprovalController::class, 'approve'])->name('listings.approve');
        Route::post('listings-pending/{listing}/reject', [ListingApprovalController::class, 'reject'])->name('listings.reject');


        /* User */
        Route::get('users', [UserVerificationController::class, 'index'])->name('users.all');
        Route::get('pending-users', [UserVerificationController::class, 'pending'])->name('users.pending');
        Route::get('users/{user}', [UserVerificationController::class, 'show'])->name('users.show');
        Route::post('users/{user}/approve', [UserVerificationController::class, 'approve'])->name('users.approve');
        Route::post('users/{user}/reject', [UserVerificationController::class, 'reject'])->name('users.reject');

        /* Payment */
        Route::get('payments', [AdminPaymentController::class, 'index'])->name('payments.index');
        Route::get('payments/{payment}', [AdminPaymentController::class, 'show'])->name('payments.show');
        Route::post('payments/{payment}/approve', [AdminPaymentController::class, 'approve'])->name('payments.approve');

        /* Pages */

        Route::post('markdown/preview', MarkdownPreviewController::class)
            ->middleware('throttle:60,1')->name('markdown.preview');

        Route::get('pages', [PageController::class, 'index'])->name('pages.index');
        Route::get('pages/{page}/edit', [PageController::class, 'edit'])->name('pages.edit');
        Route::put('pages/{page}', [PageController::class, 'update'])->name('pages.update');
        Route::put('settings/contact', [ContactSettingsController::class, 'update'])->name('settings.contact.update');

        // "reorder" must come BEFORE the resource, or {faq} would swallow it.
        Route::put('faqs/reorder', [FaqController::class, 'reorder'])->name('faqs.reorder');
        Route::patch('faqs/{faq}/toggle', [FaqController::class, 'toggle'])->name('faqs.toggle');
        Route::resource('faqs', FaqController::class)->except(['show']);
    });

/* Owner Begging */
Route::middleware(['auth',  'role:owner'])
    ->prefix('owner')
    ->name('owner.')
    ->group(function () {
        Route::get('dashboard', [OwnerDashboardController::class, 'index'])->name('dashboard');
        Route::resource('listings', \App\Http\Controllers\Owner\ListingController::class);
        Route::get('media', [MediaController::class, 'index'])->name('media.index');
        Route::post('media', [MediaController::class, 'store'])->name('media.store');
        Route::delete('media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');
        Route::get('/media/manage', [\App\Http\Controllers\Owner\MediaController::class, 'manage'])
            ->name('media.manage');
    });
Route::middleware(['auth',  'role:agent'])->prefix('agent')
    ->name('agent.')->group(function () {
        Route::get('dashboard', [AgentDashboardController::class, 'index'])->name('dashboard');
        Route::resource('listings', ListingController::class);
        Route::get('media', [AgentMediaController::class, 'index'])->name('media.index');
        Route::post('media', [AgentMediaController::class, 'store'])->name('media.store');
        Route::delete('media/{media}', [AgentMediaController::class, 'destroy'])->name('media.destroy');
        Route::get('/media/manage', [AgentMediaController::class, 'manage'])
            ->name('media.manage');
    });
Route::middleware(['auth',   'role:user'])->group(function () {
    Route::get('user/dashboard', [DashboardController::class, 'users'])->name('user.dashboard');
});
Route::middleware(['auth'])->group(function () {
    Route::get('/messages', [ConversationController::class, 'index'])->name('messages.index');
    Route::get('/messages/compose', [ConversationController::class, 'compose'])->name('messages.compose');
    Route::post('/messages', [ConversationController::class, 'store'])->name('messages.store');
    Route::get('/messages/{conversation}', [ConversationController::class, 'show'])->name('messages.show');
    Route::post('/properties/{property}/messages', [ConversationController::class, 'start'])->name('messages.start');
    Route::post('/messages/{conversation}/reply', [MessageController::class, 'store'])->name('messages.reply');
    Route::post('/messages/{conversation}/read', [MessageController::class, 'markAsRead']);


    /* Payments */

    Route::get('payments/pricing', [PaymentController::class, 'pricing'])->name('payments.pricing');
    Route::post('payments/initiate', [PaymentController::class, 'initiate'])->name('payments.initiate');
    Route::get('payments/chapa/return', [PaymentController::class, 'return'])->name('payments.chapa.return');


    Route::post('payments/stripe/initiate', [PaymentController::class, 'initiateStripe'])->name('payments.stripe.initiate');
    Route::get('payments/stripe/return', [PaymentController::class, 'stripeReturn'])->name('payments.stripe.return');


    Route::get('payments/billing', [BillingController::class, 'index'])->name('payments.billing');
    Route::post('payments/billing/cancel', [BillingController::class, 'cancel'])->name('payments.billing.cancel');
    Route::post('payments/billing/resume', [BillingController::class, 'resume'])->name('payments.billing.resume');
    Route::post('payments/billing/upgrade', [BillingController::class, 'upgrade'])->name('payments.billing.upgrade');
});
Route::post('payments/chapa/webhook', [PaymentController::class, 'webhook'])->name('payments.chapa.webhook');
Route::post('payments/stripe/webhook', [StripeWebhookController::class, 'handleWebhook'])->name('payments.stripe.webhook');


Route::post('/role-requests', [RoleRequestController::class, 'store'])->name('role-requests.store');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    Route::post('/property/{property}/message', [PropertyContactController::class, 'sendMessage'])->name('property.message');

    Route::get('/dashboard', fn() => redirect(auth()->user()->dashboardUrl()))->name('dashboard');
});
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('saved-listings', [SavedListingController::class, 'index'])->name('saved-listings.index');

    Route::middleware('throttle:60,1')->group(function () {
        Route::post('saved-listings/{property}', [SavedListingController::class, 'store'])->name('saved-listings.store');
        Route::delete('saved-listings/{property}', [SavedListingController::class, 'destroy'])->name('saved-listings.destroy');
    });
});

Route::get('saved-listings/{property}/login', function (Property $property) {
    session(['url.intended' => route('property.show', $property)]);

    return redirect()->route('login')->with('status', 'Log in to save lists');
})->middleware('guest')->name('saved-listings.guest');

Route::get('faq', [HomeFaqController::class, 'index'])->name('faq');


Route::get('pricing', [PricingController::class, 'index'])->name('pricing');

require __DIR__ . '/auth.php';

Route::get('{page}', [HomePageController::class, 'show'])
    ->where('page', '[a-z0-9\-]+')
    ->name('pages.show');
