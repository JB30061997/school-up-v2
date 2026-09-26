<?php

use App\Http\Controllers\AcademicStructureController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\CycleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnvironmentController;
use App\Http\Controllers\EnvironmentSelectionController;
use App\Http\Controllers\EnvironmentSwitchController;
use App\Http\Controllers\InternalRequestController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\SchoolYearController;
use App\Http\Controllers\SupportTeamController;
use App\Http\Controllers\TicketCategoryController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::inertia('/', 'Welcome')
    ->name('home');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'verified',
])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Environment Selection
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/select-environment',
        [EnvironmentSelectionController::class, 'index']
    )->name('environments.select');

    Route::post(
        '/select-environment',
        [EnvironmentSelectionController::class, 'store']
    )->name('environments.store');

    Route::delete(
        '/select-environment',
        [EnvironmentSelectionController::class, 'destroy']
    )->name('environments.destroy');

    /*
    |--------------------------------------------------------------------------
    | Environment Switch
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/switch-environment',
        EnvironmentSwitchController::class
    )->name('environments.switch');

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
    | Les notifications appartiennent à l'utilisateur connecté.
    | Elles ne dépendent donc pas directement de l'environnement courant.
    |
    */

    Route::prefix('notifications')
        ->name('notifications.')
        ->group(function () {

            Route::get(
                '/',
                [NotificationController::class, 'index']
            )->name('index');

            Route::post(
                '/read-all',
                [NotificationController::class, 'markAllAsRead']
            )->name('read-all');

            Route::delete(
                '/read',
                [NotificationController::class, 'destroyRead']
            )->name('destroy-read');

            Route::post(
                '/{notification}/read',
                [NotificationController::class, 'markAsRead']
            )->name('read');

            Route::post(
                '/{notification}/unread',
                [NotificationController::class, 'markAsUnread']
            )->name('unread');

            Route::delete(
                '/{notification}',
                [NotificationController::class, 'destroy']
            )->name('destroy');
        });

    /*
    |--------------------------------------------------------------------------
    | Routes nécessitant un environnement courant
    |--------------------------------------------------------------------------
    */

    Route::middleware([
        'environment',
    ])->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/dashboard',
            [DashboardController::class, 'index']
        )
            ->middleware('permission:dashboard.view')
            ->name('dashboard');

        /*
        |--------------------------------------------------------------------------
        | Tickets
        |--------------------------------------------------------------------------
        */

        Route::prefix('tickets')
            ->name('tickets.')
            ->group(function () {

                Route::get(
                    '/',
                    [TicketController::class, 'index']
                )
                    ->middleware('permission:tickets.view')
                    ->name('index');

                Route::get(
                    '/create',
                    [TicketController::class, 'create']
                )
                    ->middleware('permission:tickets.create')
                    ->name('create');

                Route::post(
                    '/',
                    [TicketController::class, 'store']
                )
                    ->middleware('permission:tickets.create')
                    ->name('store');

                Route::get(
                    '/{ticket}',
                    [TicketController::class, 'show']
                )
                    ->whereNumber('ticket')
                    ->middleware('permission:tickets.view')
                    ->name('show');

                Route::post(
                    '/{ticket}/assign',
                    [TicketController::class, 'assign']
                )
                    ->whereNumber('ticket')
                    ->middleware('permission:tickets.assign')
                    ->name('assign');

                Route::post(
                    '/{ticket}/status',
                    [TicketController::class, 'changeStatus']
                )
                    ->whereNumber('ticket')
                    ->middleware('permission:tickets.update')
                    ->name('status');

                Route::post(
                    '/{ticket}/reply',
                    [TicketController::class, 'reply']
                )
                    ->whereNumber('ticket')
                    ->middleware('permission:tickets.update')
                    ->name('reply');
            });

        /*
        |--------------------------------------------------------------------------
        | Demandes internes
        |--------------------------------------------------------------------------
        */

        Route::prefix('requests')
            ->name('requests.')
            ->group(function () {

                Route::get(
                    '/',
                    [InternalRequestController::class, 'index']
                )
                    ->middleware('permission:requests.view')
                    ->name('index');

                Route::get(
                    '/create',
                    [InternalRequestController::class, 'create']
                )
                    ->middleware('permission:requests.create')
                    ->name('create');

                Route::post(
                    '/',
                    [InternalRequestController::class, 'store']
                )
                    ->middleware('permission:requests.create')
                    ->name('store');

                Route::get(
                    '/{internalRequest}',
                    [InternalRequestController::class, 'show']
                )
                    ->whereNumber('internalRequest')
                    ->middleware('permission:requests.view')
                    ->name('show');

                Route::post(
                    '/{internalRequest}/assign',
                    [InternalRequestController::class, 'assign']
                )
                    ->whereNumber('internalRequest')
                    ->middleware('permission:requests.assign')
                    ->name('assign');

                Route::post(
                    '/{internalRequest}/status',
                    [InternalRequestController::class, 'changeStatus']
                )
                    ->whereNumber('internalRequest')
                    ->middleware('permission:requests.update')
                    ->name('status');

                Route::post(
                    '/{internalRequest}/comment',
                    [InternalRequestController::class, 'comment']
                )
                    ->whereNumber('internalRequest')
                    ->middleware('permission:requests.update')
                    ->name('comment');
            });

        /*
        |--------------------------------------------------------------------------
        | Rendez-vous
        |--------------------------------------------------------------------------
        */

        Route::prefix('appointments')
            ->name('appointments.')
            ->group(function () {

                Route::get(
                    '/',
                    [AppointmentController::class, 'index']
                )
                    ->middleware('permission:appointments.view')
                    ->name('index');

                Route::get(
                    '/create',
                    [AppointmentController::class, 'create']
                )
                    ->middleware('permission:appointments.create')
                    ->name('create');

                Route::post(
                    '/',
                    [AppointmentController::class, 'store']
                )
                    ->middleware('permission:appointments.create')
                    ->name('store');

                Route::get(
                    '/{appointment}',
                    [AppointmentController::class, 'show']
                )
                    ->whereNumber('appointment')
                    ->middleware('permission:appointments.view')
                    ->name('show');

                Route::post(
                    '/{appointment}/assign',
                    [AppointmentController::class, 'assign']
                )
                    ->whereNumber('appointment')
                    ->middleware('permission:appointments.assign')
                    ->name('assign');

                Route::post(
                    '/{appointment}/confirm',
                    [AppointmentController::class, 'confirm']
                )
                    ->whereNumber('appointment')
                    ->middleware('permission:appointments.update')
                    ->name('confirm');

                Route::post(
                    '/{appointment}/reject',
                    [AppointmentController::class, 'reject']
                )
                    ->whereNumber('appointment')
                    ->middleware('permission:appointments.update')
                    ->name('reject');

                Route::post(
                    '/{appointment}/cancel',
                    [AppointmentController::class, 'cancel']
                )
                    ->whereNumber('appointment')
                    ->middleware('permission:appointments.update')
                    ->name('cancel');

                Route::post(
                    '/{appointment}/complete',
                    [AppointmentController::class, 'complete']
                )
                    ->whereNumber('appointment')
                    ->middleware('permission:appointments.update')
                    ->name('complete');
            });

        /*
        |--------------------------------------------------------------------------
        | Utilisateurs
        |--------------------------------------------------------------------------
        */

        Route::prefix('users')
            ->name('users.')
            ->group(function () {

                Route::get(
                    '/',
                    [UserController::class, 'index']
                )
                    ->middleware('permission:users.view')
                    ->name('index');

                Route::get(
                    '/create',
                    [UserController::class, 'create']
                )
                    ->middleware('permission:users.create')
                    ->name('create');

                Route::post(
                    '/',
                    [UserController::class, 'store']
                )
                    ->middleware('permission:users.create')
                    ->name('store');

                Route::get(
                    '/{user}',
                    [UserController::class, 'show']
                )
                    ->whereNumber('user')
                    ->middleware('permission:users.view')
                    ->name('show');

                Route::get(
                    '/{user}/edit',
                    [UserController::class, 'edit']
                )
                    ->whereNumber('user')
                    ->middleware('permission:users.update')
                    ->name('edit');

                Route::put(
                    '/{user}',
                    [UserController::class, 'update']
                )
                    ->whereNumber('user')
                    ->middleware('permission:users.update')
                    ->name('update');

                Route::post(
                    '/{user}/toggle-active',
                    [UserController::class, 'toggleActive']
                )
                    ->whereNumber('user')
                    ->middleware('permission:users.update')
                    ->name('toggle-active');

                Route::post(
                    '/{user}/environments/{environment}/toggle',
                    [UserController::class, 'toggleEnvironment']
                )
                    ->whereNumber([
                        'user',
                        'environment',
                    ])
                    ->middleware('permission:users.update')
                    ->name('environments.toggle');

                Route::post(
                    '/{user}/environments/{environment}/default',
                    [UserController::class, 'setDefaultEnvironment']
                )
                    ->whereNumber([
                        'user',
                        'environment',
                    ])
                    ->middleware('permission:users.update')
                    ->name('environments.default');
            });

        /*
        |--------------------------------------------------------------------------
        | Rôles
        |--------------------------------------------------------------------------
        */

        Route::prefix('roles')
            ->name('roles.')
            ->group(function () {

                Route::get(
                    '/',
                    [RoleController::class, 'index']
                )
                    ->middleware('permission:roles.view')
                    ->name('index');

                Route::get(
                    '/create',
                    [RoleController::class, 'create']
                )
                    ->middleware('permission:roles.create')
                    ->name('create');

                Route::post(
                    '/',
                    [RoleController::class, 'store']
                )
                    ->middleware('permission:roles.create')
                    ->name('store');

                Route::get(
                    '/{role}/edit',
                    [RoleController::class, 'edit']
                )
                    ->whereNumber('role')
                    ->middleware('permission:roles.update')
                    ->name('edit');

                Route::put(
                    '/{role}',
                    [RoleController::class, 'update']
                )
                    ->whereNumber('role')
                    ->middleware('permission:roles.update')
                    ->name('update');

                Route::post(
                    '/{role}/toggle-active',
                    [RoleController::class, 'toggleActive']
                )
                    ->whereNumber('role')
                    ->middleware('permission:roles.update')
                    ->name('toggle-active');

                Route::delete(
                    '/{role}',
                    [RoleController::class, 'destroy']
                )
                    ->whereNumber('role')
                    ->middleware('permission:roles.delete')
                    ->name('destroy');
            });

        /*
        |--------------------------------------------------------------------------
        | Environnements
        |--------------------------------------------------------------------------
        */

        Route::prefix('environments')
            ->name('environments.')
            ->group(function () {

                Route::get(
                    '/',
                    [EnvironmentController::class, 'index']
                )
                    ->middleware('permission:environments.view')
                    ->name('index');

                Route::get(
                    '/create',
                    [EnvironmentController::class, 'create']
                )
                    ->middleware('permission:environments.create')
                    ->name('create');

                Route::post(
                    '/',
                    [EnvironmentController::class, 'store']
                )
                    ->middleware('permission:environments.create')
                    ->name('store');

                Route::get(
                    '/{environment}/edit',
                    [EnvironmentController::class, 'edit']
                )
                    ->whereNumber('environment')
                    ->middleware('permission:environments.update')
                    ->name('edit');

                Route::put(
                    '/{environment}',
                    [EnvironmentController::class, 'update']
                )
                    ->whereNumber('environment')
                    ->middleware('permission:environments.update')
                    ->name('update');

                Route::post(
                    '/{environment}/toggle-active',
                    [EnvironmentController::class, 'toggleActive']
                )
                    ->whereNumber('environment')
                    ->middleware('permission:environments.update')
                    ->name('toggle-active');

                Route::delete(
                    '/{environment}/logo',
                    [EnvironmentController::class, 'removeLogo']
                )
                    ->whereNumber('environment')
                    ->middleware('permission:environments.update')
                    ->name('logo.destroy');

                Route::delete(
                    '/{environment}/logo-dark',
                    [EnvironmentController::class, 'removeLogoDark']
                )
                    ->whereNumber('environment')
                    ->middleware('permission:environments.update')
                    ->name('logo-dark.destroy');

                Route::delete(
                    '/{environment}/favicon',
                    [EnvironmentController::class, 'removeFavicon']
                )
                    ->whereNumber('environment')
                    ->middleware('permission:environments.update')
                    ->name('favicon.destroy');
            });

        /*
        |--------------------------------------------------------------------------
        | Années scolaires
        |--------------------------------------------------------------------------
        */

        Route::prefix('school-years')
            ->name('school-years.')
            ->group(function () {

                Route::get(
                    '/',
                    [SchoolYearController::class, 'index']
                )
                    ->middleware('permission:school-years.view')
                    ->name('index');

                Route::get(
                    '/create',
                    [SchoolYearController::class, 'create']
                )
                    ->middleware('permission:school-years.create')
                    ->name('create');

                Route::post(
                    '/',
                    [SchoolYearController::class, 'store']
                )
                    ->middleware('permission:school-years.create')
                    ->name('store');

                Route::get(
                    '/{schoolYear}/edit',
                    [SchoolYearController::class, 'edit']
                )
                    ->whereNumber('schoolYear')
                    ->middleware('permission:school-years.update')
                    ->name('edit');

                Route::put(
                    '/{schoolYear}',
                    [SchoolYearController::class, 'update']
                )
                    ->whereNumber('schoolYear')
                    ->middleware('permission:school-years.update')
                    ->name('update');

                Route::post(
                    '/{schoolYear}/set-current',
                    [SchoolYearController::class, 'setCurrent']
                )
                    ->whereNumber('schoolYear')
                    ->middleware('permission:school-years.update')
                    ->name('set-current');

                Route::post(
                    '/{schoolYear}/toggle-active',
                    [SchoolYearController::class, 'toggleActive']
                )
                    ->whereNumber('schoolYear')
                    ->middleware('permission:school-years.update')
                    ->name('toggle-active');

                Route::delete(
                    '/{schoolYear}',
                    [SchoolYearController::class, 'destroy']
                )
                    ->whereNumber('schoolYear')
                    ->middleware('permission:school-years.delete')
                    ->name('destroy');
            });

        /*
        |--------------------------------------------------------------------------
        | Structure pédagogique
        |--------------------------------------------------------------------------
        |
        | Arborescence :
        |
        | Environnement
        |     └── Année scolaire
        |           └── Cycle
        |                 └── Niveau
        |                       └── Classe
        |
        */

        Route::get(
            '/academic-structure',
            [AcademicStructureController::class, 'index']
        )
            ->middleware('permission:school-years.view')
            ->name('academic-structure.index');

        /*
        |--------------------------------------------------------------------------
        | Cycles
        |--------------------------------------------------------------------------
        */

        Route::prefix('cycles')
            ->name('cycles.')
            ->group(function () {

                Route::post(
                    '/',
                    [CycleController::class, 'store']
                )
                    ->middleware('permission:school-years.create')
                    ->name('store');

                Route::put(
                    '/{cycle}',
                    [CycleController::class, 'update']
                )
                    ->whereNumber('cycle')
                    ->middleware('permission:school-years.update')
                    ->name('update');

                Route::post(
                    '/{cycle}/toggle-active',
                    [CycleController::class, 'toggleActive']
                )
                    ->whereNumber('cycle')
                    ->middleware('permission:school-years.update')
                    ->name('toggle-active');

                Route::delete(
                    '/{cycle}',
                    [CycleController::class, 'destroy']
                )
                    ->whereNumber('cycle')
                    ->middleware('permission:school-years.delete')
                    ->name('destroy');
            });

        /*
        |--------------------------------------------------------------------------
        | Niveaux
        |--------------------------------------------------------------------------
        */

        Route::prefix('levels')
            ->name('levels.')
            ->group(function () {

                Route::post(
                    '/',
                    [LevelController::class, 'store']
                )
                    ->middleware('permission:school-years.create')
                    ->name('store');

                Route::put(
                    '/{level}',
                    [LevelController::class, 'update']
                )
                    ->whereNumber('level')
                    ->middleware('permission:school-years.update')
                    ->name('update');

                Route::post(
                    '/{level}/toggle-active',
                    [LevelController::class, 'toggleActive']
                )
                    ->whereNumber('level')
                    ->middleware('permission:school-years.update')
                    ->name('toggle-active');

                Route::delete(
                    '/{level}',
                    [LevelController::class, 'destroy']
                )
                    ->whereNumber('level')
                    ->middleware('permission:school-years.delete')
                    ->name('destroy');
            });

        /*
        |--------------------------------------------------------------------------
        | Classes
        |--------------------------------------------------------------------------
        */

        Route::prefix('school-classes')
            ->name('school-classes.')
            ->group(function () {

                Route::post(
                    '/',
                    [SchoolClassController::class, 'store']
                )
                    ->middleware('permission:school-years.create')
                    ->name('store');

                Route::put(
                    '/{schoolClass}',
                    [SchoolClassController::class, 'update']
                )
                    ->whereNumber('schoolClass')
                    ->middleware('permission:school-years.update')
                    ->name('update');

                Route::post(
                    '/{schoolClass}/toggle-active',
                    [SchoolClassController::class, 'toggleActive']
                )
                    ->whereNumber('schoolClass')
                    ->middleware('permission:school-years.update')
                    ->name('toggle-active');

                Route::delete(
                    '/{schoolClass}',
                    [SchoolClassController::class, 'destroy']
                )
                    ->whereNumber('schoolClass')
                    ->middleware('permission:school-years.delete')
                    ->name('destroy');
            });

        /*
        |--------------------------------------------------------------------------
        | Catégories de tickets
        |--------------------------------------------------------------------------
        */

        Route::prefix('ticket-categories')
            ->name('ticket-categories.')
            ->group(function () {

                Route::get(
                    '/',
                    [TicketCategoryController::class, 'index']
                )
                    ->middleware('permission:ticket-categories.view')
                    ->name('index');

                Route::get(
                    '/create',
                    [TicketCategoryController::class, 'create']
                )
                    ->middleware('permission:ticket-categories.create')
                    ->name('create');

                Route::post(
                    '/',
                    [TicketCategoryController::class, 'store']
                )
                    ->middleware('permission:ticket-categories.create')
                    ->name('store');

                /*
                 * Important :
                 * reorder avant /{ticketCategory}
                 */

                Route::post(
                    '/reorder',
                    [TicketCategoryController::class, 'reorder']
                )
                    ->middleware('permission:ticket-categories.update')
                    ->name('reorder');

                Route::get(
                    '/{ticketCategory}/edit',
                    [TicketCategoryController::class, 'edit']
                )
                    ->whereNumber('ticketCategory')
                    ->middleware('permission:ticket-categories.update')
                    ->name('edit');

                Route::put(
                    '/{ticketCategory}',
                    [TicketCategoryController::class, 'update']
                )
                    ->whereNumber('ticketCategory')
                    ->middleware('permission:ticket-categories.update')
                    ->name('update');

                Route::post(
                    '/{ticketCategory}/toggle-active',
                    [TicketCategoryController::class, 'toggleActive']
                )
                    ->whereNumber('ticketCategory')
                    ->middleware('permission:ticket-categories.update')
                    ->name('toggle-active');

                Route::delete(
                    '/{ticketCategory}',
                    [TicketCategoryController::class, 'destroy']
                )
                    ->whereNumber('ticketCategory')
                    ->middleware('permission:ticket-categories.delete')
                    ->name('destroy');
            });

        /*
        |--------------------------------------------------------------------------
        | Équipes de support
        |--------------------------------------------------------------------------
        */

        Route::prefix('support-teams')
            ->name('support-teams.')
            ->group(function () {

                Route::get(
                    '/',
                    [SupportTeamController::class, 'index']
                )
                    ->middleware('permission:support-teams.view')
                    ->name('index');

                Route::get(
                    '/create',
                    [SupportTeamController::class, 'create']
                )
                    ->middleware('permission:support-teams.create')
                    ->name('create');

                Route::post(
                    '/',
                    [SupportTeamController::class, 'store']
                )
                    ->middleware('permission:support-teams.create')
                    ->name('store');

                Route::get(
                    '/{supportTeam}/edit',
                    [SupportTeamController::class, 'edit']
                )
                    ->whereNumber('supportTeam')
                    ->middleware('permission:support-teams.update')
                    ->name('edit');

                Route::put(
                    '/{supportTeam}',
                    [SupportTeamController::class, 'update']
                )
                    ->whereNumber('supportTeam')
                    ->middleware('permission:support-teams.update')
                    ->name('update');

                Route::post(
                    '/{supportTeam}/toggle-active',
                    [SupportTeamController::class, 'toggleActive']
                )
                    ->whereNumber('supportTeam')
                    ->middleware('permission:support-teams.update')
                    ->name('toggle-active');

                Route::delete(
                    '/{supportTeam}',
                    [SupportTeamController::class, 'destroy']
                )
                    ->whereNumber('supportTeam')
                    ->middleware('permission:support-teams.delete')
                    ->name('destroy');
            });
    });
});

/*
|--------------------------------------------------------------------------
| Settings Routes
|--------------------------------------------------------------------------
*/

require __DIR__.'/settings.php';