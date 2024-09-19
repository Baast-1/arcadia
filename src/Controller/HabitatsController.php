<?php

namespace App\Controller;

use App\Entity\Habitats;
use App\Entity\Pictures;
use App\Form\HabitatsType;
use App\Repository\HabitatsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/habitats')]
final class HabitatsController extends AbstractController
{
    #[Route(name: 'app_habitats_index', methods: ['GET'])]
    public function index(HabitatsRepository $habitatsRepository): Response
    {
        return $this->render('habitats/index.html.twig', [
            'habitats' => $habitatsRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_habitats_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $habitat = new Habitats();
        $form = $this->createForm(HabitatsType::class, $habitat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->persist($habitat);
                $entityManager->flush();

                $pictureFile = $form->get('picture')->getData();
                if ($pictureFile) {
                    $fileName = uniqid().'.'.$pictureFile->guessExtension();
                    $pictureFile->move(
                        $this->getParameter('pictures_directory'),
                        $fileName
                    );

                    $picture = new Pictures();
                    $picture->setFilename($fileName);
                    $picture->setHabitats($habitat);

                    $entityManager->persist($picture);
                    $entityManager->flush();
                }

                $this->addFlash('success', 'Habitat créé avec succès !');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la création de l\'habitat.');
            }

            return $this->redirectToRoute('app_habitats_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('habitats/new.html.twig', [
            'habitat' => $habitat,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_habitats_show', methods: ['GET'])]
    public function show(Habitats $habitat): Response
    {
        return $this->render('habitats/show.html.twig', [
            'habitat' => $habitat,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_habitats_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Habitats $habitat, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(HabitatsType::class, $habitat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->flush();
            
                $pictureFile = $form->get('picture')->getData();
                if ($pictureFile) {
                    // Suppression de l'ancienne image si elle existe
                    $oldPicture = $habitat->getPicture();
                    if ($oldPicture) {
                        $oldPicturePath = $this->getParameter('pictures_directory').'/'.$oldPicture->getFilename();
                        if (file_exists($oldPicturePath)) {
                            unlink($oldPicturePath);
                        }
                    }
            
                    // Upload de la nouvelle image
                    $fileName = uniqid().'.'.$pictureFile->guessExtension();
                    $pictureFile->move(
                        $this->getParameter('pictures_directory'),
                        $fileName
                    );
            
                    if ($oldPicture) {
                        // Mise à jour de l'image existante
                        $oldPicture->setFilename($fileName);
                        $entityManager->persist($oldPicture);
                    } else {
                        // Création d'une nouvelle image
                        $picture = new Pictures();
                        $picture->setFilename($fileName);
                        $picture->setHabitats($habitat);
                        $entityManager->persist($picture);
                    }
            
                    $entityManager->flush();
                }
            
                $this->addFlash('success', 'Habitat mis à jour avec succès !');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la mise à jour de l\'habitat : ');
            }

            return $this->redirectToRoute('app_habitats_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('habitats/edit.html.twig', [
            'habitat' => $habitat,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_habitats_delete', methods: ['POST'])]
    public function delete(Request $request, Habitats $habitat, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$habitat->getId(), $request->request->get('_token'))) {
            try {
                $picture = $habitat->getPicture();
                if ($picture) {
                    $picturePath = $this->getParameter('pictures_directory').'/'.$picture->getFilename();
                    if (file_exists($picturePath)) {
                        unlink($picturePath);
                    }
                }
        
                $entityManager->remove($habitat);
                $entityManager->flush();
        
                $this->addFlash('success', 'Habitat supprimé avec succès !');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la suppression de l\'habitat.');
            }
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('app_habitats_index', [], Response::HTTP_SEE_OTHER);
    }
}
