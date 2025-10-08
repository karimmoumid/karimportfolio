<?php

namespace App\Controller;

use App\Entity\Message;
use App\Form\ContactType;
use App\Service\ContactService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(Request $request, ContactService $contactService): Response
    {
        $message = new Message();
        $form = $this->createForm(ContactType::class, $message);
        $adminEmail = $this->getParameter('app.admin_email');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // ✅ ici, pas besoin de saveMessage()
            $contactService->handleContactMessage($message);

            $this->addFlash('success',
                '✅ Votre message a été envoyé avec succès ! Je vous répondrai dans les plus brefs délais.'
            );

            return $this->redirectToRoute('app_contact');
        }

        return $this->render('contact/index.html.twig', [
            'contactForm' => $form->createView()
        ]);
    }

    #[Route('/test-email-now', name: 'test_email_now')]
    public function testEmailNow(MailerInterface $mailer): Response
    {
        try {
            $adminEmail = $this->getParameter('app.admin_email');

            // Test 1: Email Symfony direct
            $email = (new Email())
                ->from('test@portfolio.com')
                ->to($adminEmail)
                ->subject('🔥 TEST SYMFONY MAILER')
                ->html('<h1>✅ Symfony Mailer fonctionne !</h1><p>Email envoyé à ' . date('H:i:s') . '</p>');

            $mailer->send($email);

            return new Response('
                <h1>✅ Email Symfony envoyé !</h1>
                <p><strong>Vérifiez MailHog :</strong> <a href="http://localhost:8025" target="_blank">http://localhost:8025</a></p>
                <p>Si vous voyez l\'email "TEST SYMFONY MAILER", alors Symfony Mailer + MailHog fonctionne.</p>
                <p><strong>Le problème serait donc dans EmailService ou ContactService.</strong></p>
                <hr>
                <p><a href="/test-contact-service">➡️ Tester ContactService</a></p>
            ');

        } catch (\Exception $e) {
            return new Response('
                <h1>❌ ERREUR Symfony Mailer</h1>
                <p>' . htmlspecialchars($e->getMessage()) . '</p>
                <p>Fichier: ' . $e->getFile() . ' ligne ' . $e->getLine() . '</p>
            ');
        }
    }

    // ✅ AJOUTEZ CETTE MÉTHODE POUR TESTER CONTACTSERVICE
    #[Route('/test-contact-service', name: 'test_contact_service')]
    public function testContactService(ContactService $contactService): Response
    {
        try {
            // Créer un message de test
            $testMessage = new Message();
            $testMessage->setCreatedAt(new \DateTimeImmutable());
            $testMessage->setSenderEmail('test@portfolio.com');
            $testMessage->setSenderName('Test ContactService');
            $testMessage->setSubject('Test ContactService Direct');
            $testMessage->setContent('Ceci est un test direct du ContactService.');

            // ✅ Test ContactService (SANS try/catch pour voir l'erreur)
            $contactService->handleContactMessage($testMessage);

            return new Response('
                <h1>✅ ContactService exécuté !</h1>
                <p>Si vous arrivez ici sans erreur, ContactService fonctionne.</p>
                <p><strong>Vérifiez MailHog :</strong> <a href="http://localhost:8025" target="_blank">http://localhost:8025</a></p>
                <p>ID du message test: ' . $testMessage->getId() . '</p>
            ');

        } catch (\Exception $e) {
            return new Response('
                <h1>❌ ERREUR ContactService</h1>
                <p><strong>Message:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>
                <p><strong>Fichier:</strong> ' . $e->getFile() . ' ligne ' . $e->getLine() . '</p>
                <hr>
                <h3>Stack trace:</h3>
                <pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>
            ');
        }
    }

}
