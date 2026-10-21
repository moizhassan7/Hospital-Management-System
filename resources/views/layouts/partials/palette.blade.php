<div id="lc-palette" class="dialog-backdrop hidden" role="dialog" aria-modal="true" aria-label="Jump to page" data-dialog data-backdrop-close>
    <div class="palette">
        <div class="palette-input-row">
            <x-ui.icon name="search" />
            <input type="text" class="palette-input" placeholder="Jump to a page…" autocomplete="off" spellcheck="false" role="combobox" aria-expanded="true" aria-controls="lc-palette-list" aria-autocomplete="list" aria-label="Page name" data-palette-input>
            <span class="kbd">Esc</span>
        </div>

        <div id="lc-palette-list" class="palette-list" role="listbox" aria-label="Pages">
            @php $paletteIndex = 0; @endphp
            @foreach ($navigation as $group)
                @foreach ($group['items'] as $item)
                    <a id="lc-palette-{{ $paletteIndex++ }}" href="{{ route($item['route']) }}" class="palette-item" role="option" aria-selected="false" data-palette-item data-search="{{ \Illuminate\Support\Str::lower($item['label'] . ' ' . $group['label']) }}">
                        <x-ui.icon :name="$item['icon']" />
                        <span>{{ $item['label'] }}</span>
                        <span class="palette-item-group">{{ $group['label'] }}</span>
                    </a>
                    @foreach ($item['children'] ?? [] as $child)
                        @continue($child['route'] === $item['route'])
                        <a id="lc-palette-{{ $paletteIndex++ }}" href="{{ route($child['route']) }}" class="palette-item" role="option" aria-selected="false" data-palette-item data-search="{{ \Illuminate\Support\Str::lower($child['label'] . ' ' . $item['label'] . ' ' . $group['label']) }}">
                            <x-ui.icon :name="$item['icon']" />
                            <span>{{ $child['label'] }}</span>
                            <span class="palette-item-group">{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                @endforeach
            @endforeach
            <p class="palette-empty" data-palette-empty hidden>No pages match “<span></span>”.</p>
        </div>

        <div class="palette-footer">
            <span><span class="kbd">↑</span> <span class="kbd">↓</span> move</span>
            <span><span class="kbd">Enter</span> open</span>
            <span><span class="kbd">Esc</span> close</span>
        </div>
    </div>
</div>
