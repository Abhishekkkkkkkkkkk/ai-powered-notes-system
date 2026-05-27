# LuminaNotes — AI-Powered Notes Management System

LuminaNotes is a production-ready, full-stack notes management application built on a clean architectural pattern using **Laravel (PHP)**, **MySQL**, **React (Vite)**, **Tailwind CSS**, and **Google Gemini / OpenAI APIs**. 

It enables users to create and manage notes with traditional CRUD features, while enriching the workspace with **AI Semantic Vector Search** (using OpenAI or Gemini Embeddings) and **AI Note Summarization** (using Gemini Flash or OpenAI Chat Completions), backed by **Redis Caching** to limit third-party API costs and latency.

---

## Features

- **Standard Note CRUD**: Create, update, view, and delete notes.
- **Multi-Provider AI (Gemini & OpenAI)**: Toggle seamlessly between Google Gemini (completely free tier) and OpenAI.
- **AI Semantic Vector Search**: Find notes by contextual meaning instead of exact keywords using `text-embedding-004` (Gemini) or `text-embedding-3-small` (OpenAI) and Cosine Similarity math.
- **AI-Powered Note Summaries**: Generate concise 3-5 line note summaries via **gemini-2.5-flash** or **gpt-4o-mini** with instant Redis caching.
- **Dynamic Vector Re-indexing**: Artisan command `notes:regenerate-embeddings` enables migrating all stored embeddings instantly when switching between Gemini and OpenAI.
- **Modern Glassmorphism UI**: Clean dashboard interface equipped with loaders, empty states, and toast notifications.
- **Full Swagger/OpenAPI Documentation**: Automatically generated interactive API explorer served at `/api/documentation` with definitions served at `/docs`.
- **Docker Compose Setup**: Spins up PHP-FPM, Nginx, MySQL, Redis, and Vite in containers.
- **Comprehensive Testing Suite**: Includes mathematical unit testing for Cosine Similarity and feature testing for API endpoints (with mocked AI layers).

---

## Tech Stack

### Backend
- **Laravel 11.x**: PHP 8.2 web framework.
- **Eloquent ORM**: Database mapping, seeders, and factories.
- **L5-Swagger**: Swagger integration for OpenAPI spec documentation.
- **Google Gemini API**: Direct HTTP SDK client for 100% free semantic search and summaries.
- **OpenAI PHP Client**: Official SDK for OpenAI communication.
- **Redis**: Fast key-value memory database used for AI summary caching.

### Frontend
- **React 18**: Dynamic UI rendering.
- **Vite**: Modern front-end toolchain.
- **Tailwind CSS**: Utility-first CSS framework with a custom violet/slate glassmorphic theme.
- **Axios**: Network client linking the React layer with Laravel's REST backend.
- **Lucide React**: Premium vector icons.

### Infrastructure
- **Docker & Docker Compose**: Unified local container infrastructure.
- **Nginx**: Reverse proxy web server routing web traffic to React and API requests to PHP-FPM.

---

## Project Folder Structure

