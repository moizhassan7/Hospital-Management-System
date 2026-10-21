<aside class="app-sidebar" id="app-sidebar" aria-label="Main navigation">
    <a href="{{ $homeUrl }}" class="sidebar-brand" aria-label="{{ $hospitalName }} home">
        <span class="sidebar-logo">
            @if ($hospitalLogo)
                <img src="{{ asset('storage/' . $hospitalLogo) }}" alt="">
            @else
                <x-ui.icon name="cross" class="size-5" />
            @endif
        </span>
        <span class="sidebar-brand-text">
            <span class="sidebar-brand-name">{{ $hospitalName }}</span>
            <span class="sidebar-brand-sub">Hospital management</span>
        </span>
    </a>

    <nav class="sidebar-nav">
        @foreach ($navigation as $group)
            <div class="nav-group">
                <p class="nav-group-label">{{ $group['label'] }}</p>
                <ul role="list">
                    @foreach ($group['items'] as $item)
                        @php $hasChildren = ! empty($item['children']); @endphp
                        <li @class(['nav-item', 'has-children' => $hasChildren, 'is-open' => $hasChildren && $item['is_active']])>
                            <a href="{{ route($item['route']) }}" @class(['nav-link', 'is-active' => $item['is_active']]) @if ($item['is_active'] && ! $activeChild) aria-current="page" @endif>
                                <x-ui.icon :name="$item['icon']" class="nav-icon" />
                                <span class="nav-label">{{ $item['label'] }}</span>
                            </a>
                            @if ($hasChildren)
                                @php $subId = 'nav-sub-' . \Illuminate\Support\Str::slug($item['label']); @endphp
                                <button type="button" class="nav-toggle" data-nav-toggle aria-controls="{{ $subId }}" aria-expanded="{{ $item['is_active'] ? 'true' : 'false' }}" aria-label="Show {{ $item['label'] }} pages">
                                    <x-ui.icon name="chevron-down" class="size-3.5" />
                                </button>
                                <ul class="nav-sub" id="{{ $subId }}" role="list">
                                    @foreach ($item['children'] as $child)
                                        <li>
                                            <a href="{{ route($child['route']) }}" @class(['nav-sub-link', 'is-active' => $child['is_active']]) @if ($child['is_active']) aria-current="page" @endif>{{ $child['label'] }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </nav>

    <div class="sidebar-footer">
        <span class="kbd">Ctrl</span> <span class="kbd">K</span>&nbsp; Jump to any page
    </div>
</aside>
