import React from 'react';
import { Link } from 'react-router-dom';
import { Calendar, Edit3, Trash2, FileJson, Sparkles } from 'lucide-react';

export default function NoteCard({ note, onDelete, onSummarize }) {
  // Helper to format date
  const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric'
    });
  };

  // Truncate content for card layout
  const truncateContent = (text, maxLength = 160) => {
    if (!text) return '';
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength).trim() + '...';
  };

  // Convert similarity score to percentage
  const matchPercentage = note.similarity_score !== undefined
    ? Math.max(0, Math.min(100, Math.round(note.similarity_score * 100)))
    : null;

  return (
    <div className="relative group flex flex-col justify-between p-5 rounded-2xl glass-card glass-card-hover border border-white/5 overflow-hidden animate-fade-in-up">
      {/* Background ambient glow if high similarity match */}
      {matchPercentage !== null && matchPercentage > 60 && (
        <div className="absolute -top-12 -right-12 w-24 h-24 bg-brand-500/10 rounded-full blur-2xl group-hover:bg-brand-500/15 transition-all duration-300 pointer-events-none" />
      )}

      <div>
        {/* Card Header */}
        <div className="flex items-start justify-between gap-3 mb-3">
          <h3 className="text-lg font-bold text-white group-hover:text-brand-300 transition-colors line-clamp-1">
            {note.title}
          </h3>

          {/* AI Similarity Badge */}
          {matchPercentage !== null && (
            <span className={`inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full text-xs font-semibold tracking-wide shrink-0 ${
              matchPercentage > 75
                ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20'
                : matchPercentage > 45
                ? 'bg-brand-500/10 text-brand-400 border border-brand-500/20'
                : 'bg-slate-500/10 text-slate-400 border border-slate-500/20'
            }`}>
              <Sparkles className="w-3 h-3 animate-pulse" />
              <span>{matchPercentage}% Match</span>
            </span>
          )}
        </div>

        {/* Card Body */}
        <p className="text-sm text-slate-300 leading-relaxed mb-6 font-light whitespace-pre-line">
          {truncateContent(note.content)}
        </p>
      </div>

      {/* Card Footer */}
      <div className="flex items-center justify-between pt-4 border-t border-white/5 text-xs text-slate-400">
        <div className="flex items-center space-x-1">
          <Calendar className="w-3.5 h-3.5 text-slate-500" />
          <span>{formatDate(note.created_at)}</span>
        </div>

        <div className="flex items-center space-x-2">
          {/* AI Summarize Button */}
          <button
            onClick={() => onSummarize(note.id)}
            title="Generate AI Summary"
            className="p-2 rounded-lg bg-brand-500/10 hover:bg-brand-500/20 border border-brand-500/20 hover:border-brand-500/40 text-brand-400 hover:text-brand-300 transition-all duration-200"
          >
            <Sparkles className="w-3.5 h-3.5" />
          </button>

          {/* Edit Button */}
          <Link
            to={`/edit/${note.id}`}
            title="Edit Note"
            className="p-2 rounded-lg bg-white/5 hover:bg-white/10 border border-white/10 text-slate-300 hover:text-white transition-all duration-200"
          >
            <Edit3 className="w-3.5 h-3.5" />
          </Link>

          {/* Delete Button */}
          <button
            onClick={() => onDelete(note.id)}
            title="Delete Note"
            className="p-2 rounded-lg bg-red-500/10 hover:bg-red-500/20 border border-red-500/20 hover:border-red-500/40 text-red-400 hover:text-red-300 transition-all duration-200"
          >
            <Trash2 className="w-3.5 h-3.5" />
          </button>
        </div>
      </div>
    </div>
  );
}
