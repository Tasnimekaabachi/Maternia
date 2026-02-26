<?php
// src/Security/AccessDeniedHandler.php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
use Twig\Environment;

class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function handle(Request $request, AccessDeniedException $accessDeniedException): ?Response
    {
        // Check if it's an admin route
        if (str_starts_with($request->getPathInfo(), '/admin')) {
            return new Response(
                $this->twig->render('bundles/TwigBundle/Exception/error403.html.twig'),
                Response::HTTP_FORBIDDEN
            );
        }

        // For other access denied, show 403 page
        return new Response(
            $this->twig->render('bundles/TwigBundle/Exception/error403.html.twig'),
            Response::HTTP_FORBIDDEN
        );
    }
}