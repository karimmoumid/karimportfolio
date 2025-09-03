<?php

namespace App\Controller;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use App\Repository\SkillRepository;
use App\Repository\TestimonialRepository;
use App\Service\ProjectService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/projects')]
class ProjectController extends AbstractController
{
    #[Route('', name: 'app_projects')]
    public function index(
        Request $request,
        ProjectRepository $projectRepo,
        SkillRepository $skillRepo
    ): Response {
        $skillFilter = $request->query->get('skill');
        $search = $request->query->get('search');

        $qb = $projectRepo->createQueryBuilder('p')
            ->leftJoin('p.skill', 's')
            ->orderBy('p.created_at', 'DESC');

        if ($skillFilter) {
            $qb->andWhere('s.name LIKE :skill')
                ->setParameter('skill', '%' . $skillFilter . '%');
        }

        if ($search) {
            $qb->andWhere('p.title LIKE :search OR p.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $projects = $qb->getQuery()->getResult();
        $skills = $skillRepo->findAll();

        return $this->render('project/index.html.twig', [
            'projects' => $projects,
            'skills' => $skills,
            'currentSkill' => $skillFilter,
            'currentSearch' => $search
        ]);
    }

    #[Route('/{id}', name: 'app_project_show', requirements: ['id' => '\d+'])]
    public function show(
        Project $project,
        ProjectService $projectService,
        TestimonialRepository $testimonialRepo
    ): Response {
        // Incrémenter le compteur de vues
        $projectService->incrementViewCount($project);

        // Projets similaires (même skills)
        $relatedProjects = $projectService->getRelatedProjects($project, 3);

        // Testimonials spécifiques à ce projet
        $testimonials = $testimonialRepo->createQueryBuilder('t')
            ->where('t.project = :project')
            ->setParameter('project', $project)
            ->orderBy('t.created_at', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('project/show.html.twig', [
            'project' => $project,
            'relatedProjects' => $relatedProjects,
            'testimonials' => $testimonials
        ]);
    }
    #[Route('', name: 'api_projects', methods: ['GET'])]
    public function getProjects(Request $request, ProjectRepository $projectRepo): JsonResponse
    {
        $skill = $request->query->get('skill');
        $search = $request->query->get('search');

        $qb = $projectRepo->createQueryBuilder('p')
            ->leftJoin('p.skills', 's')
            ->orderBy('p.createdAt', 'DESC');

        if ($skill) {
            $qb->andWhere('s.name LIKE :skill')
                ->setParameter('skill', '%' . $skill . '%');
        }

        if ($search) {
            $qb->andWhere('p.title LIKE :search OR p.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $projects = $qb->getQuery()->getResult();

        $data = [];
        foreach ($projects as $project) {
            $data[] = [
                'id' => $project->getId(),
                'title' => $project->getTitle(),
                'description' => $project->getDescription(),
                'image' => $project->getImage(),
                'viewCount' => $project->getViewCount(),
                'skills' => array_map(fn($skill) => $skill->getName(), $project->getSkills()->toArray()),
                'url' => $this->generateUrl('app_project_show', ['id' => $project->getId()])
            ];
        }

        return $this->json($data);
    }


}
