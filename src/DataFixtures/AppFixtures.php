<?php

namespace App\DataFixtures;

use App\Entity\Tags;
use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AppFixtures extends Fixture
{
    public function __construct (
        private readonly UserPasswordHasherInterface $hasher
    ){

    }
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        $this->loadUsers($manager);
        $manager->flush();
    }

    private function  loadUsers(ObjectManager $manager): void 
    {
        foreach($this->getUsersData() as [$fullname, $username, $password, $email, $roles])
        {
            $user = (new Users())
            ->setFullName($fullname)
            ->setUsername($username)
            ->setEmail($email)
            ->setRoles($roles);

            $user->setPassword($this->hasher->hashPassword($user,$password));
            $manager->persist($user);
            $this->addReference($username, $user);
        }

        $manager->flush();
    }

    private function loadTags(ObjectManager $manager): void
    {
      foreach($this->getTagsData() as $name) {
        $tag = new Tags($name);
        $manager->persist($tag);
        $this->addReference("tag-$name", $tag);
      }
}

     private function loadPosts(ObjectManager $manager): void
    {

    }
/**
 * @return array<array{string, string, string, string, array<string>}>
 */
    private function getUsersData(): array 
    {
        return [
            // $userData = [$fullName, $Uername, $password, $email, $role];
            ['Jane Doe', 'jane_admin', 'kitten','jane_admin@symfony.com',['ROLE_ADMIN']],
            ['Tom Doe', 'tom_admin', 'kitten','tom_admin@symfony.com',['ROLE_ADMIN']],
            ['John Doe', 'john_admin', 'kitten','john_user@symfony.com',['ROLE_USER']]
        ];
    }

    # Création manuelle les données des tags

    /**
     * @return string[]
     */
    private function getTagsData(): array
    {
        return [
            'lorem',
            'ipsum',
            'consectetur',
            'adipiscing',
            'incididunt',
            'labore',
            'voluptate',
            'dolore',
            'pariatur',
        ];
    }

}