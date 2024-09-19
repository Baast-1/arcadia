<?php

namespace App\Controller;

use App\Entity\Animals;
use App\Entity\Pictures;
use App\Form\AnimalsType;
use App\Repository\AnimalsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/animals')]
final class AnimalsController extends AbstractController
{
    #[Route(name: 'app_animals_index', methods: ['GET'])]
    public function index(AnimalsRepository $animalsRepository): Response
    {
        return $this->render('animals/index.html.twig', [
            'animals' => $animalsRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_animals_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $animal = new Animals();
        $form = $this->createForm(AnimalsType::class, $animal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->persist($animal);
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
                        $picture->setFilename($fileName);
                        $picture->setAnimals($animal);

                        $entityManager->persist($picture);
                    }
                    $entityManager->flush();
                }

                $this->addFlash('success', 'Animal créé avec succès !');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la création de l\'animal.');
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_animals_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('animals/new.html.twig', [
            'animal' => $animal,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_animals_show', methods: ['GET'])]
    public function show(Animals $animal): Response
    {
        return $this->render('animals/show.html.twig', [
            'animal' => $animal,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_animals_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Animals $animal, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AnimalsType::class, $animal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $pictureFiles = $form->get('picture')->getData();
                if ($pictureFiles) {
                    foreach ($animal->getPictures() as $oldPicture) {
                        $oldPicturePath = $this->getParameter('pictures_directory').'/'.$oldPicture->getFilename();
                        if (file_exists($oldPicturePath)) {
                            unlink($oldPicturePath);
                        }
                        $entityManager->remove($oldPicture);
                    }

                    foreach ($pictureFiles as $pictureFile) {
                        $fileName = uniqid().'.'.$pictureFile->guessExtension();
                        $pictureFile->move(
                            $this->getParameter('pictures_directory'),
                            $fileName
                        );

                        $picture = new Pictures();
                        $picture->setFilename($fileName);
                        $picture->setAnimals($animal);

                        $entityManager->persist($picture);
                    }
                }

                $entityManager->flush();

                $this->addFlash('success', 'Animal modifié avec succès !');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la modification de l\'animal.');
            }

            return $this->redirectToRoute('app_animals_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('animals/edit.html.twig', [
            'animal' => $animal,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_animals_delete', methods: ['POST'])]
    public function delete(Request $request, Animals $animal, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$animal->getId(), $request->request->get('_token'))) {
            try {
                // Suppression des images associées
                foreach ($animal->getPictures() as $picture) {
                    $picturePath = $this->getParameter('pictures_directory').'/'.$picture->getFilename();
                    if (file_exists($picturePath)) {
                        unlink($picturePath);
                    }
                    $entityManager->remove($picture);
                }

                // Suppression de l'animal
                $entityManager->remove($animal);
                $entityManager->flush();

                $this->addFlash('success', 'Animal supprimé avec succès !');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la suppression de l\'animal.');
            }
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('app_animals_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/delete-picture/{pictureId}', name: 'app_services_delete_picture', methods: ['GET'])]
    public function deletePicture(Request $request,  Animals $animal, AnimalsRepository $animalsRepository, int $pictureId, EntityManagerInterface $entityManager): Response
    {
        try {
            $picture = $animalsRepository->find($pictureId);
            if (!$picture) {
                throw $this->createNotFoundException('Aucune image trouvée pour l\'ID '.$pictureId);
            }

            $entityManager->remove($picture);
            $entityManager->flush();

            $this->addFlash('success', 'Image supprimée avec succès !');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la suppression de l\'image.');
        }

        return $this->redirectToRoute('app_services_edit', ['id' => $animal->getId()]);
    }
}
