<?php

namespace JustSomeCode\FlysystemVault\Exceptions;

use Illuminate\Http\JsonResponse;

class UnsupportedAction extends \Exception
{
    public function render(): JsonResponse
    {
        return new JsonResponse(['message' => $this->getMessage()], 500);
    }
}