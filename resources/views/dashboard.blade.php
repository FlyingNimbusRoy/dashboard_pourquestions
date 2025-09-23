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
                              d="M3 12l2-2m0 0l7-7 7 7M13 5v6h6m-6 0l2 2m-2-2L5 21h14"/>
                    </svg>
                    Home
                </a>

                <a href="{{ route('questions.index') }}"
                   class="group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-100 hover:text-blue-700 transition">
                    <svg class="mr-3 h-5 w-5 group-hover:text-blue-500" fill="none"
                         stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 10h.01M12 10h.01M16 10h.01M9 16h6M4 6h16M4 20h16"/>
                    </svg>
                    Questions
                </a>

                <a href="{{ route('dashboard.comments.index') }}"
                   class="group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-100 hover:text-blue-700 transition">
                    <svg class="mr-3 h-5 w-5 group-hover:text-blue-500" fill="none"
                         stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8-1.47 0-2.857-.316-4.02-.87L3 20l1.33-3.33C3.48 15.57 3 13.84 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    Comments
                </a>


                <a href="{{ route('dashboard.categories.index') }}"
                   class="group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-100 hover:text-blue-700 transition">
                    <svg class="mr-3 h-5 w-5 group-hover:text-blue-500" fill="none"
                         stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 10h.01M12 10h.01M16 10h.01M9 16h6M4 6h16M4 20h16"/>
                    </svg>
                    Categories
                </a>

                <a href="{{ route('dashboard.gamepacks.index') }}"
                   class="group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-100 hover:text-blue-700 transition">
                    <svg class="mr-3 h-5 w-5 group-hover:text-blue-500" fill="none"
                         stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 10h.01M12 10h.01M16 10h.01M9 16h6M4 6h16M4 20h16"/>
                    </svg>
                    Gamepacks
                </a>

                <!-- Modifiers Menu -->
                <div x-data="{ open: {{ request()->routeIs('dashboard.modifiers.*') ? 'true' : 'false' }} }" class="relative">
                    <button @click="open = !open"
                            class="group flex items-center w-full px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-100 hover:text-blue-700 transition">
                        <svg class="mr-3 h-5 w-5 group-hover:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M11.983 2.25c-.38 0-.74.214-.91.553l-1.045 2.09a7.48 7.48 0 00-1.902.785L6.06 4.85a.963.963 0 00-1.317.126l-1.5 1.5a.963.963 0 00.126 1.317l.828 1.067a7.48 7.48 0 00-.785 1.902L2.322 11.07a.963.963 0 00-.553.91v2.04c0 .38.214.74.553.91l2.09 1.045c.183.683.45 1.327.785 1.902l-1.067.828a.963.963 0 00-.126 1.317l1.5 1.5c.336.336.87.376 1.317.126l1.067-.828c.575.335 1.219.602 1.902.785l1.045 2.09c.17.34.53.553.91.553h2.04c.38 0 .74-.214.91-.553l1.045-2.09a7.48 7.48 0 001.902-.785l1.067.828c.447.25.981.21 1.317-.126l1.5-1.5a.963.963 0 00-.126-1.317l-.828-1.067c.335-.575.602-1.219.785-1.902l2.09-1.045c.34-.17.553-.53.553-.91v-2.04c0-.38-.214-.74-.553-.91l-2.09-1.045a7.48 7.48 0 00-.785-1.902l.828-1.067a.963.963 0 00.126-1.317l-1.5-1.5a.963.963 0 00-1.317-.126l-1.067.828a7.48 7.48 0 00-1.902-.785l-1.045-2.09a.963.963 0 00-.91-.553h-2.04zM12 15.75a3.75 3.75 0 110-7.5 3.75 3.75 0 010 7.5z"/>
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
                                  d="M8 10h.01M12 10h.01M16 10h.01M9 16h6M4 6h16M4 20h16"/>
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
                              d="M17 20h5V10H2v10h5m10-6a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    Users
                </a>

                <a href="{{ route('dashboard.tools.index') }}"
                   class="group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-100 hover:text-blue-700 transition">
                    <svg class="mr-3 h-5 w-5 group-hover:text-blue-500"
                         xmlns="http://www.w3.org/2000/svg" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 21l1.5-1.5m15-15L21 3m-3 3l-3 3m-6 6l-3 3m3-3a9 9 0 0112.728-12.728A9 9 0 016 18z"/>
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
