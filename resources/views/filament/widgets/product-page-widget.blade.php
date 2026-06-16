<x-filament::widget>
    <x-filament::card class="stats-widget-card">
        @php
            $stats = $this->getStats();
        @endphp

        <div class="stats-widget">
            <div class="stats-widget__scroll">
                @foreach ($stats as $stat)
                    @php
                        $bgColor = $stat['bg'] ?? '#3b82f6';
                        $textClass = $stat['text'] ?? 'text-white';
                    @endphp

                    <div
                        class="stat-box {{ $textClass }}"
                        style="background-color: {{ $bgColor }};"
                    >
                        <div class="stat-box__header">
                            @if (!empty($stat['icon']))
                                <span class="stat-box__icon">
                                    <i class="{{ $stat['icon'] }}"></i>
                                </span>
                            @endif

                            <span class="stat-box__label">
                                {{ $stat['label'] }}
                            </span>
                        </div>

                        <div class="stat-box__body">
                            <div class="stat-box__desc">
                                {{ $stat['description'] ?? ' ' }}
                            </div>

                            <div class="stat-box__value">
                                {{ $stat['value'] }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <style>
            .stats-widget-card,
            .stats-widget-card .fi-card,
            .stats-widget-card .fi-section-content,
            .stats-widget-card .fi-widget {
                overflow: visible !important;
            }

            .stats-widget {
                width: 100%;
                overflow-x: auto;
                overflow-y: hidden;
                -webkit-overflow-scrolling: touch;

                /* hide scrollbar by default */
                scrollbar-width: none; /* Firefox */
            }

            .stats-widget:hover {
                scrollbar-width: thin; /* Firefox show on hover */
            }

            /* Webkit browsers (Chrome, Edge, Safari) */

            .stats-widget::-webkit-scrollbar {
                height: 0px;
                transition: height 0.2s ease;
            }

            .stats-widget:hover::-webkit-scrollbar {
                height: 8px;
            }

            .stats-widget::-webkit-scrollbar-thumb {
                background: rgba(120,120,120,0.35);
                border-radius: 999px;
            }

            .stats-widget::-webkit-scrollbar-track {
                background: transparent;
            }

            .stats-widget__scroll {
                display: inline-flex;
                gap: 1rem;
                padding: 0.25rem 0.125rem 0.5rem;
                min-width: max-content;
            }

            .stat-box {
                flex: 0 0 auto;
                min-width: 220px;
                min-height: 110px;
                padding: 12px 14px;
                border-radius: 12px;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            }

            .stat-box__header {
                display: flex;
                align-items: center;
                gap: 6px;
                margin-bottom: 6px;
                font-size: 14px;
                font-weight: 600;
                opacity: 0.9;
            }

            .stat-box__icon i {
                font-size: 20px;
                opacity: 0.9;
            }

            .stat-box__label {
                min-width: 0;
            }

            .stat-box__body {
                display: flex;
                align-items: flex-end;
                justify-content: space-between;
                gap: 10px;
            }

            .stat-box__desc {
                max-width: 140px;
                font-size: 12px;
                opacity: 0.85;
                line-height: 1.35;
            }

            .stat-box__value {
                font-size: 22px;
                font-weight: 700;
                text-align: right;
                line-height: 1.1;
                white-space: nowrap;
            }

            .text-dark {
                color: #111827 !important;
            }

            @media (max-width: 768px) {
                .stats-widget__scroll {
                    gap: 0.75rem;
                }

                .stat-box {
                    min-width: 200px;
                    min-height: 100px;
                    padding: 10px 12px;
                }

                .stat-box__header {
                    font-size: 13px;
                }

                .stat-box__icon i {
                    font-size: 18px;
                }

                .stat-box__desc {
                    max-width: 120px;
                    font-size: 11px;
                }

                .stat-box__value {
                    font-size: 20px;
                }
            }
        </style>
    </x-filament::card>
</x-filament::widget>
