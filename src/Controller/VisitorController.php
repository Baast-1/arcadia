<?php

namespace App\Controller;

use App\Document\Opinions;
use App\Form\ContactFormType;
use App\Form\OpinionsType;
use App\Form\OpinionsVisitorType;
use App\Repository\AnimalsRepository;
use App\Repository\HabitatsRepository;
use App\Repository\HoursRepository;
use App\Repository\ServicesRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class VisitorController extends AbstractController
{
    private $servicesRepository;
    private $habitatsRepository;
    private $animalsRepository;
    private $hoursRepository;
    private $dm;

    public function __construct(
        ServicesRepository $servicesRepository,
        HabitatsRepository $habitatsRepository,
        AnimalsRepository $animalsRepository,
        HoursRepository $hoursRepository,
        DocumentManager $dm
    ) {
        $this->servicesRepository = $servicesRepository;
        $this->habitatsRepository = $habitatsRepository;
        $this->animalsRepository = $animalsRepository;
        $this->hoursRepository = $hoursRepository;
        $this->dm = $dm;
    }

    #[Route('/', name: 'app_visitor')]
    public function index(Request $request): Response
    {
        $animals = $this->animalsRepository->findAll();
        $habitats = $this->habitatsRepository->findAll();
        $services = $this->servicesRepository->findAll();
        $hours = $this->hoursRepository->findAll();
        $opinions = $this->dm->getRepository(Opinions::class)->findAll();
        $opinion = new Opinions();
        $form = $this->createForm(OpinionsVisitorType::class, $opinion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $opinion->setIsVisible(false);
            $this->dm->persist($opinion);
            $this->dm->flush();

            return $this->redirectToRoute('app_visitor');
        }
    
        return $this->render('visitor/index.html.twig', [
            'animals' => $animals,
            'habitats' => $habitats,
            'services' => $services,
            'opinions' => $opinions,
            'hours' => $hours,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/nos-services', name: 'app_visitor_services')]
    public function service(): Response
    {
        return $this->render('visitor/services.html.twig', [
            'services' => $this->servicesRepository->findAll(),
        ]);
    }

    #[Route('/nos-habitats', name: 'app_visitor_habitats')]
    public function habitats(): Response
    {
        return $this->render('visitor/habitats.html.twig', [
            'habitats' => $this->habitatsRepository->findAll(),
        ]);
    }

    #[Route('/contact', name: 'app_visitor_contact')]
    public function contact(Request $request, MailerInterface $mailer): Response
    {
        $form = $this->createForm(ContactFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            
            // Envoi de l'e-mail
            $email = (new Email())
                ->from('noreply@croisieresburdigala.fr')
                ->replyTo($formData['email'])
                ->to('contact@croisieresburdigala.fr')
                // ->to('bastien.ter@gmail.com')
                ->subject('Demande d\'information')
                ->html("
                    <p>Qui êtes-vous : {$formData['who']}</p>
                    <p>Nom : {$formData['nom']}</p>
                    <p>Prénom : {$formData['prenom']}</p>
                    <p>Email : {$formData['email']}</p>
                    <p>Téléphone : {$formData['telephone']}</p>
                    <p>Comment voulez-vous être recontacté : " . (isset($formData['recontact']) ? implode(', ', $formData['recontact']) : 'Aucun') . "</p>
                    <p>Votre demande : {$formData['demande']}</p>
                    <p>Nombre de convives : {$formData['convives']}</p>
                    <p>Date souhaitée : {$formData['date']->format('d-m-Y')}</p>
                ");

            $mailer->send($email);

            // Redirection avec un message de confirmation
            return $this->redirectToRoute('app_contact_success');
        }

        return $this->render('Front/contact.html.twig', [
            'form' => $form->createView(), // Passer la variable 'form' au template Twig
        ]);
    }

    #[Route('/contact/success', name: 'app_contact_success')]
    public function contactSuccess(): Response
    {
        // Affichage d'un message de confirmation
        $response = new Response('Votre demande a bien été envoyée. Vous allez être automatiquement redirigé.');
    
        // Ajout du script JavaScript pour la redirection après 4 secondes
        $response->headers->set('refresh', '4;url=' . $this->generateUrl('app_contact'));
    
        return $response;
    }

    #[Route('/horaires', name: 'app_visitor_hours')]
    public function hours(): Response
    {
        return $this->render('visitor/hours.html.twig');
    }
}
