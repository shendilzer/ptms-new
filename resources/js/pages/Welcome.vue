<script setup lang="ts">
import { dashboard, login, register } from '@/routes';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Welcome">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div class="min-h-screen flex flex-col bg-[#FDFDFC] text-[#1b1b18] dark:bg-[#0a0a0a] dark:text-[#EDEDEC]">
        <!-- Header -->
        <header class="bg-white dark:bg-[#1a1a1a] shadow-sm border-b border-gray-200 dark:border-gray-800">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-4">
                    <!-- Logo - Updated for better fit -->
                    <div class="flex-shrink-0">
                        <Link href="/" class="flex items-center space-x-3">
                            <img
                                src="/PTMS.png"
                                alt="LGU Maramag Logo"
                                class="h-12 w-auto max-w-[200px] object-contain"
                            />
                            <span class="text-xl font-bold text-[#1b1b18] dark:text-[#EDEDEC] hidden sm:block">
                                PTMS
                            </span>
                        </Link>
                    </div>

                    <!-- Desktop Navigation -->
                    <nav class="hidden md:flex items-center space-x-6">

                    </nav>

                    <!-- Auth Buttons -->
                    <div class="flex items-center space-x-4">
                        <template v-if="$page.props.auth.user">
                            <Link
                                :href="dashboard()"
                                class="hidden sm:inline-block rounded-sm border border-[#19140035] px-4 py-2 text-sm font-medium text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b] transition-colors"
                            >
                                Dashboard
                            </Link>
                        </template>
                        <template v-else>
                            <Link
                                :href="login()"
                                 class="rounded-sm bg-[#1b1b18] border border-[#19140035] px-4 py-2 text-sm font-medium text-white hover:bg-[#2a2a27] dark:bg-[#EDEDEC] dark:text-[#1b1b18] dark:hover:bg-[#d4d4d4] transition-colors"
                            >
                                Log in
                            </Link>

                        </template>

                        <!-- Mobile menu button -->
                        <button class="md:hidden p-2 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile Navigation -->
            <div class="md:hidden bg-white dark:bg-[#1a1a1a] border-t border-gray-200 dark:border-gray-800">
                <div class="px-2 pt-2 pb-3 space-y-1">

                    <template v-if="!$page.props.auth.user">
                        <Link
                            :href="login()"
                            class="block px-3 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-md"
                        >
                            Log in
                        </Link>
                    </template>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-grow">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <!-- Hero Section -->
                <section class="text-center max-w-6xl mx-auto">
                    <!-- Main Logo in Hero Section -->
                    <div class="flex justify-center mb-8">
                        <img
                            src="/PTMS.png"
                            alt="LGU Maramag Public Transport Management System"
                            class="h-24 w-auto max-w-xs object-contain"
                        />
                    </div>

                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold mb-6 leading-tight">
                        Public Transport Management System
                    </h1>
                    <p class="text-lg sm:text-xl text-gray-600 dark:text-gray-400 mb-8 max-w-4xl mx-auto leading-relaxed">
                        The LGU Maramag Public Transport Management System is a comprehensive digital solution designed to modernize and streamline public transportation operations within Maramag Municipality. This system aims to enhance commuter experience, improve operational efficiency, and provide real-time monitoring capabilities for both passengers and transport authorities.
                    </p>

                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-12">

                    </div>

                    <!-- Features Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-16">

                    </div>
                </section>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white dark:bg-[#1a1a1a] border-t border-gray-200 dark:border-gray-800">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <!-- Logo and Info -->
                    <div class="md:col-span-2">
                        <div class="flex items-center space-x-3 mb-4">
                            <img
                                src="/PTMS.png"
                                alt="LGU Maramag Logo"
                                class="h-16 w-auto max-w-[180px] object-contain"
                            />
                            <div>
                                <h2 class="text-lg font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Public Transport Management System</h2>
                                <p class="text-sm text-gray-600 dark:text-gray-400">LGU Maramag, Bukidnon</p>
                            </div>
                        </div>
                        <p class="text-gray-600 dark:text-gray-400 max-w-md text-sm">
                            Modernizing public transportation in Maramag Municipality through digital innovation and efficient management solutions.
                        </p>
                    </div>

                    <!-- Quick Links -->
                    <div>

                    </div>

                    <!-- Support -->
                    <div>

                    </div>
                </div>

                <!-- Bottom Bar -->
                <div class="border-t border-gray-200 dark:border-gray-800 mt-8 pt-8 flex flex-col md:flex-row justify-between items-center">
                    <p class="text-gray-500 dark:text-gray-400 text-sm">
                        &copy; 2025 LGU Maramag - Public Transport Management System. All rights reserved.
                    </p>
                    <div class="flex space-x-4 mt-4 md:mt-0">
                        <a href="#" class="text-gray-400 hover:text-[#1b1b18] dark:hover:text-white transition-colors">
                            <span class="sr-only">Facebook</span>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.879v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.129 22 16.99 22 12z" clip-rule="evenodd" />
                            </svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-[#1b1b18] dark:hover:text-white transition-colors">
                            <span class="sr-only">Twitter</span>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
