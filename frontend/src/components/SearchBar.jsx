import React, { useState } from 'react';
import { Search, Sparkles, X, Loader2 } from 'lucide-react';

export default function SearchBar({ onSearch, onClear, isSearching = false, isSearchActive = false }) {
  const [query, setQuery] = useState('');

  const handleSubmit = (e) => {
    e.preventDefault();
    if (query.trim()) {
      onSearch(query);
    }
  };

  const handleClear = () => {
    setQuery('');
    onClear();
  };

  return (
    <form onSubmit={handleSubmit} className="w-full relative max-w-2xl mx-auto mb-8 animate-fade-in-up">
      {/* Search Input Container */}
      <div className="relative flex items-center">
        <div className="absolute left-4 text-slate-400">
          <Search className="w-5 h-5" />
        </div>

        <input
          type="text"
          value={query}
          onChange={(e) => setQuery(e.target.value)}
          placeholder="Search notes semantically (e.g. 'how do I build clean code in Laravel?')..."
          disabled={isSearching}
          className="w-full pl-12 pr-32 py-3.5 rounded-2xl text-white text-sm glass-input placeholder-slate-400 border border-white/5 shadow-inner"
        />

        {/* Action Buttons inside Input */}
        <div className="absolute right-2.5 flex items-center space-x-1.5">
          {/* Clear Search Input */}
          {(query || isSearchActive) && (
            <button
              type="button"
              onClick={handleClear}
              className="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/5 transition-colors"
            >
              <X className="w-4 h-4" />
            </button>
          )}

          {/* Submit Search */}
          <button
            type="submit"
            disabled={isSearching || !query.trim()}
            className="flex items-center space-x-1.5 px-4 py-2 rounded-xl text-xs font-semibold bg-brand-500 hover:bg-brand-400 disabled:opacity-40 disabled:hover:bg-brand-500 text-white shadow-md shadow-brand-500/10 border border-brand-400/20 active:scale-95 transition-all duration-150"
          >
            {isSearching ? (
              <Loader2 className="w-3.5 h-3.5 animate-spin" />
            ) : (
              <Sparkles className="w-3.5 h-3.5 text-brand-200" />
            )}
            <span>AI Search</span>
          </button>
        </div>
      </div>

      {/* Semantic search note info */}
      {isSearchActive && (
        <div className="flex items-center justify-between mt-2.5 px-2 animate-fade-in">
          <p className="text-xs text-brand-400 flex items-center gap-1.5 font-medium">
            <Sparkles className="w-3 h-3 text-brand-400 animate-pulse" />
            <span>Showing notes ranked by vector similarity</span>
          </p>
          <button
            onClick={handleClear}
            className="text-xs text-slate-400 hover:text-white underline transition-colors"
          >
            Clear Search
          </button>
        </div>
      )}
    </form>
  );
}
