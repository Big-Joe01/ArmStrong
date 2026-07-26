'use client';
import React, { useState, useEffect } from 'react';

interface Project {
  id: number;
  title: string;
  subtitle: string;
  description: string;
  href: string;
  bgImage: string;
}

const projects: Project[] = [
  {
    id: 1,
    title: 'Lincoln University',
    subtitle: 'Ivey West Redevelopment',
    description:
      'A future-focused learning space that prioritises sustainability, wellbeing, and cultural identity.',
    href: 'https://armstrongceilings.com.au/gallery/lincoln-university/',
    bgImage: 'https://armstrongceilings.com.au/wp-content/uploads/Floc-Roll-Lincoln-University.jpg',
  },
  {
    id: 2,
    title: 'OB-GYN Practice',
    subtitle: 'Spring Hill',
    description:
      'See photos of our Mesh ceiling installed throughout the reception & waiting room area.',
    href: 'https://armstrongceilings.com.au/gallery/201-wickham-terrace-level-4/',
    bgImage:
      'https://armstrongceilings.com.au/wp-content/uploads/01-DrEvaKretowicz-L4-Archway-2025.jpg',
  },
  {
    id: 3,
    title: 'AJ Gallagher',
    subtitle: 'Brisbane',
    description:
      "See photos of WoodWorks Vector ceilings within a recent office fitout in Brisbane's busy CBD.",
    href: 'https://armstrongceilings.com.au/gallery/aj-gallagher/',
    bgImage:
      'https://armstrongceilings.com.au/wp-content/uploads/Armstrong-WoodWorks-Vector-Perforated.jpg',
  },
  {
    id: 4,
    title: 'Brisbane Grammar School',
    subtitle: 'STEAM Precinct',
    description: 'Click to see more of our MetalWorks R-H215 ceilings in mirror finish.',
    href: 'https://armstrongceilings.com.au/gallery/brisbane-grammar-school/',
    bgImage: 'https://armstrongceilings.com.au/wp-content/uploads/©CFJ_STEAM-19.jpg',
  },
  {
    id: 5,
    title: 'Sustainable Palette',
    subtitle: 'Rothelowman WA Studio',
    description: '',
    href: 'https://armstrongceilings.com.au/gallery/rothelowman-studio-perth/',
    bgImage:
      'https://armstrongceilings.com.au/wp-content/uploads/20250819_Rothelowman_Perth_DionRobeson-54_LR.jpg',
  },
];

export default function ProjectSlider() {
  const [current, setCurrent] = useState(0);

  useEffect(() => {
    const timer = setInterval(() => {
      setCurrent((prev) => (prev + 1) % projects.length);
    }, 8000);
    return () => clearInterval(timer);
  }, []);

  const prev = () => setCurrent((c) => (c - 1 + projects.length) % projects.length);
  const next = () => setCurrent((c) => (c + 1) % projects.length);

  const project = projects[current];

  return (
    <section className="w-full bg-[#EDEDED]">
      <div className="relative flex flex-col md:flex-row" style={{ minHeight: '600px' }}>
        {/* Image Side */}
        <div className="w-full md:w-[60%] relative overflow-hidden" style={{ minHeight: '400px' }}>
          {projects.map((p, i) => (
            <div
              key={p.id}
              className="absolute inset-0"
              style={{
                opacity: i === current ? 1 : 0,
                transition: 'opacity 0.8s ease-in-out',
                backgroundImage: `url(${p.bgImage})`,
                backgroundSize: 'cover',
                backgroundPosition: 'center',
              }}
            />
          ))}
        </div>

        {/* Content Side */}
        <div className="w-full md:w-[40%] bg-[#EDEDED] flex flex-col justify-end p-10 md:p-16 pb-20 md:pb-28">
          <h3
            className="text-[#1E1B1C] mb-2"
            style={{
              fontFamily: 'Graphik Medium, sans-serif',
              fontSize: 'clamp(1.5rem, 3vw, 2.8rem)',
              lineHeight: '1',
              fontWeight: '300',
            }}
          >
            {project.title}
          </h3>
          {project.subtitle && (
            <p
              className="text-[#1E1B1C] uppercase tracking-[1.5px] text-sm mb-4"
              style={{ fontFamily: 'Graphik Medium, sans-serif', fontWeight: '300' }}
            >
              {project.subtitle}
            </p>
          )}
          {project.description && (
            <p
              className="text-[#1E1B1C] text-base leading-relaxed mb-6"
              style={{ fontFamily: 'Graphik Regular, sans-serif' }}
            >
              {project.description}
            </p>
          )}
          <div>
            <a
              href={project.href}
              className="inline-block bg-[#507EEF] text-white text-xs uppercase tracking-[1.5px] px-8 py-3 rounded-full hover:bg-white hover:text-[#507EEF] transition-colors"
              style={{ fontFamily: 'Graphik Medium, sans-serif' }}
            >
              view project
            </a>
          </div>
        </div>

        {/* Arrow Controls */}
        <div className="absolute bottom-5 left-5 flex gap-3 z-10">
          <button
            onClick={prev}
            className="w-10 h-10 bg-white rounded-full flex items-center justify-center hover:bg-[#507EEF] hover:text-white transition-colors shadow"
            aria-label="Previous project"
          >
            <svg
              width="20"
              height="20"
              viewBox="0 0 32 32"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M11.433 15.992L22.69 5.712c.393-.39.393-1.03 0-1.42-.393-.39-1.03-.39-1.423 0l-11.98 10.94c-.21.21-.3.49-.285.76-.015.28.075.56.284.77l11.98 10.94c.393.39 1.03.39 1.424 0 .393-.4.393-1.03 0-1.42l-11.257-10.29"
                fill="#1E1B1C"
                fillRule="evenodd"
              />
            </svg>
          </button>
          <button
            onClick={next}
            className="w-10 h-10 bg-white rounded-full flex items-center justify-center hover:bg-[#507EEF] hover:text-white transition-colors shadow"
            aria-label="Next project"
          >
            <svg
              width="20"
              height="20"
              viewBox="0 0 32 32"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M10.722 4.293c-.394-.39-1.032-.39-1.427 0-.393.39-.393 1.03 0 1.42l11.283 10.28-11.283 10.29c-.393.39-.393 1.02 0 1.42.395.39 1.033.39 1.427 0l12.007-10.94c.21-.21.3-.49.284-.77.014-.27-.076-.55-.286-.76L10.72 4.293z"
                fill="#1E1B1C"
                fillRule="evenodd"
              />
            </svg>
          </button>
        </div>
      </div>
    </section>
  );
}
