<x-filament::widget>
    <div
        x-data="{
            openId: null,
            isMobile: false,

            init() {
                this.isMobile = window.matchMedia('(pointer: coarse)').matches;
            }
        }"
        class="product-model-widget"
    >
        <div class="product-model-scroll">

            @foreach ($this->modelGroups as $group)

                @php
                    $label = $group['label'] ?? null;
                    $groupId = $group['id'] ?? null;

                    $items = collect($group['models'] ?? [])
                        ->map(fn ($item) => [
                            'id' => $item['id'] ?? null,
                            'name' => trim((string) ($item['name'] ?? '')),
                        ])
                        ->filter(fn ($item) => $item['name'] !== '')
                        ->unique(fn ($item) => mb_strtoupper($item['name']))
                        ->values();

                    $hasSubModels = $items->count() > 1;
                @endphp

                @continue(blank($label))

                <div
                    class="model-card"
                    @mouseenter="if (!isMobile) openId = '{{ $groupId }}'"
                    @mouseleave="if (!isMobile) openId = null"
                >

                    <!-- HEADER -->
                    <div
                        class="model-card__button"
                        @pointerdown="if (isMobile) openId = (openId === '{{ $groupId }}' ? null : '{{ $groupId }}')"
                    >

                        <button
                            type="button"
                            class="model-card__title"
                            wire:click="$dispatch('modelSearch', @js([$label]))"
                        >

                            <span class="model-card__icon">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor"
                                     stroke-width="2"
                                     class="model-icon">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          d="M10.5 19.5h3m-6.75 2.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-15a2.25 2.25 0 0 0-2.25-2.25H6.75A2.25 2.25 0 0 0 4.5 4.5v15a2.25 2.25 0 0 0 2.25 2.25Z"/>
                                </svg>
                            </span>

                            <span class="model-card__label">
                                {{ $label }} @if ($hasSubModels) ▼ @endif
                            </span>

                        </button>

                    </div>

                    <!-- DROPDOWN -->
                    @if ($hasSubModels)
                        <div
                            x-cloak
                            x-show="openId === '{{ $groupId }}'"
                            @click.outside="openId = null"
                            class="model-dropdown"
                        >
                            <div class="model-dropdown__content">

                                @foreach ($items as $item)

                                    @if(isset($this->productModelIds[$item['id']]))

                                        <button
                                            type="button"
                                            wire:click="$dispatch('modelStrictSearch', @js([$item['id']]))"
                                            class="model-dropdown__chip"
                                            @click.stop="openId = null"
                                        >

                                            <span class="model-dropdown__icon">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                     fill="none"
                                                     viewBox="0 0 24 24"
                                                     stroke="currentColor"
                                                     stroke-width="2"
                                                     class="model-icon-small">
                                                    <path stroke-linecap="round"
                                                          stroke-linejoin="round"
                                                          d="M10.5 19.5h3m-6.75 2.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-15a2.25 2.25 0 0 0-2.25-2.25H6.75A2.25 2.25 0 0 0 4.5 4.5v15a2.25 2.25 0 0 0 2.25 2.25Z"/>
                                                </svg>
                                            </span>

                                            <span class="truncate">
                                                {{ $item['name'] }}
                                            </span>

                                        </button>

                                    @endif

                                @endforeach

                            </div>
                        </div>
                    @endif

                </div>

            @endforeach

        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }

        .product-model-widget {
            width: 100%;
            padding: 0.375rem 0;
        }

        .product-model-scroll {
            display: flex;
            flex-wrap: wrap;
            gap: 0.625rem;
            padding: 0.25rem;
            justify-content: center;
        }

        /* ✅ FIX: no theme switching = no flicker */
        .model-card {
            position: relative;
            min-width: 185px;
            border-radius: 16px;

            background: #0f0f10;
            color: #fff;

            transition: background 0.2s ease, box-shadow 0.2s ease;
        }

        .model-card:hover {
            background: #151518;
        }

        .model-card__button {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.4rem;
            padding: 0.48rem 0.68rem;
            border-radius: 999px;
            cursor: pointer;
            box-shadow: 0 3px 10px rgba(0,0,0,0.07);
            transition: all 0.2s ease;
        }

        .model-card:hover .model-card__button {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.12);
        }

        .model-card__title {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            flex: 1;
            min-width: 0;
            text-align: left;
        }

        .model-card__label {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .model-card__icon {
            width: 2rem;
            height: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: rgba(34,197,94,0.12);
            color: #16a34a;
        }

        .model-dropdown__icon {
            width: 1.5rem;
            height: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: rgba(34,197,94,0.12);
            color: #16a34a;
        }

        .model-icon,
        .model-icon-small {
            width: 70%;
            height: 70%;
        }

        .model-dropdown {
            position: absolute;
            left: 0;

            width: fit-content;
            max-width: 560px;
            min-width: 200px;

            border-radius: 16px;
            box-shadow: 0 16px 36px rgba(0, 0, 0, 0.16);
            z-index: 9999999;

            padding: 0.5rem;
            backdrop-filter: blur(6px);
            background: #444;
            color: #fff;
            text-align: left;
        }

        .model-dropdown__content {
            display: flex;
            flex-direction: column;

            gap: 0.4rem;
        }

        .model-dropdown__chip {
            display: flex;
            align-items: center;
            gap: 0.5rem;

            width: 100%;
            white-space: nowrap;

            padding: 4px 6px;

            cursor: pointer;
        }

        .model-dropdown__chip:hover {
            cursor: pointer;
            background: rgba(34, 197, 94, 0.12);
            border-radius: 10px;
        }
    </style>
</x-filament::widget>
