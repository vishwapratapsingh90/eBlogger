<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Http\Request;

uses(Tests\TestCase::class);

test('destroy deletes the current access token when available', function () {
    $token = new class {
        public bool $deleted = false;

        public function delete(): void
        {
            $this->deleted = true;
        }
    };

    $user = new class($token) {
        public function __construct(protected object $token)
        {
            //
        }

        public function currentAccessToken(): object
        {
            return $this->token;
        }
    };

    $request = Request::create('/api/logout', 'POST');
    $request->setUserResolver(fn () => $user);

    $response = $this->app->make(AuthenticatedSessionController::class)->destroy($request);

    expect($response->getStatusCode())->toBe(200)
        ->and($response->getData(true)['message'])->toBe('User logged out successfully');

    expect($token->deleted)->toBeTrue();
});
