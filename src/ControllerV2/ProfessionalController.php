<?php

namespace App\ControllerV2;

use App\FormV2\FilterUser\FilterUserFormModel;
use App\FormV2\FilterUser\FilterUserType;
use App\Repository\BeneficiaireRepository;
use App\Repository\MembreRepository;
use App\ServiceV2\PaginatorService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfessionalController extends AbstractController
{
    private const PAGINATION_RESULTS_LIMIT = 10;

    #[Route(path: '/beneficiaries', name: 'list_beneficiaries', methods: ['GET'])]
    #[IsGranted('ROLE_MEMBRE')]
    public function listBeneficiaries(Request $request, BeneficiaireRepository $repository, PaginatorService $paginator): Response
    {
        return $this->render('v2/professional/beneficiaries.html.twig', [
            'beneficiaries' => $paginator->create(
                $repository->findByAuthorizedProfessional($this->getUser()->getSubject()),
                $request->query->getInt('page', 1),
                self::PAGINATION_RESULTS_LIMIT,
            ),
            'form' => $this->createForm(FilterUserType::class, null, [
                'action' => $this->generateUrl('search_beneficiaries'),
                'attr' => ['data-controller' => 'ajax-list-filter'],
                'relays' => $this->getUser()->getSubjectMembre()->getAffiliatedRelaysWithBeneficiaryManagement(),
            ]),
        ]);
    }

    #[Route(
        path: '/beneficiaries/search',
        name: 'search_beneficiaries',
        methods: ['POST'],
        condition: 'request.isXmlHttpRequest()',
    )]
    #[IsGranted('ROLE_MEMBRE')]
    public function searchBeneficiaries(Request $request, BeneficiaireRepository $repository, PaginatorService $paginator): Response
    {
        $formModel = new FilterUserFormModel();
        $form = $this->createForm(FilterUserType::class, $formModel, [
            'action' => $this->generateUrl('search_beneficiaries'),
            'attr' => ['data-controller' => 'ajax-list-filter'],
            'relays' => $this->getUser()->getSubjectMembre()->getAffiliatedRelaysWithBeneficiaryManagement(),
        ])->handleRequest($request);

        return new JsonResponse([
            'html' => $this->render('v2/professional/_beneficiaries_list.html.twig', [
                'beneficiaries' => $paginator->create(
                    $repository->filterByAuthorizedProfessional(
                        $this->getUser()->getSubject(),
                        $formModel->search,
                        $formModel->relay,
                    ),
                    $request->query->getInt('page', 1),
                    self::PAGINATION_RESULTS_LIMIT,
                ),
                'form' => $form,
            ])->getContent(),
        ]);
    }

    #[Route(path: '/professionals', name: 'list_professionals', methods: ['GET'])]
    #[IsGranted('ROLE_MEMBRE')]
    public function listProfessionals(Request $request, MembreRepository $repository, PaginatorService $paginator): Response
    {
        return $this->render('v2/professional/professionals.html.twig', [
            'professionals' => $paginator->create(
                $repository->findByAuthorizedProfessional($this->getUser()->getSubject()),
                $request->query->getInt('page', 1),
                self::PAGINATION_RESULTS_LIMIT,
            ),
            'form' => $this->createForm(FilterUserType::class, null, [
                'action' => $this->generateUrl('search_professionals'),
                'attr' => ['data-controller' => 'ajax-list-filter'],
                'relays' => $this->getUser()->getSubjectMembre()->getAffiliatedRelaysWithProfessionalManagement(),
            ]),
        ]);
    }

    #[Route(
        path: '/professionals/search',
        name: 'search_professionals',
        methods: ['POST'],
        condition: 'request.isXmlHttpRequest()',
    )]
    #[IsGranted('ROLE_MEMBRE')]
    public function searchProfessionals(Request $request, MembreRepository $repository, PaginatorService $paginator): Response
    {
        $formModel = new FilterUserFormModel();
        $form = $this->createForm(FilterUserType::class, $formModel, [
            'action' => $this->generateUrl('search_professionals'),
            'attr' => ['data-controller' => 'ajax-list-filter'],
            'relays' => $this->getUser()->getSubjectMembre()->getAffiliatedRelaysWithProfessionalManagement(),
        ])->handleRequest($request);

        return new JsonResponse([
            'html' => $this->render('v2/professional/_professionals_list.html.twig', [
                'professionals' => $paginator->create(
                    $repository->findByAuthorizedProfessional(
                        $this->getUser()->getSubject(),
                        $formModel->search,
                        $formModel->relay,
                    ),
                    $request->query->getInt('page', 1),
                    self::PAGINATION_RESULTS_LIMIT,
                ),
                'form' => $form,
            ])->getContent(),
        ]);
    }
}
