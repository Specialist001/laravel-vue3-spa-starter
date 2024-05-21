<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Auth\LoginRequest;
use App\Http\Requests\API\Auth\VerifyRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class LoginController extends Controller
{
    public function submit(LoginRequest $request)
    {
        return new JsonResponse(
            data: [
                'message' => 'Submit',
            ],
            status: Response::HTTP_OK,
        );

    }

    public function verify(VerifyRequest $request)
    {
        return new JsonResponse(
            data: [
                'message' => 'Verify',
            ],
            status: Response::HTTP_OK,
        );
    }
}
