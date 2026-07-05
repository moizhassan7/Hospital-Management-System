@if($doctor->name)
    <div class="report-footer-doctor-name">{{ $doctor->name }}</div>
@endif
@if($doctor->designation)
    <div class="report-footer-doctor-meta">{{ $doctor->designation }}</div>
@endif
@if($doctor->qualifications)
    <div class="report-footer-doctor-meta">{{ $doctor->qualifications }}</div>
@endif
