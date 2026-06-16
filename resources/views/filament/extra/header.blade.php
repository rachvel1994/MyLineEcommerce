@php use Illuminate\Contracts\Pagination\Paginator; @endphp

<div class="fi-ta-header border-b border-gray-200 dark:border-gray-700 px-4 py-3">

    @php
        $records = $table->getRecords();
    @endphp
    <div class="fi-section-header-text-ctn">
        <h2 class="fi-section-header-heading text-base font-semibold text-gray-800 dark:text-white" style="font-size: medium; font-weight: bold;">
            {{ $table->getHeading() }}
        </h2>
    </div>
    <div class="fi-section-header flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">

        <!-- LEFT: TITLE -->


        <!-- RIGHT: ACTIONS / PAGINATION -->
        @if ($records instanceof Paginator)
            <div class="fi-section-header-after-ctn w-full sm:w-auto">

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3 w-full sm:w-auto">

                    <!-- Pagination -->
                    <x-filament::pagination
                            :extreme-links="true"
                            :page-options="[10, 25, 50, 100, 'all']"
                            :paginator="$records"
                            class="fi-ta-pagination w-full sm:w-auto"
                    />

                </div>
            </div>
        @endif

    </div>

</div>
