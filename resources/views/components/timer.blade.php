<div class="w-full px-4 text-white text-2xl text-center flex items-center justify-center grid grid-flow-col gap-2 grid-cols-3 sm:grid-cols-none timer-container"
    x-data="$timer">
    @isset($custom)
    {{ $custom }}
    @else
    <div class="mx-1 p-2 bg-blue-600 rounded-lg sm:w-24 hours">
        <div class="font-mono leading-none sm:text-2xl md:text-4xl" x-text="hours">00</div>
        <div class="font-mono uppercase text-sm leading-none">Hours</div>
    </div>
    <div class="mx-1 p-2 bg-blue-600 rounded-lg sm:w-24 minutes">
        <div class="font-mono leading-none sm:text-2xl md:text-4xl" x-text="minutes">00</div>
        <div class="font-mono uppercase text-sm leading-none">Minutes</div>
    </div>
    <div class="mx-1 p-2 bg-blue-600 rounded-lg sm:w-24 seconds">
        <div class="font-mono leading-none sm:text-2xl md:text-4xl" x-text="seconds">00</div>
        <div class="font-mono uppercase text-sm leading-none">Seconds</div>
    </div>
    @endisset
</div>

@once
@push('js')
<script>
    document.addEventListener('alpine:init', ()=>{
        Alpine.data('$timer', ()=>({
            seconds: '00',
            minutes: '00',
            hours: '00',
            distance: 0,
            countdown: null,
            expiredTime: new Date("{{ $dateTime }}").getTime(),
            now: new Date().getTime(),
            init() {
                this.countdown = setInterval(() => {
                    // Calculate time
                    this.now = new Date().getTime();
                    this.distance = this.expiredTime - this.now;
                    // Set Times
                    this.days = this.padNum( Math.floor(this.distance / (1000*60*60*24)) );
                    this.hours = this.padNum( Math.floor((this.distance % (1000*60*60*24)) / (1000*60*60)) );
                    this.minutes = this.padNum( Math.floor((this.distance % (1000*60*60)) / (1000*60)) );
                    this.seconds = this.padNum( Math.floor((this.distance % (1000*60)) / 1000) );
                    // Stop
                    if (this.distance < 0) {
                        clearInterval(this.countdown);
                        this.days = '00';
                        this.hours = '00';
                        this.minutes = '00';
                        this.seconds = '00';
                    }
                },100);
            },
            padNum(num) {
                var zero = '';
                for (var i = 0; i < 2; i++) {
                    zero += '0';
                }
                return (zero + num).slice(-2);
            }
        }));
    });
</script>
@endpush
@endonce