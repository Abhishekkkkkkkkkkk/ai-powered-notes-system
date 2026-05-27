import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { api } from '../services/api';
import NoteForm from '../components/NoteForm';
import { Edit3, AlertCircle, Loader2 } from 'lucide-react';

export default function EditNote() {
  const { id } = useParams();
  const navigate = useNavigate();

  // Component states
  const [note, setNote] = useState(null);
  const [loading, setLoading] = useState(true);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [errorMessage, setErrorMessage] = useState(null);

  // Fetch the note details when component loads
  useEffect(() => {
    const fetchNoteDetails = async () => {
      setLoading(true);
      try {
        const response = await api.getNote(id);
        if (response.success) {
          setNote(response.data);
        }
      } catch (error) {
        setErrorMessage(error.message || 'Note could not be retrieved.');
      } finally {
        setLoading(false);
      }
    };

    fetchNoteDetails();
  }, [id]);

  const handleUpdate = async (formData) => {
    setIsSubmitting(true);
    setErrorMessage(null);
    try {
      const response = await api.updateNote(id, formData);
      if (response.success) {
        // Updated note successfully (and embedding recalculated)
        navigate('/', { state: { message: 'Note updated successfully!' } });
      }
    } catch (error) {
      setErrorMessage(error.message || 'Failed to update note details.');
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <div className="flex-1 w-full max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12 relative">
      {/* Decorative Blur Backgrounds */}
      <div className="absolute top-10 left-1/2 w-[350px] h-[350px] glow-purple opacity-20 -z-10 rounded-full -translate-x-1/2" />

      {/* Page Title */}
      <div className="flex items-center space-x-3 mb-8">
        <div className="flex items-center justify-center w-10 h-10 rounded-xl bg-brand-500/10 border border-brand-500/20 text-brand-400">
          <Edit3 className="w-5 h-5 text-brand-400" />
        </div>
        <div>
          <h1 className="text-2xl font-bold text-white tracking-tight">Edit Note</h1>
          <p className="text-xs text-slate-400 font-light">Revise your content. AI embeddings and summaries will be recalculated.</p>
        </div>
      </div>

      {/* Backend API Error Banner */}
      {errorMessage && (
        <div className="flex items-start space-x-2.5 p-4 mb-6 rounded-2xl bg-red-500/10 border border-red-500/25 text-red-400 text-sm animate-fade-in">
          <AlertCircle className="w-5 h-5 text-red-400 shrink-0 mt-0.5" />
          <div className="flex-1">
            <h3 className="font-semibold text-white">Request failed</h3>
            <p className="font-light text-xs text-red-300/90">{errorMessage}</p>
          </div>
        </div>
      )}

      {/* Note Form Container */}
      <div className="p-6 sm:p-8 rounded-3xl glass-card border border-white/5 shadow-2xl">
        {loading ? (
          <div className="flex flex-col items-center justify-center py-20 space-y-3">
            <Loader2 className="w-10 h-10 text-brand-500 animate-spin" />
            <p className="text-xs text-slate-400 font-medium">Fetching note details from server...</p>
          </div>
        ) : note ? (
          <NoteForm
            initialValues={{ title: note.title, content: note.content }}
            onSubmit={handleUpdate}
            submitLabel="Save Changes"
            isSubmitting={isSubmitting}
          />
        ) : (
          <div className="text-center py-10">
            <p className="text-sm text-slate-400">Note not found or has been removed.</p>
          </div>
        )}
      </div>
    </div>
  );
}
