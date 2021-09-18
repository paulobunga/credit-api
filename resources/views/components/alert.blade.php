<div class="absolute h-screen w-full flex z-10 items-center justify-center bg-black bg-opacity-25" x-data="$store.$alert" x-init="$watch('open', value => {
    if(value){

    }
  })" x-show="open">
    <div class="w-80 sm:w-50 flex flex-col jusctify-center" x-show="open" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        <div class="flex items-center font-bold py-3 px-4 rounded shadow-md mb-2" :class="{
                    'bg-green-500': type == 'success',
                    'bg-blue-500': type == 'info',
                    'bg-yellow-500': type == 'warn',
                    'bg-red-500': type == 'error'
                }" role="alert">
            <!-- icon -->
            <div class="text-white border-2 mr-3 rounded-full h-8 w-8 py-3 flex justify-center items-center" :class="{
                    'bg-green-500': type == 'success',
                    'bg-blue-500': type == 'info',
                    'bg-yellow-500': type == 'warn',
                    'bg-red-500': type == 'error'
                }" role="icon">
                <i :class="{
                    'fas fa-check': type == 'success',
                    'fas fa-info': type == 'info',
                    'fas fa-exclamation': type == 'warn',
                    'fas fa-skull-crossbones': type == 'error'
                }"></i>
            </div>
            <!-- message -->
            <div class="text-white" x-text="message"></div>
            <!-- close btn -->
            <button type="button" class="flex ml-auto text-white" x-on:click="open = false">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
</div>

@once
@push('js')
<script>
    document.addEventListener('alpine:init', ()=>{
        Alpine.store('$alert', {
            open: false,
            message: '',
            type: '',
            show(type, message) {
                this.type= type;
                this.message = message;
                this.open = true;
            },
        });
    });
</script>
@endpush
@endonce