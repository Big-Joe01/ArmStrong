'use client';
import React, { useState, useEffect } from 'react';

const ceilingsMenu = [
  {
    label: 'Mineral Fibre Acoustic Ceiling Systems',
    href: 'https://armstrongceilings.com.au/product/suspended-ceilings/acoustic-ceiling-tiles/',
    img: 'https://armstrongceilings.com.au/wp-content/uploads/ceilings_menu_mineralfibre.jpg',
  },
  {
    label: 'Metal Ceilings & Baffles',
    href: 'https://armstrongceilings.com.au/product/suspended-ceilings/metal-ceilings-baffles/',
    img: 'https://armstrongceilings.com.au/wp-content/uploads/ceilings_menu_metalworksexposedgrid.jpg',
  },
  {
    label: 'Timber Ceilings',
    href: 'https://armstrongceilings.com.au/product/suspended-ceilings/timber-ceiling-panels/',
    img: 'https://armstrongceilings.com.au/wp-content/uploads/ceilings_menu_woodworks.jpg',
  },
  {
    label: 'Heradesign Wood Wool Ceiling Panels & Baffles',
    href: 'https://armstrongceilings.com.au/product/suspended-ceilings/heradesign-wood-wool-ceiling-panels-baffles/',
    img: 'https://armstrongceilings.com.au/wp-content/uploads/ceilings_menu_heradesignacoustics.jpg',
  },
  {
    label: 'Soft Fibre Baffles & Floating Ceilings',
    href: 'https://armstrongceilings.com.au/product/floating-ceilings/soft-fibre-baffles-floating-ceilings/',
    img: 'https://armstrongceilings.com.au/wp-content/uploads/ceilings_menu_soundscapesfloating.jpg',
  },
];

const suspensionMenu = [
  {
    label: 'Peakform Steel Grid Systems',
    href: 'https://armstrongceilings.com.au/product/suspension-systems/peakform-steel-grid-systems/',
    img: 'https://armstrongceilings.com.au/wp-content/uploads/suspensionsystem_menu_peakform_stillgrid.jpg',
  },
  {
    label: 'Blue Tongue Aluminium Grid Systems',
    href: 'https://armstrongceilings.com.au/product/suspension-systems/blue-tongue-aluminium-grid-systems/',
    img: 'https://armstrongceilings.com.au/wp-content/uploads/suspensionsystem_menu_bluetongue_aluminium_grid.jpg',
  },
  {
    label: 'Axiom Perimeter Solutions',
    href: 'https://armstrongceilings.com.au/product/uncategorised/axiom-perimeter-solutions/',
    img: 'https://armstrongceilings.com.au/wp-content/uploads/suspensionsystem_menu_axiomperimetersolution.jpg',
  },
  {
    label: 'Drywall Grid Systems',
    href: 'https://armstrongceilings.com.au/product/uncategorised/drywall-grid-systems/',
    img: 'https://armstrongceilings.com.au/wp-content/uploads/suspensionsystem_menu_drywall_gridsystem.jpg',
  },
  {
    label: 'Seismic Solutions',
    href: 'https://armstrongceilings.com.au/product/systems/seismic/',
    img: 'https://armstrongceilings.com.au/wp-content/uploads/performance_menu_seismic.jpg',
  },
  {
    label: 'DataStruct Data Centre Grid System',
    href: 'https://armstrongceilings.com.au/product/systems/data-centre/',
    img: 'https://armstrongceilings.com.au/wp-content/uploads/AdobeStock_164623654.jpg',
  },
];

const wallsMenu = [
  {
    label: 'HERADESIGN Acoustic Wall Panels',
    href: 'https://armstrongceilings.com.au/product/walls/heradesign/',
    img: 'https://armstrongceilings.com.au/wp-content/uploads/walls_menu_heradesignacoustics.jpg',
  },
  {
    label: 'WoodWorks Wall Systems',
    href: 'https://armstrongceilings.com.au/product/walls/woodworks/',
    img: 'https://armstrongceilings.com.au/wp-content/uploads/walls_menu_woodworks.jpg',
  },
  {
    label: 'MetalWorks Wall Systems',
    href: 'https://armstrongceilings.com.au/product/walls/metalworks-wall-systems/',
    img: 'https://armstrongceilings.com.au/wp-content/uploads/walls_menu_metalworks.jpg',
  },
  {
    label: 'Floc Roll Wool Acoustic Wall Treatment',
    href: 'https://armstrongceilings.com.au/product/walls/floc-roll/',
    img: 'https://armstrongceilings.com.au/wp-content/uploads/Floc-cover-pic.jpeg',
  },
];

