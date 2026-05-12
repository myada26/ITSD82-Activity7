@extends('layouts.app')
@section('title', 'New Event')
@section('page-title', 'New Event')

@section('content')
<div>
    <div class="mb-4">
        <a href="{{ route('org.events.index') }}" class="inline-flex items-center gap-2 text-[13px] text-green-600 hover:text-green-700 font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Events
        </a>
    </div>

    <div class="max-w-xl">
        <div class="bg-white rounded-xl border border-green-200 shadow-sm">
            <div class="px-6 py-4 border-b border-green-100">
                <h2 class="text-[16px] font-bold text-green-800">Create Attendance Event</h2>
                <p class="text-[12px] text-green-400 mt-0.5">Set up a new event to track member attendance</p>
            </div>
            <div class="p-6">
                <form method="POST" action="{{ route('org.events.store') }}" x-data="{ timeType: '{{ old('time_type', 'FULL_DAY') }}' }">
                    @csrf

                    @if($errors->any())
                    <div class="mb-5 bg-red-50 border border-red-200 rounded-lg p-4">
                        <ul class="text-[13px] text-red-600 list-disc list-inside">
                            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                        </ul>
                    </div>
                    @endif

                    <div class="mb-5">
                        <label class="block text-[13px] font-semibold text-green-800 mb-2">Event Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            class="w-full px-4 py-2.5 border-2 {{ $errors->has('name') ? 'border-red-300' : 'border-green-200' }} rounded-lg text-[14px] font-medium text-green-800 outline-none focus:border-green-500 transition-colors"
                            placeholder="e.g. General Assembly">
                        @error('name')<p class="text-[12px] text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-5">
                        <div>
                            <label class="block text-[13px] font-semibold text-green-800 mb-2">Date <span class="text-red-500">*</span></label>
                            <input type="date" name="date" value="{{ old('date') }}" required
                                class="w-full px-4 py-2.5 border-2 {{ $errors->has('date') ? 'border-red-300' : 'border-green-200' }} rounded-lg text-[14px] font-medium text-green-800 outline-none focus:border-green-500 transition-colors">
                            @error('date')<p class="text-[12px] text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-[13px] font-semibold text-green-800 mb-2">Venue</label>
                            <input type="text" name="venue" value="{{ old('venue') }}"
                                class="w-full px-4 py-2.5 border-2 border-green-200 rounded-lg text-[14px] font-medium text-green-800 outline-none focus:border-green-500 transition-colors"
                                placeholder="e.g. Gymnasium">
                        </div>
                    </div>

                    <div class="mb-5">
                        <label class="block text-[13px] font-semibold text-green-800 mb-3">Event Duration <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="flex items-start gap-3 p-4 rounded-lg border-2 cursor-pointer transition-all"
                                :class="timeType==='FULL_DAY' ? 'border-green-500 bg-green-50' : 'border-green-200 bg-white hover:border-green-300'">
                                <input type="radio" name="time_type" value="FULL_DAY" x-model="timeType" {{ old('time_type', 'FULL_DAY') === 'FULL_DAY' ? 'checked' : '' }} class="mt-0.5 accent-green-600">
                                <div>
                                    <div class="text-[14px] font-bold text-green-800">Full Day</div>
                                    <div class="text-[11px] text-green-400 mt-0.5">4 time slots: AM In/Out, PM In/Out</div>
                                </div>
                            </label>
                            <label class="flex items-start gap-3 p-4 rounded-lg border-2 cursor-pointer transition-all"
                                :class="timeType==='HALF_DAY' ? 'border-green-500 bg-green-50' : 'border-green-200 bg-white hover:border-green-300'">
                                <input type="radio" name="time_type" value="HALF_DAY" x-model="timeType" {{ old('time_type') === 'HALF_DAY' ? 'checked' : '' }} class="mt-0.5 accent-green-600">
                                <div>
                                    <div class="text-[14px] font-bold text-green-800">Half Day</div>
                                    <div class="text-[11px] text-green-400 mt-0.5">2 time slots: In + Out</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-[13px] font-semibold text-green-800 mb-2">Start Time</label>
                            <input type="time" name="start_time" value="{{ old('start_time') }}"
                                class="w-full px-4 py-2.5 border-2 border-green-200 rounded-lg text-[14px] font-medium text-green-800 outline-none focus:border-green-500 transition-colors">
                        </div>
                        <div>
                            <label class="block text-[13px] font-semibold text-green-800 mb-2">End Time</label>
                            <input type="time" name="end_time" value="{{ old('end_time') }}"
                                class="w-full px-4 py-2.5 border-2 border-green-200 rounded-lg text-[14px] font-medium text-green-800 outline-none focus:border-green-500 transition-colors">
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <a href="{{ route('org.events.index') }}" class="px-5 py-2.5 rounded-lg text-[13px] font-bold border-2 border-green-200 text-green-600 hover:border-green-400 hover:text-green-700 transition-all">
                            Cancel
                        </a>
                        <button type="submit" class="px-5 py-2.5 rounded-lg text-[13px] font-bold bg-green-600 text-white hover:bg-green-500 border-2 border-transparent transition-all shadow-sm">
                            Create Event
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection