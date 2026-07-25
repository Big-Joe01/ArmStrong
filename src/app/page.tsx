import React from 'react';
import Header from '@/components/Header';
import HeroSlider from '@/components/HeroSlider';
import AboutSection from '@/components/AboutSection';
import ProjectSlider from '@/components/ProjectSlider';
import SearchSection from '@/components/SearchSection';
import BlogSection from '@/components/BlogSection';
import InstagramSection from '@/components/InstagramSection';
import ContactSection from '@/components/ContactSection';
import NewsletterSection from '@/components/NewsletterSection';
import Footer from '@/components/Footer';

export default function HomePage() {
  return (
    <div id="top" className="min-h-screen" style={{ fontFamily: 'Graphik Regular, sans-serif' }}>
      <Header />

      {/* Main Content - offset for fixed header */}
      <main id="main" style={{ paddingTop: '104px' }}>
        {/* Hero Slider - Full Page */}
        <HeroSlider />

        {/* About Section - Blue Background */}
        <AboutSection />

        {/* Project Gallery Slider */}
        <ProjectSlider />

        {/* Product Search */}
        <SearchSection />

        {/* Blog Section */}
        <BlogSection />

        {/* Instagram Section */}
        <InstagramSection />

        {/* Contact Section */}
        <ContactSection />

        {/* Newsletter Section */}
        <NewsletterSection />
      </main>

      {/* Footer */}
      <Footer />
    </div>
  );
}
