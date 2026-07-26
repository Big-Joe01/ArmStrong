'use client';
import React from 'react';

const instagramImages = [
  {
    src: 'https://armstrongceilings.com.au/wp-content/uploads/Floc-Roll-Distributed-by-Armstrong-Ceiling-Solutions-819x1024.jpg',
    alt: 'Floc Roll Distributed by Armstrong Ceiling Solutions',
  },
  {
    src: 'https://armstrongceilings.com.au/wp-content/uploads/Armstrong-Mesh-Ceiling-1-819x1024.jpg',
    alt: 'Armstrong Mesh Ceiling',
  },
  {
    src: 'https://armstrongceilings.com.au/wp-content/uploads/Timber-Ceiling-and-SoundScapes-Shapes-819x1024.jpg',
    alt: 'Timber Ceiling and SoundScapes Shapes',
  },
  {
    src: 'https://armstrongceilings.com.au/wp-content/uploads/Industrial-Chic-Ceiling-Design-819x1024.jpg',
    alt: 'Industrial Chic Ceiling Design',
  },
  {
    src: 'https://armstrongceilings.com.au/wp-content/uploads/Architectural-Elegance.-MetalWorks-Mesh.-Instagram-Post-45-819x1024.jpg',
    alt: 'Architectural Elegance. MetalWorks Mesh.',
  },
];

export default function InstagramSection() {
  return (
    <section className="py-16 px-6" style={{ backgroundColor: '#1E1B1C' }}>
      <div className="max-w-[1290px] mx-auto flex flex-col md:flex-row gap-12">
        {/* Left - Header */}
        <div className="md:w-1/3">
          <h2
            className="text-white text-3xl mb-3"
            style={{ fontFamily: 'Graphik Medium, sans-serif', fontWeight: 'normal' }}
          >
            Follow us on Instagram
          </h2>
          <h3 className="text-[#507EEF] text-lg">
            <a
              href="https://www.instagram.com/armstrongceilingsolutionsau/"
              target="_blank"
              rel="noopener noreferrer"
              className="hover:text-white transition-colors"
              style={{ fontFamily: 'Graphik Medium, sans-serif', fontWeight: 'normal' }}
            >
              @armstrongceilingsolutionsau
            </a>
          </h3>
        </div>

        {/* Right - Image Carousel */}
        <div className="md:w-2/3 overflow-hidden">
          <div className="flex gap-2">
            {instagramImages?.map((img, i) => (
              <a
                key={i}
                href="https://www.instagram.com/armstrongceilingsolutionsau/"
                target="_blank"
                rel="noopener noreferrer"
                className="flex-shrink-0 overflow-hidden"
                style={{ width: 'calc(33.333% - 6px)' }}
              >
                <img
                  src={img?.src}
                  alt={img?.alt}
                  className="w-full h-48 object-cover hover:scale-105 transition-transform duration-300"
                />
              </a>
            ))}
          </div>
        </div>
      </div>
    </section>
  );
}
