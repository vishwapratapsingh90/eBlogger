<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class RegisteredUserController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $search = $request->query('search');
        $allowedSortColumns = ['id', 'name', 'email', 'created_at', 'updated_at'];
        $sortBy = $request->query('sort_by', 'created_at');
        $sortOrder = $request->query('sort_order','desc');

        $query = User::query();

        $query->when($search, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        });

        $query->when($sortBy && in_array($sortBy, $allowedSortColumns, true), function ($query) use ($sortBy, $sortOrder) {
            $query->orderBy($sortBy, $sortOrder);
        });

        // eager load relationships for the returned resource
        $query->with(['blogs', 'imageGenerations']);

        $users = $query->paginate($request->input('per_page', 15));

        return UserResource::collection($users);
    }

    /**
     * @unauthenticated
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): Response
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->string('password')),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return response()->noContent();
    }
}
