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
