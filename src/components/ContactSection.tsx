'use client';
import React from 'react';

export default function ContactSection() {
  return (
    <section className="w-full">
      <div className="flex flex-col md:flex-row">
        {/* Left - Contact Info */}
        <div
          className="w-full md:w-1/2 relative flex flex-col justify-center px-12 py-20 min-h-[400px]"
          style={{
            backgroundImage:
              'url(https://armstrongceilings.com.au/wp-content/uploads/MONASH_BLTB_FINAL_006_A3.jpg)',
            backgroundSize: 'cover',
            backgroundPosition: 'center',
          }}
        >
          <div className="absolute inset-0 bg-[#507EEF]/80" />
          <div className="relative z-10">
            <h2
              className="text-white text-3xl mb-4"
              style={{ fontFamily: 'Graphik Medium, sans-serif', fontWeight: 'normal' }}
            >
              Contact Us
            </h2>
            <p
              className="text-white text-base leading-relaxed mb-6"
              style={{ fontFamily: 'Graphik Regular, sans-serif' }}
            >
              Still have questions? Please contact us with any ceiling, wall or acoustical enquiry.
            </p>
            <a
              href="https://armstrongceilings.com.au/contact-us/"
              className="inline-block bg-white text-[#507EEF] text-xs uppercase tracking-wider px-6 py-3 rounded-full hover:bg-[#1E1B1C] hover:text-white transition-colors"
              style={{ fontFamily: 'Graphik Medium, sans-serif' }}
            >
              get in touch
            </a>
          </div>
        </div>

        {/* Right - Image */}
        <div
          className="w-full md:w-1/2 min-h-[400px]"
          style={{
            backgroundImage:
              'url(https://armstrongceilings.com.au/wp-content/uploads/Armstrong-Ceilings-Mesh-Ceiling.jpg)',
            backgroundSize: 'cover',
            backgroundPosition: 'center',
          }}
        />
      </div>
    </section>
  );
}
