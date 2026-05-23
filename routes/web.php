<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SupportRequestController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\EventAdminController;
use App\Http\Controllers\EventTypeAdminController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\RsvpController;
use App\Http\Controllers\MemberDashboardController;
use App\Http\Controllers\OperatorAdminController;
use App\Http\Controllers\RoleAdminController;
use App\Http\Controllers\AlertStatusController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AvatarController;
use App\Http\Controllers\LivePropagationController;
use App\Models\Event;
use App\Models\AlertStatus;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\Admin\TemporaryGuestController;
use App\Http\Controllers\Admin\CallsignController;
use App\Http\Controllers\Admin\ImpersonationController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AccountControlController;
use App\Http\Controllers\Admin\AdminMessageController;
use App\Http\Controllers\Admin\LmsAdminController;
use App\Http\Controllers\LearningController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Admin\EventAssignmentController;
use App\Http\Controllers\OperatorBriefController;
use App\Http\Controllers\Auth\OAuthController;
use App\Http\Controllers\Admin\OAuthClientController;
use App\Http\Controllers\Admin\PageEditorController;
use App\Http\Controllers\Admin\PageBuilderController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\ResourceController;
// ── Committee ──────────────────────────────────────────────────────────────
use App\Http\Controllers\Committee\CommitteeDashboardController;
use App\Http\Controllers\Committee\ReadinessController;
use App\Http\Controllers\Committee\PeopleController;
use App\Http\Controllers\Committee\AssetsController;
use App\Http\Controllers\Committee\NetworksController;
use App\Http\Controllers\Committee\ExercisesController;
use App\Http\Controllers\Committee\ActionsController;
use App\Http\Controllers\Committee\RisksController;

use App\Http\Controllers\DmrNetworkController;
use App\Http\Controllers\DmrLogStreamController;
use App\Http\Controllers\Admin\UserRoleController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\AdminAvailabilityController;

use App\Http\Controllers\InstallPreviewController;

/*
|--------------------------------------------------------------------------
| INSTALL WIZARD — runs only when site is not yet installed
|--------------------------------------------------------------------------
*/

// Public QRZ lookup (used on registration page — no auth required)
Route::get('/qrz-lookup/{callsign}', [\App\Http\Controllers\ProfileController::class, 'qrzLookup'])
     ->name('qrz.lookup.public')
     ->where('callsign', '[A-Za-z0-9]+');

Route::middleware('not.installed')->group(function () {
    Route::get('/install',           [InstallController::class, 'index'])    ->name('install.index');
    Route::get('/install/step1',     [InstallController::class, 'step1'])    ->name('install.step1');
    Route::post('/install/step1',    [InstallController::class, 'step1Post'])->name('install.step1.post');
    Route::get('/install/step2',     [InstallController::class, 'step2'])    ->name('install.step2');
    Route::post('/install/step2',    [InstallController::class, 'step2Post'])->name('install.step2.post');
    Route::get('/install/step3',     [InstallController::class, 'step3'])    ->name('install.step3');
    Route::post('/install/complete', [InstallController::class, 'complete']) ->name('install.complete');
});


Route::get('/install/welcome', function () {
    return view('install.welcome');
})->middleware(['web', 'auth'])->name('install.welcome');

/*
|--------------------------------------------------------------------------
| PUBLIC PAGES (NO LOGIN REQUIRED)
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    $today    = Carbon::today();
    $upcoming = Event::where('starts_at', '>=', $today)->orderBy('starts_at')->get();
    $alertStatus = AlertStatus::query()->first();
    $featuredPhotos = \App\Models\Photo::where('status','approved')->where('featured',true)->orderByDesc('created_at')->take(6)->get();
    $netActive = \App\Models\Setting::get('net_active','0') === '1';
    if ($netActive) {
        $netStartTime = \App\Models\Setting::get('net_start_time','');
        $netEndTime   = \App\Models\Setting::get('net_end_time','');
        $now = \Carbon\Carbon::now()->timezone('Europe/London');
        $nowTs = $now->timestamp;

        $startTs = null;
        $endTs   = null;
        if ($netStartTime) {
            $startCandidate = \Carbon\Carbon::createFromFormat('H:i', $netStartTime, 'Europe/London')
                                ->setDate($now->year, $now->month, $now->day);
            $endCandidate   = $netEndTime
                ? \Carbon\Carbon::createFromFormat('H:i', $netEndTime, 'Europe/London')
                                ->setDate($now->year, $now->month, $now->day)
                : null;
            if ($endCandidate && $endCandidate->lte($startCandidate)) $endCandidate->addDay();
            $windowOpen = $startCandidate->copy()->subMinutes(90);
            if ($nowTs >= $windowOpen->timestamp && (!$endCandidate || $nowTs < $endCandidate->timestamp)) {
                $startTs = $startCandidate->timestamp;
                $endTs   = $endCandidate ? $endCandidate->timestamp : null;
            } else {
                $startTomorrow  = $startCandidate->copy()->addDay();
                $endTomorrow    = $endCandidate ? $endCandidate->copy()->addDay() : null;
                $windowTomorrow = $startTomorrow->copy()->subMinutes(90);
                if ($nowTs >= $windowTomorrow->timestamp && (!$endTomorrow || $nowTs < $endTomorrow->timestamp)) {
                    $startTs = $startTomorrow->timestamp;
                    $endTs   = $endTomorrow ? $endTomorrow->timestamp : null;
                }
            }
        }

        // Resolve active controller from time slots
        $nowTime    = \Carbon\Carbon::now('Europe/London')->format('H:i');
        $ctrlSlots  = json_decode(\App\Models\Setting::get('net_controller_slots','[]'), true) ?? [];
        $activeController = count($ctrlSlots) ? '' : \App\Models\Setting::get('net_controller','');
        foreach ($ctrlSlots as $slot) {
            if (!empty($slot['callsign']) && !empty($slot['from']) && !empty($slot['to'])) {
                if ($nowTime >= $slot['from'] && $nowTime < $slot['to']) {
                    $activeController = strtoupper($slot['callsign']);
                    break;
                }
            }
        }

        $netData = [
            'callsign'         => \App\Models\Setting::get('net_callsign',''),
            'frequency'        => \App\Models\Setting::get('net_frequency',''),
            'controller'       => $activeController,
            'controller_slots' => $ctrlSlots,
            'description'      => \App\Models\Setting::get('net_description',''),
            'announcement'     => \App\Models\Setting::get('net_announcement',''),
            'band'             => \App\Models\Setting::get('net_band',''),
            'priority'         => \App\Models\Setting::get('net_priority','routine'),
            'start_time'       => $netStartTime,
            'end_time'         => $netEndTime,
            'start_ts'         => $startTs ?? $nowTs,
            'end_ts'           => $endTs   ?? 0,
            'now_ts'           => $nowTs,
        ];
    } else {
        $netData = null;
    }
    return view('pages.home', [
        'featuredPhotos' => $featuredPhotos,
        'nextEvent'   => $upcoming->first(),
        'upcomingEvents' => $upcoming->slice(1),
        'alertStatus' => $alertStatus,
        'netData'     => $netData,
    ]);
})->name('home');

// Event Support Pack
Route::middleware(['auth'])->group(function() {
    Route::get('/event-pack',                                    [\App\Http\Controllers\EventSupportPackController::class, 'index'])           ->name('event-pack.index');
    Route::get('/event-pack/create',                             [\App\Http\Controllers\EventSupportPackController::class, 'create'])          ->name('event-pack.create');
    Route::post('/event-pack',                                   [\App\Http\Controllers\EventSupportPackController::class, 'store'])           ->name('event-pack.store');
    Route::get('/event-pack/{eventSupportPack}',                 [\App\Http\Controllers\EventSupportPackController::class, 'show'])            ->name('event-pack.show');
    Route::patch('/event-pack/{eventSupportPack}',               [\App\Http\Controllers\EventSupportPackController::class, 'update'])          ->name('event-pack.update');
    Route::post('/event-pack/{eventSupportPack}/submit',         [\App\Http\Controllers\EventSupportPackController::class, 'submit'])          ->name('event-pack.submit');
    Route::post('/event-pack/{eventSupportPack}/approve',        [\App\Http\Controllers\EventSupportPackController::class, 'approve'])         ->name('event-pack.approve');
    Route::post('/event-pack/{eventSupportPack}/escalate',       [\App\Http\Controllers\EventSupportPackController::class, 'escalate'])        ->name('event-pack.escalate');
    Route::post('/event-pack/{eventSupportPack}/return',         [\App\Http\Controllers\EventSupportPackController::class, 'return_for_correction'])->name('event-pack.return');
    Route::post('/event-pack/{eventSupportPack}/clone',          [\App\Http\Controllers\EventSupportPackController::class, 'clone'])           ->name('event-pack.clone');
    Route::get('/event-pack/{eventSupportPack}/pdf/{type?}',     [\App\Http\Controllers\EventSupportPackController::class, 'generatePdf'])     ->name('event-pack.pdf');
    Route::post('/event-pack/{eventSupportPack}/posts',          [\App\Http\Controllers\EventSupportPackController::class, 'storePost'])       ->name('event-pack.posts.store');
    Route::delete('/event-pack/posts/{post}',                    [\App\Http\Controllers\EventSupportPackController::class, 'destroyPost'])     ->name('event-pack.posts.destroy');
    Route::post('/event-pack/{eventSupportPack}/operators',      [\App\Http\Controllers\EventSupportPackController::class, 'storeOperator'])   ->name('event-pack.operators.store');
    Route::delete('/event-pack/operators/{operator}',            [\App\Http\Controllers\EventSupportPackController::class, 'destroyOperator']) ->name('event-pack.operators.destroy');
});

Route::get('/risk-assessment',              [\App\Http\Controllers\RiskAssessmentController::class, 'index'])      ->name('risk-assessment.index');
Route::get('/risk-assessment/create',        [\App\Http\Controllers\RiskAssessmentController::class, 'create'])     ->name('risk-assessment.create');
Route::post('/risk-assessment',              [\App\Http\Controllers\RiskAssessmentController::class, 'store'])       ->name('risk-assessment.store');
Route::get('/risk-assessment/{riskAssessment}',          [\App\Http\Controllers\RiskAssessmentController::class, 'show'])        ->name('risk-assessment.show')->middleware('auth');
Route::get('/risk-assessment/{riskAssessment}/pdf',      [\App\Http\Controllers\RiskAssessmentController::class, 'generatePdf'])->name('risk-assessment.pdf')->middleware('auth');
Route::post('/risk-assessment/{riskAssessment}/approve', [\App\Http\Controllers\RiskAssessmentController::class, 'approve'])    ->name('risk-assessment.approve')->middleware('auth');

Route::get('/gallery', [\App\Http\Controllers\GalleryController::class, 'index'])->name('gallery');
Route::view('/about',         'pages.about')->name('about');
Route::view('/event-support', 'pages.event-support')->name('event-support');
Route::view('/training',      'pages.training')->name('training');
Route::view('/cookies',       'pages.cookies')->name('cookies');
Route::view('/guest-expired', 'auth.guest-expired')->name('guest.expired');
Route::view('/privacy',       'pages.privacy')->name('privacy');
Route::view('/test', 'pages.test')->name('test');

Route::get('/data-dashboard', [LivePropagationController::class, 'index'])->name('data-dashboard');

// ── Resources ──────────────────────────────────────────────────────────────
Route::get('/library', [ResourceController::class, 'index'])->name('resources.index');
Route::get('/library/download/{resource}', [ResourceController::class, 'download'])->name('resources.download');
Route::get('/library/{resource}/inline', [App\Http\Controllers\ResourceController::class, 'inline'])->name('resources.inline');
Route::get('/library/{resource}/preview',  [ResourceController::class, 'preview'])->name('resources.preview');

// Resources — auth required
Route::middleware(['auth','verified'])->group(function () {
    Route::post('/library/{resource}/bookmark',  [ResourceController::class, 'bookmark'])->name('resources.bookmark');
    Route::post('/library/follow-category',      [ResourceController::class, 'followCategory'])->name('resources.follow-category');
    Route::get('/library/{resource}/audit',      [ResourceController::class, 'auditLog'])->name('resources.audit');
});

Route::get('/request-support',  [SupportRequestController::class, 'create'])->name('request-support');
Route::post('/request-support', [SupportRequestController::class, 'store'])->name('request-support.submit');

Route::get('/register/pending', fn() => view('auth.register-pending'))->name('register.pending');
Route::get('/member-application', [App\Http\Controllers\MemberApplicationController::class, 'show'])->name('member-application');
Route::post('/member-application', [App\Http\Controllers\MemberApplicationController::class, 'submit'])->name('member-application.submit');
Route::get('/member-application/success', [App\Http\Controllers\MemberApplicationController::class, 'success'])->name('member-application.success');
Route::get('/member-application/sign/{token}',        [App\Http\Controllers\MemberApplicationController::class, 'signPage'])->name('member-application.sign');
Route::post('/member-application/sign/{token}',       [App\Http\Controllers\MemberApplicationController::class, 'signSubmit'])->name('member-application.sign.submit');
Route::get('/member-application/sign/{token}/status', [App\Http\Controllers\MemberApplicationController::class, 'signStatus'])->name('member-application.sign.status');
Route::post('/member-application/sign-token',         [App\Http\Controllers\MemberApplicationController::class, 'generateSignToken'])->name('member-application.sign-token');


// Email open tracking pixel — public, no auth
Route::get('/track/email-open/{token}', function (string $token) {
    $recipient = \App\Models\AdminNotificationRecipient::where('email_token', $token)->first();
    if ($recipient) {
        if (!$recipient->email_opened_at) {
            $recipient->update(['email_opened_at' => now()]);
        }
        if (!$recipient->read_at) {
            $recipient->update(['read_at' => now()]);
        }
    }
    return response(
        base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'),
        200,
        [
            'Content-Type'  => 'image/gif',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma'        => 'no-cache',
            'Expires'       => 'Sat, 01 Jan 2000 00:00:00 GMT',
        ]
    );
})->name('track.email-open');

/*
|--------------------------------------------------------------------------
| TELEGRAM WEBHOOK — public, no auth required
|--------------------------------------------------------------------------
*/
Route::post('/telegram/webhook', [\App\Http\Controllers\TelegramWebhookController::class, 'handle'])
    ->name('telegram.webhook');

