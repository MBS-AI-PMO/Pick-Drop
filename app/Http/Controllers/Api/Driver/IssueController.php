<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Api\Driver\BaseApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class IssueController extends BaseApiController
{
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'route_id'    => ['required', 'integer'],
                'type'        => ['required', 'string', 'max:50'], // e.g. delay, breakdown
                'reason'      => ['nullable', 'string', 'max:1000'],
                'eta_change'  => ['nullable', 'integer'], // minutes
            ]);

            // TODO: Persist issue in DB (e.g., DriverIssue model)
            $issue = $validated;
            $issue['id'] = 0; // placeholder

            return $this->successResponse($issue, 'Issue submitted', 201);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to submit issue');
        }
    }

    public function today(Request $request): JsonResponse
    {
        try {
            // TODO: Query today issues for this driver
            $issues = [];

            return $this->successResponse($issues, "Today's issues");
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to fetch issues');
        }
    }
}

