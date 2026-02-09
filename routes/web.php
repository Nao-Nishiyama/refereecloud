<?php

use App\Models\Competition;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\ChiefAssignController;
use App\Http\Controllers\CompetitionController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\AnnualReportController;
use App\Http\Controllers\Admin\ProfilesController;
use App\Http\Controllers\Admin\RefereesController;
use App\Http\Controllers\Admin\ApplicationsController;
use App\Http\Controllers\Admin\CompetitionsController;
use App\Http\Controllers\NominationCapacityController;
use App\Http\Controllers\Admin\RefereeExportController;
use App\Http\Controllers\Admin\RefereeImportController;
use App\Http\Controllers\Admin\RefereeReportsController;
use App\Http\Controllers\Admin\RefereeApprovalsController;
use App\Http\Controllers\Admin\CompetitionImportController;

Route::get('/', function () {
    return view('welcome');
});

Route::view('/terms', 'legal.terms')->name('terms');
Route::view('/privacy', 'legal.privacy')->name('privacy');

Auth::routes();
    
Route::group(['middleware' => 'auth'], function(){
    Route::get('/', [HomeController::class, 'index'])->name('index');
    
    Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => 'admin'], function(){
        
        Route::get('/competitions/import', [CompetitionImportController::class, 'show'])
            ->name('competitions.import');
        Route::post('competitions/import', [CompetitionImportController::class, 'store'])
            ->name('competitions.import.store');
        
        Route::get('/referees/import', [RefereeImportController::class, 'show'])
        ->name('referees.import.show');
        Route::get('/referees/database', [RefereeImportController::class, 'database'])
            ->name('referees.database');
        Route::post('referees/import', [RefereeImportController::class, 'store'])
            ->name('referees.import.store');

        // Export page
        Route::get('/referees/export/show', [RefereeExportController::class, 'show'])
            ->name('referees.export.show');
        Route::post('/referees/export', [RefereeExportController::class, 'export'])
            ->name('referees.export');

        Route::get('/referees', [RefereesController::class, 'index'])
            ->name('referees');
        Route::get('/referees/show', [RefereesController::class, 'show'])
            ->name('referees.show');    
        Route::get('/referees/showForChief', [RefereesController::class, 'showForChief'])
            ->name('referees.showForChief');    
        Route::get('/referees/create', [RefereesController::class, 'create'])
            ->name('referees.create');
        Route::post('/referees/store', [RefereesController::class, 'store'])
            ->name('referees.store');
        Route::post('/referees/check-duplicate', [RefereesController::class,'checkDuplicate'])
            ->name('referees.check-duplicate');
        Route::get('/referees/{id}/edit', [RefereesController::class, 'edit'])
            ->name('referees.edit');    
        Route::patch('/referees/{id}/attach-user', [RefereesController::class, 'attach'])
            ->name('referees.attach-user');
        Route::patch('/referees/{id}/detach-user', [RefereesController::class, 'detach'])
        ->name('referees.detach-user');
        Route::delete('/referees/{referee}/approvals/{year}',[RefereesController::class, 'destroy'])
            ->middleware('can:referees.delete')
            ->name('referee.approvals.destroy');
        Route::patch('/referees/{referee}/restore',[RefereesController::class, 'restore'])
            ->middleware('can:referees.restore')
            ->name('referees.restore');

        Route::get('/referees/operate', [RefereeApprovalsController::class, 'show'])
            ->name('referees.approval');
        Route::patch('/referees/{referee}/approvals/{year}/approve',[RefereeApprovalsController::class, 'approve'])
            ->name('referee.approvals.approve');
        Route::post('/referees/approvals/bulk-approve', [RefereeApprovalsController::class, 'bulkApprove'])
            ->name('referee.approvals.bulkApprove');

        Route::get ('/referees/duplicate-report',  [ReportController::class, 'duplicateCreate'])
            ->name('referees.duplicate.report.create');
        Route::post('/referees/duplicate-report',  [ReportController::class, 'duplicateStore'])
            ->name('referees.duplicate.report.store');

        Route::get('/referees/reports', [RefereeReportsController::class, 'show'])
            ->name('referees.reports.show');
        Route::get('/referees/reports/{id}/show', [RefereeReportsController::class, 'indRepShow'])
            ->name('users.reports.show');

        Route::patch('/users/{id}/make-admin', [UsersController::class, 'makeAdmin'])
        ->name('status.makeAdmin');
        Route::patch('/users/{id}/make-committee', [UsersController::class, 'makeCommittee'])
        ->name('status.makeCommittee');
        Route::patch('/users/{id}/make-chief', [UsersController::class, 'makeChief'])
        ->name('status.makeChief');
        Route::patch('/users/{id}/make-user', [UsersController::class, 'makeUser'])
        ->name('status.makeUser');

        Route::get('/competitions', [CompetitionsController::class, 'index'])->name('competitions');
        Route::get('/competitions/show', [CompetitionsController::class, 'show'])->name('competitions.show');
        Route::get('/competitions/create', [CompetitionsController::class, 'create'])->name('competitions.create');
        Route::post('/competitions/store', [CompetitionsController::class, 'store'])->name('competitions.store');
        Route::get('/competitions/{id}/edit', [CompetitionsController::class, 'edit'])->name('competitions.edit');
        Route::patch('/competitions/{id}/update', [CompetitionsController::class, 'update'])->name('competitions.update');
        Route::delete('/competitions/{competition}/delete', [CompetitionsController::class,'destroy'])
            ->name('competitions.destroy');
        Route::patch('/competitions/{id}/restore', [CompetitionsController::class,'restore'])
            ->name('competitions.restore');
        Route::delete('/competitions/{id}/force', [CompetitionsController::class,'forceDestroy'])
            ->name('competitions.force');

        Route::get('/competitions/{id}/showdetail', [CompetitionsController::class, 'showdetail'])->name('competitions.showdetail');
        Route::patch('/competitions/{id}/assign', [CompetitionsController::class, 'assign'])->name('competitions.assign');

        Route::get ('/nominations/capacities', [NominationCapacityController::class,'show'])->name('nominations.capacities.show');
        Route::get ('/nominations/capacities/edit', [NominationCapacityController::class,'edit'])->name('nominations.capacities.edit');
        Route::patch('/nominations/capacities/update', [NominationCapacityController::class,'bulkUpdate'])->name('nominations.capacities.bulkUpdate');

        Route::get('/applicatitions', [ApplicationsController::class, 'show'])->name('applications');
        Route::get('/applicatitions/{id}/detail', [ApplicationsController::class, 'application'])->name('applications.detail');

        Route::get('profiles/{id}/edit', [ProfilesController::class, 'edit'])->name('profiles.edit');
        Route::patch('profiles/{id}/update', [ProfilesController::class, 'update'])->name('profiles.update');
    });

    Route::middleware(['auth','is.chief'])->group(function () {
        Route::get('/nominations/{nomination}/candidates', [ChiefAssignController::class, 'candidates'])->name('chief.candidates');
        Route::post('/nominations/{nomination}/assign',     [ChiefAssignController::class, 'assign'])->name('chief.assign');
        Route::delete('/nominations/{nomination}/assign/{referee}', [ChiefAssignController::class, 'unassign'])->name('chief.unassign');
    });

    // COMPETITIONS
    Route::get('/competitions/show', [CompetitionController::class, 'show'])->name('competitions.show');
    Route::get('/competitions/offer', [CompetitionController::class, 'offer'])->name('competition.offer');
    Route::post('/competitions/register', [CompetitionController::class, 'register'])->name('competition.register');

    Route::get('/competitions/{id}/apply', [ApplicationController::class, 'apply'])->name('competition.apply');
    Route::post('/competitions/{competition}/store/', [ApplicationController::class, 'store'])->name('application.store');
    Route::delete('/competitions/{competition}/applications/{nomination}', [ApplicationController::class,'destroy'])->name('application.cancel')->scopeBindings();

    // REPORTS
    Route::get('/reports/{id}/show', [AnnualReportController::class, 'show'])->name('reports.show');
    Route::get('/reports/create', [AnnualReportController::class, 'create'])->name('reports.create');
    Route::post('/reports/store', [AnnualReportController::class, 'store'])->name('reports.store');

    // PROFILE
    Route::get('/profile/show', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    
});