const applicationsMenu = [
  {
    label: 'Education',
    href: 'https://armstrongceilings.com.au/a-ceiling-for-every-space/education/',
    img: 'https://armstrongceilings.com.au/wp-content/uploads/applications_menu_education.jpg',
  },
  {
    label: 'Healthcare',
    href: 'https://armstrongceilings.com.au/a-ceiling-for-every-space/healthcare/',
    img: 'https://armstrongceilings.com.au/wp-content/uploads/applications_menu_healthcare.jpg',
  },
  {
    label: 'Office',
    href: 'https://armstrongceilings.com.au/a-ceiling-for-every-space/office/',
    img: 'https://armstrongceilings.com.au/wp-content/uploads/applications_menu_office.jpg',
  },
  {
    label: 'Retail',
    href: 'https://armstrongceilings.com.au/a-ceiling-for-every-space/retail/',
    img: 'https://armstrongceilings.com.au/wp-content/uploads/applications_menu_retail.jpg',
  },
  {
    label: 'Hospitality',
    href: 'https://armstrongceilings.com.au/a-ceiling-for-every-space/hospitality/',
    img: 'https://armstrongceilings.com.au/wp-content/uploads/applications_menu_hospitality.jpg',
  },
  {
    label: 'Transportation',
    href: 'https://armstrongceilings.com.au/a-ceiling-for-every-space/transportation/',
    img: 'https://armstrongceilings.com.au/wp-content/uploads/applications_menu_transportation.jpg',
  },
  {
    label: 'Clean Rooms',
    href: 'https://armstrongceilings.com.au/a-ceiling-for-every-space/clean-room-applications/',
    img: 'https://armstrongceilings.com.au/wp-content/uploads/applications_menu_cleanrooms.jpg',
  },
  {
    label: 'Data Centres',
    href: 'https://armstrongceilings.com.au/a-ceiling-for-every-space/data-centre/',
    img: 'https://armstrongceilings.com.au/wp-content/uploads/applications_menu_datacentre.jpg',
  },
];

const performanceMenu = [
  {
    label: 'Acoustics',
    href: 'https://armstrongceilings.com.au/performance/acoustics/',
    img: 'https://armstrongceilings.com.au/wp-content/uploads/performance_menu_acoustics.jpg',
  },
  {
    label: 'Durability',
    href: 'https://armstrongceilings.com.au/performance/durability/',
    img: 'https://armstrongceilings.com.au/wp-content/uploads/performance_menu_durability.jpg',
  },
  {
    label: 'Clean Rooms',
    href: 'https://armstrongceilings.com.au/performance/clean-rooms/',
    img: 'https://armstrongceilings.com.au/wp-content/uploads/performance_menu_cleanrooms.jpg',
  },
  {
    label: 'Fire Resistance',
    href: 'https://armstrongceilings.com.au/performance/fire-resistance/',
    img: 'https://armstrongceilings.com.au/wp-content/uploads/performance_menu_fireresistance.jpg',
  },
  {
    label: 'Light Reflectance',
    href: 'https://armstrongceilings.com.au/performance/light-reflectance/',
    img: 'https://armstrongceilings.com.au/wp-content/uploads/performance_menu_lightreflectance.jpg',
  },
  {
    label: 'Sustainability',
    href: 'https://armstrongceilings.com.au/performance/sustainable-ceiling-solutions/',
    img: 'https://armstrongceilings.com.au/wp-content/uploads/performance_menu_sustainability.jpg',
  },
  {
    label: 'Cleanability',
    href: 'https://armstrongceilings.com.au/performance/cleanability/',
    img: 'https://armstrongceilings.com.au/wp-content/uploads/performance_menu_cleanability.jpg',
  },
  {
    label: 'Seismic',
    href: 'https://armstrongceilings.com.au/performance/seismic/',
    img: 'https://armstrongceilings.com.au/wp-content/uploads/performance_menu_seismic.jpg',
  },
  {
    label: 'Anti-Microbial',
    href: 'https://armstrongceilings.com.au/performance/anti-microbial/',
    img: 'https://armstrongceilings.com.au/wp-content/uploads/performance_menu_antimicrobial.jpg',
  },
  {
    label: 'Humidity / Sag Resistance',
    href: 'https://armstrongceilings.com.au/performance/humidity-sag-resistance/',
    img: 'https://armstrongceilings.com.au/wp-content/uploads/performance_menu_humidity.jpg',
  },
];

