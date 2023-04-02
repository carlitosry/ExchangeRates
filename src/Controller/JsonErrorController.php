<?php
namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class JsonErrorController extends AbstractController
{
    public function show(Throwable $exception, ?LoggerInterface $logger): JsonResponse
    {
        $code = Response::HTTP_NOT_ACCEPTABLE;

        if ($exception instanceof HttpException) {
            $code = $exception->getStatusCode();
        }

        return $this->json($exception->getMessage(), $code);
    }
}
