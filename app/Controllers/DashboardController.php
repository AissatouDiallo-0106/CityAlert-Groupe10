<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\ReportRepository;

final class DashboardController extends AbstractController
{
    public function index(Request $req): Response
    {
        $user = $this->requireAuth();
        $reports = new ReportRepository();
        // Citoyen : ses signalements ; Agent/Admin : tous (récents).
        $list = $user->isAdmin() || $user->isAgent()
            ? $reports->search([], 1, 10)
            : $reports->search(['author_id' => $user->getId()], 1, 10);

        return $this->render('dashboard/index', [
            'title'   => 'Mon espace',
            'reports' => $list,
        ]);
    }
}