/*
|--------------------------------------------------------------------------
| SSO — OIDC / OAUTH 2.0 PUBLIC ENDPOINTS
|--------------------------------------------------------------------------
*/
Route::get('/.well-known/openid-configuration', [OAuthController::class, 'discovery'])
    ->name('oidc.discovery');

Route::middleware('auth:api')->get('/oauth/userinfo', [OAuthController::class, 'userinfo'])
    ->name('oauth.userinfo');

Route::post('/oauth/introspect', [OAuthController::class, 'introspect'])
    ->name('oauth.introspect');

Route::get('/oauth/logout', [OAuthController::class, 'logout'])
    ->name('oauth.logout');

/*
|--------------------------------------------------------------------------
| PROPAGATION DATA PROXY — public, no auth required
|--------------------------------------------------------------------------
*/
Route::get('/api/propagation', function () {
    $xml = \Illuminate\Support\Facades\Cache::remember('propagation_xml', 600, function () {
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(10)
                ->withHeaders(['User-Agent' => 'Liverpool-RAYNET/1.0'])
                ->get('https://www.hamqsl.com/solarxml.php');
            return $response->successful() ? $response->body() : null;
        } catch (\Throwable $e) {
            return null;
        }
    });

    if (! $xml) {
        return response(
            '<?xml version="1.0"?><solar><solardata><updated>unavailable</updated></solardata></solar>',
            503
        )->header('Content-Type', 'text/xml');
    }
    return response($xml, 200)
        ->header('Content-Type', 'text/xml; charset=UTF-8')
        ->header('Cache-Control', 'public, max-age=600');
})->name('api.propagation');

/*
|--------------------------------------------------------------------------
| HAMDASH AGGREGATE DATA PROXY — public, no auth required
|--------------------------------------------------------------------------
*/
Route::get('/api/hamdash', function () {
    $data = \Illuminate\Support\Facades\Cache::store('file')->remember('hamdash_data', 120, function () {
        try {
            $anonKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImhvbmJ6a2V0c2tsbG16bWxyYXByIiwicm9sZSI6ImFub24iLCJpYXQiOjE3MDg2NTUyMDksImV4cCI6MjAyNDIzMTIwOX0.fKHHn4I5nEzaGrFtS_J5vLPJJSR8qnRAVrP7w3j8YjA';
            $response = \Illuminate\Support\Facades\Http::timeout(10)
                ->withHeaders([
                    'apikey'        => $anonKey,
                    'Authorization' => 'Bearer ' . $anonKey,
                    'User-Agent'    => 'Liverpool-RAYNET/1.0',
                ])
                ->get('https://honbzketskllmzmlrapr.supabase.co/functions/v1/aggregate-data');
            return $response->successful() ? $response->body() : null;
        } catch (\Throwable $e) {
            return null;
        }
    });

    if (! $data) {
        return response()->json(['error' => 'unavailable'], 503);
    }
    return response($data, 200)
        ->header('Content-Type', 'application/json')
        ->header('Cache-Control', 'public, max-age=120');
})->name('api.hamdash');

/*
|--------------------------------------------------------------------------
| CALENDAR (PUBLIC)
|--------------------------------------------------------------------------
*/
Route::get('/calendar/{year}/{month}.ics', [CalendarController::class, 'ics'])
    ->where(['year' => '[0-9]{4}', 'month' => '[0-1][0-9]'])
    ->name('calendar.ics');

Route::get('/calendar/{year?}/{month?}', [CalendarController::class, 'index'])
    ->where(['year' => '[0-9]{4}', 'month' => '[0-1][0-9]'])
    ->name('calendar');

/*
|--------------------------------------------------------------------------
| PUBLIC EVENTS
|--------------------------------------------------------------------------
*/
Route::get('/events', [EventController::class, 'index'])->name('events.index');

Route::get('/events/{year}/{month}/{slug}', [EventController::class, 'show'])
    ->where(['year' => '[0-9]{4}', 'month' => '[0-1][0-9]', 'slug' => '[A-Za-z0-9\-]+'])
    ->name('events.show');

Route::get('/events/{year}/{month}/{slug}.ics', [EventController::class, 'ics'])
    ->where(['year' => '[0-9]{4}', 'month' => '[0-1][0-9]', 'slug' => '[A-Za-z0-9\-]+'])
    ->name('events.ics');

Route::get('/events/documents/{document}/download', [EventController::class, 'downloadDocument'])
    ->name('events.documents.download');

Route::get('/events/availability/{token}', [EventAdminController::class, 'availabilityResponse'])->name('events.availability.respond');

/*
|--------------------------------------------------------------------------
| OPERATOR BRIEF & CHECK-IN — public, no auth required
|--------------------------------------------------------------------------
*/
Route::prefix('operator-brief')->group(function () {
    Route::get('/{token}',           [OperatorBriefController::class, 'show'])      ->name('operator.brief');
    Route::post('/{token}/check-in', [OperatorBriefController::class, 'checkIn'])   ->name('operator.brief.check-in');
    Route::post('/{token}/break-start', [OperatorBriefController::class, 'breakStart'])->name('operator.brief.break-start');
    Route::post('/{token}/break-end',   [OperatorBriefController::class, 'breakEnd'])  ->name('operator.brief.break-end');
    Route::post('/{token}/check-out',   [OperatorBriefController::class, 'checkOut'])  ->name('operator.brief.check-out');
});


