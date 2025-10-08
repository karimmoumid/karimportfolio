<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class EmailService
{
    // Propriété pour stocker l'instance de MailerInterface
    private MailerInterface $mailer;

    // Constructeur pour injecter le service MailerInterface
    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    // Méthode pour envoyer un email
    public function sender(string $from, string $to, string $subject, string $template, array $context = [])
    {
        // Création d'une nouvelle instance de TemplatedEmail
        $email = (new TemplatedEmail())
            ->from($from) // Définit l'expéditeur de l'email
            ->to($to) // Définit le destinataire de l'email
            ->subject($subject) // Définit le sujet de l'email
            ->htmlTemplate('emails/' . $template . '.html.twig') // Définit le template Twig pour le corps de l'email
            ->context($context) // Définit le contexte pour le template Twig
        ;

        // Envoi de l'email
        $this->mailer->send($email);
    }
}
