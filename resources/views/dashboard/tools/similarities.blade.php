@extends('dashboard')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Question Similarities</h1>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if($similarities->isEmpty())
        <p class="text-gray-600">No unhandled similarities found 🎉</p>
    @else
        <table class="min-w-full bg-white border rounded shadow">
            <thead>
            <tr>
                <th class="px-4 py-2 border-b">Question</th>
                <th class="px-4 py-2 border-b">Similar Question</th>
                <th class="px-4 py-2 border-b">Score</th>
                <th class="px-4 py-2 border-b">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($similarities as $similarity)
                <tr>
                    <td class="px-4 py-2 border-b">{{ $similarity->question->vraag }}</td>
                    <td class="px-4 py-2 border-b">{{ $similarity->similarQuestion->vraag }}</td>
                    <td class="px-4 py-2 border-b">{{ number_format($similarity->similarity_score * 100, 2) }}%</td>
                    <td class="px-4 py-2 border-b">
                        <form action="{{ route('dashboard.tools.similarities.handle', $similarity->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-700">
                                Mark Handled
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="mt-4">
            {{ $similarities->links() }}
        </div>
    @endif
@endsection
