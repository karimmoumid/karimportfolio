<?php
namespace App\Service;

use App\Entity\Message;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Psr\Log\LoggerInterface;

class ContactService
{
    public function __construct(
        private EntityManagerInterface $em,
        private EmailService $emailService, // <-- ici on injecte EmailService
        private ParameterBagInterface $params,
        private LoggerInterface $logger
    ) {}

    /**
     * Traite un message de contact : sauvegarde + envoi email
     */
    public function handleContactMessage(Message $message): void
    {
        // Sauvegarde du message en base de données
        $this->em->persist($message);
        $this->em->flush();

        // Tentative d'envoi email à l'admin
        try {
            $this->sendNotificationToAdmin($message);
        } catch (\Exception $e) {
            $this->logger->error('Erreur email admin', [
                'error' => $e->getMessage(),
                'sender_email' => $message->getSenderEmail()
            ]);
        }

        // Tentative d'envoi d'accusé de réception au client
        try {
            $this->sendConfirmationToSender($message);
        } catch (\Exception $e) {
            $this->logger->error('Erreur email client', [
                'error' => $e->getMessage(),
                'sender_email' => $message->getSenderEmail()
            ]);
        }

        $this->logger->info('Message de contact traité', [
            'sender_email' => $message->getSenderEmail(),
            'subject' => $message->getSubject()
        ]);
    }




    /**
     * Envoie une notification à l'admin
     */
    private function sendNotificationToAdmin(Message $message): void
    {
        $adminEmail = $this->params->get('app.admin_email') ?? 'karimmoumid@gmail.com';

        try {
            $this->emailService->sender(
                'noreply@example.com',
                $adminEmail,
                '🔥 Nouveau message de contact - ' . $message->getSubject(),
                'admin_notification', // nom du template Twig dans /templates/emails/admin_notification.html.twig
                [
                    'message' => $message
                ]
            );

            $this->logger->info('Email admin envoyé avec succès', [
                'to' => $adminEmail,
                'subject' => $message->getSubject()
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Erreur envoi email admin', [
                'to' => $adminEmail,
                'subject' => $message->getSubject(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    private function sendConfirmationToSender(Message $message): void
    {
        try {
            $this->emailService->sender(
                'noreply@example.com',
                $message->getSenderEmail(),
                '✅ Accusé de réception - ' . $message->getSubject(),
                'client_confirmation', // nom du template Twig dans /templates/emails/client_confirmation.html.twig
                [
                    'message' => $message
                ]
            );

            $this->logger->info('Email de confirmation envoyé avec succès', [
                'to' => $message->getSenderEmail(),
                'subject' => $message->getSubject()
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Erreur envoi email confirmation', [
                'to' => $message->getSenderEmail(),
                'subject' => $message->getSubject(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }




    /**
     * Template email pour la notification admin
     */
    /**
     * Marque un message comme lu
     */
    public function markAsRead(Message $message): void
    {
        if (!$message->getReadAt()) {
            $message->setReadAt(new \DateTimeImmutable());
            $this->em->flush();
        }
    }

    /**
     * Compte les messages non lus
     */
    public function getUnreadMessagesCount(): int
    {
        return $this->em->getRepository(Message::class)
            ->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.readAt IS NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Statistiques des messages
     */
    public function getContactStats(): array
    {
        $repo = $this->em->getRepository(Message::class);

        $qb = $repo->createQueryBuilder('m');

        $totalMessages = $qb->select('COUNT(m.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $unreadMessages = $qb->select('COUNT(m.id)')
            ->where('m.readAt IS NULL')
            ->getQuery()
            ->getSingleScalarResult();

        // Messages du mois
        $thisMonth = $qb->select('COUNT(m.id)')
            ->where('m.createdAt >= :startOfMonth')
            ->setParameter('startOfMonth', new \DateTimeImmutable('first day of this month'))
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'totalMessages' => (int) $totalMessages,
            'unreadMessages' => (int) $unreadMessages,
            'thisMonth' => (int) $thisMonth,
            'readPercentage' => $totalMessages > 0 ?
                round((($totalMessages - $unreadMessages) / $totalMessages) * 100, 1) : 0
        ];
    }
}
