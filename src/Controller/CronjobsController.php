<?php

namespace App\Controller;

use App\Entity\News;
use App\Repository\NewsRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use \DOMDocument;
use \DOMXpath;
use \DateTimeImmutable;

class CronjobsController extends AbstractController
{
    /**
     * @Route("/cronjobs", name="app_cronjobs")
     */
    

    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function saveOrUpdate($param, $type)
    {
        $datetime = new DateTimeImmutable();
        $entityManager = $this->doctrine->getManager();
        $repository = $this->doctrine->getRepository(News::class);

        $news = $repository->findOneBy([
            'post_url' => $param
        ]);
        if($type == "post_url" && !$news){ 
            //if post url does not exist, save
            $news = new News();
            $news->setPostUrl($param);
            $news->setTitle("NULL");
            $news->setDescription("NULL");
            $news->setPostImageUrl("NULL");
            $news->setIsActive(true);
            $news->setAuthor("NULL");
            $news->setCreatedAt($datetime);

            $entityManager->persist($news);
            $entityManager->flush();
        }
        else{
            //update 

            $news_update = $repository->findOneBy([
                $type => "NULL"
            ]);

            if($news_update){
            
            if($type == "title"){
                $news_update->setTitle($param);
                $entityManager->flush();
            }
            else if($type == "description"){
                    $news_update->setDescription($param);
                    $entityManager->flush();
                
            }
            else if($type == "post_image_url"){
                $news_update->setPostImageUrl($param);
                $entityManager->flush();
            }
            else if($type == "author"){
                $news_update->setAuthor($param);
                $entityManager->flush();
            }
            //already saved post url at the top
            /* else if($type == "post_url"){
                $news->setPostUrl($param);
                $entityManager->flush();
            }*/
            else{
                return "No update made!";
            }
                
        }
            
        } 
    }

    public function index(): Response
    {
        return $this->render('cronjobs/index.html.twig', [
            'controller_name' => 'CronjobsController',
        ]);
    }

    public function newsParser(): JsonResponse
    {
        //define variables
        $title_ = $title = $desc_ = $desc = $author =  $date_posted = $img_link = $count = $post_url ="";
        
        //curl get request to get the html

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://highload.today/uk/");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $html = curl_exec($ch);

        $dom = new DOMDocument();

        @ $dom->loadHTML($html);

        $xpath = new DOMXpath($dom);
        $news = $xpath->query('//div[@class="lenta-item"]/div');

        $count = $news->length;

//        foreach($news as $nw){
        if (is_array($news) || is_object($news)){

            $author_nodes = $xpath->query('//div[@class="lenta-item"]//div[@class="author-block"]//div[@class="author-right"]//p//a');

            $image_url_nodes = $xpath->query('//div[@class="lenta-item"]//div[@class="lenta-image"]//img');
    
            $post_url_nodes = $xpath->query('//div[@class="lenta-item"]//a');
    
            $title_nodes = $xpath->query('//div[@class="lenta-item"]//h2'); 
    
            $desc_nodes = $xpath->query('//div[@class="lenta-item"]//p'); 

        //Post Url
        foreach($post_url_nodes as $url)
        {
            $post_url_ = $url->getAttribute('href');
            $find1 = "https://highload.today/uk/";
            $find2 = "https://highload.today/uk/category";
            if(strpos($post_url_, $find1) !== false && strpos($post_url_, $find2) === false) {
                $post_url = $post_url_;
                $type = "post_url";
                $this->saveOrUpdate($post_url, $type);
            }            
        }

            //title
        foreach($title_nodes as $h2) 
        {
            $title = $h2->textContent; 
            //if($title == "") $title = "NULL";
           // echo $title."<hr>";   
           $type = "title";
           $this->saveOrUpdate($title, $type);
        } 
            
            //description
        foreach($desc_nodes as $des){
            $type = "description";
            $desc = $des->textContent;
            if(strlen($des->textContent) > 75){  
                $this->saveOrUpdate($desc, $type);
            } 
           /* else if(strlen($desc_) < 5){
                $desc = "No Description";
                $this->saveOrUpdate($desc, $type);
            } */
            
        }

        //images
        foreach($image_url_nodes as $img){

            $img_link_ = $img->getAttribute('src');
            $find = 'https://highload.today/wp-content/uploads/';

            if(strpos($img_link_, $find) !== false)
                {
                    $img_link = $img_link_;
                    $type = "post_image_url";
                    $this->saveOrUpdate($img_link, $type);
                }

        }

        //Author
        foreach($author_nodes as $authr){

            $author = $authr->textContent;
            $type = "author";
            $this->saveOrUpdate($author, $type);
            
        }

        }

        
   // }
        
       /* return $this->json([
            'number_of_posts' => $count,
        ]); */
        return new JsonResponse([
            'number_of_posts' => $count,
        ], 200);
    }
}
