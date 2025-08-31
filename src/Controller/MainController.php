<?php

namespace App\Controller;

use App\Repository\ProjectRepository;
use App\Repository\SkillRepository;
use App\Repository\TestimonialRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MainController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        ProjectRepository $projectRepo,
        SkillRepository $skillRepo,
        TestimonialRepository $testimonialRepo
    ): Response {
        // Projets les plus vus (top 3)
        $featuredProjects = $projectRepo->featuredProjects();
        // Toutes les compétences
        $skills = $skillRepo->findAll();

        // Testimonials généraux (non liés à un projet)
        $testimonials = $testimonialRepo->testimonialsGeneral();

        return $this->render('main/index.html.twig', [
            'featuredProjects' => $featuredProjects,
            'skills' => $skills,
            'testimonials' => $testimonials
        ]);
    }

    #[Route('/about', name: 'app_about')]
    public function about(): Response
    {
        return $this->render('pages/about.html.twig');
    }
}
