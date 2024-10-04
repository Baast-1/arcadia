<?php

namespace App\Controller;

use App\Entity\Hours;
use App\Form\HoursType;
use App\Repository\HoursRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/hours')]
final class HoursController extends AbstractController
{
    #[Route(name: 'app_hours_index', methods: ['GET'])]
    public function index(HoursRepository $hoursRepository): Response
    {
        return $this->render('hours/index.html.twig', [
            'hours' => $hoursRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_hours_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $hour = new Hours();
        $form = $this->createForm(HoursType::class, $hour);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($hour);
            $entityManager->flush();

            return $this->redirectToRoute('app_hours_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('hours/new.html.twig', [
            'hour' => $hour,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_hours_show', methods: ['GET'])]
    public function show(Hours $hour): Response
    {
        return $this->render('hours/show.html.twig', [
            'hour' => $hour,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_hours_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Hours $hour, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(HoursType::class, $hour);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_hours_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('hours/edit.html.twig', [
            'hour' => $hour,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_hours_delete', methods: ['POST'])]
    public function delete(Request $request, Hours $hour, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$hour->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($hour);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_hours_index', [], Response::HTTP_SEE_OTHER);
    }
}
