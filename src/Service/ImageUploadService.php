<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Psr\Log\LoggerInterface;

class ImageUploadService
{
    private string $projectsDirectory;
    private string $skillsDirectory;

    public function __construct(
        private SluggerInterface $slugger,
        private ParameterBagInterface $params,
        private LoggerInterface $logger
    ) {
        // Récupérer les chemins depuis les paramètres ou utiliser des valeurs par défaut
        $this->projectsDirectory = $this->params->get('app.upload.projects_directory')
            ?? ($_ENV['APP_UPLOADS_PROJECTS'] ?? 'public/uploads/projects');

        $this->skillsDirectory = $this->params->get('app.upload.skills_directory')
            ?? ($_ENV['APP_UPLOADS_SKILLS'] ?? 'public/uploads/skills');

        // S'assurer que les dossiers existent
        $this->ensureDirectoryExists($this->projectsDirectory);
        $this->ensureDirectoryExists($this->skillsDirectory);
    }

    /**
     * Upload une image pour un projet
     */
    public function uploadProjectImage(UploadedFile $file): string
    {
        return $this->uploadImage($file, $this->projectsDirectory, 'project');
    }

    /**
     * Upload une image pour un skill
     */
    public function uploadSkillImage(UploadedFile $file): string
    {
        return $this->uploadImage($file, $this->skillsDirectory, 'skill');
    }

