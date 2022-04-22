<?php

namespace App\Controller;

use App\Entity\Disponibilite;
use App\Form\DisponibiliteType;
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

        $editImageForm = $this->createFormBuilder(['method' => 'POST'])
            ->add('image', FileType::class, [
                'attr' => [
                    'class' => 'form-image',
                    'id' => 'image',
                    'onChange' => 'submit()'
                ]
            ])
            ->getForm();

        if ($request->isMethod('POST')) {
            $editImageForm->handleRequest($request);

            if ($editImageForm->isSubmitted() && $editImageForm->isValid() && $editImageForm->get('image')->getData()) {
                $this->removeUserImage($user);
                $this->setUserImage($editImageForm, $user, $slugger);
                $entityManager->persist($user);
                $entityManager->flush();
            }
        }


        $editInfoForm = $this->createFormBuilder($user, ['method' => 'GET'])
            ->add('nom', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Nom'
                ]
            ])
            ->add('prenom', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Prenom'
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Email'
                ]
            ])
            ->add('coaching', ChoiceType::class, [
                'choices'  => [
                    '' => '',
                    'Domicile' => 'Domicile',
                    'Distance' => 'Distance',
                    'En Ligne' => 'En Ligne',
                    'Libre' => 'Libre',
                ], 'attr' => [
                    'placeholder' => 'Coaching',
                ],
                'required' => false,
                'label' => false,
            ])

            ->add('ville', ChoiceType::class, [
                'attr' => [
                    'placeholder' => 'Ville'
                ], 'choices'  => [
                    '' => '',
                    'Paris' => 'Paris',
                    'Marseille' => 'Marseille',
                    'Nice' => 'Nice',
                    'Nante' => 'Nante',
                ],
                'required' => false,
                'label' => false,
            ])
            ->add('description', TextareaType::class, [
                'attr' => [
                    'cols' => '85',
                    'rows' => '5',
                    'placeholder' => 'Description'
                ],
                'required' => false,
                'label' => false,
            ])
            ->getForm();

        if ($request->isMethod('GET')) {
            $editInfoForm->handleRequest($request);

            if ($editInfoForm->isSubmitted() && $editInfoForm->isValid()) {
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
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $image->guessExtension();

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
                $imagesystem->remove($imageDerectory . '/' . $image);
            } catch (FileException $e) {
                echo $e;
            }
        }
    }

}
