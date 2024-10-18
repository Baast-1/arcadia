<?php

namespace App\Controller;

use App\Entity\Hours;
use App\Form\HoursType;
use App\Repository\HoursRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
            try {
                $entityManager->persist($hour);
                $entityManager->flush();
                $this->addFlash('success', 'Heure ajoutée avec succès.');
                return $this->redirectToRoute('app_hours_index', [], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de l\'ajout de l\'heure.');
            }
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
            try {
                $entityManager->flush();
                $this->addFlash('success', 'Heure modifiée avec succès.');
                return $this->redirectToRoute('app_hours_index', [], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la modification de l\'heure.');
            }
        }

        return $this->render('hours/edit.html.twig', [
            'hour' => $hour,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_hours_delete', methods: ['POST'])]
    public function delete(Request $request, Hours $hour, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$hour->getId(), $request->get('_token'))) {
            try {
                $entityManager->remove($hour);
                $entityManager->flush();
                $this->addFlash('success', 'Heure supprimée avec succès.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la suppression de l\'heure.');
            }
        }

        return $this->redirectToRoute('app_hours_index', [], Response::HTTP_SEE_OTHER);
    }
}