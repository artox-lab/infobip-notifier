<?php
/**
 * Infobip transport factory
 *
 * @author Maxim Petrovich <m.petrovich@artox.com>
 */

namespace ArtoxLab\Component\Notifier\Bridge\Infobip;

use Symfony\Component\Notifier\Exception\UnsupportedSchemeException;
use Symfony\Component\Notifier\Transport\AbstractTransportFactory;
use Symfony\Component\Notifier\Transport\Dsn;
use Symfony\Component\Notifier\Transport\TransportInterface;

class InfobipTransportFactory extends AbstractTransportFactory
{

    /**
     * Supported schemes
     *
     * @return array|string[]
     */
    protected function getSupportedSchemes(): array
    {
        return ['infobip'];
    }

    /**
     * Create
     *
     * @param Dsn $dsn DSN
     *
     * @return TransportInterface
     */
    public function create(Dsn $dsn): TransportInterface
    {
        $scheme = $dsn->getScheme();
        $token  = $this->getUser($dsn);
        $from   = $dsn->getOption('from');
        $host   = 'default' === $dsn->getHost() ? null : $dsn->getHost();
        $port   = $dsn->getPort();

        if ('infobip' === $scheme) {
            $transport = new InfobipTransport($token, $from, $this->client, $this->dispatcher);
            $transport->setHost($host);
            $transport->setPort($port);

            return $transport;
        }

        throw new UnsupportedSchemeException($dsn, 'infobip', $this->getSupportedSchemes());
    }

}
