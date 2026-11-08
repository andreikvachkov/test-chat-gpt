@php
    $statePath = $getStatePath();
    $disabled = $isDisabled();
@endphp
<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div class="kamali-category-tree" x-data="{ search: '' }">
        <x-filament::input.wrapper>
            <x-filament::input type="search" x-model="search" placeholder="Поиск категорий" aria-label="Поиск категорий" />
        </x-filament::input.wrapper>
        <div class="kamali-category-tree-items">
            @foreach (\App\Services\CategoryTree::nodes() as $node)
                <label class="kamali-category-tree-item" style="padding-inline-start: {{ $node['depth'] * 22 }}px"
                    data-depth="{{ $node['depth'] }}" data-search="{{ mb_strtolower($node['name']) }}"
                    x-show="!search || $el.dataset.search.includes(search.toLocaleLowerCase())">
                    <input type="checkbox" class="fi-checkbox-input" value="{{ $node['id'] }}"
                        {{ $applyStateBindingModifiers('wire:model') }}="{{ $statePath }}" @disabled($disabled) />
                    <span>{{ $node['name'] }}</span>
                </label>
            @endforeach
        </div>
    </div>
</x-dynamic-component>
