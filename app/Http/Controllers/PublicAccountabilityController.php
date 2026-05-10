<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentFine;
use Illuminate\Http\Request;

class PublicAccountabilityController extends Controller
{
    public function index(Request $request)
    {
        $studentNumber = trim($request->query('student_number', ''));
        $student       = null;
        $fines         = collect();
        $notFound      = false;

        if ($studentNumber !== '') {
            $student = Student::where('student_number', $studentNumber)
                ->with('latestEnrollment.academicYear', 'latestEnrollment.program')
                ->first();

            if ($student) {
                $fines = StudentFine::where('student_id', $student->id)
                    ->with([
                        'event:id,name,date',
                        'organization:id,name',
                        'transaction:id,or_number',
                    ])
                    ->orderByDesc('created_at')
                    ->get();
            } else {
                $notFound = true;
            }
        }

        return view('public.check-fees', compact('studentNumber', 'student', 'fines', 'notFound'));
    }
}
