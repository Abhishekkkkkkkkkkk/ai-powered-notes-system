import axios from 'axios';

// Resolve backend endpoint (uses vite proxy locally, or fallback)
const API_URL = import.meta.env.VITE_API_URL || '/api';

const apiClient = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
});

/**
 * Handle API calls gracefully and standardise response errors.
 */
const handleRequest = async (requestPromise) => {
  try {
    const response = await requestPromise;
    return response.data;
  } catch (error) {
    const message = error.response?.data?.message || 'Something went wrong. Please try again.';
    const errors = error.response?.data?.errors || null;
    
    // Throw parsed error structure
    throw {
      success: false,
      message,
      errors,
      status: error.response?.status || 500
    };
  }
};

export const api = {
  /**
   * Get paginated notes.
   */
  getNotes: (page = 1, limit = 10) => {
    return handleRequest(apiClient.get(`/notes?page=${page}&limit=${limit}`));
  },

  /**
   * Get single note details.
   */
  getNote: (id) => {
    return handleRequest(apiClient.get(`/notes/${id}`));
  },

  /**
   * Create a new note.
   */
  createNote: (noteData) => {
    return handleRequest(apiClient.post('/notes', noteData));
  },

  /**
   * Update an existing note.
   */
  updateNote: (id, noteData) => {
    return handleRequest(apiClient.put(`/notes/${id}`, noteData));
  },

  /**
   * Delete a note.
   */
  deleteNote: (id) => {
    return handleRequest(apiClient.delete(`/notes/${id}`));
  },

  /**
   * Semantic search notes using OpenAI embedding comparisons.
   */
  semanticSearch: (query) => {
    return handleRequest(apiClient.post('/notes/search', { query }));
  },

  /**
   * Generate an AI-powered summary for a note.
   */
  generateSummary: (id) => {
    return handleRequest(apiClient.post(`/notes/${id}/summary`));
  }
};

export default api;
