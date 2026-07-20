@extends('layouts.app')

@section('page_title', 'Lab Financial Summary')

@section('content')
    @php
        $money = fn ($val) => number_format((float) $val, 2);
        $fmt = fn ($val) => $val ? \Carbon\Carbon::parse($val)->format('d M Y') : null;
        $fmtTime = fn ($val) => $val ? \Carbon\Carbon::parse($val)->format('h:i A') : null;

        $queryParams = request()->except(['page', 'tpage', 'rpage']);
        $exportParams = array_merge($queryParams, ['tab' => $tab]);
        $isRateList = $tab === 'rate_list';

        $activeRows = match ($tab) {
            'patients' => $patient_rows,
            'by_test' => $test_rows,
            default => $rate_rows,
        };
        $activeTotal = $activeRows->total();
        $grand = max((float) ($summary['grand_total'] ?? 0), 0.01);
        $paidShare = $isRateList ? 0 : min(100, round(((float) ($summary['paid_amount'] ?? 0) / $grand) * 100));
        $dueShare = $isRateList ? 0 : min(100, max(0, 100 - $paidShare));
    @endphp

    <div class="hms-lsr-page">

        {{-- Toolbar --}}
        <div class="hms-lsr-toolbar">
            <div class="hms-lsr-toolbar-left">
                <a href="{{ route('pathology.index') }}" class="hms-back-btn !py-2" title="Back to Pathology Lab">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h1 class="hms-lsr-toolbar-title">Lab financial summary</h1>
                    <p class="hms-lsr-toolbar-sub">Patient billing, revenue by test, and desktop rate list</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3 shrink-0">
                <nav class="hms-lsr-tabs" aria-label="Report view">
                    <a href="{{ route('pathology.lab_financial_summary', array_merge($queryParams, ['tab' => 'patients'])) }}"
                        class="hms-lsr-tab {{ $tab === 'patients' ? 'is-active' : '' }}">
                        Patients
                    </a>
                    <a href="{{ route('pathology.lab_financial_summary', array_merge($queryParams, ['tab' => 'by_test'])) }}"
                        class="hms-lsr-tab {{ $tab === 'by_test' ? 'is-active' : '' }}">
                        By test
                    </a>
                    <a href="{{ route('pathology.lab_financial_summary', array_merge($queryParams, ['tab' => 'rate_list'])) }}"
                        class="hms-lsr-tab {{ $tab === 'rate_list' ? 'is-active' : '' }}">
                        Rate list
                    </a>
                </nav>
                <div class="flex flex-wrap gap-2">
                    <x-btn variant="ghost" size="sm" :href="route('pathology.lab_financial_summary.print', $exportParams)" target="_blank">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        Print
                    </x-btn>
                    <x-btn variant="indigo" size="sm" :href="route('pathology.lab_financial_summary.pdf', $exportParams)">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Download PDF
                    </x-btn>
                </div>
            </div>
        </div>

        {{-- Summary strip --}}
        <div class="hms-lsr-summary-strip">
            @if($isRateList)
                <div class="hms-lsr-summary-accent">
                    <p class="hms-lsr-summary-eyebrow">Desktop rate list</p>
                    <h2 class="hms-lsr-summary-title">
                        {{ number_format($summary['tests']) }} {{ $summary['tests'] === 1 ? 'test' : 'tests' }}
                    </h2>
                    <p class="hms-lsr-summary-period">All pathology tests synced from desktop</p>
                </div>
                <div class="hms-lsr-stats">
                    <div class="hms-lsr-stat">
                        <span class="hms-lsr-stat-label">Tests</span>
                        <span class="hms-lsr-stat-value is-tests">{{ $summary['tests'] }}</span>
                    </div>
                    <div class="hms-lsr-stat">
                        <span class="hms-lsr-stat-label">With rate</span>
                        <span class="hms-lsr-stat-value is-collected">{{ $summary['with_price'] }}</span>
                    </div>
                    <div class="hms-lsr-stat">
                        <span class="hms-lsr-stat-label">Test heads</span>
                        <span class="hms-lsr-stat-value is-patients">{{ $summary['heads'] }}</span>
                    </div>
                </div>
            @else
                <div class="hms-lsr-summary-accent">
                    <p class="hms-lsr-summary-eyebrow">Financial overview</p>
                    <h2 class="hms-lsr-summary-title">
                        Rs {{ $money($summary['grand_total']) }} billed
                    </h2>
                    <p class="hms-lsr-summary-period">
                        {{ $date_from->format('d M Y') }} — {{ $date_to->format('d M Y') }}
                    </p>
                    <div class="hms-lsr-pipeline" role="img" aria-label="Paid versus due distribution">
                        @if(($summary['paid_amount'] ?? 0) > 0)
                            <div class="hms-lsr-pipeline-segment bg-teal-500"
                                style="flex: {{ max(1, (float) $summary['paid_amount']) }}"
                                title="Paid: {{ $money($summary['paid_amount']) }}"></div>
                        @endif
                        @if(($summary['due_amount'] ?? 0) > 0)
                            <div class="hms-lsr-pipeline-segment bg-amber-500"
                                style="flex: {{ max(1, (float) $summary['due_amount']) }}"
                                title="Due: {{ $money($summary['due_amount']) }}"></div>
                        @endif
                    </div>
                    <div class="hms-lsr-pipeline-legend">
                        <span class="hms-lsr-pipeline-legend-item">
                            <span class="hms-lsr-pipeline-dot bg-teal-400"></span>
                            Paid ({{ $paidShare }}%)
                        </span>
                        <span class="hms-lsr-pipeline-legend-item">
                            <span class="hms-lsr-pipeline-dot bg-amber-400"></span>
                            Due ({{ $dueShare }}%)
                        </span>
                    </div>
                </div>
                <div class="hms-lsr-stats">
                    <div class="hms-lsr-stat">
                        <span class="hms-lsr-stat-label">Patients</span>
                        <span class="hms-lsr-stat-value is-patients">{{ $summary['patients'] }}</span>
                    </div>
                    <div class="hms-lsr-stat">
                        <span class="hms-lsr-stat-label">Tests</span>
                        <span class="hms-lsr-stat-value is-tests">{{ $summary['tests'] }}</span>
                    </div>
                    <div class="hms-lsr-stat">
                        <span class="hms-lsr-stat-label">Grand total</span>
                        <span class="hms-lsr-stat-value is-vials">{{ $money($summary['grand_total']) }}</span>
                    </div>
                    <div class="hms-lsr-stat">
                        <span class="hms-lsr-stat-label">Paid</span>
                        <span class="hms-lsr-stat-value is-collected">{{ $money($summary['paid_amount']) }}</span>
                    </div>
                    <div class="hms-lsr-stat">
                        <span class="hms-lsr-stat-label">Due</span>
                        <span class="hms-lsr-stat-value is-lab">{{ $money($summary['due_amount']) }}</span>
                    </div>
                </div>
            @endif
        </div>

        {{-- Filters --}}
        <div class="hms-lsr-filter">
            <div class="hms-lsr-filter-header">
                <p class="hms-lsr-filter-title">Search &amp; filters</p>
                <p class="hms-lsr-filter-hint">
                    @if($isRateList)
                        Search by test name or desktop ID, or filter by test head
                    @else
                        Narrow by date, patient, invoice, or payment status
                    @endif
                </p>
            </div>
            <div class="hms-lsr-filter-body">
                <form method="GET" action="{{ route('pathology.lab_financial_summary') }}">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <div class="hms-lsr-filter-grid">
                        @if($isRateList)
                            <x-form.field label="Test name / ID" for="lfs_test_name">
                                <input type="text" id="lfs_test_name" name="test_name" placeholder="Search test"
                                    class="hms-input hms-lsr-input-focus" value="{{ $filters['test_name'] }}">
                            </x-form.field>
                            <x-form.field label="Test head" for="lfs_test_head">
                                <select id="lfs_test_head" name="test_head_id" class="hms-select hms-lsr-input-focus">
                                    <option value="">All heads</option>
                                    @foreach($test_heads as $head)
                                        <option value="{{ $head->id }}" @selected((string) ($filters['test_head_id'] ?? '') === (string) $head->id)>
                                            {{ $head->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </x-form.field>
                        @else
                            <x-form.field label="From date" for="lfs_date_from" required>
                                <input type="date" id="lfs_date_from" name="date_from" required
                                    class="hms-input hms-lsr-input-focus" value="{{ $filters['date_from'] }}">
                            </x-form.field>
                            <x-form.field label="To date" for="lfs_date_to" required>
                                <input type="date" id="lfs_date_to" name="date_to" required
                                    class="hms-input hms-lsr-input-focus" value="{{ $filters['date_to'] }}">
                            </x-form.field>
                            <x-form.field label="Lab reg no" for="lfs_lab_reg">
                                <input type="text" id="lfs_lab_reg" name="lab_reg" placeholder="e.g. 1042"
                                    class="hms-input hms-lsr-input-focus" value="{{ $filters['lab_reg'] }}">
                            </x-form.field>
                            <x-form.field label="MR no" for="lfs_mr">
                                <input type="text" id="lfs_mr" name="mr" placeholder="Medical record"
                                    class="hms-input hms-lsr-input-focus" value="{{ $filters['mr'] }}">
                            </x-form.field>
                            <x-form.field label="Patient name" for="lfs_name">
                                <input type="text" id="lfs_name" name="name" placeholder="Search by name"
                                    class="hms-input hms-lsr-input-focus" value="{{ $filters['name'] }}">
                            </x-form.field>
                            @if($tab === 'patients')
                                <x-form.field label="Invoice" for="lfs_invoice">
                                    <input type="text" id="lfs_invoice" name="invoice" placeholder="Desktop invoice"
                                        class="hms-input hms-lsr-input-focus" value="{{ $filters['invoice'] }}">
                                </x-form.field>
                                <x-form.field label="Payment status" for="lfs_payment_status">
                                    <select id="lfs_payment_status" name="payment_status" class="hms-select hms-lsr-input-focus">
                                        <option value="all" @selected($filters['payment_status'] === 'all')>All</option>
                                        <option value="paid" @selected($filters['payment_status'] === 'paid')>Paid</option>
                                        <option value="due" @selected($filters['payment_status'] === 'due')>Due</option>
                                    </select>
                                </x-form.field>
                            @else
                                <x-form.field label="Test name" for="lfs_test_name">
                                    <input type="text" id="lfs_test_name" name="test_name" placeholder="Search test"
                                        class="hms-input hms-lsr-input-focus" value="{{ $filters['test_name'] }}">
                                </x-form.field>
                            @endif
                        @endif
                        <div class="hms-lsr-filter-actions">
                            <x-btn type="submit" variant="cyan">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                Search
                            </x-btn>
                            <x-btn variant="ghost" :href="route('pathology.lab_financial_summary', ['tab' => $tab])">Reset filters</x-btn>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Results --}}
        <div class="hms-lsr-card">
            <div class="hms-lsr-card-header">
                <div>
                    @if($tab === 'patients')
                        <h3 class="hms-lsr-card-title">Patient billing</h3>
                        <p class="hms-lsr-card-sub">Grand total, paid, and due per patient</p>
                    @elseif($tab === 'by_test')
                        <h3 class="hms-lsr-card-title">Revenue by test</h3>
                        <p class="hms-lsr-card-sub">How many times each test was billed and total revenue</p>
                    @else
                        <h3 class="hms-lsr-card-title">Rate list</h3>
                        <p class="hms-lsr-card-sub">Desktop rates for all pathology tests</p>
                    @endif
                </div>
                @if($activeTotal > 0)
                    <span class="hms-lsr-card-count">{{ number_format($activeTotal) }}</span>
                @endif
            </div>

            @if($tab === 'patients')
                @if($patient_rows->isEmpty())
                    <div class="hms-lsr-empty">
                        <div class="hms-lsr-empty-icon">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <p class="hms-lsr-empty-title">No patients match your filters</p>
                        <p class="hms-lsr-empty-desc">Try widening the date range or clearing search fields.</p>
                    </div>
                @else
                    <div class="hms-table-wrap">
                        <table class="hms-lsr-table">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Registered</th>
                                    <th scope="col">Patient</th>
                                    <th scope="col">Tests</th>
                                    <th scope="col" class="text-right">Grand total</th>
                                    <th scope="col" class="text-right">Paid</th>
                                    <th scope="col" class="text-right">Due</th>
                                    <th scope="col">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($patient_rows as $index => $row)
                                    <tr>
                                        <td class="text-gray-400 text-xs tabular-nums">{{ $patient_rows->firstItem() + $index }}</td>
                                        <td class="whitespace-nowrap text-xs">
                                            @if($fmt($row['registration_date']))
                                                {{ $fmt($row['registration_date']) }}<br>
                                                <span class="text-gray-400">{{ $fmtTime($row['registration_date']) }}</span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>
                                            <p class="hms-lsr-patient">{{ $row['patient_name'] }}</p>
                                            <p class="hms-lsr-lab-reg">Lab {{ $row['lab_registration_no'] ?? '—' }}</p>
                                            @if(! empty($row['desktop_invoice'] ?? null))
                                                <p class="text-[10px] text-gray-400 mt-0.5">Inv {{ $row['desktop_invoice'] }}</p>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="hms-lsr-test">{{ $row['test_count'] }} {{ $row['test_count'] === 1 ? 'test' : 'tests' }}</span>
                                            @if($row['tests'])
                                                <p class="hms-lsr-tests-list mt-0.5">{{ $row['tests'] }}</p>
                                            @endif
                                        </td>
                                        <td class="text-right tabular-nums whitespace-nowrap font-semibold">{{ $money($row['grand_total']) }}</td>
                                        <td class="text-right tabular-nums whitespace-nowrap text-teal-700">{{ $money($row['paid_amount']) }}</td>
                                        <td class="text-right tabular-nums whitespace-nowrap {{ $row['due_amount'] > 0 ? 'text-amber-700 font-semibold' : '' }}">
                                            {{ $money($row['due_amount']) }}
                                        </td>
                                        <td>
                                            @if($row['payment_status'] === 'paid')
                                                <span class="hms-lsr-result-badge is-done">Paid</span>
                                            @else
                                                <span class="hms-lsr-result-badge is-pending">Due</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($patient_rows->hasPages())
                        <div class="hms-lsr-pagination">{{ $patient_rows->appends(array_merge($queryParams, ['tab' => 'patients']))->links() }}</div>
                    @endif
                @endif
            @elseif($tab === 'by_test')
                @if($test_rows->isEmpty())
                    <div class="hms-lsr-empty">
                        <div class="hms-lsr-empty-icon">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                            </svg>
                        </div>
                        <p class="hms-lsr-empty-title">No tests match your filters</p>
                        <p class="hms-lsr-empty-desc">Try a different date range or test name search.</p>
                    </div>
                @else
                    <div class="hms-table-wrap">
                        <table class="hms-lsr-table">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Test</th>
                                    <th scope="col" class="text-right">Count</th>
                                    <th scope="col" class="text-right">Revenue</th>
                                    <th scope="col" class="text-right">Avg price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($test_rows as $index => $row)
                                    @php
                                        $avg = $row['test_count'] > 0 ? $row['revenue'] / $row['test_count'] : 0;
                                    @endphp
                                    <tr>
                                        <td class="text-gray-400 text-xs tabular-nums">{{ $test_rows->firstItem() + $index }}</td>
                                        <td><span class="hms-lsr-test">{{ $row['test_name'] }}</span></td>
                                        <td class="text-right tabular-nums">{{ number_format($row['test_count']) }}</td>
                                        <td class="text-right tabular-nums font-semibold whitespace-nowrap">{{ $money($row['revenue']) }}</td>
                                        <td class="text-right tabular-nums whitespace-nowrap text-gray-500">{{ $money($avg) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($test_rows->hasPages())
                        <div class="hms-lsr-pagination">{{ $test_rows->appends(array_merge($queryParams, ['tab' => 'by_test']))->links() }}</div>
                    @endif
                @endif
            @else
                @if($rate_rows->isEmpty())
                    <div class="hms-lsr-empty">
                        <div class="hms-lsr-empty-icon">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <p class="hms-lsr-empty-title">No tests in rate list</p>
                        <p class="hms-lsr-empty-desc">Sync desktop tests, or clear the search filters.</p>
                    </div>
                @else
                    <div class="hms-table-wrap">
                        <table class="hms-lsr-table">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Desktop ID</th>
                                    <th scope="col">Test</th>
                                    <th scope="col">Test head</th>
                                    <th scope="col">Type</th>
                                    <th scope="col" class="text-right">Rate</th>
                                    <th scope="col">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rate_rows as $index => $row)
                                    <tr>
                                        <td class="text-gray-400 text-xs tabular-nums">{{ $rate_rows->firstItem() + $index }}</td>
                                        <td class="tabular-nums text-xs text-gray-500">{{ $row['desktop_test_id'] ?: '—' }}</td>
                                        <td><span class="hms-lsr-test">{{ $row['test_name'] }}</span></td>
                                        <td class="text-sm text-gray-600">{{ $row['test_head'] }}</td>
                                        <td class="text-xs text-gray-500">{{ $row['type'] }}</td>
                                        <td class="text-right tabular-nums whitespace-nowrap font-semibold">{{ $money($row['price']) }}</td>
                                        <td>
                                            @if($row['is_active'])
                                                <span class="hms-lsr-result-badge is-done">Active</span>
                                            @else
                                                <span class="hms-lsr-result-badge is-pending">Inactive</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($rate_rows->hasPages())
                        <div class="hms-lsr-pagination">{{ $rate_rows->appends(array_merge($queryParams, ['tab' => 'rate_list']))->links() }}</div>
                    @endif
                @endif
            @endif
        </div>
    </div>
@endsection
