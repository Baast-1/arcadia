<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class UserControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;
    private $userRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->userRepository = static::getContainer()->get(UserRepository::class);
    }

    public function testIndexPage(): void
    {
        $this->client->request('GET', '/user');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Users');
    }

    public function testCreateNewUser(): void
    {
        $crawler = $this->client->request('GET', '/user/new');

        // Vérifier que la page est accessible
        $this->assertResponseIsSuccessful();

        // Simuler l'envoi du formulaire
        $form = $crawler->selectButton('Create')->form([
            'user[email]' => 'test@example.com',
            'user[roles]' => 'ROLE_USER'
        ]);

        $this->client->submit($form);

        // Vérifier la redirection après la création
        $this->assertResponseRedirects('/user');

        // Vérifier que l'utilisateur a été créé dans la base de données
        $user = $this->userRepository->findOneByEmail('test@example.com');
        $this->assertNotNull($user);
        $this->assertSame('test@example.com', $user->getEmail());
    }

    public function testEditUser(): void
    {
        // Créer un utilisateur de test
        $user = new User();
        $user->setEmail('testedit@example.com');
        $user->setRoles(['ROLE_USER']);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Récupérer l'utilisateur et accéder à la page d'édition
        $crawler = $this->client->request('GET', '/user/' . $user->getId() . '/edit');

        $this->assertResponseIsSuccessful();

        // Simuler la modification de l'utilisateur
        $form = $crawler->selectButton('Save')->form([
            'user[email]' => 'updatedemail@example.com',
            'user[roles]' => 'ROLE_ADMIN'
        ]);

        $this->client->submit($form);

        // Vérifier la redirection après la modification
        $this->assertResponseRedirects('/user');

        // Vérifier que l'utilisateur a été mis à jour
        $updatedUser = $this->userRepository->findOneByEmail('updatedemail@example.com');
        $this->assertNotNull($updatedUser);
        $this->assertSame('updatedemail@example.com', $updatedUser->getEmail());
        $this->assertSame(['ROLE_ADMIN'], $updatedUser->getRoles());
    }

    public function testDeleteUser(): void
    {
        // Créer un utilisateur de test
        $user = new User();
        $user->setEmail('testdelete@example.com');
        $user->setRoles(['ROLE_USER']);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Simuler la suppression de l'utilisateur
        $this->client->request('POST', '/user/' . $user->getId(), [
            '_token' => $this->client->getContainer()->get('security.csrf.token_manager')->getToken('delete' . $user->getId()),
        ]);

        // Vérifier la redirection après la suppression
        $this->assertResponseRedirects('/user');

        // Vérifier que l'utilisateur a bien été supprimé
        $deletedUser = $this->userRepository->findOneByEmail('testdelete@example.com');
        $this->assertNull($deletedUser);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