/*
|--------------------------------------------------------------------------
| CALLSIGN LOOKUP — public, no auth required
|--------------------------------------------------------------------------
*/
Route::post('/password/resolve-callsign', function (\Illuminate\Http\Request $request) {
    $request->validate(['callsign' => ['required', 'string', 'max:20']]);
    $user = \App\Models\User::where('callsign', strtoupper(trim($request->callsign)))->first();
    if (! $user) {
        return response()->json(['email' => null], 404);
    }
    return response()->json(['email' => $user->email]);
})->middleware('throttle:10,5')->name('password.resolve-callsign');

/*
|--------------------------------------------------------------------------
| EMAIL VERIFICATION (AUTH REQUIRED, verified NOT required)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});


/*
|--------------------------------------------------------------------------
| MEMBERS AREA (AUTH + VERIFIED REQUIRED)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/members', MemberDashboardController::class)->name('members');

    // ── Resources management (committee and above) ───────────────────────────
    Route::middleware(['role:committee|admin|super-admin'])->group(function () {
        // ── Resources management ─────────────────────────────────────────────
        Route::post('/library',                        [ResourceController::class, 'store'])->name('resources.store');
        Route::patch('/library/{resource}',            [ResourceController::class, 'update'])->name('resources.update');
        Route::patch('/library/{resource}/approve',    [ResourceController::class, 'approve'])->name('resources.approve');
        Route::post('/library/{resource}/new-version', [ResourceController::class, 'newVersion'])->name('resources.new-version');
        Route::delete('/library/{resource}',           [ResourceController::class, 'destroy'])->name('resources.destroy');
    });

    Route::post('/events/{event}/rsvp',   [RsvpController::class, 'store'])  ->name('events.rsvp.store');
    Route::delete('/events/{event}/rsvp', [RsvpController::class, 'destroy'])->name('events.rsvp.destroy');

    // ── Operational Map ───────────────────────────────────────────────────
    Route::get('/ops-map', fn() => view('pages.ops-map'))->name('ops-map');

    Route::prefix('ops-map')->name('ops-map.')->controller(\App\Http\Controllers\OpsMapController::class)->group(function () {
        Route::get('aprs',       'aprs')      ->name('aprs');
        Route::get('meshtastic', 'meshtastic')->name('meshtastic');
        Route::get('coverage',   'coverage')  ->name('coverage');
        Route::get('flood',      'flood')     ->name('flood');
        Route::get('weather',    'weather')   ->name('weather');
        Route::get('wind',       'wind')      ->name('wind');
        Route::get('power',      'power')     ->name('power');
    });


    Route::get('/profile',              [ProfileController::class, 'edit'])   ->name('profile.edit');
    Route::post('/profile',             [ProfileController::class, 'update']) ->name('profile.update');
    Route::post('/profile/avatar',      [AvatarController::class, 'update'])  ->name('profile.avatar.update');
    Route::post('/profile/avatar/crop', [AvatarController::class, 'crop'])    ->name('profile.avatar.crop');
    Route::delete('/profile/avatar',    [AvatarController::class, 'destroy']) ->name('profile.avatar.destroy');
    Route::post('/profile/avatar/qrz', [App\Http\Controllers\ProfileController::class, 'importQrzAvatar'])
    ->name('profile.avatar.qrz');

    Route::get('/members/activity/{year?}/{month?}', [\App\Http\Controllers\ActivityCalendarController::class, 'show'])
        ->where(['year' => '[0-9]{4}', 'month' => '[0-1][0-9]|[1-9]'])
        ->name('members.activity');

    Route::post('/dismiss-message',   [AdminMessageController::class, 'dismiss'])          ->name('message.dismiss');
    Route::post('/dismiss-broadcast', [AdminMessageController::class, 'dismissBroadcast']) ->name('message.dismiss-broadcast');

    Route::get('/my-tagged-photos',                      [\App\Http\Controllers\PhotoTagController::class, 'myTagged'])  ->name('members.photos.tagged');
    Route::get('/members/photos/tags/{tag}/remove',    [\App\Http\Controllers\PhotoTagController::class, 'removeSelf'])->name('members.photos.tags.remove-self-get');
    Route::delete('/members/photos/tags/{tag}/remove',   [\App\Http\Controllers\PhotoTagController::class, 'removeSelf'])->name('members.photos.tags.remove-self');
    Route::post('/members/photos/{photo}/tags',           [\App\Http\Controllers\PhotoTagController::class, 'store'])  ->name('members.photos.tags.store');
    Route::delete('/members/photos/{photo}/tags/{tag}',   [\App\Http\Controllers\PhotoTagController::class, 'destroy'])->name('members.photos.tags.destroy');
    Route::get('/members/albums',                          [\App\Http\Controllers\MemberAlbumController::class, 'index'])         ->name('members.albums.index');
    Route::post('/members/albums',                         [\App\Http\Controllers\MemberAlbumController::class, 'store'])          ->name('members.albums.store');
    Route::patch('/members/albums/{album}',                [\App\Http\Controllers\MemberAlbumController::class, 'update'])         ->name('members.albums.update');
    Route::delete('/members/albums/{album}',               [\App\Http\Controllers\MemberAlbumController::class, 'destroy'])        ->name('members.albums.destroy');
    Route::post('/members/albums/{album}/assign',          [\App\Http\Controllers\MemberAlbumController::class, 'assignPhoto'])    ->name('members.albums.assign');
    Route::post('/members/albums/{album}/remove-photo',    [\App\Http\Controllers\MemberAlbumController::class, 'removePhoto'])    ->name('members.albums.remove-photo');
    Route::post('/members/albums/{album}/cover',           [\App\Http\Controllers\MemberAlbumController::class, 'setCover'])       ->name('members.albums.cover');
    Route::get('/members/albums/{album}/photos', function(\App\Models\Album $album) {
        abort_if($album->user_id !== auth()->id(), 403);
        return response()->json($album->photos->map(fn($p) => [
            'id'       => $p->id,
            'thumb'    => $p->thumbUrl(),
            'caption'  => $p->caption,
            'is_cover' => $album->cover_photo_id == $p->id,
        ]));
    })->name('members.albums.photos');
    Route::post('/members/albums/{album}/submit',          [\App\Http\Controllers\MemberAlbumController::class, 'submit'])         ->name('members.albums.submit');
    Route::post('/members/albums/submit-unassigned',       [\App\Http\Controllers\MemberAlbumController::class, 'submitUnassigned'])->name('members.albums.submit-unassigned');
    Route::get('/members/my-photos', function() {
        $myPhotos = \App\Models\Photo::where('user_id', auth()->id())->orderByDesc('created_at')->get();
        return view('members.my-photos', compact('myPhotos'));
    })->name('members.my-photos');
    Route::get('/members/photos/{photo}/url',     [\App\Http\Controllers\MemberPhotoController::class, 'getUrl'])  ->name('members.photos.url');
    Route::post('/members/photos/{photo}/rotate',  [\App\Http\Controllers\MemberPhotoController::class, 'rotate']) ->name('members.photos.rotate');
    Route::post('/members/photos/{photo}/submit', [\App\Http\Controllers\MemberPhotoController::class, 'submitForApproval'])->name('members.photos.submit');
    Route::post('/members/photos/notify', [\App\Http\Controllers\MemberPhotoController::class, 'notifyApprovers'])->name('members.photos.notify');
    Route::post('/members/photos',           [\App\Http\Controllers\MemberPhotoController::class, 'store'])  ->name('members.photos.store');
    Route::patch('/members/photos/{photo}',  [\App\Http\Controllers\MemberPhotoController::class, 'update'])->name('members.photos.update');
    Route::delete('/members/photos/{photo}', [\App\Http\Controllers\MemberPhotoController::class, 'destroy'])->name('members.photos.destroy');
    Route::get('/members/photo-approval',                    [\App\Http\Controllers\MemberPhotoApprovalController::class, 'index'])        ->name('members.photo-approval.index');
    Route::post('/members/photo-approval/{photo}/approve',  [\App\Http\Controllers\MemberPhotoApprovalController::class, 'approve'])       ->name('members.photo-approval.approve');
    Route::post('/members/photo-approval/{photo}/public',   [\App\Http\Controllers\MemberPhotoApprovalController::class, 'publicApprove']) ->name('members.photo-approval.public-approve');
    Route::post('/members/photo-approval/{photo}/reject',   [\App\Http\Controllers\MemberPhotoApprovalController::class, 'reject'])        ->name('members.photo-approval.reject');
    Route::post('/members/photo-approval/{photo}/feature',  [\App\Http\Controllers\MemberPhotoApprovalController::class, 'feature'])       ->name('members.photo-approval.feature');
    Route::get('/members/refer',                   [\App\Http\Controllers\MemberReferralController::class, 'show'])  ->name('members.refer');
    Route::post('/members/refer',                  [\App\Http\Controllers\MemberReferralController::class, 'send'])  ->name('members.refer.send');
    Route::get('/members/refer/lookup/{callsign}', [\App\Http\Controllers\MemberReferralController::class, 'lookup'])->name('members.refer.lookup');

    Route::get('/members/radioid-lookup/{callsign}', function (string $callsign) {
        $response = \Illuminate\Support\Facades\Http::timeout(5)->get(
            'https://database.radioid.net/api/users',
            ['callsign' => strtoupper($callsign)]
        );
        return response()->json($response->json());
    })->name('members.radioid.lookup');

    // ── Notifications ──────────────────────────────────────────────────────
    Route::get('/members/notifications/recent', function () {
        try {
            $rows = \App\Models\AdminNotificationRecipient::with(['notification', 'notification.sender'])
                ->where('user_id', auth()->id())
                ->whereNull('removed_at')
                ->orderByDesc('created_at')
                ->take(15)
                ->get();
            $notifs = $rows
                ->filter(fn($r) => $r->notification !== null)
                ->map(fn($r) => [
                    'id'       => $r->id,
                    'title'    => $r->notification->title,
                    'body'     => $r->notification->body ?? '',
                    'priority' => $r->notification->priority,
                    'from_hq'  => $r->notification->sent_by === null,
                    'read_at'  => $r->read_at,
                    'ago'      => $r->created_at->diffForHumans(),
                ])
                ->values();
            return response()->json([
                'notifications' => $notifs,
                'unread_count'  => $notifs->filter(fn($n) => !$n['read_at'])->count(),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['notifications' => [], 'unread_count' => 0]);
        }
    })->name('notifications.recent');

    Route::post('/members/notifications/mark-all-read', function () {
        \App\Models\AdminNotificationRecipient::where('user_id', auth()->id())
            ->whereNull('read_at')->whereNull('removed_at')
            ->update(['read_at' => now()]);
        return response()->json(['ok' => true]);
    })->name('notifications.mark-all-read');

    Route::post('/members/notifications/{id}/read', function (string $id) {
        \App\Models\AdminNotificationRecipient::where('id', $id)
            ->where('user_id', auth()->id())
            ->update(['read_at' => now()]);
        return response()->json(['ok' => true]);
    })->name('notifications.read');

    // ── LMS STUDENT PORTAL ─────────────────────────────────────────────────
    Route::prefix('my-training')->name('lms.')->group(function () {
        Route::get('/',                       [LearningController::class, 'index'])     ->name('index');
        Route::get('/certificate/{courseId}', [LearningController::class, 'certificate'])->name('certificate');
        Route::post('/lesson/{id}/complete',  [LearningController::class, 'complete'])  ->name('complete');
        Route::post('/quiz/{id}/submit',      [LearningController::class, 'submitQuiz'])->name('quiz.submit');
        Route::get('/{slug}/lesson/{id}',     [LearningController::class, 'lesson'])    ->name('lesson');
        Route::get('/{slug}/quiz/{id}',       [LearningController::class, 'quiz'])      ->name('quiz');
        Route::get('/{slug}',                 [LearningController::class, 'show'])      ->name('course');
    });


    Route::get('/my-training/scorm/{lessonId}',          [\App\Http\Controllers\ScormController::class, 'play'])  ->name('lms.scorm.play');
    Route::post('/my-training/scorm/{lessonId}/api/set', [\App\Http\Controllers\ScormController::class, 'apiSet'])->name('lms.scorm.api.set');
    Route::get('/my-training/scorm/{lessonId}/api/get',  [\App\Http\Controllers\ScormController::class, 'apiGet'])->name('lms.scorm.api.get');

    Route::prefix('members/dmr-network')->name('dmr.')->group(function () {
        Route::get('/',          [DmrNetworkController::class, 'index']        )->name('index');
        Route::get('/lastheard', [DmrNetworkController::class, 'lastheard']    )->name('lastheard');
        Route::get('/peers',     [DmrNetworkController::class, 'peers']        )->name('peers');
        Route::get('/stream',    [DmrLogStreamController::class, 'stream']     )->name('stream');
        Route::get('/api/masters',   [DmrNetworkController::class, 'masters']      )->name('api.masters');
        Route::get('/api/lastheard', [DmrNetworkController::class, 'apiLastheard'] )->name('api.lastheard');
    });


    // ── COMMITTEE ──────────────────────────────────────────────────────────
    Route::prefix('committee')->name('committee.')->middleware('committee')->group(function () {
        Route::get('/', [CommitteeDashboardController::class, 'index'])->name('dashboard');

        Route::prefix('readiness')->name('readiness.')->group(function () {
            Route::get('/',       [ReadinessController::class, 'index'])->name('index');
            Route::get('/matrix', [ReadinessController::class, 'matrix'])->name('matrix');
            Route::post('/score/{indicator}', [ReadinessController::class, 'updateScore'])->name('score');
            Route::get('/lrf',    [ReadinessController::class, 'lrf'])->name('lrf');
            Route::post('/lrf/service-levels', [ReadinessController::class, 'updateServiceLevels'])->name('service-levels');
        });


        Route::prefix('people')->name('people.')->group(function () {
            Route::get('/',            [PeopleController::class, 'index'])->name('index');
            Route::get('/{user}/edit', [PeopleController::class, 'edit'])->name('edit');
            Route::put('/{user}',      [PeopleController::class, 'update'])->name('update');
        });


        Route::resource('assets',    AssetsController::class)   ->except(['show']);
        Route::resource('networks',  NetworksController::class)  ->except(['show']);
        Route::resource('exercises', ExercisesController::class) ->except(['show']);
        Route::resource('actions',   ActionsController::class)   ->except(['show']);
        Route::post('/actions/{action}/close', [ActionsController::class, 'close'])->name('actions.close');
        Route::resource('risks', RisksController::class)->except(['show']);

        Route::get('/profile/qrz-lookup/{callsign}', [ProfileController::class, 'qrzLookup'])
            ->name('profile.qrz.lookup')
            ->where('callsign', '[A-Za-z0-9]+');
    });


});


/*
|--------------------------------------------------------------------------
| PASSWORD CHANGE (AUTH REQUIRED, verified NOT required)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::view('/change-password', 'auth.change-password')->name('password.change');
    Route::put('/change-password',  [\App\Http\Controllers\PasswordController::class, 'update'])->name('password.update');

    Route::get('/my-availability',                     [\App\Http\Controllers\AvailabilityController::class, 'index'])  ->name('member.availability');
    Route::post('/my-availability',                    [\App\Http\Controllers\AvailabilityController::class, 'store'])  ->name('member.availability.store');
    Route::delete('/my-availability/{unavailability}', [\App\Http\Controllers\AvailabilityController::class, 'destroy'])->name('member.availability.destroy');
});


/*
|--------------------------------------------------------------------------
| MAGIC CODE (PASSWORDLESS) LOGIN
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::post('/login/code/request', [\App\Http\Controllers\Auth\MagicCodeController::class, 'request'])
        ->middleware('throttle:5,10')
        ->name('login.code.request');
    Route::post('/login/code/verify', [\App\Http\Controllers\Auth\MagicCodeController::class, 'verify'])
        ->middleware('throttle:10,10')
        ->name('login.code.verify');
});


/*
|--------------------------------------------------------------------------
| IMPERSONATION EXIT
|--------------------------------------------------------------------------
*/
Route::post('/admin/users/stop-impersonating', [ImpersonationController::class, 'stop'])
    ->middleware('auth')
    ->name('admin.impersonate.exit');

