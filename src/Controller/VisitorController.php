<?php

namespace App\Controller;

use App\Repository\HabitatsRepository;
use App\Repository\ServicesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class VisitorController extends AbstractController
{
    #[Route('/', name: 'app_visitor')]
    public function index(): Response
    {
        return $this->render('visitor/index.html.twig', [
            'controller_name' => 'VisitorController',
        ]);
    }

    #[Route('/nos-services', name: 'app_visitor_services')]
    public function service(ServicesRepository $servicesRepository): Response
    {
        return $this->render('visitor/services.html.twig', [
            'services' => $servicesRepository->findAll(),
        ]);
    }

    #[Route('/nos-habitats', name: 'app_visitor_habitats')]
    public function habitats(HabitatsRepository $habitatsRepository): Response
    {
        return $this->render('visitor/habitats.html.twig', [
            'habitats' => $habitatsRepository->findAll(),
        ]);
    }

    #[Route('/contact', name: 'app_visitor_contact')]
    public function contact(): Response
    {
        return $this->render('visitor/contact.html.twig', [
            'controller_name' => 'VisitorController',
        ]);
    }
}
