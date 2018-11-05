<?php

/*
 * This file is part of a Salah Medfay project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Manager\UserManager;
use FOS\RestBundle\Controller\FOSRestController;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailureException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AuthenticationController extends FOSRestController
{
    /**
     * @var UserManager
     */
    private $authenticationManager;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $userPasswordEncoder;

    /**
     * @var JWTEncoderInterface
     */
    private $jwtEncoderInterface;

    /**
     * AuthenticationController constructor.
     *
     * @param UserManager                  $authenticationManager
     * @param UserPasswordEncoderInterface $userPasswordEncoder
     * @param JWTEncoderInterface          $jwtEncoderInterface
     */
    public function __construct(UserManager $authenticationManager, UserPasswordEncoderInterface $userPasswordEncoder, JWTEncoderInterface $jwtEncoderInterface)
    {
        $this->authenticationManager = $authenticationManager;
        $this->userPasswordEncoder   = $userPasswordEncoder;
        $this->jwtEncoderInterface   = $jwtEncoderInterface;
    }

    /**
     * @Rest\Post("/authentication", name="authentication")
     *
     * @param Request $request
     *
     * @throws JWTEncodeFailureException
     *
     * @return Response
     */
    public function authenticate(Request $request): Response
    {
        $user = $this->authenticationManager->findOneBy(['email' => $request->request->get('email')]);

        if (!$user || $this->userPasswordEncoder->isPasswordValid($user, $request->request->get('password'))) {
            throw new UnauthorizedHttpException('Authentication denied. User does not exist or the password is wrong.');
        }

        $token = $this->jwtEncoderInterface->encode([
            'username' => $user->getUsername(),
        ]);

        return new JsonResponse([
            'token' => $token,
        ]);
    }
}
