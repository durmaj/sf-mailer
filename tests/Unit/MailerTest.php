<?php

namespace App\Tests\Unit;

use App\Service\MailerService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Exception\RfcComplianceException;

use Symfony\Component\Validator\Validator\ValidatorInterface;

class MailerTest extends TestCase
{
    private MailerInterface $mailer;
    private ValidatorInterface $validator;
    private LoggerInterface $logger;
    protected function setUp(): void
    {
        $this->mailer = $this->createMock(MailerInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    public function testSendValidEmail(): void
    {
        $recipient = 'test@example.com';

        $this->mailer->expects($this->once())
            ->method('send')
            ->with($this->callback(function (Email $email) use ($recipient) {
                return $email->getTo()[0]->getAddress() === $recipient;
            }));

        $this->validator->expects($this->once())
            ->method('validate')
            ->with($recipient, new Email())
            ->willReturn([]);

        $this->logger->expects($this->never())
            ->method('error');

        $service = new MailerService($this->mailer, $this->validator, $this->logger);
        $service->send($recipient);
    }

    public function testSendInvalidEmail(): void
    {
        self::expectException(RfcComplianceException::class);

        $recipient = 'invalid-email';

        $mailerService = new MailerService($this->mailer, $this->validator, $this->logger);
        $mailerService->send($recipient);
    }

    public function testSendTransportException(): void
    {

        $this->logger->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Sending email to test@example.com failed: '));

        $mailerService = new MailerService($this->mailer, $this->validator, $this->logger);

        $this->mailer->method('send')
            ->willThrowException($this->createMock(TransportExceptionInterface::class));

        $mailerService->send('test@example.com');
    }
}