const inspirationMenu = [
  {
    label: 'Colours, Textures, Patterns & Perforations',
    href: 'https://armstrongceilings.com.au/inspiration/',
    img: 'https://armstrongceilings.com.au/wp-content/uploads/inspiration1.jpg',
  },
  {
    label: 'Gallery & Case Studies',
    href: 'https://armstrongceilings.com.au/project-gallery/',
    img: 'https://armstrongceilings.com.au/wp-content/uploads/gallery.jpg',
  },
  {
    label: 'CPD',
    href: 'https://armstrongceilings.com.au/cpd/',
    img: 'https://armstrongceilings.com.au/wp-content/uploads/cpd.jpg',
  },
  {
    label: 'Book a Lunch & Learn',
    href: 'https://armstrongceilings.com.au/book-a-lunch-and-learn/',
    img: 'https://armstrongceilings.com.au/wp-content/uploads/lunch-and-learn.jpg',
  },
];

interface MenuItemProps {
  img: string;
  label: string;
  href: string;
}

function MenuImageItem({ img, label, href }: MenuItemProps) {
  return (
    <a href={href} className="flex items-center gap-3 py-2 group">
      <img src={img} alt={label} className="w-16 h-11 object-cover flex-shrink-0" />
      <span className="text-xs text-white hover:text-gray-300 transition-colors leading-tight">
        {label}
      </span>
    </a>
  );
}

