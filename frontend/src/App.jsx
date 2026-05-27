import React from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import Navbar from './components/Navbar';
import Dashboard from './pages/Dashboard';
import CreateNote from './pages/CreateNote';
import EditNote from './pages/EditNote';

export default function App() {
  return (
    <Router>
      <div className="min-h-screen flex flex-col bg-dark-950 text-slate-100 font-sans selection:bg-brand-500/30">
        
        {/* Navigation Header */}
        <Navbar />

        {/* Main Workspace Frame */}
        <main className="flex-1 flex flex-col">
          <Routes>
            {/* Notes List Dashboard */}
            <Route path="/" element={<Dashboard />} />

            {/* Create Note Form Screen */}
            <Route path="/create" element={<CreateNote />} />

            {/* Edit Note Form Screen */}
            <Route path="/edit/:id" element={<EditNote />} />

            {/* Fallback to Dashboard */}
            <Route path="*" element={<Navigate to="/" replace />} />
          </Routes>
        </main>

        {/* Minimalist Footer */}
        <footer className="w-full border-t border-white/5 bg-dark-950 py-6 text-center select-none">
          <p className="text-xs text-slate-500 font-light">
            &copy; {new Date().getFullYear()} LuminaNotes. Engineered with Laravel, React, and OpenAI.
          </p>
        </footer>

      </div>
    </Router>
  );
}
