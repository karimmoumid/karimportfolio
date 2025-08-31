<?php

namespace App\Controller;

use App\Repository\MessageRepository;
use App\Repository\ProjectRepository;
use App\Repository\TestimonialRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('', name: 'admin_dashboard')]
    public function index(
        ProjectRepository $projectRepo,
        MessageRepository $messageRepo,
        TestimonialRepository $testimonialRepo
    ): Response {
        $stats = [
            'totalProjects' => $projectRepo->count([]),
            'totalViews' => $this->getTotalViews($projectRepo),
            'unreadMessages' => $messageRepo->createQueryBuilder('m')
                ->select('COUNT(m.id)')
                ->where('m.readAt IS NULL')
                ->getQuery()
                ->getSingleScalarResult(),
            'totalTestimonials' => $testimonialRepo->count([])
        ];

        $recentProjects = $projectRepo->findBy([], ['createdAt' => 'DESC'], 5);
        $recentMessages = $messageRepo->findBy([], ['createdAt' => 'DESC'], 5);

        return $this->render('admin/dashboard/index.html.twig', [
            'stats' => $stats,
            'recentProjects' => $recentProjects,
            'recentMessages' => $recentMessages
        ]);
    }

    private function getTotalViews(ProjectRepository $projectRepo): int
    {
        return (int) $projectRepo->createQueryBuilder('p')
            ->select('SUM(p.viewCount)')
            ->getQuery()
            ->getSingleScalarResult() ?? 0;
    }
}
