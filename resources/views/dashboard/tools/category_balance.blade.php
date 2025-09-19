@extends('dashboard')

@section('content')
<h1 class="text-2xl font-bold mb-4">Category Balance</h1>

@if($categories->isEmpty())
<div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
    No categories found.
</div>
@else
<div class="bg-white p-6 rounded-lg shadow mb-6">
    <canvas id="categoryChart" class="w-full h-96"></canvas>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($categories as $category)
    <div class="p-4 border rounded-lg shadow-sm flex flex-col justify-between">
        <div>
            <h2 class="text-lg font-semibold">{{ $category->name }}</h2>
            <p class="text-sm text-gray-600">{{ $category->description }}</p>
        </div>
        <div class="mt-2">
                        <span class="inline-block px-3 py-1 text-sm rounded-full"
                              style="background-color: {{ $category->color ?? '#ddd' }}; color: white;">
                            {{ $category->questions_count }} questions
                        </span>
        </div>
    </div>
    @endforeach
</div>
@endif

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('categoryChart').getContext('2d');
    const categoryChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($categories->pluck('name')),
            datasets: [{
                label: 'Number of Questions',
                data: @json($categories->pluck('questions_count')),
                backgroundColor: @json($categories->pluck('color')->map(fn($c) => $c ?? '#4B9CD3')),
                borderColor: '#333',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {display: false},
                title: {
                    display: true,
                    text: 'Questions per Category'
                }
            },
            scales: {
                y: {beginAtZero: true}
            }
        }
    });
</script>
@endsection
