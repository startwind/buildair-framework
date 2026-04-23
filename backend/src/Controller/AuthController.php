<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/auth')]
class AuthController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly MailService $mailService,
        private readonly ValidatorInterface $validator,
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly bool $doubleOptIn,
    ) {
    }

    #[Route('/login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';

        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user || !$this->passwordHasher->isPasswordValid($user, $password)) {
            return $this->json(['error' => 'Invalid credentials.'], Response::HTTP_UNAUTHORIZED);
        }

        if ($this->doubleOptIn && !$user->isVerified()) {
            return $this->json(
                ['error' => 'Your account is not verified. Please check your email.'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        return $this->json(['token' => $this->jwtManager->create($user)]);
    }

    #[Route('/register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';

        $violations = $this->validator->validate($email, [
            new Assert\NotBlank(),
            new Assert\Email(),
        ]);

        if (count($violations) > 0) {
            return $this->json(['error' => 'Invalid email address.'], Response::HTTP_BAD_REQUEST);
        }

        if (strlen($password) < 8) {
            return $this->json(['error' => 'Password must be at least 8 characters.'], Response::HTTP_BAD_REQUEST);
        }

        if ($this->em->getRepository(User::class)->findOneBy(['email' => $email])) {
            return $this->json(['error' => 'This email is already registered.'], Response::HTTP_CONFLICT);
        }

        $user = new User();
        $user->setEmail($email);
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        if ($this->doubleOptIn) {
            $user->setVerificationToken(Uuid::v4()->toRfc4122());
            $user->setIsVerified(false);
        } else {
            $user->setIsVerified(true);
        }

        $this->em->persist($user);
        $this->em->flush();

        if ($this->doubleOptIn) {
            $this->mailService->sendVerificationEmail($user);

            return $this->json(
                ['message' => 'Registration successful. Please check your email to verify your account.'],
                Response::HTTP_CREATED
            );
        }

        return $this->json(['message' => 'Registration successful.'], Response::HTTP_CREATED);
    }

    #[Route('/verify/{token}', methods: ['GET'])]
    public function verify(string $token): JsonResponse
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['verificationToken' => $token]);

        if (!$user) {
            return $this->json(['error' => 'Invalid or expired verification token.'], Response::HTTP_NOT_FOUND);
        }

        $user->setIsVerified(true);
        $user->setVerificationToken(null);
        $this->em->flush();

        return $this->json(['message' => 'Your email has been verified. You can now log in.']);
    }

    #[Route('/me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->json([
            'id'         => $user->getId(),
            'email'      => $user->getUserIdentifier(),
            'roles'      => $user->getRoles(),
            'isAdmin'    => $user->isAdmin(),
            'isPaying'   => $user->isPaying(),
            'isVerified' => $user->isVerified(),
        ]);
    }
}
