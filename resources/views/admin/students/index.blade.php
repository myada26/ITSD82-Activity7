@extends('layouts.app')
@section('title', 'Enrolled Students')
@section('page-title', 'Enrolled Students')

@section('content')
<div class="max-w-6xl mx-auto pb-10" x-data="{ open: false }">

    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-6">
        <div>
            <h2 class="text-[22px] font-bold text-green-800">Enrolled Students</h2>
            <p class="text-[13.5px] text-green-400 mt-1 font-medium">SSC-managed master student list · Active Semester: {{ $activeSemester?->name ?? 'N/A' }}</p>
        </div>
        <div class="flex flex-wrap gap-2.5">
            <a href="{{ route('admin.students.index', ['export' => 'csv']) }}" class="px-4 py-2 rounded-lg text-[13.5px] font-bold flex items-center gap-2 bg-white border-2 border-green-200 hover:border-green-600 hover:text-green-600 text-green-400 transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>Export Roster
            </a>
            <button @click="open = 'bulk'" class="px-4 py-2 rounded-lg text-[13.5px] font-bold flex items-center gap-2 bg-white border-2 border-green-200 hover:border-green-600 hover:text-green-600 text-green-400 transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>Bulk Import
            </button>
            <button @click="open = 'manual'" class="px-4 py-2 rounded-lg text-[13.5px] font-bold flex items-center gap-2 bg-green-600 hover:bg-green-500 text-white border-2 border-transparent transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14M5 12h14"/></svg>Add Student
            </button>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-green-200 shadow-sm overflow-hidden">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 px-6 py-5 border-b border-[#eaf0ec]">
            <div>
                <h3 class="text-[15px] font-bold text-green-800">All Students</h3>
                <p class="text-[12.5px] text-green-300 font-medium mt-0.5">{{ $students->total() }} total records</p>
            </div>
            <form method="GET" action="{{ route('admin.students.index') }}" class="flex flex-wrap items-center gap-2.5">
                <div class="relative w-full md:w-[240px]">
                    <svg class="w-4 h-4 text-green-300 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input name="search" value="{{ request('search') }}" type="text" placeholder="Search by name or ID..."
                           class="w-full pl-9 pr-3 py-2 border-2 border-green-200 rounded-lg text-[13px] font-medium text-green-800 outline-none focus:border-green-600 transition-colors">
                </div>
                <button type="submit" class="px-3 py-2 border-2 border-green-200 rounded-lg text-green-400 hover:border-green-600 hover:text-green-600 transition-colors flex items-center gap-2 text-[13px] font-bold">Search</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[700px]">
                <thead>
                    <tr class="bg-green-50 border-b border-green-200">
                        <th class="px-6 py-3.5 text-[11.5px] font-bold text-green-300 uppercase tracking-widest">Student ID</th>
                        <th class="px-6 py-3.5 text-[11.5px] font-bold text-green-300 uppercase tracking-widest">Name</th>
                        <th class="px-6 py-3.5 text-[11.5px] font-bold text-green-300 uppercase tracking-widest">Program</th>
                        <th class="px-6 py-3.5 text-[11.5px] font-bold text-green-300 uppercase tracking-widest">College</th>
                        <th class="px-6 py-3.5 text-[11.5px] font-bold text-green-300 uppercase tracking-widest">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                    <tr class="border-b border-[#eaf0ec] hover:bg-[#f0f3f1]/50 transition-colors last:border-b-0">
                        <td class="px-6 py-4"><span class="font-mono text-[13px] font-bold text-green-600 bg-green-100 px-2 py-1 rounded-md">{{ $student->student_number }}</span></td>
                        <td class="px-6 py-4 text-[14px] font-bold text-green-800">{{ $student->full_name }}</td>
                        <td class="px-6 py-4 text-[13.5px] font-semibold text-green-400">{{ $student->latestEnrollment?->program?->code ?? '—' }}</td>
                        <td class="px-6 py-4 text-[13.5px] font-semibold text-green-400">{{ $student->latestEnrollment?->program?->department?->college?->code ?? '—' }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-[#dcfce7] text-[#15803d] text-[11.5px] font-bold">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#16a34a]"></span> Enrolled
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-6 py-14 text-center text-[14px] font-semibold text-green-400">No students found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-[#eaf0ec] flex justify-between items-center bg-green-50">
            <span class="text-[12.5px] font-medium text-green-300">Showing {{ $students->firstItem() ?? 0 }}–{{ $students->lastItem() ?? 0 }} of {{ $students->total() }}</span>
            {{ $students->withQueryString()->links() }}
        </div>
    </div>

    {{-- Enroll Student Modal --}}
    <div x-show="open === 'manual'" x-cloak class="fixed inset-0 z-40 flex items-center justify-center p-4 sm:p-6">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-[2px]" @click="open = false"></div>
        <div class="relative bg-white rounded-xl w-full max-w-2xl shadow-2xl z-10 flex flex-col max-h-[90vh]">
            <div class="flex items-center justify-between px-6 py-4 border-b border-[#eaf0ec] shrink-0">
                <div><h2 class="text-[18px] font-bold text-green-800">Enroll New Student</h2><p class="text-[13px] text-green-400 mt-0.5 font-medium">Add a student to the active semester.</p></div>
                <button @click="open = false" class="text-green-300 hover:bg-[#f0f3f1] p-2 rounded-lg transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form method="POST" action="{{ route('admin.students.store') }}" class="flex flex-col min-h-0">
                @csrf
                <div class="p-6 overflow-y-auto space-y-6">
                    <div>
                        <h3 class="text-[14px] font-bold text-green-600 mb-4 pb-2 border-b border-green-200">Student Identity</h3>
                        <div class="mb-4">
                            <label class="block text-[13px] font-semibold text-green-400 mb-2">Student Number <span class="text-red-500">*</span></label>
                            <input type="text" name="student_number" value="{{ old('student_number') }}" placeholder="e.g. 2024-0001" class="w-full px-4 py-2.5 border-2 border-green-200 rounded-lg bg-green-50 text-[14px] font-bold font-mono text-green-800 outline-none focus:border-green-600 transition-colors">
                        </div>
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-[13px] font-semibold text-green-400 mb-2">First Name <span class="text-red-500">*</span></label>
                                <input type="text" name="first_name" value="{{ old('first_name') }}" placeholder="e.g. Juan" class="w-full px-4 py-2.5 border-2 border-green-200 rounded-lg bg-white text-[14px] font-medium text-green-800 outline-none focus:border-green-600 transition-colors">
                            </div>
                            <div>
                                <label class="block text-[13px] font-semibold text-green-400 mb-2">Last Name <span class="text-red-500">*</span></label>
                                <input type="text" name="last_name" value="{{ old('last_name') }}" placeholder="e.g. Dela Cruz" class="w-full px-4 py-2.5 border-2 border-green-200 rounded-lg bg-white text-[14px] font-medium text-green-800 outline-none focus:border-green-600 transition-colors">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[13px] font-semibold text-green-400 mb-2">Middle Name <span class="text-[11px] font-normal text-green-300 ml-1">(Optional)</span></label>
                            <input type="text" name="middle_name" value="{{ old('middle_name') }}" placeholder="e.g. Santos" class="w-full px-4 py-2.5 border-2 border-green-200 rounded-lg bg-white text-[14px] font-medium text-green-800 outline-none focus:border-green-600 transition-colors">
                        </div>
                    </div>
                    <div>
                        <h3 class="text-[14px] font-bold text-green-600 mb-4 pb-2 border-b border-green-200">Enrollment Details</h3>
                        <div class="mb-4">
                            <label class="block text-[13px] font-semibold text-green-400 mb-2">Active Semester</label>
                            <input type="text" value="{{ $activeSemester?->name ?? 'No active semester' }}" readonly class="w-full px-4 py-2.5 border-2 border-[#eaf0ec] rounded-lg bg-[#f0f3f1] text-[14px] font-semibold text-green-400 outline-none cursor-not-allowed">
                        </div>
                        <div class="mb-4">
                            <label class="block text-[13px] font-semibold text-green-400 mb-2">Program <span class="text-red-500">*</span></label>
                            <select name="program_id" class="w-full px-4 py-2.5 border-2 border-green-200 rounded-lg bg-white text-[14px] font-medium text-green-800 outline-none focus:border-green-600 transition-colors cursor-pointer appearance-none">
                                <option value="">— Select program —</option>
                                @foreach($programs as $p)<option value="{{ $p->id }}" {{ old('program_id') == $p->id ? 'selected' : '' }}>{{ $p->name }} ({{ $p->code }})</option>@endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[13px] font-semibold text-green-400 mb-2">Year Level <span class="text-red-500">*</span></label>
                                <select name="year_level" class="w-full px-4 py-2.5 border-2 border-green-200 rounded-lg bg-white text-[14px] font-medium text-green-800 outline-none focus:border-green-600 transition-colors cursor-pointer appearance-none">
                                    @foreach(['1st Year','2nd Year','3rd Year','4th Year','5th Year'] as $yr)<option>{{ $yr }}</option>@endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-[13px] font-semibold text-green-400 mb-2">Student Type <span class="text-red-500">*</span></label>
                                <select name="student_type" class="w-full px-4 py-2.5 border-2 border-green-200 rounded-lg bg-white text-[14px] font-medium text-green-800 outline-none focus:border-green-600 transition-colors cursor-pointer appearance-none">
                                    <option>Regular</option><option>Irregular</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-[#eaf0ec] bg-green-50 flex justify-end gap-3 shrink-0 rounded-b-2xl">
                    <button type="button" @click="open = false" class="px-4 py-2 rounded-lg text-[13.5px] font-bold bg-white border-2 border-green-200 hover:border-green-600 hover:text-green-600 text-green-400 transition-all">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-lg text-[13.5px] font-bold bg-green-600 hover:bg-green-500 text-white border-2 border-transparent transition-all shadow-sm">Save Record</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
