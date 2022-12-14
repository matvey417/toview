<?php

namespace App\Telegram;

use App\Entity\User;
use App\Message\TelegramNotification;
use App\Service\AppConfig;
use DateTimeImmutable;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Security;
use Throwable;

class TelegramBot
{
    /** @var User|null $user - текущий пользователь */
    protected ?User $user;

    /**
     * @param MessageBusInterface $bus - используется для отправки задач в очередь RabbitMq
     * @param Security $security
     * @param AppConfig $appConfig
     */
    public function __construct(protected MessageBusInterface $bus, Security $security, protected AppConfig $appConfig)
    {
        $this->user = $security->getUser();
    }

    /**
     * Отправка сообщения в телеграм бота
     *
     * @param Throwable|string $error
     * @return void
     */
    public function sendErrorToDevelopers(Throwable|string $error)
    {
        if (!$this->appConfig->isProd()) {
            return;
        }

        $message = $this->prepareMessageForSend($error);
        $this->addToQueue($message);
    }

    /**
     * Подготавливает данные для отправки сообщения в телеграм
     *
     * @param Throwable|string $error
     *
     * @return string
     */
    protected function prepareMessageForSend(Throwable|string $error): string
    {
        $userId = 'Не залогинен';

        if (null !== $this->user) {
            $userId = $this->user->getId();
        }

        $trace = 'trace undefined';
        if (is_string($error)) {
            $code = 500;
            $msg = $error;
            $category = 'StringError';
            ob_start();
            debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            $trace = ob_get_clean();
        } elseif ($error instanceof Throwable) {
            $code = $error->getCode();
            $msg = $error->getMessage();
            $trace = $error->getTraceAsString();
            $category = get_class($error);
        }

        $trace = $this->clearTrace($trace);
        $action = '';
        if (isset($_SERVER['REQUEST_URI'])) {
            $action = $_SERVER['REQUEST_URI'];
        }

        $lines = [
            "\n" . '<b>Действие:</b> ' . urldecode($action),
            '<b>User ID:</b> ' . $userId,
            '<b>Сообщение:</b> ' . $msg,
            '<b>Код:</b> ' . $code,
            '<b>Категория:</b> ' . $category,
            '<b>StackTrace:</b> <pre>' . $trace . '</pre>',
        ];

        $message = implode("\r\n", $lines);

        return $this->cutMessage($message);

    }

    /**
     * Очищает stack trace от неинформативных строк
     *
     * @param string $trace
     *
     * @return string
     */
    protected function clearTrace(string $trace): string
    {
        $smallTrace = [];
        if (!empty($trace)) {
            $symbols = ["\n", '#'];

            foreach ($symbols as $symbol) {
                foreach (explode($symbol, $trace) as $item) {
                    if (false === stripos($item, 'vendor')
                        && false === stripos($item, '->run()')
                    ) {
                        $smallTrace[] = $item;
                    }
                }
                $trace = implode($symbol, $smallTrace);
            }
            $trace = str_replace('/var/www', '.', $trace);
        }

        return $trace;
    }

    /**
     * Обрезаем сообщение, для отправки в телеграм, Максимальный размер строки для отправки в телеграм 4096 символов
     *
     * @param string $message
     *
     * @return string
     */
    protected function cutMessage(string $message): string
    {
        if (strlen($message) >= 4096) {
            $message = mb_substr($message, 0, 4050);
            if (str_contains($message, '<pre>') && !str_contains($message, '</pre>')) {
                $message .= '</pre>';
            }
            $message .= ' Всё сообщение не влезло!';
        }

        return $message;
    }
}
