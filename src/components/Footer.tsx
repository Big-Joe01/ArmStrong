'use client';
import React from 'react';

const footerLinks = {
  exploreSolutions: [
    { label: 'Ceilings', href: 'https://armstrongceilings.com.au/suspendedceilings/' },
    { label: 'Suspension Systems', href: 'https://armstrongceilings.com.au/suspension-systems/' },
    { label: 'Walls', href: 'https://armstrongceilings.com.au/walls/' },
    { label: 'Applications', href: 'https://armstrongceilings.com.au/a-ceiling-for-every-space/' },
    { label: 'Performance', href: 'https://armstrongceilings.com.au/performance/' },
    { label: 'Project Gallery', href: 'https://armstrongceilings.com.au/project-gallery/' },
  ],

  technical: [
    {
      label: 'Technical & Downloads',
      href: 'https://armstrongceilings.com.au/technical-downloads/',
    },
    {
      label: 'Request a Specification',
      href: 'https://armstrongceilings.com.au/request-a-specification/',
    },
    { label: 'Request a Warranty', href: 'https://armstrongceilings.com.au/request-a-warranty/' },
    { label: 'Request a Sample', href: 'https://armstrongceilings.com.au/request-a-sample/' },
    { label: 'Find a Distributor', href: 'https://armstrongceilings.com.au/find-a-distributor/' },
    { label: 'Find a Representative', href: 'https://armstrongceilings.com.au/find-a-rep/' },
  ],

  about: [
    { label: 'About Us', href: 'https://armstrongceilings.com.au/about-us/' },
    { label: 'Our History', href: 'https://armstrongceilings.com.au/about-us/#history' },
    { label: 'Who We Are Today', href: 'https://armstrongceilings.com.au/about-us/#whoweare' },
    { label: 'Our Values', href: 'https://armstrongceilings.com.au/about-us/#value' },
  ],

  company: [
    { label: 'Contact Us', href: 'https://armstrongceilings.com.au/contact-us/' },
    { label: 'Blog', href: 'https://armstrongceilings.com.au/blog/' },
    { label: 'Careers', href: 'https://armstrongceilings.com.au/careers-at-armstrong/' },
  ],
};

const socialLinks = [
  {
    label: 'Instagram',
    href: 'https://www.instagram.com/armstrongceilingsolutionsau/',
    icon: (
      <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
      </svg>
    ),
  },
  {
    label: 'Facebook',
    href: 'https://www.facebook.com/ArmstrongCeilingSolutionsAustraila',
    icon: (
      <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
      </svg>
    ),
  },
  {
    label: 'LinkedIn',
    href: 'https://www.linkedin.com/company/armstrong-ceiling-solutions-australia/',
    icon: (
      <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
        <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
      </svg>
    ),
  },
  {
    label: 'YouTube',
    href: 'https://www.youtube.com/channel/UC-CIVbfOD5C4O4SVGwdC51Q',
    icon: (
      <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
        <path d="M23.495 6.205a3.007 3.007 0 0 0-2.088-2.088c-1.87-.501-9.396-.501-9.396-.501s-7.507-.01-9.396.501A3.007 3.007 0 0 0 .527 6.205a31.247 31.247 0 0 0-.522 5.805 31.247 31.247 0 0 0 .522 5.783 3.007 3.007 0 0 0 2.088 2.088c1.868.502 9.396.502 9.396.502s7.506 0 9.396-.502a3.007 3.007 0 0 0 2.088-2.088 31.247 31.247 0 0 0 .5-5.783 31.247 31.247 0 0 0-.5-5.805zM9.609 15.601V8.408l6.264 3.602z" />
      </svg>
    ),
  },
];

