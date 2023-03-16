<?php declare(strict_types=1);

use GuardsmanPanda\Larabear\Web\Www\Email\Controller\LarabearEmailController;
use Illuminate\Support\Facades\Route;

Route::get(uri: 'email-content/{email}', action: [LarabearEmailController::class, 'emailContent']);
