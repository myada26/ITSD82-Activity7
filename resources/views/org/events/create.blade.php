@extends('layouts.app')
@section('title', 'New Event')
@section('page-title', 'New Event')

@section('content')
<div style="max-width:580px">
    <div style="margin-bottom:16px">
        <a href="{{ route('org.events.index') }}" style="display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#4a6356;text-decoration:none">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            Back to Events
        </a>
    </div>

    <div class="card" style="padding:24px 28px">
        <div style="font-size:16px;font-weight:700;color:#0f1f17;margin-bottom:20px">Create Attendance Event</div>

        @if($errors->any())
        <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:8px;padding:10px 14px;margin-bottom:16px;font-size:13px;color:#dc2626">
            <ul style="margin:0;padding-left:16px">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('org.events.store') }}" x-data="{ timeType: '{{ old('time_type', 'FULL_DAY') }}' }">
            @csrf

            {{-- Event Name --}}
            <div style="margin-bottom:16px">
                <label style="display:block;font-size:12.5px;font-weight:600;color:#374151;margin-bottom:5px">Event Name <span style="color:#dc2626">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required
                    style="width:100%;padding:8px 12px;border:1px solid #dde8e1;border-radius:8px;font-size:13.5px;outline:none;box-sizing:border-box"
                    placeholder="e.g. General Assembly" />
            </div>

            {{-- Date --}}
            <div style="margin-bottom:16px">
                <label style="display:block;font-size:12.5px;font-weight:600;color:#374151;margin-bottom:5px">Date <span style="color:#dc2626">*</span></label>
                <input type="date" name="date" value="{{ old('date') }}" required
                    style="width:100%;padding:8px 12px;border:1px solid #dde8e1;border-radius:8px;font-size:13.5px;outline:none;box-sizing:border-box" />
            </div>

            {{-- Venue --}}
            <div style="margin-bottom:16px">
                <label style="display:block;font-size:12.5px;font-weight:600;color:#374151;margin-bottom:5px">Venue</label>
                <input type="text" name="venue" value="{{ old('venue') }}"
                    style="width:100%;padding:8px 12px;border:1px solid #dde8e1;border-radius:8px;font-size:13.5px;outline:none;box-sizing:border-box"
                    placeholder="e.g. Gymnasium, AVR" />
            </div>

            {{-- Time Type --}}
            <div style="margin-bottom:16px">
                <label style="display:block;font-size:12.5px;font-weight:600;color:#374151;margin-bottom:8px">Event Duration <span style="color:#dc2626">*</span></label>
                <div style="display:flex;gap:12px">
                    <label style="display:flex;align-items:center;gap:8px;padding:10px 14px;border-radius:8px;cursor:pointer;flex:1;border:2px solid"
                        :style="timeType==='FULL_DAY' ? 'border-color:#1a7a41;background:#f0fdf4' : 'border-color:#dde8e1;background:white'">
                        <input type="radio" name="time_type" value="FULL_DAY" x-model="timeType" {{ old('time_type', 'FULL_DAY') === 'FULL_DAY' ? 'checked' : '' }} style="accent-color:#1a7a41" />
                        <div>
                            <div style="font-size:13px;font-weight:600;color:#0f1f17">Full Day</div>
                            <div style="font-size:11px;color:#8aa89a">4 slots: AM In, AM Out, PM In, PM Out</div>
                        </div>
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;padding:10px 14px;border-radius:8px;cursor:pointer;flex:1;border:2px solid"
                        :style="timeType==='HALF_DAY' ? 'border-color:#1a7a41;background:#f0fdf4' : 'border-color:#dde8e1;background:white'">
                        <input type="radio" name="time_type" value="HALF_DAY" x-model="timeType" {{ old('time_type') === 'HALF_DAY' ? 'checked' : '' }} style="accent-color:#1a7a41" />
                        <div>
                            <div style="font-size:13px;font-weight:600;color:#0f1f17">Half Day</div>
                            <div style="font-size:11px;color:#8aa89a">2 slots: In + Out</div>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Times --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:24px">
                <div>
                    <label style="display:block;font-size:12.5px;font-weight:600;color:#374151;margin-bottom:5px">Start Time</label>
                    <input type="time" name="start_time" value="{{ old('start_time') }}"
                        style="width:100%;padding:8px 12px;border:1px solid #dde8e1;border-radius:8px;font-size:13.5px;outline:none;box-sizing:border-box" />
                </div>
                <div>
                    <label style="display:block;font-size:12.5px;font-weight:600;color:#374151;margin-bottom:5px">End Time</label>
                    <input type="time" name="end_time" value="{{ old('end_time') }}"
                        style="width:100%;padding:8px 12px;border:1px solid #dde8e1;border-radius:8px;font-size:13.5px;outline:none;box-sizing:border-box" />
                </div>
            </div>

            <div style="display:flex;gap:10px;justify-content:flex-end">
                <a href="{{ route('org.events.index') }}" style="padding:9px 18px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;border:1.5px solid #dde8e1;color:#4a6356">Cancel</a>
                <button type="submit" style="padding:9px 20px;border-radius:8px;font-size:13px;font-weight:700;border:none;cursor:pointer;background:#1a7a41;color:white">
                    Create Event &amp; Pre-fill Sheet
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