export default function Footer() {
  return (
    <footer className="bg-[#1E1B1C] text-white">
      {/* Back to Top */}
      <div className="border-b border-white/10">
        <div className="max-w-[1290px] mx-auto px-6 py-6 flex justify-center">
          <a
            href="#top"
            className="flex flex-col items-center gap-2 text-white hover:text-gray-300 transition-colors group"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              height="22px"
              viewBox="0 0 105.333 62.883"
              width="37px"
              fill="white"
              className="group-hover:opacity-70 transition-opacity"
            >
              <path d="m102.307 54.139-49.636-49.637-.004.004-.005-.004-49.636 49.637 4.242 4.242 45.399-45.399 45.398 45.399z" />
            </svg>
            <span
              className="text-xs uppercase tracking-[1.5px]"
              style={{ fontFamily: 'Graphik Medium, sans-serif' }}
            >
              Back to top
            </span>
          </a>
        </div>
      </div>
      {/* Main Footer Links */}
      <div className="max-w-[1290px] mx-auto px-6 py-12">
        <div className="grid grid-cols-2 md:grid-cols-5 gap-8">
          {/* Explore Solutions */}
          <div>
            <p
              className="text-white text-base mb-4"
              style={{ fontFamily: 'Graphik Medium, sans-serif', fontWeight: 'normal' }}
            >
              Explore Solutions
            </p>
            <ul className="space-y-2">
              {footerLinks?.exploreSolutions?.map((link) => (
                <li key={link?.label}>
                  <a
                    href={link?.href}
                    className="text-white/80 text-sm hover:text-white transition-colors"
                    style={{ fontFamily: 'Graphik Regular, sans-serif' }}
                  >
                    {link?.label}
                  </a>
                </li>
              ))}
            </ul>
          </div>

          {/* Technical */}
          <div>
            <p
              className="text-white text-base mb-4"
              style={{ fontFamily: 'Graphik Medium, sans-serif', fontWeight: 'normal' }}
            >
              Technical
            </p>
            <ul className="space-y-2">
              {footerLinks?.technical?.map((link) => (
                <li key={link?.label}>
                  <a
                    href={link?.href}
                    className="text-white/80 text-sm hover:text-white transition-colors"
                    style={{ fontFamily: 'Graphik Regular, sans-serif' }}
                  >
                    {link?.label}
                  </a>
                </li>
              ))}
            </ul>
          </div>

          {/* About */}
          <div>
            <p
              className="text-white text-base mb-4"
              style={{ fontFamily: 'Graphik Medium, sans-serif', fontWeight: 'normal' }}
            >
              About
            </p>
            <ul className="space-y-2">
              {footerLinks?.about?.map((link) => (
                <li key={link?.label}>
                  <a
                    href={link?.href}
                    className="text-white/80 text-sm hover:text-white transition-colors"
                    style={{ fontFamily: 'Graphik Regular, sans-serif' }}
                  >
                    {link?.label}
                  </a>
                </li>
              ))}
            </ul>
          </div>

          {/* Company */}
          <div>
            <p
              className="text-white text-base mb-4"
              style={{ fontFamily: 'Graphik Medium, sans-serif', fontWeight: 'normal' }}
            >
              Company
            </p>
            <ul className="space-y-2">
              {footerLinks?.company?.map((link) => (
                <li key={link?.label}>
                  <a
                    href={link?.href}
                    className="text-white/80 text-sm hover:text-white transition-colors"
                    style={{ fontFamily: 'Graphik Regular, sans-serif' }}
                  >
                    {link?.label}
                  </a>
                </li>
              ))}
            </ul>
          </div>

          {/* Logo + Social */}
          <div className="flex flex-col items-end">
            <img
              src="https://armstrongceilings.com.au/wp-content/uploads/2021/06/ac-logo-white-1024x335.png"
              alt="Armstrong Ceiling Solutions"
              className="w-40 mb-3"
            />

            <p
              className="text-white/80 text-sm mb-4 text-right"
              style={{ fontFamily: 'Graphik Regular, sans-serif' }}
            >
              <strong>Celebrating 60 Years</strong>
              <br />
              in Australia
            </p>
            <div className="flex gap-3">
              {socialLinks?.map((social) => (
                <a
                  key={social?.label}
                  href={social?.href}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="text-white hover:text-[#507EEF] transition-colors"
                  aria-label={social?.label}
                >
                  {social?.icon}
                </a>
              ))}
            </div>
          </div>
        </div>
      </div>
      {/* Bottom Bar */}
      <div className="border-t border-white/10">
        <div className="max-w-[1290px] mx-auto px-6 py-4 flex flex-col md:flex-row items-center justify-between gap-2">
          <p
            className="text-white/70 text-xs"
            style={{ fontFamily: 'Graphik Regular, sans-serif' }}
          >
            &copy;2026 Armstrong Ceiling Solutions
          </p>
          <p
            className="text-white/70 text-xs"
            style={{ fontFamily: 'Graphik Regular, sans-serif' }}
          >
            Site by{' '}
            <a
              href="http://showandtell.com.au"
              target="_blank"
              rel="noopener noreferrer"
              className="inline-flex items-center gap-1 hover:text-white transition-colors"
            >
              <img
                src="https://armstrongceilings.com.au/wp-content/uploads/showandtell_logo_primary_white-e1631150892571.png"
                alt="Show and Tell Creative"
                className="h-3 inline"
              />
            </a>
          </p>
        </div>
      </div>
    </footer>
  );
}
