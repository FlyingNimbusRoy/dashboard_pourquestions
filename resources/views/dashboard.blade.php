<x-app-layout>
    <div class="flex min-h-screen bg-gray-50">
        <!-- Sidebar -->
        <aside class="w-96 shadow-lg rounded-r-lg flex flex-col">

            <div class="px-6 py-4 border-b dashboard__label">
                <h2 class="text-xl font-bold">Dashboard</h2>
            </div>

            <nav class="flex-1 px-2 py-4 space-y-1 bg-gray-50 navigation--sidebar">
                <a href="{{ route('dashboard') }}"
                   class="group flex items-center px-3 py-2 text-sm font-medium  rounded-md hover:bg-blue-100 hover:text-blue-700 transition">
                    <svg class="mr-3 h-5 w-5 group-hover:text-blue-500" fill="none"
                         stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 9.75L12 3l9 6.75V21a1 1 0 01-1 1H15v-5h-6v5H4a1 1 0 01-1-1V9.75z"/>
                    </svg>
                    Home
                </a>

                <a href="{{ route('questions.index') }}"
                   class="group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-100 hover:text-blue-700 transition">
                    <svg class="mr-3 h-5 w-5 group-hover:text-blue-500" fill="none"
                         stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2M9 5h6m-3 4v4m0 4h.01"/>
                    </svg>
                    Questions
                </a>

                <a href="{{ route('dashboard.comments.index') }}"
                   class="group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-100 hover:text-blue-700 transition">
                    <svg class="mr-3 h-5 w-5 group-hover:text-blue-500" fill="none"
                         stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M7 8h10M7 12h6m-8 8l4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2H9l-4 4z"/>
                    </svg>
                    Comments
                </a>

                <a href="{{ route('dashboard.categories.index') }}"
                   class="group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-100 hover:text-blue-700 transition">
                    <svg class="mr-3 h-5 w-5 group-hover:text-blue-500" fill="none"
                         stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M7 7h.01M3 5a2 2 0 012-2h5.586a1 1 0 01.707.293l9.414 9.414a2 2 0 010 2.828l-5.586 5.586a2 2 0 01-2.828 0L3.293 11.707A1 1 0 013 11V5z"/>
                    </svg>
                    Categories
                </a>

                <!-- Gamepacks Menu -->
                <div x-data="{ open: {{ request()->routeIs('dashboard.gamepacks.*') ? 'true' : 'false' }} }" class="relative">
                    <button @click="open = !open"
                            class="group flex items-center w-full px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-100 hover:text-blue-700 transition">
                        <svg class="mr-3 h-5 w-5 group-hover:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/>
                        </svg>
                        Gamepacks
                        <svg :class="{'transform rotate-90': open}" class="ml-auto h-4 w-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                    <div x-show="open" class="mt-1 ml-6 flex flex-col space-y-1">
                        <a href="{{ route('dashboard.gamepacks.index') }}"
                           class="text-sm px-5 py-2 rounded hover:bg-blue-100 hover:text-blue-700 transition {{ request()->routeIs('dashboard.gamepacks.index') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                            Overview
                        </a>
                        <a href="{{ route('dashboard.gamepacks.upload') }}"
                           class="text-sm px-5 py-2 rounded hover:bg-blue-100 hover:text-blue-700 transition {{ request()->routeIs('dashboard.gamepacks.upload*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                            Upload
                        </a>
                    </div>
                </div>

                <!-- Modifiers Menu -->
                <div x-data="{ open: {{ request()->routeIs('dashboard.modifiers.*') ? 'true' : 'false' }} }" class="relative">
                    <button @click="open = !open"
                            class="group flex items-center w-full px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-100 hover:text-blue-700 transition">
                        <svg class="mr-3 h-5 w-5 group-hover:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                        </svg>
                        Modifiers
                        <svg :class="{'transform rotate-90': open}" class="ml-auto h-4 w-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                    <div x-show="open" class="mt-1 ml-6 flex flex-col space-y-1">
                        <a href="{{ route('dashboard.modifiers.index') }}"
                           class="text-sm px-5 py-2 rounded hover:bg-blue-100 hover:text-blue-700 transition {{ request()->routeIs('dashboard.modifiers.index') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                            Overview
                        </a>
                        <a href="{{ route('dashboard.modifiers.upload') }}"
                           class="text-sm px-5 py-2 rounded hover:bg-blue-100 hover:text-blue-700 transition {{ request()->routeIs('dashboard.modifiers.upload*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                            Upload
                        </a>
                    </div>
                </div>

                <!-- Characters Menu -->
                <div x-data="{ open: {{ request()->routeIs('dashboard.characters.*') ? 'true' : 'false' }} }" class="relative">
                    <button @click="open = !open"
                            class="group flex items-center w-full px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-100 hover:text-blue-700 transition">
                        <svg class="mr-3 h-5 w-5 group-hover:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M5.121 17.804A8.966 8.966 0 0112 15a8.966 8.966 0 016.879 2.804M15 10a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Characters
                        <svg :class="{'transform rotate-90': open}" class="ml-auto h-4 w-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                    <div x-show="open" class="mt-1 ml-6 flex flex-col space-y-1">
                        <a href="{{ route('dashboard.characters.index') }}"
                           class="text-sm px-5 py-2 rounded hover:bg-blue-100 hover:text-blue-700 transition {{ request()->routeIs('dashboard.characters.index') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                            Overview
                        </a>
                        <a href="{{ route('dashboard.characters.upload') }}"
                           class="text-sm px-5 py-2 rounded hover:bg-blue-100 hover:text-blue-700 transition {{ request()->routeIs('dashboard.characters.upload*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                            Upload
                        </a>
                    </div>
                </div>

                <!-- Achievements Menu -->
                <div x-data="{ open: {{ request()->routeIs('dashboard.achievements.*') ? 'true' : 'false' }} }" class="relative">
                    <button @click="open = !open"
                            class="group flex items-center w-full px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-100 hover:text-blue-700 transition">
                        <svg class="mr-3 h-5 w-5 group-hover:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        Achievements
                        <svg :class="{'transform rotate-90': open}" class="ml-auto h-4 w-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                    <div x-show="open" class="mt-1 ml-6 flex flex-col space-y-1">
                        <a href="{{ route('dashboard.achievements.index') }}"
                           class="text-sm px-5 py-2 rounded hover:bg-blue-100 hover:text-blue-700 transition {{ request()->routeIs('dashboard.achievements.index') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                            Overview
                        </a>
                        <a href="{{ route('dashboard.achievements.upload') }}"
                           class="text-sm px-5 py-2 rounded hover:bg-blue-100 hover:text-blue-700 transition {{ request()->routeIs('dashboard.achievements.upload*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                            Badge Images
                        </a>
                    </div>
                </div>

                <a href="{{ route('dashboard.users.index') }}"
                   class="group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-100 hover:text-blue-700 transition">
                    <svg class="mr-3 h-5 w-5 group-hover:text-blue-500" fill="none"
                         stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 20h2a2 2 0 002-2v-1a5 5 0 00-4-4.9M15 7a3 3 0 11-6 0 3 3 0 016 0zm-9 13v-1a5 5 0 015-5h4a5 5 0 015 5v1"/>
                    </svg>
                    Users
                </a>

                <a href="{{ route('dashboard.tools.index') }}"
                   class="group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-100 hover:text-blue-700 transition">
                    <svg class="mr-3 h-5 w-5 group-hover:text-blue-500" fill="none"
                         stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM12 15a3 3 0 100-6 3 3 0 000 6z"/>
                    </svg>
                    Tools
                </a>

                <!-- ── Wild Hunt ────────────────────────────────────────────── -->
                <div class="pt-2 pb-1 px-3">
                    <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">WildHunt</p>
                </div>

                <!-- Quarries -->
                <a href="{{ route('dashboard.wildhunt.quarries.index') }}"
                   class="group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-purple-100 hover:text-purple-700 transition {{ request()->routeIs('dashboard.wildhunt.quarries.*') ? 'bg-purple-50 text-purple-700 font-semibold' : '' }}">
                    <svg class="mr-3 h-5 w-5 group-hover:text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/>
                    </svg>
                    Quarries
                </a>

                <!-- Monsters -->
                <a href="{{ route('dashboard.wildhunt.monsters.index') }}"
                   class="group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-purple-100 hover:text-purple-700 transition {{ request()->routeIs('dashboard.wildhunt.monsters.*') ? 'bg-purple-50 text-purple-700 font-semibold' : '' }}">
                    <svg class="mr-3 h-5 w-5 group-hover:text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    Monsters
                </a>

                <!-- Skills -->
                <a href="{{ route('dashboard.wildhunt.skills.index') }}"
                   class="group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-purple-100 hover:text-purple-700 transition {{ request()->routeIs('dashboard.wildhunt.skills.*') ? 'bg-purple-50 text-purple-700 font-semibold' : '' }}">
                    <svg class="mr-3 h-5 w-5 group-hover:text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Skills
                </a>

                <!-- Gear -->
                <a href="{{ route('dashboard.wildhunt.gear.index') }}"
                   class="group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-purple-100 hover:text-purple-700 transition {{ request()->routeIs('dashboard.wildhunt.gear.*') ? 'bg-purple-50 text-purple-700 font-semibold' : '' }}">
                    <svg class="mr-3 h-5 w-5 group-hover:text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                    Gear
                </a>

            </nav>

            <div class="px-6 py-4 border-t dashboard__label">
                <a href="{{ route('profile.edit') }}"
                   class="flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-100 transition">
                    Profile
                </a>
            </div>
        </aside>

        <!-- Main content -->
        <main class="flex-1 p-6 main__content--wrapper">
            <div class="bg-white shadow rounded-lg p-6">
                @yield('content')
            </div>
        </main>
    </div>
</x-app-layout>