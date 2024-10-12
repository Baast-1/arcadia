<?php

namespace App\Controller;

use App\Document\Opinions;
use App\Form\ContactFormType;
use App\Form\OpinionsVisitorType;
use App\Repository\AnimalsRepository;
use App\Repository\HabitatsRepository;
use App\Repository\HoursRepository;
use App\Repository\ServicesRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
    private $entityManager;

    public function __construct(
        ServicesRepository $servicesRepository,
        HabitatsRepository $habitatsRepository,
        AnimalsRepository $animalsRepository,
        HoursRepository $hoursRepository,
        DocumentManager $dm,
        EntityManagerInterface $entityManager
        
    ) {
        $this->servicesRepository = $servicesRepository;
        $this->habitatsRepository = $habitatsRepository;
        $this->animalsRepository = $animalsRepository;
        $this->hoursRepository = $hoursRepository;
        $this->dm = $dm;
        $this->entityManager = $entityManager;
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
        $habitats = $this->habitatsRepository->findAll();
        
        $habitatsArray = array_map(function ($habitat) {
            return $habitat->toArray();
        }, $habitats);
        
        return $this->render('visitor/habitats.html.twig', [
            'habitats' => $habitatsArray,
        ]);
    }

    #[Route('/view/{id}', name: 'app_view', methods: ['POST'])]
    public function incrementView(int $id): JsonResponse
    {
        try {
            $animal = $this->animalsRepository->find($id);
            
            if (!$animal) {
                return new JsonResponse(['error' => 'Animal not found'], 404);
            }
            if ($animal->getView() === null) {
                $animal->setView(0);
            }
            $animal->setView($animal->getView() + 1);
            $this->entityManager->flush();

            return new JsonResponse(['view' => $animal->getView()]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Internal Server Error', 'message' => $e->getMessage()], 500);
        }
    }


    #[Route('/contact', name: 'app_visitor_contact')]
    public function contact(Request $request, MailerInterface $mailer): Response
    {
        $form = $this->createForm(ContactFormType::class);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
    
            // Création et envoi de l'e-mail
            $email = (new Email())
                ->from('votre-email@example.com')
                ->replyTo($formData['email'])
                ->to('destinataire@example.com')
                ->subject('Demande de Contact')
                ->html("
                    <p>Nom : {$formData['name']}</p>
                    <p>Email : {$formData['email']}</p>
                    <p>Message : {$formData['message']}</p>
                ");
    
            try {
                $mailer->send($email);
                $this->addFlash('success', 'Votre message a été envoyé avec succès.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi de votre message.');
            }
    
            // Redirection vers une page de succès
            return $this->redirectToRoute('app_visitor_contact');
        }
    
        return $this->render('visitor/contact.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/horaires', name: 'app_visitor_hours')]
    public function hours(): Response
    {
        return $this->render('visitor/hours.html.twig');
    }
}
