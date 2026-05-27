import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { api } from '../services/api';
import NoteForm from '../components/NoteForm';
import { Sparkles, FileText, AlertCircle } from 'lucide-react';

export default function CreateNote() {
  const navigate = useNavigate();
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [errorMessage, setErrorMessage] = useState(null);

  const handleCreate = async (formData) => {
    setIsSubmitting(true);
    setErrorMessage(null);
    try {
      const response = await api.createNote(formData);
      if (response.success) {
        // Note successfully stored (and embedding generated)
        navigate('/', { state: { message: 'Note created successfully!' } });
      }
    } catch (error) {
      setErrorMessage(error.message || 'Failed to save the note. Please check inputs.');
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <div className="flex-1 w-full max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12 relative">
      {/* Decorative Radial Backgrounds */}
      <div className="absolute top-10 left-1/2 w-[350px] h-[350px] glow-purple opacity-20 -z-10 rounded-full -translate-x-1/2" />

      {/* Header section */}
      <div className="flex items-center space-x-3 mb-8">
        <div className="flex items-center justify-center w-10 h-10 rounded-xl bg-brand-500/10 border border-brand-500/20 text-brand-400">
          <FileText className="w-5 h-5 text-brand-400" />
        </div>
        <div>
          <h1 className="text-2xl font-bold text-white tracking-tight">Create New Note</h1>
          <p className="text-xs text-slate-400 font-light">Capture your knowledge. OpenAI embeddings will be computed automatically.</p>
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

      {/* Form Container */}
      <div className="p-6 sm:p-8 rounded-3xl glass-card border border-white/5 shadow-2xl">
        <NoteForm
          onSubmit={handleCreate}
          submitLabel="Create Note"
          isSubmitting={isSubmitting}
        />
      </div>
    </div>
  );
}
