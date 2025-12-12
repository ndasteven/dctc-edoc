<?php

namespace App\Http\Controllers;

use App\Models\Reminder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class ReminderController extends Controller
{
    /**
     * Display a listing of the reminders.
     */
    public function index(Request $request)
    {
        $query = Reminder::with(['user', 'document', 'folder']);
        
        // Filtres optionnels
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        if ($request->has('file_id')) {
            $query->where('file_id', $request->file_id);
        }
        
        if ($request->has('folder_id')) {
            $query->where('folder_id', $request->folder_id);
        }
        
        if ($request->has('active')) {
            $query->where('is_active', $request->active);
        }
        
        if ($request->has('completed')) {
            $query->where('is_completed', $request->completed);
        }
        
        $reminders = $query->paginate(15);
        
        return response()->json($reminders);
    }

    /**
     * Store a newly created reminder in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'nullable|string',
            'reminder_date' => 'required|date',
            'reminder_time' => 'required|date_format:H:i',
            'file_id' => 'nullable|exists:documents,id',
            'folder_id' => 'nullable|exists:folders,id',
            'is_active' => 'boolean'
        ]);

        // Vérifier qu'un rappel est lié à un document OU un dossier, mais pas les deux
        if ($request->file_id && $request->folder_id) {
            return response()->json([
                'error' => 'Un rappel ne peut être lié qu\'à un document OU un dossier, mais pas aux deux.'
            ], 422);
        }

        if (!$request->file_id && !$request->folder_id) {
            return response()->json([
                'error' => 'Un rappel doit être lié à un document OU un dossier.'
            ], 422);
        }

        // Vérifier s'il existe déjà un rappel pour ce document ou dossier
        if ($request->file_id) {
            $existingReminder = Reminder::where('file_id', $request->file_id)->first();
        } elseif ($request->folder_id) {
            $existingReminder = Reminder::where('folder_id', $request->folder_id)->first();
        }

        if ($existingReminder) {
            return response()->json([
                'error' => 'Un rappel existe déjà pour cet élément.'
            ], 422);
        }

        $reminder = new Reminder([
            'title' => $request->title,
            'message' => $request->message,
            'reminder_date' => $request->reminder_date,
            'reminder_time' => $request->reminder_time,
            'file_id' => $request->file_id,
            'folder_id' => $request->folder_id,
            'user_id' => auth()->id(),
            'is_active' => $request->is_active ?? true
        ]);

        $reminder->save();

        return response()->json($reminder->load(['user', 'document', 'folder']), 201);
    }

    /**
     * Display the specified reminder.
     */
    public function show($id)
    {
        $reminder = Reminder::with(['user', 'document', 'folder'])->find($id);

        if (!$reminder) {
            return response()->json(['error' => 'Rappel non trouvé'], 404);
        }

        return response()->json($reminder);
    }

    /**
     * Update the specified reminder in storage.
     */
    public function update(Request $request, $id)
    {
        $reminder = Reminder::find($id);

        if (!$reminder) {
            return response()->json(['error' => 'Rappel non trouvé'], 404);
        }

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'message' => 'sometimes|nullable|string',
            'reminder_date' => 'sometimes|date',
            'reminder_time' => 'sometimes|date_format:H:i',
            'file_id' => 'sometimes|nullable|exists:documents,id',
            'folder_id' => 'sometimes|nullable|exists:folders,id',
            'is_active' => 'sometimes|boolean',
            'is_completed' => 'sometimes|boolean'
        ]);

        // Vérifier qu'un rappel est lié à un document OU un dossier, mais pas les deux
        if ($request->filled('file_id') && $request->filled('folder_id')) {
            return response()->json([
                'error' => 'Un rappel ne peut être lié qu\'à un document OU un dossier, mais pas aux deux.'
            ], 422);
        }

        $reminder->update($request->all());

        return response()->json($reminder->load(['user', 'document', 'folder']));
    }

    /**
     * Mark the specified reminder as completed.
     */
    public function complete($id)
    {
        $reminder = Reminder::find($id);

        if (!$reminder) {
            return response()->json(['error' => 'Rappel non trouvé'], 404);
        }

        $reminder->update([
            'is_completed' => true,
            'completed_at' => Carbon::now()
        ]);

        return response()->json($reminder->load(['user', 'document', 'folder']));
    }

    /**
     * Remove the specified reminder from storage.
     */
    public function destroy($id)
    {
        $reminder = Reminder::find($id);

        if (!$reminder) {
            return response()->json(['error' => 'Rappel non trouvé'], 404);
        }

        $reminder->delete();

        return response()->json(['message' => 'Rappel supprimé avec succès']);
    }

    /**
     * Get reminders for a specific document.
     */
    public function getByDocument($documentId)
    {
        $reminders = Reminder::with(['user', 'document', 'folder'])
            ->where('file_id', $documentId)
            ->get();

        return response()->json($reminders);
    }

    /**
     * Get upcoming reminders for authenticated user (within 10 minutes).
     */
    public function getUpcomingForNotification()
    {
        $now = now();
        $tenMinutesLater = $now->copy()->addMinutes(10);

        $upcomingReminders = Reminder::with(['user', 'document', 'folder'])
            ->where('user_id', auth()->id())
            ->where('is_active', true)
            ->where('is_completed', false)
            ->where(function ($query) use ($now, $tenMinutesLater) {
                $query->where('reminder_date', $now->toDateString())
                      ->whereBetween('reminder_time', [$now->toTimeString(), $tenMinutesLater->toTimeString()]);
            })
            ->orWhere(function ($query) use ($now, $tenMinutesLater) {
                $query->where('reminder_date', '>', $now->toDateString())
                      ->where('reminder_date', '<=', $tenMinutesLater->toDateString())
                      ->where('reminder_time', '<=', $tenMinutesLater->toTimeString());
            })
            ->count();

        return response()->json(['count' => $upcomingReminders]);
    }

    /**
     * Get reminders for a specific folder.
     */
    public function getByFolder($folderId)
    {
        $reminders = Reminder::with(['user', 'document', 'folder'])
            ->where('folder_id', $folderId)
            ->get();

        return response()->json($reminders);
    }
    
    /**
     * Get reminders for authenticated user.
     */
    public function getUserReminders()
    {
        $reminders = Reminder::with(['user', 'document', 'folder'])
            ->where('user_id', auth()->id())
            ->get();

        return response()->json($reminders);
    }
    
    /**
     * Get upcoming reminders for authenticated user.
     */
    public function getUpcomingReminders()
    {
        $reminders = Reminder::with(['user', 'document', 'folder'])
            ->where('user_id', auth()->id())
            ->where('is_active', true)
            ->where('is_completed', false)
            ->where(function ($query) {
                $query->where('reminder_date', '>', now()->toDateString())
                    ->orWhere(function ($query) {
                        $query->where('reminder_date', now()->toDateString())
                              ->where('reminder_time', '>', now()->toTimeString());
                    });
            })
            ->orderBy('reminder_date', 'asc')
            ->orderBy('reminder_time', 'asc')
            ->get();

        return response()->json($reminders);
    }
}