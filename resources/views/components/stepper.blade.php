<ul class="flex items-center text-lg mb-4">
    @foreach($steps as $i => $step)
    <li class="flex items-center inline-flex p-4 border-b-4 w-full justify-center" x-data="{{ json_encode($step) }}"
        x-bind:class="{
        'border-gray-500 text-gray-500 border-opacity-25': status == 0,
        'border-blue-500 text-blue-500': status == 1,
        'border-green-500 text-green-500': status == 2,
        'border-red-600 text-red-500': status == -1
    }">
        {{ $step['label'] }}
    </li>
    @endforeach
</ul>