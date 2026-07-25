<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

use LamShaml\Controllers\AdminController;
use LamShaml\Controllers\AuthController;
use LamShaml\Controllers\DashboardController;
use LamShaml\Controllers\HealthController;
use LamShaml\Controllers\HomeController;
use LamShaml\Controllers\MatchController;
use LamShaml\Controllers\NotificationController;
use LamShaml\Controllers\RequestController;
use LamShaml\Controllers\SearchController;
use LamShaml\Core\Router;

$router = new Router();
$router->get('', [HomeController::class, 'index']);
$router->get('health', [HealthController::class, 'index']);
$router->get('register', [AuthController::class, 'registerForm']);
$router->post('register', [AuthController::class, 'register']);
$router->get('login', [AuthController::class, 'loginForm']);
$router->post('login', [AuthController::class, 'login']);
$router->post('logout', [AuthController::class, 'logout']);
$router->get('dashboard', [DashboardController::class, 'index']);
$router->get('requests/create/{type}', [RequestController::class, 'createForm']);
$router->post('requests/create/{type}', [RequestController::class, 'store']);
$router->get('requests/{id}', [RequestController::class, 'show']);
$router->get('search', [SearchController::class, 'index']);
$router->get('matches/report/{requestId}/{matchedId}', [MatchController::class, 'reportForm']);
$router->post('matches/report/{requestId}/{matchedId}', [MatchController::class, 'report']);
$router->get('notifications', [NotificationController::class, 'index']);
$router->post('notifications/read/{id}', [NotificationController::class, 'read']);
$router->get('admin', [AdminController::class, 'index']);
$router->get('admin/users', [AdminController::class, 'users']);
$router->post('admin/users/{id}/status', [AdminController::class, 'userStatus']);
$router->get('admin/requests', [AdminController::class, 'requests']);
$router->post('admin/requests/{id}/status', [AdminController::class, 'requestStatus']);
$router->get('admin/matches', [AdminController::class, 'matches']);
$router->post('admin/matches/{id}/status', [AdminController::class, 'matchStatus']);
$router->get('admin/reports', [AdminController::class, 'reports']);
$router->dispatch();
