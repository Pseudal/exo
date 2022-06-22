<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('admin')]
class HomeController extends AbstractController
{
    private $data;
    public function __construct()
    {
        $this->data =  [
            ["id" => 1, "name" => "dupond", "lastname" => "roger", "age" => 22, "image" => "roger.jpg"],
            ["id" => 2, "name" => "durand", "lastname" => "thomas", "age" => 52, "image" => "thomas.jpeg"],
        ];
    }
    
    #[Route('/home', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/person', name: 'app_person')]
    public function getData(): Response
    {

        return $this->render('home/data.html.twig', [
            'personnes' => $this->data,
        ]);
    }

    /**
     * @Route("/admin/person/{id}", name="person_item")
     */
    public function getItem(int $id)
    {
        foreach ($this->data as $person) {
            if ($person['id'] == $id) {
                return $this->render('home/item_person.html.twig', ['person_data' => $person]);
            }
        }
    }
}
