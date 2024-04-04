<?php

namespace App\Controller\Admin;

use App\Entity\Posts;
use App\Form\PostType;
use Doctrine\ORM\EntityManager;
use App\Repository\PostsRepository;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

// Définition d'une route : 
#[Route('/admin/blog', name: 'admin_blog_')]
final class BlogController extends AbstractController
{
    /**
     * Méthode du controlleur pour affiche la page index.
     * 
     * Cette méthode est chargée de rendre la page d'index du blog dans le panneau d'administration.
     * @Route('', name='index', methods= ['GET'])
     * 
     * @param PostsRepository : $repository Le dépôt permettant d'asséder aux données des articles.
     * 
     * @return Response : Retourne un objet Response contenant le HTML rendu pour la page index.
     */
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(PostsRepository $repository): Response 
    {
        $posts = $repository->findAll();
        return $this->render('admin/blog/index.html.twig', [
        'posts' => $posts
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $em, UsersRepository $repository): Response 
    {
        $user = $repository->findOneBy(['username' => 'jane_admin']);
        $post = new Posts();
        $post->setAuthor($user);
        $form = $this-> createForm(PostType::class, $post);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            // $em = entity_manager;
            $em->persist($post);
            $em->flush();
            return $this->redirectToRoute('admin_blog_index', status: Response::HTTP_SEE_OTHER);
            // L'ajout de Response::HTTP_SEE_OTHER est une convention aujourd'hui en matère de réponse.
        }
        return $this->render('admin/blog/new.html.twig', [
            'form' => $form,
            'post' =>$post
        ]);
    }

    #[Route('/edit/{id}', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Posts $post, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('admin_blog_index',
            [
                'id'=> $post->getId()
            ],
             status: Response::HTTP_SEE_OTHER);
        }
        return $this->render('admin/blog/edit.html.twig', [
            'form' => $form,
            'post' => $post
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Posts $post): Response
    {
        return $this->render('admin/blog/show.html.twig', [
            'post' => $post
        ]);
    }
}