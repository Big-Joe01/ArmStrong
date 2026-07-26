'use client';
import React, { useState } from 'react';

export default function NewsletterSection() {
  const [firstName, setFirstName] = useState('');
  const [lastName, setLastName] = useState('');
  const [email, setEmail] = useState('');
  const [submitted, setSubmitted] = useState(false);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setSubmitted(true);
  };

  return (
    <section className="py-16 px-6" style={{ backgroundColor: '#507EEF' }}>
      <div className="max-w-[1290px] mx-auto flex flex-col md:flex-row gap-12 items-start">
        {/* Left - Header */}
        <div className="md:w-1/3">
          <h2
            className="text-white text-3xl mb-4"
            style={{ fontFamily: 'Graphik Medium, sans-serif', fontWeight: 'normal' }}
          >
            Subscribe to our Newsletter
          </h2>
          <p
            className="text-white text-base leading-relaxed"
            style={{ fontFamily: 'Graphik Regular, sans-serif' }}
          >
            Subscribe to our MailChimp database for all of our company news &amp; new product
            updates.
          </p>
        </div>

        {/* Right - Form */}
        <div className="md:w-2/3">
          {submitted ? (
            <div
              className="text-white text-lg"
              style={{ fontFamily: 'Graphik Medium, sans-serif' }}
            >
              Thank you for subscribing!
            </div>
          ) : (
            <form onSubmit={handleSubmit} className="space-y-4">
              <div className="flex gap-4">
                <div className="flex-1">
                  <input
                    type="text"
                    placeholder="First Name"
                    value={firstName}
                    onChange={(e) => setFirstName(e.target.value)}
                    required
                    className="w-full px-4 py-3 bg-white/20 border border-white/40 text-white placeholder-white/70 rounded focus:outline-none focus:border-white"
                    style={{ fontFamily: 'Graphik Regular, sans-serif' }}
                  />
                  <label
                    className="text-white/70 text-xs mt-1 block"
                    style={{ fontFamily: 'Graphik Regular, sans-serif' }}
                  >
                    First
                  </label>
                </div>
                <div className="flex-1">
                  <input
                    type="text"
                    placeholder="Last Name"
                    value={lastName}
                    onChange={(e) => setLastName(e.target.value)}
                    required
                    className="w-full px-4 py-3 bg-white/20 border border-white/40 text-white placeholder-white/70 rounded focus:outline-none focus:border-white"
                    style={{ fontFamily: 'Graphik Regular, sans-serif' }}
                  />
                  <label
                    className="text-white/70 text-xs mt-1 block"
                    style={{ fontFamily: 'Graphik Regular, sans-serif' }}
                  >
                    Last
                  </label>
                </div>
              </div>
              <div>
                <input
                  type="email"
                  placeholder="Email *"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  required
                  className="w-full px-4 py-3 bg-white/20 border border-white/40 text-white placeholder-white/70 rounded focus:outline-none focus:border-white"
                  style={{ fontFamily: 'Graphik Regular, sans-serif' }}
                />
              </div>
              <div>
                <button
                  type="submit"
                  className="bg-[#1E1B1C] text-white text-sm uppercase tracking-wider px-10 py-3 rounded-full hover:bg-white hover:text-[#507EEF] transition-colors"
                  style={{ fontFamily: 'Graphik Medium, sans-serif' }}
                >
                  Submit
                </button>
              </div>
            </form>
          )}
        </div>
      </div>
    </section>
  );
}
