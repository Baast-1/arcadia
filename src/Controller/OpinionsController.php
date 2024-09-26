<?php

namespace App\Controller;

use App\Document\Opinions;
use App\Form\OpinionsType;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

class OpinionsController extends AbstractController
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[Route('/fetch-opinions', name: 'app_fetch-opinions', methods: ['GET'])]
    public function index(DocumentManager $dm): JsonResponse
    {
        $this->logger->info('Fetching opinions from MongoDB');

        $opinions = $dm->getRepository(Opinions::class)->findAll();

        if (empty($opinions)) {
            $this->logger->warning('No opinions found in the database');
            return $this->json(['status' => 'No opinions found!'], Response::HTTP_NOT_FOUND);
        }

        $this->logger->info('Opinions found: ' . count($opinions));
        
        // Format opinions for JSON response
        $data = [];
        foreach ($opinions as $opinion) {
            $data[] = [
                'id' => (string) $opinion->getId(),
                'pseudo' => $opinion->getPseudo(),
                'description' => $opinion->getDescription(),
                'visible' => $opinion->isVisible(),
            ];
        }

        return $this->json($data);
    }

    #[Route('/opinions', name: 'app_opinions_index', methods: ['GET'])]
    public function showOpinions(DocumentManager $dm): Response
    {
        $opinions = $dm->getRepository(Opinions::class)->findAll();

        return $this->render('opinions/index.html.twig', [
            'opinions' => $opinions,
        ]);
    }

    #[Route('/opinions/new', name: 'app_opinions_new', methods: ['GET', 'POST'])]
    public function new(Request $request, DocumentManager $dm): Response
    {
        $opinion = new Opinions();
        $form = $this->createForm(OpinionsType::class, $opinion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dm->persist($opinion);
            $dm->flush();

            return $this->redirectToRoute('app_opinions_index');
        }

        return $this->render('opinions/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/opinions/{id}', name: 'app_opinions_show', methods: ['GET'])]
    public function read(string $id, DocumentManager $dm): JsonResponse
    {
        $opinion = $dm->getRepository(Opinions::class)->find($id);

        if (!$opinion) {
            return new JsonResponse(['status' => 'Opinion not found!'], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'id' => (string) $opinion->getId(),
            'pseudo' => $opinion->getPseudo(),
            'description' => $opinion->getDescription(),
            'visible' => $opinion->isVisible(),
        ]);
    }

    #[Route('/opinions/{id}/edit', name: 'app_opinions_edit', methods: ['GET', 'POST'])]
    public function editForm(Request $request, string $id, DocumentManager $dm): Response
    {
        $opinion = $dm->getRepository(Opinions::class)->find($id);
    
        if (!$opinion) {
            return $this->redirectToRoute('app_opinions_index');
        }
    
        $form = $this->createForm(OpinionsType::class, $opinion);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $dm->flush();
            return $this->redirectToRoute('app_opinions_index');
        }
    
        return $this->render('opinions/edit.html.twig', [
            'form' => $form->createView(),
            'opinion' => $opinion,
        ]);
    }

    #[Route('/opinions/{id}/delete', name: 'app_opinions_delete', methods: ['POST'])]
    public function delete(Request $request, string $id, DocumentManager $dm): Response
    {
        $opinion = $dm->getRepository(Opinions::class)->find($id);
    
        if (!$opinion) {
            return $this->redirectToRoute('app_opinions_index');
        }
    
        if ($this->isCsrfTokenValid('delete'.$opinion->getId(), $request->request->get('_token'))) {
            $dm->remove($opinion);
            $dm->flush();
        }
    
        return $this->redirectToRoute('app_opinions_index');
    }
}
