<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\SalaryDistribution;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SalaryDistributionController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->query('month')
            ? Carbon::parse($request->query('month') . '-01')
            : Carbon::now()->startOfMonth();

        $from = $month->copy()->startOfMonth();
        $to = $month->copy()->endOfMonth();

        $query = SalaryDistribution::with('employee')
            ->whereBetween('period_start', [$from, $to]);

        if ($employeeId = $request->query('employee_id')) {
            $query->where('employee_id', $employeeId);
        }

        $rows = $query->orderBy('employee_id')->paginate(20)->withQueryString();

        $summaryQuery = clone $query;
        $summaryRows = $summaryQuery->get([
            'employee_id',
            'base_salary',
            'bonus',
            'ta_allowances',
            'da_allowances',
            'commission',
            'document_path',
            'payment_method',
        ]);

        $total = $summaryRows->sum(function ($row) {
            return (float) $row->base_salary
                + (float) $row->bonus
                + (float) $row->ta_allowances
                + (float) $row->da_allowances
                + (float) $row->commission;
        });

        $employeeCount = $summaryRows->pluck('employee_id')->filter()->unique()->count();
        $averageDistribution = $employeeCount > 0 ? $total / $employeeCount : 0;
        $withDocuments = $summaryRows->filter(fn ($row) => filled($row->document_path))->count();
        $bankTransfers = $summaryRows->filter(fn ($row) => ($row->payment_method ?? 'bank') === 'bank')->count();

        $employees = Employee::orderBy('name')->get();
        $selectedEmployeeName = $employeeId
            ? $employees->firstWhere('id', (int) $employeeId)?->name
            : null;

        return view('admin.finance.salary_distributions.index', compact(
            'rows',
            'month',
            'total',
            'employees',
            'employeeCount',
            'averageDistribution',
            'withDocuments',
            'bankTransfers',
            'selectedEmployeeName'
        ));
    }

    public function create()
    {
        $employees = Employee::orderBy('name')->get();
        $distribution = new SalaryDistribution([
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth(),
        ]);

        return view('admin.finance.salary_distributions.create', compact('employees', 'distribution'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $distribution = null;

        DB::transaction(function () use ($data, &$distribution) {
            $distribution = $this->persistDistribution($data);
        });

        if ($request->hasFile('document')) {
            $path = $request->file('document')->store('salary-distributions', 'public');
            $distribution->update(['document_path' => $path]);
        }

        return redirect()->route('admin.salary-distributions.index')->with('status', 'Salary distribution recorded.');
    }

    public function edit(SalaryDistribution $salaryDistribution)
    {
        $employees = Employee::orderBy('name')->get();

        return view('admin.finance.salary_distributions.edit', [
            'distribution' => $salaryDistribution,
            'employees' => $employees,
        ]);
    }

    public function update(Request $request, SalaryDistribution $salaryDistribution)
    {
        $data = $this->validated($request);
        DB::transaction(function () use ($data, $salaryDistribution) {
            $this->persistDistribution($data, $salaryDistribution);
        });

        if ($request->hasFile('document')) {
            $path = $request->file('document')->store('salary-distributions', 'public');
            $salaryDistribution->update(['document_path' => $path]);
        }

        return redirect()->route('admin.salary-distributions.index')->with('status', 'Salary distribution updated.');
    }

    public function destroy(SalaryDistribution $salaryDistribution)
    {
        return redirect()
            ->route('admin.salary-distributions.index')
            ->withErrors([
                'salaryDistribution' => 'Posted salary distributions cannot be deleted. Preserve payroll history and correct them through controlled updates.',
            ]);
    }

    protected function validated(Request $request): array
    {
        $salaryDistribution = $request->route('salaryDistribution');

        return $request->validate([
            'employee_id' => [
                'required',
                'exists:employees,id',
                Rule::unique('salary_distributions')
                    ->ignore($salaryDistribution?->id)
                    ->where(function ($query) use ($request) {
                        return $query
                            ->where('period_start', $request->input('period_start'))
                            ->where('period_end', $request->input('period_end'));
                    }),
            ],
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'base_salary' => 'required|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',
            'ta_allowances' => 'nullable|numeric|min:0',
            'da_allowances' => 'nullable|numeric|min:0',
            'commission' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|max:50',
            'remarks' => 'nullable|string|max:255',
            'document' => 'nullable|file|max:10240',
        ]);
    }

    protected function persistDistribution(array $data, ?SalaryDistribution $salaryDistribution = null): SalaryDistribution
    {
        Employee::whereKey($data['employee_id'])->lockForUpdate()->first();

        $duplicateExists = SalaryDistribution::query()
            ->where('employee_id', $data['employee_id'])
            ->where('period_start', $data['period_start'])
            ->where('period_end', $data['period_end'])
            ->when($salaryDistribution, function ($query) use ($salaryDistribution) {
                $query->where($salaryDistribution->getKeyName(), '!=', $salaryDistribution->id);
            })
            ->lockForUpdate()
            ->exists();

        if ($duplicateExists) {
            throw ValidationException::withMessages([
                'employee_id' => 'A salary distribution already exists for this employee and period.',
            ]);
        }

        if ($salaryDistribution) {
            $salaryDistribution->update($data);

            return $salaryDistribution->fresh();
        }

        return SalaryDistribution::create($data);
    }
}
