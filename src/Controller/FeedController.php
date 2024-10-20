<?php

namespace App\Controller;

use App\Entity\Animals;
use App\Entity\Feed;
use App\Form\FeedType;
use App\Repository\FeedRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/feed')]
final class FeedController extends AbstractController
{
    #[Route( '/{animalId}', name: 'app_feed_index', methods: ['GET'])]
    public function index(FeedRepository $feedRepository, EntityManagerInterface $entityManager, int $animalId): Response
    {
        $animal = $entityManager->getRepository(Animals::class)->find($animalId);
        if (!$animal) {
            throw $this->createNotFoundException('Animal non trouvé');
        }
        $feeds = $feedRepository->findBy(['animal' => $animal]);

        return $this->render('feed/index.html.twig', [
            'feeds' => $feeds,
            'animal' => $animal,
        ]);
    }

    #[Route('/new/{animalId}', name: 'app_feed_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, int $animalId): Response
    {
        $animal = $entityManager->getRepository(Animals::class)->find($animalId);
        if (!$animal) {
            throw $this->createNotFoundException('Animal non trouvé');
        }
    
        $feed = new Feed();
        $feed->setAnimal($animal);
    
        $form = $this->createForm(FeedType::class, $feed);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->persist($feed);
                $entityManager->flush();
    
                $this->addFlash('success', 'Le feed a été créé avec succès.');
    
                return $this->redirectToRoute('app_animals_show', ['id' => $animalId], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la création du feed.');
            }
        }
    
        return $this->render('feed/new.html.twig', [
            'feed' => $feed,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_feed_show', methods: ['GET'])]
    public function show(Feed $feed): Response
    {
        return $this->render('feed/show.html.twig', [
            'feed' => $feed,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_feed_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Feed $feed, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FeedType::class, $feed);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_feed_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('feed/edit.html.twig', [
            'feed' => $feed,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_feed_delete', methods: ['POST'])]
    public function delete(Request $request, Feed $feed, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$feed->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($feed);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_feed_index', [], Response::HTTP_SEE_OTHER);
    }
}
