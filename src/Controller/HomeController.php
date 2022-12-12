<?php

namespace App\Controller;

use App\Entity\News;
use App\Repository\NewsRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
// Paginator 
use Knp\Component\Pager\PaginatorInterface;
class HomeController extends AbstractController
{

    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @Route("/home", name="app_home")
     */
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
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


        //$news = new News();
        return $this->render('home/index.html.twig', [
            'title' => 'News Parser',
            'news' => $news_pagination
        ]);
    }
}
