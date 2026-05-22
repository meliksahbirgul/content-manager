@extends('layouts.header')

@section('tab-title', __('panel/dashboard.title'))
@section('page-title', __('panel/dashboard.dashboard'))

@section('content')

    <!-- Page Status Counts -->
    <div class="py-4">
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
            @foreach ($dashboard->pageStatusCounts() as $statusCount)
                <div class="bg-white border border-orange-200 rounded-xl p-4 text-center">
                    <p class="text-2xl font-bold text-orange-600">{{ $statusCount->count() }}</p>
                    <p class="text-sm text-gray-500 mt-1 capitalize">{{ $statusCount->status() }}</p>
                </div>
            @endforeach
        </div>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-orange-200 p-2">
        <div class="p-4">
            <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b border-solid border-orange-200">
                {{ __('panel/dashboard.recent_activities') }}
            </h3>
            <ul class="divide-y divide-orange-100">
                @forelse($dashboard->recentActivityLogs() as $log)
                    <li class="py-3 flex justify-between items-start gap-4">
                        <div>
                            <p class="text-sm text-gray-800">{{ $log->description() }}</p>
                            @if ($log->event())
                                <span
                                    class="inline-block mt-1 text-xs bg-orange-100 text-orange-700 rounded px-2 py-0.5">{{ $log->event() }}</span>
                            @endif
                        </div>
                        <span
                            class="text-xs text-gray-400 whitespace-nowrap">{{ $log->createdAt()->format('d.m.Y H:i') }}</span>
                    </li>
                @empty
                    <li class="py-3 text-sm text-gray-400">{{ __('panel/dashboard.no_activity') }}</li>
                @endforelse
            </ul>
        </div>
    </div>
    <!-- Recent Activity Logs -->
@endsection
