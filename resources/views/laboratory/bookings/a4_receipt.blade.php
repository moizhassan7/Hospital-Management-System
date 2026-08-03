<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>A4 Receipt - {{ $patient->lab_registration_no }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            color: #000;
            margin: 0;
            padding: 0;
            background: #fff;
        }

        .page {
            width: 297mm;
            height: 210mm;
            display: flex;
            padding: 10mm;
            margin: 0 auto;
        }

        .half-page {
            width: 50%;
            height: 100%;
            padding: 0 15mm;
            position: relative;
        }

        .half-page:first-child {
            border-right: 1px dashed #ccc;
        }

        .footer {
            position: absolute;
            bottom: 5mm;
            left: 15mm;
            right: 15mm;
            text-align: center;
            font-size: 10px;
            border-top: 1px solid #ccc;
            padding-top: 5px;
        }

        @media print {
            body {
                width: 297mm;
                height: 210mm;
            }

            .page {
                padding: 0;
            }

            .no-print {
                display: none !important;
            }
        }

        .no-print-bar {
            background-color: #f1f5f9;
            padding: 15px;
            text-align: center;
        }

        .print-btn {
            background-color: #0f172a;
            color: #fff;
            border: none;
            padding: 8px 16px;
            cursor: pointer;
            border-radius: 4px;
            font-size: 14px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="no-print no-print-bar">
        <button class="print-btn" onclick="window.print()">Print A4 Landscape Receipt</button>
    </div>

    @php
        $branding = app(\App\Services\HospitalBrandingService::class);
        $mainLabName = strtoupper(trim((string) ($branding->get('name') ?? config('hospital.name', 'Hospital'))));
        $labName = $patient->resolveReportSiteLabel();

        $headerPhone = '';
        if (auth()->check()) {
            $authUser = auth()->user();
            $authUser->loadMissing('collectionCenter');
            if ($authUser->collectionCenter?->phone) {
                $headerPhone = trim((string) $authUser->collectionCenter->phone);
            } elseif ($authUser->isMainLabScope()) {
                $headerPhone = trim((string) ($branding->get('phone') ?? ''));
            }
        }
        if ($headerPhone === '') {
            $headerPhone = trim((string) ($branding->get('phone') ?? ''));
        }

        $logoPath = $branding->resolveImagePath($branding->get('logo'));
        $phcLogoPath = public_path('images/punjab-healthcare-commission-phc-logo-2F34F17F99-seeklogo.com.png');

        $logoBase64 = '';
        if ($logoPath && file_exists($logoPath)) {
            $logoBase64 = 'data:image/' . pathinfo($logoPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($logoPath));
        }

        $phcLogoBase64 = '';
        if ($phcLogoPath && file_exists($phcLogoPath)) {
            $phcLogoBase64 = 'data:image/' . pathinfo($phcLogoPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($phcLogoPath));
        }
    @endphp

    <div class="page">
        <!-- Copy 1 -->
        <div class="half-page">
            <div class="letterhead-zone" style="margin-bottom: 5px;">
                <table class="letterhead-table" style="width: 100%; border-collapse: collapse; table-layout: fixed;">
                    <tr>
                        <td style="width: 20%; vertical-align: middle; text-align: left; padding: 0;">
                            @if($logoBase64)
                                <img src="{{ $logoBase64 }}" alt="Lab Logo"
                                    style="width: 75px; height: 75px; object-fit: contain;">
                            @endif
                        </td>
                        <td style="width: 60%; vertical-align: middle; text-align: center;">
                            <div
                                style="font-family: 'Times New Roman', Times, DejaVu Serif, serif; font-size: 30px; font-weight: bold; letter-spacing: 0.05em; color: #000; line-height: 1;">
                                {{ $mainLabName }}
                            </div>
                            <div
                                style="font-family: 'Times New Roman', Times, DejaVu Serif, serif; font-size: 14px; font-weight: bold; letter-spacing: 0.05em; color: #000; text-transform: uppercase; margin-top: 5px;">
                                DIAGNOSTIC CENTRE
                            </div>
                        </td>
                        <td style="width: 20%; vertical-align: middle; text-align: right; padding: 0;">
                            @if($phcLogoBase64)
                                <img src="{{ $phcLogoBase64 }}" alt="PHC"
                                    style="width: 75px; height: 75px; object-fit: contain;">
                            @endif
                        </td>
                    </tr>
                </table>
            </div>

            <div
                style="border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 2px 0; margin-bottom: 8px; font-size: 10px; font-weight: bold;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 50%; text-align: left;">Lab Registration Number:
                            {{ $patient->lab_registration_no }}
                        </td>
                        <td style="width: 50%; text-align: right;">T/R ID: {{ $patient->id }}</td>
                    </tr>
                </table>
            </div>

            <table style="width: 100%; font-size: 10px; margin-bottom: 8px; line-height: 1.4;">
                <tr>
                    <td style="width: 33%;"><span style="font-weight: bold;">Patient:</span>
                        {{ $patient->patient_name }}
                    </td>
                    <td style="width: 33%;"><span style="font-weight: bold;">Age/Sex:</span> {{ $patient->age }}(Y) /
                        {{ $patient->gender }}
                    </td>
                    <td style="width: 34%;"><span style="font-weight: bold;">Registered at:</span>
                        {{ $patient->self_referred ? 'Malik Lab, Main' : ($patient->collectionCenter->name ?? 'Malik Lab, Main') }}
                    </td>
                </tr>
                <tr>
                    <td><span style="font-weight: bold;">Phone:</span> {{ $patient->contact_no ?? '—' }}</td>
                    <td><span style="font-weight: bold;">Referred By:</span>
                        {{ $patient->self_referred ? 'Self' : ($patient->refer_by_doctor_name ?? '—') }}
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="3" style="padding: 0;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="width: 33%;"><span style="font-weight: bold;">Collection Time:</span></td>
                                <td style="width: 33%;"><span style="font-weight: bold;">Specimen Collection
                                        Type:</span></td>
                                <td style="width: 34%;">Taken in lab</td>
                            </tr>
                            <tr>
                                <td>{{ $patient->created_at->format('d-M-Y h:i A') }}</td>
                                <td><span style="font-weight: bold;">Printed By:</span>
                                    {{ auth()->check() ? auth()->user()->name : '' }}
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3"><span style="font-weight: bold;">Printing Time:</span></td>
                            </tr>
                            <tr>
                                <td colspan="3">{{ now()->format('d-M-Y h:i A') }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <div
                style="font-weight: bold; font-size: 12px; text-align: center; border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 2px 0; margin-bottom: 5px;">
                Patient bill
            </div>

            <table style="width: 100%; font-size: 10px; border-collapse: collapse; margin-bottom: 5px;">
                <thead>
                    <tr style="border-bottom: 1px solid #000;">
                        <th style="text-align: left; padding: 4px 0; width: 10%;">Sr. No.</th>
                        <th style="text-align: left; padding: 4px 0; width: 45%;">Test Name</th>
                        <th style="text-align: left; padding: 4px 0; width: 25%;">Discount</th>
                        <th style="text-align: right; padding: 4px 0; width: 20%;">Price</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($patient->getSelectedTestsArray() as $index => $test)
                        @php
                            $listPrice = (float) ($test['list_price'] ?? $test['price'] ?? 0);
                            $netPrice = (float) ($test['price'] ?? 0);
                            $discountAmount = max(0, $listPrice - $netPrice);
                        @endphp
                        <tr>
                            <td style="padding: 4px 0;">{{ $index + 1 }}</td>
                            <td style="padding: 4px 0;">{{ $test['name'] }}</td>
                            <td style="padding: 4px 0;">{{ $discountAmount > 0 ? number_format($discountAmount, 2) : '-' }}
                            </td>
                            <td style="text-align: right; padding: 4px 0;">{{ number_format($netPrice, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="border-top: 1px solid #000; margin-bottom: 10px;"></div>

            <div style="float: right; width: 40%; font-size: 11px; margin-bottom: 10px; position: relative;">
                <table style="width: 100%;">
                    <tr>
                        <td style="font-weight: bold; padding: 2px 0;">Gross Amount</td>
                        <td style="text-align: right; padding: 2px 0;">{{ number_format($patient->sub_total, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; padding: 2px 0;">Discount In %</td>
                        <td style="text-align: right; padding: 2px 0;">
                            {{ $patient->discount_percentage > 0 ? number_format($patient->discount_percentage, 2) : '0.00' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; padding: 2px 0;">Discount In Amount</td>
                        <td style="text-align: right; padding: 2px 0;">{{ number_format($patient->discount, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; padding: 2px 0;">Paid Amount</td>
                        <td style="text-align: right; padding: 2px 0;">{{ number_format($patient->paid_amount, 2) }}
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; padding: 2px 0;">Due Amount</td>
                        <td style="text-align: right; padding: 2px 0;">{{ number_format($patient->due_amount, 2) }}</td>
                    </tr>
                </table>
                @if($patient->due_amount <= 0)
                    <div
                        style="position: absolute; top: -10px; left: -40px; border: 2px dashed #000; border-radius: 50%; width: 70px; height: 70px; display: flex; align-items: center; justify-content: center; transform: rotate(-15deg); opacity: 0.5;">
                        <span style="font-weight: bold; font-size: 18px;">PAID</span>
                    </div>
                @endif
            </div>
            <div style="clear: both;"></div>

            <div
                style="position: absolute; bottom: 15mm; left: 15mm; right: 15mm; display: flex; align-items: center; border: 1px dashed #ccc; padding: 5px; border-radius: 4px; background: #fff;">
                @if(!empty($qrCodeDataUri))
                    <img src="{{ $qrCodeDataUri }}" alt="QR" style="width: 55px; height: 55px; margin-right: 10px;">
                @endif
                <div>
                    <strong style="font-size: 12px;">Scan for Reports & History</strong><br>
                    <span style="font-size: 10px; color: #555;">Scan this QR code with your phone's camera to securely
                        view your test results online.</span>
                </div>
            </div>

            <div class="footer">
                <p>Software By Switch2itech Ph#03007844301 | Printed: {{ now()->format('d-M-Y h:i A') }}</p>
            </div>
        </div>

        <!-- Copy 2 -->
        <div class="half-page">
            <div class="letterhead-zone" style="margin-bottom: 5px;">
                <table class="letterhead-table" style="width: 100%; border-collapse: collapse; table-layout: fixed;">
                    <tr>
                        <td style="width: 20%; vertical-align: middle; text-align: left; padding: 0;">
                            @if($logoBase64)
                                <img src="{{ $logoBase64 }}" alt="Lab Logo"
                                    style="width: 75px; height: 75px; object-fit: contain;">
                            @endif
                        </td>
                        <td style="width: 60%; vertical-align: middle; text-align: center;">
                            <div
                                style="font-family: 'Times New Roman', Times, DejaVu Serif, serif; font-size: 30px; font-weight: bold; letter-spacing: 0.05em; color: #000; line-height: 1;">
                                {{ $mainLabName }}
                            </div>
                            <div
                                style="font-family: 'Times New Roman', Times, DejaVu Serif, serif; font-size: 14px; font-weight: bold; letter-spacing: 0.05em; color: #000; text-transform: uppercase; margin-top: 5px;">
                                DIAGNOSTIC CENTRE
                            </div>
                        </td>
                        <td style="width: 20%; vertical-align: middle; text-align: right; padding: 0;">
                            @if($phcLogoBase64)
                                <img src="{{ $phcLogoBase64 }}" alt="PHC"
                                    style="width: 75px; height: 75px; object-fit: contain;">
                            @endif
                        </td>
                    </tr>
                </table>
            </div>

            <div
                style="border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 2px 0; margin-bottom: 8px; font-size: 10px; font-weight: bold;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 50%; text-align: left;">Lab Registration Number:
                            {{ $patient->lab_registration_no }}
                        </td>
                        <td style="width: 50%; text-align: right;">T/R ID: {{ $patient->id }}</td>
                    </tr>
                </table>
            </div>

            <table style="width: 100%; font-size: 10px; margin-bottom: 8px; line-height: 1.4;">
                <tr>
                    <td style="width: 33%;"><span style="font-weight: bold;">Patient:</span>
                        {{ $patient->patient_name }}
                    </td>
                    <td style="width: 33%;"><span style="font-weight: bold;">Age/Sex:</span> {{ $patient->age }}(Y) /
                        {{ $patient->gender }}
                    </td>
                    <td style="width: 34%;"><span style="font-weight: bold;">Registered at:</span>
                        {{ $patient->self_referred ? 'Malik Lab, Main' : ($patient->collectionCenter->name ?? 'Malik Lab, Main') }}
                    </td>
                </tr>
                <tr>
                    <td><span style="font-weight: bold;">Phone:</span> {{ $patient->contact_no ?? '—' }}</td>
                    <td><span style="font-weight: bold;">Referred By:</span>
                        {{ $patient->self_referred ? 'Self' : ($patient->refer_by_doctor_name ?? '—') }}
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="3" style="padding: 0;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="width: 33%;"><span style="font-weight: bold;">Collection Time:</span></td>
                                <td style="width: 33%;"><span style="font-weight: bold;">Specimen Collection
                                        Type:</span></td>
                                <td style="width: 34%;">Taken in lab</td>
                            </tr>
                            <tr>
                                <td>{{ $patient->created_at->format('d-M-Y h:i A') }}</td>
                                <td><span style="font-weight: bold;">Printed By:</span>
                                    {{ auth()->check() ? auth()->user()->name : '' }}
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3"><span style="font-weight: bold;">Printing Time:</span></td>
                            </tr>
                            <tr>
                                <td colspan="3">{{ now()->format('d-M-Y h:i A') }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <div
                style="font-weight: bold; font-size: 12px; text-align: center; border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 2px 0; margin-bottom: 5px;">
                Patient bill
            </div>

            <table style="width: 100%; font-size: 10px; border-collapse: collapse; margin-bottom: 5px;">
                <thead>
                    <tr style="border-bottom: 1px solid #000;">
                        <th style="text-align: left; padding: 4px 0; width: 10%;">Sr. No.</th>
                        <th style="text-align: left; padding: 4px 0; width: 45%;">Test Name</th>
                        <th style="text-align: left; padding: 4px 0; width: 25%;">Discount</th>
                        <th style="text-align: right; padding: 4px 0; width: 20%;">Price</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($patient->getSelectedTestsArray() as $index => $test)
                        @php
                            $listPrice = (float) ($test['list_price'] ?? $test['price'] ?? 0);
                            $netPrice = (float) ($test['price'] ?? 0);
                            $discountAmount = max(0, $listPrice - $netPrice);
                        @endphp
                        <tr>
                            <td style="padding: 4px 0;">{{ $index + 1 }}</td>
                            <td style="padding: 4px 0;">{{ $test['name'] }}</td>
                            <td style="padding: 4px 0;">{{ $discountAmount > 0 ? number_format($discountAmount, 2) : '-' }}
                            </td>
                            <td style="text-align: right; padding: 4px 0;">{{ number_format($netPrice, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="border-top: 1px solid #000; margin-bottom: 10px;"></div>

            <div style="float: right; width: 40%; font-size: 11px; margin-bottom: 10px; position: relative;">
                <table style="width: 100%;">
                    <tr>
                        <td style="font-weight: bold; padding: 2px 0;">Gross Amount</td>
                        <td style="text-align: right; padding: 2px 0;">{{ number_format($patient->sub_total, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; padding: 2px 0;">Discount In %</td>
                        <td style="text-align: right; padding: 2px 0;">
                            {{ $patient->discount_percentage > 0 ? number_format($patient->discount_percentage, 2) : '0.00' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; padding: 2px 0;">Discount In Amount</td>
                        <td style="text-align: right; padding: 2px 0;">{{ number_format($patient->discount, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; padding: 2px 0;">Paid Amount</td>
                        <td style="text-align: right; padding: 2px 0;">{{ number_format($patient->paid_amount, 2) }}
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; padding: 2px 0;">Due Amount</td>
                        <td style="text-align: right; padding: 2px 0;">{{ number_format($patient->due_amount, 2) }}</td>
                    </tr>
                </table>
                @if($patient->due_amount <= 0)
                    <div
                        style="position: absolute; top: -10px; left: -40px; border: 2px dashed #000; border-radius: 50%; width: 70px; height: 70px; display: flex; align-items: center; justify-content: center; transform: rotate(-15deg); opacity: 0.5;">
                        <span style="font-weight: bold; font-size: 18px;">PAID</span>
                    </div>
                @endif
            </div>
            <div style="clear: both;"></div>

            <div
                style="position: absolute; bottom: 15mm; left: 15mm; right: 15mm; display: flex; align-items: center; border: 1px dashed #ccc; padding: 5px; border-radius: 4px; background: #fff;">
                @if(!empty($qrCodeDataUri))
                    <img src="{{ $qrCodeDataUri }}" alt="QR" style="width: 55px; height: 55px; margin-right: 10px;">
                @endif
                <div>
                    <strong style="font-size: 12px;">Scan for Reports & History</strong><br>
                    <span style="font-size: 10px; color: #555;">Scan this QR code with your phone's camera to securely
                        view your test results online.</span>
                </div>
            </div>

            <div class="footer">
                <p>Software By Switch2itech Ph#03007844301 | Printed: {{ now()->format('d-M-Y h:i A') }}</p>
            </div>
        </div>
    </div>

    <script>
        window.addEventListener('DOMContentLoaded', function () {
            setTimeout(function () {
                window.print();
                if (window.opener) {
                    window.close();
                }
            }, 800);
        });
    </script>
</body>

</html>