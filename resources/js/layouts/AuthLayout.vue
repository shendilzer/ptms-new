<script setup lang="ts">
import AuthLayout from '@/layouts/auth/AuthSimpleLayout.vue';
import { computed, ref, onMounted, onUnmounted } from 'vue';

interface Props {
    title?: string;
    description?: string;
    maxWidth?: string;
    padding?: string;
    background?: 'gradient' | 'glass' | 'colorful' | 'animated';
    theme?: 'rainbow' | 'sunset' | 'ocean' | 'forest' | 'neon';
}

const props = withDefaults(defineProps<Props>(), {
    title: '',
    description: '',
    maxWidth: '400px',
    padding: '1rem',
    background: 'colorful',
    theme: 'rainbow'
});

const containerStyle = computed(() => ({
    maxWidth: props.maxWidth,
    padding: props.padding
}));

const containerClass = computed(() => [
    'responsive-container',
    `background-${props.background}`,
    `theme-${props.theme}`
]);

// Dynamic background particles
const particles = ref<Array<{x: number, y: number, size: number, color: string}>>([]);
let animationFrame: number;

const generateParticles = (count: number = 15) => {
    const themes = {
        rainbow: ['#FF6B6B', '#4ECDC4', '#45B7D1', '#FFA07A', '#98D8C8', '#F7DC6F'],
        sunset: ['#FF9A8B', '#FF6B6B', '#FF8E53', '#FFCA61', '#FF6B95', '#FF9671'],
        ocean: ['#4FC3F7', '#29B6F6', '#03A9F4', '#0288D1', '#4FC3F7', '#81D4FA'],
        forest: ['#66BB6A', '#4CAF50', '#388E3C', '#81C784', '#A5D6A7', '#C8E6C9'],
        neon: ['#FF00FF', '#00FFFF', '#FFFF00', '#FF00FF', '#00FF00', '#FF0000']
    };

    const colors = themes[props.theme] || themes.rainbow;

    particles.value = Array.from({ length: count }, () => ({
        x: Math.random() * 100,
        y: Math.random() * 100,
        size: Math.random() * 4 + 2,
        color: colors[Math.floor(Math.random() * colors.length)],
        speed: Math.random() * 0.5 + 0.1
    }));
};

const animateParticles = () => {
    particles.value.forEach(particle => {
        particle.y += particle.speed;
        if (particle.y > 100) {
            particle.y = -5;
            particle.x = Math.random() * 100;
        }
    });
    animationFrame = requestAnimationFrame(animateParticles);
};

onMounted(() => {
    generateParticles();
    animateParticles();
});

onUnmounted(() => {
    if (animationFrame) {
        cancelAnimationFrame(animationFrame);
    }
});
</script>

<template>
    <AuthLayout
        :title="title"
        :description="description"
        :class="$attrs.class"
    >
        <div class="design-wrapper">
            <!-- Animated Background -->
            <div class="animated-background" :class="`theme-${theme}`">
                <div
                    v-for="(particle, index) in particles"
                    :key="index"
                    class="floating-particle"
                    :style="{
                        left: `${particle.x}%`,
                        top: `${particle.y}%`,
                        width: `${particle.size}px`,
                        height: `${particle.size}px`,
                        backgroundColor: particle.color,
                        animationDelay: `${Math.random() * 2}s`
                    }"
                ></div>
            </div>

            <!-- Gradient Orbs -->
            <div class="gradient-orbs">
                <div class="orb orb-1" :class="`theme-${theme}`"></div>
                <div class="orb orb-2" :class="`theme-${theme}`"></div>
                <div class="orb orb-3" :class="`theme-${theme}`"></div>
            </div>

            <!-- Main Content Container -->
            <div :class="containerClass" :style="containerStyle">
                <!-- Animated Header -->
                <div class="animated-header">
                    <div class="pulse-dots">
                        <div class="dot dot-1" :class="`theme-${theme}`"></div>
                        <div class="dot dot-2" :class="`theme-${theme}`"></div>
                        <div class="dot dot-3" :class="`theme-${theme}`"></div>
                    </div>
                    <div class="color-wave" :class="`theme-${theme}`"></div>
                </div>

                <!-- Content Slot -->
                <div class="content-wrapper">
                    <slot />
                </div>

                <!-- Animated Footer -->
                <div class="animated-footer">
                    <div class="color-trail" :class="`theme-${theme}`"></div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>

