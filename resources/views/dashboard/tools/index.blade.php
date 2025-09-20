@extends('dashboard')

@section('content')
    <h1 class="text-2xl font-bold mb-6">Tools</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Grading Tool -->
        <div class="bg-white shadow-lg rounded-lg p-6 flex flex-col items-start">
            <h2 class="text-xl font-semibold mb-2">Grading Difficulty Tool</h2>
            <p class="text-gray-600 mb-4">
                Update and manage the difficulty of questions in bulk. This task will be prompted for completion.
            </p>
            <a href="{{ route('dashboard.tools.grading') }}"
               class="px-4 w-full py-2 dashboard__cta text-white rounded hover:bg-blue-700">
                Open Tool
            </a>
        </div>

        <!-- Relevancy Checker Tool -->
        <div class="bg-white shadow-lg  rounded-lg p-6 flex flex-col items-start">
            <h2 class="text-xl font-semibold mb-2">Relevancy Checker Tool</h2>
            <p class="text-gray-600 mb-4">
                Check and update the relevancy of questions in bulk. This task will be prompted for completion.
            </p>
            <a href="{{ route('dashboard.tools.relevancy') }}"
               class="px-4 w-full py-2 dashboard__cta text-white rounded hover:bg-blue-700">
                Open Tool
            </a>
        </div>

        <!-- Category Balance Tool -->
        <div class="bg-white shadow-lg rounded-lg p-6 flex flex-col items-start">
            <h2 class="text-xl font-semibold mb-2">Category Balance Check</h2>
            <p class="text-gray-600 mb-4">
                Visualize the number of questions per category to spot gaps or oversaturation.
            </p>
            <a href="{{ route('dashboard.tools.category_balance') }}"
               class="px-4 w-full py-2 dashboard__cta text-white rounded hover:bg-blue-700">
                Open Tool
            </a>
        </div>

        {{--        <!-- Categorisation Tool -->--}}
        <div class="bg-white shadow-lg rounded-lg p-6 flex flex-col items-start">
            <h2 class="text-xl font-semibold mb-2">Recategorisation Tool</h2>
            <p class="text-gray-600 mb-4">
                Quickly move questions from one category to another with instant feedback.
            </p>
            <a href="{{ route('dashboard.tools.recategorisation') }}"
               class="px-4 w-full py-2 dashboard__cta text-white rounded hover:bg-blue-700">
                Open Tool
            </a>
        </div>

        <div class="bg-white shadow-lg rounded-lg p-6 flex flex-col items-start">
            <h2 class="text-xl font-semibold mb-2">Question Similarities Tool</h2>
            <p class="text-gray-600 mb-4">
                Review and resolve similar questions to keep the database clean.
            </p>
            <a href="{{ route('dashboard.tools.similarities') }}"
               class="px-4 w-full py-2 dashboard__cta text-white rounded hover:bg-blue-700">
                Open Tool
            </a>
        </div>



        {{--        <!-- Gamepack Batch Tool -->--}}
{{--        <div class="bg-white shadow-md rounded-lg p-6 flex flex-col items-start">--}}
{{--            <h2 class="text-xl font-semibold mb-2">Gamepack Batch Tool</h2>--}}
{{--            <p class="text-gray-600 mb-4">--}}
{{--                Manage large sets of questions inside specific gamepacks.--}}
{{--            </p>--}}
{{--            <a href="{{ route('dashboard.tools.gamepack-batch') }}"--}}
{{--               class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-700">--}}
{{--                Open Tool--}}
{{--            </a>--}}
{{--        </div>--}}
    </div>
@endsection
