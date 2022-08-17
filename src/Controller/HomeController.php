<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Person; // importer l'entité
use App\Entity\Article; // importer l'entité
use App\Entity\Comment; // importer l'entité
use App\Form\ArticleType; // importer formulaire article
use App\Form\CommentType; // importer formulaire commentaire

class HomeController extends AbstractController
{
    /**
     * Code exemple pour creer un commentair dans un article
     */
    public function exempleCreerCommentaireAssocieArticle(){
        // Je récupere un article 
        $id = 2; // Article "2" par exemple
        $article = $repoArticle->find($id);
    
        // Creer un commentaire
        $comment = new Comment();
        $comment->setMessage("On dit pain au chocolat");
        $comment->setArticle($article); // Associer le commentaire à l'article

        // -- persister en base
        $em = $this->getDoctrine()->getManager();
        $em->persist($comment);
        $em->flush();
    }

    /**
     * @Route("/", name="app_index")
     */
    public function index(): Response
    {
        // je force la redirection sur la route app_home
        return $this->redirectToRoute('app_home');
    }

    /**
     * @Route("/home", name="app_home")
     */
    public function home(): Response
    {
        // je force la redirection sur la route app_home
        return $this->render('home/index.html.twig');
    }

    /**
     * @Route("/select-person/{id}", name="app_select_person")
     */
    public function selectPerson($id): Response
    {

        // Repo Person
        $repoPerson = $this->getDoctrine()->getRepository(Person::class); // Récuperer l'entity manager doctrine
        // Je récupere une personne 
        $person = $repoPerson->find($id);


        // Repo Article
        $repoArticle = $this->getDoctrine()->getRepository(Article::class); // Récuperer l'entity manager doctrine
        // Je récupere une personne 
        $article = $repoArticle->find($id);


        // tableau
        $array = array($person, $article);

       //return $this->render('home/index.html.twig');

       return new Response("Person : ".$person . "<br>Article : " .$article);
    }

     /**
     * @Route("/create-person", name="app_create_person")
     */
    public function createPerson(): Response
    {
        // J'instancie une classe Article
        $article = new Article();
        $article->setTitle("Ouverture de la chocolatine");
        $article->setDescription("Meilleur boulangerie ever");


        // Traitement avec l'ORM
        $em = $this->getDoctrine()->getManager(); // Récuperer l'entity manager doctrine

        $em->persist($article);
        $em->flush();

        return $this->render('home/index.html.twig');
    }

    /**
     * @Route("/product/{id}", name="app_product")
     */
    public function productDetail($id, Request $request): Response
    {
        // Repo Article
        $repoArticle = $this->getDoctrine()->getRepository(Article::class); // Récuperer l'entity manager doctrine
        
        // Récupérer l'article
        $article = $repoArticle->find($id);

        // Creer le form comment avec l'action surchargé
        $commentForm = $this->createForm(CommentType::class, new Comment(),  ["action" => $this->generateUrl('app_create_comment',[ "idArticle" => $article->getId() ])]);
        $commentForm->handleRequest($request);

        // Afficher la vue
        return $this->render('article/article-show.html.twig', [
            "article" => $article,
            "commentForm" => $commentForm->createView()
        ]);
    }

      /**
     * @Route("/create-comment/{idArticle}", name="app_create_comment")
     */
    public function insertComment($idArticle, Request $request): Response
    {
        // Creer le form comment
        $commentForm = $this->createForm(CommentType::class, new Comment());
        $commentForm->handleRequest($request);

        // Part : 03
        // --Tester si le form à des données envoyées
        if ($commentForm->isSubmitted() && $commentForm->isValid()){
            // Traitement
            // -- récuperer l'entité du formumlaire
            $comment = $commentForm->getData();

            // -- Associer l'article au commentaire
            $repoArticle = $this->getDoctrine()->getRepository(Article::class);
            $article = $repoArticle->find($idArticle);

            $comment->setArticle($article); 

            // -- partie base de données
            $em = $this->getDoctrine()->getManager();
            $em->persist($comment);
            $em->flush();

            // version string format
            $this->addFlash("message_success", "Commentaire envoyé avec succès !");

            // Redirection sur la page de l'article
            return $this->redirectToRoute("app_product", ["id" => $idArticle]);
        }

        return $this->redirectToRoute("app_product", ["id" => $idArticle, "request" => $request], 307);
    }


    /**
     * @Route("/product-list/", name="app_product_list")
     */
    public function productList(): Response
    {
        // Repo Article
        $repoArticle = $this->getDoctrine()->getRepository(Article::class); // Récuperer l'entity manager doctrine
        
        // Je récupere un article 
        $articleList = $repoArticle->findAll();
        
        return $this->render('article/article.html.twig', [
            "article_list" => $articleList,
        ]);
    }

    /**
     * @Route("/product-form/", name="app_product_form")
     */
    public function showProductForm(Request $request): Response
    {
        // Part : 01
        // Article vide
        $product =  new Article();

        // Instancie le formulaire ArticleType avec un article vide
        $articleForm = $this->createForm(ArticleType::class, $product);

        // Part : 02
        // Ecouter la requette http 
        $articleForm->handleRequest($request);

        // Part : 03
        // --Tester si le form à des données envoyées
        if ($articleForm->isSubmitted() && $articleForm->isValid()){
            // Traitement
            // -- récuperer l'entité du formumlaire
            $productToSave = $articleForm->getData();

            // -- custom
            $productToSave->SetDateCreated(new DateTime());

            // -- partie base de données
            $em = $this->getDoctrine()->getManager();
            $em->persist($productToSave);
            $em->flush();

            // version simple
            // $this->addFlash("message_success", "L'article à été crée avec succèes");
            // version string format
            $this->addFlash("message_success", sprintf("L'article %s à été crée avec succès", $productToSave->getTitle()));

            // Redirection sur home
            return $this->redirectToRoute("app_home");
        }

        // -- Sinon juste afficherr le formualaire vide par défaut (premiere fois)
        // Retourner le rendu
        return $this->render('article/article-form.html.twig', [
            "articleForm" => $articleForm->createView()
        ]);

    }
}
