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

                <a href="{{ route('dashboard.characters.index') }}"
                   class="group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-100 hover:text-blue-700 transition">
                    <svg class="mr-3 h-5 w-5 group-hover:text-blue-500" fill="none"
                         stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 10h.01M12 10h.01M16 10h.01M9 16h6M4 6h16M4 20h16"/>
                    </svg>
                    Characters
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
