<?php

namespace App\Http\Controllers;

use App\Application\Review\Services\ReviewCommandService;
use App\Application\Review\Services\ReviewQueryService;
use App\Http\Requests\Review\StoreReviewRequest;
use App\Http\Requests\Review\UpdateReviewRequest;
use App\Http\Resources\Review\ReviewCollection;
use App\Http\Resources\Review\ReviewResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\UniqueConstraintViolationException;

class ReviewController extends Controller
{
    public function __construct(
        private readonly ReviewQueryService $queryService,
        private readonly ReviewCommandService $commandService
    ) {
    }

    public function index(Request $request, string $wineId)
    {
        $sortBy = $request->query('filter', 'recent');
        $page = (int) $request->query('page', 0);
        $size = min((int) $request->query('size', 20), 100);

        $reviews = $this->queryService->getByWine(
            $wineId,
            $sortBy,
            $page,
            $size
        );

        return ReviewCollection::make($reviews);
    }

    public function store(StoreReviewRequest $request, string $wineId)
    {
        try {
            $review = $this->commandService->create($request->toDto());
            return ReviewResource::make($review);
        } catch (UniqueConstraintViolationException $e) {
            return response()->json([
                'message' => 'Ya has publicado una reseña para este vino',
                'error' => 'duplicate_review'
            ], 409);
        }
    }

    public function show(string $id)
    {
        $review = $this->queryService->findByIdOrFail($id);

        return ReviewResource::make($review);
    }

    public function update(UpdateReviewRequest $request, string $wineId, string $id)
    {
        $review = $this->commandService->update($id, $request->toDto());

        return ReviewResource::make($review);
    }

    public function destroy(string $wineId, string $id): JsonResponse
    {
        $this->commandService->delete($id);

        return response()->json([
            'message' => 'Review deleted successfully',
            'id' => $id,
        ], 200);
    }

    public function like(string $wineId, string $id): JsonResponse
    {
        $this->commandService->likeReview($id, auth('api')->id());

        return response()->json([
            'message' => 'Review liked successfully',
        ], 200);
    }

    public function unlike(string $wineId, string $id): JsonResponse
    {
        $this->commandService->unlikeReview($id, auth('api')->id());

        return response()->json([
            'message' => 'Review unliked successfully',
        ], 200);
    }
}
