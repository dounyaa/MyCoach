<?php

namespace App\Controller;

use App\Entity\Calendar;
use App\Entity\Disponibilite;
use App\Entity\User;
use App\Form\CalendarType;
use App\Form\DisponibiliteType;
use App\Repository\CalendarRepository;
use App\Repository\DisponibiliteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ProfilController extends AbstractController
{
    #[Route('/profil', name: 'app_profil')]
    public function update(Request $request, Security $security, SluggerInterface $slugger, EntityManagerInterface $entityManager, DisponibiliteRepository $disponibiliteRepository): Response
    {
        $this->security = $security;
        $user = $this->security->getUser();

        $editImageForm= $this->createFormBuilder(['method' => 'POST'])
                ->add('image', FileType::class, [
                    'attr' => [
                        'class' => 'form-control', 
                        'id' => 'image',
                        'onChange' => 'submit()'
                    ]
                ])
                ->getForm();

        if ($request->isMethod('POST'))
        {  
            $editImageForm->handleRequest($request);
            
            if ($editImageForm->isSubmitted() && $editImageForm->isValid()) 
                {
                    $this->removeUserImage($user);
                    $this->setUserImage($editImageForm, $user, $slugger);
                    $entityManager->persist($user);
                    $entityManager->flush();
                }
        }   


        $editInfoForm= $this->createFormBuilder($user, ['method' => 'GET'])
                ->add('nom', TextType::class, [
                    'attr' => [
                        'class' => 'form-control', 
                        
                    ]])
                ->add('prenom', TextType::class, [
                    'attr' => [
                        'class' => 'form-control', 
                        
                    ]])
                ->add('email', EmailType::class, [
                    'attr' => [
                        'class' => 'form-control', 
                        
                    ]])
                ->add('coaching', ChoiceType::class, [
                    'attr' => [
                        'class' => 'form-control', 
                        
                    ],'choices'  => [
                        '' => '',
                        'Domicile' => 'Domicile',
                        'Distance' => 'Distance',
                        'En Ligne' => 'En Ligne',
                        'Libre' => 'Libre',
                    ],])

                ->add('ville', TextType::class, [
                    'attr' => [
                        'class' => 'form-control'],
                        ])
                ->add('description', TextareaType::class, [
                    'attr' => [
                        'class' => 'form-control', 
                        
                    ]])
                ->getForm();

        if ($request->isMethod('GET'))
        {
            $editInfoForm->handleRequest($request);
            
            if ($editInfoForm->isSubmitted() && $editInfoForm->isValid())
            {
                $entityManager->persist($user);
                $entityManager->flush();
            }

        }

        $disponibilite = new Disponibilite();
        $disponibiliteform = $this->createForm(DisponibiliteType::class, $disponibilite);
        $disponibiliteform->handleRequest($request);

        if ($disponibiliteform->isSubmitted() && $disponibiliteform->isValid()) {
            $disponibilite->setCoach($user);
            $disponibilite->setEtat("disponible");
            $disponibiliteRepository->add($disponibilite);
        }
        
        
        return $this->render('profil/index.html.twig', [
            'user' => $user,
            'editImageForm' => $editImageForm->createView(),
            'editInfoForm' => $editInfoForm->createView(),
            'disponibiliteform' => $disponibiliteform->createView(),
        ]);
    }

    function setUserImage(FormInterface $form, UserInterface $user, SluggerInterface $slugger)
    {
        $image = $form->get('image')->getData();
        if ($image) {
            $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            // this is needed to safely include the file name as part of the URL
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$image->guessExtension();

            // Move the file to the directory where brochures are stored
            try {
                $image->move(
                    $this->getParameter('coach_directory'),
                    $newFilename
                );
            } catch (FileException $e) {
                echo $e;
            }
            $user->setImage($newFilename);
        }
    }

    function removeUserImage(UserInterface $user): void
    {
        $image = $user->getImage();
        if ($image) {

            // reMove the file to the directory where brochures are stored
            try {
                $imagesystem = new Filesystem();
                $imageDerectory = $this->getParameter('coach_directory');
                $imagesystem->remove($imageDerectory.'/'.$image);

            } catch (FileException $e) {
                echo $e;
            }

        }
    }

    #[Route('/calendrier', name: 'app_calendar')]
    public function calendar(Request $request ,CalendarRepository $calendarRepository): Response
    {
        $calendar = new Calendar();
        $form = $this->createForm(CalendarType::class, $calendar);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $calendar->setBackgroundColor('#ffd500');
            $calendarRepository->add($calendar);
            return $this->redirectToRoute('app_calendar');
        }

        $events = $calendarRepository->findAll();
        $rdvs = [];
        foreach($events as $event){
            $rdvs[] = [
                'id' => $event->getId(),
                'start' => $event->getStart()->format('Y-m-d H:i:s'),
                'end' => $event->getEnd()->format('Y-m-d H:i:s'),
                'title' => $event->getTitle(),
                'backgroundColor' => $event->getBackgroundColor(),
            ];
        $data = json_encode($rdvs);
        }

        return $this->renderForm('profil/calendar.html.twig', [
            'calendar' => $calendar,
            'form' => $form,
            'data' => $data
        ]);
    }

    #[Route('/calendrier/{id}', name: 'app_calendar_edit', methods: ['POST'])]
    public function edit(Request $request, Calendar $calendar, CalendarRepository $calendarRepository): Response
    {
        $form = $this->createForm(CalendarType::class, $calendar);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $calendarRepository->add($calendar);
        }

        return $this->redirectToRoute('app_calendar', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/calendrier/{id}/delete', name: 'app_calendar_delete', methods: ['POST'])]
    public function delete(Request $request, Calendar $calendar, CalendarRepository $calendarRepository): Response
    {
        $form = $this->createForm(CalendarType::class, $calendar);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $calendarRepository->remove($calendar);
        }

        return $this->redirectToRoute('app_calendar', [], Response::HTTP_SEE_OTHER);
    }

    // #[Route('/profil', name: 'app_disponibilite_new', methods: ['GET', 'POST'])]
    // public function disponibilite(Request $request, DisponibiliteRepository $disponibiliteRepository): Response
    // {
    //     $disponibilite = new Disponibilite();
    //     $disponibiliteform = $this->createForm(DisponibiliteType::class, $disponibilite);
    //     $disponibiliteform->handleRequest($request);

    //     if ($disponibiliteform->isSubmitted() && $disponibiliteform->isValid()) {
    //         $disponibiliteRepository->add($disponibilite);
    //         return $this->redirectToRoute('app_profil');
    //     }

    //     return $this->renderForm('profil/index.html.twig', [
    //         'disponibilite' => $disponibilite,
    //         'disponibiliteform' => $disponibiliteform,
    //     ]);
    // }


}
