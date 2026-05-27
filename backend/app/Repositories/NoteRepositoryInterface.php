<?php

namespace App\Repositories;

use App\Models\Note;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface NoteRepositoryInterface
{
    /**
     * Get paginated notes, latest first.
     */
    public function getAllNotes(int $perPage = 10): LengthAwarePaginator;

    /**
     * Get all notes that have embeddings (for search comparison).
     */
    public function getAllNotesWithEmbeddings(): Collection;

    /**
     * Find a note by ID.
     */
    public function findNoteById(int $id): ?Note;

    /**
     * Create a new note.
     */
    public function createNote(array $data): Note;

    /**
     * Update an existing note.
     */
    public function updateNote(int $id, array $data): ?Note;

    /**
     * Delete a note.
     */
    public function deleteNote(int $id): bool;
}
