<?php

namespace App\Controller;

use App\Entity\Animals;
use App\Entity\Pictures;
use App\Form\AnimalsType;
use App\Repository\AnimalsRepository;
use App\Repository\FeedRepository;
use App\Repository\PicturesRepository;
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
                $animal->setView(0);
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
    public function show(Animals $animal, FeedRepository $feedRepository): Response
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
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_animals_delete', methods: ['POST'])]
    public function delete(Request $request, Animals $animal, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$animal->getId(), $request->request->get('_token'))) {
            try {
                foreach ($animal->getPictures() as $picture) {
                    $picturePath = $this->getParameter('pictures_directory').'/'.$picture->getFilename();
                    if (file_exists($picturePath)) {
                        unlink($picturePath);
                    }
                    $entityManager->remove($picture);
                }

                foreach ($animal->getReports() as $report) {
                    $entityManager->remove($report);
                }
    
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

    #[Route('/{id}/delete-picture/{pictureId}', name: 'app_animals_delete_picture', methods: ['POST'])]
    public function deletePicture(Request $request, Animals $animal, $pictureId, PicturesRepository $picturesRepository, EntityManagerInterface $entityManager): Response
    {
        $picture = $picturesRepository->find($pictureId);
        if (!$picture) {
            $this->addFlash('error', 'Image non trouvée.');
            return $this->redirectToRoute('app_animals_edit', ['id' => $animal->getId()], Response::HTTP_SEE_OTHER);
        }

        if ($this->isCsrfTokenValid('delete'.$picture->getId(), $request->request->get('_token'))) {
            try {
                $picturePath = $this->getParameter('pictures_directory').'/'.$picture->getFilename();
                if (file_exists($picturePath)) {
                    unlink($picturePath);
                }
                $entityManager->remove($picture);
                $entityManager->flush();

                $this->addFlash('success', 'Image supprimée avec succès !');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la suppression de l\'image.');
            }
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('app_animals_show', ['id' => $animal->getId()], Response::HTTP_SEE_OTHER);
    }

}