```
ai-notes-system/
│
├── backend/                  # Laravel Backend Application
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   └── Api/
│   │   │   │       ├── NoteController.php       # Notes API Handler
│   │   │   │       └── SwaggerDocs.php          # Swagger API Details
│   │   │   ├── Requests/
│   │   │   │   ├── StoreNoteRequest.php         # Create Note Validation
│   │   │   │   └── UpdateNoteRequest.php        # Update Note Validation
│   │   │   └── Resources/
│   │   │       └── NoteResource.php             # JSON API Resource
│   │   ├── Models/
│   │   │   └── Note.php                         # Note Model (casts JSON embeddings)
│   │   ├── Providers/
│   │   │   └── AppServiceProvider.php           # Service binding provider (binds AIServiceInterface)
│   │   ├── Repositories/
│   │   │   ├── NoteRepositoryInterface.php      # Repository Contract
│   │   │   └── NoteRepository.php               # Concrete Eloquent Repository
│   │   ├── Services/
│   │   │   ├── AIServiceInterface.php           # Standard Contract for AI operations
│   │   │   ├── OpenAIService.php                # OpenAI API Client Wrapper
│   │   │   ├── GeminiService.php                # Google Gemini API Client Wrapper
│   │   │   ├── SummaryService.php               # Caches & orchestrates note summaries
│   │   │   └── SemanticSearchService.php        # Similarity math & rank calculator
│   │   └── Traits/
│   │       └── ApiResponseTrait.php             # Consistent API Response Structure
│   │
│   ├── config/
│   │   └── ai.php                               # Unified AI Config (OpenAI & Gemini)
│   ├── database/
│   │   ├── factories/
│   │   │   └── NoteFactory.php                  # Mock Note & Embedding Factory
│   │   ├── migrations/
│   │   │   └── 2026_05_26_000000_create_notes_table.php
│   │   └── seeders/
│   │       ├── NoteSeeder.php
│   │       └── DatabaseSeeder.php
│   ├── routes/
│   │   ├── api.php                              # Throttled REST API Routes
│   │   ├── web.php
│   │   └── console.php
│   ├── tests/
│   │   ├── Feature/
│   │   │   └── NoteApiTest.php                  # CRUD & API Feature Tests (Mocked AI)
│   │   ├── Unit/
│   │   │   └── SemanticSearchTest.php           # Cosine Similarity Math Unit Tests
│   │   └── TestCase.php
│   ├── .env.example
│   ├── phpunit.xml
│   └── composer.json
│
├── frontend/                 # React Vite Frontend Application
│   ├── src/
│   │   ├── components/
│   │   │   ├── Navbar.jsx                       # Responsive Navigation
│   │   │   ├── NoteCard.jsx                     # Individual note widget
│   │   │   ├── NoteForm.jsx                     # Validated Reusable Form
│   │   │   ├── SearchBar.jsx                    # Query inputs and Clear Actions
│   │   │   ├── Pagination.jsx                   # Simple paginator
│   │   │   └── SummaryModal.jsx                 # AI summary & Copy Modal
│   │   ├── pages/
│   │   │   ├── Dashboard.jsx                    # Grid board with search / loaders
│   │   │   ├── CreateNote.jsx                   # Save Notes page
│   │   │   └── EditNote.jsx                     # Modify Notes page
│   │   ├── services/
│   │   │   └── api.js                           # Axios client config and endpoints
│   │   ├── App.jsx                              # Router mapping and layout
│   │   └── main.jsx                             # Dom mounting point
│   ├── tailwind.config.js                       # Theme and animations config
│   ├── postcss.config.js
│   ├── vite.config.js                           # Dev server proxy configuration
│   ├── index.html
│   └── package.json
│
├── docker/                   # Nginx & Dockerfile configurations
│   ├── Dockerfile.backend
│   ├── Dockerfile.frontend
│   └── nginx.conf
│
├── docker-compose.yml        # Multi-container orchestration config
└── README.md
```

---

## AI Implementation Details

### 1. Vector Embedding Generation
When a note is **created** or **updated**, the backend automatically triggers an embedding request via the active provider configured in `.env` (`AI_PROVIDER=gemini` or `AI_PROVIDER=openai`).
- The text representation sent to the AI API combines the note title and body context:
  `"Title: {title}\nContent: {content}"`
- **Gemini**: Model `text-embedding-004` generates **768-dimension** float vectors.
- **OpenAI**: Model `text-embedding-3-small` generates **1536-dimension** float vectors.
- The resulting float array is stored in MySQL under the `embedding` JSON column.

### 2. Cosine Similarity Semantic Search
When a user inputs a query (e.g. *"OOP inheritance in PHP"*):
1. The query text is transformed into a vector (768 or 1536 floats) using the active AI provider.
2. The system retrieves all notes containing embeddings from the MySQL database.
3. For each note, the system calculates the **Cosine Similarity** between the query embedding $Q$ and the note embedding $N$:
   $$\text{Similarity}(Q, N) = \frac{Q \cdot N}{\|Q\| \cdot \|N\|} = \frac{\sum_{i=1}^{D} Q_i N_i}{\sqrt{\sum_{i=1}^{D} Q_i^2} \cdot \sqrt{\sum_{i=1}^{D} N_i^2}}$$
   *(where $D$ represents the dimensionality of the active vector format, e.g., 768 or 1536)*
4. The helper function in `SemanticSearchService` computes this calculation.
5. Notes are sorted in descending order of similarity score.
6. The frontend receives the sorted results, displaying match percentages (e.g. `92% Match`) for vector scores.

### 3. Caching AI Summaries
Generating summaries incurs API costs and network latency.
- **Gemini Model**: `gemini-2.5-flash`
- **OpenAI Model**: `gpt-4o-mini`
- **Prompt**: `"You are a helpful notes assistant. Summarize the following note clearly in 3-5 concise lines. Keep it simple and relevant."`
- **Reasoning Token Budget**: Since `gemini-2.5-flash` is a reasoning model, its thinking process consumes tokens under the output budget. The backend configures `maxOutputTokens => 1024` to ensure the reasoning engine has ample space to think and return complete, high-quality summaries without truncation.
- **Redis Caching**: The `SummaryService` intercept checks if the note's summary exists in **Redis** (`note_summary_{id}`).
- If cached, it is served instantly. If missing, it requests the active AI provider and stores the summary in Redis with a 24-hour TTL.
- The summary cache is **invalidated** whenever the note's content is modified or deleted.

