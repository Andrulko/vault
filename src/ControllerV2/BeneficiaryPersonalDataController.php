<?php

namespace App\ControllerV2;

use App\Entity\Beneficiaire;
use App\Entity\Contact;
use App\Entity\DonneePersonnelle;
use App\Entity\Dossier;
use App\Entity\Evenement;
use App\Entity\Note;
use App\FormV2\ContactType;
use App\FormV2\EventType;
use App\FormV2\FolderType;
use App\FormV2\NoteType;
use App\FormV2\SearchType;
use App\ManagerV2\ContactManager;
use App\ManagerV2\DocumentManager;
use App\ManagerV2\EventManager;
use App\ManagerV2\FolderableItemManager;
use App\ManagerV2\FolderManager;
use App\ManagerV2\NoteManager;
use App\Repository\DossierRepository;
use App\ServiceV2\PaginatorService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/beneficiary')]
class BeneficiaryPersonalDataController extends AbstractController
{
    #[Route(path: '/{id}/notes', name: 'note_list', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted('UPDATE', 'beneficiary')]
    public function listNotes(
        Request $request,
        Beneficiaire $beneficiary,
        NoteManager $manager,
        PaginatorService $paginator,
    ): Response {
        return $this->renderForm('v2/vault/note/index.html.twig', [
            'beneficiary' => $beneficiary,
            'notes' => $paginator->create(
                $manager->getNotes($beneficiary),
                $request->query->getInt('page', 1),
            ),
            'form' => $this->getSearchForm(
                $this->generateUrl('note_search', ['id' => $beneficiary->getId()]),
            )->handleRequest($request),
        ]);
    }

