<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Enums\ReportStatus;
use App\Models\Categories\CategoryFactory;
use App\Models\Entities\{Report, StatusHistory};
use App\Repositories\{ReportRepository, CommentRepository};
use App\Services\NotificationService;
use App\Repositories\UserRepository;
use App\Exceptions\{ValidationException, AuthorizationException, EntityNotFoundException};

final class ReportController extends AbstractController
{
    private ReportRepository $reports;

    public function __construct()
    {
        parent::__construct();
        $this->reports = new ReportRepository();
    }

    /** Liste filtrable + paginée. */
    public function index(Request $req): Response
    {
        $filters = array_filter([
            'status'   => (string) $req->get('status', ''),
            'category' => (string) $req->get('category', ''),
        ]);
        $page    = max(1, (int) $req->get('page', 1));
        $perPage = 8;

        $reports = $this->reports->search($filters, $page, $perPage);
        $total   = $this->reports->countSearch($filters);

        return $this->render('reports/index', [
            'title'      => 'Signalements',
            'reports'    => $reports,
            'filters'    => $filters,
            'page'       => $page,
            'pages'      => (int) ceil($total / $perPage),
            'total'      => $total,
            'categories' => CategoryFactory::all(),
        ]);
    }

    public function show(Request $req, string $id): Response
    {
        $report = $this->reports->findWithAuthor((int) $id);
        if (!$report) throw new EntityNotFoundException('Signalement introuvable.');

        return $this->render('reports/show', [
            'title'    => $report->getTitle(),
            'report'   => $report,
            'history'  => $this->reports->historyFor((int) $id),
            'comments' => (new CommentRepository())->forReport((int) $id),
        ]);
    }

    public function create(Request $req): Response
    {
        $this->requireAuth();
        return $this->render('reports/create', ['title' => 'Nouveau signalement', 'categories' => CategoryFactory::all()]);
    }

    public function store(Request $req): Response
    {
        $user = $this->requireAuth();
        $this->verifyCsrf($req);
        try {
            $report = new Report();
            $this->fill($report, $req);
            $report->setAuthorId($user->getId());
            $report->setStatus(ReportStatus::NEW);
            $this->reports->save($report);
            return $this->redirect('reports/' . $report->getId(), 'Signalement créé.');
        } catch (ValidationException $e) {
            $_SESSION['_old'] = $req->all();
            return $this->render('reports/create', [
                'title' => 'Nouveau signalement', 'errors' => $e->errors(), 'categories' => CategoryFactory::all(),
            ]);
        }
    }

    public function edit(Request $req, string $id): Response
    {
        $report = $this->guardOwnerEditable((int) $id);
        return $this->render('reports/edit', ['title' => 'Modifier', 'report' => $report, 'categories' => CategoryFactory::all()]);
    }

    public function update(Request $req, string $id): Response
    {
        $report = $this->guardOwnerEditable((int) $id);
        $this->verifyCsrf($req);
        try {
            $this->fill($report, $req);
            $this->reports->save($report);
            return $this->redirect('reports/' . $id, 'Signalement modifié.');
        } catch (ValidationException $e) {
            $_SESSION['_old'] = $req->all();
            return $this->render('reports/edit', [
                'title' => 'Modifier', 'report' => $report, 'errors' => $e->errors(), 'categories' => CategoryFactory::all(),
            ]);
        }
    }

    public function destroy(Request $req, string $id): Response
    {
        $this->guardOwnerEditable((int) $id);
        $this->verifyCsrf($req);
        $this->reports->delete((int) $id);
        return $this->redirect('reports', 'Signalement supprimé.');
    }

    /** Changement de statut par un agent (cycle de vie + historique + notification). */
    public function changeStatus(Request $req, string $id): Response
    {
        $agent = $this->requireRole(\App\Enums\Role::AGENT);
        $this->verifyCsrf($req);
        $report = $this->reports->findWithAuthor((int) $id);
        if (!$report) throw new EntityNotFoundException('Signalement introuvable.');

        $target = ReportStatus::from((string) $req->post('status'));
        if (!$report->getStatus()->canTransitionTo($target)) {
            return $this->redirect('reports/' . $id, 'Transition de statut non autorisée.', 'error');
        }

        $report->setStatus($target);
        $report->setAgentId($agent->getId());
        $this->reports->save($report);

        $h = new StatusHistory();
        $h->setReportId((int) $id);
        $h->setAgentId($agent->getId());
        $h->setStatus($target);
        $h->setComment((string) $req->post('comment', ''));
        $this->reports->addHistory($h);

        // Notifier l'auteur (interface NotifiableInterface)
        $author = (new UserRepository())->find($report->getAuthorId());
        if ($author) {
            (new NotificationService())->notify($author,
                'Mise à jour de votre signalement',
                'Statut : ' . $target->label());
        }
        return $this->redirect('reports/' . $id, 'Statut mis à jour : ' . $target->label());
    }

    /** Remplit un report depuis la requête, avec validation. */
    private function fill(Report $report, Request $req): void
    {
        $errors = [];
        $title = trim((string) $req->post('title', ''));
        $desc  = trim((string) $req->post('description', ''));
        $addr  = trim((string) $req->post('address', ''));
        $cat   = (string) $req->post('category', 'VOIRIE');

        if (mb_strlen($title) < 4) $errors['title'] = 'Titre trop court.';
        if (mb_strlen($desc) < 10) $errors['description'] = 'Description trop courte (10 caractères min).';
        if ($addr === '') $errors['address'] = 'Adresse requise.';
        if ($errors) throw new ValidationException($errors);

        $report->setTitle($title);
        $report->setDescription($desc);
        $report->setAddress($addr);
        $report->setCategory($cat);
        $report->setPhoto((string) $req->post('photo', '') ?: null);
    }

    private function guardOwnerEditable(int $id): Report
    {
        $user = $this->requireAuth();
        $report = $this->reports->findWithAuthor($id);
        if (!$report) throw new EntityNotFoundException('Signalement introuvable.');
        if ($report->getAuthorId() !== $user->getId() && !$user->isAdmin()) {
            throw new AuthorizationException('Vous n\'êtes pas l\'auteur.');
        }
        if (!$report->isEditable()) {
            throw new AuthorizationException('Modifiable uniquement tant que « Nouveau ».');
        }
        return $report;
    }
}
