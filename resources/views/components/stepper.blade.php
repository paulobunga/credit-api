<div class="flex items-center mx-4 p-4">
    @foreach($steps as $i => $step)
    <div class="flex items-center relative" x-data="{{ json_encode($step) }}">
        <div class="rounded-full h-12 w-12 py-3 border-2 flex items-center justify-center" x-bind:class="{
                'border-gray-500 text-gray-500': status == 0,
                'border-blue-500 bg-blue-500 text-white': status == 1,
                'border-green-500 bg-green-500 text-white': status == 2,
                'border-red-600 bg-red-500 text-white': status == -1
            }">
            <i class="{{ $step['icon'] }}"></i>
        </div>
        <div class="absolute top-0 -ml-10 text-center mt-16 w-32 text-xs font-medium uppercase" x-bind:class="{
                'text-gray-500': status ==0,
                'text-blue-500': status == 1,
                'text-green-500': status == 2,
                'text-red-500': status == -1,
            }">
            {{ $step['label'] }}
        </div>
    </div>
    @if($i != count($steps) - 1)
    <div class="flex-auto border-t-2" x-data="{{ json_encode($step) }}" x-bind:class="
        {
            'border-gray-500': status ==0,
            'border-blue-500': status == 1,
            'border-green-500': status == 2,
            'border-red-500': status == -1,
        }
        "></div>
    @endif
    @endforeach
</div>