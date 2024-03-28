<?php

namespace App\Controller;

use App\Entity\Posts;
use App\Entity\Users;
use App\Repository\PostsRepository;
use Doctrine\Bundle\DoctrineBundle;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MainController extends AbstractController
{
    #[Route('/', name: 'index')]
    // public function index(EntityManagerInterface $em): Response
    // {
    //     // Création del'objet Users
    //     $author = new Users();
    //     $author->setEmail("test@test.com")
    //            ->setFullName("Billie Joe")
    //            ->setUsername("Panda")
    //            ->setPassword("Panda@29");

    //     $em->persist($author);


    //     // Création de l'objet Posts
    //     $post = new Posts();
    //     $post->setTitle("un titre")
    //          ->setSlug("un-titre")
    //          ->setContent("un contenu")
    //          ->setPublishedAt(new \DateTimeImmutable())
    //          ->setSummary("summary")
    //          ->setAuthor($author);

    //     //Méthode persist comme git init et git add avec un commit qui permmet de préparer
    //     // les datas avannt de les pusher dans la base de données.  
    //     $em->persist($post);
    //     // Méthode flush comme git push qui permet de pousser les datas dans la base de données
    //     $em->flush();

    //     // Méthode dumb & die :(idem a var dump)
    //          dd($post); 

    // }

    // Les méthodes suivantes: la méthode FindAll, permet de récupérer toutes les 
    // données de la base2Données.
    // public function index(PostsRepository $repository): Response 
    // {
    //     $posts = $repository->findAll();
    //     dd($posts);
    // }

    public function index(PostsRepository $repository): Response 
    {
        $posts = $repository->findAll();
        // $posts = $repository->findByTitle("un titre");
       
        //  La méthod ->render est utilisable seulement si on utilise : 
        // class MainController extends AbstractController et permet de diriger vers 
        // la page index.html.twig
        return $this->render('main/index.html.twig',
        [
            'posts' => $posts
        ]
    );
    }
    #[Route('/{id}', name: 'show')]
    public function show(Posts $post): Response 
    {
        return $this->render('main/show.html.twig', [
            'post' => $post
        ]
        );
    }


}
