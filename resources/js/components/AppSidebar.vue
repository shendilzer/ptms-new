<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, ListCheck, LocateOffIcon, User2Icon, Users, Car, UserCheck, Building2 } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { computed, ref, onMounted, onUnmounted } from 'vue';
import manufacturers from '@/routes/manufacturers';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
        roles: ['super_admin','inventory_manager','inventory_user','operator_manager','operator_viewer']
    },

    /* ============================================================================
       OPERATOR MANAGEMENT NAVIGATION - INDIVIDUAL ITEMS
       ============================================================================ */
    {
        title: 'Operators',
        href: '/operators',
        icon: Users,
        roles: ['super_admin','operator_manager','operator_viewer']
    },
    {
        title: 'Drivers',
        href: '/drivers',
        icon: UserCheck,
        roles: ['super_admin','operator_manager','operator_viewer']
    },
    {
        title: 'Motorcycles',
        href: '/motorcycles',
        icon: Car,
        roles: ['super_admin','operator_manager','operator_viewer']
    },
    {
        title: 'TODA',
        href: '/toda',
        icon: Building2,
        roles: ['super_admin','operator_manager','operator_viewer']
    }
];

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];

// Access the authenticated user's role from Inertia props
const page = usePage();
const userRole = computed(() => page.props.auth?.user?.role || null);

// Filter navigation items based on the user's role
const filteredNavItems = computed(() => {
    return mainNavItems.filter((item) => {
        // If no roles are defined, show the item to everyone
        if (!item.roles) return true;
        // Check if the user's role is allowed
        return item.roles.includes(userRole.value);
    });
});

// Responsive state
const isMobile = ref(false);
const sidebarOpen = ref(false);

const checkScreenSize = () => {
    isMobile.value = window.innerWidth < 768;
};

const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
};

// Close sidebar when clicking outside on mobile
const handleClickOutside = (event: MouseEvent) => {
    if (isMobile.value && sidebarOpen.value) {
        const sidebar = document.querySelector('.mobile-sidebar');
        const toggle = document.querySelector('.mobile-toggle');
        if (sidebar && !sidebar.contains(event.target as Node) &&
            toggle && !toggle.contains(event.target as Node)) {
            sidebarOpen.value = false;
        }
    }
};

onMounted(() => {
    checkScreenSize();
    window.addEventListener('resize', checkScreenSize);
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    window.removeEventListener('resize', checkScreenSize);
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div class="app-container">
        <!-- Mobile overlay -->
        <transition name="fade">
            <div
                v-if="isMobile && sidebarOpen"
                class="sidebar-overlay"
                @click="sidebarOpen = false"
            ></div>
        </transition>

        <!-- Mobile toggle button -->
        <button
            v-if="isMobile"
            class="mobile-toggle"
            @click="toggleSidebar"
            :class="{ 'open': sidebarOpen }"
            aria-label="Toggle sidebar"
        >
            <span></span>
            <span></span>
            <span></span>
        </button>

        <Sidebar
            collapsible="icon"
            variant="inset"
            :class="{
                'mobile-sidebar': isMobile,
                'sidebar-open': sidebarOpen && isMobile,
                'sidebar-closed': !sidebarOpen && isMobile,
                'glass-sidebar': true
            }"
        >
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" as-child class="logo-button">
                            <Link :href="dashboard()">
                                <AppLogo class="logo" />
                                <span class="logo-text">Admin Portal</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <div class="nav-section">
                    <div class="section-label">MAIN MENU</div>
                    <NavMain :items="filteredNavItems" />
                </div>
            </SidebarContent>

            <SidebarFooter>
                <div class="nav-section">
                    <div class="section-label">RESOURCES</div>
                    <NavFooter :items="footerNavItems" />
                </div>
                <NavUser />
            </SidebarFooter>
        </Sidebar>

        <main class="main-content" :class="{ 'sidebar-open': sidebarOpen && isMobile }">
            <div class="content-wrapper">
                <slot />
            </div>
        </main>
    </div>
</template>

<style scoped>
.app-container {
    display: flex;
    min-height: 100vh;
    position: relative;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

/* Mobile toggle button */
.mobile-toggle {
    display: none;
    position: fixed;
    top: 1.5rem;
    left: 1.5rem;
    z-index: 1000;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 12px;
    width: 50px;
    height: 50px;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    gap: 5px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    cursor: pointer;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    backdrop-filter: blur(10px);
}

.mobile-toggle:hover {
    transform: scale(1.1);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
}

.mobile-toggle span {
    display: block;
    width: 24px;
    height: 2.5px;
    background: white;
    border-radius: 2px;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    transform-origin: center;
}

.mobile-toggle.open span:nth-child(1) {
    transform: rotate(45deg) translate(6px, 6px);
    width: 24px;
}

.mobile-toggle.open span:nth-child(2) {
    opacity: 0;
    transform: scale(0);
}

.mobile-toggle.open span:nth-child(3) {
    transform: rotate(-45deg) translate(6px, -6px);
    width: 24px;
}

/* Enhanced Sidebar Design */
:deep(.glass-sidebar) {
    background: linear-gradient(135deg,
        rgba(255, 255, 255, 0.95) 0%,
        rgba(255, 255, 255, 0.90) 100%);
    backdrop-filter: blur(20px);
    border-right: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow:
        0 8px 32px rgba(0, 0, 0, 0.1),
        inset 1px 0 0 rgba(255, 255, 255, 0.5);
}

:deep(.sidebar) {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

:deep(.sidebar)::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg,
        transparent,
        rgba(102, 126, 234, 0.3),
        transparent);
}

/* Enhanced Logo Button */
.logo-button {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 16px;
    margin: 8px;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
    border: 1px solid rgba(102, 126, 234, 0.1);
    position: relative;
    overflow: hidden;
}

.logo-button:hover {
    transform: translateY(-2px) scale(1.02);
    box-shadow:
        0 12px 25px rgba(102, 126, 234, 0.15),
        0 4px 12px rgba(118, 75, 162, 0.1);
    border-color: rgba(102, 126, 234, 0.3);
}

.logo-button::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg,
        transparent,
        rgba(102, 126, 234, 0.1),
        transparent);
    transition: left 0.6s ease;
}

