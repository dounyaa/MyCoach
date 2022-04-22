<?php

namespace App\Controller;

use App\Entity\Disponibilite;
use App\Form\CalendarType;
use App\Repository\DisponibiliteRepository;
use DateInterval;
use DateTime;
use Symfony\Component\Security\Core\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class CalendarController extends AbstractController
{
    #[Route('/calendrier', name: 'app_calendar')]
    public function calendar(Request $request, Security $security, DisponibiliteRepository $disponibiliteRepository): Response
    {
        $this->security = $security;
        $coach = $this->security->getUser();

        $disponibilite = new Disponibilite();
        $form = $this->createForm(CalendarType::class, $disponibilite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $disponibilite->setCoach($coach);
            $disponibilite->setEtat('Disponible');

            $disponibiliteRepository->add($disponibilite);
            return $this->redirectToRoute('app_calendar');
        }

        $events = $coach->getDisponibilites();
        $rdvs = [];
        foreach ($events as $event) {
            $dateDebut = $event->getDate();
            $dateFin = new DateTime($event->getDate()->format('Y-m-d H:i:s'));
            $dateFin->add(new DateInterval('PT' . $event->getDuree() . 'M'));

            $rdvs[] = [
                'id' => $event->getId(),
                'start' => $dateDebut->format('Y-m-d H:i:s'),
                'end' => $dateFin->format('Y-m-d H:i:s'),
                'title' => $event->getEtat(),
                'backgroundColor' => '#ffd500',
            ];
            $data = json_encode($rdvs);
        }
        $data = json_encode($rdvs);
        return $this->renderForm('calendar/index.html.twig', [
            'calendar' => $disponibilite,
            'form' => $form,
            'data' => $data
        ]);
    }

    #[Route('/calendrier/{id}', name: 'app_calendar_edit', methods: ['POST'])]
    public function edit(Request $request, Disponibilite $calendar, DisponibiliteRepository $calendarRepository): Response
    {
        $form = $this->createForm(CalendarType::class, $calendar);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $calendarRepository->add($calendar);
        }

        return $this->redirectToRoute('app_calendar', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/calendrier/{id}/delete', name: 'app_calendar_delete', methods: ['POST'])]
    public function delete(Request $request, Disponibilite $calendar, DisponibiliteRepository $calendarRepository): Response
    {
        $form = $this->createForm(CalendarType::class, $calendar);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $calendarRepository->remove($calendar);
        }

        return $this->redirectToRoute('app_calendar', [], Response::HTTP_SEE_OTHER);
    }
}
