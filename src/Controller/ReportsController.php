<?php

namespace App\Controller;

use App\Entity\Animals;
use App\Entity\Reports;
use App\Form\ReportsType;
use App\Repository\ReportsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/reports')]
final class ReportsController extends AbstractController
{
    #[Route(name: 'app_reports_index', methods: ['GET'])]
    public function index(ReportsRepository $reportsRepository): Response
    {
        return $this->render('reports/index.html.twig', [
            'reports' => $reportsRepository->findAll(),
        ]);
    }

    #[Route('/new/{animalId}', name: 'app_reports_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, Security $security, int $animalId): Response
    {
        $user = $security->getUser();

        $animal = $entityManager->getRepository(Animals::class)->find($animalId);
        if (!$animal) {
            throw $this->createNotFoundException('Animal non trouvé');
        }

        $report = new Reports();
        $report->setAnimal($animal);
        $report->setUser($user);

        $form = $this->createForm(ReportsType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->persist($report);
                $entityManager->flush();

                $this->addFlash('success', 'Le rapport a été créé avec succès.');

                return $this->redirectToRoute('app_animals_show', ['id' => $animalId], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la création du rapport.');
            }
        }

        return $this->render('feed/new.html.twig', [
            'feed' => $report,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_reports_show', methods: ['GET'])]
    public function show(Reports $report): Response
    {
        return $this->render('reports/show.html.twig', [
            'report' => $report,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reports_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reports $report, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReportsType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_reports_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reports/edit.html.twig', [
            'report' => $report,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reports_delete', methods: ['POST'])]
    public function delete(Request $request, Reports $report, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$report->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($report);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reports_index', [], Response::HTTP_SEE_OTHER);
    }
}
