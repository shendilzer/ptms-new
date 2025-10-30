<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';
import { Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
    theme?: 'rainbow' | 'sunset' | 'ocean' | 'forest' | 'neon';
}>();
</script>

<template>
    <div
        class="auth-layout-wrapper"
        :class="`theme-${theme || 'rainbow'}`"
    >
        <!-- Animated Background Elements -->
        <div class="animated-background">
            <div class="floating-orb orb-1"></div>
            <div class="floating-orb orb-2"></div>
            <div class="floating-orb orb-3"></div>
            <div class="floating-orb orb-4"></div>
        </div>

        <!-- Main Content -->
        <div class="content-container">
            <div class="card-wrapper">
                <!-- Header Section -->
                <div class="header-section">
                    <Link
                        :href="home()"
                        class="logo-link"
                    >
                        <div class="logo-container">
                            <AppLogoIcon
                                class="app-logo"
                            />
                            <div class="logo-glow"></div>
                        </div>
                        <span class="sr-only">{{ title }}</span>
                    </Link>

                    <div class="title-section">
                        <h1 class="title">{{ title }}</h1>
                        <p class="description">
                            {{ description }}
                        </p>
                    </div>
                </div>

                <!-- Content Slot -->
                <div class="content-section">
                    <slot />
                </div>
            </div>
        </div>

        <!-- Floating Particles -->
        <div class="particles-container">
            <div
                v-for="i in 12"
                :key="i"
                class="particle"
                :style="{
                    '--delay': `${(i - 1) * 0.5}s`,
                    '--duration': `${Math.random() * 3 + 2}s`
                }"
            ></div>
        </div>
    </div>
</template>