<style scoped>
.design-wrapper {
    position: relative;
    width: 100%;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    box-sizing: border-box;
    overflow: hidden;
}

/* Animated Background */
.animated-background {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: -2;
}

.floating-particle {
    position: absolute;
    border-radius: 50%;
    opacity: 0.6;
    animation: float-particle 8s ease-in-out infinite;
    filter: blur(1px);
}

@keyframes float-particle {
    0%, 100% {
        transform: translateY(0) rotate(0deg);
        opacity: 0.3;
    }
    25% {
        transform: translateX(10px) rotate(90deg);
        opacity: 0.6;
    }
    50% {
        transform: translateY(-10px) rotate(180deg);
        opacity: 0.8;
    }
    75% {
        transform: translateX(-10px) rotate(270deg);
        opacity: 0.6;
    }
}

/* Gradient Orbs */
.gradient-orbs {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: -1;
}

.orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(40px);
    opacity: 0.4;
    animation: orb-move 15s ease-in-out infinite;
}

.orb-1 {
    width: 300px;
    height: 300px;
    top: 10%;
    left: 10%;
    animation-delay: 0s;
}

.orb-2 {
    width: 400px;
    height: 400px;
    top: 60%;
    right: 10%;
    animation-delay: -5s;
}

.orb-3 {
    width: 250px;
    height: 250px;
    bottom: 20%;
    left: 20%;
    animation-delay: -10s;
}

@keyframes orb-move {
    0%, 100% {
        transform: translate(0, 0) scale(1);
    }
    25% {
        transform: translate(50px, -30px) scale(1.1);
    }
    50% {
        transform: translate(-30px, 40px) scale(0.9);
    }
    75% {
        transform: translate(40px, 30px) scale(1.05);
    }
}

