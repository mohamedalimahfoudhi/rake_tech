<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

abstract class BaseController extends AbstractController
{
    protected function renderWithBase($template, array $parameters = []): Response
    {
        return $this->render($template, array_merge([
            'base_template' => 'base.html.twig',
        ], $parameters));
    }
} 