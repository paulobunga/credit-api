<header class="shadow">
    <nav
        class="sm:max-w-2xl md:container lg:max-w-4xl mx-auto items-center sm:items-start font-sans flex flex-col text-center content-center flex-row py-2 px-6 bg-white w-full">
        <a class="mb-2 flex flex-row items-center">
            <div class="h-10 w-10 self-center mr-2">
                <img class="h-10 w-10 self-center" src="{{ asset('/img/logo.png') }}" />
            </div>
            <div class="text-2xl no-underline text-grey-darkest hover:text-blue-dark font-sans font-bold">
                {{ env('APP_NAME') }}
            </div>
        </a>
    </nav>
</header>