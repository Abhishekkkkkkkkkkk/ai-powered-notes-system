# LuminaNotes — AI-Powered Notes Management System

LuminaNotes is a production-ready, full-stack notes management application built on a clean architectural pattern using **Laravel (PHP)**, **MySQL**, **React (Vite)**, **Tailwind CSS**, and **Google Gemini / OpenAI APIs**. 

It enables users to create and manage notes with traditional CRUD features, while enriching the workspace with **AI Semantic Vector Search** (using OpenAI or Gemini Embeddings) and **AI Note Summarization** (using Gemini Flash or OpenAI Chat Completions), backed by **Redis Caching** to limit third-party API costs and latency.

---

## Features

- **Standard Note CRUD**: Create, update, view, and delete notes.
- **Multi-Provider AI (Gemini & OpenAI)**: Toggle seamlessly between Google Gemini (completely free tier) and OpenAI.
- **AI Semantic Vector Search**: Find notes by contextual meaning instead of exact keywords using `text-embedding-004` (Gemini) or `text-embedding-3-small` (OpenAI) and Cosine Similarity math.
- **AI-Powered Note Summaries**: Generate concise 3-5 line note summaries via `gemini-1.5-flash` or `gpt-4o-mini` with instant Redis caching.
- **Dynamic Vector Re-indexing**: Artisan command `notes:regenerate-embeddings` enables migrating all stored embeddings instantly when switching between Gemini and OpenAI.
- **Modern Glassmorphism UI**: Clean dashboard interface equipped with loaders, empty states, and toast notifications.
- **Full Swagger/OpenAPI Documentation**: Automatically generated interactive API explorer.
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
- **Gemini Model**: `gemini-1.5-flash`
- **OpenAI Model**: `gpt-4o-mini`
- **Prompt**: `"You are a helpful notes assistant. Summarize the following note clearly in 3-5 concise lines. Keep it simple and relevant."`
- The `SummaryService` intercept checks if the note's summary exists in **Redis** (`note_summary_{id}`).
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

## AI Tools, Prompts & Code Validation

This project was built using AI-Assisted Development practices, co-piloted by **Antigravity** (developed by the Google DeepMind team). Below is the documentation of how AI was used, the prompts executed, and how the codebase was validated.

### 1. AI Tools Used
- **Antigravity (Google DeepMind)**: Used as the lead architect and pair programmer to generate the clean architecture layout, write files, manage dependencies, and diagnose Docker/container configurations.
- **Tailwind / Vite Config Tools**: Used to style the front-end layout with custom violet/slate glassmorphism elements.

### 2. Key Prompts Used
- **Planning Prompt**: *"You are a senior Full Stack AI Engineer and Laravel Architect. Build a complete production-ready AI-Powered Notes Management System using Laravel, MySQL, React, Tailwind CSS, and OpenAI APIs... generate a scalable directory structure."*
- **Gemini Integration Prompt**: *"Option 1: Use Google Gemini API. Refactor the backend to support a generic AIServiceInterface so the application can toggle between OpenAI and Google Gemini APIs without breaking downstream repositories or controllers."*
- **Docker Config Prompt**: *"Create a docker-compose.yml and Dockerfiles for PHP-FPM, Nginx, Redis, and MySQL to support local containerization."*
- **Mathematical Validation Prompt**: *"Write a unit test to verify that the cosine similarity calculations handle identical, orthogonal, opposite, and zero-dimensional vectors properly without division-by-zero errors."*
- **Troubleshooting Prompt**: *"Class 'Predis\Client' not found when executing Laravel config. Help me fix the composer lock sync and cache mapping."*

### 3. How the Generated Code Was Validated
All generated code underwent strict multi-layer validation checks:

1. **Mathematical Unit Testing**:
   - Implemented `SemanticSearchTest.php` to calculate and assert vector similarities. Identical vectors were tested to ensure they returned exactly `1.0`, orthogonal vectors returned `0.0`, and opposite vectors returned `-1.0`.
2. **REST API Endpoint Testing**:
   - Created `NoteApiTest.php` to test notes CRUD operations under mock conditions. This verified that API responses, validation error payloads, and HTTP status codes (201, 200, 404, 422) adhere strictly to REST specifications.
3. **Mocking External Services**:
   - AI embedding and completion APIs were mocked using Mockery in testing environments. This prevents outbound HTTP requests during tests, keeping test suites fast, free of charge, and deterministic.
4. **Local Host Troubleshooting & Debugging**:
   - **Port Conflict Isolation**: Identified that host port `3306` was occupied by a local MySQL service on the host computer. Solved it by modifying `docker-compose.yml` to map the database to host port `3307` while retaining container-to-container port `3306` queries.
   - **Directory Permissions**: Solved `bootstrap/cache` and `storage/` directory errors by creating directories with `.gitkeep` placeholders and setting container folder permissions to `777` via `chmod`.
   - **Composer Lock Synchronization**: Identified out-of-sync dependency caches. Solved by updating the composer dependencies with `composer update predis/predis`.
5. **Cross-Origin Resource Sharing (CORS) Bypass**:
   - Configured both Laravel `cors.php` configuration blocks AND Vite's internal port dev server proxy mapping (`/api` mapped to `http://webserver:80`). This guarantees the browser avoids CORS blockages on origin redirects.
