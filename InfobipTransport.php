<?php
/**
 * Infobip transport
 *
 * @author Maxim Petrovich <m.petrovich@artox.com>
 */
namespace  ArtoxLab\Component\Notifier\Bridge\Infobip;

use Symfony\Component\Notifier\Exception\LogicException;
use Symfony\Component\Notifier\Exception\TransportException;
use Symfony\Component\Notifier\Message\MessageInterface;
use Symfony\Component\Notifier\Transport\AbstractTransport;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class InfobipTransport extends AbstractTransport
{

    public const HOST = 'api.infobip.com';

    /**
     * Login
     *
     * @var string
     */
    private string $token;

    /**
     * Sender name
     *
     * @var string
     */
    private string $from;

    /**
     * SmsLineTransport constructor.
     *
     * @param string                        $token      Password
     * @param string                        $from       Sender name
     * @param HttpClientInterface|null      $client     Http client
     * @param EventDispatcherInterface|null $dispatcher Event dispatcher
     */
    public function __construct(
        string $token,
        string $from,
        HttpClientInterface $client = null,
        EventDispatcherInterface $dispatcher = null
    ) {
        $this->token = $token;
        $this->from  = $from;

        parent::__construct($client, $dispatcher);
    }

    /**
     * Send message
     *
     * @param MessageInterface $message Message
     *
     * @return void
     */
    protected function doSend(MessageInterface $message): void
    {
        if (false === $message instanceof SmsMessage) {
            throw new LogicException(sprintf('The "%s" transport only supports instances of "%s" (instance of "%s" given).', __CLASS__, SmsMessage::class, get_debug_type($message)));
        }

        $endpoint = sprintf('https://%s/sms/2/text/advanced', $this->getEndpoint());
        $response = $this->client->request(
            'POST',
            $endpoint,
            [
                'headers' => [
                    'Authorization' => 'App ' . $this->token,
                ],
                'json'    => [
                    'messages' => [
                        [
                            'from'         => $this->from,
                            'destinations' => [
                                [
                                    'to' => $message->getPhone(),
                                ],
                            ],
                            'text'         => $message->getSubject(),
                        ],
                    ],
                ],
            ]
        );

        if (200 !== $response->getStatusCode()) {
            $error = $response->toArray(false)['requestError']['serviceException'];

            throw new TransportException(
                'Unable to send the SMS: ' . $error['text'] . sprintf(' (id %s).', $error['messageId']),
                $response
            );
        }
    }

    /**
     * Supports
     *
     * @param MessageInterface $message Message
     *
     * @return bool
     */
    public function supports(MessageInterface $message): bool
    {
        return $message instanceof SmsMessage;
    }

    /**
     * To string
     *
     * @return string
     */
    public function __toString(): string
    {
        return sprintf('infobip://%s?from=%s', $this->getEndpoint(), $this->from);
    }

}
