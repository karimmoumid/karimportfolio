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
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $contactService->handleContactMessage($message);

                $this->addFlash('success',
                    '✅ Votre message a été envoyé avec succès ! Je vous répondrai dans les plus brefs délais.'
                );

                return $this->redirectToRoute('app_contact');
            } catch (\Exception $e) {
                $this->addFlash('error',
                    '❌ Erreur lors de l\'envoi du message. Veuillez réessayer.'
                );
            }
        }

        return $this->render('pages/contact.html.twig', [
            'contactForm' => $form->createView()
        ]);
    }
}
