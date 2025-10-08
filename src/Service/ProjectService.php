<?php
// ================================================
// SERVICES MÉTIER
// ================================================

// src/Service/ProjectService.php
namespace App\Service;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;

class ProjectService
{
    public function __construct(
        private EntityManagerInterface $em,
        private ProjectRepository $projectRepository
    ) {}

    /**
     * Incrémente le compteur de vues d'un projet
     */
    public function incrementViewCount(Project $project): void
    {
        $project->setViewCount($project->getViewCount() + 1);
        $this->em->flush();
    }

    /**
     * Trouve des projets similaires basés sur les compétences partagées
     */
    public function getRelatedProjects(Project $project, int $limit = 3): array
    {
        // Récupérer les IDs des skills du projet courant
        $skillIds = [];
        foreach ($project->getSkill() as $skill) {
            $skillIds[] = $skill->getId();
        }

        if (empty($skillIds)) {
            // Si pas de skills, retourner les projets les plus récents
            return $this->projectRepository->findBy(
                ['id' => ['$ne' => $project->getId()]], // Exclure le projet courant
                ['created_at' => 'DESC'],
                $limit
            );
        }

        // Requête pour trouver les projets avec des skills similaires
        $qb = $this->projectRepository->createQueryBuilder('p')
            ->leftJoin('p.skill', 's')
            ->where('s.id IN (:skillIds)')
            ->andWhere('p.id != :currentProjectId')
            ->setParameter('skillIds', $skillIds)
            ->setParameter('currentProjectId', $project->getId())
            ->groupBy('p.id')
            ->orderBy('COUNT(s.id)', 'DESC') // Trier par nombre de skills en commun
            ->addOrderBy('p.view_count', 'DESC') // Puis par popularité
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    /**
     * Génère un slug unique pour un projet
     */
    public function generateSlug(string $title): string
    {
        // Convertir en minuscules et remplacer les caractères spéciaux
        $slug = strtolower($title);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');

        // Vérifier l'unicité
        $originalSlug = $slug;
        $counter = 1;

        while ($this->projectRepository->findOneBy(['slug' => $slug])) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Trouve les projets les plus populaires
     */
    public function getMostViewedProjects(int $limit = 5): array
    {
        return $this->projectRepository->findBy(
            [],
            ['view_count' => 'DESC'],
            $limit
        );
    }

    /**
     * Calcule les statistiques globales des projets
     */
    public function getProjectStats(): array
    {
        $qb = $this->projectRepository->createQueryBuilder('p');

        $totalProjects = $qb->select('COUNT(p.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $totalViews = $qb->select('SUM(p.view_count)')
            ->getQuery()
            ->getSingleScalarResult();

        $avgViews = $totalProjects > 0 ? round($totalViews / $totalProjects, 1) : 0;

        return [
            'totalProjects' => (int) $totalProjects,
            'totalViews' => (int) ($totalViews ?? 0),
            'avgViews' => $avgViews
        ];
    }
}
