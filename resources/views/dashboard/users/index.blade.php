@extends('dashboard')

@section('content')
    <h1 class="text-2xl font-bold mb-4">User Management</h1>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Admin</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
            @foreach($users as $user)
                <tr>
                    <td class="px-6 py-4 text-sm text-gray-700">{{ $user->id }}</td>
                    <td class="px-6 py-4 text-sm text-gray-700">{{ $user->name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-700">{{ $user->email }}</td>
                    <td class="px-6 py-4 text-sm">
                        @if($user->is_admin)
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Admin
                                </span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    User
                                </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-right">
                        <form action="{{ route('dashboard.users.toggle-admin', $user) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                    class="px-3 py-1 rounded text-white {{ $user->is_admin ? 'bg-red-600 hover:bg-red-700' : 'bg-blue-600 hover:bg-blue-700' }}">
                                {{ $user->is_admin ? 'Revoke Admin' : 'Make Admin' }}
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
@endsection
