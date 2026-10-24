@php
    $announcements = \App\Models\Announcement::live()->get();
@endphp
@if ($announcements->isNotEmpty())
    <div class="pq-announcements" id="pq-announcements" role="alert" aria-live="polite">
        @foreach ($announcements as $ann)
            <div class="pq-announcement pq-announcement--{{ $ann->type ?? 'info' }}">
                <span class="pq-announcement-msg">{{ $ann->message }}</span>
            </div>
        @endforeach
    </div>
@endif
