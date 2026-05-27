import React, { useState, useEffect, useCallback } from 'react';
import { api } from '../services/api';
import NoteCard from '../components/NoteCard';
import SearchBar from '../components/SearchBar';
import Pagination from '../components/Pagination';
import SummaryModal from '../components/SummaryModal';
import { PlusCircle, Sparkles, FileText, Loader2, AlertCircle, CheckCircle2 } from 'lucide-react';
import { Link } from 'react-router-dom';

export default function Dashboard() {
  // Notes and loading states
  const [notes, setNotes] = useState([]);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);
  const [lastPage, setLastPage] = useState(1);
  
  // Search states
  const [isSearching, setIsSearching] = useState(false);
  const [isSearchActive, setIsSearchActive] = useState(false);

  // Summary modal states
  const [modalOpen, setModalOpen] = useState(false);
  const [summaryLoading, setSummaryLoading] = useState(false);
  const [summaryText, setSummaryText] = useState('');
  const [selectedNoteTitle, setSelectedNoteTitle] = useState('');

  // Toast notifications state
  const [toast, setToast] = useState(null);

  // Notification helper
  const showToast = (message, type = 'success') => {
    setToast({ message, type });
    setTimeout(() => setToast(null), 3500);
  };

  // Fetch paginated notes list
  const fetchNotes = useCallback(async (pageNumber = 1) => {
    setLoading(true);
    try {
      const response = await api.getNotes(pageNumber, 9); // 9 items per page (3x3 grid)
      if (response.success) {
        setNotes(response.data);
        setPage(response.meta.current_page);
        setLastPage(response.meta.last_page);
      }
      setIsSearchActive(false);
    } catch (error) {
      showToast(error.message || 'Failed to load notes', 'error');
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchNotes(1);
  }, [fetchNotes]);

  // Handle AI semantic search
  const handleSearch = async (query) => {
    setIsSearching(true);
    setLoading(true);
    try {
      const response = await api.semanticSearch(query);
      if (response.success) {
        setNotes(response.data);
        setIsSearchActive(true);
        setPage(1);
        setLastPage(1); // Disable pagination during search
        showToast(`AI found ${response.data.length} matches`, 'success');
      }
    } catch (error) {
      showToast(error.message || 'Semantic search failed', 'error');
    } finally {
      setIsSearching(false);
      setLoading(false);
    }
  };

  // Clear search results and reload notes
  const handleClearSearch = () => {
    fetchNotes(1);
  };

  // Handle Note deletion
  const handleDelete = async (id) => {
    if (window.confirm('Are you sure you want to delete this note?')) {
      try {
        const response = await api.deleteNote(id);
        if (response.success) {
          showToast('Note deleted successfully', 'success');
          // Reload current page or fallback to page 1
          if (notes.length === 1 && page > 1) {
            fetchNotes(page - 1);
          } else {
            fetchNotes(page);
          }
        }
      } catch (error) {
        showToast(error.message || 'Failed to delete note', 'error');
      }
    }
  };

  // Handle AI summary generation
  const handleSummarize = async (id) => {
    const note = notes.find((n) => n.id === id);
    if (!note) return;

    setSelectedNoteTitle(note.title);
    setSummaryText('');
    setSummaryLoading(true);
    setModalOpen(true);

    try {
      const response = await api.generateSummary(id);
      if (response.success) {
        setSummaryText(response.data.summary);
      }
    } catch (error) {
      showToast(error.message || 'Failed to generate note summary', 'error');
      setModalOpen(false);
    } finally {
      setSummaryLoading(false);
    }
  };

  return (
    <div className="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 relative">
      
      {/* Toast Notification Banner */}
      {toast && (
        <div className="fixed bottom-5 right-5 z-50 flex items-center space-x-2 px-4 py-3 rounded-2xl glass-card border border-white/10 shadow-lg animate-slide-up">
          {toast.type === 'success' ? (
            <CheckCircle2 className="w-5 h-5 text-emerald-400 shrink-0" />
          ) : (
            <AlertCircle className="w-5 h-5 text-red-400 shrink-0" />
          )}
          <span className="text-sm font-semibold text-white">{toast.message}</span>
        </div>
      )}

      {/* Decorative Blur Backgrounds */}
      <div className="absolute top-20 left-1/4 w-[500px] h-[500px] glow-purple opacity-20 -z-10 rounded-full" />
      <div className="absolute bottom-20 right-1/4 w-[400px] h-[400px] glow-blue opacity-25 -z-10 rounded-full" />

      {/* Page Header */}
      <div className="text-center mb-10 max-w-2xl mx-auto">
        <h1 className="text-4xl font-extrabold tracking-tight text-white mb-2 bg-gradient-to-r from-white via-slate-100 to-brand-300 bg-clip-text text-transparent">
          My Knowledge Hub
        </h1>
        <p className="text-sm text-slate-400 font-light leading-relaxed">
          Create, edit, and organize your ideas. Leverage OpenAI semantic query matching and instant notes summarization.
        </p>
      </div>

      {/* Semantic Search Bar */}
      <SearchBar
        onSearch={handleSearch}
        onClear={handleClearSearch}
        isSearching={isSearching}
        isSearchActive={isSearchActive}
      />

      {/* Notes Grid Section */}
      {loading && notes.length === 0 ? (
        // Shimmering Skeleton Loader Cards
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {[...Array(6)].map((_, i) => (
            <div key={i} className="flex flex-col justify-between p-5 h-64 rounded-2xl glass-card border border-white/5 animate-pulse">
              <div className="space-y-4">
                <div className="h-5 bg-white/10 rounded-md w-3/4" />
                <div className="space-y-2">
                  <div className="h-3.5 bg-white/5 rounded-md w-full" />
                  <div className="h-3.5 bg-white/5 rounded-md w-5/6" />
                  <div className="h-3.5 bg-white/5 rounded-md w-2/3" />
                </div>
              </div>
              <div className="h-8 bg-white/5 rounded-md w-full mt-4" />
            </div>
          ))}
        </div>
      ) : notes.length === 0 ? (
        // Empty State UI
        <div className="flex flex-col items-center justify-center py-20 px-4 rounded-3xl glass-card border border-white/5 max-w-md mx-auto text-center animate-fade-in">
          <div className="flex items-center justify-center w-16 h-16 rounded-2xl bg-brand-500/10 border border-brand-500/20 text-brand-400 mb-5 glow-purple">
            <FileText className="w-8 h-8" />
          </div>
          <h2 className="text-xl font-bold text-white mb-2">No notes found</h2>
          <p className="text-sm text-slate-400 font-light mb-6">
            {isSearchActive
              ? "We couldn't find any match for your query. Try writing simpler concepts."
              : "Let's capture your first thought! Create a note to begin."}
          </p>
          {isSearchActive ? (
            <button
              onClick={handleClearSearch}
              className="px-5 py-2.5 rounded-xl text-sm font-semibold border border-white/10 text-slate-300 hover:text-white hover:bg-white/5 transition-all"
            >
              Clear Search Query
            </button>
          ) : (
            <Link
              to="/create"
              className="flex items-center space-x-1.5 px-5 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 text-white shadow-lg border border-brand-400/20 transition-all hover:scale-105 active:scale-95"
            >
              <PlusCircle className="w-4.5 h-4.5" />
              <span>Create First Note</span>
            </Link>
          )}
        </div>
      ) : (
        // Notes Listings Grid
        <>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {notes.map((note) => (
              <NoteCard
                key={note.id}
                note={note}
                onDelete={handleDelete}
                onSummarize={handleSummarize}
              />
            ))}
          </div>

          {/* Standard Pagination Controls */}
          {!isSearchActive && (
            <Pagination
              currentPage={page}
              lastPage={lastPage}
              onPageChange={(pageNumber) => fetchNotes(pageNumber)}
            />
          )}
        </>
      )}

      {/* AI Summary Modal Overlay */}
      <SummaryModal
        isOpen={modalOpen}
        onClose={() => setModalOpen(false)}
        summary={summaryText}
        noteTitle={selectedNoteTitle}
        isLoading={summaryLoading}
      />
    </div>
  );
}
