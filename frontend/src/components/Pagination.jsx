import React from 'react';
import { ChevronLeft, ChevronRight } from 'lucide-react';

export default function Pagination({ currentPage, lastPage, onPageChange }) {
  if (lastPage <= 1) return null;

  return (
    <div className="flex items-center justify-center space-x-6 mt-12 animate-fade-in">
      {/* Previous Button */}
      <button
        onClick={() => onPageChange(currentPage - 1)}
        disabled={currentPage === 1}
        className="flex items-center space-x-1 px-3 py-2 rounded-xl text-sm font-semibold border border-white/5 bg-white/5 text-slate-300 hover:text-white hover:bg-white/10 disabled:opacity-30 disabled:hover:bg-white/5 disabled:hover:text-slate-300 transition-all duration-200"
      >
        <ChevronLeft className="w-4 h-4" />
        <span>Prev</span>
      </button>

      {/* Page Info */}
      <span className="text-xs font-semibold text-slate-400 select-none">
        Page <span className="text-white text-sm px-1.5 py-0.5 rounded-lg bg-white/5 border border-white/5">{currentPage}</span> of <span className="text-slate-300">{lastPage}</span>
      </span>

      {/* Next Button */}
      <button
        onClick={() => onPageChange(currentPage + 1)}
        disabled={currentPage === lastPage}
        className="flex items-center space-x-1 px-3 py-2 rounded-xl text-sm font-semibold border border-white/5 bg-white/5 text-slate-300 hover:text-white hover:bg-white/10 disabled:opacity-30 disabled:hover:bg-white/5 disabled:hover:text-slate-300 transition-all duration-200"
      >
        <span>Next</span>
        <ChevronRight className="w-4 h-4" />
      </button>
    </div>
  );
}
