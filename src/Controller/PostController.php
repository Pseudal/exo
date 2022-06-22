<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Theme;
use App\Form\PostType;
use DateTimeImmutable;
use App\Repository\PostRepository;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

#[Route('admin')]
class PostController extends AbstractController
{
    #[Route('/post2', name: 'app_post')]
    public function index(): Response
    {
        return $this->render('post/index.html.twig', [
            'controller_name' => 'PostController',
        ]);
    }

    /**
     * @Route("/post", name="create_post")
     */
    public function createPost(ManagerRegistry $doctrine, Request $request){
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN', null, 'access_denied');
        $manager = $doctrine->getManager();

        $post = new Post();
        $form = $this->createFormBuilder($post)
                     ->add('reference',null, ['label'=>'Reference', 'attr'=>['placeholder'=>'Entrez la référence']])
                     ->add('title', TextType::class, ['label'=>'Titre', 'attr'=>['placeholder'=>'Entrez la référence']])
                     ->add('author', null, ['label'=>'Auteur','attr'=>['placeholder'=>'Entrez l\'auteur']])
                     ->add('theme', EntityType::class, ['class'=>Theme::class, 'choice_label'=>'name'])
                     ->add('image')
                     ->add('content', null, ['label'=>'Contenu','attr'=>['placeholder'=>'Entrez le contenu']])
                     ->add('soumettre', SubmitType::class)
                     ->getForm();
        $form->remove('publishedAt');
        $post->setPublishedAt(new \DatetimeImmutable());
        $form->handleRequest($request);
        //dd($request);
        if($form->isSubmitted() && $form->isValid()){
            $manager->persist($post);
            $manager->flush();
            $this->addFlash('success', "Article N°: ".$post->getId()." est créé");
            return $this->redirectToRoute('list_posts');
        }
        // $post->setReference("52356totot30")
        //      ->setTitle("Election législative")
        //      ->setContent("survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.")
        //      ->setAuthor("Anoine")
        //      ->setPublishedAt(new \DateTimeImmutable())
        //      ->setImage("election.jpg");

        return $this->renderForm('post/add.html.twig',['form_post'=>$form]);
    }

    #[Route('/list', name:"list_posts")]
    public function getPosts(ManagerRegistry $doctrine, Request $request){

        //dd($request->request->get('search'));

        $search = $request->request->get('search');
        //dd($search);

        $repo = $doctrine->getRepository(Post::class);
        $tab_posts = ($search) ? $repo->findSearch($search) : $repo->findBy([],['id'=>'DESC']);
        //$tab_posts = $repo->findAll();
        //dd($tab_posts);
        return $this->render('post/list.html.twig', ['posts'=> $tab_posts]);
    }


    #[Route('/delete/{id}', name:"delete_post")]
    public function deletePost(ManagerRegistry $doctrine, $id){
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN', null, 'access_denied');
        $repo = $doctrine->getRepository(Post::class);
        $post = $repo->find($id);

        $manager = $doctrine->getManager();
        $manager->remove($post);
        $manager->flush();
        $this->addFlash('success','L\'article '.$post->getTitle().' a été supprimé');
        return $this->redirectToRoute('list_posts');
    }

    #[Route('/update/{id}', name:"update_post")]
    public function updatePost(PostRepository $repo, $id, ManagerRegistry $doctrine, Request $request){
        $post = $repo->find($id);
        $form = $this->createForm(PostType::class, $post);
        $form->remove('publishedAt');
        // $post->setTitle('Nième sanction')
        //     ->setAuthor('Nabil');
        // $manager = $doctrine->getManager();
        // $manager->flush();
        //$this->addFlash('success','L\'article '.$post->getTitle().' a été modifié');
        //return $this->redirectToRoute('list_posts');
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $manager = $doctrine->getManager();
            $manager->flush();
        $this->addFlash('success','L\'article '.$post->getTitle().' a été modifié');
        return $this->redirectToRoute('list_posts');
        }
        return $this->renderForm('post/edit.html.twig', ['form_edit'=>$form]);
    }

    #[Route('/show/{id}', name:'show_post')]
    public function showPost(Post $post){
        return $this->render('post/show.html.twig', ['post'=>$post]);
    }

    #[Route('/show/theme/{id}', name:'show_theme_id')]
    public function showTheme(Theme $theme){
        return $this->render('post/show_theme.html.twig', ['theme'=>$theme]);
    }
}