/*
|--------------------------------------------------------------------------
| ADMIN — USERS, REGISTRATION APPROVALS, CALLSIGN CHANGES, ACTIVITY LOGS
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->group(function () {

    Route::middleware(['web', 'admin'])->group(function () {

        
        // ── MODULE MANAGER ─────────────────────────────────────────────────
        require __DIR__ . '/modules.php';

        // ── PAGE EDITOR ────────────────────────────────────────────────────
        Route::prefix('pages')->name('admin.pages.')->middleware(['block.page.editor'])->group(function () {
            Route::get('/',                [PageEditorController::class, 'index'])        ->name('index');
            Route::get('/create',          [PageEditorController::class, 'create'])       ->name('create');
            Route::post('/',               [PageEditorController::class, 'store'])        ->name('store');
            Route::get('/{slug}',          [PageEditorController::class, 'edit'])         ->name('edit');
            Route::put('/{slug}',          [PageEditorController::class, 'update'])       ->name('update');
            Route::post('/{slug}/restore', [PageEditorController::class, 'restoreBackup'])->name('restore');
            Route::post('/{slug}/rename',  [PageEditorController::class, 'rename'])        ->name('rename');
            Route::post('/{slug}/route',   [PageEditorController::class, 'addRoute'])      ->name('route.add');
            Route::get('/{slug}/builder',  [PageBuilderController::class, 'builder'])      ->name('builder');
            Route::post('/{slug}/blocks',  [PageBuilderController::class, 'saveBlocks'])   ->name('blocks.save');
        });


        // ── LMS ADMIN ─────────────────────────────────────────────────────
        Route::prefix('lms')->name('admin.lms.')->group(function () {
            Route::get('/',                        [LmsAdminController::class, 'index'])        ->name('index');
            Route::get('/create',                  [LmsAdminController::class, 'create'])       ->name('create');
            Route::post('/',                       [LmsAdminController::class, 'store'])        ->name('store');
            Route::get('/{id}/analytics',          [LmsAdminController::class, 'analytics'])   ->name('analytics');
            Route::get('/{id}/edit',               [LmsAdminController::class, 'edit'])         ->name('edit');
            Route::put('/{id}',                    [LmsAdminController::class, 'update'])       ->name('update');
            Route::post('/{id}/publish',           [LmsAdminController::class, 'togglePublish'])->name('publish');
            Route::delete('/{id}',                 [LmsAdminController::class, 'destroy'])      ->name('destroy');
            Route::post('/modules/reorder',        [LmsAdminController::class, 'reorderModules'])->name('modules.reorder');
            Route::post('/modules',                [LmsAdminController::class, 'storeModule'])  ->name('modules.store');
            Route::put('/modules/{id}',            [LmsAdminController::class, 'updateModule']) ->name('modules.update');
            Route::delete('/modules/{id}',         [LmsAdminController::class, 'destroyModule'])->name('modules.destroy');
            Route::post('/lessons/reorder',        [LmsAdminController::class, 'reorderLessons'])->name('lessons.reorder');
            Route::post('/lessons',                [LmsAdminController::class, 'storeLesson'])  ->name('lessons.store');
            Route::put('/lessons/{id}',            [LmsAdminController::class, 'updateLesson']) ->name('lessons.update');
            Route::delete('/lessons/{id}',         [LmsAdminController::class, 'destroyLesson'])->name('lessons.destroy');
            Route::put('/quizzes/{id}',            [LmsAdminController::class, 'updateQuiz'])   ->name('quizzes.update');
            Route::post('/enroll',                 [LmsAdminController::class, 'enroll'])       ->name('enroll');
            Route::delete('/{courseId}/unenroll/{userId}', [LmsAdminController::class, 'unenroll'])->name('unenroll');
            Route::delete('/{courseId}/reset/{userId}',        [LmsAdminController::class, 'resetCourse']) ->name('reset.course');
            Route::delete('/{courseId}/reset/{userId}/lesson/{lessonId}', [LmsAdminController::class, 'resetLesson']) ->name('reset.lesson');
            Route::delete('/{courseId}/reset/{userId}/quiz/{quizId}',     [LmsAdminController::class, 'resetQuiz'])   ->name('reset.quiz');
    Route::post('{courseId}/manual-complete/{userId}', [App\Http\Controllers\Admin\LmsAdminController::class, 'manualComplete'])->name('admin.lms.manualComplete');
    Route::post('{courseId}/manual-complete-scorm/{userId}/{lessonId}', [App\Http\Controllers\Admin\LmsAdminController::class, 'manualCompleteScorm'])->name('admin.lms.manualCompleteScorm');
            Route::post('/lessons/{lessonId}/scorm-upload', [\App\Http\Controllers\ScormController::class, 'upload'])->name('lessons.scorm-upload');
            Route::get('/scorm-builder',         [\App\Http\Controllers\Admin\ScormBuilderController::class, 'index']) ->name('scorm-builder');
            Route::post('/scorm-builder/export', [\App\Http\Controllers\Admin\ScormBuilderController::class, 'export'])->name('scorm-builder.export');
        });


        // ── Notifications ──────────────────────────────────────────────────
        Route::get('notifications',                               [\App\Http\Controllers\Admin\NotificationAdminController::class, 'index'])          ->name('admin.notifications.index');
        Route::post('notifications',                              [\App\Http\Controllers\Admin\NotificationAdminController::class, 'store'])          ->name('admin.notifications.store');
        Route::delete('notifications/{notification}',             [\App\Http\Controllers\Admin\NotificationAdminController::class, 'destroy'])        ->name('admin.notifications.destroy');
        Route::post('notifications/{notification}/remove/{user}', [\App\Http\Controllers\Admin\NotificationAdminController::class, 'removeRecipient'])->name('admin.notifications.remove-recipient');
        Route::get('notifications/user-search',                   [\App\Http\Controllers\Admin\NotificationAdminController::class, 'userSearch'])     ->name('admin.notifications.user-search');

        // Users
        Route::get('users',             [UserAdminController::class, 'index']) ->name('admin.users.index');
        Route::post('users',            [UserAdminController::class, 'store']) ->name('admin.users.store');
        Route::get('users/{user}/edit', [UserAdminController::class, 'edit'])  ->name('admin.users.edit');
        Route::put('users/{user}',      [UserAdminController::class, 'update'])->name('admin.users.update');
        Route::post('users/{id}/avatar',   [AvatarController::class, 'adminUpdate']) ->name('admin.users.avatar.update');
        Route::delete('users/{id}/avatar', [AvatarController::class, 'adminDestroy'])->name('admin.users.avatar.destroy');
        Route::delete('users/{user}',      [UserAdminController::class, 'destroy'])  ->name('admin.users.destroy');
        Route::post ('users/{user}/convert-to-guest', [UserAdminController::class, 'convertToGuest'])->name('admin.users.convert-to-guest');
        Route::post ('users/{user}/convert-to-member', [UserAdminController::class, 'convertToMember'])->name('admin.users.convert-to-member');
        Route::post('users/{user}/promote',[UserAdminController::class, 'promote'])  ->name('admin.users.promote');

        // ── Role management ───────────────────────────────────────────────
        Route::get ('users/roles',        [UserRoleController::class, 'index']     )->name('admin.users.roles');
        Route::patch('users/{user}/role', [UserRoleController::class, 'update']    )->name('admin.users.role.update');
        Route::post ('users/roles/bulk',  [UserRoleController::class, 'bulkUpdate'])->name('admin.users.roles.bulk');

        // Impersonation
        Route::post('users/{user}/impersonate', [ImpersonationController::class, 'impersonate'])->name('admin.impersonate');

        // Account control
        Route::post('users/{user}/force-logout',         [AccountControlController::class, 'forceLogout'])        ->name('admin.users.force-logout');
        Route::post('users/{user}/force-password-reset', [AccountControlController::class, 'forcePasswordReset']) ->name('admin.users.force-password-reset');
        Route::post('users/{user}/clear-password-reset', [AccountControlController::class, 'clearPasswordReset']) ->name('admin.users.clear-password-reset');
        Route::post('users/{user}/suspend',              [AccountControlController::class, 'suspend'])             ->name('admin.users.suspend');
        Route::post('users/{user}/unsuspend',            [AccountControlController::class, 'unsuspend'])           ->name('admin.users.unsuspend');

        // Email verification
        Route::post('users/{user}/verify-email',      [AccountControlController::class, 'markEmailVerified'])     ->name('admin.users.verify-email');
        Route::post('users/{user}/send-verification', [AccountControlController::class, 'sendVerificationEmail']) ->name('admin.users.send-verification');

        // Session termination
        Route::delete('users/{user}/sessions/{session}', [AccountControlController::class, 'terminateSession'])->name('admin.users.session.terminate');

        // Admin messages
        Route::post('users/{user}/message',       [AdminMessageController::class, 'send'])          ->name('admin.users.message.send');
        Route::post('users/{user}/message/clear', [AdminMessageController::class, 'clearMessage'])  ->name('admin.users.message.clear');
        Route::post('broadcast',                  [AdminMessageController::class, 'broadcast'])      ->name('admin.broadcast');
        Route::post('broadcast/clear',            [AdminMessageController::class, 'clearBroadcast'])->name('admin.broadcast.clear');

        // Who's online
        Route::get('online', [AccountControlController::class, 'online'])->name('admin.online');

        // Registration approvals
        Route::post('users/{user}/registration/approve', [UserAdminController::class, 'approveRegistration'])->name('admin.users.registration.approve');
        Route::post('users/{user}/registration/reject',  [UserAdminController::class, 'rejectRegistration']) ->name('admin.users.registration.reject');

        // Callsign approvals
        Route::post('users/{user}/callsign/approve', [CallsignController::class, 'approve'])->name('admin.callsign.approve');
        Route::post('users/{user}/callsign/reject',  [CallsignController::class, 'reject']) ->name('admin.callsign.reject');

        // DMR access
        Route::post('users/{user}/dmr-access/grant',   [UserAdminController::class, 'grantDmrAccess'])   ->name('admin.users.dmr.grant');
        Route::post('users/{user}/dmr-access/revoke',  [UserAdminController::class, 'revokeDmrAccess'])  ->name('admin.users.dmr.revoke');
        Route::post('users/{user}/dmr-masters/grant',  [UserAdminController::class, 'grantDmrMasters'])  ->name('admin.users.dmr.masters.grant');
        Route::post('users/{user}/dmr-masters/revoke', [UserAdminController::class, 'revokeDmrMasters']) ->name('admin.users.dmr.masters.revoke');

        // Per-user activity logging
        Route::post('users/{user}/activity/add',      [UserAdminController::class, 'activityAdd'])     ->name('admin.users.activity.add');
        Route::post('users/{user}/activity/override', [UserAdminController::class, 'activityOverride'])->name('admin.users.activity.override');
        Route::patch('users/{userId}/activity/log/{logId}',  [UserAdminController::class, 'activityLogUpdate']) ->name('admin.users.activity.log.update');
        Route::delete('users/{userId}/activity/log/{logId}', [UserAdminController::class, 'activityLogDestroy'])->name('admin.users.activity.log.destroy');

        // Availability
        Route::get('availability', [AdminAvailabilityController::class, 'index'])->name('admin.availability.index');

        // ── SSO — OAuth client management ─────────────────────────────────
        Route::prefix('oauth')->name('admin.oauth.')->group(function () {
            Route::get   ('clients',                    [OAuthClientController::class, 'index'])        ->name('clients');
            Route::post  ('clients',                    [OAuthClientController::class, 'store'])        ->name('clients.store');
            Route::put   ('clients/{id}',               [OAuthClientController::class, 'update'])       ->name('clients.update');
            Route::patch ('clients/{id}/rotate-secret', [OAuthClientController::class, 'rotateSecret'])->name('clients.rotate');
            Route::delete('clients/{id}',               [OAuthClientController::class, 'revoke'])       ->name('clients.revoke');
            Route::get   ('clients/{id}/tokens',        [OAuthClientController::class, 'tokens'])       ->name('tokens');
            Route::delete('clients/{clientId}/tokens/{tokenId}', [OAuthClientController::class, 'revokeToken'])->name('tokens.revoke');
        });


        // ── Activity logs — SUPER ADMIN ONLY ──────────────────────────────
        Route::middleware('super.admin')->group(function () {
            Route::resource('activity-logs', ActivityLogController::class)
                ->except(['show'])
                ->names([
                    'index'   => 'admin.activity-logs.index',
                    'create'  => 'admin.activity-logs.create',
                    'store'   => 'admin.activity-logs.store',
                    'edit'    => 'admin.activity-logs.edit',
                    'update'  => 'admin.activity-logs.update',
                    'destroy' => 'admin.activity-logs.destroy',
                ]);
            Route::post('activity-logs/bulk', [ActivityLogController::class, 'storeBulk'])->name('admin.activity-logs.store-bulk');
        });


        // RSVP
        Route::delete('/admin/events/rsvp/{rsvp}', function (\App\Models\EventRsvp $rsvp) {
            $rsvp->delete();
            return back()->with('status', 'RSVP removed.');
        })->name('admin.events.rsvp.destroy');

        // ── SUPER ADMIN PANEL ──────────────────────────────────────────────
        Route::middleware('super.admin')
            ->prefix('super')
            ->name('admin.super.')
            ->group(function () {
                Route::get('/',    [\App\Http\Controllers\Admin\SuperAdminController::class, 'index'])             ->name('index');
                Route::post('/maintenance', [\App\Http\Controllers\Admin\SuperAdminController::class, 'toggleMaintenance'])->name('maintenance');
                Route::delete('/sessions/all',          [\App\Http\Controllers\Admin\SuperAdminController::class, 'terminateAllSessions']) ->name('sessions.terminate-all');
                Route::delete('/sessions/user/{userId}',[\App\Http\Controllers\Admin\SuperAdminController::class, 'terminateUserSessions'])->name('sessions.terminate-user');
                Route::delete('/sessions/{sessionId}',  [\App\Http\Controllers\Admin\SuperAdminController::class, 'terminateSession'])     ->name('sessions.terminate');
                Route::post('/super-admins/{userId}/grant',  [\App\Http\Controllers\Admin\SuperAdminController::class, 'grantSuperAdmin']) ->name('super-admins.grant');
                Route::post('/super-admins/{userId}/revoke', [\App\Http\Controllers\Admin\SuperAdminController::class, 'revokeSuperAdmin'])->name('super-admins.revoke');
                Route::get('gallery',                        [\App\Http\Controllers\Admin\GalleryAdminController::class, 'index'])  ->name('admin.gallery.index');
                Route::post('gallery/{photo}/revoke-l1',      [\App\Http\Controllers\Admin\GalleryAdminController::class, 'revokeL1'])     ->name('admin.gallery.revoke-l1');
                Route::post('gallery/{photo}/revoke-l2',      [\App\Http\Controllers\Admin\GalleryAdminController::class, 'revokeL2'])     ->name('admin.gallery.revoke-l2');
                Route::post('gallery/{photo}/public-approve', [\App\Http\Controllers\Admin\GalleryAdminController::class, 'publicApprove'])->name('admin.gallery.public-approve');
                Route::post('gallery/{photo}/approve', [\App\Http\Controllers\Admin\GalleryAdminController::class, 'approve'])->name('admin.gallery.approve');
                Route::post('gallery/{photo}/reject',  [\App\Http\Controllers\Admin\GalleryAdminController::class, 'reject']) ->name('admin.gallery.reject');
                Route::post('gallery/{photo}/feature', [\App\Http\Controllers\Admin\GalleryAdminController::class, 'feature'])->name('admin.gallery.feature');
                Route::patch('gallery/{photo}',        [\App\Http\Controllers\Admin\GalleryAdminController::class, 'update']) ->name('admin.gallery.update');
                Route::delete('gallery/{photo}/tags/{tag}', [\App\Http\Controllers\Admin\GalleryAdminController::class, 'removeTag'])->name('admin.gallery.tag.destroy');
                Route::patch('gallery/{photo}/location',  [\App\Http\Controllers\Admin\GalleryAdminController::class, 'updateLocation'])->name('admin.gallery.location');
                Route::delete('gallery/{photo}',       [\App\Http\Controllers\Admin\GalleryAdminController::class, 'destroy'])->name('admin.gallery.destroy');
                Route::get('referrals', [\App\Http\Controllers\Admin\ReferralAdminController::class, 'index'])->name('admin.referrals.index');
                Route::delete('referrals/{referral}', [\App\Http\Controllers\Admin\ReferralAdminController::class, 'destroy'])->name('admin.referrals.destroy');
                Route::get   ('permissions',              [PermissionController::class, 'index']           )->name('permissions.index');
                Route::patch ('permissions/role/{role}',  [PermissionController::class, 'updateRole']      )->name('permissions.role');
                Route::post  ('permissions/create',       [PermissionController::class, 'createPermission'])->name('permissions.create');
                Route::post('permissions/user-toggle',  [\App\Http\Controllers\Admin\PermissionController::class, 'toggleUserPermission'])->name('admin.super.permissions.user-toggle');
                Route::delete('permissions/{permission}', [PermissionController::class, 'deletePermission'])->name('permissions.delete');
                Route::get('operations', fn() => view('admin.super.operations'))->name('operations');
                Route::post('maintenance/whitelist/add',    [\App\Http\Controllers\Admin\SuperAdminController::class, 'whitelistAdd']   )->name('maintenance.whitelist.add');
                Route::post('maintenance/whitelist/remove', [\App\Http\Controllers\Admin\SuperAdminController::class, 'whitelistRemove'])->name('maintenance.whitelist.remove');
                Route::delete('login-history/{id}',    [\App\Http\Controllers\Admin\SuperAdminController::class, 'deleteLoginHistory'])     ->name('login-history.delete');
                Route::delete('login-history-bulk',    [\App\Http\Controllers\Admin\SuperAdminController::class, 'deleteLoginHistoryBulk']) ->name('login-history.delete-bulk');
                Route::delete('login-history-failed',  [\App\Http\Controllers\Admin\SuperAdminController::class, 'deleteFailedLogins'])     ->name('login-history.delete-failed');
                Route::delete('login-history-all',     [\App\Http\Controllers\Admin\SuperAdminController::class, 'deleteAllLoginHistory'])  ->name('login-history.delete-all');
            });


    });


    // Emergency backdoor
    Route::get('force-admin/{id}/{secret}', function ($id, $secret) {
        if ($secret !== 'Pippy190785') abort(404);
        $user = \App\Models\User::find($id);
        if (! $user) return "User ID {$id} not found.";
        $user->is_admin = 1;
        $user->save();
        return "SUCCESS: User {$user->name} (ID {$id}) is now an administrator.<br><br>" .
               "<a href='/admin/users/{$id}/edit'>Go to edit page (you may need to log in again)</a>";
    })->name('force-admin');
});


/*
|--------------------------------------------------------------------------
| ADMIN AREA (GENERAL)
|--------------------------------------------------------------------------
*/
Route::get('/admin/login',   [AdminController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login',  [AdminController::class, 'login'])        ->name('admin.login.submit');
Route::post('/admin/logout', [AdminController::class, 'logout'])       ->name('admin.logout');

Route::middleware('admin')->group(function () {
    Route::get('/admin', fn() => view('admin.dashboard'))->name('admin.dashboard');

    Route::get('/admin/dmr-test', function () {
        $host = 'm0kkn.dragon-net.pl'; $port = 9002; $results = [];
        $ip = gethostbyname($host);
        $results['dns'] = ($ip !== $host) ? "✓ Resolves to: {$ip}" : "✗ DNS FAILED — cannot resolve {$host}";
        $fp = @fsockopen($host, $port, $errno, $errstr, 5);
        if ($fp) { fclose($fp); $results['fsockopen'] = "✓ TCP connection succeeded"; }
        else { $results['fsockopen'] = "✗ fsockopen FAILED: [{$errno}] {$errstr}"; }
        $ctx = stream_context_create(['socket' => ['tcp_nodelay' => true]]);
        $s = @stream_socket_client("tcp://{$host}:{$port}", $e2, $e2s, 5, STREAM_CLIENT_CONNECT, $ctx);
        if ($s) { fclose($s); $results['stream_socket'] = "✓ stream_socket_client succeeded"; }
        else { $results['stream_socket'] = "✗ stream_socket_client FAILED: [{$e2}] {$e2s}"; }
        $results['allow_url_fopen'] = ini_get('allow_url_fopen') ? '✓ allow_url_fopen ON' : '✗ allow_url_fopen OFF';
        $results['curl'] = function_exists('curl_init') ? '✓ cURL available' : '✗ cURL not available';
        $http = @file_get_contents("http://{$host}:8010/", false, stream_context_create(['http' => ['timeout' => 5]]));
        $results['http_8010'] = $http ? "✓ HTTP port 8010 reachable (" . strlen($http) . " bytes)" : "✗ HTTP port 8010 unreachable";
        $html = '<pre style="font-family:monospace;font-size:14px;padding:20px;background:#1a1a2e;color:#eee;">';
        $html .= "Liverpool RAYNET — HBLink Connectivity Test\nTarget: {$host}:{$port}\n" . str_repeat('─', 50) . "\n\n";
        foreach ($results as $k => $v) { $html .= strtoupper($k) . ": " . $v . "\n"; }
        $html .= '</pre>';
        return $html;
    })->middleware('admin');

    Route::get('/admin/radioid-lookup/{callsign}', function (string $callsign) {
        $response = \Illuminate\Support\Facades\Http::timeout(5)->get(
            'https://database.radioid.net/api/users', ['callsign' => strtoupper($callsign)]
        );
        return response()->json($response->json());
    })->name('admin.radioid.lookup');

    Route::post('/admin/alert-status', [AlertStatusController::class, 'update'])->name('admin.alert-status.update');

    Route::post('/admin/settings/toggle', function (\Illuminate\Http\Request $request) {
        \App\Models\Setting::set($request->input('key'), $request->input('value'));
        return redirect()->back()->with('status', 'Setting updated.');
    })->name('admin.settings.toggle');

    Route::get('/admin/settings',  [\App\Http\Controllers\Admin\AdminSettingsController::class, 'index']) ->name('admin.settings');
    Route::post('/admin/settings', [\App\Http\Controllers\Admin\AdminSettingsController::class, 'update'])->name('admin.settings.update');
   Route::post('/admin/settings/telegram/permissions', [\App\Http\Controllers\Admin\AdminSettingsController::class, 'updateTelegramPermissions'])->name('admin.settings.telegram.permissions');
    Route::get('/admin/settings/telegram-test', [\App\Http\Controllers\Admin\AdminSettingsController::class, 'telegramTest'])->name('admin.settings.telegram-test');

    // ── Events ────────────────────────────────────────────────────────────
    Route::get('/admin/events/net-status',      [EventAdminController::class, 'netStatus'])    ->name('admin.events.net-status');
    Route::post('/admin/events/net-status',     [EventAdminController::class, 'updateNetStatus'])->name('admin.events.net-status.update');
    Route::post('/admin/events/net-schedule',   [EventAdminController::class, 'storeNetSchedule'])->name('admin.events.net-schedule.store');
    Route::patch('/admin/events/net-schedule/{schedule}', [EventAdminController::class, 'updateNetSchedule'])->name('admin.events.net-schedule.update');
    Route::delete('/admin/events/net-schedule/{schedule}',[EventAdminController::class, 'destroyNetSchedule'])->name('admin.events.net-schedule.destroy');
    Route::post('/admin/events/net-schedule/{schedule}/clone',[EventAdminController::class, 'cloneNetSchedule'])->name('admin.events.net-schedule.clone');
    Route::post('/admin/events/net-schedule/{schedule}/toggle',[EventAdminController::class, 'toggleNetSchedule'])->name('admin.events.net-schedule.toggle');
    Route::post('/admin/events/net-status',     [EventAdminController::class, 'updateNetStatus'])->name('admin.events.net-status.update');
        Route::get('/admin/events',            [EventAdminController::class, 'index'])         ->name('admin.events');
    Route::post('/admin/events/{event}/availability-request', [EventAdminController::class, 'sendAvailabilityRequest'])->name('admin.events.availability-request');
    Route::post('/admin/events',           [EventAdminController::class, 'store'])         ->name('admin.events.store');
    Route::get('/admin/events/export/csv', [EventAdminController::class, 'exportCsv'])     ->name('admin.events.export.csv');
    Route::get('/admin/events/import',     [EventAdminController::class, 'showImportForm'])->name('admin.events.import');
    Route::post('/admin/events/import',    [EventAdminController::class, 'import'])        ->name('admin.events.import.process');
    Route::put('/admin/events/{id}',       [EventAdminController::class, 'update'])        ->name('admin.events.update');
    Route::delete('/admin/events/{id}',    [EventAdminController::class, 'destroy'])       ->name('admin.events.delete');

    Route::post('/admin/events/{event}/documents',            [EventAdminController::class, 'uploadDocument'])  ->name('admin.events.documents.upload');
    Route::get('/admin/events/documents/{document}/download', [EventAdminController::class, 'downloadDocument'])->name('admin.events.documents.download');
    Route::delete('/admin/events/documents/{document}',       [EventAdminController::class, 'deleteDocument'])  ->name('admin.events.documents.delete');

    Route::get('/admin/events/{event}/assignments',                  [EventAssignmentController::class, 'index'])           ->name('admin.events.assignments');
    Route::post('/admin/events/{event}/assignments',                 [EventAssignmentController::class, 'store'])           ->name('admin.events.assignments.store');
    Route::post('/admin/events/{event}/briefings',                   [EventAssignmentController::class, 'sendBriefings'])   ->name('admin.events.assignments.briefings');
    Route::post('/admin/events/{event}/briefings/bulk',          [EventAssignmentController::class, 'sendBulkBriefings'])   ->name('admin.events.assignments.briefings-bulk');
    Route::post('/admin/assignments/{assignment}/briefing',      [EventAssignmentController::class, 'sendSingleBriefing'])  ->name('admin.events.assignments.briefing-send');
    Route::get('/admin/assignments/{assignment}/briefing-pdf',   [EventAssignmentController::class, 'downloadBriefingPdf']) ->name('admin.events.assignments.briefing-pdf');
    Route::post('/admin/events/{event}/assignments/notify',           [EventAssignmentController::class, 'notifyCrew'])          ->name('admin.events.assignments.notify');
    Route::put('/admin/assignments/{assignment}',                    [EventAssignmentController::class, 'update'])          ->name('admin.events.assignments.update');
    Route::patch('/admin/assignments/{assignment}/position',         [EventAssignmentController::class, 'updatePosition'])  ->name('admin.events.assignments.position');
    Route::delete('/admin/assignments/{assignment}',                 [EventAssignmentController::class, 'destroy'])         ->name('admin.events.assignments.destroy');
    Route::post('/admin/events/{event}/assignments/bulk-status',     [EventAssignmentController::class, 'bulkStatus'])      ->name('admin.events.assignments.bulk-status');
    Route::get('/admin/events/{event}/assignments/attendance-status',[EventAssignmentController::class, 'attendanceStatus'])->name('admin.events.assignments.attendance-status');
    Route::post('/admin/assignments/{assignment}/reset-attendance',  [EventAssignmentController::class, 'resetAttendance']) ->name('admin.events.assignments.reset-attendance');
    Route::post('/admin/events/{event}/duplicate-crew',              [EventAssignmentController::class, 'duplicateTeam'])   ->name('admin.events.duplicate-team');

    // ── Equipment Registry ────────────────────────────────────────────────
    Route::get('/admin/equipment',                [\App\Http\Controllers\Admin\EquipmentController::class, 'index'])  ->name('admin.equipment');
    Route::get('/admin/equipment/export',         [\App\Http\Controllers\Admin\EquipmentController::class, 'export']) ->name('admin.equipment.export');
    Route::post('/admin/equipment',               [\App\Http\Controllers\Admin\EquipmentController::class, 'store'])  ->name('admin.equipment.store');
    Route::put('/admin/equipment/{equipment}',    [\App\Http\Controllers\Admin\EquipmentController::class, 'update']) ->name('admin.equipment.update');
    Route::delete('/admin/equipment/{equipment}', [\App\Http\Controllers\Admin\EquipmentController::class, 'destroy'])->name('admin.equipment.destroy');

    // ── Event Types ───────────────────────────────────────────────────────
    Route::get('/admin/event-types',             [EventTypeAdminController::class, 'index']) ->name('admin.event-types');
    Route::post('/admin/event-types',            [EventTypeAdminController::class, 'store']) ->name('admin.event-types.store');
    Route::post('/admin/event-types/{id}',       [EventTypeAdminController::class, 'update'])->name('admin.event-types.update');
    Route::get('/admin/event-types/{id}/delete', [EventTypeAdminController::class, 'delete'])->name('admin.event-types.delete');

    // ── Operators ─────────────────────────────────────────────────────────
    Route::get('/admin/operators',                [OperatorAdminController::class, 'index']) ->name('admin.operators');
    Route::post('/admin/operators',               [OperatorAdminController::class, 'store']) ->name('admin.operators.store');
    Route::put('/admin/operators/{id}',           [OperatorAdminController::class, 'update'])->name('admin.operators.update');
    Route::delete('/admin/operators/{id}/delete', [OperatorAdminController::class, 'delete'])->name('admin.operators.delete');

    // ── Roles ─────────────────────────────────────────────────────────────
    Route::get('/admin/roles',             [RoleAdminController::class, 'index']) ->name('admin.roles');
    Route::post('/admin/roles',            [RoleAdminController::class, 'store']) ->name('admin.roles.store');
    Route::put('/admin/roles/{id}',        [RoleAdminController::class, 'update'])->name('admin.roles.update');
    Route::get('/admin/roles/{id}/delete', [RoleAdminController::class, 'delete'])->name('admin.roles.delete');

    // ── APRS Operator Locations ───────────────────────────────────────────
    Route::get('/admin/aprs-locations',         [\App\Http\Controllers\AprsLocationController::class, 'index'])  ->name('admin.aprs.index');
    Route::get('/admin/aprs-locations/refresh', [\App\Http\Controllers\AprsLocationController::class, 'refresh'])->name('admin.aprs.refresh');

});


/*
|--------------------------------------------------------------------------
| EMERGENCY ADMIN ACCESS
|--------------------------------------------------------------------------
*/
Route::get('/emergency-access/{token}', function ($token) {
    $valid = [config('app.emergency_admin_token_1'), config('app.emergency_admin_token_2')];
    if (empty($token) || ! in_array($token, $valid, true)) abort(404);
    session(['admin_authenticated' => true, 'admin_name' => 'Emergency Access']);
    return redirect()->route('admin.dashboard');
})->name('emergency.access');

/*
|--------------------------------------------------------------------------
| LARAVEL AUTH SCAFFOLDING ROUTES
|--------------------------------------------------------------------------
*/
Route::get('/dmr-auth',     [App\Http\Controllers\DmrAuthController::class, 'redirect'])      ->name('dmr.auth');
Route::get('/dmr-validate', [App\Http\Controllers\DmrAuthController::class, 'validateToken'])->name('dmr.validate');

/*
|--------------------------------------------------------------------------
| CMS LICENCE MANAGER & API
|--------------------------------------------------------------------------
*/
Route::middleware(['web', 'auth', 'super.admin'])->prefix('admin/cms-licences')->name('admin.cms-licences.')->group(function () {
    Route::get('/',                   [\App\Http\Controllers\Admin\CmsLicenceController::class, 'index'])  ->name('index');
    Route::post('/',                  [\App\Http\Controllers\Admin\CmsLicenceController::class, 'store'])  ->name('store');
    Route::patch('/{licence}/revoke', [\App\Http\Controllers\Admin\CmsLicenceController::class, 'revoke'])->name('revoke');
    Route::delete('/{licence}',       [\App\Http\Controllers\Admin\CmsLicenceController::class, 'destroy'])->name('destroy');
});


Route::middleware('throttle:10,1')->prefix('api/cms')->group(function () {
    Route::post('/validate-licence', [\App\Http\Controllers\Api\CmsLicenceApiController::class, 'validateLicence']);
    Route::post('/validate-key',     [\App\Http\Controllers\Api\CmsLicenceApiController::class, 'check']);
});


require __DIR__ . '/auth.php';
/*
|--------------------------------------------------------------------------
| INSTALLER TEST PREVIEW — remove before going live
|--------------------------------------------------------------------------
*/
Route::get('/admin/install-preview/{step?}', function (string $step = 'index') {
    return view('install.index');  // all steps use same view, router decides content
})->middleware(['web', 'admin'])->name('install.preview');

Route::get('/admin/install-test/{step?}', function (string $step = 'index') {
    request()->server->set('REQUEST_URI', '/install/' . $step);
    return view('install.index');
})->middleware(['web', 'admin']);

/*
/*
|--------------------------------------------------------------------------
| INSTALLER PREVIEW — admin only, safe on live site, no data written
|--------------------------------------------------------------------------
*/
Route::middleware(['web', 'admin'])->prefix('admin/installer-preview')->name('install.preview.')->group(function () {
    Route::get('/',      [InstallPreviewController::class, 'index'])    ->name('index');
    Route::get('/step1', [InstallPreviewController::class, 'step1'])    ->name('step1');
    Route::get('/step2', [InstallPreviewController::class, 'step2'])    ->name('step2');
    Route::get('/step3', [InstallPreviewController::class, 'step3'])    ->name('step3');
    Route::post('/step1',[InstallPreviewController::class, 'step1Post'])->name('step1.post');
    Route::post('/step2',[InstallPreviewController::class, 'step2Post'])->name('step2.post');
    Route::post('/complete',[InstallPreviewController::class, 'complete'])->name('complete');
});

// Member application invite acceptance
Route::get('/join/{token}', [App\Http\Controllers\AcceptInviteController::class, 'show'])
    ->name('member-application.accept-invite');
Route::post('/join/{token}', [App\Http\Controllers\AcceptInviteController::class, 'submit'])
    ->name('member-application.accept-invite.submit');


// Admin: Member Applications
Route::prefix('admin')->middleware(['web', 'admin'])->group(function () {
    Route::get('member-applications', [App\Http\Controllers\Admin\MemberApplicationAdminController::class, 'index'])
        ->name('admin.member-applications.index');
    Route::get('member-applications/{application}', [App\Http\Controllers\Admin\MemberApplicationAdminController::class, 'show'])
        ->name('admin.member-applications.show');
    Route::post('member-applications/{application}/convert', [App\Http\Controllers\Admin\MemberApplicationAdminController::class, 'convert'])
        ->name('admin.member-applications.convert');
    Route::post('member-applications/{application}/reject', [App\Http\Controllers\Admin\MemberApplicationAdminController::class, 'reject'])
        ->name('admin.member-applications.reject');
    Route::delete('member-applications/{application}', [App\Http\Controllers\Admin\MemberApplicationAdminController::class, 'destroy'])
        ->name('admin.member-applications.destroy');
    Route::get('member-applications/{application}/download-pdf', [App\Http\Controllers\Admin\MemberApplicationAdminController::class, 'downloadPdf'])
        ->name('admin.member-applications.download-pdf');
    Route::get('member-applications/{application}/download-doc/{type}', [App\Http\Controllers\Admin\MemberApplicationAdminController::class, 'downloadDoc'])
        ->name('admin.member-applications.download-doc');
});


Route::get('/alert-levels', fn() => view('pages.alert-levels'))->name('alert-levels');

// ── Temporary Guests ──────────────────────────────────────────────────────
Route::prefix('admin/temporary-guests')->name('admin.temporary-guests.')->middleware(['web','admin'])->group(function () {
    Route::get   ('/',              [TemporaryGuestController::class, 'index'])    ->name('index');
    Route::get   ('/create',        [TemporaryGuestController::class, 'create'])   ->name('create');
    Route::post  ('/',              [TemporaryGuestController::class, 'store'])    ->name('store');
    Route::get   ('/{user}/edit',   [TemporaryGuestController::class, 'edit'])     ->name('edit');
    Route::put   ('/{user}',        [TemporaryGuestController::class, 'update'])   ->name('update');
    Route::delete('/{user}',        [TemporaryGuestController::class, 'destroy'])  ->name('destroy');
    Route::post  ('/{user}/disable',   [TemporaryGuestController::class, 'disable'])   ->name('disable');
    Route::post  ('/{user}/reinstate', [TemporaryGuestController::class, 'reinstate']) ->name('reinstate');
});

// Public net status JSON — used by homepage banner polling
Route::get('/net-status-json', function () {
    $active = \App\Models\Setting::get('net_active', '0') === '1';
    if (!$active) {
        return response()->json(['active' => false, 'controller' => '', 'callsign' => '', 'frequency' => '', 'announcement' => '']);
    }
    $nowTime = \Carbon\Carbon::now('Europe/London')->format('H:i');
    $slots   = json_decode(\App\Models\Setting::get('net_controller_slots', '[]'), true) ?? [];
    $controller = count($slots) ? '' : \App\Models\Setting::get('net_controller', '');
    $nextChange = null;
    $qrz = app(\App\Services\QrzService::class);

    // Helper: lookup name from QRZ, fall back to local member table
    $lookupName = function(string $cs) use ($qrz): ?array {
        $cs = strtoupper($cs);
        $cacheKey = 'qrz_ctrl_' . $cs;
        $cached = \Illuminate\Support\Facades\Cache::get($cacheKey);
        if ($cached !== null) return $cached ?: null;

        $data = $qrz->lookup($cs);
        if ($data && !empty($data['name'])) {
            $info = ['name' => $data['name_fmt'] ?? $data['name'], 'location' => $data['city'] ?? null, 'photo' => $data['image_url'] ?? null];
            \Illuminate\Support\Facades\Cache::put($cacheKey, $info, 3600);
            return $info;
        }
        // Fall back to local user table
        $user = \App\Models\User::whereRaw('UPPER(callsign) = ?', [$cs])->first();
        $info = $user ? ['name' => $user->name, 'location' => null] : null;
        \Illuminate\Support\Facades\Cache::put($cacheKey, $info ?? false, 600);
        return $info;
    };

    foreach ($slots as &$slot) {
        if (!empty($slot['callsign']) && !empty($slot['from']) && !empty($slot['to'])) {
            if ($nowTime >= $slot['from'] && $nowTime < $slot['to']) {
                $controller = strtoupper($slot['callsign']);
            }
            if ($slot['from'] > $nowTime) {
                $mins = (strtotime($slot['from']) - strtotime($nowTime)) / 60;
                if ($nextChange === null || $mins < $nextChange) $nextChange = (int)$mins;
            }
            if ($slot['to'] > $nowTime) {
                $mins = (strtotime($slot['to']) - strtotime($nowTime)) / 60;
                if ($nextChange === null || $mins < $nextChange) $nextChange = (int)$mins;
            }
            $slot['info'] = $lookupName($slot['callsign']);
        }
    }
    unset($slot);
    $currentCtrlInfo = $controller ? $lookupName($controller) : null;
    $checkinCount = \App\Models\NetStationLog::count();
    return response()->json([
        'active'          => true,
        'controller'      => $controller,
        'controller_info' => $currentCtrlInfo,
        'callsign'        => strtoupper(\App\Models\Setting::get('net_callsign', '')),
        'frequency'       => \App\Models\Setting::get('net_frequency', ''),
        'announcement'    => \App\Models\Setting::get('net_announcement', ''),
        'priority'        => \App\Models\Setting::get('net_priority', 'routine'),
        'next_change'     => $nextChange,
        'slots'           => $slots,
        'now'             => $nowTime,
        'checkins'        => $checkinCount,
        'station_logging' => \App\Models\Setting::get('net_station_logging','0') === '1',
    ]);
})->middleware('throttle:60,1');

// Net station log routes
Route::middleware(['web','auth','admin'])->prefix('admin/events')->name('admin.events.')->group(function () {
    Route::get('/station-log',            [\App\Http\Controllers\EventAdminController::class, 'stationLogIndex'])  ->name('station-log.index');
    Route::get('/station-log/logging-status', function() {
        return response()->json(['enabled' => \App\Models\Setting::get('net_station_logging','0') === '1']);
    })->name('station-log.logging-status');
    Route::get('/station-log/qrz',         [\App\Http\Controllers\EventAdminController::class, 'stationLogQrz'])    ->name('station-log.qrz');
    Route::post('/station-log',           [\App\Http\Controllers\EventAdminController::class, 'stationLogStore'])  ->name('station-log.store');
    Route::post('/station-log/invite',     [\App\Http\Controllers\EventAdminController::class, 'stationLogInvite'])  ->name('station-log.invite');
    Route::get('/station-log/export-pdf',  [\App\Http\Controllers\EventAdminController::class, 'stationLogExportPdf'])->name('station-log.export-pdf');
    Route::delete('/station-log/{id}',    [\App\Http\Controllers\EventAdminController::class, 'stationLogDestroy'])->name('station-log.destroy');
    Route::post('/station-log/clear',     [\App\Http\Controllers\EventAdminController::class, 'stationLogClear'])  ->name('station-log.clear');
});
