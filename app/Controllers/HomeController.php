<?php
declare(strict_types=1);

namespace LamShaml\Controllers;

use LamShaml\Core\View;
use LamShaml\Repositories\RequestRepository;

final class HomeController
{
    public function index(): string
    {
        return View::render('home', [
            'title' => 'لم شمل',
            'stats' => (new RequestRepository())->stats(),
        ]);
    }
}
