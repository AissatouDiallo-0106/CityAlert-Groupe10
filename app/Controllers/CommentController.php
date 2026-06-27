<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Entities\Comment;
use App\Repositories\CommentRepository;

final class CommentController extends AbstractController
{
    public function store(Request $req, string $reportId): Response
    {
        $user = $this->requireAuth();
        $this->verifyCsrf($req);
        $body = trim((string) $req->post('body', ''));
        if ($body !== '') {
            $c = new Comment();
            $c->setReportId((int) $reportId);
            $c->setAuthorId($user->getId());
            $c->setBody($body);
            (new CommentRepository())->save($c);
        }
        return $this->redirect('reports/' . $reportId, 'Commentaire ajouté.');
    }
}
