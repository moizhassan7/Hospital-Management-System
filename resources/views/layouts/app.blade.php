<!DOCTYPE html>
<html lang="en">

@php
    $authUser = Auth::user();
    $isSuperAdmin = $authUser->isSuperAdmin();
    $permissionCache = [];
    $can = function (string $permission) use ($authUser, $isSuperAdmin, &$permissionCache) {
        return $permissionCache[$permission] ??= ($isSuperAdmin || $authUser->hasPermission($permission));
    };

    $canFinance = $can('View Finance') || $can('Create Vouchers') || $can('Create Cash Handover') || $can('Manage Doctor Payments') || $can('Receive Cash Handover');
    $canHospitalFinance = $can('View Finance') || $can('Create Vouchers') || $can('Manage Doctor Payments') || $can('Receive Cash Handover');
    $financeSelfOnly = $can('Create Cash Handover') && ! $canHospitalFinance;
    $canCreateVoucher = $can('Create Vouchers') || $can('Manage Doctor Payments') || $can('Create Cash Handover');
    $canVoucherCategories = $can('Manage Voucher Categories');

    $labChildren = fn (string $prefix) => [
        ['label' => 'Patient registration', 'route' => "$prefix.patient_registration"],
        ['label' => 'Result entry', 'route' => "$prefix.result_entry.search"],
        ['label' => 'Test catalog', 'route' => "$prefix.test_catalog"],
        ['label' => 'Tests', 'route' => "$prefix.manage_test"],
        ['label' => 'Test heads', 'route' => "$prefix.test_head"],
        ['label' => 'Test particulars', 'route' => "$prefix.add_test_particulars"],
    ];

    $navigation = [
        ['label' => 'Workspace', 'items' => [
            ['label' => 'Dashboard', 'icon' => 'layout-dashboard', 'route' => 'dashboard', 'show' => $can('View Dashboard')],
            ['label' => 'Patients', 'icon' => 'users', 'route' => 'patients.index', 'show' => $can('View Patients'),
                'active' => ['patients.index', 'patients.register', 'patients.store', 'patients.all', 'patients.book_appointment', 'patients.store_appointment', 'patients.appointments.*', 'patients.admission_form', 'patients.birth_certificates', 'patients.death_certificates'],
                'children' => [
                    ['label' => 'Register patient', 'route' => 'patients.register'],
                    ['label' => 'All patients', 'route' => 'patients.all'],
                    ['label' => 'Book appointment', 'route' => 'patients.book_appointment'],
                    ['label' => 'Appointments', 'route' => 'patients.appointments.index', 'active' => ['patients.appointments.*']],
                ]],
            ['label' => 'Departments', 'icon' => 'building', 'route' => 'departments.index', 'show' => $can('View Departments'),
                'active' => ['departments.*', 'specialities.*', 'floors.*', 'rooms.*', 'doctor_types.*', 'shifts.*', 'emergency_charges.*', 'diagnoses.*'],
                'children' => [
                    ['label' => 'Departments', 'route' => 'departments.add', 'active' => ['departments.add', 'departments.edit']],
                    ['label' => 'Specialities', 'route' => 'specialities.add', 'active' => ['specialities.*']],
                    ['label' => 'Diagnoses', 'route' => 'diagnoses.index', 'active' => ['diagnoses.*']],
                    ['label' => 'Floors', 'route' => 'floors.add', 'active' => ['floors.*']],
                    ['label' => 'Rooms & wards', 'route' => 'rooms.add', 'active' => ['rooms.*']],
                    ['label' => 'Doctor types', 'route' => 'doctor_types.add', 'active' => ['doctor_types.*']],
                    ['label' => 'Shifts', 'route' => 'shifts.add', 'active' => ['shifts.*']],
                    ['label' => 'Emergency charges', 'route' => 'emergency_charges.add', 'active' => ['emergency_charges.*']],
                ]],
        ]],
        ['label' => 'Patient care', 'items' => [
            ['label' => 'OPD', 'icon' => 'clipboard', 'route' => 'patients.outdoor_register', 'show' => $can('View Patients'),
                'active' => ['patients.outdoor_register', 'patients.store_outdoor', 'patients.outdoor_receipt', 'opd.*'],
                'children' => [
                    ['label' => "Today's registration", 'route' => 'patients.outdoor_register'],
                    ['label' => 'Appointments', 'route' => 'patients.appointments.index', 'active' => ['patients.appointments.*']],
                    ['label' => 'Book appointment', 'route' => 'patients.book_appointment'],
                ]],
            ['label' => 'Indoor / IPD', 'icon' => 'bed', 'route' => 'patients.indoor_register', 'show' => $can('View Patients'),
                'active' => ['patients.indoor_register', 'patients.store_indoor', 'patients.discharge', 'patients.store_discharge', 'inpatient.*'],
                'children' => [
                    ['label' => 'Admission', 'route' => 'patients.indoor_register', 'active' => ['patients.indoor_register', 'patients.store_indoor']],
                    ['label' => 'Discharge', 'route' => 'patients.discharge', 'active' => ['patients.discharge', 'patients.store_discharge']],
                ]],
            ['label' => 'Emergency', 'icon' => 'siren', 'route' => 'emergency.index', 'show' => $can('View Emergency'),
                'active' => ['emergency.*'],
                'children' => [
                    ['label' => 'Register patient', 'route' => 'emergency.patients.create'],
                    ['label' => 'Emergency patients', 'route' => 'emergency.patients.all', 'active' => ['emergency.patients.all', 'emergency.patient.receipt']],
                ]],
            ['label' => 'Day care', 'icon' => 'sun', 'route' => 'day-care.create', 'show' => $can('View Procedures'),
                'active' => ['day-care.*', 'procedures.*'],
                'children' => [
                    ['label' => 'Day care procedure', 'route' => 'day-care.create', 'active' => ['day-care.*']],
                    ['label' => 'Procedure catalogue', 'route' => 'procedures.index', 'active' => ['procedures.*']],
                ]],
            ['label' => 'Doctors', 'icon' => 'stethoscope', 'route' => 'doctors.index', 'show' => $can('View Doctors'),
                'active' => ['doctors.index', 'doctors.create', 'doctors.edit', 'doctors.all'],
                'children' => [
                    ['label' => 'Add doctor', 'route' => 'doctors.create', 'active' => ['doctors.create', 'doctors.edit']],
                    ['label' => 'All doctors', 'route' => 'doctors.all'],
                ]],
        ]],
        ['label' => 'Diagnostics', 'items' => [
            ['label' => 'Pathology lab', 'icon' => 'flask', 'route' => 'pathology.index', 'show' => $can('View Laboratory'),
                'active' => ['pathology.*', 'laboratory.*', 'lab-attendant.*'], 'children' => $labChildren('pathology')],
            ['label' => 'Radiology', 'icon' => 'scan', 'route' => 'radiology.index', 'show' => $can('View Laboratory'),
                'active' => ['radiology.*'], 'children' => $labChildren('radiology')],
        ]],
        ['label' => 'Operations & finance', 'items' => [
            ['label' => 'Indoor advances', 'icon' => 'wallet', 'route' => 'patients.advances', 'show' => $can('View Patients'), 'active' => ['patients.advances*']],
            ['label' => 'Store', 'icon' => 'package', 'route' => 'store.index', 'show' => $can('View Store'),
                'active' => ['store.*'],
                'children' => [
                    ['label' => 'Consumable items', 'route' => 'store.consumable_items', 'active' => ['store.consumable_items*']],
                    ['label' => 'Non-consumable items', 'route' => 'store.non_consumable_items', 'active' => ['store.non_consumable_items*']],
                    ['label' => 'Categories & locations', 'route' => 'store.definitions', 'active' => ['store.definitions', 'store.categories.*', 'store.locations.*']],
                    ['label' => 'Purchase stock', 'route' => 'store.purchase_stock'],
                    ['label' => 'Issue stock', 'route' => 'store.issue_stock'],
                    ['label' => 'Return stock', 'route' => 'store.return_stock'],
                    ['label' => 'Suppliers', 'route' => 'store.supplier', 'active' => ['store.supplier*']],
                ]],
            ['label' => 'Finance', 'icon' => 'landmark', 'route' => 'finance.index', 'show' => $canFinance,
                'active' => ['finance.*'],
                'children' => array_values(array_filter([
                    ['label' => 'Voucher daybook', 'route' => 'finance.vouchers.index', 'active' => ['finance.vouchers.index', 'finance.vouchers.show']],
                    $canCreateVoucher ? ['label' => 'New voucher', 'route' => 'finance.vouchers.create'] : null,
                    ['label' => 'Account ledger', 'route' => 'finance.ledger.index'],
                    ! $financeSelfOnly ? ['label' => 'Trial balance', 'route' => 'finance.reports.trial_balance'] : null,
                    ! $financeSelfOnly ? ['label' => 'Chart of accounts', 'route' => 'finance.accounts.index'] : null,
                    $canVoucherCategories ? ['label' => 'Voucher categories', 'route' => 'finance.categories.index'] : null,
                ]))],
        ]],
        ['label' => 'Administration', 'items' => [
            ['label' => 'Reports', 'icon' => 'bar-chart', 'route' => 'index', 'show' => $can('View Reports'), 'active' => ['index', 'reports.*']],
            ['label' => 'Users', 'icon' => 'user-cog', 'route' => 'admin.user_manager', 'show' => $can('View Users'), 'active' => ['admin.user_manager*']],
            ['label' => 'Roles', 'icon' => 'shield-check', 'route' => 'admin.roles.index', 'show' => $can('Manage Roles'), 'active' => ['admin.roles.*']],
            ['label' => 'Settings', 'icon' => 'settings', 'route' => 'admin.settings.index', 'show' => $authUser->hasRole('Admin'), 'active' => ['admin.settings.*']],
        ]],
    ];

    // Resolve visibility and active state once; the sidebar, breadcrumbs and palette all read from this.
    $activeItem = null;
    $activeChild = null;
    foreach ($navigation as $g => $group) {
        $navigation[$g]['items'] = array_values(array_filter($group['items'], fn ($item) => $item['show']));
        foreach ($navigation[$g]['items'] as $i => $item) {
            $isActive = request()->routeIs(...($item['active'] ?? [$item['route']]));
            $navigation[$g]['items'][$i]['is_active'] = $isActive;
            foreach ($item['children'] ?? [] as $c => $child) {
                $childActive = $isActive && request()->routeIs(...($child['active'] ?? [$child['route']]));
                $navigation[$g]['items'][$i]['children'][$c]['is_active'] = $childActive;
                if ($childActive && ! $activeChild) {
                    $activeChild = $child;
                }
            }
            if ($isActive && ! $activeItem) {
                $activeItem = $item;
            }
        }
    }
    $navigation = array_values(array_filter($navigation, fn ($group) => count($group['items']) > 0));

    $quickActions = array_values(array_filter([
        $can('View Patients') ? ['label' => 'Register patient', 'icon' => 'user-plus', 'route' => 'patients.register'] : null,
        $can('View Patients') ? ['label' => 'OPD visit', 'icon' => 'clipboard', 'route' => 'patients.outdoor_register'] : null,
        $can('View Patients') ? ['label' => 'Indoor admission', 'icon' => 'bed', 'route' => 'patients.indoor_register'] : null,
        $can('View Emergency') ? ['label' => 'Emergency patient', 'icon' => 'siren', 'route' => 'emergency.patients.create'] : null,
        $can('View Procedures') ? ['label' => 'Day care procedure', 'icon' => 'sun', 'route' => 'day-care.create'] : null,
        $can('View Laboratory') ? ['label' => 'Lab registration', 'icon' => 'flask', 'route' => 'pathology.patient_registration'] : null,
        $canCreateVoucher ? ['label' => 'Accounting voucher', 'icon' => 'receipt', 'route' => 'finance.vouchers.create'] : null,
    ]));

    $homeUrl = isset($navigation[0]['items'][0]) ? route($navigation[0]['items'][0]['route']) : url('/');

    // Breadcrumbs: module › sub-page › page title (from @section('title')), without repeating labels.
    $pageTitle = html_entity_decode(trim($__env->yieldContent('title')), ENT_QUOTES);
    $breadcrumbs = [];
    if ($activeItem) {
        $breadcrumbs[] = ['label' => $activeItem['label'], 'url' => route($activeItem['route'])];
        if ($activeChild && $activeChild['label'] !== $activeItem['label'] && $activeChild['route'] !== $activeItem['route']) {
            $breadcrumbs[] = ['label' => $activeChild['label'], 'url' => route($activeChild['route'])];
        }
    }
    if ($pageTitle !== '' && (empty($breadcrumbs) || strcasecmp(end($breadcrumbs)['label'], $pageTitle) !== 0)) {
        $breadcrumbs[] = ['label' => $pageTitle, 'url' => null];
    }

    $hospitalName = get_setting('hospital_name', config('app.name', 'Hospital'));
    $hospitalLogo = get_setting('logo');
    $roleName = optional($authUser->roles->first())->name;
    $initials = collect(preg_split('/\s+/', trim($authUser->name)))->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');
@endphp

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@hasSection('title')@yield('title') · @endif{{ $hospitalName }}</title>

    <script>
        (function () {
            try {
                var pref = localStorage.getItem('lc.sidebar');
                if (pref === 'collapsed' || (pref === null && window.innerWidth < 1280)) {
                    document.documentElement.classList.add('sidebar-collapsed');
                }
            } catch (e) {}
        })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@500;600&family=Inter:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body>
    <a href="#main" class="skip-link">Skip to main content</a>

    @include('layouts.partials.sidebar')
    <div class="sidebar-backdrop" data-sidebar-backdrop hidden></div>

    <div class="app-main">
        @include('layouts.partials.topbar')

        <main id="main" class="app-content" tabindex="-1">
            @include('layouts.partials.flash')
            @yield('content')
        </main>
    </div>

    @include('layouts.partials.palette')
    @include('layouts.partials.confirm')

    @stack('scripts')
</body>

</html>