/* Theme Colors */
.theme-rainbow {
    --primary: #FF6B6B;
    --secondary: #4ECDC4;
    --accent: #45B7D1;
    --gradient: linear-gradient(135deg, #FF6B6B, #4ECDC4, #45B7D1);
}

.theme-sunset {
    --primary: #FF6B6B;
    --secondary: #FF8E53;
    --accent: #FFCA61;
    --gradient: linear-gradient(135deg, #FF6B6B, #FF8E53, #FFCA61);
}

.theme-ocean {
    --primary: #4FC3F7;
    --secondary: #29B6F6;
    --accent: #03A9F4;
    --gradient: linear-gradient(135deg, #4FC3F7, #29B6F6, #03A9F4);
}

.theme-forest {
    --primary: #66BB6A;
    --secondary: #4CAF50;
    --accent: #388E3C;
    --gradient: linear-gradient(135deg, #66BB6A, #4CAF50, #388E3C);
}

.theme-neon {
    --primary: #FF00FF;
    --secondary: #00FFFF;
    --accent: #FFFF00;
    --gradient: linear-gradient(135deg, #FF00FF, #00FFFF, #FFFF00);
}

/* Main Container Styles */
.responsive-container {
    width: 100%;
    margin: 0 auto;
    box-sizing: border-box;
    border-radius: 24px;
    position: relative;
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

/* Background Variants */
.background-glass {
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(20px);
}

.background-gradient {
    background: var(--gradient);
    color: white;
}

.background-colorful {
    background: linear-gradient(135deg,
        rgba(255, 255, 255, 0.2) 0%,
        rgba(255, 255, 255, 0.1) 50%,
        rgba(255, 255, 255, 0.05) 100%);
    backdrop-filter: blur(25px);
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.background-animated {
    background: linear-gradient(-45deg,
        rgba(255, 255, 255, 0.1),
        rgba(255, 255, 255, 0.05),
        rgba(255, 255, 255, 0.1),
        rgba(255, 255, 255, 0.05));
    background-size: 400% 400%;
    animation: gradient-shift 8s ease infinite;
    backdrop-filter: blur(20px);
}

@keyframes gradient-shift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

/* Animated Header */
.animated-header {
    position: relative;
    height: 80px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
}

.pulse-dots {
    display: flex;
    gap: 8px;
    margin-bottom: 12px;
}

.dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    animation: pulse 2s ease-in-out infinite;
}

.dot-1 { animation-delay: 0s; }
.dot-2 { animation-delay: 0.3s; }
.dot-3 { animation-delay: 0.6s; }

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
        opacity: 0.7;
    }
    50% {
        transform: scale(1.3);
        opacity: 1;
    }
}

.color-wave {
    width: 100%;
    height: 4px;
    border-radius: 2px;
    background: var(--gradient);
    animation: wave 3s ease-in-out infinite;
}

@keyframes wave {
    0%, 100% {
        transform: scaleX(0.8);
        opacity: 0.6;
    }
    50% {
        transform: scaleX(1);
        opacity: 1;
    }
}

.content-wrapper {
    position: relative;
    z-index: 2;
}

/* Animated Footer */
.animated-footer {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 6px;
    overflow: hidden;
}

.color-trail {
    width: 200%;
    height: 100%;
    background: var(--gradient);
    animation: trail 4s linear infinite;
}

@keyframes trail {
    0% { transform: translateX(-50%); }
    100% { transform: translateX(0%); }
}

/* Enhanced Hover Effects */
.responsive-container:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow:
        0 25px 50px rgba(0, 0, 0, 0.25),
        0 0 100px rgba(255, 255, 255, 0.1);
}

/* Ultra-Responsive Design */
@media (max-width: 360px) {
    .design-wrapper {
        padding: 0.5rem;
    }

    .responsive-container {
        border-radius: 16px;
        padding: 1.25rem !important;
    }

    .animated-header {
        height: 60px;
    }

    .orb {
        filter: blur(30px);
        opacity: 0.3;
    }

    .floating-particle {
        display: none;
    }
}

@media (max-width: 480px) {
    .responsive-container {
        padding: 1.5rem;
        max-width: 95vw;
        border-radius: 20px;
    }

    .gradient-orbs .orb {
        width: 200px;
        height: 200px;
    }
}

@media (min-width: 481px) and (max-width: 768px) {
    .responsive-container {
        padding: 2rem;
        max-width: 85vw;
    }

    .orb {
        opacity: 0.35;
    }
}

@media (min-width: 769px) and (max-width: 1024px) {
    .responsive-container {
        padding: 2.5rem;
        max-width: 75vw;
    }
}

@media (min-width: 1025px) and (max-width: 1440px) {
    .responsive-container {
        padding: 3rem;
        max-width: 450px;
    }
}

@media (min-width: 1441px) {
    .responsive-container {
        padding: 3.5rem;
        max-width: 500px;
    }
}

/* Orientation-specific styles */
@media (max-height: 600px) and (orientation: landscape) {
    .design-wrapper {
        padding: 0.5rem;
        min-height: 120vh;
    }

    .responsive-container {
        max-width: 90vw;
        padding: 1.5rem !important;
    }

    .animated-header {
        height: 50px;
        margin-bottom: 0.5rem;
    }
}

/* Touch device optimizations */
@media (hover: none) and (pointer: coarse) {
    .responsive-container:hover {
        transform: none;
    }

    .responsive-container:active {
        transform: scale(0.98);
    }
}

/* High contrast mode support */
@media (prefers-contrast: high) {
    .responsive-container {
        border: 2px solid var(--primary);
        backdrop-filter: none;
        background: white;
    }

    .floating-particle,
    .gradient-orbs {
        display: none;
    }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
    .floating-particle,
    .orb,
    .color-wave,
    .color-trail,
    .dot {
        animation: none;
    }

    .responsive-container {
        transition: none;
    }

    .background-animated {
        animation: none;
    }
}

/* Dark mode enhancements */
@media (prefers-color-scheme: dark) {
    .background-glass,
    .background-colorful {
        background: rgba(0, 0, 0, 0.2);
        border-color: rgba(255, 255, 255, 0.1);
    }
}

/* Performance optimizations */
@media (prefers-reduced-transparency: reduce) {
    .background-glass,
    .background-colorful,
    .background-animated {
        backdrop-filter: none;
        background: white;
    }
}
</style>