.logo-button:hover::before {
    left: 100%;
}

.logo {
    transition: all 0.4s ease;
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
}

.logo-button:hover .logo {
    transform: scale(1.1) rotate(5deg);
}

.logo-text {
    font-weight: 700;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-size: 1.1em;
    letter-spacing: -0.5px;
}

/* Section Labels */
.nav-section {
    margin-bottom: 2rem;
}

.section-label {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #6b7280;
    margin: 0 1rem 1rem;
    padding: 0.5rem 1rem;
    background: rgba(102, 126, 234, 0.05);
    border-radius: 8px;
    border-left: 3px solid #667eea;
}

/* Enhanced Main Content */
.main-content {
    flex: 1;
    background: rgba(249, 250, 251, 0.95);
    backdrop-filter: blur(10px);
    position: relative;
    overflow: hidden;
}

.main-content::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 240px;
    background: linear-gradient(135deg,
        rgba(102, 126, 234, 0.05) 0%,
        rgba(118, 75, 162, 0.05) 100%);
    z-index: 0;
}

.content-wrapper {
    position: relative;
    z-index: 1;
    min-height: 100vh;
}

/* Mobile styles */
@media (max-width: 767px) {
    .mobile-toggle {
        display: flex;
    }

    .mobile-sidebar {
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        z-index: 900;
        transform: translateX(-100%);
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        width: 280px;
        border-radius: 0 24px 24px 0;
    }

    .sidebar-open {
        transform: translateX(0);
        box-shadow:
            16px 0 40px rgba(0, 0, 0, 0.15),
            8px 0 20px rgba(0, 0, 0, 0.1);
    }

    .sidebar-closed {
        transform: translateX(-100%);
    }

    .sidebar-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 800;
        backdrop-filter: blur(4px);
    }

    .main-content {
        width: 100%;
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 0;
    }

    .main-content.sidebar-open {
        transform: translateX(280px) scale(0.98);
        border-radius: 24px 0 0 24px;
        overflow: hidden;
    }

    .app-container {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
}

/* Desktop styles */
@media (min-width: 768px) {
    :deep(.sidebar) {
        position: sticky;
        top: 0;
        height: 100vh;
        border-radius: 0 24px 24px 0;
        margin-right: 8px;
    }

    .main-content {
        flex: 1;
        transition: margin-left 0.4s ease;
        border-radius: 24px 0 0 24px;
        margin-left: 0;
        margin-top: 8px;
        margin-bottom: 8px;
        margin-right: 8px;
        box-shadow:
            -8px 0 25px rgba(0, 0, 0, 0.05),
            inset 1px 0 0 rgba(255, 255, 255, 0.8);
    }
}

/* Enhanced Animation Keyframes */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideInFromLeft {
    from {
        transform: translateX(-30px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideInFromRight {
    from {
        transform: translateX(30px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes scaleIn {
    from {
        transform: scale(0.9);
        opacity: 0;
    }
    to {
        transform: scale(1);
        opacity: 1;
    }
}

/* Enhanced transition classes */
.fade-enter-active,
.fade-leave-active {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Stagger animation for nav items */
:deep(.nav-item) {
    animation: slideInFromLeft 0.5s cubic-bezier(0.4, 0, 0.2, 1) forwards;
    opacity: 0;
    transform: translateX(-20px);
}

:deep(.nav-item:nth-child(1)) { animation-delay: 0.05s; }
:deep(.nav-item:nth-child(2)) { animation-delay: 0.1s; }
:deep(.nav-item:nth-child(3)) { animation-delay: 0.15s; }
:deep(.nav-item:nth-child(4)) { animation-delay: 0.2s; }
:deep(.nav-item:nth-child(5)) { animation-delay: 0.25s; }
:deep(.nav-item:nth-child(6)) { animation-delay: 0.3s; }

/* Hover effects for nav items */
:deep(.nav-item:hover) {
    transform: translateX(8px);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Smooth scrollbar */
:deep(::-webkit-scrollbar) {
    width: 6px;
}

:deep(::-webkit-scrollbar-track) {
    background: rgba(0, 0, 0, 0.05);
    border-radius: 3px;
}

:deep(::-webkit-scrollbar-thumb) {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 3px;
}

:deep(::-webkit-scrollbar-thumb:hover) {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a679e 100%);
}
</style>
