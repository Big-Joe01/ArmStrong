'use client';
import React, { useState, useEffect, useCallback } from 'react';

interface Slide {
  id: number;
  title: string;
  subtitle: string;
  heading: string;
  buttonText: string;
  buttonHref: string;
  bgImage: string;
  type: 'standard' | 'grid';
}

const slides: Slide[] = [
  {
    id: 1,
    title: 'FLOC ROLL',
    subtitle: '',
    heading: '100% Wool Acoustic\nWall Treatment',
    buttonText: 'LEARN MORE',
    buttonHref: 'https://armstrongceilings.com.au/product/walls/floc-roll/',
    bgImage:
      'https://armstrongceilings.com.au/wp-content/uploads/Floc-Roll-Threefold-Architecture.jpg',
    type: 'standard',
  },
  {
    id: 2,
    title: 'ARCHITECTURAL ELEGANCE.',
    subtitle: '',
    heading: 'MetalWorks\nMesh',
    buttonText: 'EXPLORE THE RANGE',
    buttonHref: 'https://armstrongceilings.com.au/product/ceilings/mesh-ceilings/',
    bgImage:
      'https://armstrongceilings.com.au/wp-content/uploads/Armstrong-Ceilings-Mesh-Ceiling.jpg',
    type: 'standard',
  },
  {
    id: 3,
    title: 'WoodWorks FR',
    subtitle: '',
    heading: 'Featuring Laminex Finishes',
    buttonText: 'CLICK TO SEE THE RANGE',
    buttonHref:
      'https://armstrongceilings.com.au/product/suspended-ceilings/timber-ceiling-panels/',
    bgImage:
      'https://armstrongceilings.com.au/wp-content/uploads/Armstrong-WoodWorks-Vector-Ceiling-Panels.jpg',
    type: 'standard',
  },
  {
    id: 4,
    title: 'book an in person cpd with us',
    subtitle: '',
    heading: 'Seismic requirements for suspended ceilings in Australia',
    buttonText: 'CLICK TO REGISTER',
    buttonHref: 'https://armstrongceilings.com.au/cpd/#seismic-requirements',
    bgImage: 'https://armstrongceilings.com.au/wp-content/uploads/cpd-header.jpg',
    type: 'standard',
  },
  {
    id: 5,
    title: '',
    subtitle: '',
    heading: '',
    buttonText: '',
    buttonHref: '',
    bgImage: 'https://armstrongceilings.com.au/wp-content/uploads/colour-bg2.jpg',
    type: 'grid',
  },
  {
    id: 6,
    title: 'feature article',
    subtitle: '',
    heading: 'What sustainable creativity sounds like!',
    buttonText: 'LEARN MORE',
    buttonHref:
      'https://armstrongceilings.com.au/ceilings/heradesign-acoustic-panels-what-sustainable-creativity-sounds-like/',
    bgImage: 'https://armstrongceilings.com.au/wp-content/uploads/04-1.jpg',
    type: 'standard',
  },
];

const gridItems = [
  {
    label: 'Colours',
    href: 'https://armstrongceilings.com.au/inspiration/#colours',
    bg: 'https://armstrongceilings.com.au/wp-content/uploads/colour-bg2.jpg',
  },
  {
    label: 'Textures',
    href: 'https://armstrongceilings.com.au/inspiration/#textures',
    bg: 'https://armstrongceilings.com.au/wp-content/uploads/texture-bg2.jpg',
  },
  {
    label: 'Patterns',
    href: 'https://armstrongceilings.com.au/inspiration/#patterns',
    bg: 'https://armstrongceilings.com.au/wp-content/uploads/pattern-bg3.jpg',
  },
  {
    label: 'Perforations',
    href: 'https://armstrongceilings.com.au/inspiration/#perforations',
    bg: 'https://armstrongceilings.com.au/wp-content/uploads/perforation-pattern1-scaled.jpg',
  },
];

