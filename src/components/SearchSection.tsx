'use client';
import React from 'react';

const searchCategories = [
  'Mineral Fibre Acoustic Ceiling Systems',
  'Metal Ceilings & Baffles',
  'Timber Ceilings',
  'Heradesign Wood Wool',
  'Soft Fibre Baffles',
  'Suspension Systems',
  'Wall Panels',
];

export default function SearchSection() {
  return (
    <section className="bg-[#1E1B1C] py-8 px-6">
      <div className="max-w-[1290px] mx-auto">
        <div className="relative">
          <input
            type="text"
            placeholder="Search for a product"
            className="w-full bg-transparent border-b-2 border-white/30 text-white placeholder-white/50 py-3 pr-10 text-lg focus:outline-none focus:border-white transition-colors"
            style={{ fontFamily: 'Graphik Regular, sans-serif' }}
          />
          <button className="absolute right-0 top-1/2 -translate-y-1/2 text-white/60 hover:text-white transition-colors">
            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </button>
        </div>
      </div>
    </section>
  );
}
