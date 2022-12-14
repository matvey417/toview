<?php

namespace App\Controller;

use App\Entity\Sms;
use App\Entity\User;
use App\Factory\CommentFactory;
use App\Factory\MoneyFactory;
use App\Factory\SubscriptionStatusFactory;
use App\Repository\PaymentRepository;
use App\Repository\SmsRepository;
use App\Repository\StaffRepository;
use App\Service\CheckOnline\CheckOnlineComponent;
use App\Service\CheckService;
use App\Service\CommentService;
use App\Service\Mandarin\CardBinding\CardBindingService;
use App\Service\Mandarin\CardBinding\CardService;
use App\Service\Mandarin\CardBinding\MandarinCardBindingCallbackResponse;
use App\Service\Mandarin\MerchantComponent;
use App\Service\Mandarin\Transaction\TransactionService;
use App\Service\Mandarin\Transaction\MandarinPayCallBackResponse;
use App\Service\PaymentService;
use App\Service\StreamTelecom\StreamTelecomSmsCallbackResponse;
use App\Telegram\TelegramBot;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

#[Route('/api', name: 'api_')]
class ApiController extends AbstractController
{
    /**
     * Обработка коллбека от мандарина по привязке карты
     *
     * @param Request $request
     * @param MerchantComponent $merchantComponent
     * @param CardBindingService $cardBindingService
     * @param EntityManagerInterface $entityManager
     * @param CardService $cardService
     * @param TelegramBot $bot
     *
     * @return Response
     * @throws \Doctrine\DBAL\Exception
     */
    #[Route('/cardBindingCallback', name: 'cardBindingCallback')]
    public function cardBindingCallback(
        Request                $request,
        MerchantComponent      $merchantComponent,
        CardBindingService     $cardBindingService,
        EntityManagerInterface $entityManager,
        CardService            $cardService,
        TelegramBot            $bot,
    ): Response {
        $fields = $request->query->all();
        $connection = $entityManager->getConnection();
        try {
            $mandarinCallbackResponse = new MandarinCardBindingCallbackResponse($fields, $merchantComponent);

            $connection->beginTransaction();
            $cardBind = $cardBindingService->updateBindingCardByCallback($mandarinCallbackResponse);
            $entityManager->persist($cardBind);
            $entityManager->flush();

            if ($cardBind->getStatus()->isCompleted()) {
                /** Удаляем актуальную карту по клиенту */
                $cardBind->getUser()->removeActualCard();

                /** Создаем новую карту */
                $card = $cardService->createCardByMandarinCallback($mandarinCallbackResponse, $cardBind->getUser());
                $entityManager->persist($card);
                $entityManager->flush();
            }

            $connection->commit();
        } catch (Throwable $exception) {
            $connection->rollBack();
            $bot->sendErrorToDevelopers($exception);
        }

        return new Response('', 200);
    }

    /**
     * Обработка коллбека от Мандарина, сохранение данных платежки
     *
     * @param Request $request
     * @param MerchantComponent $merchantComponent
     * @param EntityManagerInterface $entityManager
     * @param PaymentService $paymentService
     * @param TelegramBot $bot
     * @param PaymentRepository $paymentRepository
     * @param SubscriptionStatusFactory $subscriptionStatusFactory
     * @param MoneyFactory $moneyFactory
     * @param CommentService $commentService
     * @param CommentFactory $commentFactory
     * @param StaffRepository $staffRepository
     * @param TransactionService $transactionService
     * @param CheckOnlineComponent $checkOnlineComponent
     * @param CheckService $checkService
     *
     * @return Response
     * @throws NonUniqueResultException
     * @throws \Doctrine\DBAL\Exception
     * @todo временное решение, захардкодил имеющийся коллбек платежки
     */
    #[Route('/transactionPayCallback', name: 'transactionPayCallback')]
    public function payCallback(
        Request                   $request,
        MerchantComponent         $merchantComponent,
        EntityManagerInterface    $entityManager,
        PaymentService            $paymentService,
        TelegramBot               $bot,
        PaymentRepository         $paymentRepository,
        SubscriptionStatusFactory $subscriptionStatusFactory,
        MoneyFactory              $moneyFactory,
        CommentService            $commentService,
        CommentFactory            $commentFactory,
        StaffRepository           $staffRepository,
        TransactionService        $transactionService,
        CheckOnlineComponent      $checkOnlineComponent,
        CheckService              $checkService,
    ): Response {

        $fields = $request->query->all();
        $orderId = $fields['orderId'];
        $payment = $paymentRepository->findByOrderId($orderId);

        /** @var User $user */
        $user = $payment->getUser();
        $subscription = $payment?->getSubscription();

        $connection = $entityManager->getConnection();
        try {
            $mandarinCallbackResponse = new MandarinPayCallBackResponse($fields, $merchantComponent, $moneyFactory);

            /** todo нужно закрыть предыдущие подписки неуспешно если не сакцесс */
            if ($subscription !== null && $mandarinCallbackResponse->isSuccess()) {
                $subscription->setStatus($subscriptionStatusFactory->makeActive());
                $entityManager->persist($subscription);
                $entityManager->flush();
            }

            /** todo одинаковые куски, можно вынести в отдельный метод или класс */
            $connection->beginTransaction();
            $payment = $paymentService->setPaymentFromCallbackData($mandarinCallbackResponse);
            $entityManager->flush();

            if ($mandarinCallbackResponse->isSuccess()) {
                $checkOnlineResponse = $checkOnlineComponent->send(
                    $mandarinCallbackResponse->getEmail(),
                    $mandarinCallbackResponse->getPrice()
                );
                $check = $checkService->createCheck($payment, $checkOnlineResponse);
                $entityManager->persist($check);
                $entityManager->flush();

                if ($checkOnlineResponse->hasError()) {
                    $bot->sendErrorToDevelopers($checkOnlineResponse->getError());
                }
            }

            $transactionService->createAggregateIncoming($payment);

            $text = 'Успешный платеж. orderId ' . $orderId;
            $comment = $commentFactory->makeSuccess($text);
            $commentService->add($comment, $staffRepository->findRobot(), $user);

            $connection->commit();
        } catch (Throwable $exception) {
            $connection->rollBack();

            $text = 'Ошибка платежа. orderId ' . $orderId;
            $comment = $commentFactory->makeError($text);
            $commentService->add($comment, $staffRepository->findRobot(), $user);

            $bot->sendErrorToDevelopers($exception);
        }

        return new Response('', 200);
    }

