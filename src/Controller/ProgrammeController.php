<?php

namespace App\Controller;

use App\Entity\Programme;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Request;
use App\Form\AddProgrammeType;
use App\Repository\ProgrammeRepository;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/programme')]
class ProgrammeController extends AbstractController
{
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/add/{errors}', methods: ['GET'], name: 'app_add_programme')]
    public function addProgrammeShow(Request $request, String $errors = ""): Response
    {
        $this->denyAccessUnlessGranted("ROLE_COACH");
        $programme = new Programme();
        $form = $this->createForm(AddProgrammeType::class, $programme);
        $form->handleRequest($request);

        return $this->render('programme/addProgramme.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors
        ]);
    }

    #[Route('/add', methods: ['POST'], name: 'app_add_programme_post')]

    public function addProgramme(Request $request, Security $security, SluggerInterface $slugger): Response
    {
        $this->denyAccessUnlessGranted("ROLE_COACH");
        $programme = new Programme();
        $form = $this->createForm(AddProgrammeType::class, $programme);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $programme = $form->getData();
            $image = $form->get('image')->getData();

            if ($image) {
                $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $image->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $image->move(
                        $this->getParameter('programme_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    echo $e;
                }
                $programme->setImage($newFilename);
            }


            $this->security = $security;
            $coach = $this->security->getUser();

            $programme->setUser($coach);
            $this->em->persist($programme);
            $this->em->flush();
            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_programme', [
                'categorie' => 'moi'
            ]);
        }

        return $this->redirectToRoute('app_add_programme', [
            'errors' => 'Formulaire invalide !'
        ]);
    }

    #[Route('/edit/{id}', name: 'app_edit_programme')]
    public function editProgramme(int $id, Request $request, SluggerInterface $slugger): Response
    {
        $this->denyAccessUnlessGranted("ROLE_COACH");
        $programme = $this->em->getRepository(Programme::class)->find($id);
        $img = $programme->getImage();

        $form = $this->createForm(AddProgrammeType::class, $programme);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('image')->getData()) 
            {
                $this->removeProgrammeImage($programme);
                $this->setProgrammeImage($form, $programme, $slugger);
            }
            else
            {
                $programme->setImage($img);
            }

            $this->em->persist($programme);
            $this->em->flush();
            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_programme', [
                'categorie' => 'moi',
            ]);
        }

        return $this->render('programme/editProgramme.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/delete/{id}', name: 'app_delete_programme', methods: ['POST'])]
    public function deleteProgramme(int $id, Request $request): Response
    {
        if ($request->request->get('_token') > 0) {
            $programme = $this->em->getRepository(Programme::class)->find($id);
            $this->em->remove($programme);
            $this->em->flush();
        }

        return $this->redirectToRoute('app_programme', [
            'categorie' => 'moi',
        ]);
    }

    #[Route('/detail/{id}', name: 'app_programme_detail')]
    public function programmeDetail(int $id): Response
    {
        $repo = $this->em->getRepository(Programme::class);
        $programme = $repo->find($id);
        $coach = $programme->getUser();

        return $this->render('programme/programmeDetail.html.twig', [
            'programme' => $programme,
            'coach' => $coach,
        ]);
    }

    #[Route('/{categorie}', name: 'app_programme')]
    public function index(Security $security, $categorie = ''): Response
    {
        $repo = $this->em->getRepository(Programme::class);

        $isMesProgrammes = ($categorie == 'moi');

        $programmes = array();
        if (empty($categorie)) {
            $programmes = $repo->findAll();
        } else if ($categorie == 'moi') {
            $this->denyAccessUnlessGranted("ROLE_COACH");
            $this->security = $security;
            $coach = $this->security->getUser();
            $programmes = $coach->getProgramme();
        } else {
            $programmes = $repo->findBy(array('categorie' => $categorie));
        }

        $categories = array();
        foreach ($programmes as $programme) {
            $programmeCategory = $programme->getCategorie();

            if (!in_array($programmeCategory, $categories)) {
                array_push($categories, $programmeCategory);
            }
        }

        $categoriesCompteur = array();
        foreach ($categories as $categorie) {
            $categoriesCompteur[$categorie] = count($repo->findBy(array('categorie' => $categorie)));
        }

        return $this->render('programme/index.html.twig', [
            'programmes' => $programmes,
            'isMesProgrammes' => $isMesProgrammes,
            'categories' => $categoriesCompteur
        ]);
    }

    function setProgrammeImage(FormInterface $form, Programme $programme, SluggerInterface $slugger)
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
                    $this->getParameter('programme_directory'),
                    $newFilename
                );
            } catch (FileException $e) {
                echo $e;
            }
            $programme->setImage($newFilename);
        }
    }

    function removeProgrammeImage(Programme $programme): void
    {
        $image = $programme->getImage();
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
