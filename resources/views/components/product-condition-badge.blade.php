@props(['condition'])

@php
    $config = match($condition) {
        'good'    => ['label' => 'Baik', 'bg' => '#defbe6', 'color' => '#0e6027', 'border' => '#24a148'],
        'damaged' => ['label' => 'Rusak', 'bg' => '#fdf6dd', 'color' => '#8e6a00', 'border' => '#f1c21b'],
        'lost'    => ['label' => 'Hilang', 'bg' => '#fff1f1', 'color' => '#750e13', 'border' => '#da1e28'],
        default   => ['label' => ucfirst($condition), 'bg' => '#f4f4f4', 'color' => '#4d4d4d', 'border' => '#e0e0e0'],
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
