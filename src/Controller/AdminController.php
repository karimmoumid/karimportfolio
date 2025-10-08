<?php

namespace App\Controller;

use App\Repository\MessageRepository;
use App\Repository\ProjectRepository;
use App\Repository\SkillRepository;
use App\Repository\TestimonialRepository;
use App\Repository\UserRepository;
use App\Service\ContactService;
use App\Service\ProjectService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\Project;
use App\Entity\ProjectImage;
use App\Entity\Skill;
use App\Entity\Message;
use App\Form\ProjectType;
use App\Form\SkillType;
use Symfony\Component\String\Slugger\SluggerInterface;


#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/menu', name: 'admin_dashboard')]
    public function index(
        ProjectRepository $projectRepo,
        MessageRepository $messageRepo,
        SkillRepository $skillRepo,
        TestimonialRepository $testimonialRepo,
        UserRepository $userRepo,
        ContactService $contactService,
        ProjectService $projectService
    ): Response {
        // Statistiques générales
        $stats = [
            'totalProjects' => $projectRepo->count([]),
            'totalSkills' => $skillRepo->count([]),
            'totalUsers' => $userRepo->count([]),
            'totalTestimonials' => $testimonialRepo->count([]),
        ];

        // Statistiques des messages
        $contactStats = $contactService->getContactStats();
        $stats = array_merge($stats, $contactStats);

        // Statistiques des projets
        $projectStats = $projectService->getProjectStats();
        $stats = array_merge($stats, $projectStats);

        // Données pour les graphiques
        $recentProjects = $projectRepo->findBy([], ['created_at' => 'DESC'], 5);
        $recentMessages = $messageRepo->findBy([], ['createdAt' => 'DESC'], 5);
        $popularProjects = $projectService->getMostViewedProjects(5);

        // Messages non lus
        $unreadMessages = $messageRepo->findBy(['readAt' => null], ['createdAt' => 'DESC'], 10);

        return $this->render('admin/dashboard/index.html.twig', [
            'stats' => $stats,
            'recentProjects' => $recentProjects,
            'recentMessages' => $recentMessages,
            'popularProjects' => $popularProjects,
            'unreadMessages' => $unreadMessages
        ]);
    }

    #[Route('/profile', name: 'admin_profile')]
    public function profile(): Response
    {
        return $this->render('admin/profile/index.html.twig');
    }

    public function __construct(private EntityManagerInterface $em)
    {}

    #[Route('/utilisateur', name: 'admin_users')]
    public function user(UserRepository $userRepo): Response
    {
        $users = $userRepo->findBy([], ['created_at' => 'DESC']);

        return $this->render('admin/user/index.html.twig', [
            'users' => $users
        ]);
    }

    #[Route('/nouveau/utilisateur', name: 'admin_user_new')]
    public function new(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hasher le mot de passe
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $user->setPassword(
                    $passwordHasher->hashPassword($user, $plainPassword)
                );
            }

            $this->em->persist($user);
            $this->em->flush();

            $this->addFlash('success', '✅ Utilisateur créé avec succès !');
            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    #[Route('/utilisateur/{id}/modification', name: 'admin_user_edit')]
    public function edit(Request $request, User $user, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(UserType::class, $user, ['is_edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Mettre à jour le mot de passe seulement s'il est fourni
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $user->setPassword(
                    $passwordHasher->hashPassword($user, $plainPassword)
                );
            }

            $this->em->flush();

            $this->addFlash('success', '✅ Utilisateur modifié avec succès !');
            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    #[Route('/utilisateur/{id}/suprimer', name: 'admin_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user): Response
    {
        // Empêcher la suppression de son propre compte
        if ($user === $this->getUser()) {
            $this->addFlash('error', '❌ Vous ne pouvez pas supprimer votre propre compte !');
            return $this->redirectToRoute('admin_users');
        }

        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $this->em->remove($user);
            $this->em->flush();

            $this->addFlash('success', '✅ Utilisateur supprimé avec succès !');
        }

        return $this->redirectToRoute('admin_users');
    }

    #[Route('/utilisateur/{id}/toggle-role', name: 'admin_user_toggle_role', methods: ['POST'])]
    public function toggleRole(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('toggle'.$user->getId(), $request->request->get('_token'))) {
            $roles = $user->getRoles();

            if (in_array('ROLE_ADMIN', $roles)) {
                $user->setRoles(['ROLE_USER']);
                $message = 'Utilisateur retiré des administrateurs';
            } else {
                $user->setRoles(['ROLE_ADMIN']);
                $message = 'Utilisateur promu administrateur';
            }

            $this->em->flush();
            $this->addFlash('success', '✅ ' . $message);
        }

        return $this->redirectToRoute('admin_users');
    }

