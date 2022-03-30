<?php

namespace App\Controller;

use App\Entity\Calendar;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }

    #[Route('/api', name: 'app_api')]
    public function index(): Response
    {
        return $this->render('api/index.html.twig', [
            'controller_name' => 'ApiController',
        ]);
    }

    #[Route('/api/{id}/edit', name: 'app_api_edit', methods: 'PUT')]
    public function majEvent(?Calendar $calendar, Request $request): Response
    {
        //on recupere les données
        $donnees = json_decode($request->getContent());
        if(
            isset($donnees->title) && !empty($donnees->title) &&
            isset($donnees->start) && !empty($donnees->start) &&
            isset($donnees->end) && !empty($donnees->end) &&
            isset($donnees->description) && !empty($donnees->description) &&
            isset($donnees->backgroundColor) && !empty($donnees->backgroundColor) &&
            isset($donnees->borderColor) && !empty($donnees->borderColor) &&
            isset($donnees->textColor) && !empty($donnees->textColor)
        ) // les données sont completes
        {
            $code = 200;
            //on verifier si id existe
            if(!$calendar){
                //on instanie un rendez vous
                $calendar = new Calendar;
                $code = 201;
            }
            //on hydrate l'objet avec les données
            $calendar->setTitle($donnees->title);
            $calendar->setDescription($donnees->description);
            $calendar->setStart(new DateTime($donnees->start));
            if($donnees->allDay){
                $calendar->setEnd(new DateTime($donnees->start));
            }else{
                $calendar->setEnd(new DateTime($donnees->end));
            }
            $calendar->setAllDay($donnees->allDay);
            $calendar->setBackgroundColor($donnees->backgroundColo);
            $calendar->setBorderColor($donnees->borderColor);
            $calendar->setTextColor($donnees->textColor);

            $this->em->persist($calendar);
            $this->em->flush();

            //on retourne le code
            return new Response('ok', $code);
        }
        else //les données sont incompletes
        {
            return new Response('Données incomplétes', 404);
        }

        return $this->render('api/index.html.twig', [
            'controller_name' => 'ApiController',
        ]);
    }
}