export default function Header() {
  const [activeMenu, setActiveMenu] = useState<string | null>(null);
  const [mobileOpen, setMobileOpen] = useState(false);
  const [scrolled, setScrolled] = useState(false);

  useEffect(() => {
    const handleScroll = () => {
      setScrolled(window.scrollY > 60);
    };
    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  const navItems = [
    { label: 'CEILINGS', key: 'ceilings', items: ceilingsMenu },
    { label: 'SUSPENSION SYSTEMS', key: 'suspension', items: suspensionMenu },
    { label: 'WALL PANELS', key: 'walls', items: wallsMenu },
    { label: 'APPLICATIONS', key: 'applications', items: applicationsMenu },
    { label: 'PERFORMANCE', key: 'performance', items: performanceMenu },
    { label: 'INSPIRATION', key: 'inspiration', items: inspirationMenu },
    {
      label: 'TECHNICAL & DOWNLOADS',
      key: 'technical',
      href: 'https://armstrongceilings.com.au/technical-downloads/',
    },
  ];

  return (
    <header
      className="fixed top-0 left-0 right-0 z-50 bg-white"
      style={{ boxShadow: scrolled ? '0 2px 10px rgba(0,0,0,0.1)' : 'none' }}
    >
      {/* Top Bar */}
      <div className="border-b border-gray-100">
        <div className="max-w-[1290px] mx-auto px-6 flex items-center justify-between h-[52px]">
          {/* Logo */}
          <a href="https://armstrongceilings.com.au" className="flex-shrink-0">
            <img
              src="https://armstrongceilings.com.au/wp-content/uploads/2021/06/ac-logo-2048x669.png"
              alt="Armstrong Ceiling Solutions"
              className="h-[52px] w-auto object-contain"
            />
          </a>

          {/* Top Nav Links */}
          <nav className="hidden lg:flex items-center gap-6">
            {[
              { label: 'About Us', href: 'https://armstrongceilings.com.au/about-us/' },
              { label: 'Blog', href: 'https://armstrongceilings.com.au/blog/' },
              { label: 'Contact Us', href: 'https://armstrongceilings.com.au/contact-us/' },
              {
                label: 'Find a Distributor',
                href: 'https://armstrongceilings.com.au/find-a-distributor/',
              },
              {
                label: 'Find a Representative',
                href: 'https://armstrongceilings.com.au/find-a-rep/',
              },
            ].map((item) => (
              <a
                key={item.label}
                href={item.href}
                className="text-[13px] text-gray-600 hover:text-[#507EEF] transition-colors"
                style={{ fontFamily: 'Graphik Regular, sans-serif' }}
              >
                {item.label}
              </a>
            ))}
          </nav>

          {/* Right Actions */}
          <div className="hidden lg:flex items-center gap-4">
            <button className="text-gray-600 hover:text-[#507EEF] transition-colors">
              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth={2}
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                />
              </svg>
            </button>
            <a
              href="https://armstrongceilings.com.au/request-a-specification/"
              className="bg-[#507EEF] text-white text-[12px] font-medium px-5 py-2 rounded-full hover:bg-[#1E1B1C] transition-colors uppercase tracking-wider"
              style={{ fontFamily: 'Graphik Medium, sans-serif' }}
            >
              Request a Specification
            </a>
          </div>

          {/* Mobile Hamburger */}
          <button
            className="lg:hidden text-gray-700"
            onClick={() => setMobileOpen(!mobileOpen)}
            aria-label="Menu Toggle"
          >
            {mobileOpen ? (
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth={2}
                  d="M6 18L18 6M6 6l12 12"
                />
              </svg>
            ) : (
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth={2}
                  d="M4 6h16M4 12h16M4 18h16"
                />
              </svg>
            )}
          </button>
        </div>
      </div>

      {/* Main Nav */}
      <div className="hidden lg:block bg-white border-b border-gray-100">
        <div className="max-w-[1290px] mx-auto px-6">
          <nav className="flex items-center">
            {navItems.map((item) => (
              <div
                key={item.key}
                className="relative"
                onMouseEnter={() => (item.items ? setActiveMenu(item.key) : null)}
                onMouseLeave={() => setActiveMenu(null)}
              >
                {item.href ? (
                  <a
                    href={item.href}
                    className="flex items-center gap-1 px-3 py-4 text-[12px] font-medium text-gray-700 hover:text-[#507EEF] transition-colors uppercase tracking-wide"
                    style={{ fontFamily: 'Graphik Medium, sans-serif' }}
                  >
                    {item.label}
                  </a>
                ) : (
                  <button
                    className="flex items-center gap-1 px-3 py-4 text-[12px] font-medium text-gray-700 hover:text-[#507EEF] transition-colors uppercase tracking-wide"
                    style={{ fontFamily: 'Graphik Medium, sans-serif' }}
                  >
                    {item.label}
                    <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth={2}
                        d="M19 9l-7 7-7-7"
                      />
                    </svg>
                  </button>
                )}

                {/* Mega Dropdown */}
                {item.items && activeMenu === item.key && (
                  <div
                    className="fixed left-0 right-0 bg-[#1E1B1C] shadow-xl z-50"
                    style={{ top: '104px' }}
                    onMouseEnter={() => setActiveMenu(item.key)}
                    onMouseLeave={() => setActiveMenu(null)}
                  >
                    <div className="max-w-[1290px] mx-auto px-6 py-6">
                      <div className="grid grid-cols-3 gap-4">
                        {item.items.map((menuItem) => (
                          <MenuImageItem
                            key={menuItem.href}
                            img={menuItem.img}
                            label={menuItem.label}
                            href={menuItem.href}
                          />
                        ))}
                      </div>
                    </div>
                  </div>
                )}
              </div>
            ))}
          </nav>
        </div>
      </div>

      {/* Mobile Menu */}
      {mobileOpen && (
        <div className="lg:hidden bg-[#1E1B1C] text-white">
          <div className="px-4 py-4">
            {[
              { label: 'Ceilings', href: 'https://armstrongceilings.com.au/suspendedceilings/' },
              {
                label: 'Suspension Systems',
                href: 'https://armstrongceilings.com.au/suspension-systems/',
              },
              { label: 'Wall Panels', href: 'https://armstrongceilings.com.au/walls/' },
              {
                label: 'Applications',
                href: 'https://armstrongceilings.com.au/a-ceiling-for-every-space/',
              },
              { label: 'Performance', href: 'https://armstrongceilings.com.au/performance/' },
              { label: 'Inspiration', href: 'https://armstrongceilings.com.au/inspiration/' },
              {
                label: 'Technical & Downloads',
                href: 'https://armstrongceilings.com.au/technical-downloads/',
              },
              { label: 'About Us', href: 'https://armstrongceilings.com.au/about-us/' },
              { label: 'Blog', href: 'https://armstrongceilings.com.au/blog/' },
              { label: 'Contact Us', href: 'https://armstrongceilings.com.au/contact-us/' },
            ].map((item) => (
              <a
                key={item.label}
                href={item.href}
                className="block py-3 text-sm text-white border-b border-white/10 hover:text-gray-300"
                style={{ fontFamily: 'Graphik Regular, sans-serif' }}
              >
                {item.label}
              </a>
            ))}
            <a
              href="https://armstrongceilings.com.au/request-a-specification/"
              className="block mt-4 bg-[#507EEF] text-white text-center py-3 rounded-full text-sm uppercase tracking-wider"
              style={{ fontFamily: 'Graphik Medium, sans-serif' }}
            >
              Request a Specification
            </a>
          </div>
        </div>
      )}
    </header>
  );
}
