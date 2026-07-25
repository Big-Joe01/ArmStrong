import { imageHosts } from './image-hosts.config.mjs';

/** @type {import('next').NextConfig} */
const nextConfig = {
  images: {
    remotePatterns: imageHosts,
    minimumCacheTTL: 60,
  },
};

export default nextConfig;