    /**
     * Обработка коллбека от Мандарина, сохранение данных платежки
     * todo временное решение, захардкодил имеющийся коллбек платежки
     *
     * @param Request $request
     * @param MerchantComponent $merchantComponent
     * @param EntityManagerInterface $entityManager
     * @param PaymentService $paymentService
     * @param TelegramBot $bot
     * @param PaymentRepository $paymentRepository
     * @param MoneyFactory $moneyFactory
     * @param CommentService $commentService
     * @param CommentFactory $commentFactory
     * @param StaffRepository $staffRepository
     * @param TransactionService $transactionService
     *
     * @return Response
     * @throws NonUniqueResultException
     * @throws \Doctrine\DBAL\Exception
     */
    #[Route('/transactionPayoutCallback', name: 'transactionPayoutCallback')]
    public function payoutCallback(
        Request                $request,
        MerchantComponent      $merchantComponent,
        EntityManagerInterface $entityManager,
        PaymentService         $paymentService,
        TelegramBot            $bot,
        PaymentRepository      $paymentRepository,
        MoneyFactory           $moneyFactory,
        CommentService         $commentService,
        CommentFactory         $commentFactory,
        StaffRepository        $staffRepository,
        TransactionService     $transactionService,
        CheckOnlineComponent   $checkOnlineComponent,
        CheckService           $checkService,
    ): Response {

        $fields = $request->query->all();
        $orderId =  $request->get('orderId');
        $orderId = $fields['orderId'];
        $payment = $paymentRepository->findByOrderId($orderId);

        /** @var User $user */
        $user = $payment->getUser();

        $connection = $entityManager->getConnection();
        try {
            $mandarinCallbackResponse = new MandarinPayCallBackResponse($fields, $merchantComponent, $moneyFactory);

            $connection->beginTransaction();
            $payment = $paymentService->setPaymentFromCallbackData($mandarinCallbackResponse);
            $entityManager->flush();

            if ($mandarinCallbackResponse->isSuccess()) {
                $checkOnlineResponse = $checkOnlineComponent->send(
                    $mandarinCallbackResponse->getEmail(),
                    $mandarinCallbackResponse->getPrice()
                );
                $check = $checkService->createCheck($payment, $checkOnlineResponse);
                $entityManager->persist($check);
                $entityManager->flush();

                if ($checkOnlineResponse->hasError()) {
                    $bot->sendErrorToDevelopers($checkOnlineResponse->getError());
                }
            }

            $transactionService->createAggregateOutgoing($payment);

            $text = 'Успешный платеж. orderId ' . $orderId;
            $comment = $commentFactory->makeSuccess($text);
            $commentService->add($comment, $staffRepository->findRobot(), $user);

            $connection->commit();
        } catch (Throwable $exception) {
            $connection->rollBack();

            $text = 'Ошибка платежа. orderId ' . $orderId;
            $comment = $commentFactory->makeError($text);
            $commentService->add($comment, $staffRepository->findRobot(), $user);

            $bot->sendErrorToDevelopers($exception);
        }

        return new Response('', 200);
    }
}