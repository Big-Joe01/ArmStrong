'use client';
import React from 'react';
import Link from 'next/link';
import { useParams } from 'next/navigation';
import Header from '@/components/Header';
import Footer from '@/components/Footer';

interface BlogPost {
  id: number;
  slug: string;
  title: string;
  excerpt: string;
  image: string;
  alt: string;
  date: string;
  author: string;
  category: string;
  readTime: string;
  content: string[];
}

const blogPosts: BlogPost[] = [
  {
    id: 1,
    slug: 'heradesign-acoustic-panels-sustainable-creativity',
    title: 'Architecture & Design Article: HERADESIGN® acoustic panels – what sustainable creativity sounds like',
    excerpt: 'The way we experience spaces is shaped not only by what we see, but also by what we hear – and what we don\'t.',
    image: 'https://armstrongceilings.com.au/wp-content/uploads/04-1.jpg',
    alt: 'HERADESIGN acoustic panels in a modern interior space',
    date: 'March 12, 2024',
    author: 'Armstrong Ceilings',
    category: 'Architecture & Design',
    readTime: '5 min read',
    content: [
      'The way we experience spaces is shaped not only by what we see, but also by what we hear – and what we don\'t. Acoustic comfort is a fundamental aspect of interior design that is often overlooked, yet it profoundly affects how people feel, work, and interact within a space.',
      'HERADESIGN® acoustic panels represent a breakthrough in sustainable acoustic design. Manufactured from wood wool – a natural, renewable material – these panels combine exceptional sound absorption with striking visual appeal. The unique fibrous texture creates a surface that is both tactile and visually dynamic, offering designers a canvas for creative expression.',
      'What sets HERADESIGN® apart is its commitment to environmental responsibility. The panels are produced using a low-energy manufacturing process, and the wood wool fibres are sourced from sustainably managed forests. This means that specifying HERADESIGN® contributes to a building\'s sustainability credentials without compromising on performance or aesthetics.',
      'The acoustic performance of HERADESIGN® panels is impressive across a wide range of frequencies. With sound absorption coefficients reaching up to αw = 1.0, these panels can transform even the most reverberant spaces into comfortable, productive environments. Whether it\'s an open-plan office, a school classroom, or a healthcare facility, HERADESIGN® delivers measurable acoustic improvement.',
      'From a design perspective, HERADESIGN® offers extraordinary versatility. Available in a range of panel sizes, thicknesses, and surface textures, the system can be configured to create virtually any ceiling or wall treatment imaginable. The panels can be painted in any RAL colour, allowing seamless integration with any design palette.',
      'Architects and interior designers around the world have embraced HERADESIGN® for its ability to solve acoustic challenges while adding genuine visual interest to a space. From cultural institutions to corporate headquarters, the panels have proven their worth in some of the world\'s most demanding environments.',
      'As the construction industry continues its transition towards more sustainable practices, materials like HERADESIGN® will play an increasingly important role. By choosing products that are both high-performing and environmentally responsible, designers and specifiers can contribute to a built environment that is better for people and the planet.',
    ],
  },
  {
    id: 2,
    slug: 'earthquake-design-hospital-ceilings',
    title: 'Earthquake Design for Hospital Ceilings',
    excerpt: 'In Australia, we get on average 100 earthquakes of magnitude of 3.0 or more each year; and magnitude 5.0 or more every one-to-two years.',
    image: 'https://armstrongceilings.com.au/wp-content/uploads/AdobeStock_330561954-scaled.jpeg',
    alt: 'Hospital corridor with seismic-rated ceiling system',
    date: 'February 8, 2024',
    author: 'Armstrong Ceilings',
    category: 'Healthcare',
    readTime: '6 min read',
    content: [
      'In Australia, we get on average 100 earthquakes of magnitude of 3.0 or more each year; and magnitude 5.0 or more every one-to-two years. While Australia is not typically considered a high seismic risk country, the consequences of ceiling failure in a hospital during an earthquake can be catastrophic.',
      'Hospitals are critical facilities that must remain operational during and after seismic events. The ceiling system is one of the most vulnerable components of a building during an earthquake, and its failure can cause injuries, block evacuation routes, and render critical areas unusable at exactly the time they are most needed.',
      'Seismic design for hospital ceilings requires a comprehensive approach that considers the dynamic forces generated during an earthquake. Traditional suspended ceiling systems, designed primarily for static loads, may not perform adequately under the lateral and vertical accelerations experienced during a seismic event.',
      'Armstrong\'s seismic ceiling systems are engineered to meet the stringent requirements of Australian Standard AS 1170.4 – Structural Design Actions: Earthquake Actions. These systems incorporate a range of features designed to maintain ceiling integrity during a seismic event, including perimeter relief angles, seismic bracing wires, and specially designed grid connections.',
      'The key to seismic ceiling performance is allowing the ceiling to move with the building while preventing tiles from falling. Armstrong\'s systems achieve this through a combination of positive tile retention clips, which hold tiles securely in the grid, and perimeter details that allow the ceiling to accommodate building movement without damage.',
      'In healthcare environments, the ceiling system must also meet stringent hygiene and infection control requirements. Armstrong\'s seismic ceiling solutions are available in a range of finishes that are easy to clean and resistant to moisture, mould, and bacteria, making them ideal for use in operating theatres, intensive care units, and other critical care areas.',
      'The specification of seismic ceiling systems in hospitals is not just a matter of compliance – it is a fundamental aspect of responsible design. By investing in ceiling systems that are engineered to perform during a seismic event, healthcare facility owners and designers can protect patients, staff, and the community they serve.',
    ],
  },
  {
    id: 3,
    slug: 'physical-containment-facilities',
    title: 'Physical Containment Facilities',
    excerpt: 'A Physical Containment Facility is designed to safely work with microorganisms and prevent the release of pathogens into the environment.',
    image: 'https://armstrongceilings.com.au/wp-content/uploads/Westmead_Millennium_16.jpg',
    alt: 'Physical containment facility with specialized ceiling system',
    date: 'January 22, 2024',
    author: 'Armstrong Ceilings',
    category: 'Healthcare',
    readTime: '7 min read',
    content: [
      'A Physical Containment Facility is designed to safely work with microorganisms and prevent the release of pathogens into the environment. These highly specialised facilities are used in research, diagnostics, and manufacturing, and they require building systems that meet the most demanding performance standards.',
      'The ceiling system in a Physical Containment Facility plays a critical role in maintaining the integrity of the containment envelope. It must be airtight, easy to decontaminate, resistant to the chemicals used in cleaning and disinfection, and capable of supporting the mechanical and electrical services required for facility operation.',
      'Armstrong has extensive experience in the design and supply of ceiling systems for Physical Containment Facilities at all levels, from PC1 through to PC4. Our systems have been installed in some of Australia\'s most critical research and healthcare facilities, where they have proven their performance in the most demanding conditions.',
      'For PC2 and PC3 facilities, Armstrong\'s monolithic ceiling systems provide a seamless, airtight surface that can be easily decontaminated. These systems are constructed from panels that are bonded together and sealed at all joints, creating a continuous surface with no gaps or crevices where microorganisms could accumulate.',
      'The materials used in Armstrong\'s containment ceiling systems are carefully selected for their resistance to the chemicals commonly used in laboratory decontamination, including formaldehyde, hydrogen peroxide, and chlorine-based disinfectants. This ensures that the ceiling system will maintain its integrity and appearance throughout its service life.',
      'Lighting, ventilation, and other services penetrations are carefully detailed to maintain the airtight integrity of the ceiling system. Armstrong works closely with mechanical and electrical engineers to develop penetration details that meet the requirements of the containment standard while allowing for the efficient installation and maintenance of building services.',
      'The design and construction of Physical Containment Facilities is a highly specialised field, and the ceiling system is one of the most critical components. Armstrong\'s expertise in this area, combined with our extensive range of high-performance ceiling products, makes us the ideal partner for these challenging projects.',
    ],
  },
  {
    id: 4,
    slug: 'guarding-tiles-against-bacteria-mould-microorganisms',
    title: 'Guarding Tiles Against Bacteria, Mould, and Microorganisms',
    excerpt: "Guarding Tiles Against Bacteria, Mould, and Microorganisms. That's Armstrong Bioguard's Acoustic's superpower.",
    image: 'https://armstrongceilings.com.au/wp-content/uploads/MONASH_BLTB_FINAL_006_A3.jpg',
    alt: 'Armstrong Bioguard acoustic ceiling tiles in a healthcare facility',
    date: 'December 15, 2023',
    author: 'Armstrong Ceilings',
    category: 'Healthcare',
    readTime: '4 min read',
    content: [
      "Guarding Tiles Against Bacteria, Mould, and Microorganisms. That's Armstrong Bioguard Acoustic's superpower. In healthcare environments, infection control is paramount, and every surface in a clinical space has the potential to harbour and transmit pathogens.",
      'Armstrong Bioguard Acoustic ceiling tiles are engineered with an antimicrobial agent that is incorporated throughout the tile, not just applied to the surface. This means that the antimicrobial protection remains effective even if the tile surface is scratched or damaged, providing long-lasting protection against a wide range of bacteria, mould, and microorganisms.',
      'The antimicrobial agent used in Bioguard Acoustic tiles has been independently tested and proven effective against a range of healthcare-relevant pathogens, including Staphylococcus aureus (MRSA), Escherichia coli, and Aspergillus niger. This gives healthcare facility managers confidence that their ceiling system is contributing to infection control efforts.',
      'In addition to their antimicrobial properties, Bioguard Acoustic tiles offer excellent acoustic performance, with noise reduction coefficients of up to 0.90. This makes them ideal for use in areas where both infection control and acoustic comfort are important, such as patient rooms, waiting areas, and consultation rooms.',
      'The tiles are also designed for easy cleaning and maintenance. The smooth, washable surface can be wiped down with standard hospital-grade disinfectants without affecting the antimicrobial performance or the appearance of the tile. This simplifies the cleaning regime and reduces the risk of surface damage from harsh cleaning chemicals.',
      'Bioguard Acoustic tiles are available in a range of sizes and edge profiles, allowing them to be used in a variety of ceiling grid systems. They can also be specified with a range of light reflectance values, making it easy to optimise the lighting performance of a space while maintaining infection control standards.',
      'For healthcare facility designers and managers, Armstrong Bioguard Acoustic tiles offer a compelling combination of infection control, acoustic performance, and ease of maintenance. By specifying these tiles, they can contribute to a safer, more comfortable environment for patients and staff alike.',
    ],
  },
];

