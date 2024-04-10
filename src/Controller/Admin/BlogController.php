<?php

namespace App\Controller\Admin;

use App\Entity\Posts;
use App\Entity\Users;
use App\Form\PostType;
use Doctrine\ORM\EntityManager;
use App\Security\Voter\PostVoter;
use App\Repository\PostsRepository;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
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
        $posts = $repository->findBy([], orderBy:['published_at' => 'DESC']);
        return $this->render('admin/blog/index.html.twig', [
        'posts' => $posts
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function create(#[CurrentUser] Users $user,Request $request, EntityManagerInterface $em, UsersRepository $repository): Response 
    {
        // $user = $repository->findOneBy(['username' => 'jane_admin']);
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
    #[IsGranted(PostVoter::MUTATE, 'post')]
    public function edit(Posts $post, Request $request, EntityManagerInterface $em): Response
    {
        // $this->denyAccessUnlessGranted('POST_MUTATE', $post);
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
   
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted(PostVoter::MUTATE, 'post')]
    public function delete(Posts $post, Request $request, EntityManagerInterface $em): Response
    {
        /** @var string | null $token  */
        $token = $request->getPayLoad()->get('token');
        if($this->isCsrfTokenValid('delete', $token)) {
        $em->remove($post);
        $em->flush();
        }
        return $this->redirectToRoute('admin_blog_index', status: Response::HTTP_SEE_OTHER);
    }
}