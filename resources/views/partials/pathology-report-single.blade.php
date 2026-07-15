@php
    $reportService = app(\App\Services\PathologyReportService::class);
    $hasRemarksPage = $hasRemarksPage ?? $reportService->hasRemarksPage($test, $historyResults, $testComment ?? null);
    $hasTroponinInterpretation = $hasTroponinInterpretation ?? $reportService->hasTroponinHsInterpretationPage($test);
    $referenceTables = $referenceTables ?? $reportService->getReferenceTables($test);
    $showRemarks = $hasRemarksPage && ($test->report_format === 'Quantitative' || ! $test->report_format);
    $hasReferencePage = ! empty($referenceTables);

    $trailingPages = [];
    if ($showRemarks) { $trailingPages[] = 'remarks'; }

    $totalPages = 1 + count($trailingPages);
    $pageOneLabel = $totalPages > 1 ? 'Page 1 of ' . $totalPages : 'Page 1';
    $hasTrailingPages = count($trailingPages) > 0;
    $pageCounter = 1;
@endphp

<div class="report-page-main {{ $hasTrailingPages ? 'has-remarks-page' : '' }}">
    @include('partials.pathology-report-header', [
        'labPatient' => $labPatient,
        'test' => $test,
        'qrCodeDataUri' => $qrCodeDataUri ?? null,
        'reportEnteredBy' => $reportEnteredBy ?? null,
    ])

    @if($test->report_format === 'Quantitative' || !$test->report_format)
        @include('partials.pathology-results-table', ['testComment' => $testComment ?? null])
    @else
        <div class="descriptive-content">
            @php
                $currentResults = $historyResults[$labPatient->id] ?? collect();
            @endphp

            @foreach($test->testParticulars as $particular)
                @php
                    $result = $currentResults->where('test_particular_id', $particular->id)->first();
                    $val = $result ? $result->result_value : '';
                @endphp
                @if($val)
                    <div class="descriptive-item">
                        <b>{{ $particular->name }}</b>
                        <div>{!! $val !!}</div>
                    </div>
                @endif
            @endforeach

            @if(isset($testImages) && $testImages->count() > 0)
                <div class="descriptive-item">
                    <b>Attached Images</b>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-top: 10px;">
                        @foreach($testImages as $img)
                            <img src="{{ asset('storage/' . $img->image_path) }}" style="width: 100%; border: 1px solid #eee; border-radius: 4px;">
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        @if($historyResults->count() > 1)
            <div style="margin-top: 30px; page-break-before: always;">
                <h4 style="color: #004a99; border-bottom: 1px solid #eee; padding-bottom: 5px;">PREVIOUS REPORTS HISTORY</h4>
                <table style="margin-top: 10px;">
                    <thead>
                        <tr>
                            <th>Date</th>
                            @foreach($test->testParticulars->where('name', '!=', 'Findings') as $p)
                                <th>{{ $p->name }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($historyResults as $pid => $results)
                            @if($pid != $labPatient->id)
                                <tr>
                                    <td>{{ $results->first()->created_at->format('d-M-Y') }}</td>
                                    @foreach($test->testParticulars->where('name', '!=', 'Findings') as $p)
                                        @php $res = $results->where('test_particular_id', $p->id)->first(); @endphp
                                        <td>{{ $res ? $res->result_value : '-' }}</td>
                                    @endforeach
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @endif

    @if($hasTroponinInterpretation)
        @include('partials.pathology-troponin-hs-interpretation')
    @endif

    @if($hasReferencePage)
        @include('partials.pathology-reference-tables-page', [
            'referenceGroups' => [['label' => $test->name, 'tables' => $referenceTables]],
            'labPatient' => $labPatient,
            'mode' => 'inline',
        ])
    @endif

    @include('partials.lab-report-doctors-footer')

    <div class="inline-page-footer">
        <div>{{ $pageOneLabel }}</div>
    </div>
</div>

@foreach($trailingPages as $trailingPage)
    @php $pageCounter++; @endphp

    @if($trailingPage === 'remarks')
        @include('partials.pathology-results-remarks-page', [
            'testComment' => $testComment ?? null,
            'labPatient' => $labPatient,
            'test' => $test,
            'historyResults' => $historyResults,
            'hasTrailingPage' => $pageCounter < $totalPages,
        ])
    @endif

    <div class="inline-page-footer">
        <div>Page {{ $pageCounter }} of {{ $totalPages }}</div>
    </div>
@endforeach
