<!DOCTYPE html>
<html lang="en">
<head>
    <title>Prescription</title>
    <style>
        body { 
            font-family: sans-serif; margin: 0; padding: 0; font-size: 10pt; 
            -webkit-print-color-adjust: exact; 
            print-color-adjust: exact;
        }
        .print-area { padding: 0 1cm; }
        h3.section-title { 
            font-weight: bold; 
            margin-bottom: 0.3rem; 
            font-size: 11pt; 
            text-transform: uppercase; 
            border-bottom: 1px solid #ccc; 
            padding-bottom: 0.2rem; 
        }
        @import url('https://fonts.googleapis.com/css2?family=Noto+Nastaliq+Urdu:wght@400;700&display=swap');
        .section-text { font-size: 9pt; line-height: 1.3; }
        .section { margin-bottom: 1rem; }
        
        .patient-details-row { 
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid #eee;
            padding: 8px 15px;
            background: #fcfcfc;
            border-radius: 4px;
            margin-bottom: 0.5cm;
        }
        .patient-detail-item { font-size: 10pt; }
        .patient-detail-label { font-weight: bold; color: #555; margin-right: 5px; }
        .patient-detail-value { font-weight: bold; color: #000; }

        .main-content-container { 
            display: flex; 
            justify-content: space-between; 
            margin-top: 0.5cm; 
            gap: 1.5cm;
        }
        
        .left-rx-container { 
            width: 68%; 
            border-right: 1px solid #ddd;
            padding-right: 0.8cm;
        }
        
        .right-info-container { 
            width: 30%; 
            padding-left: 0.2cm;
        }
        
        .vitals-list p { margin: 0.25rem 0; font-size: 9pt; border-bottom: 0.5px solid #eee; padding-bottom: 2px; }
        
        .rx-section { margin: 0; }
        .rx-header {
            display: flex;
            align-items: center;
            margin-bottom: 0.8rem;
            border-bottom: 2px solid #333;
            padding-bottom: 0.3rem;
        }
        .rx-symbol {
            font-size: 24pt;
            font-weight: bold;
            margin-right: 0.5rem;
            font-family: serif;
        }
        .rx-title {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .medicines-list-container { margin-top: 0.5rem; }
        .medicine-item { 
            margin-bottom: 0.8rem;
            line-height: 1.4;
            padding-left: 0.5rem;
        }
        .med-row-top {
            display: flex;
            align-items: baseline;
        }
        .med-number { 
            min-width: 1.5rem; 
            font-weight: bold;
            font-size: 11pt;
            color: #444;
        }
        .med-name { 
            font-weight: bold; 
            font-size: 12pt;
            color: #000;
        }
        .med-row-bottom {
            margin-left: 1.5rem;
            font-size: 10pt; 
            color: #444;
            display: flex;
            gap: 1rem;
            margin-top: 2px;
        }
        .med-dosage { font-style: italic; }
        .med-duration { color: #666; font-weight: 500; }

        .abstain-list-container { margin-top: 0.5rem; background: #f9f9f9; padding: 0.5rem; border-radius: 5px; }
        .abstain-item { 
            display: flex; 
            align-items: baseline; 
            margin-bottom: 0.3rem; 
        }
        .abstain-number { 
            min-width: 1.2rem; 
            text-align: right; 
            margin-right: 0.5rem; 
            font-weight: bold;
        }
        .abstain-text { font-size: 9pt; }
        
        .next-visit { 
            margin-top: 1.5rem; 
            font-size: 10pt;
            padding: 5px;
            border: 1px dashed #999;
            text-align: center;
        }
        
        .right-info-container .section-title {
            font-size: 10pt;
            margin-bottom: 0.3rem;
            color: #555;
            border-bottom: 1px solid #eee;
        }
        .right-info-container .section {
            margin-bottom: 1.2rem;
        }

        /* Watermark and Header Styles */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.08;
            z-index: -1;
            width: 70%;
            pointer-events: none;
        }
        .header-container {
            height: 4.5cm;
            width: 100%;
            display: flex;
            position: relative;
            border-bottom: 2px solid #2e7d32;
            margin-bottom: 1.2cm;
            padding: 0;
            align-items: flex-start;
            justify-content: space-between;
        }
        .header-stripes {
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3cm;
            display: flex;
            overflow: hidden;
        }
        .stripe-green { 
            width: 50px; 
            background: #2e7d32; 
            height: 150%; 
            transform: skewX(-25deg); 
            margin-left: -20px; 
        }
        .stripe-purple { 
            width: 40px; 
            background: #6a1b9a; 
            height: 150%; 
            transform: skewX(-25deg); 
            margin-left: 10px; 
        }

        .header-content {
            display: flex;
            width: 100%;
            padding: 0.5cm 0.5cm 0 3.5cm;
            justify-content: space-between;
        }
        .header-english { width: 55%; }
        .header-urdu { width: 40%; text-align: right; direction: rtl; font-family: 'Noto Nastaliq Urdu', serif; }

        .doc-name-en { font-family: 'Georgia', serif; font-size: 20pt; font-weight: bold; color: #2e7d32; margin-bottom: 4px; font-style: italic; }
        .doc-details-en { font-size: 10pt; color: #333; line-height: 1.3; font-weight: bold; }
        .doc-email-en { font-size: 9pt; color: #555; margin-top: 6px; }

        .doc-name-ur { font-size: 22pt; font-weight: bold; color: #6a1b9a; margin-bottom: 8px; }
        .doc-details-ur { font-size: 11pt; color: #333; line-height: 1.6; font-weight: bold; }
        
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <!-- Watermark Image (Optional) -->
    @if(file_exists(public_path('bg-image.jpeg')))
        <img src="{{ asset('bg-image.jpeg') }}" class="watermark" alt="Watermark">
    @endif

    <div class="print-area font-sans text-gray-800 leading-relaxed">
        <!-- Structured Header -->
        <div class="header-container">
            <div class="header-stripes">
                <div class="stripe-green"></div>
                <div class="stripe-purple"></div>
            </div>
            <div class="header-content">
                <div class="header-english">
                    <div class="doc-name-en">Dr. Kashif Rafique</div>
                    <div class="doc-details-en">
                        MBBS, FCPS (Nephrology)<br>
                        Consultant Nephrologist & Transplant Physician<br>
                        Expert in Kidney Diseases & Hypertension
                    </div>
                    <div class="doc-email-en">Email: kashifrafique88@gmail.com</div>
                </div>
                <div class="header-urdu">
                    <div class="doc-name-ur">ڈاکٹر کاشف رفیق</div>
                    <div class="doc-details-ur">
                        ایم بی بی ایس، ایف سی پی ایس (نیفرالوجی)<br>
                        کنسلٹنٹ نیفرولوجسٹ اینڈ ٹرانسپلانٹ فزیشن<br>
                        ماہر امراض گردہ اور بلڈ پریشر
                    </div>
                </div>
            </div>
        </div>

        <div class="patient-details-row">
            <div class="patient-detail-item">
                <span class="patient-detail-label">Name:</span>
                <span class="patient-detail-value">{{ $prescription->patient->name }}</span>
            </div>
            <div class="patient-detail-item">
                <span class="patient-detail-label">Age:</span>
                <span class="patient-detail-value">{{ $prescription->patient->age }} Years</span>
            </div>
            <div class="patient-detail-item">
                <span class="patient-detail-label">Gender:</span>
                <span class="patient-detail-value">{{ $prescription->patient->gender }}</span>
            </div>
            <div class="patient-detail-item">
                <span class="patient-detail-label">Date:</span>
                <span class="patient-detail-value">{{ $prescription->created_at->format('d-m-Y') }}</span>
            </div>
        </div>

        <div class="main-content-container">
            <!-- Rx Column (Left) -->
            <div class="left-rx-container">
                <div class="rx-section">
                    <div class="rx-header">
                        <span class="rx-symbol">Rx</span>
                        <span class="rx-title">Prescription</span>
                    </div>
                    <div class="medicines-list-container">
                        @if(!empty($prescription->medicines))
                            @foreach($prescription->medicines as $index => $med)
                                <div class="medicine-item">
                                    <div class="med-row-top">
                                        <span class="med-number">{{ $index + 1 }}.</span>
                                        <span class="med-name">{{ $med['name'] }}</span>
                                    </div>
                                    <div class="med-row-bottom">
                                        <span class="med-dosage">{{ $med['dosage'] }}</span>
                                        <span class="med-duration">&mdash; for {{ $med['duration'] }} days/units</span>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <p class="text-gray-500 italic">No medicines prescribed.</p>
                        @endif
                    </div>
                </div>

            </div>

            <!-- Info Column (Right) -->
            <div class="right-info-container">
                <div class="section">
                    <h3 class="section-title">Complaints</h3>
                    <p class="section-text" style="font-style: italic;">{{ $prescription->complaints ?? 'N/A' }}</p>
                </div>
                
                <div class="section">
                    <h3 class="section-title">Vitals</h3>
                    <div class="vitals-list">
                        <p><strong>B.P:</strong> {{ $prescription->bp ?? 'N/A' }}</p>
                        <p><strong>Pulse:</strong> {{ $prescription->pulse ?? 'N/A' }} bpm</p>
                        <p><strong>Temp:</strong> {{ $prescription->temperature ?? 'N/A' }} °F</p>
                        <p><strong>O2:</strong> {{ $prescription->oxygen ?? 'N/A' }} %</p>
                        <p><strong>Weight:</strong> {{ $prescription->weight ?? 'N/A' }} kg</p>
                    </div>
                </div>
                
                <div class="section">
                    <h3 class="section-title">Diagnoses</h3>
                    <p class="section-text" style="font-weight: bold;">{{ !empty($formattedDiagnoses) ? implode(', ', $formattedDiagnoses) : 'N/A' }}</p>
                </div>
                
                <div class="section">
                    <h3 class="section-title">Reports</h3>
                    <p class="section-text">{{ !empty($formattedReports) ? implode(', ', $formattedReports) : 'N/A' }}</p>
                </div>
                
                <div class="section">
                    <h3 class="section-title">Clinical Notes</h3>
                    <p class="section-text">{{ $prescription->notes ?? 'N/A' }}</p>
                </div>
                
                <div class="section next-visit">
                    <p>Next Visit: <strong>{{ $prescription->next_visit_date ? \Carbon\Carbon::parse($prescription->next_visit_date)->format('d-m-Y') : 'N/A' }}</strong></p>
                </div>
            </div>
        </div>

        <!-- Abstain Section at Bottom -->
        <div style="margin-top: 1rem; border-top: 1px solid #eee; pt-4">
            <div class="section">
                <h3 class="section-title" style="font-size: 12pt; border-bottom: 2px solid #333; margin-bottom: 0.5rem;">Advice / Abstain (Parhaiz)</h3>
                <div class="abstain-list-container" style="background: #fff; border: 1px solid #eee; padding: 0.8rem;">
                    @if(!empty($formattedAbstains))
                        <div style="display: flex; flex-wrap: wrap; gap: 1rem;">
                            @foreach($formattedAbstains as $index => $text)
                                <div class="abstain-item" style="min-width: 45%; margin-bottom: 0.5rem;">
                                    <span class="abstain-number" style="font-weight: bold; color: #444;">{{ $index + 1 }}.</span>
                                    <span class="abstain-text" style="font-size: 10pt;">{{ $text }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 italic text-sm">No specific advice or abstains listed.</p>
                    @endif
                </div>
            </div>
        </div>

        <div style="height: 1cm;"></div>
        <div style="text-align: center; border-top: 1px solid #ddd; padding-top: 5px; font-size: 8pt; color: #666;">
            Software by <strong>Switch2itech</strong> 03007844301
        </div>
        <div style="height: 1cm;"></div>
    </div>
    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
