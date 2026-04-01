<?php

namespace App\Http\Controllers\Api\ParentSelf;

use App\Models\NotificationPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class NotificationPreferenceController extends BaseApiController
{
    public function show(Request $request): JsonResponse
    {
        try {
            $prefs = NotificationPreference::firstOrCreate(
                ['user_id' => $request->user()->id],
                [
                    'push_enabled' => true,
                    'email_enabled' => true,
                    'new_messages' => true,
                    'child_activity' => true,
                    'school_alerts' => true,
                    'payment_reminders' => true,
                    'weekly_updates' => false,
                    'promotions' => false,
                ]
            );

            return $this->successResponse($prefs, 'Notification preferences');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to fetch notification preferences');
        }
    }

    public function update(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'push_enabled' => ['sometimes', 'boolean'],
                'email_enabled' => ['sometimes', 'boolean'],
                'new_messages' => ['sometimes', 'boolean'],
                'child_activity' => ['sometimes', 'boolean'],
                'school_alerts' => ['sometimes', 'boolean'],
                'payment_reminders' => ['sometimes', 'boolean'],
                'weekly_updates' => ['sometimes', 'boolean'],
                'promotions' => ['sometimes', 'boolean'],
            ]);

            $prefs = NotificationPreference::firstOrCreate(['user_id' => $request->user()->id]);
            $prefs->fill($validated);
            $prefs->save();

            return $this->successResponse($prefs, 'Notification preferences updated');
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to update notification preferences');
        }
    }
}

