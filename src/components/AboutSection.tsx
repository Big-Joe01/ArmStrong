'use client';
import React from 'react';

export default function AboutSection() {
  return (
    <section className="bg-[#507EEF] py-12 px-6" style={{ animation: 'fadeIn 0.6s ease-in-out' }}>
      <div className="max-w-[1290px] mx-auto flex flex-col md:flex-row gap-8 md:gap-16">
        {/* Left - Heading */}
        <div className="md:w-1/3">
          <h2
            className="text-white text-2xl md:text-3xl leading-tight"
            style={{ fontFamily: 'Graphik Medium, sans-serif', fontWeight: 'normal' }}
          >
            <strong>Celebrating over 65 years</strong> in the supply of acoustic ceilings.
          </h2>
        </div>

        {/* Right - Text */}
        <div className="md:w-2/3">
          <div
            className="text-white text-base leading-relaxed"
            style={{ fontFamily: 'Graphik Regular, sans-serif' }}
          >
            <p className="mb-4">
              For over 65 years, we have been a trusted, dependable supplier of acoustic suspended
              ceiling systems in the Australian market. Since our journey began in 1961 with mineral
              fibre acoustic ceiling systems, we have continuously evolved and today we supply a
              diverse range of high-performance wall and ceiling systems for commercial projects.
            </p>
            <p>
              While our product range has grown over the past six decades, our purpose remains
              unchanged: to enhance the wellbeing of building occupants.&nbsp;
            </p>
          </div>
        </div>
      </div>
    </section>
  );
}
