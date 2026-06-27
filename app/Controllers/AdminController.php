<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\ReportRepository;

final class AdminController extends AbstractController
{
    public function stats(Request $req): Response
    {
        $this->requireRole(\App\Enums\Role::ADMIN);
        $reports = new ReportRepository();
        return $this->render('admin/stats', [
            'title'      => 'Statistiques',
            'byStatus'   => $reports->statsByStatus(),
            'byCategory' => $reports->statsByCategory(),
            'avgDays'    => $reports->averageResolutionDays(),
        ]);
    }
}
