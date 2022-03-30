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
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;


#[Route('/programme')]
class ProgrammeController extends AbstractController
{
    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }

    #[Route('/', name: 'app_programme')]
    public function index(): Response
    {
        $repo = $this->em->getRepository(Programme::class);
        $programme = $repo->findAll();

        return $this->render('programme/index.html.twig', [
            'programme' => $programme,
            'addButton' => false
        ]);
    }

    #[Route('/moi', name: 'app_mesprogrammes')]
    public function mesProgrammes(Security $security): Response
    {
        $this->security = $security;
        $coach = $this->security->getUser();
        
        $programme = $coach->getProgramme();

        return $this->render('programme/index.html.twig', [
            'programme' => $programme,
            'addButton' => true
        ]);
    }

    #[Route('/add/{errors}', methods: ['GET'], name: 'app_add_programme')]
    public function addProgrammeShow(Request $request, String $errors = ""): Response
    {
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
        $programme = new Programme();
        $form = $this->createForm(AddProgrammeType::class, $programme);
        $form->handleRequest($request);

        if ($form->isValid()) 
        {
            $programme = $form->getData();
            $image = $form->get('image')->getData();

            if ($image) {
                $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$image->guessExtension();

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

            return $this->redirectToRoute('app_mesprogrammes');
        }

        return $this->redirectToRoute('app_add_programme', [
            'errors' => 'Formulaire invalide !'
        ]);
    }

    #[Route('/{id}', name: 'app_programme_detail')]
    public function programmeDetail(int $id): Response
    {
        $repo = $this->em->getRepository(Programme::class);
        $programme = $repo->find($id);

        return $this->render('programme/programmeDetail.html.twig', [
            'programme' => $programme,
        ]);
    }
}
