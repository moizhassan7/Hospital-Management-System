@extends('layouts.app')

@section('page_title', 'Test Catalog')

@section('content')
    <div class="hms-page-toolbar">
        <div>
            <h2 class="hms-page-heading">Pathology Test Catalog</h2>
            <p class="text-sm text-gray-500 mt-1">Browse tests, sample vials, and result parameters</p>
        </div>
        <a href="{{ route('pathology.index') }}" class="hms-back-btn">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Pathology
        </a>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
        <div class="hms-stat-card">
            <p class="hms-stat-label">Test heads</p>
            <p class="hms-stat-value text-gray-900">{{ number_format($stats['heads']) }}</p>
        </div>
        <div class="hms-stat-card">
            <p class="hms-stat-label">Total tests</p>
            <p class="hms-stat-value text-blue-700">{{ number_format($stats['tests']) }}</p>
        </div>
        <div class="hms-stat-card">
            <p class="hms-stat-label">Matching</p>
            <p class="hms-stat-value text-purple-700">{{ number_format($filteredTestsCount) }}</p>
        </div>
        <div class="hms-stat-card">
            <p class="hms-stat-label">Parameters</p>
            <p class="hms-stat-value text-teal-700">{{ number_format($stats['particulars']) }}</p>
        </div>
    </div>

    <div class="hms-panel hms-panel-padded mb-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end">
            <div class="flex-1 min-w-0">
                <label for="catalog-search" class="hms-label">Search catalog</label>
                <div class="hms-search-bar mt-1">
                    <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input
                        type="search"
                        id="catalog-search"
                        class="hms-input"
                        placeholder="Search test name, code, vial, parameter, unit, range…"
                        value="{{ $filters['q'] }}"
                        autocomplete="off"
                    >
                    <button type="button" id="catalog-search-clear" class="text-sm text-gray-500 hover:text-gray-700 px-2 {{ $filters['q'] === '' ? 'hidden' : '' }}">Clear</button>
                </div>
                <p class="text-xs text-gray-400 mt-1">Case-insensitive · updates as you type</p>
            </div>

            <div class="w-full lg:w-52">
                <label for="catalog-test-head" class="hms-label">Test head</label>
                <select id="catalog-test-head" class="hms-select mt-1">
                    <option value="">All test heads</option>
                    @foreach($allTestHeads as $head)
                        <option value="{{ $head->id }}" {{ (string) $filters['test_head_id'] === (string) $head->id ? 'selected' : '' }}>
                            {{ $head->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="w-full lg:w-44">
                <label for="catalog-particulars" class="hms-label">Parameters</label>
                <select id="catalog-particulars" class="hms-select mt-1">
                    <option value="" {{ $filters['particulars'] === '' ? 'selected' : '' }}>All tests</option>
                    <option value="with" {{ $filters['particulars'] === 'with' ? 'selected' : '' }}>With parameters</option>
                    <option value="without" {{ $filters['particulars'] === 'without' ? 'selected' : '' }}>Without parameters</option>
                </select>
            </div>

            @if($hasActiveFilters)
                <div class="shrink-0">
                    <a href="{{ route('pathology.test_catalog') }}" class="hms-btn hms-btn-ghost">Reset</a>
                </div>
            @endif
        </div>
    </div>

    <div id="catalog-results" class="transition-opacity duration-150">
        @include('laboratory.partials.test_catalog_results')
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const results = document.getElementById('catalog-results');
            const searchInput = document.getElementById('catalog-search');
            const clearBtn = document.getElementById('catalog-search-clear');
            const headSelect = document.getElementById('catalog-test-head');
            const particularsSelect = document.getElementById('catalog-particulars');
            let searchTimer = null;

            function catalogUrl(page) {
                const url = new URL(window.location.pathname, window.location.origin);
                const q = searchInput ? String(searchInput.value || '').trim() : '';
                const head = headSelect ? headSelect.value : '';
                const particulars = particularsSelect ? particularsSelect.value : '';

                if (q !== '') {
                    url.searchParams.set('q', q);
                }
                if (head !== '') {
                    url.searchParams.set('test_head_id', head);
                }
                if (particulars !== '') {
                    url.searchParams.set('particulars', particulars);
                }
                if (page && page > 1) {
                    url.searchParams.set('page', String(page));
                }

                return url.toString();
            }

            function toggleClearButton() {
                if (!clearBtn || !searchInput) {
                    return;
                }
                clearBtn.classList.toggle('hidden', String(searchInput.value || '').trim() === '');
            }

            function loadCatalog(url) {
                if (!results) {
                    return;
                }

                results.classList.add('opacity-50', 'pointer-events-none');

                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html',
                    },
                })
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('Catalog search failed');
                        }
                        return response.text();
                    })
                    .then(function (html) {
                        results.innerHTML = html;
                        window.history.replaceState({}, '', url);
                        bindPagination();
                    })
                    .catch(function (error) {
                        console.error('Catalog search error:', error);
                    })
                    .finally(function () {
                        results.classList.remove('opacity-50', 'pointer-events-none');
                    });
            }

            function reloadCatalog(page) {
                loadCatalog(catalogUrl(page || 1));
            }

            function bindPagination() {
                if (!results) {
                    return;
                }

                results.querySelectorAll('.test-catalog-pagination a').forEach(function (link) {
                    link.addEventListener('click', function (event) {
                        event.preventDefault();
                        const href = link.getAttribute('href');
                        if (href) {
                            loadCatalog(href);
                        }
                    });
                });
            }

            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    toggleClearButton();
                    clearTimeout(searchTimer);
                    searchTimer = setTimeout(function () {
                        reloadCatalog(1);
                    }, 300);
                });

                searchInput.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        clearTimeout(searchTimer);
                        reloadCatalog(1);
                    }
                });
            }

            if (clearBtn && searchInput) {
                clearBtn.addEventListener('click', function () {
                    searchInput.value = '';
                    toggleClearButton();
                    reloadCatalog(1);
                    searchInput.focus();
                });
            }

            [headSelect, particularsSelect].forEach(function (select) {
                if (!select) {
                    return;
                }
                select.addEventListener('change', function () {
                    reloadCatalog(1);
                });
            });

            bindPagination();
        });
    </script>
@endsection
