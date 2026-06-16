@php
    // If your $images are storage paths, convert them here so Alpine gets full URLs.
    // Otherwise, keep as-is.
    $images = collect($images ?? [])
        ->filter()
        ->map(fn($img) => function_exists('getImageUrl') ? getImageUrl($img) : $img)
        ->values()
        ->all();
@endphp

<div
        x-data="{
        images: @js($images),
        i: 0,
        get current(){ return this.images[this.i] ?? null },
        prev(){ if (this.images.length) this.i = (this.i - 1 + this.images.length) % this.images.length },
        next(){ if (this.images.length) this.i = (this.i + 1) % this.images.length },
        setIdx(idx){ this.i = idx },

        // swipe support
        _swipeX: null,
        _swipeY: null,
        _swipeStart(e){
            const t = e.touches?.[0];
            if(!t) return;
            this._swipeX = t.clientX; this._swipeY = t.clientY;
        },
        _swipeMove(e){
            // prevent vertical scroll from triggering slide if mostly horizontal
            if (this._swipeX === null) return;
            const t = e.touches?.[0];
            if(!t) return;
            const dx = t.clientX - this._swipeX;
            const dy = Math.abs(t.clientY - this._swipeY);
            if (Math.abs(dx) > 40 && dy < 40) {
                dx < 0 ? this.next() : this.prev();
                this._swipeX = null; this._swipeY = null;
            }
        },
        _swipeEnd(){ this._swipeX = null; this._swipeY = null; },
    }"
        x-init="$nextTick(() => {
        const onKey = e => {
            if (e.key === 'ArrowLeft') prev();
            if (e.key === 'ArrowRight') next();
        };
        window.addEventListener('keydown', onKey);
        $el._cleanup = () => window.removeEventListener('keydown', onKey);
    })"
        x-on:destroy.window="if ($el._cleanup) $el._cleanup()"
        class="w-full"
>
    @if (empty($images))
        <div class="text-center text-sm text-slate-500 py-8">@lang('admin.no_image')</div>
    @else

        <div class="relative">

            <!-- Main image (single, bound via Alpine) -->
            <div
                    class="aspect-video w-full overflow-hidden rounded-xl bg-slate-100 flex items-center justify-center"
                    @touchstart.passive="_swipeStart($event)"
                    @touchmove.passive="_swipeMove($event)"
                    @touchend.passive="_swipeEnd()"
            >
                <template x-if="current">
                    <img :src="current" alt="{{ $title ?? 'Image' }}" style="width: 350px;
    object-fit: cover;
    margin: 5%;">
                </template>
            </div>

            <!-- Controls -->
        </div>

        <!-- Thumbs -->
        <div class="mt-4 grid grid-cols-6 sm:grid-cols-8 md:grid-cols-10 gap-2 flex" style="display: flex">
            <template x-for="(img, idx) in images" :key="idx">
                <button type="button"
                        @click="setIdx(idx)"
                        class="relative rounded-lg overflow-hidden ring-1 ring-slate-200 hover:ring-slate-300 focus:outline-none">
                    <img :src="img" class="h-16 w-full object-cover">
                    <span class="absolute inset-0 rounded-lg"
                          :class="idx === i ? 'ring-2 ring-offset-2 ring-primary-500' : ''"></span>
                </button>
            </template>
        </div>
    @endif
</div>
