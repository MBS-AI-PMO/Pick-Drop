@extends('layout.master')

@section('content')

<div class="card">

    <div class="card-header">
        <h4>Notifications</h4>
    </div>

    <div class="card-body">

        <table class="table table-bordered">

            <thead>

                <tr>

                    <th>Title</th>

                    <th>Message</th>

                    <th>Type</th>

                    <th>Status</th>

                    <th>Date</th>

                </tr>

            </thead>

            <tbody>

                @forelse($notifications as $notification)

                <tr>

                    <td>{{ $notification->title }}</td>

                    <td>{{ $notification->message }}</td>

                    <td>{{ ucfirst($notification->type) }}</td>

                    <td>

                        @if($notification->is_read)

                            Read

                        @else

                            Unread

                        @endif

                    </td>

                    <td>{{ $notification->created_at->format('d M Y h:i A') }}</td>

                </tr>

                @empty

                <tr>

                    <td colspan="5" class="text-center">

                        No Notifications Found

                    </td>

                </tr>

                @endforelse

            </tbody>

        </table>

        {{ $notifications->links() }}

    </div>

</div>

@endsection