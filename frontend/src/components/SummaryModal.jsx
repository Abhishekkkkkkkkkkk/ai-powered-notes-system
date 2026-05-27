import React, { useState } from 'react';
import { X, Copy, Check, Sparkles, Loader2 } from 'lucide-react';

export default function SummaryModal({ isOpen, onClose, summary, noteTitle, isLoading = false }) {
  const [copied, setCopied] = useState(false);

  if (!isOpen) return null;

  const handleCopy = () => {
    if (summary) {
      navigator.clipboard.writeText(summary);
      setCopied(true);
      setTimeout(() => setCopied(false), 2000);
    }
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-dark-950/80 backdrop-blur-md animate-fade-in">
      {/* Modal Container */}
      <div className="relative w-full max-w-lg rounded-3xl glass-card border border-white/10 shadow-2xl p-6 glow-purple animate-slide-up">
        
        {/* Modal Header */}
        <div className="flex items-start justify-between pb-4 border-b border-white/5 mb-5">
          <div className="flex items-center space-x-2">
            <div className="flex items-center justify-center w-8 h-8 rounded-lg bg-brand-500/20 text-brand-400">
              <Sparkles className="w-4 h-4 text-brand-300" />
            </div>
            <div>
              <h2 className="text-md font-bold text-white tracking-tight">AI Note Summary</h2>
              <p className="text-xs text-slate-400 line-clamp-1">For: {noteTitle}</p>
            </div>
          </div>
          
          <button
            onClick={onClose}
            className="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/5 transition-colors"
          >
            <X className="w-4.5 h-4.5" />
          </button>
        </div>

        {/* Modal Body / Summary Output */}
        <div className="min-h-[140px] flex flex-col justify-center">
          {isLoading ? (
            <div className="flex flex-col items-center justify-center py-6 space-y-3">
              <Loader2 className="w-8 h-8 text-brand-500 animate-spin" />
              <p className="text-xs text-slate-400 font-medium">Generating concise summary via OpenAI...</p>
            </div>
          ) : (
            <div className="text-slate-200 text-sm leading-relaxed whitespace-pre-line font-light py-2 bg-white/5 px-4 rounded-xl border border-white/5 select-text">
              {summary}
            </div>
          )}
        </div>

        {/* Modal Footer Actions */}
        {!isLoading && (
          <div className="flex items-center justify-end mt-6 pt-4 border-t border-white/5 gap-3">
            {/* Copy Button */}
            <button
              onClick={handleCopy}
              disabled={!summary}
              className="flex items-center space-x-1.5 px-4 py-2 rounded-xl text-xs font-semibold border border-white/10 hover:border-white/20 text-slate-300 hover:text-white hover:bg-white/5 disabled:opacity-40 transition-all duration-200"
            >
              {copied ? (
                <>
                  <Check className="w-3.5 h-3.5 text-emerald-400" />
                  <span className="text-emerald-400">Copied!</span>
                </>
              ) : (
                <>
                  <Copy className="w-3.5 h-3.5" />
                  <span>Copy Summary</span>
                </>
              )}
            </button>

            {/* Close Button */}
            <button
              onClick={onClose}
              className="px-5 py-2 rounded-xl text-xs font-semibold bg-brand-600 hover:bg-brand-500 text-white shadow-md shadow-brand-500/10 border border-brand-400/20 active:scale-95 transition-all duration-150"
            >
              Close
            </button>
          </div>
        )}
      </div>
    </div>
  );
}