export default function HeroSlider() {
  const [current, setCurrent] = useState(0);
  const [isAnimating, setIsAnimating] = useState(false);

  const goToSlide = useCallback(
    (index: number) => {
      if (isAnimating) return;
      setIsAnimating(true);
      setCurrent(index);
      setTimeout(() => setIsAnimating(false), 800);
    },
    [isAnimating]
  );

  useEffect(() => {
    const timer = setInterval(() => {
      setCurrent((prev) => (prev + 1) % slides.length);
    }, 6500);
    return () => clearInterval(timer);
  }, []);

  const slide = slides[current];

  return (
    <div
      className="relative w-full overflow-hidden"
      style={{ height: '100vh', minHeight: '600px', maxHeight: '850px' }}
    >
      {/* Background Images */}
      {slides.map((s, i) => (
        <div
          key={s.id}
          className="absolute inset-0 transition-opacity duration-800"
          style={{
            opacity: i === current ? 1 : 0,
            transition: 'opacity 0.8s ease-in-out',
            backgroundImage: `linear-gradient(45deg, rgba(0,0,0,0.5) 0%, rgba(0,0,0,0) 100%), url(${s.bgImage})`,
            backgroundSize: 'cover',
            backgroundPosition: 'center',
          }}
        />
      ))}

      {/* Content */}
      <div className="relative z-10 h-full flex items-end">
        <div className="w-full pb-20 px-8 md:px-12 max-w-[800px]">
          {slide.type === 'standard' && (
            <>
              {slide.title && (
                <p
                  className="text-white text-sm uppercase tracking-[2px] mb-3"
                  style={{ fontFamily: 'Graphik Medium, sans-serif', letterSpacing: '1.5px' }}
                >
                  {slide.title}
                </p>
              )}
              {slide.heading && (
                <h2
                  className="text-white font-normal mb-6"
                  style={{
                    fontFamily: 'Graphik Medium, sans-serif',
                    fontSize: 'clamp(2rem, 5vw, 4.5rem)',
                    lineHeight: '1',
                    whiteSpace: 'pre-line',
                  }}
                >
                  {slide.heading}
                </h2>
              )}
              {slide.buttonText && (
                <a
                  href={slide.buttonHref}
                  className="inline-block bg-[#507EEF] text-white text-sm uppercase tracking-[2px] px-8 py-4 rounded-full hover:bg-[#1E1B1C] transition-colors"
                  style={{ fontFamily: 'Graphik Medium, sans-serif' }}
                >
                  {slide.buttonText}
                </a>
              )}
            </>
          )}

          {slide.type === 'grid' && (
            <div className="w-full">
              <div className="grid grid-cols-2 md:grid-cols-4 gap-0">
                {gridItems.map((item) => (
                  <div
                    key={item.label}
                    className="relative flex flex-col items-center justify-center p-6 min-h-[200px]"
                    style={{
                      backgroundImage: `url(${item.bg})`,
                      backgroundSize: 'cover',
                      backgroundPosition: 'center',
                    }}
                  >
                    <div className="absolute inset-0 bg-black/30" />
                    <div className="relative z-10 text-center">
                      <h3
                        className="text-white text-3xl font-normal mb-4"
                        style={{ fontFamily: 'Graphik Medium, sans-serif' }}
                      >
                        {item.label}
                      </h3>
                      <a
                        href={item.href}
                        className="inline-block bg-[#507EEF] text-white text-xs uppercase tracking-[1px] px-5 py-2 rounded-full hover:bg-[#1E1B1C] transition-colors"
                        style={{ fontFamily: 'Graphik Medium, sans-serif' }}
                      >
                        LEARN MORE
                      </a>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          )}
        </div>
      </div>

      {/* Bullet Navigation */}
      <div className="absolute bottom-8 left-8 z-20 flex gap-3">
        {slides.map((s, i) => (
          <button
            key={s.id}
            onClick={() => goToSlide(i)}
            aria-label={`Slide ${i + 1}`}
            className="w-3 h-3 rounded-full border-2 border-white transition-all"
            style={{
              backgroundColor: i === current ? '#ffffff' : 'transparent',
            }}
          />
        ))}
      </div>
    </div>
  );
}
