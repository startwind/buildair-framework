<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailService
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly string $appUrl,
        private readonly string $fromEmail,
    ) {
    }

    public function sendVerificationEmail(User $user): void
    {
        $verifyUrl = rtrim($this->appUrl, '/') . '/verify/' . $user->getVerificationToken();

        $email = (new Email())
            ->from($this->fromEmail)
            ->to((string) $user->getEmail())
            ->subject('Please verify your email address')
            ->text("Welcome! Please verify your email by visiting: {$verifyUrl}")
            ->html(
                "<p>Welcome!</p>"
                . "<p>Please verify your email address by clicking the link below:</p>"
                . "<p><a href=\"{$verifyUrl}\">{$verifyUrl}</a></p>"
                . "<p>If you did not register, you can ignore this email.</p>"
            );

        $this->mailer->send($email);
    }
}