export default function BlogArticlePage() {
  const params = useParams();
  const slug = params?.slug as string;

  const post = blogPosts.find((p) => p.slug === slug) || blogPosts[0];
  const relatedPosts = blogPosts.filter((p) => p.id !== post.id).slice(0, 3);

  return (
    <div className="min-h-screen" style={{ fontFamily: 'Graphik Regular, sans-serif' }}>
      <Header />

      <main style={{ paddingTop: '104px' }}>
        {/* Breadcrumb */}
        <div className="bg-gray-50 border-b border-gray-100">
          <div className="max-w-[1290px] mx-auto px-6 py-3">
            <nav className="flex items-center gap-2 text-xs text-gray-500" style={{ fontFamily: 'Graphik Regular, sans-serif' }}>
              <Link href="/" className="hover:text-[#507EEF] transition-colors">Home</Link>
              <span>/</span>
              <Link href="/#blog" className="hover:text-[#507EEF] transition-colors">Blog</Link>
              <span>/</span>
              <span className="text-[#1E1B1C] truncate max-w-[300px]">{post.title}</span>
            </nav>
          </div>
        </div>

        {/* Article */}
        <article className="max-w-[1290px] mx-auto px-6 py-12">
          <div className="max-w-[860px]">
            {/* Category + Meta */}
            <div className="flex flex-wrap items-center gap-3 mb-5">
              <span
                className="bg-[#507EEF] text-white text-[10px] uppercase tracking-widest px-3 py-1 rounded-full"
                style={{ fontFamily: 'Graphik Medium, sans-serif' }}>
                {post.category}
              </span>
              <span className="text-gray-400 text-xs">{post.date}</span>
              <span className="text-gray-300">·</span>
              <span className="text-gray-400 text-xs">{post.readTime}</span>
            </div>

            {/* Title */}
            <h1
              className="text-[#1E1B1C] text-3xl md:text-4xl leading-tight mb-6"
              style={{ fontFamily: 'Graphik Medium, sans-serif', fontWeight: 'normal' }}>
              {post.title}
            </h1>

            {/* Author row */}
            <div className="flex items-center gap-3 mb-8 pb-8 border-b border-gray-100">
              <div className="w-9 h-9 rounded-full bg-[#507EEF] flex items-center justify-center flex-shrink-0">
                <span className="text-white text-xs" style={{ fontFamily: 'Graphik Medium, sans-serif' }}>AC</span>
              </div>
              <div>
                <p className="text-[#1E1B1C] text-sm" style={{ fontFamily: 'Graphik Medium, sans-serif' }}>{post.author}</p>
                <p className="text-gray-400 text-xs">Published {post.date}</p>
              </div>
            </div>

            {/* Featured Image */}
            <div className="mb-10 overflow-hidden rounded-sm">
              <img
                src={post.image}
                alt={post.alt}
                className="w-full h-[420px] object-cover"
              />
            </div>

            {/* Article Body */}
            <div className="space-y-5">
              {post.content.map((paragraph, index) => (
                <p
                  key={index}
                  className="text-gray-700 text-base leading-relaxed"
                  style={{ fontFamily: 'Graphik Regular, sans-serif' }}>
                  {paragraph}
                </p>
              ))}
            </div>

            {/* Tags / Share row */}
            <div className="mt-10 pt-8 border-t border-gray-100 flex flex-wrap items-center justify-between gap-4">
              <div className="flex items-center gap-2">
                <span className="text-xs text-gray-400 uppercase tracking-wider" style={{ fontFamily: 'Graphik Medium, sans-serif' }}>Category:</span>
                <span className="text-xs text-[#507EEF]" style={{ fontFamily: 'Graphik Medium, sans-serif' }}>{post.category}</span>
              </div>
              <Link
                href="/#blog"
                className="inline-flex items-center gap-2 text-xs uppercase tracking-wider text-[#507EEF] hover:text-[#1E1B1C] transition-colors"
                style={{ fontFamily: 'Graphik Medium, sans-serif' }}>
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Blog
              </Link>
            </div>
          </div>
        </article>

        {/* Related Posts */}
        <section className="bg-gray-50 py-14 px-6">
          <div className="max-w-[1290px] mx-auto">
            <h2
              className="text-[#1E1B1C] text-2xl mb-8"
              style={{ fontFamily: 'Graphik Medium, sans-serif', fontWeight: 'normal' }}>
              Related Articles
            </h2>
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
              {relatedPosts.map((related) => (
                <article key={related.id}>
                  <Link href={`/blog/${related.slug}`} className="block mb-3 overflow-hidden">
                    <img
                      src={related.image}
                      alt={related.alt}
                      className="w-full h-44 object-cover hover:scale-105 transition-transform duration-300"
                    />
                  </Link>
                  <div>
                    <div className="flex items-center gap-2 mb-2">
                      <span className="text-[10px] text-[#507EEF] uppercase tracking-wider" style={{ fontFamily: 'Graphik Medium, sans-serif' }}>{related.category}</span>
                      <span className="text-gray-300 text-xs">·</span>
                      <span className="text-gray-400 text-xs">{related.date}</span>
                    </div>
                    <h3 className="mb-2">
                      <Link
                        href={`/blog/${related.slug}`}
                        className="text-[#1E1B1C] text-sm font-medium leading-snug hover:text-[#507EEF] transition-colors"
                        style={{ fontFamily: 'Graphik Medium, sans-serif' }}>
                        {related.title}
                      </Link>
                    </h3>
                    <p
                      className="text-gray-500 text-sm leading-relaxed mb-3 line-clamp-2"
                      style={{ fontFamily: 'Graphik Regular, sans-serif' }}>
                      {related.excerpt}
                    </p>
                    <Link
                      href={`/blog/${related.slug}`}
                      className="text-[#507EEF] text-xs uppercase tracking-wider hover:text-[#1E1B1C] transition-colors"
                      style={{ fontFamily: 'Graphik Medium, sans-serif' }}>
                      Read More
                    </Link>
                  </div>
                </article>
              ))}
            </div>
          </div>
        </section>
      </main>

      <Footer />
    </div>
  );
}
