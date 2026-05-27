<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Http\Resources\NoteResource;
use App\Repositories\NoteRepositoryInterface;
use App\Services\AIServiceInterface;
use App\Services\SummaryService;
use App\Services\SemanticSearchService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class NoteController extends Controller
{
    use ApiResponseTrait;

    protected NoteRepositoryInterface $noteRepository;
    protected AIServiceInterface $openAIService;
    protected SummaryService $summaryService;
    protected SemanticSearchService $semanticSearchService;

    public function __construct(
        NoteRepositoryInterface $noteRepository,
        AIServiceInterface $openAIService,
        SummaryService $summaryService,
        SemanticSearchService $semanticSearchService
    ) {
        $this->noteRepository = $noteRepository;
        $this->openAIService = $openAIService;
        $this->summaryService = $summaryService;
        $this->semanticSearchService = $semanticSearchService;
    }

    /**
     * @OA\Get(
     *     path="/notes",
     *     summary="Retrieve paginated notes",
     *     description="Get a list of all notes sorted by latest first with pagination support.",
     *     tags={"Notes"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Number of notes per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notes retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Notes retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="title", type="string", example="Java Programming"),
     *                     @OA\Property(property="content", type="string", example="Learn object-oriented concepts..."),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="total", type="integer", example=48)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $limit = $request->query('limit', 10);
            $notes = $this->noteRepository->getAllNotes((int)$limit);

            return response()->json([
                'success' => true,
                'message' => 'Notes retrieved successfully',
                'data' => NoteResource::collection($notes),
                'meta' => [
                    'current_page' => $notes->currentPage(),
                    'last_page' => $notes->lastPage(),
                    'per_page' => $notes->perPage(),
                    'total' => $notes->total(),
                ]
            ], 200);
        } catch (Exception $e) {
            Log::error('Failed to retrieve notes: ' . $e->getMessage());
            return $this->errorResponse('Something went wrong while retrieving notes.', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/notes",
     *     summary="Create a new note",
     *     description="Validate the input, generate an embedding using OpenAI, and store the note.",
     *     tags={"Notes"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title","content"},
     *             @OA\Property(property="title", type="string", example="My AI Note"),
     *             @OA\Property(property="content", type="string", example="This is the content of the note.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Note created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Note created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="My AI Note"),
     *                 @OA\Property(property="content", type="string", example="This is the content of the note."),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function store(StoreNoteRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            // Generate Embedding for semantic search (combining title and content)
            $textToEmbed = "Title: " . $data['title'] . "\nContent: " . $data['content'];
            
            try {
                $data['embedding'] = $this->openAIService->generateEmbedding($textToEmbed);
            } catch (Exception $e) {
                Log::warning('AI Embedding generation failed during note creation, setting as null. Error: ' . $e->getMessage());
                $data['embedding'] = null;
            }

            $note = $this->noteRepository->createNote($data);

            return $this->successResponse(
                'Note created successfully',
                new NoteResource($note),
                201
            );
        } catch (Exception $e) {
            Log::error('Failed to create note: ' . $e->getMessage());
            return $this->errorResponse('Something went wrong while creating the note.', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/notes/{id}",
     *     summary="Retrieve a single note",
     *     description="Fetch a note details by its unique identifier.",
     *     tags={"Notes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the note to retrieve",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Note retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Note retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="My AI Note"),
     *                 @OA\Property(property="content", type="string", example="This is the content of the note.")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Note not found"
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $note = $this->noteRepository->findNoteById($id);

            if (!$note) {
                return $this->errorResponse('Note not found', 404);
            }

            return $this->successResponse('Note retrieved successfully', new NoteResource($note));
        } catch (Exception $e) {
            Log::error('Failed to show note: ' . $e->getMessage());
            return $this->errorResponse('Something went wrong while fetching the note.', 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/notes/{id}",
     *     summary="Update an existing note",
     *     description="Update the note's title and content, regenerate the vector embedding, and clear summary cache.",
     *     tags={"Notes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the note to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title","content"},
     *             @OA\Property(property="title", type="string", example="Updated Note Title"),
     *             @OA\Property(property="content", type="string", example="Updated note content.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Note updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Note updated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Note not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(UpdateNoteRequest $request, int $id): JsonResponse
    {
        try {
            $note = $this->noteRepository->findNoteById($id);

            if (!$note) {
                return $this->errorResponse('Note not found', 404);
            }

            $data = $request->validated();

            // Invalidate summary cache because content changed
            $this->summaryService->invalidateSummaryCache($id);

            // Regenerate embedding with new text
            $textToEmbed = "Title: " . $data['title'] . "\nContent: " . $data['content'];
            try {
                $data['embedding'] = $this->openAIService->generateEmbedding($textToEmbed);
            } catch (Exception $e) {
                Log::warning('AI Embedding update failed, keeping current embedding. Error: ' . $e->getMessage());
                // Don't overwrite the old embedding with null if generation failed; keep the old one.
            }

            $updatedNote = $this->noteRepository->updateNote($id, $data);

            return $this->successResponse('Note updated successfully', new NoteResource($updatedNote));
        } catch (Exception $e) {
            Log::error('Failed to update note: ' . $e->getMessage());
            return $this->errorResponse('Something went wrong while updating the note.', 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/notes/{id}",
     *     summary="Delete a note",
     *     description="Deletes a note from the system and invalidates its summary cache.",
     *     tags={"Notes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the note to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Note deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Note deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Note not found"
     *     )
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $note = $this->noteRepository->findNoteById($id);

            if (!$note) {
                return $this->errorResponse('Note not found', 404);
            }

            // Invalidate cached summary
            $this->summaryService->invalidateSummaryCache($id);

            // Delete Note
            $this->noteRepository->deleteNote($id);

            return $this->successResponse('Note deleted successfully');
        } catch (Exception $e) {
            Log::error('Failed to delete note: ' . $e->getMessage());
            return $this->errorResponse('Something went wrong while deleting the note.', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/notes/{id}/summary",
     *     summary="Generate AI summary of a note",
     *     description="Send note content to OpenAI chat completion and generate 3-5 concise summary lines. Output is cached.",
     *     tags={"AI Services"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the note to summarize",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Summary generated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Summary generated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="summary", type="string", example="1. Explains object programming.\n2. Compares abstract classes vs interfaces.")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Note not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="AI/OpenAI API error"
     *     )
     * )
     */
    public function generateSummary(int $id): JsonResponse
    {
        try {
            $summary = $this->summaryService->getNoteSummary($id);

            return $this->successResponse('Summary generated successfully', [
                'summary' => $summary
            ]);
        } catch (Exception $e) {
            $code = $e->getCode() === 404 ? 404 : 500;
            Log::error('Failed to generate summary for note ' . $id . ': ' . $e->getMessage());
            return $this->errorResponse($e->getMessage(), $code);
        }
    }

    /**
     * @OA\Post(
     *     path="/notes/search",
     *     summary="Semantic search notes",
     *     description="Accepts a query text, computes its embedding, and performs cosine similarity against stored note embeddings.",
     *     tags={"AI Services"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"query"},
     *             @OA\Property(property="query", type="string", example="OOP encapsulation in python")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Semantic search completed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Semantic search completed successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=2),
     *                     @OA\Property(property="title", type="string", example="Encapsulation concepts"),
     *                     @OA\Property(property="similarity_score", type="number", format="float", example=0.875)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="AI/OpenAI API error"
     *     )
     * )
     */
    public function semanticSearch(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'query' => 'required|string|max:500',
            ]);

            $query = $request->input('query');
            $results = $this->semanticSearchService->search($query);

            return $this->successResponse(
                'Semantic search completed successfully',
                NoteResource::collection(collect($results))
            );
        } catch (Exception $e) {
            Log::error('Failed to run semantic search: ' . $e->getMessage());
            return $this->errorResponse('Something went wrong while running semantic search.', 500);
        }
    }
}
