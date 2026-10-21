{{-- Formats an amount with the hospital currency symbol, tabular figures. --}}
@props(['amount' => 0, 'decimals' => 2, 'symbol' => null])

<span {{ $attributes->class(['money']) }}>{{ $symbol ?? get_setting('currency_symbol', 'Rs') }} {{ number_format((float) $amount, $decimals) }}</span>
