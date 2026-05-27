import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { Save, ArrowLeft, Loader2 } from 'lucide-react';

export default function NoteForm({ initialValues, onSubmit, submitLabel = 'Save Note', isSubmitting = false }) {
  const [title, setTitle] = useState('');
  const [content, setContent] = useState('');
  const [errors, setErrors] = useState({});

  // Sync initial values when they load (important for Edit Note)
  useEffect(() => {
    if (initialValues) {
      setTitle(initialValues.title || '');
      setContent(initialValues.content || '');
    }
  }, [initialValues]);

  // Client-side validation helper
  const validateForm = () => {
    const newErrors = {};
    if (!title.trim()) {
      newErrors.title = 'Title is required';
    } else if (title.length > 255) {
      newErrors.title = 'Title must be less than 255 characters';
    }
    
    if (!content.trim()) {
      newErrors.content = 'Content is required';
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    if (validateForm()) {
      onSubmit({ title, content });
    }
  };

  return (
    <form onSubmit={handleSubmit} className="w-full space-y-6 animate-fade-in">
      {/* Title Input field */}
      <div className="space-y-2">
        <label htmlFor="title" className="text-sm font-semibold text-slate-300 tracking-wide">
          Note Title
        </label>
        <input
          type="text"
          id="title"
          value={title}
          onChange={(e) => {
            setTitle(e.target.value);
            if (errors.title) setErrors((prev) => ({ ...prev, title: null }));
          }}
          disabled={isSubmitting}
          placeholder="e.g. Design Patterns in Laravel Architecture"
          className={`w-full px-4 py-3 rounded-xl border text-white text-base glass-input ${
            errors.title
              ? 'border-red-500/50 focus:border-red-500 focus:shadow-red-500/10'
              : 'border-white/10 focus:border-brand-500'
          }`}
        />
        {errors.title && (
          <p className="text-xs text-red-400 font-medium select-none">{errors.title}</p>
        )}
      </div>

      {/* Content Textarea field */}
      <div className="space-y-2">
        <label htmlFor="content" className="text-sm font-semibold text-slate-300 tracking-wide">
          Note Content
        </label>
        <textarea
          id="content"
          rows="12"
          value={content}
          onChange={(e) => {
            setContent(e.target.value);
            if (errors.content) setErrors((prev) => ({ ...prev, content: null }));
          }}
          disabled={isSubmitting}
          placeholder="Start writing your note content... Markdown syntax or paragraphs are welcome."
          className={`w-full px-4 py-3 rounded-xl border text-white text-base glass-input resize-y min-h-[200px] ${
            errors.content
              ? 'border-red-500/50 focus:border-red-500 focus:shadow-red-500/10'
              : 'border-white/10 focus:border-brand-500'
          }`}
        />
        {errors.content && (
          <p className="text-xs text-red-400 font-medium select-none">{errors.content}</p>
        )}
      </div>

      {/* Action Buttons */}
      <div className="flex items-center justify-between pt-4 gap-4">
        <Link
          to="/"
          className="flex items-center space-x-1.5 px-4 py-2.5 rounded-xl text-sm font-semibold border border-white/10 text-slate-300 hover:text-white hover:bg-white/5 transition-all duration-200"
        >
          <ArrowLeft className="w-4 h-4" />
          <span>Cancel</span>
        </Link>

        <button
          type="submit"
          disabled={isSubmitting}
          className="flex items-center space-x-2 px-6 py-2.5 rounded-xl text-sm font-semibold bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 text-white shadow-lg shadow-brand-500/15 border border-brand-400/20 hover:border-brand-400/40 hover:scale-[1.02] active:scale-[0.98] disabled:scale-100 disabled:opacity-50 transition-all duration-200"
        >
          {isSubmitting ? (
            <>
              <Loader2 className="w-4 h-4 animate-spin" />
              <span>Saving...</span>
            </>
          ) : (
            <>
              <Save className="w-4 h-4" />
              <span>{submitLabel}</span>
            </>
          )}
        </button>
      </div>
    </form>
  );
}