    /**
     * Upload générique d'une image
     */
    private function uploadImage(UploadedFile $file, string $directory, string $prefix = 'image'): string
    {
        try {
            // Validation basique du fichier
            $this->validateImageFile($file);

            // Générer un nom de fichier unique
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $this->slugger->slug($originalFilename);
            $fileName = $prefix . '_' . $safeFilename . '_' . uniqid() . '.' . $file->guessExtension();

            // Déplacer le fichier
            $file->move($directory, $fileName);

            // Optimiser l'image si possible
            $this->optimizeImage($directory . '/' . $fileName);

            $this->logger->info('Image uploadée avec succès', [
                'filename' => $fileName,
                'original' => $file->getClientOriginalName(),
                'size' => $file->getSize()
            ]);

            return $fileName;

        } catch (FileException $e) {
            $this->logger->error('Erreur lors de l\'upload d\'image', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName()
            ]);
            throw new \Exception('Erreur lors de l\'upload du fichier : ' . $e->getMessage());
        }
    }

    /**
     * Valide un fichier image
     */
    private function validateImageFile(UploadedFile $file): void
    {
        // Vérifier la taille (max 2MB)
        if ($file->getSize() > 2 * 1024 * 1024) {
            throw new \Exception('Le fichier est trop volumineux. Taille maximale : 2MB');
        }

        // Vérifier le type MIME
        $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/svg+xml'];
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            throw new \Exception('Type de fichier non autorisé. Formats acceptés : JPG, PNG, WebP, SVG');
        }

        // Vérifier l'extension
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
        if (!in_array($file->guessExtension(), $allowedExtensions)) {
            throw new \Exception('Extension de fichier non autorisée.');
        }
    }

    /**
     * Optimise une image (redimensionnement basique)
     */
    private function optimizeImage(string $filepath): void
    {
        // Vérifier si l'extension GD est disponible
        if (!extension_loaded('gd')) {
            return; // Skip l'optimisation si GD n'est pas disponible
        }

        $imageInfo = getimagesize($filepath);
        if (!$imageInfo) {
            return;
        }

        [$width, $height, $type] = $imageInfo;

        // Si l'image est trop grande, la redimensionner
        $maxWidth = 1200;
        $maxHeight = 800;

        if ($width <= $maxWidth && $height <= $maxHeight) {
            return; // Pas besoin de redimensionner
        }

        // Calculer les nouvelles dimensions
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = round($width * $ratio);
        $newHeight = round($height * $ratio);

        try {
            // Créer l'image source
            $sourceImage = match($type) {
                IMAGETYPE_JPEG => imagecreatefromjpeg($filepath),
                IMAGETYPE_PNG => imagecreatefrompng($filepath),
                IMAGETYPE_WEBP => imagecreatefromwebp($filepath),
                default => null
            };

            if (!$sourceImage) {
                return;
            }

            // Créer l'image redimensionnée
            $resizedImage = imagecreatetruecolor($newWidth, $newHeight);

            // Préserver la transparence pour PNG et WebP
            if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_WEBP) {
                imagealphablending($resizedImage, false);
                imagesavealpha($resizedImage, true);
            }

            // Redimensionner
            imagecopyresampled(
                $resizedImage, $sourceImage,
                0, 0, 0, 0,
                $newWidth, $newHeight, $width, $height
            );

            // Sauvegarder l'image optimisée
            match($type) {
                IMAGETYPE_JPEG => imagejpeg($resizedImage, $filepath, 85),
                IMAGETYPE_PNG => imagepng($resizedImage, $filepath, 6),
                IMAGETYPE_WEBP => imagewebp($resizedImage, $filepath, 85),
            };

            // Libérer la mémoire
            imagedestroy($sourceImage);
            imagedestroy($resizedImage);

            $this->logger->info('Image optimisée', [
                'filepath' => $filepath,
                'original_size' => $width . 'x' . $height,
                'new_size' => $newWidth . 'x' . $newHeight
            ]);

        } catch (\Exception $e) {
            $this->logger->warning('Impossible d\'optimiser l\'image', [
                'filepath' => $filepath,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Supprime une image
     */
    public function deleteImage(string $filename, string $type = 'project'): bool
    {
        $directory = $type === 'skill' ? $this->skillsDirectory : $this->projectsDirectory;
        $filepath = $directory . '/' . $filename;

        if (file_exists($filepath)) {
            try {
                unlink($filepath);
                $this->logger->info('Image supprimée', ['filepath' => $filepath]);
                return true;
            } catch (\Exception $e) {
                $this->logger->error('Erreur suppression image', [
                    'filepath' => $filepath,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return false;
    }

    /**
     * Crée un dossier s'il n'existe pas
     */
    private function ensureDirectoryExists(string $directory): void
    {
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0755, true) && !is_dir($directory)) {
                throw new \Exception('Impossible de créer le dossier : ' . $directory);
            }
        }
    }

    /**
     * Génère une miniature
     */
    public function generateThumbnail(string $filename, string $type = 'project', int $size = 300): ?string
    {
        $directory = $type === 'skill' ? $this->skillsDirectory : $this->projectsDirectory;
        $filepath = $directory . '/' . $filename;

        if (!file_exists($filepath) || !extension_loaded('gd')) {
            return null;
        }

        $thumbnailName = 'thumb_' . $size . '_' . $filename;
        $thumbnailPath = $directory . '/' . $thumbnailName;

        // Si la miniature existe déjà
        if (file_exists($thumbnailPath)) {
            return $thumbnailName;
        }

        try {
            $imageInfo = getimagesize($filepath);
            if (!$imageInfo) {
                return null;
            }

            [$width, $height, $imageType] = $imageInfo;

            // Créer l'image source
            $sourceImage = match($imageType) {
                IMAGETYPE_JPEG => imagecreatefromjpeg($filepath),
                IMAGETYPE_PNG => imagecreatefrompng($filepath),
                IMAGETYPE_WEBP => imagecreatefromwebp($filepath),
                default => null
            };

            if (!$sourceImage) {
                return null;
            }

            // Calculer les dimensions du carré centré
            $minDimension = min($width, $height);
            $x = ($width - $minDimension) / 2;
            $y = ($height - $minDimension) / 2;

            // Créer la miniature
            $thumbnail = imagecreatetruecolor($size, $size);

            // Préserver transparence
            if ($imageType === IMAGETYPE_PNG || $imageType === IMAGETYPE_WEBP) {
                imagealphablending($thumbnail, false);
                imagesavealpha($thumbnail, true);
            }

            // Redimensionner en carré
            imagecopyresampled(
                $thumbnail, $sourceImage,
                0, 0, $x, $y,
                $size, $size, $minDimension, $minDimension
            );

            // Sauvegarder
            $success = match($imageType) {
                IMAGETYPE_JPEG => imagejpeg($thumbnail, $thumbnailPath, 85),
                IMAGETYPE_PNG => imagepng($thumbnail, $thumbnailPath, 6),
                IMAGETYPE_WEBP => imagewebp($thumbnail, $thumbnailPath, 85),
                default => false
            };

            // Libérer mémoire
            imagedestroy($sourceImage);
            imagedestroy($thumbnail);

            return $success ? $thumbnailName : null;

        } catch (\Exception $e) {
            $this->logger->error('Erreur génération miniature', [
                'filepath' => $filepath,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Retourne les statistiques d'upload
     */
    public function getUploadStats(): array
    {
        $projectFiles = glob($this->projectsDirectory . '/*');
        $skillFiles = glob($this->skillsDirectory . '/*');

        $projectSize = 0;
        $skillSize = 0;

        foreach ($projectFiles as $file) {
            if (is_file($file)) {
                $projectSize += filesize($file);
            }
        }

        foreach ($skillFiles as $file) {
            if (is_file($file)) {
                $skillSize += filesize($file);
            }
        }

        return [
            'project_files' => count($projectFiles),
            'skill_files' => count($skillFiles),
            'project_size' => $this->formatBytes($projectSize),
            'skill_size' => $this->formatBytes($skillSize),
            'total_size' => $this->formatBytes($projectSize + $skillSize)
        ];
    }

    /**
     * Formate les octets en format lisible
     */
    private function formatBytes(int $size, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }

        return round($size, $precision) . ' ' . $units[$i];
    }
}
