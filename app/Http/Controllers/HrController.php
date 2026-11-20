<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ChartOfAccount;
use App\Models\Department;
use App\Models\Employee;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\LeaveRequest;
use App\Models\PayrollRun;
use App\Models\Payslip;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HrController extends Controller
{
    public function index()
    {
        $today = today();
        $staff_count = Employee::where('status', 'active')->count();
        $on_duty_today = Attendance::whereDate('date', $today)
            ->whereIn('status', ['present', 'late', 'half_day'])->count();
        $pending_leave = LeaveRequest::where('status', 'pending')->count();
        $latest_payroll = PayrollRun::latest()->first();

        return view('hr.index', compact('staff_count', 'on_duty_today', 'pending_leave', 'latest_payroll'));
    }

    public function employees()
    {
        $employees = Employee::with('department')->latest()->paginate(20);

        return view('hr.employees', compact('employees'));
    }

    public function createEmployee()
    {
        $departments = Department::orderBy('name')->get();

        return view('hr.create-employee', compact('departments'));
    }

    public function storeEmployee(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'nullable|email|max:200',
            'phone' => 'nullable|string|max:30',
            'national_id' => 'nullable|string|max:50',
            'napsa_number' => 'nullable|string|max:50',
            'nhima_number' => 'nullable|string|max:50',
            'tpin' => 'nullable|string|max:50',
            'department_id' => 'required|exists:departments,id',
            'employment_type' => 'required|in:full_time,part_time,contract,intern',
            'job_title' => 'required|string|max:100',
            'vertical' => 'nullable|in:bags,furniture,interiors,admin,shared',
            'basic_salary' => 'required|numeric|min:0',
            'salary_type' => 'required|in:monthly,daily,piece_rate',
            'hire_date' => 'required|date',
            'status' => 'required|in:active,on_leave,suspended,terminated',
            'address' => 'nullable|string|max:500',
            'emergency_contact' => 'nullable|string|max:200',
            'bank_name' => 'nullable|string|max:100',
            'bank_account' => 'nullable|string|max:50',
            'mobile_money_number' => 'nullable|string|max:30',
        ]);

        $data['employee_number'] = $this->generateEmployeeNumber();
        Employee::create($data);

        return redirect()->route('hr.employees')->with('success', 'Employee added.');
    }

    public function showEmployee(Employee $employee)
    {
        $employee->load(['department', 'attendance' => fn ($q) => $q->latest()->limit(20), 'leaveRequests' => fn ($q) => $q->latest()->limit(10)]);
        $departments = Department::orderBy('name')->get();

        return view('hr.show-employee', compact('employee', 'departments'));
    }

    public function updateEmployee(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'nullable|email|max:200',
            'phone' => 'nullable|string|max:30',
            'department_id' => 'required|exists:departments,id',
            'employment_type' => 'required|in:full_time,part_time,contract,intern',
            'job_title' => 'required|string|max:100',
            'vertical' => 'nullable|in:bags,furniture,interiors,admin,shared',
            'basic_salary' => 'required|numeric|min:0',
            'salary_type' => 'required|in:monthly,daily,piece_rate',
            'hire_date' => 'required|date',
            'status' => 'required|in:active,on_leave,suspended,terminated',
            'address' => 'nullable|string|max:500',
            'emergency_contact' => 'nullable|string|max:200',
            'bank_name' => 'nullable|string|max:100',
            'bank_account' => 'nullable|string|max:50',
            'mobile_money_number' => 'nullable|string|max:30',
        ]);

        $employee->update($data);

        return redirect()->route('hr.employees.show', $employee)->with('success', 'Employee updated.');
    }

    public function attendance()
    {
        $records = Attendance::with('employee')->latest()->paginate(30);
        $employees = Employee::where('status', 'active')->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'employee_number']);

        return view('hr.attendance', compact('records', 'employees'));
    }

    public function checkIn(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'check_in' => 'required|date_format:H:i',
            'status' => 'required|in:present,late,half_day',
            'notes' => 'nullable|string',
        ]);

        Attendance::updateOrCreate(
            ['employee_id' => $request->employee_id, 'date' => $request->date],
            [
                'check_in' => $request->check_in,
                'status' => $request->status,
                'notes' => $request->notes,
                'recorded_by' => Auth::id(),
            ]
        );

        return back()->with('success', 'Attendance recorded.');
    }

    public function checkOut(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'check_out' => 'required|date_format:H:i',
        ]);

        $record = Attendance::where('employee_id', $request->employee_id)
            ->where('date', $request->date)->first();

        if ($record) {
            $checkIn = Carbon::parse($record->check_in);
            $checkOut = Carbon::parse($request->check_out);
            $hours = round($checkIn->floatDiffInHours($checkOut), 2);

            $record->update(['check_out' => $request->check_out, 'hours_worked' => $hours]);
        }

        return back()->with('success', 'Check-out recorded.');
    }

    public function leave()
    {
        $requests = LeaveRequest::with('employee')->latest()->paginate(20);
        $employees = Employee::where('status', 'active')->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'employee_number']);

        return view('hr.leave', compact('requests', 'employees'));
    }

    public function applyLeave(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'type' => 'required|in:annual,sick,maternity,paternity,unpaid,emergency',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string',
        ]);

        $days = Carbon::parse($request->start_date)->diffInDays($request->end_date) + 1;

        LeaveRequest::create([
            'employee_id' => $request->employee_id,
            'type' => $request->type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'days_requested' => $days,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Leave request submitted.');
    }

    public function approveLeave(Request $request, LeaveRequest $leave)
    {
        $request->validate([
            'action' => 'required|in:approved,rejected',
            'rejection_reason' => 'nullable|string',
        ]);

        $leave->update([
            'status' => $request->action,
            'approved_by' => Auth::id(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        if ($request->action === 'approved') {
            Employee::where('id', $leave->employee_id)->update(['status' => 'on_leave']);
        }

        return back()->with('success', 'Leave '.$request->action.'.');
    }

    public function payroll()
    {
        $runs = PayrollRun::latest()->paginate(12);

        return view('hr.payroll', compact('runs'));
    }

    public function runPayroll(Request $request)
    {
        $request->validate([
            'period' => 'required|string|regex:/^\d{4}-\d{2}$/',
            'pay_date' => 'required|date',
        ]);

        if (PayrollRun::where('period', $request->period)->exists()) {
            return back()->withErrors(['period' => 'Payroll for this period already exists.']);
        }

        DB::transaction(function () use ($request) {
            $employees = Employee::where('status', 'active')->where('salary_type', 'monthly')->get();

            $totals = ['gross' => 0, 'paye' => 0, 'napsa' => 0, 'nhima' => 0, 'net' => 0];

            $run = PayrollRun::create([
                'period' => $request->period,
                'pay_date' => $request->pay_date,
                'status' => 'draft',
                'total_gross' => 0, 'total_paye' => 0, 'total_napsa' => 0, 'total_nhima' => 0, 'total_net' => 0,
                'processed_by' => Auth::id(),
            ]);

            foreach ($employees as $emp) {
                $gross = (float) $emp->basic_salary;
                $napsa = round($gross * 0.05, 2);      // 5% employee NAPSA
                $nhima = round($gross * 0.01, 2);      // 1% NHIMA
                $taxable = $gross - $napsa;
                $paye = $this->computePaye($taxable);
                $net = $gross - $paye - $napsa - $nhima;

                Payslip::create([
                    'payroll_run_id' => $run->id,
                    'employee_id' => $emp->id,
                    'basic_salary' => $gross,
                    'allowances' => 0,
                    'gross_pay' => $gross,
                    'paye_tax' => $paye,
                    'napsa_employee' => $napsa,
                    'napsa_employer' => round($gross * 0.05, 2),
                    'nhima_employee' => $nhima,
                    'nhima_employer' => round($gross * 0.01, 2),
                    'other_deductions' => 0,
                    'net_pay' => max(0, $net),
                    'days_worked' => 22,
                    'days_absent' => 0,
                ]);

                $totals['gross'] += $gross;
                $totals['paye'] += $paye;
                $totals['napsa'] += $napsa;
                $totals['nhima'] += $nhima;
                $totals['net'] += max(0, $net);
            }

            $run->update([
                'total_gross' => $totals['gross'],
                'total_paye' => $totals['paye'],
                'total_napsa' => $totals['napsa'],
                'total_nhima' => $totals['nhima'],
                'total_net' => $totals['net'],
                'status' => 'approved',
            ]);

            try {
                $this->postPayrollJournal($run, $totals);
            } catch (\Throwable $e) {
                report($e);
            }
        });

        return back()->with('success', 'Payroll run completed for '.$request->period.'.');
    }

    public function payslips(string $month)
    {
        $run = PayrollRun::where('period', $month)->firstOrFail();
        $slips = Payslip::with('employee')->where('payroll_run_id', $run->id)->get();

        return view('hr.payslips', compact('run', 'slips'));
    }

    public function departments()
    {
        $departments = Department::withCount('employees')->get();

        return view('hr.departments', compact('departments'));
    }

    public function storeDepartment(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100|unique:departments,name']);
        Department::create(['name' => $request->name]);

        return back()->with('success', 'Department added.');
    }

    public function searchStaff(Request $request)
    {
        $q = $request->get('q', '');
        $staff = Employee::where(function ($query) use ($q) {
            $query->where('first_name', 'like', "%{$q}%")
                ->orWhere('last_name', 'like', "%{$q}%")
                ->orWhere('employee_number', 'like', "%{$q}%");
        })->limit(10)->get(['id', 'first_name', 'last_name', 'employee_number', 'job_title']);

        return response()->json($staff->map(fn ($e) => [
            'id' => $e->id,
            'name' => $e->first_name.' '.$e->last_name,
            'number' => $e->employee_number,
            'title' => $e->job_title,
        ]));
    }

    private function postPayrollJournal(PayrollRun $run, array $totals): void
    {
        $codes = ['1000', '2110', '2120', '2130', '5100'];
        $accounts = ChartOfAccount::whereIn('code', $codes)->where('is_active', true)->get()->keyBy('code');
        foreach ($codes as $code) {
            if (! $accounts->has($code)) {
                return;
            }
        }

        $net = round($totals['net'], 2);
        $paye = round($totals['paye'], 2);
        $napsa = round($totals['napsa'], 2);
        $nhima = round($totals['nhima'], 2);
        $gross = round($net + $paye + $napsa + $nhima, 2);

        DB::transaction(function () use ($run, $accounts, $gross, $net, $paye, $napsa, $nhima) {
            $entry = JournalEntry::create([
                'reference' => 'JNL-PAY-'.$run->period.'-'.$run->id,
                'user_id' => Auth::id(),
                'entry_date' => $run->pay_date,
                'description' => 'Payroll for '.$run->period,
                'source' => 'payroll',
                'source_id' => $run->id,
            ]);

            $lines = [
                ['5100', $gross, 0, 'Gross salaries and wages'],
                ['1000', 0, $net, 'Net pay to staff'],
                ['2110', 0, $paye, 'PAYE payable'],
                ['2120', 0, $napsa, 'NAPSA payable'],
                ['2130', 0, $nhima, 'NHIMA payable'],
            ];

            foreach ($lines as [$code, $debit, $credit, $description]) {
                JournalLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $accounts[$code]->id,
                    'debit' => $debit,
                    'credit' => $credit,
                    'description' => $description,
                ]);
            }
        });
    }

    private function generateEmployeeNumber(): string
    {
        $last = Employee::orderByDesc('id')->value('employee_number');
        $seq = $last ? ((int) preg_replace('/\D/', '', $last)) + 1 : 1;

        return 'MPS-'.str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    private function computePaye(float $monthly): float
    {
        // Zambia PAYE bands (monthly) — simplified
        $annual = $monthly * 12;
        $paye = 0;
        if ($annual <= 57600) {
            $paye = 0;
        } elseif ($annual <= 69600) {
            $paye = ($annual - 57600) * 0.25;
        } elseif ($annual <= 110400) {
            $paye = 3000 + ($annual - 69600) * 0.30;
        } else {
            $paye = 15240 + ($annual - 110400) * 0.37;
        }

        return round($paye / 12, 2);
    }
}
