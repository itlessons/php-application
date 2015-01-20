<?php

namespace Application\Provider;

trait SwiftMailerTrait
{
    /**
     * Sends an email.
     *
     * @param \Swift_Message $message A \Swift_Message instance
     * @param array $failedRecipients An array of failures by-reference
     *
     * @return int The number of sent messages
     */
    public function mail(\Swift_Message $message, &$failedRecipients = null)
    {
        return $this->getMailer()->send($message, $failedRecipients);
    }

    /**
     * Sends emails from the spool
     *
     * @param int $messageLimit The maximum number of messages to sends.
     * @param int $timeLimit The time limit for sending messages (in seconds).
     * @param int $recoverTimeout The timeout for recovering messages that have taken too long to send (in seconds).
     * @return int The number of sent emails
     */
    public function mailFlushQueue($messageLimit = 10, $timeLimit = 30, $recoverTimeout = -1)
    {
        $transport = $this->getMailer()->getTransport();

        if ($transport instanceof \Swift_Transport_SpoolTransport) {

            $spool = $transport->getSpool();

            if ($spool instanceof \Swift_ConfigurableSpool) {
                $spool->setMessageLimit($messageLimit);
                $spool->setTimeLimit($timeLimit);
            }

            if ($spool instanceof \Swift_FileSpool) {
                if ($recoverTimeout > 0) {
                    $spool->recover($recoverTimeout);
                } else {
                    $spool->recover();
                }
            }

            $sent = $spool->flushQueue($this->make('swiftmailer.transport'));
            return $sent;
        }

        return -1;
    }

    /**
     * @return \Swift_Mailer
     */
    public function getMailer()
    {
        return $this->make('mailer');
    }
}