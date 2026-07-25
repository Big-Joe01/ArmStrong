import React from 'react';
import type { Metadata, Viewport } from 'next';
import '../styles/index.css';

export const viewport: Viewport = {
  width: 'device-width',
  initialScale: 1,
};

export const metadata: Metadata = {
  title: 'Home | Armstrong Ceiling Solutions Australia',
  description:
    'Acoustic and sustainable ceiling tiles, ceiling panels, and ceiling systems designed to promote the well-being of building occupants. Seismic solutions available.',
  icons: {
    icon: [
      {
        url: '/favicon.ico',
        type: 'image/x-icon',
      },
    ],
  },
};

interface RootLayoutProps {
  children: React.ReactNode;
}

export default function RootLayout({
  children,
}: Readonly<RootLayoutProps>) {
  return (
    <html lang="en-AU" suppressHydrationWarning>
      <body>{children}</body>
    </html>
  );
}