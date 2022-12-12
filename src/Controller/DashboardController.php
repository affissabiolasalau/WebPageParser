<?php

namespace App\Controller;

use App\Entity\News;
use App\Entity\User;
use App\Repository\NewsRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

// Paginator 
use Knp\Component\Pager\PaginatorInterface;

class DashboardController extends AbstractController
{

    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @Route("/dashboard", name="app_dashboard")
     */
    public function index(): Response
    {
        return new Response('Hello!');
    }

    public function dashboard(Request $request, PaginatorInterface $paginator): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $news_repository = $this->doctrine->getRepository(News::class);
        $news = $news_repository->findAll();
 
        // Paginate the results
        $news_pagination = $paginator->paginate(
            // Doctrine Query
            $news,
            // Define the page parameter
            $request->query->getInt('page', 1),
            // Items per page
            10
        );

        return $this->render('dashboard/index.html.twig', [
            'title' => 'News Parser',
            'news' => $news_pagination
        ]);
    }

    public function delete_news(int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $repository = $this->doctrine->getRepository(News::class);
        $post = $repository->find($id);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($post);
        $entityManager->flush();

        return $this->redirectToRoute('dashboard');

    }
}
