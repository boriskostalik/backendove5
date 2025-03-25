<?php

namespace App\Http\Controllers;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $notes = Note::orderBy('updated_at', 'desc')->get();
        return response()->json($notes);
    }

    public function store(Request $request)
    {
        $note = Note::create([
            'user_id' => $request->user_id,
            'title' => $request->title,
            'body' => $request->body
        ]);

        if ($note) {
            return response()->json(['message' => 'Poznámka bola vytvorená'], Response::HTTP_CREATED);
        } else {
            return response()->json(['message' => 'Poznámka nebola vytvorená'], Response::HTTP_FORBIDDEN);
        }

    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $note = Note::find($id);

        if (!$note) {
            return response()->json(['message' => 'Poznámka nebola nájdená'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($note);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $note = Note::find($id);

        if (!$note) {
            return response()->json(['message' => 'Poznámka nebola nájdená'], Response::HTTP_NOT_FOUND);
        }

        $note->update([
            'title' => $request->title,
            'body' => $request->body
        ]);

        return response()->json(['message' => 'Poznámka bola aktualizovaná', 'note' => $note]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $note = Note::find($id);

        if (!$note) {
            return response()->json(['message' => 'Poznámka nebola nájdená'], Response::HTTP_NOT_FOUND);
        }

        $note->delete();
        return response()->json(['message' => 'Poznámka bola vymazaná']);
    }
    /**
     * Vlastné metódy
     */

    public function notesWithUsers()
    {
        $notes = DB::table('notes')
            ->join('users', 'notes.user_id', '=', 'users.id')
            ->select('notes.*', 'users.name as user_name')
            ->get();

        return response()->json($notes);
    }




    public function usersWithNoteCount()
    {
        $users = DB::table('users')
            ->select('users.id', 'users.name')
            ->selectSub(function ($query) {
                $query->from('notes')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('notes.user_id', 'users.id');
            }, 'note_count')
            ->get();

        return response()->json($users);
    }




    public function searchNotes(Request $request)
    {
        $query = $request->query('q');

        if (empty($query)) {
            return response()->json(['message' => 'Musíte zadať dopyt na vyhľadávanie'], Response::HTTP_BAD_REQUEST);
        }

        $notes = Note::searchByTitleOrBody($query); 

        if ($notes->isEmpty()) {
            return response()->json(['message' => 'Žiadne poznámky sa nenašli'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($notes);
    }

    

    public function longestAndShortestNote()
    {
        $longest = DB::table('notes')
            ->select('id', 'title', 'body', DB::raw('LENGTH(body) as length'))
            ->orderByDesc('length')
            ->first();

        $shortest = DB::table('notes')
            ->select('id', 'title', 'body', DB::raw('LENGTH(body) as length'))
            ->orderBy('length')
            ->first();

        return response()->json([
            'longest' => $longest,
            'shortest' => $shortest
        ]);
    }


    public function usersWithNotesCount()
    {
        $users = DB::table('notes')
            ->join('users', 'notes.user_id', '=', 'users.id')
            ->select('users.id', 'users.name', DB::raw('COUNT(notes.id) as note_count'))
            ->groupBy('users.id', 'users.name')
            ->having('note_count', '>', 1)
            ->orderByDesc('note_count')
            ->get();

        return response()->json($users);
    }


    public function notesLastWeek()
    {
        $count = DB::table('notes')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        return response()->json(['last_week_notes' => $count]);
    }

}