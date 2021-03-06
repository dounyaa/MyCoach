<?php

namespace App\Controller;

use App\Entity\Disponibilite;
use App\Entity\User;
use App\Repository\DisponibiliteRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;

use Symfony\Component\Routing\Annotation\Route;

class RendezVousController extends AbstractController
{

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }

    #[Route('/{id}/rendezvous', name: 'app_rendezvous')]
    public function index(User $coach, Security $security): Response
    {
        $this->denyAccessUnlessGranted('ROLE_CLIENT');
        return $this->render('rendez_vous/index.html.twig', [
            'disponibilites' => $coach->getDisponibilites(),
        ]);
    }

    #[Route('/reserver/{id}', name: 'app_rendezvous_reserver', methods: ['POST'])]
    public function reserver(Request $request,Security $security, Disponibilite $disponibilite, DisponibiliteRepository $disponibiliteRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_CLIENT');
        $this->security = $security;
        $user = $this->security->getUser();

        if ($request->request->get('reserve') > 0) {
            $disponibilite->setUser($user);
            $disponibilite->setEtat('Reservé');
            $disponibiliteRepository->add($disponibilite);
            return $this->redirectToRoute('app_mes_rendezvous');
        }

        return $this->redirectToRoute('app_mes_rendezvous');
    }

    #[Route('/mesrendezvous', name: 'app_mes_rendezvous')]
    public function mesRendezvous(Security $security): Response
    {
        $this->denyAccessUnlessGranted('ROLE_CLIENT');
        $this->security = $security;
        $user = $this->security->getUser();
        
        return $this->render('rendez_vous/mesrendezvous.html.twig', [
            'rendezvous' => $user->getRendezvous(),
        ]);
    }

    #[Route('/mesrendezvous/{id}/annuler', name: 'app_mes_rendezvous_annuler')]
    public function annuler(Request $request, Security $security, Disponibilite $disponibilite): Response
    {
        $this->denyAccessUnlessGranted('ROLE_CLIENT');
        $this->security = $security;
        $user = $this->security->getUser();

        if ($request->request->get('annuler') > 0) {
            $disponibilite->setEtat('disponible');
            $user->removeRendezvou($disponibilite);
            $this->em->persist($disponibilite);
            $this->em->flush();

        }
        return $this->redirectToRoute('app_mes_rendezvous');
    }
}
