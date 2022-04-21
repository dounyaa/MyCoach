<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Commentaire;
use App\Entity\Promo;
use App\Repository\PromoRepository;

#[Route('/coachs')]
class CoachsController extends AbstractController
{
    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }

    #[Route('/', name: 'app_coachs')]
    public function index(Request $request, UserRepository $users)
    {
        $coachs = [];
        $ville = $request->query->get('ville', null);
        $coaching = $request->query->get('coaching', null);
        $coachs = $users->findCoachs("ROLE_COACH", $ville, $coaching);

        return $this->render('coachs/index.html.twig', [
            'selectVille' => $ville,
            'selectCoaching' => $coaching,
            'coachs' => $coachs,
        ]);
    }

    #[Route('/{id}', methods: ['GET'], name: 'app_coachdeatail')]
    public function coachDetail(int $id,Commentaire $commentaire=null, Request $request): Response
    {
        // detail
        $repoUser = $this->em->getRepository(User::class);
        $coach = $repoUser->find($id);
        $programmes = $coach->getProgramme();

        $commentaire = $coach->getCommentaire();
        $this->addFlash('alert', 'Vous ne pouvez pas prendre un rendez-vous!');

        return $this->render('coachs/coachDetail.html.twig', [
            'coach' => $coach,
            'commentaire' => $commentaire,
            'programmes' => $programmes
        ]);
    }

    #[Route('/{id}', methods: ['POST'], name: 'app_coachdeatail_comment')]
    public function coachComment(int $id, Request $request, Security $security, User $coach): Response
    {
        // commentaire 
        $commentaire = new Commentaire();
            
        $this->security = $security;
        $CurrentUser = $this->security->getUser();
        
        if(!empty($CurrentUser)){
            $nom = $CurrentUser->getNom();
            $prenom = $CurrentUser->getPrenom();
            $userName = $nom .' '.$prenom;
            $userImage = $CurrentUser->getImage();
        }

        if ($request->request->count() > 0){

            $commentaire->setAuteur($userName);
            $commentaire->setAuteurImage($userImage);
            $commentaire->setContenu($request->request->get('description'));
            $commentaire->setCreatedAt(new \DateTime());

            $commentaire->setUser($coach);
            $this->em->persist($commentaire);
            

            $this->em->flush();

            return $this->redirectToRoute('app_coachdeatail', [
                'id' => $id,
            ]);
        }

        return $this->redirectToRoute('app_coachdeatail', [
            'commentaire' => $commentaire,
        ]);
    }
}
