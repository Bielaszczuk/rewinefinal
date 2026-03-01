<?php

namespace App\Http\Controllers;

use App\Models\Wine;
use App\Models\Winery;
use App\Models\Event;
use App\Models\Review;
use App\Models\User;
use App\Models\WineRoute;
use Illuminate\Http\JsonResponse;

class AdminDashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        return response()->json([
            'wines' => Wine::count(),
            'wineries' => Winery::count(),
            'events' => Event::count(),
            'reviews' => Review::count(),
            'users' => User::count(),
            'routes' => WineRoute::count(),
        ]);
    }
}

