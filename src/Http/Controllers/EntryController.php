<?php

namespace Ratespecial\LaravelSpyhole\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Ratespecial\LaravelSpyhole\Http\Requests\StoreEntryRequest;
use Ratespecial\LaravelSpyhole\Models\SessionRecording;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;

class EntryController extends Controller
{
    public function store(StoreEntryRequest $request): JsonResponse
    {
        $recordingId = null;
        if ($request->has('recordingId')) {
            $recordingId = (int)decrypt($request->input('recordingId'));

            if (! SessionRecording::whereId($recordingId)->exists()) {
                throw new NotAcceptableHttpException();
            }
        }

        // get frames directly from raw input, don't pull value from $request
        $data = json_decode($request->getContent(), true);
        $frames = $data['frames'] ?? [];

        if (config('laravel-spyhole.track_request_session_id')) {
            $sessionId = $request->session()->getId();
        } else {
            if (session()->has('spyhole_session_id')) {
                $sessionId = session()->get('spyhole_session_id');
            } else {
                do {
                    $sessionId = Str::uuid()->toString();
                } while (
                    SessionRecording::whereSessionId($sessionId)->exists() &&
                    $sessionId !== $request->session()->getId()
                );
                session()->put('spyhole_session_id', $sessionId);
            }
        }

        $userId = null;
        if (config('laravel-spyhole.record_user_id')) {
            $user = Auth::user();
            $userId = $user ? $user->getAuthIdentifier() : null;
        }

        if ($recordingId === null) {
            $recording = new SessionRecording();
            $recording->session_id = $sessionId;
            $recording->path = $request->input('path');
            if (!empty($request->input('type'))) {
                $recording->type = $request->input('type');
            }
            $recording->recordings = $frames;
        } else {
            $recording = SessionRecording::whereKey($recordingId)->first();

            if ($recording === null) {
                throw new NotAcceptableHttpException();
            }

            // Merge frames from the same session
            $recording->recordings = array_merge(
                $recording->recordings,
                $frames
            );
        }

        $recording->save();

        return response()->json([
            'success' => true,
            'recordingId' => encrypt($recording->id),
        ]);
    }
}
