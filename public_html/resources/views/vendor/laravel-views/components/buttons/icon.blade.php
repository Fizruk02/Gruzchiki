@props(['icon', 'size' => 'md', 'color' => 'gray'])

<button {{ $attributes }} class="{{ $size === 'sm' ? 'p-1' : 'p-2'  }} border-2 border-transparent text-{{$color}}-600 rounded-full hover:text-{{$color}}-700 focus:outline-none focus:text-{{$color}}-700 focus:bg-{{$color}}-100 transition duration-150 ease-in-out">
  <i data-feather="{{ $icon }}" class="{{ $size === 'sm' ? 'w-5 h-5' : ''  }}"></i>
</button>
