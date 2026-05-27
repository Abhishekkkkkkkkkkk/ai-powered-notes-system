import React from 'react';
import { Link, useLocation } from 'react-router-dom';
import { Sparkles, FileText, PlusCircle } from 'lucide-react';

export default function Navbar() {
  const location = useLocation();
  const isDashboard = location.pathname === '/';

  return (
    <nav className="sticky top-0 z-50 w-full glass-card border-b border-white/5 backdrop-blur-md bg-dark-950/80">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex items-center justify-between h-16">
          {/* Logo Brand */}
          <Link to="/" className="flex items-center space-x-2 group">
            <div className="flex items-center justify-center w-9 h-9 rounded-xl bg-brand-500/10 border border-brand-500/30 group-hover:border-brand-500/60 transition-all duration-300">
              <Sparkles className="w-5 h-5 text-brand-400 group-hover:text-brand-300 transition-colors animate-pulse" />
            </div>
            <span className="text-xl font-bold tracking-tight bg-gradient-to-r from-white via-slate-200 to-brand-300 bg-clip-text text-transparent group-hover:text-glow">
              Lumina<span className="text-brand-400">Notes</span>
            </span>
          </Link>

          {/* Navigation Links */}
          <div className="flex items-center space-x-4">
            <Link
              to="/"
              className={`flex items-center space-x-1.5 px-3.5 py-2 rounded-xl text-sm font-medium transition-all duration-200 ${
                isDashboard
                  ? 'bg-white/5 text-white border border-white/10'
                  : 'text-slate-400 hover:text-slate-200 hover:bg-white/5'
              }`}
            >
              <FileText className="w-4 h-4" />
              <span>Dashboard</span>
            </Link>
            
            <Link
              to="/create"
              className="flex items-center space-x-1.5 px-4 py-2 rounded-xl text-sm font-semibold bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 text-white shadow-lg shadow-brand-500/15 border border-brand-400/20 hover:border-brand-400/40 hover:scale-[1.02] active:scale-[0.98] transition-all duration-200"
            >
              <PlusCircle className="w-4 h-4" />
              <span>New Note</span>
            </Link>
          </div>
        </div>
      </div>
    </nav>
  );
}