    #[Route(
        path: '/{id}/notes/search',
        name: 'note_search',
        requirements: ['id' => '\d+'],
        methods: ['POST'],
        condition: 'request.isXmlHttpRequest()',
    )]
    #[IsGranted('UPDATE', 'beneficiary')]
    public function searchNotes(
        Beneficiaire $beneficiary,
        Request $request,
        NoteManager $manager,
        PaginatorService $paginator
    ): JsonResponse {
        $searchForm = $this->getSearchForm(
            $this->generateUrl('note_search', ['id' => $beneficiary->getId()]),
        )->handleRequest($request);

        $search = $searchForm->get('search')->getData();

        return new JsonResponse([
            'html' => $this->renderForm('v2/vault/note/_list.html.twig', [
                'notes' => $paginator->create(
                    $manager->getNotes($beneficiary, $search),
                    $request->query->getInt('page', 1),
                ),
                'beneficiary' => $beneficiary,
                'form' => $searchForm,
            ])->getContent(),
        ]);
    }

    #[Route(
        path: '/{id}/note/create',
        name: 'note_create',
        requirements: ['id' => '\d+'],
        methods: ['GET', 'POST'],
    )]
    #[IsGranted('UPDATE', 'beneficiary')]
    public function createNote(Beneficiaire $beneficiary, Request $request, EntityManagerInterface $em): Response
    {
        $note = new Note($beneficiary);
        $form = $this->getCreateForm(
            NoteType::class,
            $note,
            $this->generateUrl('note_create', ['id' => $beneficiary->getId()])
        )->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($note);
            $em->flush();
            $this->addFlash('success', 'note_created');

            return $this->redirectToRoute('note_list', ['id' => $beneficiary->getId()]);
        }

        return $this->renderForm('v2/vault/note/create.html.twig', [
            'form' => $form,
            'beneficiary' => $beneficiary,
        ]);
    }

    #[Route(path: '/{id}/contacts', name: 'contact_list', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted('UPDATE', 'beneficiary')]
    public function listContacts(
        Request $request,
        Beneficiaire $beneficiary,
        ContactManager $manager,
        PaginatorService $paginator,
    ): Response {
        return $this->renderForm('v2/vault/contact/index.html.twig', [
            'beneficiary' => $beneficiary,
            'contacts' => $paginator->create(
                $manager->getContacts($beneficiary),
                $request->query->getInt('page', 1),
            ),
            'form' => $this->getSearchForm(
                $this->generateUrl('contact_search', ['id' => $beneficiary->getId()]),
            )->handleRequest($request),
        ]);
    }

    #[Route(
        path: '/{id}/contacts/search',
        name: 'contact_search',
        requirements: ['id' => '\d+'],
        methods: ['POST'],
        condition: 'request.isXmlHttpRequest()',
    )]
    #[IsGranted('UPDATE', 'beneficiary')]
    public function searchContacts(
        Beneficiaire $beneficiary,
        Request $request,
        ContactManager $manager,
        PaginatorService $paginator
    ): Response {
        $searchForm = $this->getSearchForm(
            $this->generateUrl('contact_search', ['id' => $beneficiary->getId()]),
        )->handleRequest($request);

        $search = $searchForm->get('search')->getData();

        return new JsonResponse([
            'html' => $this->renderForm('v2/vault/contact/_list.html.twig', [
                'contacts' => $paginator->create(
                    $manager->getContacts($beneficiary, $search),
                    $request->query->getInt('page', 1),
                ),
                'beneficiary' => $beneficiary,
                'form' => $searchForm,
            ])->getContent(),
        ]);
    }

    #[Route(
        path: '/{id}/contact/create',
        name: 'contact_create',
        requirements: ['id' => '\d+'],
        methods: ['GET', 'POST'],
    )]
    #[IsGranted('UPDATE', 'beneficiary')]
    public function createContact(Beneficiaire $beneficiary, Request $request, EntityManagerInterface $em): Response
    {
        $contact = new Contact($beneficiary);
        $form = $this->getCreateForm(
            ContactType::class,
            $contact,
            $this->generateUrl('contact_create', ['id' => $beneficiary->getId()])
        )->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($contact);
            $em->flush();
            $this->addFlash('success', 'contact_created');

            return $this->redirectToRoute('contact_list', ['id' => $beneficiary->getId()]);
        }

        return $this->renderForm('v2/vault/contact/create.html.twig', [
            'form' => $form,
            'beneficiary' => $beneficiary,
        ]);
    }

    #[Route(
        path: '/{id}/events',
        name: 'event_list',
        requirements: ['id' => '\d+'],
        methods: ['GET'],
    )]
    #[IsGranted('UPDATE', 'beneficiary')]
    public function listEvents(
        Request $request,
        Beneficiaire $beneficiary,
        EventManager $manager,
        PaginatorService $paginator,
    ): Response {
        return $this->renderForm('v2/vault/event/index.html.twig', [
            'beneficiary' => $beneficiary,
            'events' => $paginator->create(
                $manager->getEvents($beneficiary),
                $request->query->getInt('page', 1),
            ),
            'form' => $this->getSearchForm(
                $this->generateUrl('event_search', ['id' => $beneficiary->getId()]),
            )->handleRequest($request),
        ]);
    }

    #[Route(
        path: '/{id}/events/search',
        name: 'event_search',
        requirements: ['id' => '\d+'],
        methods: ['POST'],
        condition: 'request.isXmlHttpRequest()',
    )]
    #[IsGranted('UPDATE', 'beneficiary')]
    public function searchEvents(
        Request $request,
        Beneficiaire $beneficiary,
        EventManager $manager,
        PaginatorService $paginator
    ): Response {
        $searchForm = $this->getSearchForm(
            $this->generateUrl('event_search', ['id' => $beneficiary->getId()]),
        )->handleRequest($request);

        $search = $searchForm->get('search')->getData();

        return new JsonResponse([
            'html' => $this->renderForm('v2/vault/event/_list.html.twig', [
                'events' => $paginator->create(
                    $manager->getEvents($beneficiary, $search),
                    $request->query->getInt('page', 1),
                ),
                'beneficiary' => $beneficiary,
                'form' => $searchForm,
            ])->getContent(),
        ]);
    }

    #[Route(
        path: '/{id}/event/create',
        name: 'event_create',
        requirements: ['id' => '\d+'],
        methods: ['GET', 'POST'],
    )]
    #[IsGranted('UPDATE', 'beneficiary')]
    public function createEvent(Request $request, Beneficiaire $beneficiary, EntityManagerInterface $em): Response
    {
        $event = new Evenement($beneficiary);
        $form = $this->getCreateForm(
            EventType::class,
            $event,
            $this->generateUrl('event_create', ['id' => $beneficiary->getId()])
        )->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($event);
            $em->flush();
            $this->addFlash('success', 'event_created');

            return $this->redirectToRoute('event_list', ['id' => $beneficiary->getId()]);
        }

        return $this->renderForm('v2/vault/event/form.html.twig', [
            'form' => $form,
            'beneficiary' => $beneficiary,
            'event' => $event,
        ]);
    }

    #[Route(path: '/{id}/documents', name: 'document_list', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted('UPDATE', 'beneficiary')]
    public function listDocuments(
        Request $request,
        Beneficiaire $beneficiary,
        FolderableItemManager $manager,
        PaginatorService $paginator,
    ): Response {
        return $this->renderForm('v2/vault/document/index.html.twig', [
            'beneficiary' => $beneficiary,
            'foldersAndDocuments' => $paginator->create(
                $manager->getFoldersAndDocumentsWithUrl($beneficiary),
                $request->query->getInt('page', 1),
            ),
            'form' => $this->getSearchForm(
                $this->generateUrl('document_search', ['id' => $beneficiary->getId()]),
            )->handleRequest($request),
        ]);
    }

    #[Route(
        path: '/{id}/documents/upload',
        name: 'document_upload',
        requirements: ['id' => '\d+'],
        methods: ['POST'],
    )]
    #[IsGranted('UPDATE', 'beneficiary')]
    public function upload(
        Request $request,
        Beneficiaire $beneficiary,
        DocumentManager $manager,
        DossierRepository $folderRepository
    ): Response {
        if (!$files = $request->files->get('files')) {
            return new Response(null, 400);
        }
        $folderId = $request->query->get('folder');

        $manager->uploadFiles(
            $files,
            $beneficiary,
            $folderId ? $folderRepository->find($folderId) : null
        );

        return new Response(null, 201);
    }

    #[Route(
        path: '/{id}/documents/search',
        name: 'document_search',
        requirements: ['id' => '\d+'],
        methods: ['POST'],
        condition: 'request.isXmlHttpRequest()',
    )]
    #[IsGranted('UPDATE', 'beneficiary')]
    public function searchDocuments(
        Request $request,
        Beneficiaire $beneficiary,
        FolderableItemManager $manager,
        PaginatorService $paginator,
    ): JsonResponse {
        $searchForm = $this->getSearchForm(
            $this->generateUrl('document_search', ['id' => $beneficiary->getId()]),
        )->handleRequest($request);

        $search = $searchForm->get('search')->getData();

        return new JsonResponse([
            'html' => $this->renderForm('v2/vault/document/_list.html.twig', [
                'foldersAndDocuments' => $paginator->create(
                    $manager->getFoldersAndDocumentsWithUrl($beneficiary, null, $search),
                    $request->query->getInt('page', 1),
                ),
                'beneficiary' => $beneficiary,
                'form' => $searchForm,
            ])->getContent(),
        ]);
    }

    #[Route(
        path: '/{id}/folder/{folderId}/search',
        name: 'folder_search',
        requirements: ['id' => '\d+', 'folderId' => '\d+'],
        methods: ['POST'],
        condition: 'request.isXmlHttpRequest()',
    )]
    #[ParamConverter('folder', class: 'App\Entity\Dossier', options: ['id' => 'folderId'])]
    #[IsGranted('UPDATE', 'beneficiary')]
    public function searchFolders(
        Request $request,
        Beneficiaire $beneficiary,
        Dossier $folder,
        FolderableItemManager $manager,
        PaginatorService $paginator,
    ): JsonResponse {
        $this->denyAccessUnlessGranted('UPDATE', $folder);

        $searchForm = $this->getSearchForm(
            $this->generateUrl('folder_search', ['id' => $beneficiary->getId(), 'folderId' => $folder->getId()]),
        )->handleRequest($request);

        $search = $searchForm->get('search')->getData();

        return new JsonResponse([
            'html' => $this->renderForm('v2/vault/document/_list.html.twig', [
                'foldersAndDocuments' => $paginator->create(
                    $manager->getFoldersAndDocumentsWithUrl($beneficiary, $folder, $search),
                    $request->query->getInt('page', 1),
                ),
                'beneficiary' => $beneficiary,
                'form' => $searchForm,
            ])->getContent(),
        ]);
    }

    #[Route(
        path: '/{id}/folder/create',
        name: 'folder_create',
        requirements: ['id' => '\d+'],
        methods: ['GET', 'POST'],
    )]
    #[IsGranted('UPDATE', 'beneficiary')]
    public function createFolder(
        Request $request,
        Beneficiaire $beneficiary,
        EntityManagerInterface $em,
        FolderManager $manager
    ): Response {
        $folder = (new Dossier())->setBeneficiaire($beneficiary);
        $form = $this->getCreateForm(
            FolderType::class,
            $folder,
            $this->generateUrl('folder_create', ['id' => $beneficiary->getId()]),
        )->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($folder);
            $em->flush();

            return $this->redirectToRoute('document_list', ['id' => $beneficiary->getId()]);
        }

        return $this->renderForm('v2/vault/folder/create.html.twig', [
            'form' => $form,
            'beneficiary' => $beneficiary,
            'autocompleteNames' => $manager->getAutocompleteFolderNames(),
        ]);
    }

    private function getSearchForm(string $url): FormInterface
    {
        return $this->createForm(SearchType::class, null, [
            'attr' => ['data-controller' => 'ajax-list-filter'],
            'action' => $url,
        ]);
    }

    private function getCreateForm(string $type, DonneePersonnelle $entity, string $url): FormInterface
    {
        return $this->createForm($type, $entity, [
            'action' => $url,
            'private' => $this->isLoggedInUser($entity->getBeneficiaire()->getUser()),
        ]);
    }
}
