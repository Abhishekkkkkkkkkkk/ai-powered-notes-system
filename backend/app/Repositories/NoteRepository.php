<?php

namespace App\Repositories;

use App\Models\Note;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class NoteRepository implements NoteRepositoryInterface
{
    /**
     * Get paginated notes, latest first.
     */
    public function getAllNotes(int $perPage = 10): LengthAwarePaginator
    {
        return Note::latest('id')->paginate($perPage);
    }

    /**
     * Get all notes that have embeddings (for search comparison).
     */
    public function getAllNotesWithEmbeddings(): Collection
    {
        return Note::whereNotNull('embedding')->get();
    }

    /**
     * Find a note by ID.
     */
    public function findNoteById(int $id): ?Note
    {
        return Note::find($id);
    }

    /**
     * Create a new note.
     */
    public function createNote(array $data): Note
    {
        return Note::create($data);
    }

    /**
     * Update an existing note.
     */
    public function updateNote(int $id, array $data): ?Note
    {
        $note = $this->findNoteById($id);
        if ($note) {
            $note->update($data);
            return $note;
        }
        return null;
    }

    /**
     * Delete a note.
     */
    public function deleteNote(int $id): bool
    {
        $note = $this->findNoteById($id);
        if ($note) {
            return $note->delete();
        }
        return false;
    }
}
