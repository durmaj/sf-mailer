<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MailerService
{
    private MailerInterface $mailer;

    private ValidatorInterface $validator;

    private LoggerInterface $logger;

    public function __construct(MailerInterface $mailer, ValidatorInterface $validator,LoggerInterface $logger)
    {
        $this->mailer = $mailer;
        $this->validator = $validator;
        $this->logger = $logger;
    }

    public function send(string $recipient): void
    {
        $errors = $this->validator->validate($recipient, new Email());

        if ($errors instanceof ConstraintViolationListInterface && count($errors) > 0) {
            $errorMessage = (string) $errors->get(0)->getMessage();
            $this->logger->error(sprintf('Invalid email address "%s": %s', $recipient, $errorMessage));
            return;
        }

        $email = $this->createConfirmationEmail($recipient);

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error(sprintf('Sending email to %s failed: %s', $recipient, $e->getMessage()));
        }
    }

    private function createConfirmationEmail(string $recipient): Email
    {
        return (new Email())
            ->from('noreply@openmobi.com')
            ->to($recipient)
            ->subject('Registration confirmation')
            ->text(
                sprintf(
                    'Hello, %s! Your account has been successfully registered. Welcome on board.', $recipient)
            );
    }
}