---

## Installation & Setup Instructions

### Prerequisites
Make sure you have the following installed:
- [Docker & Docker Desktop](https://www.docker.com/)

---

### Setup using Docker (Recommended)

1. Clone or navigate to the directory `ai-notes-system`.
2. Open the `backend/.env` file. By default, it is configured to use Google Gemini. 
3. Insert your API credentials:
   ```env
   AI_PROVIDER=gemini
   
   # If using Gemini (100% Free at Google AI Studio)
   GEMINI_API_KEY=your-gemini-api-key-here
   
   # If using OpenAI (Requires funded credits)
   OPENAI_API_KEY=your-openai-api-key-here
   ```
4. Run the following Docker command from the root folder:
   ```bash
   docker-compose up -d --build
   ```
   *This starts the database, Redis, PHP-FPM backend, Nginx, and Vite.*
5. Install PHP dependencies and run migrations/seeders inside the container:
   ```bash
   # Run Composer installs
   docker-compose exec app composer install
   
   # Generate Application Key
   docker-compose exec app php artisan key:generate
   
   # Run migrations and seed database with 25 records
   docker-compose exec app php artisan migrate --seed
   ```
6. **Migrate existing Note Vector Embeddings**:
   If the database was seeded or contains existing note vectors, run this Artisan command inside the app container to convert all note embeddings to the active provider's structure (OpenAI 1536-dim or Gemini 768-dim):
   ```bash
   docker-compose exec app php artisan notes:regenerate-embeddings
   ```
7. Spin up the applications in your browser:
   - **Frontend UI**: [http://localhost:5173](http://localhost:5173)
   - **Backend API URL**: [http://localhost:8000/api](http://localhost:8000/api)
   - **Interactive API Swagger Docs**: [http://localhost:8000/api/documentation](http://localhost:8000/api/documentation)

---

## Running Automated Tests

Laravel unit and feature tests can be executed locally inside the PHP app container:

```bash
# Execute tests using PHPUnit inside Docker app container
docker-compose exec app php artisan test
```

This runs:
- `tests/Unit/SemanticSearchTest.php`: mathematical validations for vectors.
- `tests/Feature/NoteApiTest.php`: integration tests for CRUD endpoints, mock AI responses, and 422 validations.

---

## API Endpoints List

All routes are throttled to `60 requests / min` for security.

### Notes Core REST Resources
| Verb | URI | Action | Description | Status Code |
| :--- | :--- | :--- | :--- | :--- |
| **GET** | `/api/notes?page=1&limit=10` | `index` | List notes (latest first, paginated) | `200 OK` |
| **POST** | `/api/notes` | `store` | Create a note + compute embedding | `201 Created` |
| **GET** | `/api/notes/{id}` | `show` | Fetch details of a single note | `200 OK` / `404 Not Found` |
| **PUT** | `/api/notes/{id}` | `update` | Modify note + update embedding + clear cache | `200 OK` / `404 Not Found` |
| **DELETE**| `/api/notes/{id}` | `destroy` | Remove note + clear summary cache | `200 OK` / `404 Not Found` |

### AI Services
| Verb | URI | Request Payload | Description | Status Code |
| :--- | :--- | :--- | :--- | :--- |
| **POST** | `/api/notes/search` | `{"query": "java OOP"}` | Returns notes ranked by semantic similarity | `200 OK` / `422 Error` |
| **POST** | `/api/notes/{id}/summary`| *None* | Generates 3-5 lines summaries (Cached in Redis) | `200 OK` / `404 Not Found` |

---

## Future Improvements

1. **Database Vector Indexes (Cosine Similarity)**: For massive datasets, doing cosine similarity in PHP PHP-side is not ideal. Using pgvector (PostgreSQL) or vector index extensions for MySQL will allow matching vectors directly in DB queries.
2. **Asynchronous Queue Jobs**: Embeddings can be generated asynchronously using Laravel Queues (e.g. database/Redis queues) so note creation is instantaneous, and vector calculations run in the background.
3. **Collaboration and Multi-user support**: Implement Laravel Sanctum/Passport authentication to allow individual user note logs.
4. **Rich Text Formatting**: Introduce Quill.js or Editor.js in the React form to allow rich-text note edits.


---

## Database Schema Design

The database layer is managed using Laravel migrations. The primary storage uses MySQL with a dedicated `notes` schema designed to store text content alongside vector embeddings.

### `notes` Table Schema Details
| Column | Type | Attributes | Description |
| :--- | :--- | :--- | :--- |
| **`id`** | `bigint(20)` | `unsigned`, `primary key`, `auto_increment` | Unique identifier for each note. |
| **`title`** | `varchar(255)` | `not null` | The title of the note. |
| **`content`** | `text` | `not null` | The body text content of the note. |
| **`embedding`** | `json` | `nullable` | Stores the vector representation coordinates array (768 floats for Gemini, 1536 floats for OpenAI). |
| **`created_at`** | `timestamp` | `nullable` | Timestamp of note creation. |
| **`updated_at`** | `timestamp` | `nullable` | Timestamp of the last note update. |

* **Model Casts**: In [Note.php](file:///D:/ai-notes-system/backend/app/Models/Note.php), the `embedding` column is cast to a PHP `array`. This handles serialization/deserialization between the PHP application and the MySQL JSON representation automatically.

---

## Architectural & Code Quality Explanation

The system is built on **Clean Architecture** patterns, prioritizing separation of concerns, testability, and decoupling.

### 1. The Service-Repository Pattern
To prevent bloating controllers and models, logic is split into dedicated layers:
* **Repository Layer**: The [NoteRepository](file:///D:/ai-notes-system/backend/app/Repositories/NoteRepository.php) encapsulates all database queries. The application binds it through the [NoteRepositoryInterface](file:///D:/ai-notes-system/backend/app/Repositories/NoteRepositoryInterface.php) using Laravel's dependency injection (DI) container.
* **Service Layer**: Handles the domain and AI logic.
  * `SummaryService`: Manages note summarization caching workflows (using Redis to save API limits).
  * `SemanticSearchService`: Computes Cosine Similarity comparisons on float arrays and handles vector sorting.

### 2. Multi-Provider AI Abstraction
* To allow hot-swapping AI models without modifying core controller logic, we defined the [AIServiceInterface](file:///D:/ai-notes-system/backend/app/Services/AIServiceInterface.php) containing standard contracts for `generateEmbedding()` and `generateSummary()`.
* In [AppServiceProvider.php](file:///D:/ai-notes-system/backend/app/Providers/AppServiceProvider.php), the interface is bound dynamically:
  * If `AI_PROVIDER=gemini`, the DI container returns the `GeminiService`.
  * If `AI_PROVIDER=openai`, it returns the `OpenAIService`.

### 3. Redis Cache Orchestration
* Summaries are cached in **Redis** with a 24-hour TTL (`note_summary_{id}`).
* When a note is updated or deleted, the cache is automatically invalidated by calling `invalidateSummaryCache()` to ensure data consistency.

---

## AI-Assisted Development Methodology

This project utilized AI-assisted development tools (such as **Antigravity** and **GitHub Copilot**) to accelerate planning, writing math algorithms, and writing tests.

### 1. AI Tools Used
* **Antigravity (Google DeepMind)**: Served as a lead pair-programming agent, helping design the architecture, debug docker networking, setup Redis drivers, and write configuration abstractions.
* **GitHub Copilot**: Used for inline code completions, formatting swagger block annotations, and generating boilerplate test cases.

### 2. Key Prompt Templates Used
* **Architecture Planning Prompt**:
  > *"You are a senior Full-Stack Architect. Design a Notes management system using Laravel 11 and React. Decouple the database layer using the Repository pattern and define a generic AI service interface that supports swapping between Google Gemini and OpenAI. Keep controllers thin and write a Docker Compose configuration for MySQL, Redis, and Nginx."*
* **Vector Similarity Math Prompt**:
  > *"Write a PHP function in SemanticSearchService to calculate the Cosine Similarity between two arrays of floats (representing vector embeddings). Ensure it prevents division-by-zero errors when handling zero vectors or empty inputs by returning 0.0 safely."*
* **PHPUnit Mocking Prompt**:
  > *"Write a PHPUnit feature test for NoteController summary generation. Mock the AIServiceInterface using Mockery to prevent hitting live API endpoints during testing. Ensure the test asserts that a 200 OK response is returned with the mocked summary text."*

### 3. Generated Code Validation Strategy
To guarantee the safety and accuracy of AI-generated components, we established a strict three-layer validation system:
* **Mathematical Vector Assertions**: Implemented mathematical validation unit tests (`SemanticSearchTest.php`) verifying vector similarity values (orthogonal vector matches = `0.0`, identical = `1.0`, opposite = `-1.0`).
* **Deterministic Service Mocking**: Isolated the AI layers during test suite runs using Mockery. This prevents external network dependence and ensures tests are fast, cost-free, and stable.
* **System End-to-End Tests**: Ran integration feature tests covering the full database transaction, request validations (422 responses), CORS configurations, and cache clearing behaviors.

<!-- End of README -->

