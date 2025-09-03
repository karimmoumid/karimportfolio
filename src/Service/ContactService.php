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
        private MailerInterface $mailer,
        private ParameterBagInterface $params,
        private LoggerInterface $logger
    ) {}

    /**
     * Traite un message de contact : sauvegarde + envoi email
     */
    public function handleContactMessage(Message $message): void
    {
        try {
            // Sauvegarder le message en base de données
            $this->saveMessage($message);

            // Envoyer une notification email à l'admin
            $this->sendNotificationToAdmin($message);

            // Envoyer un accusé de réception au client
            $this->sendConfirmationToSender($message);

            $this->logger->info('Message de contact traité avec succès', [
                'sender_email' => $message->getSenderEmail(),
                'subject' => $message->getSubject()
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Erreur lors du traitement du message de contact', [
                'error' => $e->getMessage(),
                'sender_email' => $message->getSenderEmail()
            ]);
            throw $e;
        }
    }

    /**
     * Sauvegarde le message en base de données
     */
    private function saveMessage(Message $message): void
    {
        $this->em->persist($message);
        $this->em->flush();
    }

    /**
     * Envoie une notification à l'admin
     */
    private function sendNotificationToAdmin(Message $message): void
    {
        $adminEmail = $this->params->get('app.admin_email') ?? 'karimmoumid@gmail.com';

        $email = (new Email())
            ->from('noreply@portfolio.com')
            ->to($adminEmail)
            ->subject('🔥 Nouveau message de contact - ' . $message->getSubject())
            ->html($this->getAdminNotificationTemplate($message))
            ->priority(Email::PRIORITY_HIGH);

        $this->mailer->send($email);
    }

    /**
     * Envoie un accusé de réception au client
     */
    private function sendConfirmationToSender(Message $message): void
    {
        $email = (new Email())
            ->from('noreply@portfolio.com')
            ->to($message->getSenderEmail())
            ->subject('✅ Accusé de réception - ' . $message->getSubject())
            ->html($this->getConfirmationTemplate($message));

        $this->mailer->send($email);
    }

    /**
     * Template email pour la notification admin
     */
    private function getAdminNotificationTemplate(Message $message): string
    {
        return sprintf('
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #007bff; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
                    .content { background: #f8f9fa; padding: 20px; border-radius: 0 0 5px 5px; }
                    .info-row { margin: 10px 0; padding: 10px; background: white; border-radius: 3px; }
                    .label { font-weight: bold; color: #007bff; }
                    .message-content { background: #e9ecef; padding: 15px; border-radius: 5px; margin: 15px 0; }
                    .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h2>🔥 Nouveau message de contact</h2>
                    </div>
                    <div class="content">
                        <div class="info-row">
                            <span class="label">De :</span> %s (%s)
                        </div>
                        <div class="info-row">
                            <span class="label">Entreprise :</span> %s
                        </div>
                        <div class="info-row">
                            <span class="label">Sujet :</span> %s
                        </div>
                        <div class="info-row">
                            <span class="label">Date :</span> %s
                        </div>

                        <div class="message-content">
                            <div class="label">Message :</div>
                            <p>%s</p>
                        </div>
                    </div>
                    <div class="footer">
                        <p>Message reçu via le formulaire de contact du portfolio</p>
                    </div>
                </div>
            </body>
            </html>
        ',
            htmlspecialchars($message->getSenderName()),
            htmlspecialchars($message->getSenderEmail()),
            htmlspecialchars($message->getCompany() ?? 'Non renseignée'),
            htmlspecialchars($message->getSubject()),
            $message->getCreatedAt()->format('d/m/Y à H:i'),
            nl2br(htmlspecialchars($message->getContent()))
        );
    }

    /**
     * Template email pour l'accusé de réception
     */
    private function getConfirmationTemplate(Message $message): string
    {
        return sprintf('
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #28a745; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
                    .content { background: #f8f9fa; padding: 20px; border-radius: 0 0 5px 5px; }
                    .highlight { background: #e9ecef; padding: 15px; border-radius: 5px; margin: 15px 0; }
                    .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h2>✅ Message bien reçu !</h2>
                    </div>
                    <div class="content">
                        <p>Bonjour <strong>%s</strong>,</p>

                        <p>Merci pour votre message concernant : <strong>%s</strong></p>

                        <div class="highlight">
                            <p>Votre message a été reçu le <strong>%s</strong> et je m\'efforce de répondre à tous les messages dans les <strong>24 heures</strong>.</p>
                        </div>

                        <p>En attendant, n\'hésitez pas à :</p>
                        <ul>
                            <li>Consulter mes projets sur mon portfolio</li>
                            <li>Me suivre sur <a href="https://github.com/karimmoumid">GitHub</a> ou <a href="https://www.linkedin.com/in/karim-moumid-0a0312104/">LinkedIn</a></li>
                            <li>M\'appeler directement au +33 7 51 95 33 39</li>
                        </ul>

                        <p>À très bientôt !</p>
                        <p><strong>Karim MOUMID</strong><br>
                        Développeur Web Full-Stack</p>
                    </div>
                    <div class="footer">
                        <p>Ceci est un message automatique, merci de ne pas y répondre.</p>
                    </div>
                </div>
            </body>
            </html>
        ',
            htmlspecialchars($message->getSenderName()),
            htmlspecialchars($message->getSubject()),
            $message->getCreatedAt()->format('d/m/Y à H:i')
        );
    }

    /**
     * Marque un message comme lu
     */
    public function markAsRead(Message $message): void
    {
        if (!$message->getReadAt()) {
            $message->setReadAt(new \DateTime());
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
            ->setParameter('startOfMonth', new \DateTime('first day of this month'))
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
