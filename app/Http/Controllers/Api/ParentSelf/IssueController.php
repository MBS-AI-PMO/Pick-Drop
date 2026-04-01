<?php

namespace App\Http\Controllers\Api\ParentSelf;

use App\Models\IssueReport;
use App\Models\PickupRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class IssueController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        try {
            $issues = IssueReport::where('user_id', $request->user()->id)
                ->orderByDesc('id')
                ->paginate(20);

            return $this->successResponse($issues, 'Issues');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to fetch issues');
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'pickup_request_id' => ['nullable', 'integer', 'exists:pickup_requests,id'],
                'subject' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
            ]);

            if (!empty($validated['pickup_request_id'])) {
                $pr = PickupRequest::where('id', $validated['pickup_request_id'])
                    ->where('parent_id', $request->user()->id)
                    ->first();
                if (!$pr) {
                    return $this->errorResponse('Invalid pickup_request_id', 422);
                }
            }

            $issue = IssueReport::create([
                'user_id' => $request->user()->id,
                'pickup_request_id' => $validated['pickup_request_id'] ?? null,
                'subject' => $validated['subject'],
                'description' => $validated['description'] ?? null,
                'status' => 'open',
            ]);

            return $this->successResponse($issue, 'Issue reported', 201);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to create issue');
        }
    }
}

