<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Blog\Post;
use AppBundle\Utils\Slugger;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class BlogController extends Controller {

    /**
     * @Route("/", name="blog_homepage")
     */
    public function indexAction() {
        // replace this example code with whatever you need
        //$posts = array(1,2,3,4,5);
        $posts = $this->getDoctrine()->getRepository(Post::class)->findBy(array(), array('publishDate' => 'desc'), 30, 0);
        //$repository = $this->getDoctrine()->getRepository('AppBundle:Blog\Entity\Post');
        //$posts = $repository->findAll();
        //$posts = array(1,2,3,4,5);
        return $this->render('Blog/homepage.html.twig', array('posts' => $posts));
    }

    /**
     * @Route("/nouveau", name="blog_nouveau")
     */
    public function createAction(Request $request, Slugger $slugger) {
        $post = new Post();

        $form = $this->createFormBuilder($post)
                ->add('title', TextType::class, ['attr' => ['autofocus' => true]])
                ->add('content', TextareaType::class)
                ->add('save', SubmitType::class)
                ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setCompressedContent(substr($post->getContent(), 0, 120) . '...');
            $post->setUrlAlias($slugger->slugify($post->getTitle()));
            $post->setPublishDate(new \DateTime('NOW'));

            $em = $this->getDoctrine()->getManager();
            $em->persist($post);
            $em->flush();

            $this->addFlash('notice', 'Article ajouté avec succès!');

            if ($form->get('save')->isClicked()) {
                return $this->redirectToRoute('blog_nouveau');
            }

            return $this->redirectToRoute('blog_homepage');
        }

        return $this->render('Blog/nouveau.html.twig', ['post' => $post, 'form' => $form->createView()]);
    }

    /**
     * @Route("/post/{postslug}", name="blog_post")
     */
    public function postAction($postslug) {
        // replace this example code with whatever you need
        $post = $this->getDoctrine()->getRepository(Post::class)->findOneByUrlAlias($postslug);
        //return $this->render('Blog/post.html.twig', array('postslug' => $postslug));
        return $this->render('Blog/post.html.twig', array('post' => $post));
    }

    /**
     * @Route("/edit/{urlAlias}", name="blog_edit")
     */
    public function editAction(Request $request, Post $post, Slugger $slugger) {

        $form = $this->createFormBuilder($post)
                ->add('title', TextType::class, ['attr' => ['autofocus' => true]])
                ->add('content', TextareaType::class)
                ->add('save', SubmitType::class)
                ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setUrlAlias($slugger->slugify($post->getTitle()));
            $post->setCompressedContent(substr($post->getContent(), 0, 120) . '...');
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('notice', 'Article modifié avec succès!');

            return $this->redirectToRoute('blog_edit', ['urlAlias' => $post->getUrlAlias()]);
        }

        return $this->render('Blog/edit.html.twig', [
                    'post' => $post,
                    'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/supprimer/{urlAlias}", name="blog_supprimer")
     */
    public function deleteAction(Request $request, Post $post) {

        $form = $this->createFormBuilder($post)
                ->add('delete', SubmitType::class)
                ->getForm();

        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($post);
            $em->flush();

            $this->addFlash('notice', 'Article supprimé avec succès!');

            return $this->redirectToRoute('blog_homepage');
        }


        return $this->render('Blog/delete.html.twig', [
                    'post' => $post,
                    'form' => $form->createView(),
        ]);
    }

}
