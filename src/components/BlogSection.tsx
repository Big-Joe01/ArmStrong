'use client';
import React from 'react';
import Link from 'next/link';

interface BlogPost {
  id: number;
  slug: string;
  title: string;
  excerpt: string;
  image: string;
  alt: string;
}

const blogPosts: BlogPost[] = [
  {
    id: 1,
    slug: 'heradesign-acoustic-panels-sustainable-creativity',
    title:
      'Architecture & Design Article: HERADESIGN® acoustic panels – what sustainable creativity sounds like',
    excerpt:
      "The way we experience spaces is shaped not only by what we see, but also by what we hear – and what we don't.",
    image: 'https://armstrongceilings.com.au/wp-content/uploads/04-1.jpg',
    alt: 'HERADESIGN acoustic panels in a modern interior space',
  },
  {
    id: 2,
    slug: 'earthquake-design-hospital-ceilings',
    title: 'Earthquake Design for Hospital Ceilings',
    excerpt:
      'In Australia, we get on average 100 earthquakes of magnitude of 3.0 or more each year; and magnitude 5.0 or more every one-to-two years.',
    image: 'https://armstrongceilings.com.au/wp-content/uploads/AdobeStock_330561954-scaled.jpeg',
    alt: 'Hospital corridor with seismic-rated ceiling system',
  },
  {
    id: 3,
    slug: 'physical-containment-facilities',
    title: 'Physical Containment Facilities',
    excerpt:
      'A Physical Containment Facility is designed to safely work with microorganisms and prevent the release of pathogens into the environment.',
    image: 'https://armstrongceilings.com.au/wp-content/uploads/Westmead_Millennium_16.jpg',
    alt: 'Physical containment facility with specialized ceiling system',
  },
  {
    id: 4,
    slug: 'guarding-tiles-against-bacteria-mould-microorganisms',
    title: 'Guarding Tiles Against Bacteria, Mould, and Microorganisms',
    excerpt:
      "Guarding Tiles Against Bacteria, Mould, and Microorganisms. That's Armstrong Bioguard's Acoustic's superpower.",
    image: 'https://armstrongceilings.com.au/wp-content/uploads/MONASH_BLTB_FINAL_006_A3.jpg',
    alt: 'Armstrong Bioguard acoustic ceiling tiles in a healthcare facility',
  },
];

export default function BlogSection() {
  return (
    <section id="blog" className="py-16 px-6 bg-white">
      <div className="max-w-[1290px] mx-auto flex flex-col md:flex-row gap-12">
        {/* Left - Blog Header */}
        <div className="md:w-1/3">
          <div className="md:sticky md:top-24">
            <h2
              className="text-[#1E1B1C] text-3xl mb-4"
              style={{ fontFamily: 'Graphik Medium, sans-serif', fontWeight: 'normal' }}
            >
              Blog
            </h2>
            <p
              className="text-gray-600 text-base leading-relaxed mb-4"
              style={{ fontFamily: 'Graphik Regular, sans-serif' }}
            >
              Dive into the world of commercial ceilings via our blog!
            </p>
            <p
              className="text-gray-600 text-base leading-relaxed mb-6"
              style={{ fontFamily: 'Graphik Regular, sans-serif' }}
            >
              It&apos;s where we strive to be your go to resource for elevating spaces from the
              ceiling down.
            </p>
            <a
              href="https://armstrongceilings.com.au/blog/"
              className="inline-block bg-[#507EEF] text-white text-xs uppercase tracking-wider px-6 py-2 rounded-full hover:bg-[#1E1B1C] transition-colors"
              style={{ fontFamily: 'Graphik Medium, sans-serif' }}
            >
              view all
            </a>
          </div>
        </div>

        {/* Right - Blog Grid */}
        <div className="md:w-2/3">
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-8">
            {blogPosts.map((post) => (
              <article key={post.id}>
                <Link href={`/blog/${post.slug}`} className="block mb-3 overflow-hidden">
                  <img
                    src={post.image}
                    alt={post.alt}
                    className="w-full h-48 object-cover hover:scale-105 transition-transform duration-300"
                  />
                </Link>
                <div>
                  <h3 className="mb-2">
                    <Link
                      href={`/blog/${post.slug}`}
                      className="text-[#1E1B1C] text-sm font-medium leading-snug hover:text-[#507EEF] transition-colors"
                      style={{ fontFamily: 'Graphik Medium, sans-serif' }}
                    >
                      {post.title}
                    </Link>
                  </h3>
                  <p
                    className="text-gray-600 text-sm leading-relaxed mb-3"
                    style={{ fontFamily: 'Graphik Regular, sans-serif' }}
                  >
                    {post.excerpt}
                  </p>
                  <Link
                    href={`/blog/${post.slug}`}
                    className="text-[#507EEF] text-xs uppercase tracking-wider hover:text-[#1E1B1C] transition-colors"
                    style={{ fontFamily: 'Graphik Medium, sans-serif' }}
                  >
                    Read More
                  </Link>
                </div>
              </article>
            ))}
          </div>
        </div>
      </div>
    </section>
  );
}
