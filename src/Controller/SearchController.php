<?php

namespace App\Controller;

use App\Entity\Search;
use App\Repository\UserRepository;
use App\Form\SearchType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    #[Route('/search', name: 'app_search')]
    public function searchBar(Request $request)
    {
        $search = new Search();
        $form = $this->createForm(SearchType::class, $search);
        $form->handleRequest($request);

        return $this->render('search/searchBar.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/handleSearch',  name: 'handleSearch')]
    public function handleSearch(Request $request, UserRepository $user)
    {
        $search = new Search();
        $form = $this->createForm(SearchType::class, $search);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $user->findByName($search->getQuery());
        }

        return $this->render('search/index.html.twig', [
            'data' => $data
        ]);
    }
}
