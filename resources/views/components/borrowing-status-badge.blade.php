@props(['status'])

@php
    $config = match($status) {
        'returned' => ['label' => 'Dikembalikan', 'bg' => '#defbe6', 'color' => '#0e6027', 'border' => '#24a148'],
        'overdue'  => ['label' => 'Terlambat', 'bg' => '#fff1f1', 'color' => '#750e13', 'border' => '#da1e28'],
        'borrowed' => ['label' => 'Dipinjam', 'bg' => '#e8f5ff', 'color' => '#0043ce', 'border' => '#0062ff'],
        default    => ['label' => ucfirst($status), 'bg' => '#f4f4f4', 'color' => '#4d4d4d', 'border' => '#e0e0e0'],
    };
@endphp

<span style="
    display: inline-block;
    padding: 2px 8px;
    font-size: 12px;
    font-weight: 400;
    letter-spacing: 0.32px;
    background: {{ $config['bg'] }};
    color: {{ $config['color'] }};
    border: 1px solid {{ $config['border'] }};
">{{ $config['label'] }}</span>
