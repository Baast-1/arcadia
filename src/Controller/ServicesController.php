<?php

namespace App\Controller;

use App\Entity\Pictures;
use App\Entity\Services;
use App\Form\ServicesType;
use App\Repository\PicturesRepository;
use App\Repository\ServicesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/services')]
final class ServicesController extends AbstractController
{
    #[Route(name: 'app_services_index', methods: ['GET'])]
    public function index(ServicesRepository $servicesRepository): Response
    {
        return $this->render('services/index.html.twig', [
            'services' => $servicesRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_services_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $service = new Services();
        $form = $this->createForm(ServicesType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->persist($service);
                $entityManager->flush();

                $pictureFiles = $form->get('picture')->getData();
                if ($pictureFiles) {
                    foreach ($pictureFiles as $pictureFile) {
                        $fileName = uniqid().'.'.$pictureFile->guessExtension();
                        $pictureFile->move(
                            $this->getParameter('pictures_directory'),
                            $fileName
                        );

                        $picture = new Pictures();
                        $picture->setFile($fileName);
                        $picture->setServices($service);

                        $entityManager->persist($picture);
                    }
                    $entityManager->flush();
                }

                $this->addFlash('success', 'Service créé avec succès !');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la création du service.');
            }

            return $this->redirectToRoute('app_services_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('services/new.html.twig', [
            'service' => $service,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_services_show', methods: ['GET'])]
    public function show(Services $service): Response
    {
        return $this->render('services/show.html.twig', [
            'service' => $service,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_services_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Services $service, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ServicesType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->flush();

                $pictureFiles = $form->get('picture')->getData();
                if ($pictureFiles) {
                    foreach ($pictureFiles as $pictureFile) {
                        $fileName = uniqid().'.'.$pictureFile->guessExtension();
                        $pictureFile->move(
                            $this->getParameter('pictures_directory'),
                            $fileName
                        );

                        $picture = new Pictures();
                        $picture->setFile($fileName);
                        $picture->setServices($service);

                        $entityManager->persist($picture);
                    }
                    $entityManager->flush();
                }

                $this->addFlash('success', 'Service mis à jour avec succès !');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la mise à jour du service.');
            }

            return $this->redirectToRoute('app_services_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('services/edit.html.twig', [
            'service' => $service,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_services_delete', methods: ['POST'])]
    public function delete(Request $request, Services $service, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$service->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($service);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_services_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/delete-picture/{pictureId}', name: 'app_services_delete_picture', methods: ['GET'])]
    public function deletePicture(Request $request, Services $service, PicturesRepository $picturesRepository, int $pictureId, EntityManagerInterface $entityManager): Response
    {
        try {
            $picture = $picturesRepository->find($pictureId);
            if (!$picture) {
                throw $this->createNotFoundException('Aucune image trouvée pour l\'ID '.$pictureId);
            }

            $entityManager->remove($picture);
            $entityManager->flush();

            $this->addFlash('success', 'Image supprimée avec succès !');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la suppression de l\'image.');
        }

        return $this->redirectToRoute('app_services_edit', ['id' => $service->getId()]);
    }
}