<style scoped>
/* CSS Variables for Themes */
.theme-rainbow {
    --primary: #FF6B6B;
    --secondary: #4ECDC4;
    --accent: #45B7D1;
    --gradient: linear-gradient(135deg, #FF6B6B, #4ECDC4, #45B7D1);
    --glass-bg: rgba(255, 255, 255, 0.15);
    --text-primary: #1a202c;
}

.theme-sunset {
    --primary: #FF6B6B;
    --secondary: #FF8E53;
    --accent: #FFCA61;
    --gradient: linear-gradient(135deg, #FF6B6B, #FF8E53, #FFCA61);
    --glass-bg: rgba(255, 255, 255, 0.12);
    --text-primary: #2d3748;
}

.theme-ocean {
    --primary: #4FC3F7;
    --secondary: #29B6F6;
    --accent: #03A9F4;
    --gradient: linear-gradient(135deg, #4FC3F7, #29B6F6, #03A9F4);
    --glass-bg: rgba(255, 255, 255, 0.1);
    --text-primary: white;
}

.theme-forest {
    --primary: #66BB6A;
    --secondary: #4CAF50;
    --accent: #388E3C;
    --gradient: linear-gradient(135deg, #66BB6A, #4CAF50, #388E3C);
    --glass-bg: rgba(255, 255, 255, 0.12);
    --text-primary: #1a202c;
}

.theme-neon {
    --primary: #FF00FF;
    --secondary: #00FFFF;
    --accent: #FFFF00;
    --gradient: linear-gradient(135deg, #FF00FF, #00FFFF, #FFFF00);
    --glass-bg: rgba(255, 255, 255, 0.08);
    --text-primary: white;
}

/* Main Layout */
.auth-layout-wrapper {
    min-height: 100svh;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    box-sizing: border-box;
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg,
        color-mix(in srgb, var(--primary) 5%, transparent 95%),
        color-mix(in srgb, var(--secondary) 8%, transparent 92%),
        color-mix(in srgb, var(--accent) 5%, transparent 95%)
    );
}

/* Animated Background */
.animated-background {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 0;
}

.floating-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(60px);
    opacity: 0.15;
    animation: float-orb 20s ease-in-out infinite;
}

.orb-1 {
    width: min(400px, 60vw);
    height: min(400px, 60vw);
    background: var(--primary);
    top: 10%;
    left: 5%;
    animation-delay: 0s;
}

.orb-2 {
    width: min(500px, 70vw);
    height: min(500px, 70vw);
    background: var(--secondary);
    bottom: 10%;
    right: 5%;
    animation-delay: -5s;
}

.orb-3 {
    width: min(300px, 50vw);
    height: min(300px, 50vw);
    background: var(--accent);
    top: 50%;
    left: 60%;
    animation-delay: -10s;
}

.orb-4 {
    width: min(350px, 55vw);
    height: min(350px, 55vw);
    background: var(--primary);
    bottom: 30%;
    left: 10%;
    animation-delay: -15s;
}

@keyframes float-orb {
    0%, 100% {
        transform: translate(0, 0) scale(1);
    }
    25% {
        transform: translate(5%, -3%) scale(1.1);
    }
    50% {
        transform: translate(-2%, 4%) scale(0.9);
    }
    75% {
        transform: translate(3%, 2%) scale(1.05);
    }
}

/* Content Container */
.content-container {
    width: 100%;
    max-width: min(400px, 95vw);
    position: relative;
    z-index: 2;
}

.card-wrapper {
    background: var(--glass-bg);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 24px;
    padding: clamp(1.5rem, 4vw, 2.5rem);
    box-shadow:
        0 20px 40px rgba(0, 0, 0, 0.1),
        0 0 80px rgba(255, 255, 255, 0.05);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.card-wrapper:hover {
    transform: translateY(-5px);
    box-shadow:
        0 30px 60px rgba(0, 0, 0, 0.15),
        0 0 120px rgba(255, 255, 255, 0.1);
}

/* Header Section */
.header-section {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: clamp(1rem, 3vw, 1.5rem);
    margin-bottom: clamp(1rem, 3vw, 1.5rem);
}

.logo-link {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
    transition: transform 0.3s ease;
}

.logo-link:hover {
    transform: scale(1.05);
}

.logo-container {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}

.app-logo {
    width: clamp(2.5rem, 6vw, 3rem);
    height: clamp(2.5rem, 6vw, 3rem);
    fill: currentColor;
    color: var(--text-primary);
    filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.1));
    z-index: 2;
    position: relative;
}

.logo-glow {
    position: absolute;
    width: 120%;
    height: 120%;
    background: var(--gradient);
    border-radius: 12px;
    opacity: 0.3;
    filter: blur(12px);
    animation: logo-pulse 3s ease-in-out infinite;
}

@keyframes logo-pulse {
    0%, 100% {
        opacity: 0.2;
        transform: scale(1);
    }
    50% {
        opacity: 0.4;
        transform: scale(1.1);
    }
}

/* Title Section */
.title-section {
    text-align: center;
    width: 100%;
}

.title {
    font-size: clamp(1.25rem, 4vw, 1.5rem);
    font-weight: 600;
    color: var(--text-primary);
    margin: 0 0 0.5rem 0;
    line-height: 1.3;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.description {
    font-size: clamp(0.875rem, 3vw, 0.9rem);
    color: color-mix(in srgb, var(--text-primary) 70%, transparent);
    line-height: 1.5;
    margin: 0;
    max-width: 100%;
}

/* Content Section */
.content-section {
    width: 100%;
}

/* Floating Particles */
.particles-container {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 1;
}

.particle {
    position: absolute;
    width: 4px;
    height: 4px;
    background: var(--gradient);
    border-radius: 50%;
    opacity: 0;
    animation: particle-float var(--duration, 3s) ease-in-out infinite;
    animation-delay: var(--delay, 0s);
}

.particle:nth-child(odd) {
    background: var(--primary);
}

.particle:nth-child(even) {
    background: var(--secondary);
}

@keyframes particle-float {
    0% {
        transform: translateY(100vh) translateX(0) rotate(0deg);
        opacity: 0;
    }
    10% {
        opacity: 0.7;
    }
    90% {
        opacity: 0.3;
    }
    100% {
        transform: translateY(-100px) translateX(100px) rotate(360deg);
        opacity: 0;
    }
}

/* Ultra-Responsive Breakpoints */
@media (max-width: 360px) {
    .auth-layout-wrapper {
        padding: 0.75rem;
    }

    .card-wrapper {
        padding: 1.25rem;
        border-radius: 20px;
    }

    .header-section {
        gap: 0.875rem;
        margin-bottom: 0.875rem;
    }

    .floating-orb {
        filter: blur(40px);
        opacity: 0.1;
    }
}

@media (max-width: 480px) {
    .card-wrapper {
        max-width: 95vw;
    }

    .particle {
        display: none; /* Reduce animations on very small screens */
    }
}

@media (min-width: 481px) and (max-width: 768px) {
    .content-container {
        max-width: min(380px, 85vw);
    }

    .card-wrapper {
        padding: 2rem;
    }
}

@media (min-width: 769px) and (max-width: 1024px) {
    .content-container {
        max-width: min(420px, 75vw);
    }
}

@media (min-width: 1025px) {
    .content-container {
        max-width: min(440px, 50vw);
    }
}

/* Landscape Orientation */
@media (max-height: 600px) and (orientation: landscape) {
    .auth-layout-wrapper {
        padding: 0.5rem;
        min-height: 120vh;
    }

    .card-wrapper {
        padding: 1.5rem;
    }

    .header-section {
        flex-direction: row;
        text-align: left;
        gap: 1rem;
    }

    .title-section {
        text-align: left;
    }
}

/* Reduced Motion Support */
@media (prefers-reduced-motion: reduce) {
    .floating-orb,
    .particle,
    .logo-glow,
    .card-wrapper {
        animation: none;
        transition: none;
    }

    .card-wrapper:hover {
        transform: none;
    }
}

/* High Contrast Mode */
@media (prefers-contrast: high) {
    .card-wrapper {
        background: white;
        border: 2px solid var(--primary);
        backdrop-filter: none;
    }

    .floating-orb,
    .particle {
        display: none;
    }
}

/* Dark Mode Adjustments */
@media (prefers-color-scheme: dark) {
    .theme-rainbow,
    .theme-sunset,
    .theme-forest {
        --text-primary: white;
    }

    .card-wrapper {
        background: rgba(0, 0, 0, 0.2);
        border-color: rgba(255, 255, 255, 0.1);
    }
}

/* Touch Device Optimizations */
@media (hover: none) and (pointer: coarse) {
    .card-wrapper:hover {
        transform: none;
    }

    .logo-link:hover {
        transform: none;
    }

    .card-wrapper:active {
        transform: scale(0.98);
    }
}
</style>
