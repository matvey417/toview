<?php

namespace App\Controller;

use App\Component\Mandarin\MandarinCardBindingDataComponent;
use App\Entity\CardBinding;
use App\Factory\CommentFactory;
use App\Repository\CardBindingRepository;
use App\Repository\StaffRepository;
use App\Service\CommentService;
use App\Service\Mandarin\BaseApiMandarin;
use App\Service\Mandarin\CardBinding\CardBindingService;
use App\Service\Mandarin\CardBinding\MandarinCardBindingResponse;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use HttpResponseException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/card', name: 'card_')]
class CardController extends AbstractController implements ClickHouseAccessLogInterface
{
    /**
     * @param Request $request
     * @param BaseApiMandarin $baseApiMandarin
     * @param MandarinCardBindingDataComponent $mandarinCardBindingDataComponent
     * @param CardBindingRepository $cardBindingRepository
     * @param CardBindingService $cardBindingService
     * @param EntityManagerInterface $entityManager
     *
     * @return RedirectResponse|Response
     * @throws HttpResponseException
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    #[Route('/binding', name: 'binding', methods: 'POST')]
    public function binding(
        Request                          $request,
        BaseApiMandarin                  $baseApiMandarin,
        MandarinCardBindingDataComponent $mandarinCardBindingDataComponent,
        CardBindingRepository            $cardBindingRepository,
        CardBindingService               $cardBindingService,
        EntityManagerInterface           $entityManager,
    ): RedirectResponse|Response {
        $countBindToday = $cardBindingRepository->countBindTodayByUser($this->getUser());
        if ($countBindToday > CardBinding::MAXIMUM_COUNT_BIND_CARD_PER_DAY) {
            $this->addFlash('danger', 'Вы превысили максимальное количество попыток добавления карты. Попробуйте еще раз завтра.');

            return $this->redirect($request->headers->get('referer'));
        }
        $cardBind = $cardBindingRepository->getPendingRecordsByUser($this->getUser());

        if (null === $cardBind) {
            $cardBind = $cardBindingService->createCardBinding($this->getUser());
            $entityManager->persist($cardBind);
            $entityManager->flush();
        }

        if ($cardBind->getStatus()->isSuccess()) {
            return $this->render('card/waitCallback.html.twig', ['card' => $cardBind]);
        }

        $mandarinDefaultResponse = $baseApiMandarin->cardBinding($mandarinCardBindingDataComponent);

        return $this->render('card/binding.html.twig', ['jsOperationId' => $mandarinDefaultResponse->getJsOperationId()]);
    }

    #[Route('/actual', name: 'actual')]
    public function current(): Response
    {
        $card = $this->getUser()->getActualCard();

        return $this->render('card/actual.html.twig', ['card' => $card]);
    }


    /**
     * @param Request $request
     * @param CardBindingService $cardBindingService
     * @param EntityManagerInterface $entityManager
     * @param CardBindingRepository $cardBindingRepository
     * @param CommentService $commentService
     * @param CommentFactory $commentFactory
     * @param StaffRepository $staffRepository
     * @return Response
     * @throws HttpResponseException
     * @throws InvalidArgumentException
     * @throws NonUniqueResultException
     */
    #[Route('/ajaxSaveCardBinding', name: 'ajaxSaveCardBinding', methods: 'POST')]
    public function ajaxSaveCardBinding(
        Request                $request,
        CardBindingService     $cardBindingService,
        EntityManagerInterface $entityManager,
        CardBindingRepository  $cardBindingRepository,
        CommentService         $commentService,
        CommentFactory         $commentFactory,
        StaffRepository        $staffRepository,
    ): Response {
        /** Проверяем что есть запись привязки карты в статусе "created" */
        $cardBind = $cardBindingRepository->getBindWithCreatedStatusByUser($this->getUser());

        if (null === $cardBind) {
            return new JsonResponse('Нет привязки карты в статусе created', 400);
        }

        $response = new MandarinCardBindingResponse($request->getContent());
        $cardBind = $cardBindingService->setBindingCard($cardBind, $response);
        $entityManager->persist($cardBind);
        $entityManager->flush();

        if ($cardBind->getStatus()->isFailed()) {
            $text = 'Недействительная карта.: ' . $cardBind->getCardNumber()->getValue();
            $comment = $commentFactory->makeError($text);

            $this->addFlash('danger', 'Недействительная карта. Убедитесь в правильности вводимых данных и повторите попытку');
        } else {
            $text = 'Карта успешно привязана: ' . $cardBind->getCardNumber()->getValue();
            $comment = $commentFactory->makeSuccess($text);

            $this->addFlash('success', ' Через минуту ваша карта появится в кошельке -
                    Вы увидите её в разделе "Моя карта"
                    Платить можно сразу, указывать данные не придется.');
        }


        $commentService->add($comment, $staffRepository->findRobot(), $cardBind->getUser());

        return new JsonResponse('ok', 200);
    }
}
