@extends('index')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
    <h3 class="mb-0 fw-bold">Notifications</h3>
    @if($notifications->whereNull('read_at')->count() > 0)
    <form action="/notifications/read-all" method="POST">
        @csrf
        <button class="btn btn-outline-light btn-sm">Mark All as Read</button>
    </form>
    @endif
</div>

<div class="glass-panel p-3">
    <div class="list-group">
        @forelse($notifications as $n)
        <div class="list-group-item bg-transparent border-secondary mb-2 {{ $n->read_at ? 'opacity-75' : 'border-start border-4 border-primary' }}">
            <div class="d-flex w-100 justify-content-between">
                <h5 class="mb-1 text-dark">{{ $n->title }}</h5>
                <small class="text-secondary">{{ $n->created_at->diffForHumans() }}</small>
            </div>
            <p class="mb-1 text-secondary">{{ $n->message }}</p>
            @if(!$n->read_at)
            <form action="/notifications/{{ $n->id }}/read" method="POST" class="mt-2">
                @csrf
                <button class="btn btn-sm btn-link p-0 text-primary text-decoration-none small">Mark as read</button>
            </form>
            @endif
        </div>
        @empty
        <div class="text-center py-4 text-secondary">No notifications found.</div>
        @endforelse
    </div>

    <div class="mt-3">
        {{ $notifications->links() }}
    </div>
</div>
@endsection
