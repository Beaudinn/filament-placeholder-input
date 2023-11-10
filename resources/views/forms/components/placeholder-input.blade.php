@php
    $linksWith = $getLinksWith();
    $variables = $getVariables();
    $canCopy = $canCopy();
	$activeLocale = $getActiveLocale();
@endphp

<x-dynamic-component
        :component="$getFieldWrapperView()"
        :id="$getId()"

>
    <div x-data="{
        linked: @js($linksWith->keys()->first()),
        active_locale: @js($activeLocale),
        addToBody (e, key) {
            // Append the variable (key) to the body
            let original = $wire.get('data.' + this.active_locale + '.' + this.linked)
            let updated = ((! original) ? '' : original + ' ') + '@{{ ' + key + ' }}'

            $wire.set('data.' + this.active_locale + '.' + this.linked, updated)

            // Let tiptap know the content has been updated, on the next tick
            window.setTimeout(() => $dispatch('refresh-tiptap-editors'), 0)
        },
        copyToClipboard (key) {
            // Copy the variable (key) to the clipboard
            // Only works on secure origins (https://)
            navigator.clipboard.writeText('@{{ ' + key + ' }}')

            new FilamentNotification()
                .title('Copied \'' + key + '\' to clipboard')
                .success()
                .send()
        }
    }">

        @if ($linksWith && $linksWith->count() > 1)
            <x-filament::input.wrapper>
                <x-filament::input.select x-model="linked">
                    @foreach ($linksWith as $target => $label)
                        <option value="{{ $target }}">
                            {{ $label }}
                        </option>
                    @endforeach
                </x-filament::input.select>
            </x-filament::input.wrapper>
        @endif

        <div class="flex flex-wrap gap-3 mt-6">
            @foreach ($variables as $variable)

                @if ($linksWith && $linksWith->isNotEmpty())
                    @php
                        $string = 'addToBody($event, "' . $variable->getKey().'")';
                    @endphp
                    <x-filament::button
                            :x-on:click="$string"
                            icon="heroicon-o-plus"
                            size="xs"
                            outlined
                            color="info">
                        {{ $variable->getLabel() }}
                    </x-filament::button>
                @elseif($canCopy)
                    <x-filament::button
                            x-on:click="copyToClipboard(@js($variable->getKey()))"
                            icon="heroicon-o-plus"
                            color="info">
                        {{ $variable->getLabel() }}
                    </x-filament::button>
                @endif

            @endforeach
        </div>
    </div>
</x-dynamic-component>

