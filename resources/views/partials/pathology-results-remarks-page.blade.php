@php
    $remarkItems = collect();

    foreach ($test->testParticulars as $particular) {
        if (! $particular->remarks) {
            continue;
        }

        $hasSavedResult = false;
        $resultValue = null;

        foreach ($historyResults as $results) {
            $saved = $results->where('test_particular_id', $particular->id)->first();
            if ($saved && $saved->result_value !== '' && $saved->result_value !== null) {
                $hasSavedResult = true;
                $resultValue = $saved->result_value;
            }
        }

        if ($particular->is_calculated && ! $hasSavedResult) {
            continue;
        }

        if (! $hasSavedResult) {
            continue;
        }

        $remarkItems->push([
            'name' => $particular->name,
            'remarks' => $particular->remarks,
            'result' => $resultValue,
            'unit' => $particular->unit,
        ]);
    }

    $hasRemarksPage = $remarkItems->isNotEmpty();
@endphp

@if($hasRemarksPage)
    <div class="report-remarks-page">
        <div class="remarks-page-header">
            <h3>Interpretation &amp; Clinical Notes</h3>
            <p class="remarks-subtitle">{{ $test->name }} — {{ $labPatient->patient_name }} (Lab Reg: {{ $labPatient->lab_registration_no ?? 'N/A' }})</p>
        </div>

        @foreach($remarkItems as $item)
            <div class="remark-block">
                <h4 class="remark-title">{{ $item['name'] }}</h4>
                @if($item['result'] !== null && $item['result'] !== '')
                    <p class="remark-result">
                        <strong>Result:</strong>
                        {{ $item['result'] }}{{ $item['unit'] ? ' ' . $item['unit'] : '' }}
                    </p>
                @endif
                <div class="remark-body">{!! nl2br(e($item['remarks'])) !!}</div>
            </div>
        @endforeach
    </div>
@endif
