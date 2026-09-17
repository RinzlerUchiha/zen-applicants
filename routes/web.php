<?php

use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BasicAbstractReasoningController;
use App\Http\Controllers\BasicMathController;
use App\Http\Controllers\CareerAnchorController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\CharacterRefController;
use App\Http\Controllers\ColorController;
use App\Http\Controllers\DiscController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\EducationController;
use App\Http\Controllers\EligibilityController;
use App\Http\Controllers\EmploymentRecController;
use App\Http\Controllers\EnneagramController;
use App\Http\Controllers\FamilyController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JobListingController;
use App\Http\Controllers\MayaController;
use App\Http\Controllers\MiqController;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\TaptController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VakController;
use App\Http\Controllers\WhyIWorkController;
use Illuminate\Support\Facades\Route;

// The front door. A visitor gets the landing page; a signed-in applicant is
// taken straight to their own application, since they have already landed.
Route::get('/', [JobListingController::class, 'landing'])->name('landing');

// Public: both documents must be reachable without an account, since they are
// what a visitor reads before deciding to create one — and the registration
// acknowledgement links to each of them.
Route::view('/privacy', 'pages.privacy')->name('privacy');
Route::view('/terms', 'pages.terms')->name('terms');

Route::get('/careers', [JobListingController::class, 'index'])->name('careers.index');
Route::get('/careers/{id}', [JobListingController::class, 'show'])->name('careers.show');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/apply', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [UserController::class, 'store'])->name('register.store');
});

Route::middleware(['auth', 'checkUserStatus'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/home', [HomeController::class, 'index'])->name('home');
    // Route::get('/logout', [AuthController::class, 'logout']);

    Route::post('/careers/{id}/apply', [JobListingController::class, 'apply'])->name('careers.apply');
    Route::get('/applications', [ApplicationController::class, 'index'])->name('applications.index');
    // Withdraws ONE application. Found only among the signed-in applicant's own.
    Route::post('/applications/{application}/withdraw', [ApplicationController::class, 'withdraw'])
        ->whereNumber('application')->name('applications.withdraw');

    Route::get('/profile/personal', [UserController::class, 'show'])->name('personal.show');
    Route::post('/profile/personal', [UserController::class, 'store'])->name('personal.store')->middleware('continueAfterSave');

    Route::get('/profile/family', [FamilyController::class, 'index'])->name('family.index');
    Route::post('/profile/family', [FamilyController::class, 'store'])->name('family.store')->middleware('continueAfterSave');
    Route::delete('/profile/family/{id}', [FamilyController::class, 'delete'])->name('family.delete');

    Route::get('/profile/skill', [SkillController::class, 'index'])->name('skill.index');
    Route::post('/profile/skill', [SkillController::class, 'store'])->name('skill.store')->middleware('continueAfterSave');
    Route::delete('/profile/skill/{id}', [SkillController::class, 'delete'])->name('skill.delete');

    Route::get('/profile/education', [EducationController::class, 'index'])->name('education.index');
    Route::post('/profile/education', [EducationController::class, 'store'])->name('education.store')->middleware('continueAfterSave');
    Route::delete('/profile/education/{id}', [EducationController::class, 'delete'])->name('education.delete');

    Route::get('/profile/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::post('/profile/documents', [DocumentController::class, 'store'])->name('documents.store');
    // No delete: an applicant replaces a document by uploading a new one, which
    // sends it back to HR to check.
    Route::get('/profile/documents/{id}/view', [DocumentController::class, 'view'])->name('documents.view');

    Route::get('/professional/license', [EligibilityController::class, 'index'])->name('license.index');
    Route::post('/professional/license', [EligibilityController::class, 'store'])->name('license.store')->middleware('continueAfterSave');
    Route::delete('/professional/license/{id}', [EligibilityController::class, 'delete'])->name('license.delete');

    Route::get('/professional/certificate', [CertificateController::class, 'index'])->name('certificate.index');
    Route::post('/professional/certificate', [CertificateController::class, 'store'])->name('certificate.store')->middleware('continueAfterSave');
    Route::delete('/professional/certificate/{id}', [CertificateController::class, 'delete'])->name('certificate.delete');

    Route::get('/work/employment', [EmploymentRecController::class, 'index'])->name('employment.index');
    Route::post('/work/employment', [EmploymentRecController::class, 'store'])->name('employment.store')->middleware('continueAfterSave');
    Route::post('/work/employment/first-job', [EmploymentRecController::class, 'setFirstJob'])->name('employment.first-job');
    Route::delete('/work/employment/{id}', [EmploymentRecController::class, 'delete'])->name('employment.delete');

    Route::get('/work/characterref', [CharacterRefController::class, 'index'])->name('characterref.index');
    Route::post('/work/characterref', [CharacterRefController::class, 'store'])->name('characterref.store')->middleware('continueAfterSave');
    Route::delete('/work/characterref/{id}', [CharacterRefController::class, 'delete'])->name('characterref.delete');

    Route::get('/assessments', [AssessmentController::class, 'index'])->name('assessments.index');

    Route::get('/personality/enneagram', [EnneagramController::class, 'show'])->name('enneagram.show');
    Route::post('/personality/enneagram', [EnneagramController::class, 'store'])->name('enneagram.store');

    Route::get('/personality/tapt', [TaptController::class, 'show'])->name('tapt.show');
    Route::post('/personality/tapt', [TaptController::class, 'store'])->name('tapt.store');

    Route::get('/personality/disc', [DiscController::class, 'show'])->name('disc.show');
    Route::post('/personality/disc', [DiscController::class, 'store'])->name('disc.store');

    Route::get('/personality/miq', [MiqController::class, 'show'])->name('miq.show');
    Route::post('/personality/miq', [MiqController::class, 'store'])->name('miq.store');

    Route::get('/personality/color', [ColorController::class, 'show'])->name('color.show');
    Route::post('/personality/color', [ColorController::class, 'store'])->name('color.store');

    Route::get('/personality/vak', [VakController::class, 'show'])->name('vak.show');
    Route::post('/personality/vak', [VakController::class, 'store'])->name('vak.store');
    
    Route::get('/personality/why-i-work', [WhyIWorkController::class, 'show'])->name('why_i_work.show');
    Route::post('/personality/why-i-work', [WhyIWorkController::class, 'store'])->name('why_i_work.store');

    Route::get('/personality/career-anchors', [CareerAnchorController::class, 'show'])->name('career_anchors.show');
    Route::post('/personality/career-anchors', [CareerAnchorController::class, 'store'])->name('career_anchors.store');

    Route::get('/personality/abtract-reasoning', [BasicAbstractReasoningController::class, 'show'])->name('abstract_reasoning.show');
    Route::post('/personality/abtract-reasoning', [BasicAbstractReasoningController::class, 'store'])->name('abstract_reasoning.store');

    Route::get('/personality/basic-math', [BasicMathController::class, 'show'])->name('basic_math.show');
    Route::post('/personality/basic-math', [BasicMathController::class, 'store'])->name('basic_math.store');

    Route::get('/personality/maya', [MayaController::class, 'show'])->name('maya.show');
    Route::post('/personality/maya', [MayaController::class, 'store'])->name('maya.store');

    Route::get('/file/{src}/{filename}', [FileController::class, 'serve'])->name('file.get');

    Route::post('/profile/img', [UserController::class, 'storeProfileImg'])->name('file.store');
});