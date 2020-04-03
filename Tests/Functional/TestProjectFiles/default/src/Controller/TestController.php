<?php

declare(strict_types=1);

namespace App\Controller;

use Auxmoney\OpentracingBundle\Service\Tracing;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class TestController extends AbstractController
{
    public function index(Tracing $tracing): JsonResponse
    {
        $tracing->setTagOfActiveSpan('tag.from.controller', true);
        $tracing->logInActiveSpan(['message' => 'log message from controller']);
        return new JsonResponse(['reply' => true]);
    }

    public function error(): JsonResponse
    {
        // TODO: add test case
        throw new Exception('something bad happened in the controller');
    }
}