// ========== PROJECTS ==========

    #[Route('/projects', name: 'admin_projects')]
    public function projects(ProjectRepository $projectRepository): Response
    {
        return $this->render('admin/project/index.html.twig', [
            'projects' => $projectRepository->findBy([], ['created_at' => 'DESC']),
        ]);
    }

    #[Route('/project/new', name: 'admin_project_new')]
    public function newProject(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $project = new Project();
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('imageFile')->getData();
            if ($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $cleanFilename = strtolower($slugger->slug($originalFilename));
                $newFilename = $cleanFilename . '-' . uniqid() . '.' . $file->guessExtension();
                $file->move($this->getParameter('app.uploads.projects'), $newFilename);
                $project->setImage($newFilename);
            }
            $galleryImagesFiles = $form->get('galleryImagesFiles')->getData();
            if ($galleryImagesFiles) {
                foreach ($galleryImagesFiles as $imageFile) {
                    $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $cleanFilename = strtolower($slugger->slug($originalFilename));
                    $newFilename = $cleanFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                    // Upload dans le dossier galerie
                    $imageFile->move($this->getParameter('app.uploads.projects.gallery'), $newFilename);
                    $projectImage = new ProjectImage();
                    $projectImage->setFilename($newFilename);
                    $projectImage->setOriginalName($imageFile->getClientOriginalName());
                    $projectImage->setAltText($project->getTitle() . ' - Image ' . ($project->getImages()->count() + 1));
                    $projectImage->setProject($project);

                    $em->persist($projectImage);

                }

            }

            $em->persist($project);
            $em->flush();

            $this->addFlash('success', 'Projet créé avec succès');
            return $this->redirectToRoute('admin_projects');
        }

        return $this->render('admin/project/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/project/{id}/edit', name: 'admin_project_edit')]
    public function editProject(Project $project, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Reset view count si demandé
            if ($request->request->get('reset_views')) {
                $project->setViewCount(0);
            }

            $em->flush();
            $this->addFlash('success', 'Projet modifié avec succès');
            return $this->redirectToRoute('admin_projects');
        }

        return $this->render('admin/project/edit.html.twig', [
            'form' => $form,
            'project' => $project,
        ]);
    }

    #[Route('/project/{id}/delete', name: 'admin_project_delete', methods: ['POST'])]
    public function deleteProject(Project $project, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$project->getId(), $request->request->get('_token'))) {
            $em->remove($project);
            $em->flush();
            $this->addFlash('success', 'Projet supprimé avec succès');
        }

        return $this->redirectToRoute('admin_projects');
    }

    // ========== SKILLS ==========

    #[Route('/skills', name: 'admin_skills')]
    public function skills(SkillRepository $skillRepository): Response
    {
        return $this->render('admin/skill/index.html.twig', [
            'skills' => $skillRepository->findBy([], ['name' => 'ASC']),
        ]);
    }

    #[Route('/skill/new', name: 'admin_skill_new')]
    public function newSkill(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $skill = new Skill();
        $form = $this->createForm(SkillType::class, $skill);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('logoFile')->getData();
                if ($file) {
                    $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $cleanFilename = strtolower($slugger->slug($originalFilename));
                    $newFilename = $cleanFilename . '-' . uniqid() . '.' . $file->guessExtension();
                    $file->move($this->getParameter('app.uploads.skills'), $newFilename);
                    $skill->setLogo($newFilename);
                }
            $em->persist($skill);
            $em->flush();

            $this->addFlash('success', 'Compétence ajoutée avec succès');
            return $this->redirectToRoute('admin_skills');
        }

        return $this->render('admin/skill/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/skill/{id}/edit', name: 'admin_skill_edit')]
    public function editSkill(Skill $skill, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(SkillType::class, $skill);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Compétence modifiée avec succès');
            return $this->redirectToRoute('admin_skills');
        }

        return $this->render('admin/skill/edit.html.twig', [
            'form' => $form,
            'skill' => $skill,
        ]);
    }

    #[Route('/skill/{id}/delete', name: 'admin_skill_delete', methods: ['POST'])]
    public function deleteSkill(Skill $skill, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$skill->getId(), $request->request->get('_token'))) {
            $em->remove($skill);
            $em->flush();
            $this->addFlash('success', 'Compétence supprimée avec succès');
        }

        return $this->redirectToRoute('admin_skills');
    }

    // ========== MESSAGES ==========

    #[Route('/messages', name: 'admin_messages')]
    public function messages(MessageRepository $messageRepository): Response
    {
        return $this->render('admin/message/index.html.twig', [
            'messages' => $messageRepository->findBy([], ['createdAt' => 'DESC']),
        ]);
    }

    #[Route('/message/{id}', name: 'admin_message_show')]
    public function showMessage(Message $message, EntityManagerInterface $em): Response
    {
        // Marquer comme lu si pas déjà lu
        if (!$message->getReadAt()) {
            $message->setReadAt(new \DateTimeImmutable());
            $em->flush();
        }

        return $this->render('admin/message/show.html.twig', [
            'message' => $message,
        ]);
    }

    #[Route('/message/{id}/delete', name: 'admin_message_delete', methods: ['POST'])]
    public function deleteMessage(Message $message, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$message->getId(), $request->request->get('_token'))) {
            $em->remove($message);
            $em->flush();
            $this->addFlash('success', 'Message supprimé avec succès');
        }

        return $this->redirectToRoute('admin_messages');
    }




}
