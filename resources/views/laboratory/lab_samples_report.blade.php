@extends('layouts.app')

@section('page_title', 'Lab Samples Report')

@section('content')
    @php
        use App\Models\LabSampleVial;

        $fmt = fn ($val) => $val ? \Carbon\Carbon::parse($val)->format('d M Y') : null;
        $fmtTime = fn ($val) => $val ? \Carbon\Carbon::parse($val)->format('h:i A') : null;
        $fmtFull = fn ($val) => $val ? \Carbon\Carbon::parse($val)->format('d M Y h:i A') : '—';

        $queryParams = request()->except(['page', 'vpage']);
        $exportParams = array_merge($queryParams, ['tab' => $tab]);

        $pipelineStages = [
            ['key' => 'not_collected', 'label' => 'Not collected', 'count' => $summary['not_collected'], 'dot' => 'bg-gray-400', 'bar' => 'bg-gray-400'],
            ['key' => 'collected', 'label' => 'Collected', 'count' => $summary['collected'], 'dot' => 'bg-green-400', 'bar' => 'bg-green-500'],
            ['key' => 'in_lab', 'label' => 'In lab', 'count' => $summary['in_lab'], 'dot' => 'bg-blue-400', 'bar' => 'bg-blue-500'],
            ['key' => 'completed', 'label' => 'Results done', 'count' => $summary['completed'], 'dot' => 'bg-teal-400', 'bar' => 'bg-teal-500'],
        ];
        $activeRows = $tab === 'tests' ? $test_rows : $vial_rows;
        $activeTotal = $activeRows->total();
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
                    <h1 class="hms-lsr-toolbar-title">Lab samples report</h1>
                    <p class="hms-lsr-toolbar-sub">Track collection, lab receipt, and result completion</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3 shrink-0">
                <nav class="hms-lsr-tabs" aria-label="Report view">
                    <a href="{{ route('pathology.lab_samples_report', array_merge($queryParams, ['tab' => 'tests'])) }}"
                        class="hms-lsr-tab {{ $tab === 'tests' ? 'is-active' : '' }}">
                        Tests workflow
                    </a>
                    <a href="{{ route('pathology.lab_samples_report', array_merge($queryParams, ['tab' => 'vials'])) }}"
                        class="hms-lsr-tab {{ $tab === 'vials' ? 'is-active' : '' }}">
                        Sample vials
                    </a>
                </nav>
                <div class="flex flex-wrap gap-2">
                    <x-btn variant="ghost" size="sm" :href="route('pathology.lab_samples_report.print', $exportParams)" target="_blank">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        Print
                    </x-btn>
                    <x-btn variant="indigo" size="sm" :href="route('pathology.lab_samples_report.pdf', $exportParams)">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Download PDF
                    </x-btn>
                </div>
            </div>
        </div>

        {{-- Summary strip with pipeline --}}
        <div class="hms-lsr-summary-strip">
            <div class="hms-lsr-summary-accent">
                <p class="hms-lsr-summary-eyebrow">Sample pipeline</p>
                <h2 class="hms-lsr-summary-title">
                    {{ $summary['tests'] }} {{ $summary['tests'] === 1 ? 'test' : 'tests' }} in period
                </h2>
                <p class="hms-lsr-summary-period">
                    {{ $date_from->format('d M Y') }} — {{ $date_to->format('d M Y') }}
                </p>
                <div class="hms-lsr-pipeline" role="img" aria-label="Sample workflow distribution">
                    @foreach($pipelineStages as $stage)
                        @if($stage['count'] > 0)
                            <div class="hms-lsr-pipeline-segment {{ $stage['bar'] }}"
                                style="flex: {{ $stage['count'] }}"
                                title="{{ $stage['label'] }}: {{ $stage['count'] }}"></div>
                        @endif
                    @endforeach
                </div>
                <div class="hms-lsr-pipeline-legend">
                    @foreach($pipelineStages as $stage)
                        <span class="hms-lsr-pipeline-legend-item">
                            <span class="hms-lsr-pipeline-dot {{ $stage['dot'] }}"></span>
                            {{ $stage['label'] }} ({{ $stage['count'] }})
                        </span>
                    @endforeach
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
                    <span class="hms-lsr-stat-label">Vials</span>
                    <span class="hms-lsr-stat-value is-vials">{{ $summary['vials'] }}</span>
                </div>
                <div class="hms-lsr-stat">
                    <span class="hms-lsr-stat-label">Not collected</span>
                    <span class="hms-lsr-stat-value is-muted">{{ $summary['not_collected'] }}</span>
                </div>
                <div class="hms-lsr-stat">
                    <span class="hms-lsr-stat-label">Collected</span>
                    <span class="hms-lsr-stat-value is-collected">{{ $summary['collected'] }}</span>
                </div>
                <div class="hms-lsr-stat">
                    <span class="hms-lsr-stat-label">In lab</span>
                    <span class="hms-lsr-stat-value is-lab">{{ $summary['in_lab'] }}</span>
                </div>
                <div class="hms-lsr-stat">
                    <span class="hms-lsr-stat-label">Results done</span>
                    <span class="hms-lsr-stat-value is-done">{{ $summary['completed'] }}</span>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="hms-lsr-filter">
            <div class="hms-lsr-filter-header">
                <p class="hms-lsr-filter-title">Search &amp; filters</p>
                <p class="hms-lsr-filter-hint">Narrow results by date, patient, or sample status</p>
            </div>
            <div class="hms-lsr-filter-body">
                <form method="GET" action="{{ route('pathology.lab_samples_report') }}">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <div class="hms-lsr-filter-grid">
                        <x-form.field label="From date" for="lsr_date_from" required>
                            <input type="date" id="lsr_date_from" name="date_from" required
                                class="hms-input hms-lsr-input-focus" value="{{ $filters['date_from'] }}">
                        </x-form.field>
                        <x-form.field label="To date" for="lsr_date_to" required>
                            <input type="date" id="lsr_date_to" name="date_to" required
                                class="hms-input hms-lsr-input-focus" value="{{ $filters['date_to'] }}">
                        </x-form.field>
                        <x-form.field label="Lab reg no" for="lsr_lab_reg">
                            <input type="text" id="lsr_lab_reg" name="lab_reg" placeholder="e.g. 1042"
                                class="hms-input hms-lsr-input-focus" value="{{ $filters['lab_reg'] }}">
                        </x-form.field>
                        <x-form.field label="MR no" for="lsr_mr">
                            <input type="text" id="lsr_mr" name="mr" placeholder="Medical record"
                                class="hms-input hms-lsr-input-focus" value="{{ $filters['mr'] }}">
                        </x-form.field>
                        <x-form.field label="Patient name" for="lsr_name">
                            <input type="text" id="lsr_name" name="name" placeholder="Search by name"
                                class="hms-input hms-lsr-input-focus" value="{{ $filters['name'] }}">
                        </x-form.field>
                        @if($tab === 'vials')
                            <x-form.field label="Barcode" for="lsr_barcode">
                                <input type="text" id="lsr_barcode" name="barcode" placeholder="Vial barcode"
                                    class="hms-input hms-lsr-input-focus font-mono" value="{{ $filters['barcode'] }}">
                            </x-form.field>
                        @endif
                        <x-form.field label="Sample status" for="lsr_sample_status">
                            <select id="lsr_sample_status" name="sample_status" class="hms-select hms-lsr-input-focus">
                                <option value="all">All statuses</option>
                                @foreach($sampleStatuses as $key => $label)
                                    <option value="{{ $key }}" @selected($filters['sample_status'] === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </x-form.field>
                        @if($tab === 'tests')
                            <x-form.field label="Result status" for="lsr_result_status">
                                <select id="lsr_result_status" name="result_status" class="hms-select hms-lsr-input-focus">
                                    <option value="all" @selected($filters['result_status'] === 'all')>All results</option>
                                    <option value="pending" @selected($filters['result_status'] === 'pending')>Pending</option>
                                    <option value="completed" @selected($filters['result_status'] === 'completed')>Completed</option>
                                </select>
                            </x-form.field>
                        @endif
                        <div class="hms-lsr-filter-actions">
                            <x-btn type="submit" variant="cyan">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                Search
                            </x-btn>
                            <x-btn variant="ghost" :href="route('pathology.lab_samples_report', ['tab' => $tab])">Reset filters</x-btn>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Results --}}
        <div class="hms-lsr-card">
            <div class="hms-lsr-card-header">
                <div>
                    @if($tab === 'tests')
                        <h3 class="hms-lsr-card-title">Tests workflow</h3>
                        <p class="hms-lsr-card-sub">Sample collection, lab receipt, and result status per test</p>
                    @else
                        <h3 class="hms-lsr-card-title">Sample vials</h3>
                        <p class="hms-lsr-card-sub">Physical vials — barcode, collection, and reporting timeline</p>
                    @endif
                </div>
                @if($activeTotal > 0)
                    <span class="hms-lsr-card-count">{{ number_format($activeTotal) }}</span>
                @endif
            </div>

            @if($tab === 'tests')
                @if($test_rows->isEmpty())
                    <div class="hms-lsr-empty">
                        <div class="hms-lsr-empty-icon">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <p class="hms-lsr-empty-title">No tests match your filters</p>
                        <p class="hms-lsr-empty-desc">Try widening the date range or clearing patient search fields.</p>
                    </div>
                @else
                    <div class="hms-table-wrap">
                        <table class="hms-lsr-table">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Registered</th>
                                    <th scope="col">Patient</th>
                                    <th scope="col">Test</th>
                                    <th scope="col">Sample</th>
                                    <th scope="col">Workflow</th>
                                    <th scope="col">Result</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($test_rows as $index => $row)
                                    <tr>
                                        <td class="text-gray-400 text-xs tabular-nums">{{ $test_rows->firstItem() + $index }}</td>
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
                                        </td>
                                        <td><span class="hms-lsr-test">{{ $row['test_name'] }}</span></td>
                                        <td>
                                            <span class="hms-lsr-status {{ LabSampleVial::statusBadgeClass($row['sample_status']) }}">
                                                {{ $row['sample_status_label'] }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="hms-lsr-workflow">
                                                <div class="hms-lsr-workflow-step">
                                                    <span class="hms-lsr-workflow-dot {{ $row['sample_collected_at'] ? 'is-done' : 'is-pending' }}"></span>
                                                    <span class="hms-lsr-workflow-label">Collect</span>
                                                    <span class="hms-lsr-workflow-value {{ $row['sample_collected_at'] ? '' : 'is-empty' }}">
                                                        {{ $fmtFull($row['sample_collected_at']) }}
                                                    </span>
                                                </div>
                                                <div class="hms-lsr-workflow-step">
                                                    <span class="hms-lsr-workflow-dot {{ $row['sample_received_in_lab_at'] ? 'is-done' : 'is-pending' }}"></span>
                                                    <span class="hms-lsr-workflow-label">In lab</span>
                                                    <span class="hms-lsr-workflow-value {{ $row['sample_received_in_lab_at'] ? '' : 'is-empty' }}">
                                                        {{ $fmtFull($row['sample_received_in_lab_at']) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($row['result_status'] === 'completed')
                                                <span class="hms-lsr-result-badge is-done">Done</span>
                                                @if($row['result_completed_at'])
                                                    <p class="text-[10px] text-gray-400 mt-1 whitespace-nowrap">{{ $fmtFull($row['result_completed_at']) }}</p>
                                                @endif
                                            @else
                                                <span class="hms-lsr-result-badge is-pending">Pending</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($row['result_status'] === 'completed')
                                                <a href="{{ route('pathology.print_report', ['lab_patient_id' => $row['lab_patient_id'], 'test_id' => $row['test_id']]) }}"
                                                    onclick="event.preventDefault(); window.promptWithHeaderFooter(withHeader => window.open(window.appendWithHeaderParam(this.href, withHeader), '_blank'));" class="hms-lsr-action is-report">
                                                    Report
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                    </svg>
                                                </a>
                                            @else
                                                <a href="{{ route('pathology.result_entry.show_form', ['lab_patient_id' => $row['lab_patient_id'], 'test_id' => $row['test_id']]) }}"
                                                    class="hms-lsr-action is-enter">
                                                    Enter results
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                    </svg>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($test_rows->hasPages())
                        <div class="hms-lsr-pagination">{{ $test_rows->appends(array_merge($queryParams, ['tab' => 'tests']))->links() }}</div>
                    @endif
                @endif
            @else
                @if($vial_rows->isEmpty())
                    <div class="hms-lsr-empty">
                        <div class="hms-lsr-empty-icon">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                            </svg>
                        </div>
                        <p class="hms-lsr-empty-title">No vials match your filters</p>
                        <p class="hms-lsr-empty-desc">Try a different date range or search by barcode.</p>
                    </div>
                @else
                    <div class="hms-table-wrap">
                        <table class="hms-lsr-table">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Patient</th>
                                    <th scope="col">Barcode</th>
                                    <th scope="col">Vial</th>
                                    <th scope="col">Tests</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Workflow</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($vial_rows as $index => $row)
                                    <tr>
                                        <td class="text-gray-400 text-xs tabular-nums">{{ $vial_rows->firstItem() + $index }}</td>
                                        <td>
                                            <p class="hms-lsr-patient">{{ $row['patient_name'] }}</p>
                                            <p class="hms-lsr-lab-reg">Lab {{ $row['lab_registration_no'] ?? '—' }}</p>
                                        </td>
                                        <td><span class="hms-lsr-barcode">{{ $row['barcode'] }}</span></td>
                                        <td><span class="hms-lsr-vial">{{ $row['vial_type'] }} #{{ $row['vial_number'] }}</span></td>
                                        <td><p class="hms-lsr-tests-list">{{ $row['tests'] }}</p></td>
                                        <td>
                                            <span class="hms-lsr-status {{ LabSampleVial::statusBadgeClass($row['status']) }}">
                                                {{ $row['status_label'] }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="hms-lsr-workflow">
                                                <div class="hms-lsr-workflow-step">
                                                    <span class="hms-lsr-workflow-dot {{ $row['collected_at'] ? 'is-done' : 'is-pending' }}"></span>
                                                    <span class="hms-lsr-workflow-label">Collect</span>
                                                    <span class="hms-lsr-workflow-value {{ $row['collected_at'] ? '' : 'is-empty' }}">
                                                        {{ $fmtFull($row['collected_at']) }}
                                                    </span>
                                                </div>
                                                <div class="hms-lsr-workflow-step">
                                                    <span class="hms-lsr-workflow-dot {{ $row['received_in_lab_at'] ? 'is-done' : 'is-pending' }}"></span>
                                                    <span class="hms-lsr-workflow-label">In lab</span>
                                                    <span class="hms-lsr-workflow-value {{ $row['received_in_lab_at'] ? '' : 'is-empty' }}">
                                                        {{ $fmtFull($row['received_in_lab_at']) }}
                                                    </span>
                                                </div>
                                                <div class="hms-lsr-workflow-step">
                                                    <span class="hms-lsr-workflow-dot {{ $row['reported_at'] ? 'is-done' : 'is-pending' }}"></span>
                                                    <span class="hms-lsr-workflow-label">Report</span>
                                                    <span class="hms-lsr-workflow-value {{ $row['reported_at'] ? '' : 'is-empty' }}">
                                                        {{ $fmtFull($row['reported_at']) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($vial_rows->hasPages())
                        <div class="hms-lsr-pagination">{{ $vial_rows->appends(array_merge($queryParams, ['tab' => 'vials']))->links() }}</div>
                    @endif
                @endif
            @endif
        </div>
    </div>
@endsection
